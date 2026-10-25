<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan & Pengantar - {{ $wo->delivery_note_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            line-height: 1.25;
            color: #1e293b;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }

        /* HEADER & KOP PERUSAHAAN */
        .kop-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .kop-table td {
            vertical-align: middle;
        }
        .company-name {
            font-size: 14pt;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
            line-height: 1.1;
        }
        .company-meta {
            font-size: 7.2pt;
            color: #475569;
            margin-top: 3px;
            line-height: 1.3;
        }
        .doc-title-block {
            text-align: right;
        }
        .doc-main-title {
            font-size: 11pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0;
        }
        .doc-number-highlight {
            font-size: 10pt;
            font-weight: 800;
            color: #0284c7;
            margin-top: 2px;
        }
        .badge-pill {
            display: inline-block;
            font-size: 6.8pt;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 3px;
        }
        .badge-spp { background-color: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }
        .badge-qc  { background-color: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8; }

        /* LEGAL NOTICE BANNER */
        .system-banner {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 3.5px solid #0284c7;
            padding: 5px 8px;
            font-size: 7pt;
            color: #334155;
            margin-bottom: 8px;
            border-radius: 2px;
        }
        .system-banner strong {
            color: #0f172a;
        }

        /* CARD GRIDS */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7px;
        }
        .grid-cell {
            vertical-align: top;
            padding: 2px;
        }
        .info-card {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            border-radius: 4px;
            padding: 6px 8px;
            min-height: 72px;
        }
        .card-header-label {
            font-size: 6.8pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 2px;
            margin-bottom: 4px;
        }
        .card-body-text {
            font-size: 7.6pt;
            line-height: 1.35;
        }

        /* ITEMS TABLE */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .items-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 7.2pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 5px 6px;
            border: 1px solid #0f172a;
        }
        .items-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 6px;
            font-size: 7.8pt;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        /* SEAL BOX & FOOTER */
        .bottom-section-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .system-seal-box {
            border: 1px dashed #0284c7;
            background-color: #f0f9ff;
            border-radius: 4px;
            padding: 6px 10px;
        }
        .seal-title {
            font-size: 8pt;
            font-weight: 800;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .seal-text {
            font-size: 6.8pt;
            color: #334155;
            line-height: 1.3;
            margin-top: 2px;
        }

        /* QC TABLE SPECIFIC */
        .qc-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .qc-table th {
            background-color: #be185d;
            color: #ffffff;
            font-size: 7.2pt;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px 6px;
            border: 1px solid #be185d;
            text-align: left;
        }
        .qc-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 7px;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .qc-box-verdict {
            border: 1.5px solid #be185d;
            background-color: #fff1f2;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 8px;
        }
    </style>
</head>
<body>

@php
    $order = $wo->order;
    $client = $order->client;
    $transporter = $wo->transporter;
    $vehicle = $wo->vehicleType ?? $order->vehicleType;

    $categories = [
        'GENERAL_CARGO' => ['title' => 'General Cargo (Muatan Umum)', 'sop' => 'Strapping standar & terpal anti air tertutup rapat'],
        'FMCG_DRY' => ['title' => 'FMCG / Dry Food (Konsumsi Ritel)', 'sop' => 'Wajib beralas palet bersih, higienis, bebas bau & hama'],
        'FRAGILE_ELECTRONICS' => ['title' => 'Barang Pecah Belah & Elektronik Presisi', 'sop' => 'Maksimal tumpukan 3 koli, handle with care, ganjal busa/dunnage'],
        'COLD_CHAIN' => ['title' => 'Cold Chain (Suhu Terkontrol / Beku)', 'sop' => 'Suhu reefer box wajib stabil pada kisaran -18°C s/d -22°C sepanjang rute'],
        'HEAVY_INDUSTRIAL' => ['title' => 'Heavy Industrial (Baja, Besi & Mesin Berat)', 'sop' => 'Rantai lashing K3, balok pengganjal dunnage kayu keras bersertifikat'],
        'HAZMAT_DANGEROUS' => ['title' => 'Bahan Kimia / Dangerous Goods (Hazmat)', 'sop' => 'Wajib lampiran MSDS, simbol hazard terpasang, APAR aktif di kabin'],
    ];

    $cargoMeta = $categories[$order->cargo_category] ?? [
        'title' => 'Muatan Umum Terdaftar',
        'sop' => 'Standar Keamanan Transportasi Jalan Raya'
    ];
@endphp

{{-- ========================================================================= --}}
{{-- HALAMAN 1: SURAT JALAN & SURAT PENGANTAR PENGIRIMAN (CLEAN B2B VIEW)       --}}
{{-- ========================================================================= --}}
<table class="kop-table">
    <tr>
        <td style="width: 58%;">
            <div class="company-name">PT DWI RAVI LADANG ENERGI</div>
            <div class="company-meta">
                Jl. Ali Murtopo – Cemaka, Kelurahan Ranai Kota, Kecamatan Bunguran Timur,<br>
                Kabupaten Natuna, Provinsi Kepulauan Riau.<br>
                Hotline Operasional & Dispatch: (0777) 555-890 | Email: operasional@dwiravi.co.id
            </div>
        </td>
        <td style="width: 42%;" class="doc-title-block">
            <div class="doc-main-title">SURAT JALAN & SURAT PENGANTAR</div>
            <div class="doc-number-highlight">{{ $wo->delivery_note_number }}</div>
            <div class="badge-pill badge-spp">Surat Pengantar Pemegang MoU Utama</div>
        </td>
    </tr>
</table>

{{-- KETERANGAN RESMI: DIBUAT SISTEM & BEBAS TTD --}}
<div class="system-banner">
    <strong>KETERANGAN RESMI SISTEM OTOMASI LOGISTIK:</strong><br>
    Dokumen ini diterbitkan secara otomatis oleh sistem atas kuasa <strong>PT Dwi Ravi Ladang Energi</strong> selaku pemegang MoU utama. Dokumen ini <strong>TIDAK MEMERLUKAN TANDA TANGAN BASAH</strong> pada lembar ini karena legalitas fisik serah terima barang dilakukan terpisah pada <strong>Surat Serah Terima Barang / e-POD Resmi dari Pihak Vendor Client</strong>.
</div>

{{-- INFORMASI 3 PIHAK TERKAIT --}}
<table class="grid-table">
    <tr>
        {{-- Pihak 1: Klien Pemegang MoU --}}
        <td style="width: 33.3%;" class="grid-cell">
            <div class="info-card" style="border-left: 3px solid #0f172a;">
                <div class="card-header-label" style="color: #0f172a;">1. Pihak Utama Pemegang MoU</div>
                <div class="card-body-text">
                    <strong style="color: #0f172a;">{{ $client->company_name }}</strong><br>
                    <span style="color: #64748b;">No. PO:</span> <strong>{{ $order->po_number_client }}</strong><br>
                    <span style="color: #64748b;">No. SPP:</span> {{ $order->dispatch_note_number ?? 'SPP-AUTO' }}<br>
                    <span style="color: #64748b;">No. WO:</span> {{ $wo->wo_number }}
                </div>
            </div>
        </td>

        {{-- Pihak 2: Struktur Kontrak --}}
        <td style="width: 33.3%;" class="grid-cell">
            <div class="info-card" style="border-left: 3px solid #0284c7;">
                <div class="card-header-label" style="color: #0284c7;">2. Struktur Pihak Muatan</div>
                <div class="card-body-text">
                    @if($order->client_contract_role === 'SECOND_PARTY_SUB')
                        <span style="color: #b45309; font-weight: bold;">[Vendor Client Pihak Kedua]</span><br>
                        <strong>{{ $order->vendor_client_name ?? 'Vendor Mitra Ditunjuk' }}</strong><br>
                        <span style="color: #64748b;">Fungsi:</span> Penyerah Barang Fisik
                    @else
                        <span style="color: #0f172a; font-weight: bold;">[Pihak Pertama / Principal]</span><br>
                        <strong>{{ $client->company_name }}</strong><br>
                        <span style="color: #64748b;">Fungsi:</span> Pemilik Kargo Langsung
                    @endif
                    <div style="font-size: 6.8pt; color: #64748b; margin-top: 2px;">Bukti: Surat Serah Terima Vendor</div>
                </div>
            </div>
        </td>

        {{-- Pihak 3: Transporter --}}
        <td style="width: 33.4%;" class="grid-cell">
            <div class="info-card" style="border-left: 3px solid #16a34a;">
                <div class="card-header-label" style="color: #16a34a;">3. Ekspedisi & Driver Pengangkut</div>
                <div class="card-body-text">
                    <strong style="color: #0f172a;">{{ $transporter->company_name }}</strong><br>
                    <span style="color: #64748b;">Nopol:</span> <strong style="color: #0f172a; font-size: 8.5pt;">{{ $wo->vehicle_plate_no }}</strong><br>
                    <span style="color: #64748b;">Driver:</span> {{ $wo->driver_name }} ({{ $wo->driver_phone }})<br>
                    <span style="color: #64748b;">Unit:</span> {{ $vehicle->name ?? 'Armada Angkut' }}
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- RUTE MUAT & BONGKAR --}}
<table class="grid-table">
    <tr>
        <td style="width: 50%;" class="grid-cell">
            <div class="info-card">
                <div class="card-header-label" style="color: #0369a1;">Titik Asal Pemuatan (Origin)</div>
                <div class="card-body-text">
                    <strong>{{ $order->origin_facility_name }}</strong><br>
                    <span style="color: #475569;">Alamat:</span> {{ $order->origin_address }}<br>
                    <span style="color: #475569;">PIC Lapangan:</span> {{ $order->origin_pic_name }} ({{ $order->origin_pic_phone }})<br>
                    <span style="color: #475569;">Rencana Muat:</span> {{ \Carbon\Carbon::parse($order->loading_plan_date)->format('d M Y, H:i') }} WIB
                </div>
            </div>
        </td>
        <td style="width: 50%;" class="grid-cell">
            <div class="info-card">
                <div class="card-header-label" style="color: #15803d;">Titik Tujuan Pembongkaran (Destination)</div>
                <div class="card-body-text">
                    <strong>{{ $order->dest_facility_name }}</strong><br>
                    <span style="color: #475569;">Alamat:</span> {{ $order->dest_address }}<br>
                    <span style="color: #475569;">PIC Penerima:</span> {{ $order->dest_pic_name }} ({{ $order->dest_pic_phone }})<br>
                    <span style="color: #475569;">Estimasi Tiba (ETA):</span> {{ \Carbon\Carbon::parse($order->unloading_plan_date)->format('d M Y, H:i') }} WIB
                </div>
            </div>
        </td>
    </tr>
