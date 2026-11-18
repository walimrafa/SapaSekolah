@php
    $appSettings = \App\Services\AppSettingService::all();
    $lbl = fn(string $k, string $def = '') => \App\Services\AppSettingService::getSidebarLabel($k, $def);
    $activeCampus = \App\Services\AppContextService::getActiveCampus();
    $campusLabel = $activeCampus ? $activeCampus->name : 'Seluruh Kampus';
    $dashUser = auth()->user();
    $canManageMasterAndPortal = $dashUser->isAdmin() || $dashUser->hasRole('Pimpinan') || $dashUser->hasRole('Koordinator Marketing');
    $canAccessPeriodClosing = $dashUser->isAdmin()
        || $dashUser->isPimpinan()
        || $dashUser->isKoordinator()
        || $dashUser->isAnyCustomerService();
    $hasPendingPeriodNotice = \App\Services\PeriodClosingService::pendingForUser($dashUser);
    $eBrochureCount = \App\Models\EBrochure::count();
    $todayWaCount = $dashUser->isAdmin()
        ? \App\Models\WhatsAppNotificationLog::whereDate('created_at', now()->toDateString())->count()
        : 0;
@endphp
<x-app-layout title="Dashboard Command Center">
    <!-- Top Welcome Banner & Marketing Target Bar -->
    <div
        class="mb-8 p-6 lg:p-8 rounded-3xl bg-gradient-to-r from-slate-950 via-brand-navy to-slate-900 text-white shadow-xl relative overflow-hidden">
        @if(!empty($appSettings['campus_building_url']))
            <div class="absolute inset-0 bg-cover bg-center opacity-20 mix-blend-luminosity scale-105 pointer-events-none"
                style="background-image: url('{{ $appSettings['campus_building_url'] }}')"></div>
            <div
                class="absolute inset-0 bg-gradient-to-r from-slate-950 via-brand-navy/90 to-slate-900/90 pointer-events-none">
            </div>
        @endif
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl pointer-events-none">
        </div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div
                    class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold mb-3 border border-amber-500/30">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                    <span>{{ $campusLabel }} Command Center</span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">
                    Halo, {{ auth()->user()->name }} 👋
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl">
                    Monitoring digital mapping sekolah, agenda marketing, dan konversi PMB secara realtime.
                </p>
            </div>

            <!-- Quick Action Toolbar -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('map.index') }}"
                    class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-amber-500/20 flex items-center space-x-2 transition duration-150">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Peta GIS</span>
                </a>
                <a href="{{ route('schools.index') }}"
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl text-xs border border-white/20 flex items-center space-x-2 transition duration-150">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Data Sekolah</span>
                </a>
                <a href="{{ route('visit-plans.index') }}"
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl text-xs border border-white/20 flex items-center space-x-2 transition duration-150">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    <span>Rencana Kunjungan</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 10 KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <x-stat-card title="Total Sekolah" :value="$totalSchools" icon="school" color="navy" :subtitle="$campusLabel" />
        <x-stat-card title="Prioritas A" :value="$priorityA" icon="star" color="red" subtitle="Skor 80 - 100" />
        <x-stat-card title="Prioritas B" :value="$priorityB" icon="check-circle" color="amber"
            subtitle="Skor 60 - 79" />
        <x-stat-card title="Prioritas C" :value="$priorityC" icon="shield" color="emerald" subtitle="Skor < 60" />
        <x-stat-card title="Sudah Dikunjungi" :value="$visitedCount" icon="map-pin" color="blue"
            subtitle="Terkunjungi" />

        <x-stat-card title="Belum Dikunjungi" :value="$unvisitedCount" icon="clock" color="white"
            subtitle="Target Outreach" />
        <x-stat-card title="Follow Up Hari Ini" :value="$followUpsToday" icon="phone-call" color="amber"
            subtitle="Jatuh Tempo" />
        <x-stat-card title="Total Prospek" :value="$totalProspects" icon="users" color="purple" subtitle="Data CRM" />
        <x-stat-card title="Registrasi" :value="$registeredProspects" icon="user-check" color="emerald"
            subtitle="Telah mendaftar" />
        <x-stat-card title="Mahasiswa Fix" :value="$mahasiswaCount" icon="graduation-cap" color="navy"
            subtitle="Konversi PMB" />
    </div>

    <!-- Pusat Layanan, Publikasi & Administrasi (Dipindahkan dari Sidebar agar Navigasi Ramping) -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-extrabold text-slate-900 flex items-center gap-2">
                    <i data-lucide="layout-grid" class="w-4 h-4 text-brand-600"></i>
                    <span>Pusat Layanan, Publikasi &amp; Administrasi</span>
                </h2>
                <p class="text-xs text-slate-500">Akses cepat informasi PMB, portal publik, data induk akademik, dan pengaturan sistem sesuai hak akses Anda.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
            <!-- Kelompok 1: Info PMB (UBSI) -->
            <div x-data="{ open: false }"
                class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden transition hover:border-amber-300/80">
                <button type="button" @click="open = !open"
                    class="w-full p-5 flex items-center justify-between text-left hover:bg-slate-50/70 transition cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 border border-amber-200/60 flex items-center justify-center shrink-0">
                            <i data-lucide="graduation-cap" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $lbl('menu_pmb', 'Info PMB') }}</h3>
                            <p class="text-[11px] text-slate-500 truncate">Referensi resmi, brosur, biaya &amp; beasiswa</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-2">
                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-100 text-amber-800 rounded-lg border border-amber-200">
                            {{ $lbl('badge_pmb', 'UBSI') }}
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-amber-600' : ''"></i>
                    </div>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="px-5 pb-5 pt-3 border-t border-slate-100" style="display: none;">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        <a href="{{ route('pmb.index') }}"
                            class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-800 border border-slate-100 hover:border-amber-200 font-semibold transition">
                            <i data-lucide="layout" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                            <span class="truncate">{{ $lbl('submenu_pmb_portal', 'Portal') }}</span>
                        </a>

                        <a href="{{ route('e-brochures.index') }}"
                            class="flex items-center justify-between gap-1.5 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-100 hover:border-emerald-200 font-semibold transition">
                            <span class="flex items-center gap-2 min-w-0">
                                <i data-lucide="file-text" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_ebrochures', 'E-Brosur') }}</span>
                            </span>
                            <span class="px-1.5 py-0.2 text-[9px] font-extrabold bg-emerald-100 text-emerald-700 rounded shrink-0">{{ $eBrochureCount }}</span>
                        </a>

                        <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between gap-1 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-800 border border-slate-100 hover:border-blue-200 font-semibold transition">
                            <span class="flex items-center gap-2 min-w-0">
                                <i data-lucide="globe" class="w-3.5 h-3.5 text-blue-500 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_pmb_web', 'Website') }}</span>
                            </span>
                            <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-400 shrink-0"></i>
                        </a>

                        <a href="https://pmbubsi.id/pmb/lokasi" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between gap-1 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-100 hover:border-emerald-200 font-semibold transition">
                            <span class="flex items-center gap-2 min-w-0">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_pmb_campus', 'Biaya Kuliah') }}</span>
                            </span>
                            <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-400 shrink-0"></i>
                        </a>

                        <a href="https://pmbubsi.id/infopmb/kampus/daftar_sep" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between gap-1 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-purple-50 text-slate-700 hover:text-purple-800 border border-slate-100 hover:border-purple-200 font-semibold transition">
                            <span class="flex items-center gap-2 min-w-0">
                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-500 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_pmb_schedule', 'Jadwal') }}</span>
                            </span>
                            <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-400 shrink-0"></i>
                        </a>

                        <a href="https://pmbubsi.id/pmb/prosedur" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between gap-1 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-cyan-50 text-slate-700 hover:text-cyan-800 border border-slate-100 hover:border-cyan-200 font-semibold transition">
                            <span class="flex items-center gap-2 min-w-0">
                                <i data-lucide="file-check" class="w-3.5 h-3.5 text-cyan-500 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_pmb_procedure', 'Alur Daftar') }}</span>
                            </span>
                            <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-400 shrink-0"></i>
                        </a>

                        <a href="https://bsi.today/beasiswa/" target="_blank" rel="noopener noreferrer"
                            class="flex items-center justify-between gap-1 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-800 border border-slate-100 hover:border-amber-200 font-semibold transition col-span-2 sm:col-span-3">
                            <span class="flex items-center gap-2">
                                <i data-lucide="award" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                                <span>{{ $lbl('submenu_pmb_scholarship', 'Beasiswa') }} UBSI</span>
                            </span>
                            <i data-lucide="arrow-up-right" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kelompok 2: Akses Publik & Kuisioner -->
            <div x-data="{ open: false }"
                class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden transition hover:border-indigo-300/80">
                <button type="button" @click="open = !open"
                    class="w-full p-5 flex items-center justify-between text-left hover:bg-slate-50/70 transition cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-200/60 flex items-center justify-center shrink-0">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $lbl('menu_portals_questionnaires', 'Portal & Kuisioner') }}</h3>
                            <p class="text-[11px] text-slate-500 truncate">Kontrol formulir publik, pertanyaan &amp; QR Code</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-2">
                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-indigo-100 text-indigo-800 rounded-lg border border-indigo-200">
                            {{ $lbl('badge_portals', 'Publik') }}
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-indigo-600' : ''"></i>
                    </div>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="px-5 pb-5 pt-3 border-t border-slate-100" style="display: none;">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                        @if($canManageMasterAndPortal)
                            <a href="{{ route('master-data.index', ['tab' => 'portals']) }}"
                                class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-50 hover:bg-rose-50 text-slate-700 hover:text-rose-800 border border-slate-100 hover:border-rose-200 font-semibold transition">
                                <div class="w-8 h-8 rounded-lg bg-rose-100/80 text-rose-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="shield-alert" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold truncate">{{ $lbl('submenu_portals', 'Portal Publik') }}</div>
                                    <div class="text-[10px] text-slate-400">Buka / tutup akses formulir</div>
                                </div>
                            </a>

                            <a href="{{ route('master-questionnaires.index') }}"
                                class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-50 hover:bg-indigo-50 text-slate-700 hover:text-indigo-800 border border-slate-100 hover:border-indigo-200 font-semibold transition">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100/80 text-indigo-600 flex items-center justify-center shrink-0">
                                    <i data-lucide="clipboard-edit" class="w-4 h-4"></i>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold truncate">{{ $lbl('submenu_master_questionnaires', 'Master Kuisioner') }}</div>
                                    <div class="text-[10px] text-slate-400">Kelola daftar pertanyaan siswa</div>
                                </div>
                            </a>
                        @endif

                        <a href="{{ route('qr-codes.index') }}"
                            class="flex items-center gap-2.5 p-3 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-800 border border-slate-100 hover:border-amber-200 font-semibold transition sm:col-span-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100/80 text-amber-600 flex items-center justify-center shrink-0">
                                <i data-lucide="qr-code" class="w-4 h-4"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-bold truncate">{{ $lbl('submenu_qr_codes', 'QR Code') }} Generator</div>
                                <div class="text-[10px] text-slate-400">Unduh &amp; bagikan QR formulir kunjungan dan kuisioner sekolah</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kelompok 3: Master Data Akademik (Admin, Pimpinan, Koordinator Marketing) -->
            @if($canManageMasterAndPortal)
            <div x-data="{ open: false }"
                class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden transition hover:border-emerald-300/80">
                <button type="button" @click="open = !open"
                    class="w-full p-5 flex items-center justify-between text-left hover:bg-slate-50/70 transition cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200/60 flex items-center justify-center shrink-0">
                            <i data-lucide="database" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $lbl('menu_master_data', 'Master Data') }}</h3>
                            <p class="text-[11px] text-slate-500 truncate">Data induk kampus, tahun ajaran &amp; wilayah</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-2">
                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-emerald-100 text-emerald-800 rounded-lg border border-emerald-200">
                            {{ $lbl('badge_master_data', 'Akademik') }}
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-emerald-600' : ''"></i>
                    </div>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="px-5 pb-5 pt-3 border-t border-slate-100" style="display: none;">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                        <a href="{{ route('master-data.index', ['tab' => 'campuses']) }}"
                            class="flex flex-col gap-2 p-3.5 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-100 hover:border-emerald-200 font-semibold transition">
                            <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold">{{ $lbl('submenu_campuses', 'Master Kampus') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Cabang &amp; koordinat</div>
                            </div>
                        </a>

                        <a href="{{ route('master-data.index', ['tab' => 'periods']) }}"
                            class="flex flex-col gap-2 p-3.5 rounded-xl bg-slate-50 hover:bg-sky-50 text-slate-700 hover:text-sky-800 border border-slate-100 hover:border-sky-200 font-semibold transition">
                            <div class="w-8 h-8 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                                <i data-lucide="calendar" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold">{{ $lbl('submenu_periods', 'Master Periode') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Tahun ajaran PMB</div>
                            </div>
                        </a>

                        <a href="{{ route('master-data.index', ['tab' => 'wilayah']) }}"
                            class="flex flex-col gap-2 p-3.5 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-800 border border-slate-100 hover:border-amber-200 font-semibold transition">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                <i data-lucide="map-pin" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold">{{ $lbl('submenu_master_data', 'Master Wilayah') }}</div>
                                <div class="text-[10px] text-slate-400 mt-0.5">Jenjang &amp; bobot skor</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Kelompok 4: Sistem & Manajemen -->
            @if($canManageMasterAndPortal || $canAccessPeriodClosing)
            <div x-data="{ open: false }"
                class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden transition hover:border-sky-300/80 {{ !$canManageMasterAndPortal ? 'md:col-span-2' : '' }}">
                <button type="button" @click="open = !open"
                    class="w-full p-5 flex items-center justify-between text-left hover:bg-slate-50/70 transition cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 border border-sky-200/60 flex items-center justify-center shrink-0">
                            <i data-lucide="settings-2" class="w-5 h-5"></i>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-extrabold text-slate-900 truncate">{{ $lbl('menu_system_ops', 'Sistem & Manajemen') }}</h3>
                            <p class="text-[11px] text-slate-500 truncate">Akun pengguna, branding, backup cloud &amp; audit</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 ml-2">
                        @if($hasPendingPeriodNotice)
                            <span class="px-2 py-0.5 text-[10px] font-extrabold bg-amber-400 text-slate-900 rounded-full animate-pulse">1 Aksi</span>
                        @endif
                        <span class="px-2 py-0.5 text-[10px] font-extrabold bg-sky-100 text-sky-800 rounded-lg border border-sky-200">
                            {{ $lbl('badge_system_ops', 'Admin') }}
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180 text-sky-600' : ''"></i>
                    </div>
                </button>

                <div x-show="open" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="px-5 pb-5 pt-3 border-t border-slate-100" style="display: none;">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                        @if($dashUser->isAdmin() || $dashUser->hasRole('Pimpinan'))
                            <a href="{{ route('users.index') }}"
                                class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 border border-slate-100 hover:border-slate-300 font-semibold transition">
                                <i data-lucide="user-check" class="w-3.5 h-3.5 text-slate-600 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_users', 'User & Akun') }}</span>
                            </a>
                        @endif

                        @if($dashUser->isAdmin())
                            <a href="{{ route('whatsapp-notifications.index') }}"
                                class="flex items-center justify-between gap-1.5 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 border border-slate-100 hover:border-emerald-200 font-semibold transition">
                                <span class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="message-square" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i>
                                    <span class="truncate">{{ $lbl('submenu_whatsapp', 'WhatsApp Gateway') }}</span>
                                </span>
                                @if($todayWaCount > 0)
                                    <span class="px-1.5 py-0.2 text-[9px] font-extrabold bg-emerald-100 text-emerald-700 rounded shrink-0">{{ $todayWaCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('settings.index') }}"
                                class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-amber-50 text-slate-700 hover:text-amber-800 border border-slate-100 hover:border-amber-200 font-semibold transition">
                                <i data-lucide="sliders" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_settings', 'Pengaturan Branding') }}</span>
                            </a>
                        @endif

                        @if($canManageMasterAndPortal)
                            <a href="{{ route('database-backups.index') }}"
                                class="flex items-center justify-between gap-1.5 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-blue-50 text-slate-700 hover:text-blue-800 border border-slate-100 hover:border-blue-200 font-semibold transition">
                                <span class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="database" class="w-3.5 h-3.5 text-blue-500 shrink-0"></i>
                                    <span class="truncate">{{ $lbl('submenu_database_backups', 'Cadangan Database GDrive') }}</span>
                                </span>
                            </a>

                            <a href="{{ route('activity-logs.index') }}"
                                class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-slate-50 hover:bg-cyan-50 text-slate-700 hover:text-cyan-800 border border-slate-100 hover:border-cyan-200 font-semibold transition">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-cyan-500 shrink-0"></i>
                                <span class="truncate">{{ $lbl('submenu_activity_logs', 'Log Aktivitas') }}</span>
                            </a>
                        @endif

                        @if($canAccessPeriodClosing)
                            <a href="{{ route('period-closing.index') }}"
                                class="flex items-center justify-between gap-1.5 px-3 py-2.5 rounded-xl {{ $hasPendingPeriodNotice ? 'bg-red-50 text-red-800 border-red-200' : 'bg-slate-50 hover:bg-red-50 text-slate-700 hover:text-red-800 border-slate-100 hover:border-red-200' }} border font-semibold transition">
                                <span class="flex items-center gap-2 min-w-0">
                                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-red-500 shrink-0"></i>
                                    <span class="truncate">{{ $lbl('submenu_period_closing', 'Tutup Periode') }}</span>
                                </span>
                                @if($hasPendingPeriodNotice)
                                    <span class="px-1.5 py-0.2 text-[9px] font-extrabold bg-amber-400 text-slate-900 rounded-full shrink-0">1</span>
                                @endif
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Jadwal Follow Up Terdekat (Ringkas) & Pintasan Diagram Analisis -->
    <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
            <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                <i data-lucide="phone-call" class="w-4 h-4 text-amber-500"></i>
                <span>Jadwal Follow Up Terdekat</span>
            </h3>
            <div class="flex items-center gap-3 text-xs">
                <a href="{{ route('reports.index', ['category' => 'diagram', 'tab' => 'charts']) }}"
                    class="font-bold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                    <i data-lucide="pie-chart" class="w-3.5 h-3.5"></i>
                    <span>Buka Diagram &amp; Analisis Sekolah &rarr;</span>
                </a>
                <span class="text-slate-300">|</span>
                <a href="{{ route('follow-ups.index') }}" class="font-semibold text-brand-600 hover:text-brand-700">Semua Follow Up</a>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @forelse($upcomingFollowups as $fu)
                <div class="flex items-center justify-between p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:border-amber-200 transition">
                    <div class="min-w-0 pr-2">
                        <p class="text-xs font-bold text-slate-800 truncate">
                            {{ $fu->school ? $fu->school->name : 'Sekolah' }}</p>
                        <p class="text-[10px] text-slate-500 truncate">PIC: {{ $fu->user ? $fu->user->name : '-' }} &bull;
                            {{ $fu->method }}</p>
                    </div>
                    <span class="text-[10px] font-bold px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg shrink-0">
                        {{ \Carbon\Carbon::parse($fu->followup_date)->format('d M') }}
                    </span>
                </div>
            @empty
                <div class="col-span-full text-xs text-slate-400 text-center py-4">Tidak ada jadwal follow-up terdekat.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>