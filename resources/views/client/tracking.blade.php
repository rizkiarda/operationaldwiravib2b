<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Pengiriman Klien - {{ $order->order_code }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 antialiased pb-20">

    <!-- HEADER PORTAL KLIEN -->
    <header class="bg-slate-900 text-white shadow sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-sky-600 flex items-center justify-center font-bold text-white text-base shadow">
                    🏢
                </div>
                <div>
                    <h1 class="font-extrabold text-xs md:text-sm tracking-wide text-white uppercase">PORTAL MONITORING PENGIRIMAN KLIEN</h1>
                    <p class="text-[11px] text-slate-400">Dikelola oleh PT Dwi Ravi Ladang Energi</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white/10 text-sky-200 text-xs rounded-lg border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Live Read-Only
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 mt-5 space-y-4">

        <!-- KARTU UTAMA IDENTITAS ORDER -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
            <div class="flex flex-wrap items-center justify-between gap-2 pb-3.5 border-b border-slate-100">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kode Pesanan / Order</span>
                    <h2 class="text-base md:text-lg font-bold text-slate-900">{{ $order->order_code }}</h2>
                </div>
                <div class="text-right">
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">No. PO Klien</span>
                    <div class="text-xs font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200">
                        {{ $order->po_number_client }}
                    </div>
                </div>
            </div>

            <!-- STATUS GLOBAL ORDER -->
            <div class="mt-4 p-4 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase">Status Pengiriman Kargo</div>
                    <div class="text-base font-extrabold text-slate-900 mt-0.5">
                        @switch($order->status)
                            @case('CONFIRMED')
                                ⏳ Terkonfirmasi (Menunggu Alokasi Armada)
                                @break
                            @case('WO_DISPATCHED')
                                🚚 Armada Telah Diterbitkan (Persiapan Muat)
                                @break
                            @case('IN_TRANSIT')
                                🚛 Dalam Perjalanan Menuju Lokasi Bongkar
                                @break
                            @case('DELIVERED')
                                ✅ Kargo Telah Tiba / Selesai Bongkar
                                @break
                            @case('COMPLETED')
                                🏁 Pengiriman Selesai & Terverifikasi
                                @break
                            @default
                                Dalam Pemrosesan Sistem
                        @endswitch
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Klien: <strong>{{ $order->client->company_name }}</strong> | Kategori: <strong>{{ $order->cargo_category }}</strong>
                    </p>
                </div>
                <div class="w-11 h-11 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-base">
                    📦
                </div>
            </div>

            <!-- INFORMASI RUTE ASAL & TUJUAN -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-4 text-xs">
                <div class="p-3.5 rounded-xl border border-blue-100 bg-blue-50/30">
                    <div class="text-[11px] font-bold text-blue-700 uppercase mb-0.5">1. Lokasi Pemuatan (Origin)</div>
                    <div class="font-bold text-slate-900">{{ $order->origin_facility_name }}</div>
                    <p class="text-[11px] text-slate-600 mt-1">{{ $order->origin_address }}</p>
                    <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-blue-100">
                        Rencana Muat: <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($order->loading_plan_date)->format('d M Y, H:i') }} WIB</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border border-emerald-100 bg-emerald-50/30">
                    <div class="text-[11px] font-bold text-emerald-700 uppercase mb-0.5">2. Lokasi Pembongkaran (Destination)</div>
                    <div class="font-bold text-slate-900">{{ $order->dest_facility_name }}</div>
                    <p class="text-[11px] text-slate-600 mt-1">{{ $order->dest_address }}</p>
                    <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-emerald-100">
                        Estimasi Tiba (ETA): <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($order->unloading_plan_date)->format('d M Y, H:i') }} WIB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- DAFTAR ITEM BARANG DALAM ORDER INI -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
            <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span>📋</span> Rincian Muatan Kargo (Manifest Pesanan)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="text-[11px] text-slate-400 uppercase bg-slate-50 border-y border-slate-100">
                        <tr>
                            <th class="py-2.5 px-3">Deskripsi Barang</th>
                            <th class="py-2.5 px-3 text-center">Jumlah</th>
                            <th class="py-2.5 px-3 text-center">Satuan</th>
                            <th class="py-2.5 px-3">Catatan / Segel</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse($order->items as $item)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-slate-900">{{ $item->item_name }}</td>
                                <td class="py-2.5 px-3 text-center font-semibold">{{ number_format($item->quantity, 0) }}</td>
                                <td class="py-2.5 px-3 text-center uppercase text-blue-700 font-semibold">{{ $item->unit }}</td>
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

        <!-- DAFTAR ARMADA & PROGRESS PER-TRIP (READ ONLY 100%) -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
            <h3 class="font-bold text-xs text-slate-900 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                <span>🚛</span> Status Armada & Pengiriman Lapangan
            </h3>

            @forelse($order->workOrders as $wo)
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3 mb-3 last:mb-0">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 pb-2">
                        <div>
                            <span class="text-xs font-extrabold text-slate-900 tracking-wide">{{ $wo->vehicle_plate_no }}</span>
                            <span class="text-xs text-slate-500 ml-2">({{ $wo->driver_name }} - {{ $wo->driver_phone }})</span>
                        </div>
                        <div>
                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-lg 
                                @if($wo->status === 'POD_VERIFIED') bg-emerald-100 text-emerald-800
                                @elseif($wo->status === 'UNLOADED_WAITING_POD') bg-amber-100 text-amber-800
                                @elseif($wo->status === 'ON_THE_WAY') bg-blue-100 text-blue-800
                                @else bg-slate-200 text-slate-700 @endif">
                                {{ $wo->status }}
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs text-slate-600">
                        <div>
                            <span class="text-slate-400 block text-[10px]">No. Surat Jalan:</span>
                            <strong class="text-slate-800">{{ $wo->delivery_note_number }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Tipe Armada:</span>
                            <strong class="text-slate-800">{{ $wo->vehicleType->name ?? 'Truk Box' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px]">Dokumen Surat Jalan:</span>
                            <a href="{{ route('print.surat-jalan', $wo) }}" target="_blank" class="text-blue-600 font-bold hover:underline inline-flex items-center gap-1">
                                📄 Unduh PDF
                            </a>
                        </div>
                    </div>

                    <!-- JIKA SUDAH BONGKAR & ADA e-POD -->
                    @if($wo->deliveryProof)
                        <div class="pt-2 border-t border-slate-200 flex items-center justify-between text-xs">
                            <span class="text-emerald-700 font-semibold">
                                ✓ Diterima oleh: <strong>{{ $wo->deliveryProof->recipient_name }}</strong> ({{ \Carbon\Carbon::parse($wo->deliveryProof->received_at)->format('d/m/Y H:i') }})
                            </span>
                            @if($wo->deliveryProof->epod_image_url)
                                <a href="{{ asset($wo->deliveryProof->epod_image_url) }}" target="_blank" class="text-xs font-bold text-emerald-800 bg-emerald-100 px-2.5 py-1 rounded-md hover:bg-emerald-200">
                                    Lihat Surat Jalan Berstempel
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-6 text-xs text-slate-400">
                    Armada pengangkut sedang disiapkan oleh tim operasional PT Dwi Ravi Ladang Energi.
                </div>
            @endforelse
        </div>

    </main>

    <!-- FOOTER RESMI -->
    <footer class="text-center text-xs text-slate-400 mt-10">
        &copy; {{ date('Y') }} PT Dwi Ravi Ladang Energi &ndash; B2B Freight Orchestrator.<br>
        Halaman ini dibuat khusus untuk kemudahan pemantauan kargo Klien dan bersifat Read-Only.
    </footer>

</body>
</html>
