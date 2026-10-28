@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp
<x-guest-layout title="Login Marketing Command Center">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-12 bg-white">
        <!-- Left Side: Branding & Marketing Visuals -->
        <div class="lg:col-span-7 bg-gradient-to-br from-slate-950 via-slate-900 to-brand-navy text-white p-8 lg:p-16 flex flex-col justify-between relative overflow-hidden">
            @if(!empty($appSettings['campus_building_url']))
                <!-- Subtle High-End Campus Building Backdrop -->
                <div class="absolute inset-0 bg-cover bg-center opacity-30 mix-blend-luminosity scale-105 pointer-events-none transition-transform duration-1000" style="background-image: url('{{ $appSettings['campus_building_url'] }}')"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/80 to-slate-950/85 pointer-events-none"></div>
            @endif

            <!-- Background Map Pattern Effect -->
            <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:24px_24px] pointer-events-none"></div>

            <!-- Top Logo -->
            <div class="relative z-10 flex items-center space-x-3">
                @if(!empty($appSettings['app_logo_url']))
                    <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-white p-1.5 shadow-lg shadow-brand-500/20">
                @else
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-tr from-brand-500 to-amber-400 p-0.5 shadow-lg shadow-brand-500/20">
                        <div class="w-full h-full bg-slate-900 rounded-[10px] flex items-center justify-center">
                            <i data-lucide="map-pin" class="w-6 h-6 text-amber-400"></i>
                        </div>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight">{{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}</h1>
                    <p class="text-xs text-brand-300 font-medium tracking-wide uppercase">{{ $appSettings['app_subtitle'] ?? 'Sistem Informasi Mapping Sekolah' }}</p>
                </div>
            </div>

            <!-- Hero Section -->
            <div class="relative z-10 my-auto py-12">
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold mb-6">
                    <i data-lucide="sparkles" class="w-4 h-4"></i>
                    <span>Marketing Command Center</span>
                </div>
                <h2 class="text-3xl lg:text-5xl font-extrabold text-white leading-tight mb-6">
                    Kenali Sekolahnya,<br>
                    <span class="bg-gradient-to-r from-brand-300 via-sky-300 to-amber-300 bg-clip-text text-transparent">
                        Tentukan Prioritasnya,
                    </span><br>
                    Tingkatkan Konversinya.
                </h2>
                <p class="text-slate-300 text-base lg:text-lg max-w-xl leading-relaxed mb-6">
                    Platform GIS cerdas pemetaan sekolah SLTA sederajat di Kabupaten Karawang untuk perencana kunjungan, analisis potensi, tracking marketing, dan optimasi konversi PMB.
                </p>

                @if(!empty($appSettings['campus_building_url']))
                <!-- Showcase Campus Card -->
                <div class="mb-8 p-3.5 rounded-2xl bg-slate-900/70 backdrop-blur-md border border-slate-700/60 max-w-lg flex items-center gap-3.5 shadow-xl">
                    <img src="{{ $appSettings['campus_building_url'] }}" alt="Gedung Kampus" class="w-20 h-14 object-cover rounded-xl border border-white/20 shadow shrink-0">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5 mb-1">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-300">Gedung Kampus Acuan</span>
                        </div>
                        <h4 class="text-xs font-bold text-white truncate">{{ $appSettings['campus_name'] ?? 'UBSI Kampus Karawang' }}</h4>
                        <p class="text-[10px] text-slate-300 truncate mt-0.5">{{ $appSettings['campus_address'] ?? 'Karawang Barat' }}</p>
                    </div>
                </div>
                @endif

                <!-- Feature Highlights -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-slate-800 pt-8">
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center text-amber-400">
                            <i data-lucide="map" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Smart GIS Map</p>
                            <p class="text-xs text-slate-400">Leaflet OpenStreetMap</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center text-amber-400">
                            <i data-lucide="target" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Priority Scoring</p>
                            <p class="text-xs text-slate-400">Weighted Algorithm</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-9 h-9 rounded-lg bg-slate-800 flex items-center justify-center text-amber-400">
                            <i data-lucide="route" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">Route & Track</p>
                            <p class="text-xs text-slate-400">GPS & Follow Up</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer info -->
            <div class="relative z-10 text-xs text-slate-400 flex items-center justify-between border-t border-slate-800/80 pt-6">
                <p>&copy; {{ date('Y') }} SAPA Sekolah. Perguruan Tinggi Marketing Command Center.</p>
                <span class="text-slate-500">v1.0.0</span>
            </div>
        </div>

        <!-- Right Side: Login Form -->
        <div class="lg:col-span-5 p-8 lg:p-16 flex flex-col justify-center bg-white">
            <div class="max-w-md mx-auto w-full">
                <!-- Mobile Brand Header -->
                <div class="lg:hidden flex items-center space-x-3 mb-8 pb-4 border-b border-slate-100">
                    @if(!empty($appSettings['app_logo_url']))
                        <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-slate-900 p-1.5 shadow-md">
                    @else
                        <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-slate-900 p-1.5 shadow-md">
                    @endif
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">{{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}</h1>
                        <p class="text-xs text-brand-600 font-bold uppercase tracking-wider">{{ $appSettings['app_subtitle'] ?? 'Command Center' }}</p>
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl lg:text-3xl font-bold text-slate-900 mb-2">Selamat Datang 👋</h2>
                    <p class="text-slate-500 text-sm">Masuk dengan akun marketing Anda untuk mengakses Command Center.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        <div class="flex items-center space-x-2 mb-1">
                            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                            <span class="font-semibold">Login Gagal</span>
                        </div>
                        <ul class="list-disc list-inside space-y-1 text-xs text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">Email Address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="mail" class="w-5 h-5"></i>
                            </div>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm transition duration-150 placeholder-slate-400"
                                placeholder="nama@kampus.ac.id">
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <a href="#" class="text-xs font-semibold text-brand-600 hover:text-brand-700">Lupa password?</a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <i data-lucide="lock" class="w-5 h-5"></i>
                            </div>
                            <input type="password" name="password" id="password" required
                                class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800 text-sm transition duration-150 placeholder-slate-400"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500">
                            <span class="ml-2 text-sm text-slate-600">Ingat saya di perangkat ini</span>
                        </label>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 px-4 bg-gradient-to-r from-brand-navy to-brand-700 hover:from-brand-900 hover:to-brand-800 text-white font-bold rounded-xl shadow-lg shadow-brand-900/20 hover:shadow-brand-900/40 transition duration-200 flex items-center justify-center space-x-2">
                        <span>Masuk ke Command Center</span>
                        <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
