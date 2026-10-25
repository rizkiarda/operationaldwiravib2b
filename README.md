# B2B Logistics Orchestrator & Freight Broker Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12" />
  <img src="https://img.shields.io/badge/Filament-3.x-FFA439?style=for-the-badge&logo=filament&logoColor=black" alt="Filament v3" />
  <img src="https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3" />
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS" />
  <img src="https://img.shields.io/badge/Database-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL" />
</p>

Sistem orkestrasi logistik B2B dan broker ekspedisi terintegrasi yang dirancang khusus untuk operasional **PT Dwi Ravi Ladang Energi** (Ranai Kota, Bunguran Timur, Natuna, Kepulauan Riau).

Platform ini menghubungkan alur pengiriman mulai dari permintaan pengiriman klien (*Order*), penugasan ke mitra transportir (*Work Order*), pembuatan dokumen Surat Jalan & lembar QC inspeksi cacat barang resmi, hingga portal pelacakan armada untuk pengemudi (*Driver Desk*) dan klien (*Client Tracking Portal*).

---

## 🌟 Fitur Utama

### 1. Manajemen Order (Multi-Item Repeater)
- **Multi-Item Entry**: Input berbagai jenis barang sekaligus dalam 1 Order (misal: Tabung LPG 3kg Isi, Tabung Kosong, Drum, Pallet, dsb) lengkap dengan kuantitas, satuan, dan berat.
- **Validasi Logistik Ketat**: Validasi otomatis yang memastikan tanggal rencana tiba (*ETA*) tidak boleh lebih awal dari tanggal muat (*Loading Date*).
- **Shortcut Terbitkan WO**: Tombol aksi langsung di baris tabel Order untuk langsung memproses dan mendisposisikan surat perintah kerja (*Work Order*).
- **Akses Tautan Pelacakan Klien**: Tombol langsung untuk menyalin/membuka tautan pelacakan klien.

### 2. Orkestrasi Work Order & Disposisi Armada
- **Kalkulasi Finansial Otomatis**: Menghitung otomatis pendapatan kotor (*Client Total*), beban vendor (*Vendor Total*), serta estimasi laba kotor (*Gross Profit Margin* dalam Rupiah dan persentase %).
- **Modal Input Cepat (On-the-Fly)**: Jika operator belum mendaftarkan Klien, Transportir, atau Jenis Armada, operator dapat langsung menambahkannya lewat modal *quick-create* langsung dari formulir WO tanpa perlu berpindah tab/halaman.
- **Mata Uang Terstandarisasi Rupiah (Rp)**: Seluruh pencatatan tarif sewa dan tagihan menggunakan format resmi Rupiah Indonesia.

### 3. Mesin Cetak Surat Jalan & Lembar QC Cacat Barang (PDF Engine)
- **Kop Surat Resmi Perusahaan**: Sesuai legalitas PT Dwi Ravi Ladang Energi (Jl. Ali Murtopo – Cemaka, Ranai Kota, Natuna).
- **Klausul Otomatisasi Sistem**: Dilengkapi pernyataan bahwa dokumen diterbitkan secara komputerisasi dan sah tanpa tanda tangan basah konvensional.
- **Halaman 2 - Lembar Kontrol Kualitas (QC Checklist)**: Berisi tabel pemeriksaan kondisi fisik barang (tabung/kemasan bocor, penyok, karat parah, segel rusak) untuk proses serah terima dan *double-checking* di lokasi muat & bongkar.

### 4. Portal Lapangan Driver / Vendor (`/track/{hash}`)
- **Mobile-First Driver Desk**: Halaman responsif yang dirancang untuk layar smartphone pengemudi tanpa memerlukan akun login.
- **Alur Status Linear (Forward-Only)**: Driver hanya dapat memperbarui status ke tahapan berikutnya (`Draft` $\rightarrow$ `Dispatched` $\rightarrow$ `Loading` $\rightarrow$ `In Transit` $\rightarrow$ `Arrived` $\rightarrow$ `Completed`) dan dilarang mundur ke tahapan yang sudah terlewati.
- **Bukti Serah Terima Elektronik (e-POD)**: Driver dapat mengunggah foto bukti serah terima barang fisik dan catatan penerimaan langsung dari lokasi tujuan.
- **Isolasi Status Order**: Pembaruan status pengiriman oleh driver terisolasi pada Work Order dan tidak memanipulasi status Order utama secara sepihak.

