# Arsitektur Aplikasi Mobile: Ekspedisi & Logistik B2B
## PT Dwi Ravi Ladang Energi (Field & Driver Operations)

Dokumen ini mendefinisikan arsitektur sistem perangkat bergerak (*Mobile Architecture*) yang terintegrasi secara *real-time* dengan backend orkestrator **Laravel 12 / MySQL** yang telah dibangun. Arsitektur ini dirancang khusus untuk mengakomodasi kebutuhan lapangan di wilayah operasional kepulauan (Kabupaten Natuna dan rute lintas armada), dengan keandalan tinggi pada kondisi sinyal seluler yang fluktuatif (*offline-first*).

---

## 1. Diagram Arsitektur Tingkat Tinggi (High-Level Mobile Architecture)

Arsitektur aplikasi mobile mengadopsi standar **Clean Architecture** berlapis tiga (*Presentation, Domain, Data*) dengan pola *Offline-First Local Storage Engine*:

```mermaid
flowchart TD
    subgraph UI_Layer["1. PRESENTATION LAYER (Flutter / Dart UI)"]
        UI_Screens["Mobile Screens<br/>- Active Trip / Task List<br/>- Milestone Linear Stepper<br/>- Camera & Image Compressor<br/>- Signature Pad Serah Terima<br/>- QC Defect Inspection Form"]
        UI_BLoC["State Management (BLoC / Cubit)<br/>- AuthBloc<br/>- WorkOrderBloc<br/>- TrackingSyncBloc<br/>- EpodUploadBloc"]
    end

    subgraph Domain_Layer["2. DOMAIN LAYER (Pure Business Logic)"]
        UC["Use Cases / Interactors<br/>- GetAssignedTripsUseCase<br/>- ProgressMilestoneUseCase (Forward-Only)<br/>- SubmitEpodWithGeoTagUseCase<br/>- SyncOfflineQueueUseCase"]
        Entities["Domain Entities<br/>- WorkOrderEntity<br/>- DeliveryProofEntity<br/>- TrackingLogEntity<br/>- DefectChecklistEntity"]
        RepoInterfaces["Repository Interfaces (Contracts)"]
    end

    subgraph Data_Layer["3. DATA & PERSISTENCE LAYER"]
        RepoImpl["Repository Implementations"]
        LocalDS["Local Data Source (Offline-First)<br/>- SQLite / Drift / Isar DB<br/>- Secure Storage (Encrypted JWT)<br/>- Offline Mutation Queue Table"]
        RemoteDS["Remote Data Source (Network)<br/>- Dio HTTP Client (Interceptors)<br/>- Retrofit REST Client<br/>- Multipart File Uploader"]
    end

    subgraph Backend_Cloud["4. BACKEND INTEGRATION (Laravel 12 REST API)"]
        LaravelAPI["Laravel Orchestrator Backend<br/>- Sanctum Bearer Token Auth<br/>- /api/v1/driver/trips<br/>- /api/v1/driver/milestone<br/>- /api/v1/driver/epod (Multipart)<br/>- /api/v1/driver/telemetry"]
        CloudStorage["Storage (e-POD Photos & BAST)<br/>Public Disk / S3 / Cloudflare R2"]
    end

    UI_Screens <--> UI_BLoC
    UI_BLoC <--> UC
    UC <--> Entities
    UC <--> RepoInterfaces
    RepoInterfaces <--> RepoImpl
    RepoImpl <--> LocalDS
    RepoImpl <--> RemoteDS
    RemoteDS <-->|HTTPS / TLS 1.3| LaravelAPI
    LaravelAPI --> CloudStorage
```

---

## 2. Pilihan Tech Stack Rekomendasi

| Komponen | Pilihan Teknologi | Alasan Pemilihan & Relevansi Akademik |
| :--- | :--- | :--- |
| **Framework** | **Flutter (Dart 3.x)** | Standar industri untuk aplikasi cross-platform performa tinggi (60/120 FPS *native rendering engine*), manajemen memori efisien untuk rendering kamera & peta. |
| **Pola Arsitektur** | **Clean Architecture (Uncle Bob)** | Memisahkan kode logika bisnis secara tegas dari antarmuka UI dan driver basis data, sehingga mudah diuji (*unit testing*) dan *maintainable*. |
| **State Management** | **flutter_bloc (BLoC Pattern)** | Menjamin aliran data yang reaktif, searah (*unidirectional data flow*), mudah dilacak status transisi *state* (`Initial`, `Loading`, `Success`, `Error`). |
| **Network Client** | **Dio + Retrofit** | Mendukung *HTTP Interceptors* untuk *auto-attach* token Sanctum, penanganan *refresh token*, serta pemantauan *upload progress* foto e-POD. |
| **Local Database** | **Drift (SQLite) / Hive** | Basis data lokal relasional berkecepatan tinggi untuk menyimpan *cache* tugas pengiriman dan antrean perubahan status (*Offline Mutation Queue*). |
| **Hardware Access** | `camera`, `image_picker`, `geolocator`, `signature` | Akses native hardware Android/iOS untuk foto Surat Jalan, kompresi gambar on-device, penandaan koordinat lintang-bujur GPS, dan tanda tangan digital. |

