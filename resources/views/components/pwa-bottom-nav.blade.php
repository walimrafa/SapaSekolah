@php
    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $isDashboard = in_array($currentRoute, ['dashboard', 'home']);
    $isVisitPlans = str_starts_with($currentRoute, 'visit-plans');
    $isVisitsCreate = ($currentRoute === 'visits.create');
    $isMap = str_starts_with($currentRoute, 'map') || str_starts_with($currentRoute, 'marketing-routes');
    $isQuestionnaire = str_starts_with($currentRoute, 'student-questionnaires') || str_starts_with($currentRoute, 'prospects') || str_starts_with($currentRoute, 'event-students');
@endphp

<div x-data="{ mobileMenuOpen: false }">
    <!-- PWA Bottom Navigation Bar (Khusus Handphone / Layar Kecil) -->
    <nav class="sm:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-950/95 backdrop-blur-xl border-t border-slate-800/90 shadow-[0_-8px_25px_rgba(0,0,0,0.5)] px-2 py-1.5 safe-area-inset-bottom">
        <div class="grid grid-cols-5 items-center max-w-md mx-auto relative">
            
            <!-- 1. Dashboard Ringkas -->
            <a href="{{ route('dashboard') }}" 
                class="flex flex-col items-center justify-center py-1 rounded-xl transition {{ $isDashboard ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="layout-dashboard" class="w-5 h-5 {{ $isDashboard ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                    @if($isDashboard)
                        <span class="absolute -top-1 -right-1 w-1.5 h-1.5 bg-amber-400 rounded-full animate-ping"></span>
                    @endif
                </div>
                <span class="text-[10px] mt-1 leading-none">Dashboard</span>
            </a>

            <!-- 2. Jadwal Kunjungan Lapangan -->
            <a href="{{ route('visit-plans.index') }}" 
                class="flex flex-col items-center justify-center py-1 rounded-xl transition {{ $isVisitPlans ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="calendar" class="w-5 h-5 {{ $isVisitPlans ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                </div>
                <span class="text-[10px] mt-1 leading-none">Jadwal</span>
            </a>

            <!-- 3. TOMBOL UTAMA TENGAH: Catat Kunjungan Baru (Elevated Floating Action Button) -->
            <div class="flex flex-col items-center justify-center -mt-6">
                <a href="{{ route('visits.create') }}" 
                    title="Catat Kunjungan Sekolah Baru"
                    class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-brand-600 to-rose-500 hover:from-brand-500 hover:to-rose-400 text-white flex items-center justify-center shadow-lg shadow-rose-600/40 border-2 border-slate-900 active:scale-95 transition-transform duration-150">
                    <i data-lucide="plus" class="w-6 h-6 stroke-[3]"></i>
                </a>
                <span class="text-[9px] font-black uppercase text-rose-400 mt-1 tracking-wider leading-none">Kunjungan</span>
            </div>

            <!-- 4. Peta GIS & Navigasi Rute -->
            <a href="{{ route('map.index') }}" 
                class="flex flex-col items-center justify-center py-1 rounded-xl transition {{ $isMap ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="map-pin" class="w-5 h-5 {{ $isMap ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                </div>
                <span class="text-[10px] mt-1 leading-none">Peta GIS</span>
            </a>

            <!-- 5. Kuisioner & Menu Cepat Lapangan -->
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                class="flex flex-col items-center justify-center py-1 rounded-xl transition {{ $isQuestionnaire ? 'text-amber-400 font-bold' : 'text-slate-400 hover:text-slate-200' }}">
                <div class="relative">
                    <i data-lucide="grid" class="w-5 h-5 {{ $isQuestionnaire ? 'stroke-[2.5]' : 'stroke-[1.8]' }}"></i>
                </div>
                <span class="text-[10px] mt-1 leading-none">Aksi Cepat</span>
            </button>

        </div>
    </nav>

    <!-- Bottom Sheet / Drawer Aksi Cepat Lapangan -->
    <div x-show="mobileMenuOpen" style="display: none;" 
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
        </div>

        <button type="button" @click="mobileMenuOpen = false"
            class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs rounded-xl transition">
            Tutup Menu
        </button>
    </div>

    <!-- Backdrop Dimmer for Quick Drawer -->
    <div x-show="mobileMenuOpen" @click="mobileMenuOpen = false" 
        style="display: none;"
        class="sm:hidden fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-xs"></div>
</div>

