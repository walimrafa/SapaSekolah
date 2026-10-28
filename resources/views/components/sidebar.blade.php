<aside x-data="{ open: true }" 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-64'"
    class="fixed inset-y-0 left-0 z-30 w-64 theme-sidebar bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 ease-in-out border-r border-slate-800 shadow-2xl">

@php
    $appSettings = \App\Services\AppSettingService::all();
    $lbl = fn(string $k, string $def = '') => \App\Services\AppSettingService::getSidebarLabel($k, $def);
    
    // Check active states for auto-expanding current menu
    $isSchoolActive = request()->routeIs('map.*', 'schools.*', 'priorities.*');
    $isFieldActive = request()->routeIs('visit-plans.*', 'visits.*', 'follow-ups.*', 'socializations.completed.*', 'marketing-routes.*', 'activities.*');
    $isCrmActive = request()->routeIs('prospects.*', 'new-students.*', 'student-questionnaires.*', 'student-follow-ups.*', 'study-programs.*', 'reports.*');
    $isEventActive = request()->routeIs('event-proposals.*', 'event-reports.*', 'school-events.*', 'event-students.*');
    $isPmbActive = request()->routeIs('pmb.*', 'e-brochures.*');
    $canAccessPeriodClosing = auth()->user()->isAdmin() 
        || auth()->user()->isPimpinan() 
        || auth()->user()->isKoordinator() 
        || auth()->user()->isCustomerService();
    $hasPendingPeriodNotice = \App\Services\PeriodClosingService::pendingForUser(auth()->user());
    $isMasterActive = request()->routeIs('master-data.*', 'users.*', 'settings.*', 'whatsapp-notifications.*', 'period-closing.*') || (!auth()->user()->isCustomerService() && request()->routeIs('qr-codes.*'));