### 5. Portal Pelacakan Publik Klien (`/client/track/{hash}`)
- **100% Read-Only**: Portal khusus klien untuk memantau perjalanan kargo secara transparan tanpa risiko perubahan data oleh pihak luar.
- **Progress Bar & Timeline Real-Time**: Visualisasi tahapan pengiriman, estimasi waktu, informasi armada/sopir, serta riwayat log pergerakan.

---

## 🛠️ Tech Stack

| Komponen | Teknologi |
| :--- | :--- |
| **Backend Framework** | Laravel 12.x |
| **Admin Panel** | Filament v3 (TALL Stack) |
| **Bahasa Pemrograman** | PHP 8.2 / 8.3 |
| **Database** | MySQL / MariaDB |
| **PDF Generator** | DomPDF (`barryvdh/laravel-dompdf`) |
| **Frontend Styling** | Tailwind CSS & Blade |
| **Font & Ikon** | Heroicons & Lucide Icons |

---

## 🚀 Panduan Instalasi Lokal

### Prasyarat Sistem
- PHP >= 8.2 dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `gd`, `zip`, `intl`.
- Composer 2.x
- MySQL Server (misal via XAMPP atau Docker)
- Git

### Langkah-Langkah

1. **Clone Repository**
   ```bash
   git clone git@github.com:rizkiarda/operationaldwiravib2b.git
   cd operationaldwiravib2b
   ```

2. **Install Dependensi Composer**
   ```bash
   composer install
   ```

3. **Konfigurasi Environment (`.env`)**
   Salin berkas `.env.example` ke `.env`:
   ```bash
   copy .env.example .env
   ```
   Buka berkas `.env` dan sesuaikan koneksi database MySQL Anda:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=b2b_logistics_db
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key & Symlink Storage**
   ```bash
   php artisan key:generate
   php artisan storage:link
   ```

5. **Import Skema Database & Migrasi**
   Pastikan database `b2b_logistics_db` telah dibuat di MySQL, lalu jalankan:
   ```bash
   php artisan migrate
   ```
   *(Opsional: Anda juga dapat menggunakan berkas skema terstruktur di `database/schema_b2b_logistics.sql`)*.

6. **Buat Akun Administrator Filament**
   ```bash
   php artisan make:filament-user
   ```
   Masukkan Nama, Email, dan Password untuk akun admin Anda.

7. **Jalankan Web Server Lokal**
   ```bash
   php artisan serve
   ```
   Buka browser dan akses panel admin di:
   ```text
   http://127.0.0.1:8000/admin
   ```

---

## 📂 Struktur Direktori Utama

```text
app/
├── Filament/
│   └── Resources/
│       ├── Clients/            # Manajemen Master Data Klien (Shipper)
│       ├── Orders/             # Manajemen Order Kargo & Multi-Item
│       ├── Transporters/       # Manajemen Mitra Vendor Angkutan
│       ├── VehicleTypes/       # Master Jenis & Kapasitas Armada
│       └── WorkOrders/         # Disposisi Pengiriman, Margin, Cetak Dokumen
├── Http/Controllers/
│   ├── ClientTrackingController.php  # Portal Pelacakan Klien (Read-Only)
│   ├── DocumentPrintController.php   # Render PDF Surat Jalan & QC Sheet
│   └── VendorTrackingController.php  # Portal Driver & Upload e-POD
└── Models/                     # Model Eloquent & Relasi Database
resources/
└── views/
    ├── client/tracking.blade.php     # Template Pelacakan Publik Klien
    ├── pdf/surat_jalan.blade.php     # Template 2-Halaman Surat Jalan & QC
    └── vendor/tracking.blade.php     # Template Driver Desk Lapangan
```

---

## 🔒 Keamanan & Lisensi

- Semua data rahasia seperti credential database, API token, dan encryption key dikelola melalui `.env` dan tidak pernah dimasukkan ke dalam version control.
- Project ini dilisensikan di bawah lisensi privat untuk operasional **PT Dwi Ravi Ladang Energi**.