---

## 3. Strategi Ketahanan Offline-First (Offline Sync Engine)

Kondisi kepulauan dan rute ekspedisi sering kali melintasi wilayah tanpa sinyal seluler (*blank spot*). Aplikasi mobile dirancang dengan prinsip **Zero Failure on Low Network**:

```mermaid
sequenceDiagram
    autonumber
    actor Driver as Driver Lapangan
    participant MobileUI as Mobile UI / BLoC
    participant SyncQueue as Local Offline Queue (SQLite)
    participant BackgroundService as WorkManager Sync Service
    participant Backend as Laravel REST API

    Driver->>MobileUI: 1. Foto Surat Jalan Fisik & Konfirmasi Selesai Bongkar
    MobileUI->>MobileUI: 2. Kompresi Foto (< 500 KB) & Tag GPS Terakhir
    MobileUI->>SyncQueue: 3. Simpan mutasi data (status: PENDING_SYNC)
    MobileUI-->>Driver: 4. Berikan feedback sukses instan (UI update ke UNLOADED)

    alt Kondisi Terhubung Internet (Online)
        SyncQueue->>Backend: 5a. POST /api/v1/driver/epod (Multipart upload)
        Backend-->>SyncQueue: 6a. Respons 201 Created (Synced)
        SyncQueue->>SyncQueue: 7a. Tandai antrean sebagai COMPLETED
    else Kondisi Tanpa Sinyal (Blank Spot / Offline)
        SyncQueue--xBackend: 5b. Request gagal (Network Timeout)
        SyncQueue->>SyncQueue: 6b. Simpan antrean, aktifkan retry policy
        Note over BackgroundService,SyncQueue: Tunggu koneksi internet pulih (Connectivity Watcher)
        BackgroundService->>Backend: 7b. Otomatis sync di background saat internet aktif
        Backend-->>SyncQueue: 8b. Respons 201 Created (Synced)
    end
```

### Aturan Utama Sinkronisasi:
1. **Idempotency Key**: Setiap transaksi status dan upload e-POD diberi UUID unik di sisi HP agar backend tidak menduplikasi data ketika terjadi percobaan ulang pengiriman (*retry*).
2. **Kompresi Gambar On-Device**: Foto beresolusi tinggi dari kamera (3–8 MB) otomatis dikompresi menjadi format JPEG berkualitas 80% dengan batas lebar maksimal 1280px (< 450 KB) sebelum masuk ke antrean kirim untuk menghemat kuota dan mempercepat pengunggahan.

---

## 4. Struktur Modul & Fitur Aplikasi Mobile

```
lib/
├── core/
│   ├── constants/             # API Endpoints, App Colors, Asset Paths
│   ├── errors/                # Failure & Exception classes
│   ├── network/               # Dio Client, Auth Interceptors, Network Info
│   ├── services/              # LocationService, CameraService, CompressionService
│   └── utils/                 # CurrencyFormatter, DateFormatter
├── features/
│   ├── auth/                  # Modul Autentikasi Pengemudi / Akun Lapangan
│   │   ├── data/              # AuthRemoteDataSource, AuthRepositoryImpl
│   │   ├── domain/            # LoginUseCase, AuthEntity
│   │   └── presentation/      # LoginScreen, PinCodeScreen, AuthBloc
│   ├── work_orders/           # Modul Daftar Tugas & Perjalanan Aktif
│   │   ├── data/              # WorkOrderLocalDataSource, WorkOrderRepository
│   │   ├── domain/            # GetTripsUseCase, WorkOrderEntity
│   │   └── presentation/      # TripListScreen, TripDetailScreen, WorkOrderBloc
│   ├── tracking_milestones/   # Modul Pembaruan Status Searah (Forward-Only)
│   │   ├── domain/            # UpdateMilestoneUseCase
│   │   └── presentation/      # LinearStepTimelineWidget, StatusUpdateModal
│   ├── epod_inspection/       # Modul Serah Terima e-POD & Lembar Cacat Barang
│   │   ├── data/              # EpodSyncQueueTable, EpodRemoteUploader
│   │   ├── domain/            # SubmitEpodUseCase, DefectInspectionEntity
│   │   └── presentation/      # EpodCameraScreen, SignaturePadScreen, DefectChecklistScreen
└── main.dart                  # Entry Point & Dependency Injection (GetIt)
```

