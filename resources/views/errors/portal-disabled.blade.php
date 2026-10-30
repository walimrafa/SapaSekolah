@php
    $appSettings = \App\Services\AppSettingService::all();
    $campus = \App\Services\AppContextService::getActiveCampus();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Dinonaktifkan Sementara - {{ $appSettings['app_name'] }}</title>
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-950 border border-slate-800 rounded-3xl p-8 shadow-2xl text-center space-y-6 relative overflow-hidden">
        <!-- Accent Glow -->
        <div class="absolute -top-24 -left-24 w-48 h-48 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -right-24 w-48 h-48 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <!-- Logo -->
        <div class="flex justify-center">
            @if(!empty($appSettings['app_logo_url']))
                <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-16 h-16 object-contain rounded-2xl bg-white p-2 shadow-lg">
            @else
                <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-16 h-16 object-contain rounded-2xl bg-white p-2 shadow-lg">
            @endif
        </div>

        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-400/15 text-amber-300 border border-amber-400/30">
                <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                Status: Portal Nonaktif
            </span>
            <h1 class="text-xl font-black tracking-tight text-white pt-2">{{ $portalName ?? 'Portal Layanan' }}</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Mohon maaf, pengisian atau akses ke halaman formulir ini sedang <strong class="text-amber-400">dinonaktifkan sementara</strong> oleh Administrator / Tim Marketing Kampus.
            </p>
        </div>

        <div class="p-4 rounded-2xl bg-slate-900/80 border border-slate-800/80 text-left text-xs space-y-2 text-slate-300">
            <p class="font-semibold text-white flex items-center gap-2">
                <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Informasi & Bantuan:
            </p>
            <p class="text-slate-400">
                Jika Anda memiliki keperluan mendesak atau ingin mengajukan kegiatan/kunjungan, silakan menghubungi narahubung resmi kami:
            </p>
            <div class="pt-2 border-t border-slate-800 space-y-1 text-[11px]">
                <p><strong class="text-slate-200">Kampus:</strong> {{ $campus->name ?? $appSettings['campus_name'] }}</p>
                @if(!empty($campus->phone) || !empty($appSettings['campus_phone']))
                    <p><strong class="text-slate-200">Telepon / WhatsApp:</strong> {{ $campus->phone ?? $appSettings['campus_phone'] }}</p>
                @endif
                @if(!empty($campus->email) || !empty($appSettings['campus_email']))
                    <p><strong class="text-slate-200">Email:</strong> {{ $campus->email ?? $appSettings['campus_email'] }}</p>
                @endif
            </div>
        </div>

        <div class="pt-2">
            <a href="https://bsi.today" target="_blank" class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-gradient-to-r from-brand-600 to-amber-600 hover:from-brand-500 hover:to-amber-500 text-white font-bold text-xs shadow-lg transition">
                <span>Kunjungi Website Resmi Kampus</span>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>

        <p class="text-[11px] text-slate-500">
            &copy; {{ date('Y') }} {{ $appSettings['app_name'] }} &bull; Marketing Command Center
        </p>
    </div>
</body>
</html>

