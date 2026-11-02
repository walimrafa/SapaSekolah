@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $isDashboard = in_array($currentRoute, ['dashboard', 'home']);
    $isVisitPlans = str_starts_with($currentRoute, 'visit-plans');
    $isVisitsCreate = ($currentRoute === 'visits.create');
    $isMap = str_starts_with($currentRoute, 'map') || str_starts_with($currentRoute, 'marketing-routes');
    $isQuestionnaire = str_starts_with($currentRoute, 'student-questionnaires') || str_starts_with($currentRoute, 'prospects') || str_starts_with($currentRoute, 'event-students');
@endphp

<div x-data="{ mobileMenuOpen: false }">
    <!-- PWA Bottom Navigation Bar (Khusus Smartphone / Layar Kecil, Strict Horizontal Row) -->
    <nav class="sm:hidden"
        style="position: fixed !important; bottom: 0 !important; left: 0 !important; right: 0 !important; width: 100% !important; z-index: 45 !important; background-color: rgba(10, 15, 30, 0.98) !important; -webkit-backdrop-filter: blur(16px) !important; backdrop-filter: blur(16px) !important; border-top: 1px solid rgba(255, 255, 255, 0.1) !important; box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.45) !important;">
        <div style="display: flex !important; flex-direction: row !important; flex-wrap: nowrap !important; align-items: center !important; justify-content: space-around !important; width: 100% !important; max-width: 480px !important; margin: 0 auto !important; height: 58px !important; padding: 0 4px !important;">
            
            <!-- 1. Dashboard Ringkas -->
            <a href="{{ route('dashboard') }}" 
                style="flex: 1 1 0% !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; text-decoration: none !important; min-width: 0 !important;"
                class="py-1 rounded-xl transition {{ $isDashboard ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 {{ $isDashboard ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                    @if($isDashboard)
                        <span class="absolute -top-1 -right-1 w-1.5 h-1.5 bg-amber-400 rounded-full animate-ping"></span>
                    @endif
                </div>
                <span style="font-size: 10px !important; line-height: 1 !important; margin-top: 3px !important; white-space: nowrap !important;">Dashboard</span>
            </a>

            <!-- 2. Jadwal Kunjungan Lapangan -->
            <a href="{{ route('visit-plans.index') }}" 
                style="flex: 1 1 0% !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; text-decoration: none !important; min-width: 0 !important;"
                class="py-1 rounded-xl transition {{ $isVisitPlans ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="calendar" class="w-5 h-5 {{ $isVisitPlans ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                </div>
                <span style="font-size: 10px !important; line-height: 1 !important; margin-top: 3px !important; white-space: nowrap !important;">Jadwal</span>
            </a>

            <!-- 3. TOMBOL UTAMA TENGAH: Catat Kunjungan Baru (Raised Floating Action Button) -->
            <div style="flex: 1 1 0% !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; position: relative !important; min-width: 0 !important;">
                <a href="{{ route('visits.create') }}" 
                    title="Catat Kunjungan Sekolah Baru"
                    style="margin-top: -18px !important; width: 42px !important; height: 42px !important; border-radius: 14px !important; display: flex !important; align-items: center !important; justify-content: center !important; box-shadow: 0 4px 15px rgba(223, 11, 43, 0.45) !important;"
                    class="bg-gradient-to-tr from-brand-600 to-rose-500 hover:from-brand-500 hover:to-rose-400 text-white border-2 border-slate-900 active:scale-95 transition-transform duration-150">
                    <i data-lucide="plus" class="w-5 h-5 stroke-[3]"></i>
                </a>
                <span style="font-size: 9px !important; font-weight: 800 !important; text-transform: uppercase !important; margin-top: 2px !important; line-height: 1 !important; white-space: nowrap !important;" class="text-rose-400">Kunjungan</span>
            </div>

            <!-- 4. Peta GIS & Navigasi Rute -->
            <a href="{{ route('map.index') }}" 
                style="flex: 1 1 0% !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; text-decoration: none !important; min-width: 0 !important;"
                class="py-1 rounded-xl transition {{ $isMap ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="map-pin" class="w-5 h-5 {{ $isMap ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                </div>
                <span style="font-size: 10px !important; line-height: 1 !important; margin-top: 3px !important; white-space: nowrap !important;">Peta GIS</span>
            </a>

            <!-- 5. Kuisioner & Menu Cepat Lapangan -->
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                style="flex: 1 1 0% !important; display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; background: transparent !important; border: none !important; cursor: pointer !important; min-width: 0 !important;"
                class="py-1 rounded-xl transition {{ $isQuestionnaire ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="grid" class="w-5 h-5 {{ $isQuestionnaire ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                </div>
                <span style="font-size: 10px !important; line-height: 1 !important; margin-top: 3px !important; white-space: nowrap !important;">Aksi Cepat</span>
            </button>

        </div>
    </nav>

    <!-- Bottom Sheet / Drawer Aksi Cepat Lapangan -->
    <div x-show="mobileMenuOpen" 
        style="display: none; position: fixed !important; left: 0 !important; right: 0 !important; bottom: 0 !important; z-index: 60 !important; max-height: 85vh !important; background-color: #020617 !important;"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-full"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-full"
        class="sm:hidden fixed inset-x-0 bottom-0 z-50 bg-slate-950 border-t border-slate-800 rounded-t-3xl shadow-2xl p-5 pb-8 max-h-[85vh] overflow-y-auto">
        
        <div class="w-12 h-1.5 bg-slate-700 rounded-full mx-auto mb-4 cursor-pointer" @click="mobileMenuOpen = false"></div>

        <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
            <div>
                <h3 class="text-sm font-black text-white flex items-center gap-1.5">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i>
                    <span>Menu Operasional Lapangan PWA</span>
                </h3>
                <p class="text-[11px] text-slate-400">Akses cepat tugas marketing via smartphone</p>
            </div>
            <button type="button" @click="mobileMenuOpen = false" class="p-1.5 rounded-lg bg-slate-800 text-slate-400 hover:text-white">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-2.5 text-left text-xs mb-4">
            <!-- 1. Input Kuisioner Siswa -->
            <a href="{{ route('student-questionnaires.index') }}" @click="mobileMenuOpen = false"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center">
                    <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">Kuisioner Siswa</span>
                    <span class="block text-[10px] text-slate-400">Input skor &amp; minat siswa</span>
                </div>
            </a>

            <!-- 2. QR Code Center -->
            <a href="{{ route('qr-codes.index') }}" @click="mobileMenuOpen = false"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-purple-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-purple-400/10 border border-purple-400/20 text-purple-400 flex items-center justify-center">
                    <i data-lucide="qr-code" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">QR Code Siswa</span>
                    <span class="block text-[10px] text-slate-400">Tampilkan QR di kelas</span>
                </div>
            </a>

            <!-- 3. Hasil Support Event -->
            <a href="{{ route('event-students.index') }}" @click="mobileMenuOpen = false"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-400/10 border border-emerald-400/20 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">Hasil Support Event</span>
                    <span class="block text-[10px] text-slate-400">Leads siswa dari event</span>
                </div>
            </a>

            <!-- 4. Laporan Suport Kegiatan -->
            <a href="{{ route('event-reports.index') }}" @click="mobileMenuOpen = false"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-blue-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-blue-400/10 border border-blue-400/20 text-blue-400 flex items-center justify-center">
                    <i data-lucide="camera" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">Laporan Event</span>
                    <span class="block text-[10px] text-slate-400">Upload foto ke Drive</span>
                </div>
            </a>

            <!-- 5. Rute Navigasi Sekolah -->
            <a href="{{ route('marketing-routes.index') }}" @click="mobileMenuOpen = false"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-sky-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-sky-400/10 border border-sky-400/20 text-sky-400 flex items-center justify-center">
                    <i data-lucide="navigation" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">Rute Marketing</span>
                    <span class="block text-[10px] text-slate-400">Navigasi GPS rute sekolah</span>
                </div>
            </a>

            <!-- 6. Cek Status Proposal -->
            <a href="{{ route('school-events.tracking') }}" target="_blank"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-amber-400/10 border border-amber-400/20 text-amber-400 flex items-center justify-center">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">Tracking Proposal</span>
                    <span class="block text-[10px] text-slate-400">Cek persetujuan proposal</span>
                </div>
            </a>

            @if(auth()->check() && (auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan')))
            <!-- 7. Master Kampus -->
            <a href="{{ route('master-data.index', ['tab' => 'campuses']) }}" @click="mobileMenuOpen = false"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-emerald-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-400/10 border border-emerald-400/20 text-emerald-400 flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">Master Kampus</span>
                    <span class="block text-[10px] text-slate-400">Kelola kampus operasional</span>
                </div>
            </a>

            <!-- 8. Master Periode -->
            <a href="{{ route('master-data.index', ['tab' => 'periods']) }}" @click="mobileMenuOpen = false"
                class="p-3 rounded-2xl bg-slate-900 border border-slate-800 hover:border-sky-400/50 flex flex-col justify-between space-y-2">
                <div class="w-8 h-8 rounded-xl bg-sky-400/10 border border-sky-400/20 text-sky-400 flex items-center justify-center">
                    <i data-lucide="calendar" class="w-4 h-4"></i>
                </div>
                <div>
                    <span class="block font-bold text-white text-xs">Master Periode</span>
                    <span class="block text-[10px] text-slate-400">Atur periode PMB aktif</span>
                </div>
            </a>
            @endif
        </div>

        <button type="button" @click="mobileMenuOpen = false"
            class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl transition">
            Tutup Menu
        </button>
    </div>

    <!-- Backdrop Dimmer for Quick Drawer -->
    <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" 
        style="display: none; position: fixed !important; inset: 0 !important; z-index: 55 !important; background-color: rgba(2, 6, 23, 0.75) !important; -webkit-backdrop-filter: blur(4px) !important; backdrop-filter: blur(4px) !important;"
        class="sm:hidden fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs"></div>
</div>

