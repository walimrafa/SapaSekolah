<x-app-layout title="Master Rencana Event Kampus - SAPA Sekolah">
    <div x-data="eventPlanManager()" class="space-y-6">

        <!-- Top Header & Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            <i data-lucide="calendar" class="w-3.5 h-3.5 mr-1.5 text-amber-400"></i> Master Event Kampus
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <i data-lucide="zap" class="w-3.5 h-3.5 mr-1.5 text-emerald-400"></i> Otomatis Masuk ke Form Sebar Undangan
                        </span>
                        @if($activeCampus)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800/80 text-slate-300 border border-slate-700">
                                <i data-lucide="map-pin" class="w-3 h-3 mr-1 text-sky-400"></i> {{ $activeCampus->name }}
                            </span>
                        @endif
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Master Rencana Event Kampus
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Kelola data tema acara, tanggal, waktu, dan lokasi event promosi UBSI. Event yang masih aktif akan <strong>muncul otomatis</strong> pada formulir kunjungan (kategori <em>Mengantar Undangan Acara</em>), dan event yang sudah <strong>kadaluarsa akan hilang otomatis</strong> dari daftar pilihan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <button type="button" @click="openCreateModal()"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 shadow-lg shadow-amber-500/30 transition hover:scale-105">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Tambah Rencana Event</span>
                    </button>

                    <a href="{{ route('invitations.completed.index') }}"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 shadow-lg backdrop-blur-sm transition hover:scale-105"
                        title="Lihat Data Kunjungan yang Sudah Mengantar Undangan">
                        <i data-lucide="mail-check" class="w-4 h-4 text-amber-300"></i>
                        <span>Undangan Tersebar</span>
                    </a>
                </div>
            </div>
        </div>

        @if(session('toast'))
            <div class="p-4 rounded-2xl {{ (session('toast')['type'] ?? '') === 'error' ? 'bg-rose-50 border border-rose-200 text-rose-800' : ((session('toast')['type'] ?? '') === 'warning' ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-emerald-50 border border-emerald-200 text-emerald-800') }} text-sm font-semibold flex items-center gap-3 shadow-sm">
                <i data-lucide="{{ (session('toast')['type'] ?? '') === 'error' ? 'alert-triangle' : ((session('toast')['type'] ?? '') === 'warning' ? 'alert-circle' : 'check-circle') }}" class="w-5 h-5 shrink-0"></i>
                <span>{{ session('toast')['message'] ?? 'Data berhasil diproses.' }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-start gap-3 shadow-sm">
                <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                <div>
                    <p class="font-bold">Terjadi kesalahan input:</p>
                    <ul class="list-disc list-inside text-xs mt-1 space-y-0.5">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Event Mendatang (Aktif) -->
            <div class="p-4 bg-white rounded-2xl border border-emerald-200/80 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Event Mendatang</p>
                    <span class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i data-lucide="calendar-check-2" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['upcoming_events']) }}</p>
                <span class="text-[11px] text-emerald-600 font-semibold">Aktif &amp; Siap Disebar</span>
            </div>

            <!-- Hari Ini -->
            <div class="p-4 bg-white rounded-2xl border border-amber-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Hari Ini</p>
                    <span class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600">
                        <i data-lucide="flame" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['today_events']) }}</p>
                <span class="text-[11px] text-amber-600 font-semibold">Berlangsung Hari Ini</span>
            </div>

            <!-- Kadaluarsa / Selesai -->
            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Kadaluarsa</p>
                    <span class="w-7 h-7 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500">
                        <i data-lucide="clock-alert" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['expired_events']) }}</p>
                <span class="text-[11px] text-slate-500 font-semibold">Otomatis Tersembunyi</span>
            </div>

            <!-- Total Event -->
            <div class="p-4 bg-white rounded-2xl border border-indigo-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Total Event</p>
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_events']) }}</p>
                <span class="text-[11px] text-indigo-600 font-semibold">Tersimpan di Master</span>
            </div>

            <!-- Undangan Tersebar -->
            <div class="p-4 bg-white rounded-2xl border border-sky-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-sky-700 uppercase tracking-wider">Undangan Disebar</p>
                    <span class="w-7 h-7 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
                        <i data-lucide="send" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_invitations']) }}</p>
                <span class="text-[11px] text-sky-600 font-semibold">Kunjungan Sekolah</span>
            </div>
        </div>

        <!-- Filter Bar & Search -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-1.5">
                    <a href="{{ route('event-plans.index', array_merge(request()->except('status', 'page'), ['status' => 'upcoming'])) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $statusFilter === 'upcoming' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                        <i data-lucide="calendar-check-2" class="w-3.5 h-3.5"></i>
                        <span>Mendatang / Aktif ({{ $stats['upcoming_events'] }})</span>
                    </a>

                    <a href="{{ route('event-plans.index', array_merge(request()->except('status', 'page'), ['status' => 'today'])) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $statusFilter === 'today' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                        <i data-lucide="flame" class="w-3.5 h-3.5"></i>
                        <span>Hari Ini ({{ $stats['today_events'] }})</span>
                    </a>

                    <a href="{{ route('event-plans.index', array_merge(request()->except('status', 'page'), ['status' => 'expired'])) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $statusFilter === 'expired' ? 'bg-slate-700 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                        <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                        <span>Kadaluarsa / Selesai ({{ $stats['expired_events'] }})</span>
                    </a>

                    <a href="{{ route('event-plans.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-600' }}">
                        <i data-lucide="list" class="w-3.5 h-3.5"></i>
                        <span>Semua Event ({{ $stats['total_events'] }})</span>
                    </a>
                </div>

                <!-- Search Form -->
                <form method="GET" action="{{ route('event-plans.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $statusFilter }}">
                    <div class="relative w-full sm:w-64">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari tema, lokasi..."
                            class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-medium">
                    </div>
                    <button type="submit" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition">
                        Cari
                    </button>
                    @if(request()->has('q') || request('status') !== 'upcoming')
                        <a href="{{ route('event-plans.index') }}" class="p-2 rounded-xl text-xs text-slate-500 hover:bg-slate-100 transition" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Event List Table -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Daftar Rencana Event Promosi Kampus</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        @if($statusFilter === 'upcoming')
                            Menampilkan event aktif mendatang yang otomatis muncul pada pilihan sebar undangan.
                        @elseif($statusFilter === 'expired')
                            Menampilkan event yang sudah lewat tanggalnya (kadaluarsa) dan otomatis tersembunyi dari form input.
                        @else
                            Menampilkan seluruh arsip rencana event kampus.
                        @endif
                    </p>
                </div>
                <span class="text-xs font-bold text-slate-400">{{ $events->total() }} Data Ditemukan</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-4 min-w-[240px]">Tema &amp; Rincian Event</th>
                            <th class="py-3 px-4 min-w-[170px]">Jadwal &amp; Waktu</th>
                            <th class="py-3 px-4 min-w-[200px]">Lokasi Pelaksanaan</th>
                            <th class="py-3 px-4 min-w-[150px]">Target &amp; Kuota</th>
                            <th class="py-3 px-4 min-w-[130px]">Kampus</th>
                            <th class="py-3 px-4 min-w-[130px] text-center">Undangan Tersebar</th>
                            <th class="py-3 px-4 min-w-[130px] text-center">Status</th>
                            <th class="py-3 px-4 min-w-[140px] text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($events as $index => $event)
                            @php
                                $badge = $event->status_badge;
                            @endphp
                            <tr class="hover:bg-amber-50/20 transition">
                                <td class="py-3 px-3 text-center font-bold text-slate-400">
                                    {{ $events->firstItem() + $index }}
                                </td>

                                <!-- Tema & Rincian -->
                                <td class="py-3 px-4">
                                    <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500 shrink-0"></i>
                                        <span>{{ $event->title }}</span>
                                    </div>
                                    @if($event->description)
                                        <p class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ $event->description }}</p>
                                    @endif
                                </td>

                                <!-- Jadwal & Waktu -->
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                                        <span>{{ $event->formatted_date }}</span>
                                    </div>
                                    <div class="text-[11px] text-slate-600 mt-1 flex items-center gap-1.5">
                                        <i data-lucide="clock" class="w-3 h-3 text-slate-400 shrink-0"></i>
                                        <span>{{ $event->event_time }}</span>
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold {{ $event->is_today ? 'bg-amber-100 text-amber-900 border border-amber-300' : ($event->is_expired ? 'bg-slate-100 text-slate-500' : 'bg-emerald-100 text-emerald-800 border border-emerald-200') }}">
                                            {{ $event->days_remaining }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Lokasi -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-800 flex items-start gap-1.5">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5"></i>
                                        <span>{{ $event->location }}</span>
                                    </div>
                                </td>

                                <!-- Target & Kuota -->
                                <td class="py-3 px-4">
                                    <div class="text-xs font-semibold text-slate-800">
                                        {{ $event->target_audience ?: 'Siswa SLTA / Umum' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                        <i data-lucide="users" class="w-3 h-3 text-slate-400"></i>
                                        <span>Kuota: {{ $event->quota ? number_format($event->quota) . ' Peserta' : 'Tidak Dibatasi' }}</span>
                                    </div>
                                </td>

                                <!-- Kampus -->
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $event->campus?->name ?? 'Semua Kampus' }}
                                    </span>
                                </td>

                                <!-- Undangan Tersebar -->
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ route('invitations.completed.index', ['q' => $event->title]) }}"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold bg-sky-50 text-sky-700 hover:bg-sky-100 border border-sky-200 transition"
                                        title="Lihat Kunjungan Tersebar untuk Event Ini">
                                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                                        <span>{{ $event->school_visits_count }} Sekolah</span>
                                    </a>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $badge['class'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $badge['dot'] }}"></span>
                                        {{ $badge['label'] }}
                                    </span>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" @click="openEditModal({{ json_encode([
                                            'id' => $event->id,
                                            'title' => $event->title,
                                            'event_date' => $event->event_date->format('Y-m-d'),
                                            'event_time' => $event->event_time,
                                            'location' => $event->location,
                                            'target_audience' => $event->target_audience ?? '',
                                            'quota' => $event->quota ?? '',
                                            'description' => $event->description ?? '',
                                            'status' => $event->status,
                                            'campus_id' => $event->campus_id ?? '',
                                            'update_url' => route('event-plans.update', $event->id),
                                        ]) }})"
                                            class="p-1.5 rounded-lg text-slate-600 hover:bg-slate-100 transition"
                                            title="Ubah Rincian Event">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>

                                        <!-- Delete Button -->
                                        <form method="POST" action="{{ route('event-plans.destroy', $event->id) }}" class="inline"
                                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus Rencana Event: {{ addslashes($event->title) }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 transition"
                                                title="Hapus Rencana Event">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 px-4 text-center">
                                    <div class="max-w-md mx-auto space-y-3">
                                        <div class="w-16 h-16 mx-auto rounded-full bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500">
                                            <i data-lucide="calendar" class="w-8 h-8"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-800">
                                            Belum Ada Data Rencana Event
                                        </h3>
                                        <p class="text-xs text-slate-500 leading-relaxed">
                                            @if($statusFilter === 'upcoming')
                                                Tidak ada event mendatang yang aktif saat ini. Klik tombol "Tambah Rencana Event" di atas untuk menambahkan tema acara baru.
                                            @elseif($statusFilter === 'expired')
                                                Belum ada event yang kadaluarsa.
                                            @else
                                                Belum ada data event yang tersimpan.
                                            @endif
                                        </p>
                                        <div>
                                            <button type="button" @click="openCreateModal()"
                                                class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 transition">
                                                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                                <span>Buat Rencana Event Baru</span>
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $events->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Tambah Rencana Event Baru -->
        <div x-show="showCreateModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div @click.away="showCreateModal = false"
                class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                <form method="POST" action="{{ route('event-plans.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">Tambah Rencana Event Baru</h3>
                                <p class="text-xs text-slate-500">Data otomatis terhubung ke input kunjungan sebar undangan</p>
                            </div>
                        </div>
                        <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Tema / Nama Event -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Tema / Nama Event Acara Kampus <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required
                            placeholder="Contoh: BSI Flash 2026 / Workshop Guru BK / Seminar Nasional AI"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-bold text-slate-800">
                    </div>

                    <!-- Tanggal & Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Tanggal Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="event_date" required min="{{ now()->toDateString() }}"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-semibold text-slate-800">
                            <p class="text-[10px] text-slate-400 mt-1">Pilih tanggal acara akan diselenggarakan</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Waktu / Jam Acara <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="event_time" required
                                placeholder="Contoh: 09:00 - 12:00 WIB"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-semibold text-slate-800">
                        </div>
                    </div>

                    <!-- Lokasi Acara -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Lokasi / Tempat Acara <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="location" required
                            placeholder="Contoh: Aula UBSI Kampus Karawang / Gedung Pertemuan..."
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-semibold text-slate-800">
                    </div>

                    <!-- Target Peserta & Kuota -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Target Peserta</label>
                            <input type="text" name="target_audience"
                                placeholder="Contoh: Siswa Kelas 12 / Guru BK"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi / Kuota Peserta</label>
                            <input type="number" name="quota" min="0"
                                placeholder="Contoh: 200"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 text-xs">
                        </div>
                    </div>

                    <!-- Kampus Penugasan -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kampus Penyelenggara</label>
                        <select name="campus_id"
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 font-semibold text-slate-800">
                            @if(auth()->user()->canAccessAllCampuses())
                                <option value="">-- Semua Kampus (Pusat / Terbuka) --</option>
                            @endif
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}" {{ ($activeCampus?->id === $c->id) ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Keterangan / Deskripsi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Rincian Acara</label>
                        <textarea name="description" rows="3"
                            placeholder="Tuliskan benefit, pembicara, fasilitas, atau instruksi khusus untuk marketing saat menyebarkan undangan..."
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-md shadow-amber-500/30 transition">
                            Simpan Rencana Event
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Rencana Event -->
        <div x-show="showEditModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div @click.away="showEditModal = false"
                class="bg-white rounded-3xl max-w-xl w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                <form :action="editForm.update_url" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                                <i data-lucide="edit-3" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-extrabold text-slate-900">Ubah Rencana Event</h3>
                                <p class="text-xs text-slate-500" x-text="editForm.title"></p>
                            </div>
                        </div>
                        <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Tema / Nama Event -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Tema / Nama Event Acara Kampus <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" x-model="editForm.title" required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-bold text-slate-800">
                    </div>

                    <!-- Tanggal & Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Tanggal Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="event_date" x-model="editForm.event_date" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-800">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">
                                Waktu / Jam Acara <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="event_time" x-model="editForm.event_time" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-800">
                        </div>
                    </div>

                    <!-- Lokasi Acara -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Lokasi / Tempat Acara <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="location" x-model="editForm.location" required
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-800">
                    </div>

                    <!-- Target & Kuota -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Target Peserta</label>
                            <input type="text" name="target_audience" x-model="editForm.target_audience"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Estimasi / Kuota Peserta</label>
                            <input type="number" name="quota" x-model="editForm.quota" min="0"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 text-xs">
                        </div>
                    </div>

                    <!-- Status & Kampus -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status Event</label>
                            <select name="status" x-model="editForm.status" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-800">
                                <option value="aktif">Aktif (Tersedia untuk Undangan)</option>
                                <option value="selesai">Selesai (Kadaluarsa)</option>
                                <option value="dibatalkan">Dibatalkan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kampus</label>
                            <select name="campus_id" x-model="editForm.campus_id"
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 font-semibold text-slate-800">
                                <option value="">-- Semua Kampus --</option>
                                @foreach($campuses as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan / Rincian Acara</label>
                        <textarea name="description" x-model="editForm.description" rows="3"
                            class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showEditModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white shadow-md shadow-indigo-600/30 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function eventPlanManager() {
            return {
                showCreateModal: false,
                showEditModal: false,
                editForm: {
                    id: null,
                    title: '',
                    event_date: '',
                    event_time: '',
                    location: '',
                    target_audience: '',
                    quota: '',
                    description: '',
                    status: 'aktif',
                    campus_id: '',
                    update_url: '',
                },
                openCreateModal() {
                    this.showCreateModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },
                openEditModal(data) {
                    this.editForm = Object.assign({}, data);
                    this.showEditModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
