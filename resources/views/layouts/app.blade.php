@php
    $appSettings = \App\Services\AppSettingService::all();
    $themePrimary = $appSettings['theme_color_primary'] ?? '#0B023E';
    $themeAccent = $appSettings['theme_color_accent'] ?? '#DF0B2B';
    $themeSecondary = $appSettings['theme_color_secondary'] ?? '#1A1A1A';
    $themeBg = $appSettings['theme_color_bg'] ?? '#F2F2F2';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - {{ $appSettings['app_name'] }} {{ $appSettings['app_subtitle'] }}</title>

    <!-- Favicon / Logo Title SAPA Sekolah -->
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Dynamic Theme Styling Variables -->
    <style>
        :root {
            --color-primary: {{ $themePrimary }};
            --color-accent: {{ $themeAccent }};
            --color-secondary: {{ $themeSecondary }};
            --color-bg: {{ $themeBg }};
        }
        body {
            background-color: var(--color-bg) !important;
        }
        /* Custom Elegant Sidebar themed with Visionary Navy */
        aside.theme-sidebar {
            background: linear-gradient(180deg, var(--color-primary) 0%, #060124 100%) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .theme-sidebar-header {
            background: rgba(11, 2, 62, 0.75) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .theme-sidebar-footer {
            background: rgba(6, 1, 36, 0.85) !important;
            border-top: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        /* Active Sidebar items with Progressive Red */
        .sidebar-active-gradient {
            background: linear-gradient(135deg, var(--color-accent) 0%, #a80820 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 14px -2px rgba(223, 11, 43, 0.45) !important;
        }
        /* Primary CTA buttons */
        .btn-theme-accent {
            background: linear-gradient(135deg, var(--color-accent) 0%, #b80923 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px -2px rgba(223, 11, 43, 0.35) !important;
        }
        .btn-theme-accent:hover {
            opacity: 0.92;
        }
        .btn-theme-navy {
            background-color: var(--color-primary) !important;
            color: #ffffff !important;
        }
        .btn-theme-navy:hover {
            opacity: 0.92;
        }
        /* Data table headers with Dark Charcoal */
        table thead tr {
            background-color: var(--color-secondary) !important;
        }
        /* Accent elements */
        .text-theme-accent {
            color: var(--color-accent) !important;
        }
        .border-theme-accent {
            border-color: var(--color-accent) !important;
        }
        .badge-theme-accent {
            background-color: rgba(223, 11, 43, 0.12) !important;
            color: var(--color-accent) !important;
            border: 1px solid rgba(223, 11, 43, 0.3) !important;
        }
        /* Overrides for common brand utility classes */
        .bg-brand-navy {
            background-color: var(--color-primary) !important;
        }
        .bg-brand-600 {
            background-color: var(--color-accent) !important;
        }
        .text-brand-600 {
            color: var(--color-accent) !important;
        }
        .border-brand-500 {
            border-color: var(--color-accent) !important;
        }
        .shadow-brand-600\/30 {
            box-shadow: 0 4px 14px -2px rgba(223, 11, 43, 0.35) !important;
        }
    </style>

    @stack('styles')
</head>
<body class="font-sans antialiased text-slate-800 min-h-screen" style="background-color: var(--color-bg);" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex">
        <!-- Sidebar Navigation -->
        <x-sidebar />

        <!-- Overlay backdrop for mobile sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-20 bg-slate-900/60 backdrop-blur-sm lg:hidden" style="display: none;"></div>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 lg:pl-64">
            <!-- Top Navbar -->
            <x-navbar :title="$title ?? 'Dashboard'" />

            @auth
                @php
                    $pendingPeriodApproval = \App\Services\PeriodClosingService::pendingForUser(auth()->user());
                @endphp
                @if($pendingPeriodApproval)
                    <div class="bg-gradient-to-r from-amber-500 via-rose-500 to-red-600 text-white px-4 lg:px-8 py-2.5 shadow-md flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                        <div class="flex items-center space-x-2.5">
                            <span class="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm shrink-0">
                                <i data-lucide="alert-octagon" class="w-4 h-4 text-white"></i>
                            </span>
                            <div>
                                <span class="font-extrabold tracking-wide uppercase text-[10px] bg-white/25 px-2 py-0.5 rounded-full mr-1.5">Persetujuan Diperlukan</span>
                                <span class="font-bold">Permohonan Tutup Periode Pemasaran &amp; PMB.</span>
                                <span class="opacity-95 hidden md:inline">Membutuhkan persetujuan Anda sebagai <strong>{{ auth()->user()->role->name ?? 'Approver' }}</strong>.</span>
                            </div>
                        </div>
                        <a href="{{ route('period-closing.index') }}" class="px-3.5 py-1.5 bg-white text-slate-900 font-extrabold text-xs rounded-xl hover:bg-slate-100 shadow transition flex items-center space-x-1.5 shrink-0">
                            <span>Tinjau &amp; Berikan Keputusan</span>
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                @endif
            @endauth

            <!-- Main Content Body -->
            <main class="flex-1 p-4 lg:p-8 overflow-y-auto">
                {{ $slot }}
            </main>

            <!-- Compact Footer -->
            <footer class="bg-white border-t border-slate-200/80 px-4 lg:px-8 py-3 text-xs text-slate-500 flex items-center justify-between">
                <span>{{ $appSettings['footer_text'] }}</span>
                <span class="text-slate-400 font-semibold">{{ $appSettings['footer_subtext'] }}</span>
            </footer>
        </div>
    </div>

    <!-- Toast Component -->
    <x-toast />

    @stack('scripts')
</body>
</html>
