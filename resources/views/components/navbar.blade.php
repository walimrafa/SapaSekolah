@php
    $appSettings = \App\Services\AppSettingService::all();
    $activeCampus = \App\Services\AppContextService::getActiveCampus();
    $activePeriod = \App\Services\AppContextService::getActivePeriod();
    $allCampuses = \App\Services\AppContextService::getAllCampuses();
    $allPeriods = \App\Services\AppContextService::getAllPeriods();
    $user = auth()->user();
    $canSwitchCampus = \App\Services\AppContextService::canSwitchCampus($user);
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
            <p class="text-[11px] text-slate-500 font-medium">{{ $activeCampus ? $activeCampus->name : $appSettings['campus_name'] }} &bull; {{ date('l, d F Y') }}</p>
        </div>
    </div>

    <!-- Context Switcher (Kampus & Periode Aktif) -->
    <div class="hidden sm:flex items-center space-x-2">
        <!-- Campus Selector Dropdown -->
        @if($canSwitchCampus)
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" @click.away="open = false" type="button"
                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-slate-300/80 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-800 transition shadow-xs cursor-pointer">
                    <i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                    <span class="max-w-[140px] truncate">{{ $activeCampus ? $activeCampus->name : 'Semua Kampus' }}</span>
                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-500 shrink-0"></i>
                </button>
                <div x-show="open" x-transition
                    class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                    <div class="px-3.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 mb-1 flex items-center justify-between">
                        <span>Pilih Filter Kampus</span>
                        <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-emerald-600"></i>
                    </div>
                    <form method="POST" action="{{ route('context.switch') }}">
                        @csrf
                        <input type="hidden" name="campus_id" value="ALL">
                        <button type="submit" class="w-full text-left px-3.5 py-2 text-xs font-semibold hover:bg-slate-50 flex items-center justify-between {{ !$activeCampus ? 'text-brand-600 font-bold bg-brand-50/50' : 'text-slate-700' }}">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full {{ !$activeCampus ? 'bg-brand-600' : 'bg-slate-300' }}"></span>
                                <span>Semua Kampus (Pusat)</span>
                            </div>
                            @if(!$activeCampus)<i data-lucide="check" class="w-3.5 h-3.5 text-brand-600"></i>@endif
                        </button>
                    </form>
                    <div class="my-1 border-t border-slate-100"></div>
                    @foreach($allCampuses as $camp)
                        <form method="POST" action="{{ route('context.switch') }}">
                            @csrf
                            <input type="hidden" name="campus_id" value="{{ $camp->id }}">
                            <button type="submit" class="w-full text-left px-3.5 py-2 text-xs font-semibold hover:bg-slate-50 flex items-center justify-between {{ ($activeCampus && $activeCampus->id == $camp->id) ? 'text-brand-600 font-bold bg-brand-50/50' : 'text-slate-700' }}">
                                <div class="flex items-center space-x-2 truncate mr-2">
                                    <span class="w-2 h-2 rounded-full {{ ($activeCampus && $activeCampus->id == $camp->id) ? 'bg-emerald-600' : 'bg-slate-300' }}"></span>
                                    <span class="truncate">{{ $camp->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">({{ $camp->code }})</span>
                                </div>
                                @if($activeCampus && $activeCampus->id == $camp->id)
                                    <i data-lucide="check" class="w-3.5 h-3.5 text-brand-600 shrink-0"></i>
                                @endif
                            </button>
                        </form>
                    @endforeach
                </div>
            </div>
        @else
            <div class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-slate-200/90 bg-slate-50 text-xs font-bold text-slate-700 shadow-xs" title="Kampus Penugasan Akun Anda">
                <i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-600"></i>
                <span class="max-w-[140px] truncate">{{ $activeCampus ? $activeCampus->name : 'Kampus Operasional' }}</span>
            </div>
        @endif

        <!-- Academic Period Selector Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false" type="button"
                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl border border-slate-200/90 bg-slate-50 hover:bg-slate-100 text-xs font-bold text-slate-700 transition shadow-xs">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-600"></i>
                <span class="max-w-[120px] truncate">{{ $activePeriod ? 'T.A. ' . $activePeriod->name : 'Semua Periode' }}</span>
                <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400"></i>
            </button>
            <div x-show="open" x-transition
                class="absolute left-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50">
                <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pilih Filter Periode</div>
                <form method="POST" action="{{ route('context.switch') }}">
                    @csrf
                    <input type="hidden" name="period_id" value="ALL">
                    <button type="submit" class="w-full text-left px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 flex items-center justify-between {{ !$activePeriod ? 'text-brand-600 font-bold bg-brand-50/50' : 'text-slate-700' }}">
                        <span>Semua Periode</span>
                        @if(!$activePeriod)<i data-lucide="check" class="w-3.5 h-3.5 text-brand-600"></i>@endif
                    </button>
                </form>
                <div class="my-1 border-t border-slate-100"></div>
                @foreach($allPeriods as $prd)
                    <form method="POST" action="{{ route('context.switch') }}">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $prd->id }}">
                        <button type="submit" class="w-full text-left px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 flex items-center justify-between {{ ($activePeriod && $activePeriod->id == $prd->id) ? 'text-brand-600 font-bold bg-brand-50/50' : 'text-slate-700' }}">
                            <span>{{ $prd->name }} {{ $prd->is_current ? '(Aktif)' : '' }}</span>
                            @if($activePeriod && $activePeriod->id == $prd->id)
                                <i data-lucide="check" class="w-3.5 h-3.5 text-brand-600 shrink-0"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
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

        <!-- Notification Bell Center (Real-Time Database Backed) -->
        @php
            $navbarUser = auth()->user();
            $unreadNotifCount = \App\Services\NotificationService::getUnreadCount($navbarUser);
            $recentNotifs = \App\Services\NotificationService::getRecentForUser($navbarUser, 6);
        @endphp
        <div x-data="{
                open: false,
                unreadCount: {{ $unreadNotifCount }},
                async markAsRead(id, actionUrl) {
                    try {
                        const res = await fetch(`/notifications/${id}/read`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (data && data.success) {
                            this.unreadCount = data.unread_count;
                        }
                    } catch (e) {
                        console.error('Mark as read error:', e);
                    }
                    if (actionUrl && actionUrl !== '') {
                        window.location.href = actionUrl;
                    }
                },
                async markAllAsRead() {
                    try {
                        const res = await fetch(`{{ route('notifications.read-all') }}`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await res.json();
                        if (data && data.success) {
                            this.unreadCount = 0;
                            window.location.reload();
                        }
                    } catch (e) {
                        window.location.reload();
                    }
                }
            }"
            class="relative">
            <button @click="open = !open" @click.away="open = false" class="p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 relative transition duration-150" title="Notifikasi Sistem">
                <i data-lucide="bell" class="w-5 h-5"></i>
                <template x-if="unreadCount > 0">
                    <span class="absolute top-1 right-1 min-w-[18px] h-[18px] px-1 bg-rose-500 text-white text-[10px] font-extrabold rounded-full flex items-center justify-center ring-2 ring-white"
                          x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
                </template>
            </button>

            <!-- Notifications Panel -->
            <div x-show="open" x-transition
                class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-100 py-3 z-50">
                <div class="px-4 py-2 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-slate-800">Notifikasi Sistem</span>
                        <template x-if="unreadCount > 0">
                            <span class="text-[10px] font-extrabold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full" x-text="unreadCount + ' Baru'"></span>
                        </template>
                    </div>
                    <template x-if="unreadCount > 0">
                        <button type="button" @click="markAllAsRead()" class="text-[11px] font-bold text-brand-600 hover:text-brand-700">
                            Tandai Semua Dibaca
                        </button>
                    </template>
                </div>
                <div class="divide-y divide-slate-50 max-h-80 overflow-y-auto">
                    @forelse($recentNotifs as $notif)
                        <div @click="markAsRead({{ $notif->id }}, '{{ $notif->action_url ?: '' }}')"
                             class="p-3 transition cursor-pointer flex items-start gap-3 hover:bg-slate-50 {{ $notif->is_read ? 'opacity-75' : 'bg-brand-50/20' }}">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5 {{ $notif->is_read ? 'bg-slate-100 text-slate-500' : 'bg-brand-100 text-brand-700' }}">
                                <i data-lucide="{{ $notif->icon ?: 'bell' }}" class="w-4 h-4"></i>
                            </div>
                            <div class="flex-1 min-w-0 text-left">
                                <div class="flex items-center justify-between gap-1">
                                    <p class="text-xs font-bold text-slate-800 truncate">{{ $notif->title }}</p>
                                    @if(!$notif->is_read)
                                        <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-600 mt-0.5 line-clamp-2 leading-relaxed">{{ $notif->message }}</p>
                                <span class="text-[9px] text-slate-400 mt-1 block">{{ $notif->created_at ? $notif->created_at->diffForHumans() : '' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-slate-400">
                            <i data-lucide="bell-off" class="w-6 h-6 mx-auto mb-1.5 opacity-50"></i>
                            <p class="text-xs font-semibold">Tidak ada notifikasi baru</p>
                        </div>
                    @endforelse
                </div>

                <div class="px-4 pt-2.5 mt-1 border-t border-slate-100 text-center">
                    <a href="{{ route('notifications.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700 inline-flex items-center gap-1">
                        <span>Lihat Semua Notifikasi</span>
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- User Dropdown -->
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" @click.away="open = false" class="flex items-center space-x-2.5 p-1 rounded-xl hover:bg-slate-100 transition duration-150">
                <div class="w-8 h-8 rounded-lg bg-slate-900 border border-slate-700/30 text-white font-extrabold text-xs flex items-center justify-center shadow-sm">
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
                <a href="{{ route('profile.show') }}" class="w-full text-left flex items-center space-x-2 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <i data-lucide="user-cog" class="w-4 h-4 text-slate-400"></i>
                    <span>Profil &amp; Ganti Password</span>
                </a>
                <div class="border-t border-slate-100 my-1"></div>
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

<!-- Mobile Sub-Navbar: Context Switcher (Kampus & Periode Aktif di Layar Smartphone) -->
<div class="sm:hidden bg-slate-900 border-b border-slate-800 px-3 py-2 flex items-center justify-between gap-2 shadow-md sticky top-16 z-20 text-white">
    <!-- 1. Pilihan Kampus Mobile -->
    @if($canSwitchCampus)
        <div x-data="{ open: false }" class="relative flex-1 min-w-0">
            <button @click="open = !open" @click.away="open = false" type="button"
                class="w-full inline-flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 border border-slate-700/80 text-[11px] font-bold text-slate-100 shadow-xs transition">
                <div class="flex items-center space-x-1.5 truncate mr-1">
                    <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                    <span class="truncate">{{ $activeCampus ? $activeCampus->name : 'Semua Kampus' }}</span>
                </div>
                <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400 shrink-0"></i>
            </button>
            <div x-show="open" x-transition
                class="absolute left-0 mt-1 w-64 bg-slate-900 rounded-2xl shadow-2xl border border-slate-700 py-2 z-50">
                <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800 mb-1 flex items-center justify-between">
                    <span>Pilih Filter Kampus</span>
                    <a href="{{ route('master-data.index', ['tab' => 'campuses']) }}" class="text-[10px] text-emerald-400 hover:underline">Master Kampus &rarr;</a>
                </div>
                <form method="POST" action="{{ route('context.switch') }}">
                    @csrf
                    <input type="hidden" name="campus_id" value="ALL">
                    <button type="submit" class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-800 flex items-center justify-between {{ !$activeCampus ? 'text-amber-400 font-bold bg-slate-800/80' : 'text-slate-200' }}">
                        <div class="flex items-center space-x-2">
                            <span class="w-2 h-2 rounded-full {{ !$activeCampus ? 'bg-amber-400' : 'bg-slate-600' }}"></span>
                            <span>Semua Kampus (Pusat)</span>
                        </div>
                        @if(!$activeCampus)<i data-lucide="check" class="w-3.5 h-3.5 text-amber-400"></i>@endif
                    </button>
                </form>
                <div class="my-1 border-t border-slate-800"></div>
                @foreach($allCampuses as $camp)
                    <form method="POST" action="{{ route('context.switch') }}">
                        @csrf
                        <input type="hidden" name="campus_id" value="{{ $camp->id }}">
                        <button type="submit" class="w-full text-left px-3 py-2 text-xs font-semibold hover:bg-slate-800 flex items-center justify-between {{ ($activeCampus && $activeCampus->id == $camp->id) ? 'text-emerald-400 font-bold bg-slate-800/80' : 'text-slate-200' }}">
                            <div class="flex items-center space-x-2 truncate mr-1">
                                <span class="w-2 h-2 rounded-full {{ ($activeCampus && $activeCampus->id == $camp->id) ? 'bg-emerald-400' : 'bg-slate-600' }}"></span>
                                <span class="truncate">{{ $camp->name }}</span>
                                <span class="text-[9px] text-slate-400 font-mono">({{ $camp->code }})</span>
                            </div>
                            @if($activeCampus && $activeCampus->id == $camp->id)
                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
                            @endif
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @else
        <div class="flex-1 min-w-0 inline-flex items-center space-x-1.5 px-2.5 py-1.5 rounded-xl bg-slate-800 border border-slate-700/80 text-[11px] font-bold text-slate-200 truncate">
            <i data-lucide="graduation-cap" class="w-3.5 h-3.5 text-emerald-400 shrink-0"></i>
            <span class="truncate">{{ $activeCampus ? $activeCampus->name : 'Kampus Operasional' }}</span>
        </div>
    @endif

    <!-- 2. Pilihan Periode Mobile -->
    <div x-data="{ open: false }" class="relative flex-1 min-w-0">
        <button @click="open = !open" @click.away="open = false" type="button"
            class="w-full inline-flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 border border-slate-700/80 text-[11px] font-bold text-slate-100 shadow-xs transition">
            <div class="flex items-center space-x-1.5 truncate mr-1">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-sky-400 shrink-0"></i>
                <span class="truncate">{{ $activePeriod ? 'T.A. ' . $activePeriod->name : 'Semua Periode' }}</span>
            </div>
            <i data-lucide="chevron-down" class="w-3 h-3 text-slate-400 shrink-0"></i>
        </button>
        <div x-show="open" x-transition
            class="absolute right-0 mt-1 w-56 bg-slate-900 rounded-2xl shadow-2xl border border-slate-700 py-2 z-50">
            <div class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-800 mb-1 flex items-center justify-between">
                <span>Pilih Filter Periode</span>
                <a href="{{ route('master-data.index', ['tab' => 'periods']) }}" class="text-[10px] text-sky-400 hover:underline">Master Periode &rarr;</a>
            </div>
            <form method="POST" action="{{ route('context.switch') }}">
                @csrf
                <input type="hidden" name="period_id" value="ALL">
                <button type="submit" class="w-full text-left px-3 py-1.5 text-xs font-semibold hover:bg-slate-800 flex items-center justify-between {{ !$activePeriod ? 'text-amber-400 font-bold bg-slate-800/80' : 'text-slate-200' }}">
                    <span>Semua Periode</span>
                    @if(!$activePeriod)<i data-lucide="check" class="w-3.5 h-3.5 text-amber-400"></i>@endif
                </button>
            </form>
            <div class="my-1 border-t border-slate-800"></div>
            @foreach($allPeriods as $prd)
                <form method="POST" action="{{ route('context.switch') }}">
                    @csrf
                    <input type="hidden" name="period_id" value="{{ $prd->id }}">
                    <button type="submit" class="w-full text-left px-3 py-1.5 text-xs font-semibold hover:bg-slate-800 flex items-center justify-between {{ ($activePeriod && $activePeriod->id == $prd->id) ? 'text-sky-400 font-bold bg-slate-800/80' : 'text-slate-200' }}">
                        <span class="truncate">{{ $prd->name }} {{ $prd->is_current ? '(Aktif)' : '' }}</span>
                        @if($activePeriod && $activePeriod->id == $prd->id)
                            <i data-lucide="check" class="w-3.5 h-3.5 text-sky-400 shrink-0"></i>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</div>
