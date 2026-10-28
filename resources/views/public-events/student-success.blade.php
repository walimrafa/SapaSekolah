@php
    $appSettings = \App\Services\AppSettingService::all();
    $studentName = session('student_name', 'Siswa / Calon Mahasiswa');
    $isScholarship = session('scholarship_interest') === 'ya';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Berhasil - {{ $appSettings['app_name'] }}</title>

    <!-- Favicon / Logo Title SAPA Sekolah -->
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-950 min-h-screen py-12 px-4 sm:px-6 relative overflow-x-hidden flex items-center justify-center">

    <div class="fixed top-0 left-1/3 w-96 h-96 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/3 w-96 h-96 bg-brand-500/15 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-md w-full space-y-6 relative z-10 text-center">
        <!-- Success Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-8 sm:p-10 shadow-2xl text-slate-200 relative overflow-hidden">
            <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-slate-950 flex items-center justify-center mx-auto shadow-xl shadow-emerald-500/20 mb-6 animate-bounce">
                <i data-lucide="check" class="w-10 h-10 stroke-[3]"></i>
            </div>

            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 mb-3">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 mr-1 text-emerald-400"></i> Data Berhasil Diterima
            </span>

            <h1 class="text-2xl font-black text-white tracking-tight">
                Terima Kasih, {{ $studentName }}!
            </h1>
            <p class="text-sm text-slate-300 mt-2 leading-relaxed">
                Formulir pendaftaran Anda telah tersimpan. Tim Admisi {{ $appSettings['campus_name'] ?? 'Universitas BSI' }} akan segera menghubungi Anda melalui WhatsApp untuk konsultasi perkuliahan
                @if($isScholarship)
                    dan panduan <strong class="text-amber-300">Program Beasiswa UBSI</strong>.
                @else
                    dan rincian biaya kuliah.
                @endif
            </p>

            <div class="mt-8 pt-6 border-t border-slate-800 flex flex-col gap-3">
                <a href="{{ route('event-students.create') }}"
                    class="inline-flex items-center justify-center space-x-2 px-6 py-3.5 rounded-2xl text-sm font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-lg shadow-brand-600/25 transition">
                    <i data-lucide="user-plus" class="w-4 h-4"></i>
                    <span>Isi Formulir Siswa Lainnya</span>
                </a>

                <a href="https://pmbubsi.id/pmb" target="_blank"
                    class="inline-flex items-center justify-center space-x-2 px-5 py-3 rounded-2xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 transition">
                    <i data-lucide="globe" class="w-4 h-4 text-amber-400"></i>
                    <span>Kunjungi Portal PMB UBSI</span>
                </a>
            </div>
        </div>

        <div class="text-xs text-slate-500">
            © {{ date('Y') }} {{ $appSettings['app_name'] }} • {{ $appSettings['campus_name'] ?? 'Universitas Bina Sarana Informatika' }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