</table>

{{-- KATEGORI BARANG & SOP --}}
<div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 8px; margin-bottom: 7px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 40%; vertical-align: top;">
                <span style="color: #64748b; font-size: 6.8pt; text-transform: uppercase;">Jenis Kategori Barang:</span><br>
                <strong style="color: #0f172a; font-size: 8pt;">{{ $cargoMeta['title'] }}</strong>
            </td>
            <td style="width: 60%; vertical-align: top;">
                <span style="color: #64748b; font-size: 6.8pt; text-transform: uppercase;">SOP Penanganan Khusus Armada:</span><br>
                <span style="color: #334155; font-size: 7.4pt;">{{ $cargoMeta['sop'] }} | {{ $order->special_instructions ?? 'Standar Keselamatan Transportasi Darat' }}</span>
            </td>
        </tr>
    </table>
</div>

{{-- TABEL RINCIAN MUATAN BARANG --}}
<table class="items-table">
    <thead>
        <tr>
            <th style="width: 5%;" class="text-center">No</th>
            <th style="width: 45%;">Deskripsi Item Kargo Barang</th>
            <th style="width: 14%;" class="text-center">Jumlah (Qty)</th>
            <th style="width: 14%;" class="text-center">Satuan</th>
            <th style="width: 22%;">No. Segel / Remarks</th>
        </tr>
    </thead>
    <tbody>
        @forelse($order->items as $idx => $item)
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td>
                    <strong>{{ $item->item_name }}</strong>
                    @if($item->weight_kg > 0)
                        <span style="font-size: 6.8pt; color: #64748b; display: block;">Estimasi Berat: {{ number_format($item->weight_kg, 0, ',', '.') }} Kg</span>
                    @endif
                </td>
                <td class="text-center"><strong>{{ number_format($item->quantity, 0, ',', '.') }}</strong></td>
                <td class="text-center uppercase">{{ $item->unit }}</td>
                <td>{{ $item->remarks ?? 'Segel Terpasang Sempurna' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center" style="padding: 12px; color: #94a3b8;">Item muatan terdaftar lengkap pada Surat Serah Terima Fisik dari Vendor Client.</td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- STEMPEL SISTEM & QR AUDIT --}}
<table class="bottom-section-table">
    <tr>
        <td style="width: 80%; vertical-align: top;">
            <div class="system-seal-box">
                <div class="seal-title">Pengesahan Otomatis Sistem Logistik Terpadu</div>
                <div class="seal-text">
                    1. Dokumen ini sah dan diterbitkan secara digital oleh sistem atas kuasa <strong>PT Dwi Ravi Ladang Energi</strong>.<br>
                    2. <strong>Bebas Tanda Tangan:</strong> Berdasarkan kesepakatan tata kelola B2B, tanda tangan serah terima dilakukan pada <strong>Surat Serah Terima Barang Fisik dari Pihak Vendor Client</strong>.<br>
                    3. QR Code di samping berfungsi untuk verifikasi keaslian surat, pengecekan posisi armada (live tracking), dan validasi e-POD.
                </div>
            </div>
        </td>
        <td style="width: 20%; text-align: center; vertical-align: middle;">
            @if(isset($qrCode))
                <img src="data:image/svg+xml;base64,{{ $qrCode }}" width="68" height="68" style="border: 1px solid #e2e8f0; padding: 2px; border-radius: 4px;">
                <div style="font-size: 6.2pt; color: #64748b; margin-top: 2px;">Scan Validasi / e-POD</div>
            @endif
        </td>
    </tr>
</table>

<div style="font-size: 6.2pt; color: #94a3b8; margin-top: 4px; text-align: right;">
    Hash Integritas: {{ $wo->qr_code_hash }} | Waktu Terbit Sistem: {{ now()->format('d/m/Y H:i:s') }} WIB
</div>

{{-- HALAMAN 2: LEMBAR CEK CACAT BARANG --}}
<div class="page-break"></div>

{{-- ========================================================================= --}}
{{-- HALAMAN 2: LEMBAR CEK KELAYAKAN & CACAT BARANG (DOUBLE CHECKING CLIENT)    --}}
{{-- ========================================================================= --}}
<table class="kop-table" style="border-bottom-color: #be185d;">
    <tr>
        <td style="width: 60%;">
            <div class="company-name" style="color: #be185d;">LEMBAR PEMERIKSAAN KELAYAKAN & CACAT BARANG</div>
            <div class="company-meta">
                <strong>Quality Assurance & Double Checking Protocol</strong><br>
                Protokol Verifikasi Fisik Kargo oleh Pihak Utama Pemegang MoU Client<br>
                PT Dwi Ravi Ladang Energi &ndash; Divisi Manajemen Kualitas Pengiriman
            </div>
        </td>
        <td style="width: 40%;" class="doc-title-block">
            <div class="doc-main-title" style="color: #be185d;">LEMBAR QC & VERIFIKASI</div>
            <div class="doc-number-highlight" style="color: #be185d; font-size: 9pt;">REF WO: {{ $wo->wo_number }}</div>
            <div class="badge-pill badge-qc">Verifikasi Fisik Kargo Lapangan</div>
        </td>
    </tr>
</table>

<div class="system-banner" style="border-left-color: #be185d;">
    <strong>PROTOKOL DOUBLE-CHECKING PIHAK UTAMA CLIENT:</strong><br>
    Lembar ini diisi oleh petugas Quality Inspector / Pengawas Penerimaan dari <strong>Pihak Utama Pemegang MoU Client</strong> di titik serah terima barang untuk memeriksa cacat tersembunyi, kondisi segel, kebocoran suhu, atau kerusakan kemasan yang luput dari surat serah terima biasa.
</div>

{{-- CHECKLIST PARAMETER QC --}}
<table class="qc-table">
    <thead>
        <tr>
            <th style="width: 28%;">Parameter Pemeriksaan</th>
            <th style="width: 28%;">Standar Kelayakan Kargo</th>
            <th style="width: 20%; text-align: center;">Hasil Cek Lapangan</th>
            <th style="width: 24%;">Catatan Fisik Pengawas</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>1. Kondisi Segel (Security Seal)</strong></td>
            <td>Nomor segel kontainer/box cocok dengan surat jalan & tidak putus</td>
            <td class="text-center">[ &nbsp; ] Sesuai &nbsp; [ &nbsp; ] Rusak/Putus</td>
            <td>No Segel Fisik: ..............................</td>
        </tr>
        <tr>
            <td><strong>2. Kondisi Kemasan Luar (Outer Box/Palet)</strong></td>
            <td>Karton tidak penyok, tidak basah terkena hujan, palet tidak patah</td>
            <td class="text-center">[ &nbsp; ] Utuh &nbsp; [ &nbsp; ] Penyok / Robek</td>
            <td>Jumlah koli cacat: ......... koli</td>
        </tr>
        <tr>
            <td><strong>3. Kontrol Suhu (Khusus Cold Chain)</strong></td>
            <td>Suhu display armada sesuai standar komoditas frozen/chilled</td>
            <td class="text-center">[ &nbsp; ] Memenuhi &nbsp; [ &nbsp; ] Suhu Naik</td>
            <td>Suhu aktual bongkar: .......... °C</td>
        </tr>
        <tr>
            <td><strong>4. Kebocoran / Bau Menyengat (Hazmat/Liquid)</strong></td>
            <td>Drum/IBC Tank bebas rembesan dan segel katup tidak bocor</td>
            <td class="text-center">[ &nbsp; ] Kering &nbsp; [ &nbsp; ] Ada Rembesan</td>
            <td>Tingkat kebocoran: ....................</td>
        </tr>
        <tr>
            <td><strong>5. Kesesuaian Kuantitas Fisik (Tally Sheet)</strong></td>
            <td>Kuantitas fisik cocok 100% dengan Surat Serah Terima Vendor</td>
            <td class="text-center">[ &nbsp; ] Cocok &nbsp; [ &nbsp; ] Ada Selisih</td>
            <td>Selisih: (+ / -) ......... koli</td>
        </tr>
    </tbody>
</table>

{{-- KESIMPULAN REKOMENDASI --}}
<div class="qc-box-verdict">
    <strong style="font-size: 7.8pt; color: #be185d;">KESIMPULAN HASIL DOUBLE CHECKING:</strong>
    <table style="width: 100%; margin-top: 4px; font-size: 7.5pt;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                [ &nbsp; ] <strong>DITERIMA LENGKAP & BAIK (FULL ACCEPTED)</strong><br>
                <span style="color: #64748b; font-size: 6.8pt;">Kargo sempurna, tidak ada cacat yang mempengaruhi nilai barang.</span>
            </td>
            <td style="width: 50%; vertical-align: top;">
                [ &nbsp; ] <strong>DITERIMA DENGAN CATATAN (ACCEPTED WITH CLAIM)</strong><br>
                <span style="color: #64748b; font-size: 6.8pt;">Terdapat cacat/selisih fisik, Berita Acara Kerusakan (BAST) wajib dilampirkan.</span>
            </td>
        </tr>
    </table>
</div>

{{-- PENGESAHAN VERIFIKASI DOUBLE CHECKING --}}
<table style="width: 100%; border-collapse: collapse; margin-top: 6px;">
    <tr>
        <td style="width: 50%; padding-right: 5px;">
            <div style="border: 1px solid #e2e8f0; border-radius: 4px; padding: 6px; text-align: center; background-color: #fafafa;">
                <div style="font-size: 7.2pt; font-weight: 700; color: #334155;">Pengemudi Armada (Driver)</div>
                <div style="font-size: 6.5pt; color: #64748b;">Menyaksikan pemeriksaan fisik kargo</div>
                <div style="margin-top: 36px; border-top: 1px dotted #94a3b8; padding-top: 3px; font-weight: bold; font-size: 7.6pt;">
                    ( {{ $wo->driver_name }} - {{ $wo->vehicle_plate_no }} )
                </div>
            </div>
        </td>
        <td style="width: 50%; padding-left: 5px;">
            <div style="border: 1.5px solid #be185d; border-radius: 4px; padding: 6px; text-align: center; background-color: #fff1f2;">
                <div style="font-size: 7.2pt; font-weight: 700; color: #be185d;">Quality Inspector / Pengawas Pihak Utama MoU</div>
                <div style="font-size: 6.5pt; color: #64748b;">Penetapan Kelayakan Kargo PT Dwi Ravi Ladang Energi</div>
                <div style="margin-top: 36px; border-top: 1px dotted #be185d; padding-top: 3px; font-weight: bold; font-size: 7.6pt; color: #be185d;">
                    ( .............................................................. )
                </div>
            </div>
        </td>
    </tr>
</table>

<div style="font-size: 6.2pt; color: #94a3b8; margin-top: 6px; text-align: right;">
    Protokol Audit Ekspedisi B2B | Dilindungi oleh Sistem Otomasi Terpadu PT Dwi Ravi Ladang Energi
</div>

</body>
</html>
