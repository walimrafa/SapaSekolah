@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Kesalahan Sistem | {{ $appSettings['app_name'] }}</title>
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-950 border border-slate-800 rounded-3xl p-8 shadow-2xl text-center space-y-6">
        <div class="w-16 h-16 mx-auto rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-2xl font-black">
            500
        </div>
        <div class="space-y-2">
            <h1 class="text-xl font-black tracking-tight text-white">Terjadi Kesalahan Server</h1>
            <p class="text-xs text-slate-400 leading-relaxed">
                Sistem mendeteksi kendala saat memproses permintaan Anda. Tim administrator telah menerima laporan log ini.
            </p>
        </div>
        <div class="pt-2">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-lg transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>
</body>
</html>

