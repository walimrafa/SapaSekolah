<aside x-data="{ open: true }" 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0 lg:w-64'"
    class="fixed inset-y-0 left-0 z-30 w-64 theme-sidebar bg-slate-900 text-slate-300 flex flex-col transition-all duration-300 ease-in-out border-r border-slate-800 shadow-2xl">

@php
    $appSettings = \App\Services\AppSettingService::all();
    $lbl = fn(string $k, string $def = '') => \App\Services\AppSettingService::getSidebarLabel($k, $def);
    
    // Check active states for auto-expanding current menu
    $isSchoolActive = request()->routeIs('map.*', 'schools.*', 'priorities.*');
    $isFieldActive = request()->routeIs('visit-plans.*', 'visits.*', 'follow-ups.*', 'socializations.completed.*', 'marketing-routes.*', 'activities.*');
    $isCrmActive = request()->routeIs('prospects.*', 'new-students.*', 'student-questionnaires.*', 'student-follow-ups.*', 'study-programs.*', 'kol-recommendations.*');
    $isEventActive = request()->routeIs('event-proposals.*', 'school-events.*', 'event-students.*');
    $isReportActive = request()->routeIs('reports.*', 'comprehensive-reports.*', 'event-reports.*');
    $isPmbActive = request()->routeIs('pmb.*', 'e-brochures.*');
    $canAccessPeriodClosing = auth()->user()->isAdmin() 
        || auth()->user()->isPimpinan() 
        || auth()->user()->isKoordinator() 
        || auth()->user()->isCustomerService();
    $hasPendingPeriodNotice = \App\Services\PeriodClosingService::pendingForUser(auth()->user());
    $isMasterActive = request()->routeIs('master-data.*', 'users.*', 'settings.*', 'whatsapp-notifications.*', 'period-closing.*', 'activity-logs.*', 'master-questionnaires.*') || (!auth()->user()->isCustomerService() && request()->routeIs('qr-codes.*'));

    // Realtime scoped counts for Prospects and New Students (isolated per active campus and period)
    $sidebarCampus = \App\Services\AppContextService::getActiveCampus();
    $sidebarPeriod = \App\Services\AppContextService::getActivePeriod();

    $prospectsCount = \App\Models\Prospect::where('status', '!=', \App\Models\Prospect::STATUS_MAHASISWA)
        ->when($sidebarCampus, function ($q) use ($sidebarCampus) {
            $q->where(function ($sub) use ($sidebarCampus) {
                $sub->where('campus_id', $sidebarCampus->id)
                    ->orWhere(function ($ss) use ($sidebarCampus) {
                        $ss->whereNull('campus_id')
                           ->whereHas('school', fn($sq) => $sq->where('campus_id', $sidebarCampus->id));
                    });
            });
        })
        ->when($sidebarPeriod, fn($q) => $q->where(fn($p) => $p->where('academic_period_id', $sidebarPeriod->id)->orWhereNull('academic_period_id')))
        ->count();

    $newStudentsCount = \App\Models\Prospect::where('status', \App\Models\Prospect::STATUS_MAHASISWA)
        ->when($sidebarCampus, function ($q) use ($sidebarCampus) {
            $q->where(function ($sub) use ($sidebarCampus) {
                $sub->where('campus_id', $sidebarCampus->id)
                    ->orWhere(function ($ss) use ($sidebarCampus) {
                        $ss->whereNull('campus_id')
                           ->whereHas('school', fn($sq) => $sq->where('campus_id', $sidebarCampus->id));
                    });
            });
        })
        ->when($sidebarPeriod, fn($q) => $q->where(fn($p) => $p->where('academic_period_id', $sidebarPeriod->id)->orWhereNull('academic_period_id')))
        ->count();
