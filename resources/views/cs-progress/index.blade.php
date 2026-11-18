<x-app-layout title="Kontrol Progres Customer Service">
    <div class="space-y-6">
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-brand-navy to-teal-950 rounded-2xl p-6 text-white shadow-xl border border-slate-800 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-44 h-44 bg-teal-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 relative z-10">
                <div>
                    <div class="flex items-center gap-2 flex-wrap mb-1.5">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-teal-500/20 text-teal-300 border border-teal-400/30 uppercase tracking-wider">
                            <i data-lucide="activity" class="w-3 h-3 inline mr-1"></i>Kontrol &amp; Evaluasi CS
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-slate-200 border border-white/10">
                            {{ $activeCampus ? $activeCampus->name : 'Semua Kampus' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/15 text-amber-300 border border-amber-400/25">
                            {{ $activePeriod ? 'Periode ' . $activePeriod->name : 'Semua Periode' }}
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">Kontrol Progres &amp; Kinerja Customer Service</h1>
                    <p class="text-xs text-slate-300 mt-1 max-w-2xl">
                        Pantau beban ploting, kecepatan tindak lanjut, konversi pendaftaran aplikasi, dan riwayat interaksi harian seluruh tim Customer Service secara real-time.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('student-follow-ups.distribute.page') }}"
                        class="px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-extrabold text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-1.5">
                        <i data-lucide="git-branch" class="w-4 h-4"></i>
                        <span>Ploting &amp; Bagi Data</span>
                    </a>
                    <a href="{{ route('student-follow-ups.index') }}"
                        class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs border border-white/15 transition flex items-center gap-1.5">
                        <i data-lucide="user-check" class="w-4 h-4 text-emerald-300"></i>
                        <span>Daftar Follow Up</span>
                    </a>
                    <a href="{{ route('cs-progress.export', request()->query()) }}"
                        class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-1.5">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Ekspor Excel</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert jika masih ada data siswa belum diploting -->
        @if($summary['unassigned_count'] > 0)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                        <i data-lucide="bell-ring" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="text-xs font-extrabold text-amber-950">
                            Terdapat {{ number_format($summary['unassigned_count']) }} siswa belum diploting ke petugas Customer Service!
                        </p>
                        <p class="text-[11px] text-amber-800 mt-0.5">
                            Segera lakukan distribusi data agar calon mahasiswa cepat dihubungi dan progres follow-up tetap terjaga.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('student-follow-ups.index', ['assigned_to' => 'unassigned']) }}"
                        class="px-3.5 py-2 rounded-xl bg-white hover:bg-amber-100 text-amber-900 border border-amber-300 font-bold text-xs transition">
                        Lihat Belum Diploting
                    </a>
                    <a href="{{ route('student-follow-ups.distribute.page') }}"
                        class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-sm transition">
                        Ploting Sekarang &rarr;
                    </a>
                </div>
            </div>
        @endif

        <!-- 5 Summary KPI Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Petugas Aktif</span>
                    <span class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                        <i data-lucide="headset" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['officers_count']) }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">
                    {{ $roleFilter === 'cs' ? 'Divisi Customer Service' : ($roleFilter === 'marketing' ? 'Divisi Marketing' : 'Seluruh Tim Follow Up') }}
                </p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Total Ploting</span>
                    <span class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($summary['total_assigned']) }}</p>
                <p class="text-[10px] text-rose-600 font-semibold mt-0.5">
                    {{ number_format($summary['total_pending']) }} belum dihubungi
                </p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Progres Follow Up</span>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-blue-700 mt-2">{{ $summary['avg_progress_rate'] }}%</p>
                <p class="text-[10px] text-slate-500 mt-0.5">
                    {{ number_format($summary['total_contacted']) }} siswa sudah dihubungi
                </p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400">Interaksi Hari Ini</span>
                    <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i data-lucide="message-square-text" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-purple-700 mt-2">{{ number_format($summary['today_interactions']) }}</p>
                <p class="text-[10px] text-slate-500 mt-0.5">Log follow-up tercatat hari ini</p>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-emerald-200/80 shadow-soft bg-gradient-to-br from-white to-emerald-50/40 col-span-2 md:col-span-1">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-extrabold uppercase tracking-wider text-emerald-700">Daftar Aplikasi</span>
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($summary['total_daftar_aplikasi']) }}</p>
                <p class="text-[10px] text-emerald-700 font-semibold mt-0.5">
                    Konversi {{ $summary['avg_conversion_rate'] }}% dari dihubungi
                </p>
            </div>
        </div>

        <!-- Filter Panel -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-soft">
            <form method="GET" action="{{ route('cs-progress.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs items-end">
                <div>
                    <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Kelompok Tim</label>
                    <select name="role_filter" onchange="this.form.submit()" class="w-full py-2 px-3 rounded-xl border border-slate-300 bg-white font-semibold focus:ring-2 focus:ring-teal-500">
                        <option value="cs" {{ $roleFilter === 'cs' ? 'selected' : '' }}>Tim Customer Service (CS &amp; Koord. CS)</option>
                        <option value="all" {{ $roleFilter === 'all' ? 'selected' : '' }}>Semua Petugas Follow Up (CS + Marketing)</option>
                        <option value="marketing" {{ $roleFilter === 'marketing' ? 'selected' : '' }}>Tim Marketing Saja</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Cari Petugas</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Nama, email, HP..."
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Log Interaksi Dari</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500">
                </div>

                <div>
                    <label class="block text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-1">Log Interaksi Sampai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition flex items-center justify-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    @if($search || $dateFrom || $dateTo || $roleFilter !== 'cs')
                        <a href="{{ route('cs-progress.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Utama Kontrol Progres Per Petugas -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i data-lucide="bar-chart-horizontal" class="w-4 h-4 text-teal-600"></i>
                        <span>Rincian Progres &amp; Beban Kerja Per Petugas</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Klik tombol aksi di kanan untuk langsung memeriksa daftar siswa atau tindak lanjut yang tertunda.</p>
                </div>
                <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 self-start sm:self-auto">
                    {{ $officers->count() }} Petugas Ditampilkan
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-900 text-white text-[10px] font-extrabold uppercase tracking-wider">
                            <th class="py-3 px-4">Petugas &amp; Jabatan</th>
                            <th class="py-3 px-4 w-56">Progres Follow Up</th>
                            <th class="py-3 px-4 text-center">No Respon</th>
                            <th class="py-3 px-4 text-center">Respon</th>
                            <th class="py-3 px-4 text-center">Berminat</th>
                            <th class="py-3 px-4 text-center">Daftar Aplikasi</th>
                            <th class="py-3 px-4">Aktivitas &amp; Prospek</th>
                            <th class="py-3 px-4 text-right">Kontrol Langsung</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse($officers as $item)
                            @php
                                $u = $item['user'];
                                $rate = $item['progress_rate'];
                                $barColor = $rate >= 80 ? 'bg-emerald-500' : ($rate >= 40 ? 'bg-amber-500' : 'bg-rose-500');
                                $textColor = $rate >= 80 ? 'text-emerald-700' : ($rate >= 40 ? 'text-amber-700' : 'text-rose-700');
                                $isKoordCs = $u->role?->name === 'Koordinator Customer Service';
                                $isCs = $u->role?->name === 'Customer Service';
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Petugas -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl {{ $isKoordCs ? 'bg-teal-100 text-teal-800' : ($isCs ? 'bg-cyan-100 text-cyan-800' : 'bg-indigo-100 text-indigo-800') }} font-extrabold flex items-center justify-center text-xs shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-extrabold text-slate-900">{{ $u->name }}</div>
                                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                                <span class="inline-flex px-2 py-0.2 rounded text-[10px] font-bold border {{ $isKoordCs ? 'bg-teal-50 text-teal-800 border-teal-200' : ($isCs ? 'bg-cyan-50 text-cyan-800 border-cyan-200' : 'bg-blue-50 text-blue-700 border-blue-200') }}">
                                                    {{ $u->role?->name ?? '-' }}
                                                </span>
                                                @if($u->campus)
                                                    <span class="text-[10px] text-slate-400 font-semibold">{{ $u->campus->code }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Progres Bar -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-between text-[11px] mb-1">
                                        <span class="font-bold text-slate-700">
                                            {{ $item['contacted'] }} / {{ $item['total_assigned'] }} siswa
                                        </span>
                                        <span class="font-black {{ $textColor }}">{{ $rate }}%</span>
                                    </div>
                                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full {{ $barColor }} rounded-full transition-all" style="width: {{ min(100, $rate) }}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between mt-1 text-[10px]">
                                        <span class="{{ $item['belum_dihubungi'] > 0 ? 'text-rose-600 font-bold' : 'text-emerald-600 font-semibold' }}">
                                            {{ $item['belum_dihubungi'] > 0 ? $item['belum_dihubungi'] . ' belum dihubungi' : 'Semua sudah dihubungi' }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Kriteria -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 font-bold text-xs">
                                        {{ $item['no_respon'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 font-bold text-xs">
                                        {{ $item['respon'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 font-bold text-xs">
                                        {{ $item['berminat'] }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex flex-col items-center">
                                        <span class="px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-800 font-extrabold text-xs">
                                            {{ $item['daftar_aplikasi'] }}
                                        </span>
                                        <span class="text-[9px] text-emerald-700 font-bold mt-0.5">
                                            {{ $item['conversion_rate'] }}% konv.
                                        </span>
                                    </div>
                                </td>

                                <!-- Aktivitas & Prospek -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-1 text-[11px]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-800">{{ $item['total_interactions'] }}x interaksi</span>
                                            @if($item['today_interactions'] > 0)
                                                <span class="px-1.5 py-0.2 rounded bg-emerald-100 text-emerald-800 text-[9px] font-extrabold">
                                                    +{{ $item['today_interactions'] }} hari ini
                                                </span>
                                            @else
                                                <span class="px-1.5 py-0.2 rounded bg-slate-100 text-slate-500 text-[9px] font-semibold">
                                                    0 hari ini
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-500">
                                            Prospek: <strong class="text-slate-700">{{ $item['prospects_count'] }}</strong> &bull;
                                            Maba: <strong class="text-amber-700">{{ $item['new_students_count'] }}</strong>
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            @if($item['last_activity_at'])
                                                Terakhir: {{ $item['last_activity_at']->diffForHumans() }} ({{ $item['last_activity_at']->format('d/m H:i') }})
                                            @else
                                                <span class="italic text-rose-400">Belum ada aktivitas</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Aksi Kontrol -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="inline-flex flex-col sm:flex-row items-end sm:items-center gap-1.5">
                                        <a href="{{ route('student-follow-ups.index', ['assigned_to' => $u->id]) }}"
                                            class="px-2.5 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-[11px] font-bold transition inline-flex items-center gap-1">
                                            <i data-lucide="eye" class="w-3 h-3"></i>
                                            <span>Lihat Siswa</span>
                                        </a>
                                        @if($item['belum_dihubungi'] > 0)
                                            <a href="{{ route('student-follow-ups.index', ['assigned_to' => $u->id, 'status' => 'belum_dihubungi']) }}"
                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-[11px] font-bold transition inline-flex items-center gap-1">
                                                <i data-lucide="clock" class="w-3 h-3"></i>
                                                <span>Pending ({{ $item['belum_dihubungi'] }})</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400 text-xs">
                                    <i data-lucide="users" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                    <p class="font-bold text-slate-600">Belum ada petugas Customer Service ditemukan pada filter ini.</p>
                                    <p class="text-[11px] mt-1">Tambahkan akun petugas melalui menu <strong>User &amp; Akun</strong> atau ubah filter kelompok tim di atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Feed Log Interaksi Terbaru Tim CS -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                        <i data-lucide="history" class="w-4 h-4 text-indigo-600"></i>
                        <span>Log Aktivitas Follow Up Terbaru Tim</span>
                    </h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">20 catatan tindak lanjut terakhir untuk mengontrol kualitas komunikasi dan kecepatan respon tim CS.</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentActivities as $act)
                    @php
                        $st = $act->studentQuestionnaire;
                        $statusBadge = match ($act->status) {
                            'daftar_aplikasi', 'closing' => 'bg-emerald-100 text-emerald-800',
                            'berminat' => 'bg-amber-100 text-amber-800',
                            'respon' => 'bg-blue-100 text-blue-800',
                            'no_respon' => 'bg-rose-100 text-rose-800',
                            default => 'bg-slate-100 text-slate-700',
                        };
                        $statusText = match ($act->status) {
                            'daftar_aplikasi', 'closing' => 'Daftar Aplikasi',
                            'berminat' => 'Berminat',
                            'respon' => 'Respon',
                            'no_respon' => 'No Respon',
                            default => ucfirst(str_replace('_', ' ', (string) $act->status)),
                        };
                    @endphp
                    <div class="p-4 hover:bg-slate-50/70 transition flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 flex-wrap text-xs">
                                <span class="font-extrabold text-slate-900">{{ $act->user?->name ?? 'Petugas' }}</span>
                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-semibold">
                                    {{ $act->user?->role?->name ?? '-' }}
                                </span>
                                <span class="text-slate-400">&rarr;</span>
                                <span class="font-bold text-indigo-700">{{ $st?->student_name ?? 'Siswa' }}</span>
                                @if($st?->school)
                                    <span class="text-[11px] text-slate-500">({{ $st->school->name }})</span>
                                @endif
                            </div>
                            @if($act->notes)
                                <p class="text-xs text-slate-600 bg-slate-50 border border-slate-100 rounded-lg px-3 py-1.5 italic">
                                    "{{ $act->notes }}"
                                </p>
                            @endif
                        </div>

                        <div class="flex sm:flex-col items-center sm:items-end gap-1.5 shrink-0">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold {{ $statusBadge }}">
                                {{ $statusText }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-semibold">
                                {{ $act->created_at?->format('d/m/Y H:i') }} ({{ $act->created_at?->diffForHumans() }})
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        Belum ada catatan interaksi follow up pada rentang filter ini.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