@endphp

    <!-- Sidebar Header -->
    <div class="h-16 flex items-center justify-between px-5 theme-sidebar-header bg-slate-950/60 border-b border-slate-800 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
            @if(!empty($appSettings['app_logo_url']))
                <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-9 h-9 object-contain rounded-xl bg-white p-1 shadow-md">
            @else
                <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-9 h-9 object-contain rounded-xl bg-white p-1 shadow-md">
            @endif
            <div>
                <span class="text-base font-extrabold tracking-tight text-white block leading-none">{{ $appSettings['app_name'] }}</span>
                <span class="text-[10px] text-amber-400 font-semibold uppercase tracking-wider block mt-0.5">{{ $appSettings['app_subtitle'] }}</span>
            </div>
        </a>

        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>
    </div>

    <!-- Navigation Menu (Accordion Collapsible Menus) -->
    <div class="flex-1 overflow-y-auto px-3 py-4 space-y-2">

        <!-- 1. Standalone Dashboard Menu -->
        <div>
            <a href="{{ route('dashboard') }}"
                class="flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ request()->routeIs('dashboard') ? 'bg-brand-600 text-white shadow-md shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-amber-400"></i>
                <span>{{ $lbl('menu_dashboard', 'Dashboard') }}</span>
            </a>
        </div>


        <!-- 2. Menu Utama: Data & GIS Sekolah -->
        <div x-data="{ expanded: {{ $isSchoolActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isSchoolActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="building-2" class="w-4 h-4 {{ $isSchoolActive ? 'text-brand-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_gis', 'Data & GIS Sekolah') }}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-400/20 text-amber-300 rounded">{{ $lbl('badge_gis', 'GIS') }}</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-400' : ''"></i>
                </div>
            </button>

            <!-- Submenu Items -->
            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="pl-4 ml-4 border-l border-slate-800 space-y-1 pt-1" style="display: none;">
                
                <a href="{{ route('map.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('map.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="map" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>{{ $lbl('submenu_map', 'Peta Sekolah') }}</span>
                    </div>
                </a>

                <a href="{{ route('schools.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('schools.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_schools', 'Data Sekolah') }}</span>
                </a>

                @if(!auth()->user()->isCustomerService())
                <a href="{{ route('priorities.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('priorities.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_priorities', 'Prioritas Sekolah') }}</span>
                </a>
                @endif
            </div>
        </div>

        @if(auth()->user()->isCustomerService())
        <!-- Customer Service Follow Up Group -->
        <div x-data="{ expanded: {{ request()->routeIs('follow-ups.*', 'qr-codes.*') ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ request()->routeIs('follow-ups.*', 'qr-codes.*') ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="phone-call" class="w-4 h-4 text-emerald-400"></i>
                    <span>{{ $lbl('menu_cs_interactions', 'Interaksi & Kontak') }}</span>
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-400' : ''"></i>
            </button>

            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="pl-4 ml-4 border-l border-slate-800 space-y-1 pt-1" style="display: none;">
                
                <a href="{{ route('follow-ups.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('follow-ups.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="phone-forwarded" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_cs_followups', 'Follow Up Sekolah') }}</span>
                </a>

                <a href="{{ route('qr-codes.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('qr-codes.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_cs_qrcodes', 'Master QR Code') }}</span>
                </a>
            </div>
        </div>
        @else
        <!-- 3. Operasional Lapangan (Marketing, Koordinator, Pimpinan, Admin) -->
        <div x-data="{ expanded: {{ $isFieldActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isFieldActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="navigation-2" class="w-4 h-4 {{ $isFieldActive ? 'text-brand-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_field', 'Operasional Lapangan') }}</span>
                </div>
                <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-400' : ''"></i>
            </button>

            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="pl-4 ml-4 border-l border-slate-800 space-y-1 pt-1" style="display: none;">
                
                <a href="{{ route('visit-plans.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('visit-plans.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_visit_plans', 'Rencana Kunjungan') }}</span>
                </a>

                <a href="{{ route('visits.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('visits.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_visits', 'Kunjungan') }}</span>
                </a>

                <a href="{{ route('follow-ups.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('follow-ups.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_field_followups', 'Follow Up') }}</span>
                </a>

                <a href="{{ route('socializations.completed.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('socializations.completed.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="presentation" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>{{ $lbl('submenu_socializations_completed', 'Sekolah Sudah Sosialisasi') }}</span>
                </a>

                <a href="{{ route('marketing-routes.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('marketing-routes.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="route" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_marketing_routes', 'Rute Marketing') }}</span>
                </a>

                <a href="{{ route('activities.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('activities.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_activities', 'Aktivitas Marketing') }}</span>
                </a>
            </div>
        </div>
        @endif

        <!-- 4. Prospek & Konversi (CRM Workspace) -->
        <div x-data="{ expanded: {{ $isCrmActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isCrmActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="users" class="w-4 h-4 {{ $isCrmActive ? 'text-brand-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_crm', 'Prospek & Konversi') }}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-emerald-500/20 text-emerald-300 rounded">{{ $lbl('badge_crm', 'CRM') }}</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-400' : ''"></i>
                </div>
            </button>

            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="pl-4 ml-4 border-l border-slate-800 space-y-1 pt-1" style="display: none;">
                
                <a href="{{ route('prospects.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('prospects.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                        <span>{{ $lbl('submenu_prospects', 'Data Prospek') }}</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-slate-800 text-slate-300 rounded">{{ \App\Models\Prospect::where('status', '!=', 'mahasiswa')->count() }}</span>
                </a>

                <a href="{{ route('new-students.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('new-students.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>{{ $lbl('submenu_new_students', 'Mahasiswa Baru') }}</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-400/20 text-amber-300 rounded">{{ \App\Models\Prospect::where('status', 'mahasiswa')->count() }}</span>
                </a>

                <a href="{{ route('student-questionnaires.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('student-questionnaires.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_questionnaires', 'Hasil Kuisioner') }}</span>
                </a>

                <a href="{{ route('student-follow-ups.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('student-follow-ups.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>{{ $lbl('submenu_student_followups', 'Follow Up Siswa') }}</span>
                </a>

                <a href="{{ route('study-programs.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('study-programs.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_study_programs', 'Program Studi') }}</span>
                </a>

                <a href="{{ route('reports.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('reports.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="bar-chart-3" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_reports', 'Laporan & Analytics') }}</span>
                </a>
            </div>
        </div>

        <!-- Support Event Sekolah (Proposal & Laporan Kegiatan Terintegrasi) -->
        <div x-data="{ expanded: {{ $isEventActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isEventActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="sparkles" class="w-4 h-4 {{ $isEventActive ? 'text-amber-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_events', 'Support Event Sekolah') }}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    @php
                        $pendingProposalsCount = \App\Models\SchoolEventProposal::where('status', \App\Models\SchoolEventProposal::STATUS_MENUNGGU_REVIEW)->count();
                    @endphp
                    @if($pendingProposalsCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-400 text-slate-900 rounded-full animate-pulse">{{ $pendingProposalsCount }}</span>
                    @else
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-indigo-500/20 text-indigo-300 rounded">{{ $lbl('badge_events', 'Event') }}</span>
                    @endif
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-400' : ''"></i>
                </div>
            </button>

            <!-- Submenu Items -->
            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="pl-4 ml-4 border-l border-slate-800 space-y-1 pt-1" style="display: none;">
                
                <a href="{{ route('event-proposals.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('event-proposals.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="inbox" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>{{ $lbl('submenu_event_proposals', 'Data Proposal Masuk') }}</span>
                    </div>
                    @if($pendingProposalsCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-500/20 text-amber-300 rounded">{{ $pendingProposalsCount }}</span>
                    @endif
                </a>

                <a href="{{ route('event-reports.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('event-reports.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>{{ $lbl('submenu_event_reports', 'Laporan Suport Kegiatan') }}</span>
                    </div>
                    @php
                        $approvedEventsCount = \App\Models\SchoolEventProposal::where('status', \App\Models\SchoolEventProposal::STATUS_DISETUJUI)->count();
                    @endphp
                    @if($approvedEventsCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-emerald-500/20 text-emerald-300 rounded">{{ $approvedEventsCount }}</span>
                    @endif
                </a>

                <a href="{{ route('event-students.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('event-students.index') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span>{{ $lbl('submenu_event_students', 'Hasil Support Event') }}</span>
                    </div>
                    @php
                        $eventStudentsCount = \App\Models\StudentQuestionnaire::where(function ($q) {
                            $q->where('source', 'support_event')->orWhereNotNull('school_event_proposal_id')->orWhere('college_interest_notes', 'like', '%Support Event%');
                        })->count();
                    @endphp
                    @if($eventStudentsCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-purple-500/20 text-purple-300 rounded">{{ $eventStudentsCount }}</span>
                    @endif
                </a>
            </div>
        </div>

        <!-- Informasi PMB UBSI -->
        <div x-data="{ expanded: {{ $isPmbActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isPmbActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="graduation-cap" class="w-4 h-4 {{ $isPmbActive ? 'text-brand-400' : 'text-amber-400' }}"></i>
                    <span>{{ $lbl('menu_pmb', 'Informasi PMB') }}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer" @click.stop
                        title="Buka Website PMB UBSI di Tab Baru"
                        class="p-1 rounded text-slate-400 hover:text-amber-400 hover:bg-slate-700/60 transition">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-400/20 text-amber-300 rounded">{{ $lbl('badge_pmb', 'UBSI') }}</span>
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-400' : ''"></i>
                </div>
            </button>

            <!-- Submenu Items -->
            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="pl-4 ml-4 border-l border-slate-800 space-y-1 pt-1" style="display: none;">
                
                <a href="{{ route('pmb.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('pmb.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="layout" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_pmb_portal', 'Pusat Info & Portal') }}</span>
                </a>

                <a href="{{ route('e-brochures.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('e-brochures.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>{{ $lbl('submenu_ebrochures', 'E-Brosur PMB') }}</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-emerald-500/20 text-emerald-300 rounded">{{ \App\Models\EBrochure::count() }}</span>
                </a>

                <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-blue-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_web', 'Website PMB UBSI') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://pmbubsi.id/pmb/lokasi" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_campus', 'Kampus & Biaya Kuliah') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://pmbubsi.id/infopmb/kampus/daftar_sep" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_schedule', 'Jadwal & Gelombang') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://pmbubsi.id/pmb/prosedur" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-cyan-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_procedure', 'Alur Pendaftaran') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://bsi.today/beasiswa/" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_scholarship', 'Beasiswa UBSI 2026') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>
            </div>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan') || $canAccessPeriodClosing)
        <!-- 5. Sistem & Master (Admin & Pimpinan & Otorisator Tutup Periode) -->
        <div x-data="{ expanded: {{ $isMasterActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isMasterActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="settings-2" class="w-4 h-4 {{ $isMasterActive ? 'text-brand-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_master', 'Sistem & Master') }}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    @if($hasPendingPeriodNotice)
                        <span class="px-1.5 py-0.2 text-[9px] font-extrabold bg-amber-400 text-slate-900 rounded-full animate-bounce">1 Aksi</span>
                    @endif
                    <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180 text-amber-400' : ''"></i>
                </div>
            </button>

            <div x-show="expanded" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 -translate-y-1"
                class="pl-4 ml-4 border-l border-slate-800 space-y-1 pt-1" style="display: none;">
                
                @if(auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan'))
                <a href="{{ route('master-data.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('master-data.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="database" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_master_data', 'Master Data') }}</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <a href="{{ route('users.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('users.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_users', 'User Management') }}</span>
                </a>
                @endif

                @if(!auth()->user()->isCustomerService() && (auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan')))
                <a href="{{ route('qr-codes.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('qr-codes.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_qr_codes', 'Master QR Code') }}</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <a href="{{ route('whatsapp-notifications.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('whatsapp-notifications.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="message-square" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>{{ $lbl('submenu_whatsapp', 'Notifikasi WA') }}</span>
                    </div>
                    @php
                        $todayWaCount = \App\Models\WhatsAppNotificationLog::whereDate('created_at', now()->toDateString())->count();
                    @endphp
                    @if($todayWaCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-emerald-500/20 text-emerald-300 rounded">{{ $todayWaCount }}</span>
                    @endif
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <a href="{{ route('settings.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('settings.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="sliders" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_settings', 'Pengaturan') }}</span>
                </a>
                @endif

                @if($canAccessPeriodClosing)
                <a href="{{ route('period-closing.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('period-closing.*') ? 'bg-red-600 text-white shadow-sm shadow-red-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5 {{ request()->routeIs('period-closing.*') ? 'text-white' : 'text-red-400' }}"></i>
                        <span>{{ $lbl('submenu_period_closing', 'Tutup Periode') }}</span>
                    </div>
                    @if($hasPendingPeriodNotice)
                        <span class="px-1.5 py-0.2 text-[9px] font-extrabold bg-amber-400 text-slate-900 rounded-full animate-bounce">1 Aksi</span>
                    @endif
                </a>
                @endif
            </div>
        </div>
        @endif

    </div>

    <!-- User Profile Footer -->
    <div class="p-3 theme-sidebar-footer bg-slate-950/80 border-t border-slate-800 shrink-0">
        <div class="flex items-center space-x-3 px-2 py-2 rounded-xl bg-slate-900">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-tr from-brand-600 to-amber-500 flex items-center justify-center text-white font-bold text-sm shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-amber-400 font-medium truncate">{{ auth()->user()->role ? auth()->user()->role->name : 'Marketing' }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition duration-150">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