@endphp

    <!-- Sidebar Header -->
    <div class="h-16 flex items-center justify-between px-5 theme-sidebar-header bg-slate-950/60 border-b border-slate-800 shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 group">
            @if(!empty($appSettings['app_logo_url']))
                <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-9 h-9 object-contain rounded-full bg-white p-0.5 shadow-md">
            @else
                <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-9 h-9 object-contain rounded-full bg-white p-0.5 shadow-md">
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
                    <span>{{ $lbl('menu_gis', 'Data Sekolah') }}</span>
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
                        <span>{{ $lbl('submenu_map', 'Peta GIS') }}</span>
                    </div>
                </a>

                <a href="{{ route('schools.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('schools.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="table" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_schools', 'Daftar Sekolah') }}</span>
                </a>

                @if(!auth()->user()->isCustomerService())
                <a href="{{ route('priorities.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('priorities.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_priorities', 'Prioritas') }}</span>
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
                    <span>{{ $lbl('menu_cs_interactions', 'Kontak & CS') }}</span>
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
                    <span>{{ $lbl('submenu_cs_followups', 'Follow Up') }}</span>
                </a>

                <a href="{{ route('qr-codes.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('qr-codes.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_cs_qrcodes', 'QR Code') }}</span>
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
                    <span>{{ $lbl('menu_field', 'Operasional') }}</span>
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
                    <span>{{ $lbl('submenu_visit_plans', 'Rencana') }}</span>
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
                    <span>{{ $lbl('submenu_socializations_completed', 'Sosialisasi') }}</span>
                </a>

                <a href="{{ route('marketing-routes.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('marketing-routes.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="route" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_marketing_routes', 'Rute Mobil') }}</span>
                </a>

                <a href="{{ route('activities.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('activities.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="megaphone" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_activities', 'Aktivitas') }}</span>
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
                    <span>{{ $lbl('menu_crm', 'Prospek & CRM') }}</span>
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
                        <span>{{ $lbl('submenu_prospects', 'Prospek') }}</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-slate-800 text-slate-300 rounded">{{ $prospectsCount }}</span>
                </a>

                <a href="{{ route('new-students.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('new-students.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>{{ $lbl('submenu_new_students', 'Mahasiswa Baru') }}</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-400/20 text-amber-300 rounded">{{ $newStudentsCount }}</span>
                </a>

                <a href="{{ route('student-questionnaires.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('student-questionnaires.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_questionnaires', 'Kuisioner') }}</span>
                </a>

                <a href="{{ route('student-follow-ups.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('student-follow-ups.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="user-check" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>{{ $lbl('submenu_student_followups', 'Follow Up') }}</span>
                </a>

                {{-- Rekomendasi KOL (Hanya Administrator, Pimpinan, dan Koordinator) --}}
                @if(auth()->user()->canAccessKolMenu())
                <a href="{{ route('kol-recommendations.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('kol-recommendations.*') ? 'bg-gradient-to-r from-pink-600 to-rose-600 text-white shadow-sm shadow-pink-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-pink-400"></i>
                        <span>{{ $lbl('submenu_kol_recommendations', 'Rekomendasi KOL') }}</span>
                    </div>
                    @php
                        $kolCandidateQuery = \App\Models\StudentQuestionnaire::kolCandidates();
                        if ($sidebarCampus) {
                            $kolCandidateQuery->where(function($q) use ($sidebarCampus) {
                                $q->where('campus_id', $sidebarCampus->id)
                                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $sidebarCampus->id))
                                  ->orWhereHas('eventProposal', fn($ep) => $ep->where('campus_id', $sidebarCampus->id));
                            });
                        }
                        $kolCandidateCount = $kolCandidateQuery->count();
                    @endphp
                    @if($kolCandidateCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-pink-500/20 text-pink-300 border border-pink-500/30 rounded">{{ $kolCandidateCount }}</span>
                    @endif
                </a>
                @endif

                <a href="{{ route('study-programs.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('study-programs.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_study_programs', 'Prodi') }}</span>
                </a>
            </div>
        </div>

        <!-- Support Event Sekolah (Proposal & Laporan Kegiatan Terintegrasi) -->
        <div x-data="{ expanded: {{ $isEventActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isEventActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="sparkles" class="w-4 h-4 {{ $isEventActive ? 'text-amber-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_events', 'Support Event') }}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    @php
                        $activeCampusId = \App\Services\AppContextService::getActiveCampus()?->id;
                        $pendingProposalsQuery = \App\Models\SchoolEventProposal::where('status', \App\Models\SchoolEventProposal::STATUS_MENUNGGU_REVIEW);
                        if ($activeCampusId) {
                            $pendingProposalsQuery->where('campus_id', $activeCampusId);
                        }
                        $pendingProposalsCount = $pendingProposalsQuery->count();
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
                
                <!-- 1. Proposal Masuk -->
                <a href="{{ route('event-proposals.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('event-proposals.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="inbox" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span>{{ $lbl('submenu_event_proposals', 'Proposal') }}</span>
                    </div>
                    @if($pendingProposalsCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-400 text-slate-900 rounded-full animate-pulse">{{ $pendingProposalsCount }}</span>
                    @endif
                </a>

                <!-- 2. Hasil Support Event (Data Siswa Masuk) -->
                <a href="{{ route('event-students.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('event-students.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="users" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span>{{ $lbl('submenu_event_students', 'Data Siswa Hasil Support Event') }}</span>
                    </div>
                    @php
                        $eventStudentsQuery = \App\Models\StudentQuestionnaire::where(function ($q) {
                            $q->where('source', 'support_event')->orWhereNotNull('school_event_proposal_id')->orWhere('college_interest_notes', 'like', '%Support Event%');
                        });
                        if ($activeCampusId) {
                            $eventStudentsQuery->where(function ($q) use ($activeCampusId) {
                                $q->where('campus_id', $activeCampusId)
                                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampusId))
                                  ->orWhereHas('eventProposal', fn($ep) => $ep->where('campus_id', $activeCampusId));
                            });
                        }
                        $eventStudentsCount = $eventStudentsQuery->count();
                    @endphp
                    @if($eventStudentsCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-purple-500/20 text-purple-300 rounded">{{ $eventStudentsCount }}</span>
                    @endif
                </a>

                <!-- 3. Tracking Proposal -->
                <a href="{{ route('school-events.tracking') }}" target="_blank"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="search" class="w-3.5 h-3.5 text-sky-400"></i>
                        <span>Tracking Proposal</span>
                    </div>
                    <i data-lucide="external-link" class="w-3 h-3 text-slate-500"></i>
                </a>
            </div>
        </div>

        <!-- Laporan -->
        <div x-data="{ expanded: {{ $isReportActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isReportActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="bar-chart-3" class="w-4 h-4 {{ $isReportActive ? 'text-amber-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_reports', 'Laporan') }}</span>
                </div>
                <div class="flex items-center space-x-1.5">
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-amber-500/20 text-amber-300 rounded">{{ $lbl('badge_reports', 'Laporan') }}</span>
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
                
                <!-- 1. Diagram & Analisis Visual -->
                <a href="{{ route('reports.index', ['category' => 'diagram', 'tab' => 'charts']) }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ (request()->routeIs('reports.*') && (request('category') === 'diagram' || (!request('category') && request('tab') !== 'visits' && request('tab') !== 'followups' && request('tab') !== 'activities' && request('tab') !== 'prospects' && request('tab') !== 'questionnaires' && request('tab') !== 'schools') || request('tab') === 'charts')) ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="pie-chart" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_report_charts', 'Diagram & Analisis') }}</span>
                </a>

                <!-- 2. Laporan Data Tabular -->
                <a href="{{ route('reports.index', ['category' => 'data', 'tab' => 'visits']) }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ (request()->routeIs('reports.*') && (request('category') === 'data' || in_array(request('tab'), ['visits', 'followups', 'activities', 'prospects', 'questionnaires', 'schools'])) && request('tab') !== 'charts') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="table" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>{{ $lbl('submenu_report_data', 'Laporan Data') }}</span>
                </a>

                <!-- 3. Laporan Support Event -->
                <a href="{{ route('event-reports.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('event-reports.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="clipboard-check" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span>{{ $lbl('submenu_report_events', 'Laporan & Dokumentasi Kegiatan') }}</span>
                    </div>
                    @php
                        $safeApprovedCount = $approvedEventsCount ?? (\App\Models\SchoolEventProposal::where('status', \App\Models\SchoolEventProposal::STATUS_DISETUJUI)->when($activeCampusId, fn($q) => $q->where('campus_id', $activeCampusId))->count());
                    @endphp
                    @if($safeApprovedCount > 0)
                        <span class="px-1.5 py-0.2 text-[9px] font-bold bg-purple-500/20 text-purple-300 rounded">{{ $safeApprovedCount }}</span>
                    @endif
                </a>

                <!-- 4. Ekspor & Rekap Multi-format -->
                <a href="{{ route('comprehensive-reports.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('comprehensive-reports.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5 text-sky-400"></i>
                        <span>{{ $lbl('submenu_report_export', 'Ekspor & Rekap') }}</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-sky-500/20 text-sky-300 rounded">XLSX</span>
                </a>
            </div>
        </div>

        <!-- Informasi PMB UBSI -->
        <div x-data="{ expanded: {{ $isPmbActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isPmbActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="graduation-cap" class="w-4 h-4 {{ $isPmbActive ? 'text-brand-400' : 'text-amber-400' }}"></i>
                    <span>{{ $lbl('menu_pmb', 'Info PMB') }}</span>
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
                    <span>{{ $lbl('submenu_pmb_portal', 'Portal') }}</span>
                </a>

                <a href="{{ route('e-brochures.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('e-brochures.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="file-text" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>{{ $lbl('submenu_ebrochures', 'E-Brosur') }}</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-emerald-500/20 text-emerald-300 rounded">{{ \App\Models\EBrochure::count() }}</span>
                </a>

                <a href="https://pmbubsi.id/pmb" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="globe" class="w-3.5 h-3.5 text-blue-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_web', 'Website') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://pmbubsi.id/pmb/lokasi" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_campus', 'Biaya Kuliah') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://pmbubsi.id/infopmb/kampus/daftar_sep" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-purple-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_schedule', 'Jadwal') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://pmbubsi.id/pmb/prosedur" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-cyan-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_procedure', 'Alur Daftar') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>

                <a href="https://bsi.today/beasiswa/" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 hover:bg-slate-800 hover:text-white text-slate-400 group">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                        <span class="group-hover:text-white">{{ $lbl('submenu_pmb_scholarship', 'Beasiswa') }}</span>
                    </div>
                    <i data-lucide="arrow-up-right" class="w-3 h-3 text-slate-500 group-hover:text-amber-400 transition-colors"></i>
                </a>
            </div>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan') || auth()->user()->hasRole('Koordinator Marketing') || $canAccessPeriodClosing)
        <!-- 5. Sistem & Master (Admin & Pimpinan & Koordinator & Otorisator Tutup Periode) -->
        <div x-data="{ expanded: {{ $isMasterActive ? 'true' : 'false' }} }" class="space-y-1">
            <button @click="expanded = !expanded" type="button"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-semibold transition duration-150 {{ $isMasterActive ? 'text-white bg-slate-800/80 font-bold border-l-2 border-brand-500' : 'hover:bg-slate-800 hover:text-white text-slate-300' }}">
                <div class="flex items-center space-x-3">
                    <i data-lucide="settings-2" class="w-4 h-4 {{ $isMasterActive ? 'text-brand-400' : 'text-slate-400' }}"></i>
                    <span>{{ $lbl('menu_master', 'Master & Sistem') }}</span>
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
                
                @if(auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan') || auth()->user()->hasRole('Koordinator Marketing'))
                <!-- Master Kampus -->
                <a href="{{ route('master-data.index', ['tab' => 'campuses']) }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ (request()->routeIs('master-data.*') && request('tab', 'campuses') === 'campuses') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-emerald-400"></i>
                    <span>{{ $lbl('submenu_campuses', 'Master Kampus') }}</span>
                </a>

                <!-- Master Periode -->
                <a href="{{ route('master-data.index', ['tab' => 'periods']) }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ (request()->routeIs('master-data.*') && request('tab') === 'periods') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-400"></i>
                    <span>{{ $lbl('submenu_periods', 'Master Periode') }}</span>
                </a>

                <!-- Master Wilayah -->
                <a href="{{ route('master-data.index', ['tab' => 'wilayah']) }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ (request()->routeIs('master-data.*') && request('tab') === 'wilayah') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_master_data', 'Master Wilayah') }}</span>
                </a>

                <!-- Kontrol Portal Publik -->
                <a href="{{ route('master-data.index', ['tab' => 'portals']) }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ (request()->routeIs('master-data.*') && request('tab') === 'portals') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="shield-alert" class="w-3.5 h-3.5 text-rose-400"></i>
                    <span>{{ $lbl('submenu_portals', 'Portal Publik') }}</span>
                </a>

                <!-- Master Kuisioner -->
                <a href="{{ route('master-questionnaires.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('master-questionnaires.*') ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-600/30 font-bold' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="clipboard-edit" class="w-3.5 h-3.5 text-indigo-400"></i>
                    <span>{{ $lbl('submenu_master_questionnaires', 'Master Kuisioner') }}</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan'))
                <a href="{{ route('users.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('users.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>{{ $lbl('submenu_users', 'User') }}</span>
                </a>
                @endif

                @if(!auth()->user()->isCustomerService() && (auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan')))
                <a href="{{ route('qr-codes.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('qr-codes.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="qr-code" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>{{ $lbl('submenu_qr_codes', 'QR Code') }}</span>
                </a>
                @endif

                @if(auth()->user()->isAdmin())
                <a href="{{ route('whatsapp-notifications.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('whatsapp-notifications.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="message-square" class="w-3.5 h-3.5 text-emerald-400"></i>
                        <span>{{ $lbl('submenu_whatsapp', 'WhatsApp') }}</span>
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

                @if(auth()->user()->isAdmin() || auth()->user()->hasRole('Pimpinan') || auth()->user()->hasRole('Koordinator Marketing'))
                <a href="{{ route('activity-logs.index') }}"
                    class="flex items-center space-x-2.5 px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('activity-logs.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <i data-lucide="activity" class="w-3.5 h-3.5 text-cyan-400"></i>
                    <span>{{ $lbl('submenu_activity_logs', 'Log Aktivitas') }}</span>
                </a>

                <a href="{{ route('database-backups.index') }}"
                    class="flex items-center justify-between px-3 py-2 rounded-lg text-xs font-semibold transition duration-150 {{ request()->routeIs('database-backups.*') ? 'bg-brand-600 text-white shadow-sm shadow-brand-600/30' : 'hover:bg-slate-800 hover:text-white text-slate-400' }}">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="database" class="w-3.5 h-3.5 text-blue-400"></i>
                        <span>Backup Database</span>
                    </div>
                    <span class="px-1.5 py-0.2 text-[9px] font-bold bg-blue-500/20 text-blue-300 rounded border border-blue-500/30">GDrive</span>
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
                {{ strtoupper(substr(auth()->user()->name ?? 'US', 0, 2)) }}
            </div>
            <a href="{{ route('profile.show') }}" class="flex-1 min-w-0 hover:opacity-80 transition" title="Buka Profil & Ganti Password Mandiri">
                <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                <p class="text-[10px] text-amber-400 font-medium truncate">{{ auth()->user()->role?->name ?? 'Marketing' }}</p>
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition duration-150">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    </div>
</aside>
