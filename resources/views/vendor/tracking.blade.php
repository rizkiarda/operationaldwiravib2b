<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Tracking & Portal Driver - {{ $wo->wo_number }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 antialiased pb-20">

    <!-- TOP HEADER -->
    <header class="bg-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center font-bold text-white text-base shadow">
                    🚚
                </div>
                <div>
                    <h1 class="font-extrabold text-xs md:text-sm tracking-wide text-white">LIVE SHIPMENT & DRIVER DESK</h1>
                    <p class="text-[11px] text-slate-400">PT Dwi Ravi Ladang Energi</p>
                </div>
            </div>
            <a href="{{ route('print.surat-jalan', $wo) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold rounded-lg shadow transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Surat Jalan (PDF)
            </a>
        </div>
    </header>

    <main class="max-w-3xl mx-auto px-4 mt-5 space-y-4">

        <!-- NOTIFIKASI SUKSES FLASH -->
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <span class="text-base">✅</span> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm text-rose-800 text-xs font-semibold">
                <ul>
                    @foreach($errors->all() as $err)
                        <li>⚠️ {{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- KARTU UTAMA WORK ORDER -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3.5 border-b border-slate-100">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">No. Work Order</span>
                    <h2 class="text-base md:text-lg font-bold text-slate-900">{{ $wo->wo_number }}</h2>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">No. Surat Jalan</span>
                    <div class="text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-200">
                        {{ $wo->delivery_note_number }}
                    </div>
                </div>
            </div>

            <!-- STATUS CARD -->
            <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase">Status Perjalanan Terkini</div>
                    <div class="text-base font-extrabold text-blue-900 mt-0.5">
                        {{ $milestones[$wo->status]['label'] ?? $wo->status }}
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ $milestones[$wo->status]['desc'] ?? 'Dalam pemantauan sistem' }}
                    </p>
                </div>
                <div class="w-11 h-11 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-base">
                    ✓
                </div>
            </div>

            <!-- DETAIL VENDOR & DRIVER -->
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4 text-xs">
                <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block mb-0.5 text-[11px]">Vendor Armada</span>
                    <span class="font-bold text-slate-800">{{ $wo->transporter->company_name }}</span>
                </div>
                <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100">
                    <span class="text-slate-400 block mb-0.5 text-[11px]">Nomor Polisi Truk</span>
                    <span class="font-extrabold text-slate-900 text-sm tracking-wide">{{ $wo->vehicle_plate_no }}</span>
                </div>
                <div class="bg-slate-50/70 p-3 rounded-xl border border-slate-100 col-span-2 sm:col-span-1">
                    <span class="text-slate-400 block mb-0.5 text-[11px]">Driver Pengemudi</span>
                    <span class="font-bold text-slate-800">{{ $wo->driver_name }}</span>
                    <span class="text-slate-500 block text-[11px]">{{ $wo->driver_phone }}</span>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- PANEL AKSI KHUSUS DRIVER LAPANGAN (HANYA MENAMPILKAN STATUS MAJU)  --}}
        {{-- ================================================================= --}}
        <div class="bg-gradient-to-br from-blue-900 to-indigo-950 rounded-2xl p-5 text-white shadow-md border border-blue-800">
            <div class="flex items-center gap-2 mb-3">
                <span class="p-1.5 bg-blue-500/30 rounded-lg text-lg">📱</span>
                <div>
                    <h3 class="font-extrabold text-sm tracking-wide text-white uppercase">Panel Aksi Driver Lapangan</h3>
                    <p class="text-[11px] text-blue-200">Perjalanan bergerak maju secara linier (tidak bisa memilih tahap yang sudah lewat)</p>
                </div>
            </div>

            @if(count($availableNextOptions) > 0)
                <!-- FORM 1: UPDATE MILESTONES PERJALANAN (HANYA STATUS BERIKUTNYA YANG MUNCUL) -->
                <form action="{{ route('vendor.tracking.update-status', $wo->qr_code_hash) }}" method="POST" class="space-y-3 bg-white/10 p-3.5 rounded-xl border border-white/10 backdrop-blur-sm">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-blue-200 mb-1">
                            Pilih Tahap Perjalanan Selanjutnya:
                        </label>
                        <select name="status" class="w-full bg-slate-900 text-white border border-blue-400/40 rounded-lg px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-blue-400 outline-none">
                            @foreach($availableNextOptions as $statusKey => $statusText)
                                <option value="{{ $statusKey }}">{{ $statusText }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <input type="text" name="notes" placeholder="Catatan posisi (Contoh: Masuk Tol / Tiba Gerbang Pabrik)" class="w-full bg-slate-900 text-white border border-blue-400/30 rounded-lg px-3 py-1.5 text-xs placeholder-slate-400 outline-none">
                    </div>
                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-500 font-bold text-xs rounded-lg transition shadow-md flex items-center justify-center gap-1.5">
                        <span>🚀</span> Konfirmasi Status Selanjutnya
                    </button>
                </form>
            @else
                <div class="bg-emerald-500/20 border border-emerald-400/40 p-3 rounded-xl text-xs text-emerald-200 font-semibold flex items-center gap-2">
                    <span>🏁</span> Seluruh tahapan perjalanan armada telah selesai!
                </div>
            @endif

            <!-- FORM 2: UPLOAD FOTO BUKTI SERAH TERIMA (e-POD) -->
            <div class="mt-4 pt-4 border-t border-white/15">
                <div class="text-xs font-bold text-blue-200 mb-2 flex items-center gap-1.5">
                    <span>📸</span> Upload Bukti Serah Terima Fisik (Surat Jalan Balik):
                </div>

                <form action="{{ route('vendor.tracking.upload-pod', $wo->qr_code_hash) }}" method="POST" enctype="multipart/form-data" class="space-y-3 bg-white/10 p-3.5 rounded-xl border border-white/10 backdrop-blur-sm">
                    @csrf
                    <div>
                        <label class="block text-[11px] text-slate-200 mb-1">Nama Terang Penerima Barang:</label>
                        <input type="text" name="recipient_name" required placeholder="Contoh: Pak Herman (Gudang Penerima)" class="w-full bg-slate-900 text-white border border-blue-400/40 rounded-lg px-3 py-1.5 text-xs outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-200 mb-1">Foto Surat Serah Terima / Surat Jalan Berstempel:</label>
                        <input type="file" name="epod_image" accept="image/*" capture="environment" required class="w-full bg-slate-900 text-xs text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:bg-blue-600 file:text-white rounded-lg border border-blue-400/40 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-200 mb-1">Foto Kondisi Kargo Selesai Dibongkar (Opsional):</label>
                        <input type="file" name="cargo_photo" accept="image/*" capture="environment" class="w-full bg-slate-900 text-xs text-slate-300 file:mr-2 file:py-1 file:px-2.5 file:rounded file:border-0 file:text-[11px] file:bg-slate-700 file:text-white rounded-lg border border-blue-400/40 cursor-pointer">
                    </div>
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 font-extrabold text-xs rounded-lg transition shadow-md flex items-center justify-center gap-1.5">
                        <span>📤</span> Kirim Bukti Serah Terima (e-POD)
                    </button>
                </form>
            </div>
        </div>

        <!-- RIWAYAT LOG AUDIT PERJALANAN (HISTORY TIMELINE LENGKAP) -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span>🕒</span> Riwayat Catatan Perjalanan (Audit History)
            </h3>

            @if($wo->trackingLogs->count() > 0)
                <div class="relative pl-5 space-y-3.5 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200 text-xs">
                    @foreach($wo->trackingLogs as $log)
                        <div class="relative">
                            <div class="absolute -left-5 top-1 w-2.5 h-2.5 rounded-full bg-blue-600 border-2 border-white"></div>
                            <div>
                                <div class="font-bold text-slate-800">{{ $milestones[$log->milestone]['label'] ?? $log->milestone }}</div>
                                <div class="text-[11px] text-slate-500">{{ $log->location_description }} &bull; <span class="font-medium text-slate-400">{{ \Carbon\Carbon::parse($log->logged_at)->format('d M Y, H:i') }} WIB</span></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-slate-400">Belum ada riwayat check-in perjalanan.</p>
            @endif
        </div>

        <!-- PROGRESS TIMELINE STEP-BY-STEP -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider mb-4 flex items-center gap-1.5">
                <span>📍</span> Progress Rantai Pasok Pengiriman
            </h3>

            <div class="relative pl-6 space-y-5 before:content-[''] before:absolute before:left-2 before:top-2 before:bottom-2 before:w-0.5 before:bg-slate-200">
                @foreach($milestones as $key => $m)
                    @php
                        $isPassed = $currentStep > $m['step'];
                        $isCurrent = $currentStep === $m['step'];
                    @endphp
                    <div class="relative">
                        <div class="absolute -left-6 top-1 w-4 h-4 rounded-full border-2 
                            {{ $isCurrent ? 'bg-blue-600 border-white ring-4 ring-blue-100' : ($isPassed ? 'bg-emerald-500 border-white' : 'bg-slate-200 border-white') }}">
                        </div>
                        <div>
                            <div class="text-xs font-bold {{ $isCurrent ? 'text-blue-700 font-extrabold' : ($isPassed ? 'text-slate-800' : 'text-slate-400') }}">
                                {{ $m['label'] }}
                                @if($isCurrent)
                                    <span class="ml-2 inline-block px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] rounded-full font-bold uppercase">Posisi Sekarang</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-500 mt-0.5">{{ $m['desc'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- RUTE PENGIRIMAN -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span>🛣️</span> Rute Pengiriman
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                <div class="p-3.5 rounded-xl border border-blue-100 bg-blue-50/40">
                    <div class="text-[11px] font-bold text-blue-700 uppercase mb-0.5">Titik Muat (Origin)</div>
                    <div class="font-bold text-slate-900">{{ $wo->order->origin_facility_name }}</div>
                    <p class="text-[11px] text-slate-600 mt-1">{{ $wo->order->origin_address }}</p>
                    <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-blue-100">
                        PIC: <span class="font-semibold text-slate-700">{{ $wo->order->origin_pic_name }}</span> ({{ $wo->order->origin_pic_phone }})
                    </div>
                </div>
                <div class="p-3.5 rounded-xl border border-emerald-100 bg-emerald-50/40">
                    <div class="text-[11px] font-bold text-emerald-700 uppercase mb-0.5">Titik Bongkar (Destination)</div>
                    <div class="font-bold text-slate-900">{{ $wo->order->dest_facility_name }}</div>
                    <p class="text-[11px] text-slate-600 mt-1">{{ $wo->order->dest_address }}</p>
                    <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-emerald-100">
                        PIC: <span class="font-semibold text-slate-700">{{ $wo->order->dest_pic_name }}</span> ({{ $wo->order->dest_pic_phone }})
                    </div>
                </div>
            </div>
        </div>

        <!-- DAFTAR ITEM MUATAN FISIK -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
            <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span>📦</span> Daftar Rincian Barang Muatan
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-[11px] text-slate-400 uppercase bg-slate-50 border-y border-slate-100">
                        <tr>
                            <th class="py-2.5 px-3">Nama Kargo</th>
                            <th class="py-2.5 px-3 text-center">Jumlah</th>
                            <th class="py-2.5 px-3 text-center">Satuan</th>
                            <th class="py-2.5 px-3">Catatan / Segel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($wo->order->items as $item)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ $item->item_name }}</td>
                                <td class="py-2.5 px-3 text-center">{{ number_format($item->quantity, 0) }}</td>
                                <td class="py-2.5 px-3 text-center uppercase font-semibold text-blue-700">{{ $item->unit }}</td>
                                <td class="py-2.5 px-3 text-slate-500">{{ $item->remarks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-3 text-center text-slate-400">Rincian muatan standar sesuai SPK.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- BUKTI e-POD YANG SUDAH TERUPLOAD -->
        @if($wo->deliveryProof)
            <div class="bg-emerald-50 rounded-2xl p-5 border border-emerald-200">
                <div class="flex items-center gap-2 text-emerald-800 font-bold text-sm mb-1.5">
                    <span>✅</span> Bukti Serah Terima Kargo (e-POD)
                </div>
                <div class="text-xs text-emerald-700">
                    Diterima oleh: <strong>{{ $wo->deliveryProof->recipient_name }}</strong> pada {{ \Carbon\Carbon::parse($wo->deliveryProof->received_at)->format('d M Y, H:i') }} WIB.
                </div>
                @if($wo->deliveryProof->epod_image_url)
                    <div class="mt-3 flex gap-2">
                        <a href="{{ asset($wo->deliveryProof->epod_image_url) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800 bg-white px-3 py-1.5 rounded-lg border border-emerald-300 shadow-sm hover:bg-emerald-50">
                            🔍 Lihat Foto Surat Jalan Balik
                        </a>
                        @if($wo->deliveryProof->cargo_unloaded_photo_url)
                            <a href="{{ asset($wo->deliveryProof->cargo_unloaded_photo_url) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-800 bg-white px-3 py-1.5 rounded-lg border border-emerald-300 shadow-sm hover:bg-emerald-50">
                                📦 Lihat Foto Fisik Kargo
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endif

    </main>

    <!-- FOOTER -->
    <footer class="text-center text-xs text-slate-400 mt-10">
        &copy; {{ date('Y') }} PT Dwi Ravi Ladang Energi &ndash; Sistem Logistik Terpadu.<br>
        Informasi kargo dilindungi oleh protokol kerahasiaan operasional B2B.
    </footer>

</body>
</html>