---

## 5. Spesifikasi Kontrak RESTful API (Integrasi Laravel Backend)

Backend Laravel mengekspos rute terproteksi API Token (`auth:sanctum`):

### A. Autentikasi Driver
- **Endpoint**: `POST /api/v1/auth/login-driver`
- **Request Body**:
  ```json
  {
    "phone_number": "081234567890",
    "pin": "123456"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "success": true,
    "token": "1|xyzSecureSanctumBearerToken...",
    "driver": {
      "id": 14,
      "name": "Budi Santoso",
      "phone": "081234567890",
      "transporter": "PT Natuna Angkutan Mandiri"
    }
  }
  ```

### B. Mengambil Daftar Tugas Aktif (Work Orders)
- **Endpoint**: `GET /api/v1/driver/work-orders`
- **Headers**: `Authorization: Bearer <token>`
- **Response (200 OK)**:
  ```json
  {
    "data": [
      {
        "wo_number": "WO-202610-A9128",
        "qr_code_hash": "a1b2c3d4e5f6...",
        "delivery_note_number": "SJ/202610/0042",
        "current_status": "ON_THE_WAY",
        "origin": {
          "facility": "Depo LPG Ranai",
          "address": "Jl. Ali Murtopo, Ranai Kota",
          "loading_date": "2026-11-20 08:30:00"
        },
        "destination": {
          "facility": "Pangkalan Gas Bunguran Barat",
          "address": "Sedanau, Bunguran Barat",
          "unloading_date": "2026-11-20 16:00:00"
        },
        "items": [
          {"name": "Tabung LPG 3kg Isi", "quantity": 560, "unit": "Tabung"},
          {"name": "Tabung LPG 12kg Kosong", "quantity": 40, "unit": "Tabung"}
        ]
      }
    ]
  }
  ```

### C. Pembaruan Milestone Pengiriman (Forward-Only)
- **Endpoint**: `POST /api/v1/driver/work-orders/{hash}/milestone`
- **Request Body**:
  ```json
  {
    "status": "AT_DESTINATION",
    "latitude": 3.945210,
    "longitude": 108.389140,
    "notes": "Armada telah tiba di gerbang gudang penerima"
  }
  ```

### D. Pengunggahan Bukti e-POD & Lembar Cek Cacat
- **Endpoint**: `POST /api/v1/driver/work-orders/{hash}/epod`
- **Content-Type**: `multipart/form-data`
- **Form Fields**:
  - `recipient_name`: Nama PIC penerima di gudang.
  - `epod_image`: Berkas foto Surat Jalan fisik yang telah dicap & ditandatangani.
  - `cargo_photo`: Foto fisik kargo saat selesai dibongkar.
  - `signature_svg_base64`: (Opsional) Tanda tangan digital penerima di layar HP.
  - `has_discrepancy`: `0` atau `1`.
  - `defects`: JSON string checklist cacat (bocor, penyok, karat, rusak).

---

## 6. Fitur Unggulan Mobile untuk Operasional Lapangan

1. **Scanner Surat Jalan Cepat (QR Code)**:
   Driver cukup mengarahkan kamera HP ke QR Code yang tercetak di sudut atas lembar Surat Jalan resmi untuk langsung membuka detail tugas pengiriman tanpa perlu mengetik nomor DO.
2. **QC Cek Cacat Barang Interaktif (Halaman 2 Surat Jalan)**:
   Di lokasi muat/bongkar, driver dapat mencentang kondisi tabung gas LPG (bocor, penyok, valve rusak, karat parah) dan langsung mengambil foto kerusakan sebagai bukti sanggah klaim.
3. **Background GPS Telemetry**:
   Setiap 15 menit sekali selama status armada berada dalam `ON_THE_WAY`, modul latar belakang mengirimkan koordinat GPS ke server untuk mengupdate lokasi di portal pemantauan klien (*Client Tracking Portal*).
