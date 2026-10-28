@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp

<header class="h-16 bg-white border-b border-slate-200/80 sticky top-0 z-20 shadow-sm flex items-center justify-between px-4 lg:px-8">
    <div class="flex items-center space-x-3">
        <!-- Sidebar Mobile Toggle -->
        <button @click="sidebarOpen = !sidebarOpen" class="p-2 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition duration-150">
            <i data-lucide="menu" class="w-5 h-5"></i>
        </button>

        <!-- Mobile Brand Title Logo -->
        <div class="flex items-center space-x-2 sm:hidden">
            @if(!empty($appSettings['app_logo_url']))
                <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-7 h-7 object-contain rounded-lg bg-white shadow-xs p-0.5 border border-slate-100">
            @else
                <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-7 h-7 object-contain rounded-lg bg-white shadow-xs p-0.5 border border-slate-100">
            @endif
            <span class="text-xs font-extrabold text-slate-900 tracking-tight">{{ $appSettings['app_name'] ?? 'SAPA Sekolah' }}</span>
        </div>

        <!-- Page Header Breadcrumb / Title -->
        <div class="hidden sm:block">
            <h2 class="text-base font-bold text-slate-800 tracking-tight">{{ $title ?? $appSettings['header_title'] }}</h2>
            <p class="text-[11px] text-slate-500 font-medium">{{ $appSettings['campus_name'] }} &bull; {{ date('l, d F Y') }}</p>
        </div>
    </div>

    <!-- Right Navbar Quick Actions & Notifications -->
    <div class="flex items-center space-x-3">
        <!-- Quick Action Button -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false"
                class="px-3.5 py-2 btn-theme-accent text-white font-bold rounded-xl text-xs shadow-sm flex items-center space-x-1.5 transition duration-150">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span class="hidden sm:inline">Quick Action</span>
                <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
            </button>

            <!-- Dropdown Menu -->
            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                <div class="px-3 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aksi Cepat Marketing</div>
                <a href="{{ route('schools.index') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-brand-500"></i>
                    <span>Tambah Sekolah Baru</span>
                </a>
                <a href="{{ route('visit-plans.create') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">
                    <i data-lucide="calendar-plus" class="w-4 h-4 text-emerald-500"></i>
                    <span>Buat Rencana Kunjungan</span>
                </a>
                <a href="{{ route('visits.create') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">
                    <i data-lucide="play-circle" class="w-4 h-4 text-amber-500"></i>
                    <span>Catat Hasil Kunjungan Lapangan</span>
                </a>
                <a href="{{ route('follow-ups.index') }}" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-brand-600">
                    <i data-lucide="phone-outgoing" class="w-4 h-4 text-purple-500"></i>
                    <span>Tambah Follow Up</span>
                </a>
                <div class="my-1 border-t border-slate-100"></div>
                <a href="{{ route('public-visit.index') }}" target="_blank" class="flex items-center space-x-2.5 px-3 py-2 text-xs font-bold text-amber-700 hover:bg-amber-50">
                    <i data-lucide="qr-code" class="w-4 h-4 text-amber-500"></i>
                    <span>QR Code Portal Input Lapangan ↗</span>
                </a>
            </div>
        </div>

        <!-- Notification Bell Center -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 relative transition duration-150">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <span class="absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-amber-500 rounded-full ring-2 ring-white"></span>
            </button>

            <!-- Notifications Panel -->
            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 py-3 z-50">
                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-800">Notifikasi System</span>
                    <span class="text-[10px] font-semibold text-brand-600 bg-brand-50 px-2 py-0.5 rounded-full">3 Baru</span>
                </div>
                <div class="divide-y divide-slate-50 max-h-64 overflow-y-auto">
                    <div class="p-3 hover:bg-slate-50 transition cursor-pointer">
                        <p class="text-xs font-semibold text-slate-800">Follow-up Terlambat</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">SMAN 1 Ciampel jatuh tempo hari ini.</p>
                        <span class="text-[9px] text-slate-400 mt-1 block">10 menit lalu</span>
                    </div>
                    <div class="p-3 hover:bg-slate-50 transition cursor-pointer">
                        <p class="text-xs font-semibold text-slate-800">Rencana Kunjungan Baru</p>
                        <p class="text-[11px] text-slate-500 mt-0.5">Budi Santoso membuat kunjungan ke SMKS Texmaco.</p>
                        <span class="text-[9px] text-slate-400 mt-1 block">1 jam lalu</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false" class="flex items-center space-x-2.5 p-1 rounded-xl hover:bg-slate-100 transition duration-150">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-brand- navy to-brand-600 text-white font-bold text-xs flex items-center justify-center shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="hidden md:block text-left">
                    <span class="text-xs font-bold text-slate-800 block leading-tight">{{ auth()->user()->name }}</span>
                    <span class="text-[10px] text-slate-500 font-medium block leading-tight">{{ auth()->user()->role ? auth()->user()->role->name : 'Marketing' }}</span>
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 hidden md:block"></i>
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                <div class="px-4 py-2 border-b border-slate-100">
                    <p class="text-xs font-bold text-slate-800">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left flex items-center space-x-2 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 transition">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
