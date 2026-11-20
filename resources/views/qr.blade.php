<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akses Mobile QR Hub - PT Dwi Ravi Ladang Energi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen py-10 px-4">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-semibold tracking-wide uppercase mb-3">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                LAN Hotspot Server Active
            </div>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
                Scan QR Code Akses Ponsel
            </h1>
            <p class="text-slate-400 mt-2 text-sm sm:text-base max-w-xl mx-auto">
                Arahkan kamera HP Anda ke QR Code di bawah untuk langsung membuka portal lapangan, tracking kargo, atau panel operasional tanpa mengetik URL.
            </p>
            <div class="mt-3 inline-block bg-slate-800/80 px-4 py-1.5 rounded-lg border border-slate-700/60 text-xs text-slate-300">
                Alamat IP Host Komputer: <code class="text-emerald-400 font-mono font-bold">{{ $ip }}:8000</code>
            </div>
        </div>

        <!-- Grid QR Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach ($links as $item)
                <div class="bg-slate-800/60 rounded-2xl p-6 border border-slate-700/60 shadow-xl backdrop-blur-sm flex flex-col items-center text-center hover:border-slate-600 transition duration-200">
                    <div class="w-full flex items-center justify-between mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-slate-700 text-slate-200">
                            {{ $item['badge'] }}
                        </span>
                        <span class="text-xs text-slate-400 font-mono truncate max-w-[200px]">
                            {{ $item['url'] }}
                        </span>
                    </div>

                    <!-- QR Code Container -->
                    <div class="bg-white p-4 rounded-xl shadow-inner mb-4 flex items-center justify-center">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(175)->margin(1)->generate($item['url']) !!}
                    </div>

                    <h3 class="text-lg font-bold text-white mb-1">
                        {{ $item['title'] }}
                    </h3>
                    <p class="text-xs text-slate-400 mb-4 flex-grow">
                        {{ $item['desc'] }}
                    </p>

                    <a href="{{ $item['url'] }}" target="_blank" class="w-full py-2.5 px-4 rounded-xl bg-slate-700/80 hover:bg-slate-700 text-white text-xs font-semibold flex items-center justify-center gap-2 transition">
                        <span>Buka Tautan Langsung</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Footer Notice -->
        <div class="mt-10 text-center text-xs text-slate-500">
            Pastikan ponsel Anda terhubung ke jaringan Wi-Fi yang sama dengan komputer ini.<br>
            PT Dwi Ravi Ladang Energi &bull; Natuna Logistics Orchestrator Platform
        </div>
    </div>
</body>
</html>
