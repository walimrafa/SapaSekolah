<x-app-layout title="Follow Up Sekolah & Fix Jadwal Sosialisasi">
    <div x-data="{ showAddModal: false, showScheduleModal: false, selectedFollowUp: null }">
        <!-- Page Header -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-gradient-to-tr from-brand-600 to-amber-500 text-white shadow-md">
                        <i data-lucide="phone-call" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Follow Up Sekolah & Fix Jadwal Sosialisasi</h1>
                        <p class="text-xs text-slate-500 mt-0.5">Tindak lanjut data kunjungan lapangan untuk koordinasi dan penguncian jadwal sosialisasi kampus UBSI.</p>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <!-- Sync button from Visits -->
                <form method="POST" action="{{ route('follow-ups.sync') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs shadow-soft flex items-center gap-2 transition duration-150" title="Sinkronkan data kunjungan terbaru">
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-brand-600"></i>
                        <span>Sinkronkan Kunjungan</span>
                    </button>
                </form>

                <!-- Download Excel Dropdown / Button -->
                <div x-data="{ exportOpen: false }" class="relative inline-block text-left">
                    <button @click="exportOpen = !exportOpen" type="button" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2 transition cursor-pointer">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Unduh Excel</span>
                        <i data-lucide="chevron-down" class="w-3.5 h-3.5"></i>
                    </button>
                    <div x-show="exportOpen" @click.away="exportOpen = false" x-cloak class="absolute right-0 mt-2 w-64 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 border border-slate-100 z-50 py-1.5 divide-y divide-slate-100">
                        <a href="{{ route('follow-ups.export', array_merge(request()->query(), ['type' => 'fixed'])) }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-emerald-50 hover:text-emerald-800 font-semibold transition">
                            <span class="p-1.5 rounded-lg bg-emerald-100 text-emerald-700">
                                <i data-lucide="calendar-check-2" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800">Unduh Jadwal FIX (Excel)</p>
                                <p class="text-[10px] text-slate-400">Khusus sekolah yang sudah FIX</p>
                            </div>
                        </a>
                        <a href="{{ route('follow-ups.export', request()->query()) }}" class="flex items-center gap-3 px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 font-semibold transition">
                            <span class="p-1.5 rounded-lg bg-brand-50 text-brand-600">
                                <i data-lucide="table" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <p class="font-bold text-slate-800">Unduh Semua Data (Excel)</p>
                                <p class="text-[10px] text-slate-400">Semua riwayat follow up & antrean</p>
                            </div>
                        </a>
                    </div>
                </div>

                <button @click="showAddModal = true" type="button"
                    class="px-4 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition cursor-pointer">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                    <span>Tambah Follow Up Manual</span>
                </button>
            </div>
        </div>

        <!-- Alert Notification -->
        @if(session('toast'))
            <div class="p-4 mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-3 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 shrink-0"></i>
                <span>{{ session('toast')['message'] ?? 'Data berhasil diperbarui.' }}</span>
            </div>
        @endif

        <!-- Combobox Kategori Follow Up Kunjungan -->
        <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-soft mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <label for="combobox_agenda_category" class="block text-xs font-extrabold text-slate-900">
                            Pilih Kategori Follow Up Kunjungan
                        </label>
                        <p class="text-[11px] text-slate-500">Pilih agenda kunjungan yang ingin difollow up agar antrean tampil sesuai kategori masing-masing</p>
                    </div>
                </div>
                <div class="w-full sm:w-80">
                    <form method="GET" action="{{ route('follow-ups.index') }}" id="formAgendaCategory">
                        @foreach(request()->except('agenda_category', 'page') as $k => $v)
                            @if(is_string($v) && $v !== '')
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        <select id="combobox_agenda_category" name="agenda_category" onchange="document.getElementById('formAgendaCategory').submit()"
                            class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-500/50 bg-white font-extrabold text-xs text-slate-800 shadow-xs focus:border-brand-600 focus:ring-2 focus:ring-brand-500/20 transition cursor-pointer">
                            <option value="">-- Semua Kategori Agenda ({{ $categoryCounts['all'] ?? $totalCount }} Sekolah) --</option>
                            <option value="penjadwalan_sosialisasi" {{ request('agenda_category') === 'penjadwalan_sosialisasi' ? 'selected' : '' }}>
                                1. Penjadwalan Sosialisasi ({{ $categoryCounts['penjadwalan_sosialisasi'] ?? 0 }})
                            </option>
                            <option value="mengantar_undangan" {{ request('agenda_category') === 'mengantar_undangan' ? 'selected' : '' }}>
                                2. Mengantar Undangan Acara ({{ $categoryCounts['mengantar_undangan'] ?? 0 }})
                            </option>
                            <option value="kunjungan_lainnya" {{ request('agenda_category') === 'kunjungan_lainnya' ? 'selected' : '' }}>
                                3. Kunjungan Lainnya ({{ $categoryCounts['kunjungan_lainnya'] ?? 0 }})
                            </option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Info Hint Berdasarkan Kategori yang Dipilih -->
            <div class="mt-3 pt-2.5 border-t border-slate-100">
                @if(request('agenda_category') === 'penjadwalan_sosialisasi')
                    <div class="text-[11px] text-brand-700 bg-brand-50/80 px-3 py-1.5 rounded-xl border border-brand-200/60 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-brand-600 shrink-0"></i>
                            <span><strong>Mode Penjadwalan Sosialisasi:</strong> Menampilkan antrean sekolah untuk konfirmasi target &amp; penguncian jadwal FIX sosialisasi kampus.</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-brand-100 text-brand-800 shrink-0">{{ $totalCount }} Antrean</span>
                    </div>
                @elseif(request('agenda_category') === 'mengantar_undangan')
                    <div class="text-[11px] text-amber-800 bg-amber-50/80 px-3 py-1.5 rounded-xl border border-amber-200/60 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="mail" class="w-4 h-4 text-amber-600 shrink-0"></i>
                            <span><strong>Mode Mengantar Undangan:</strong> Menampilkan antrean sekolah untuk tindak lanjut konfirmasi kehadiran undangan acara (Akan Hadir / Minta Ijin Dulu / Tidak Bisa).</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 shrink-0">{{ $totalCount }} Antrean</span>
                    </div>
                @elseif(request('agenda_category') === 'kunjungan_lainnya')
                    <div class="text-[11px] text-purple-800 bg-purple-50/80 px-3 py-1.5 rounded-xl border border-purple-200/60 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="clipboard-list" class="w-4 h-4 text-purple-600 shrink-0"></i>
                            <span><strong>Mode Kunjungan Lainnya:</strong> Menampilkan antrean tindak lanjut hasil silaturahmi &amp; audiensi dinas sekolah.</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 shrink-0">{{ $totalCount }} Antrean</span>
                    </div>
                @else
                    <div class="text-[11px] text-slate-600 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/80 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <i data-lucide="layers" class="w-4 h-4 text-slate-500 shrink-0"></i>
                            <span><strong>Semua Kategori:</strong> Menampilkan seluruh antrean follow up kunjungan dari semua kategori agenda.</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-800 shrink-0">{{ $totalCount }} Antrean</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            @if(request('agenda_category') === 'mengantar_undangan')
                <!-- Card 1: Total Undangan -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Undangan</span>
                        <i data-lucide="mail" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalCount) }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Undangan Diantar</p>
                </div>

                <!-- Card 2: Akan Hadir -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-500/5 border border-emerald-300 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Akan Hadir</span>
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($completed->count()) }}</p>
                    <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Konfirmasi Hadir di Acara</p>
                </div>

                <!-- Card 3: Minta Ijin Dulu -->
                <div class="p-4 rounded-2xl bg-white border border-blue-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Minta Ijin Dulu</span>
                        <i data-lucide="clock" class="w-4 h-4 text-blue-500"></i>
                    </div>
                    <p class="text-2xl font-black text-blue-600 mt-2">{{ number_format($progress->count()) }}</p>
                    <p class="text-[11px] text-blue-500/80 mt-0.5">Proses Ijin Kepala Sekolah</p>
                </div>

                <!-- Card 4: Menunggu Respon -->
                <div class="p-4 rounded-2xl bg-white border border-amber-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu Respon</span>
                        <i data-lucide="help-circle" class="w-4 h-4 text-amber-500"></i>
                    </div>
                    <p class="text-2xl font-black text-amber-600 mt-2">{{ number_format($pending->count()) }}</p>
                    <p class="text-[11px] text-amber-500/80 mt-0.5">Belum Dikonfirmasi</p>
                </div>

                <!-- Card 5: Tidak Bisa Hadir -->
                <div class="p-4 rounded-2xl bg-white border border-rose-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-rose-700 uppercase tracking-wider">Tidak Bisa Hadir</span>
                        <i data-lucide="x-circle" class="w-4 h-4 text-rose-500"></i>
                    </div>
                    <p class="text-2xl font-black text-rose-700 mt-2">{{ number_format($failed->count()) }}</p>
                    <p class="text-[11px] text-rose-500/80 mt-0.5">Berhalangan Hadir</p>
                </div>
            @elseif(request('agenda_category') === 'kunjungan_lainnya')
                <!-- Card 1: Total Kunjungan Dinas -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kunjungan</span>
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalCount) }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Silaturahmi / Audiensi</p>
                </div>

                <!-- Card 2: Tindak Lanjut Selesai -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-500/5 border border-emerald-300 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Tuntas</span>
                        <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($completed->count()) }}</p>
                    <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Tindak Lanjut Selesai</p>
                </div>

                <!-- Card 3: Sedang Diproses -->
                <div class="p-4 rounded-2xl bg-white border border-blue-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Diproses</span>
                        <i data-lucide="refresh-cw" class="w-4 h-4 text-blue-500"></i>
                    </div>
                    <p class="text-2xl font-black text-blue-600 mt-2">{{ number_format($progress->count()) }}</p>
                    <p class="text-[11px] text-blue-500/80 mt-0.5">Sedang Koordinasi / Penyiapan</p>
                </div>

                <!-- Card 4: Perlu Tindak Lanjut -->
                <div class="p-4 rounded-2xl bg-white border border-amber-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Perlu Follow-Up</span>
                        <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                    </div>
                    <p class="text-2xl font-black text-amber-600 mt-2">{{ number_format($pending->count()) }}</p>
                    <p class="text-[11px] text-amber-500/80 mt-0.5">Baru Masuk Kunjungan</p>
                </div>

                <!-- Card 5: Ditutup / Selesai -->
                <div class="p-4 rounded-2xl bg-white border border-slate-300 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Ditutup</span>
                        <i data-lucide="archive" class="w-4 h-4 text-slate-500"></i>
                    </div>
                    <p class="text-2xl font-black text-slate-700 mt-2">{{ number_format($failed->count()) }}</p>
                    <p class="text-[11px] text-slate-500/80 mt-0.5">Tanpa Lanjutan Tambahan</p>
                </div>
            @else
                <!-- Total Follow Up -->
                <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Follow Up</span>
                        <i data-lucide="building-2" class="w-4 h-4 text-slate-400"></i>
                    </div>
                    <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($totalCount) }}</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Sekolah Terdata</p>
                </div>

                <!-- Jadwal FIX (Completed) -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-500/5 border border-emerald-300 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Jadwal FIX</span>
                        <i data-lucide="calendar-check-2" class="w-4 h-4 text-emerald-600"></i>
                    </div>
                    <p class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($fixedScheduleCount) }}</p>
                    <p class="text-[11px] font-bold text-emerald-600 mt-0.5">Terkonfirmasi Sekolah</p>
                </div>

                <!-- Dalam Koordinasi (Progress) -->
                <div class="p-4 rounded-2xl bg-white border border-blue-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Koordinasi</span>
                        <i data-lucide="message-square" class="w-4 h-4 text-blue-500"></i>
                    </div>
                    <p class="text-2xl font-black text-blue-600 mt-2">{{ number_format($progressCount) }}</p>
                    <p class="text-[11px] text-blue-500/80 mt-0.5">Sedang Diskusi Jadwal</p>
                </div>

                <!-- Menunggu Konfirmasi (Pending) -->
                <div class="p-4 rounded-2xl bg-white border border-amber-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Menunggu</span>
                        <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                    </div>
                    <p class="text-2xl font-black text-amber-600 mt-2">{{ number_format($pendingCount) }}</p>
                    <p class="text-[11px] text-amber-500/80 mt-0.5">Baru Masuk Kunjungan</p>
                </div>

                <!-- Estimasi Audiens Terkunci -->
                <div class="p-4 rounded-2xl bg-white border border-purple-200 shadow-soft">
                    <div class="flex items-center justify-between">
                        <span class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Estimasi Audiens</span>
                        <i data-lucide="users" class="w-4 h-4 text-purple-500"></i>
                    </div>
                    <p class="text-2xl font-black text-purple-700 mt-2">{{ number_format($totalEstimatedAudience) }}</p>
                    <p class="text-[11px] text-purple-500/80 mt-0.5">Target Siswa Kelas 12</p>
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- PENGALIHAN DIAGRAM ANALISIS FOLLOW UP KE PUSAT LAPORAN -->
        <!-- ======================================================== -->
        <div class="mb-6 flex flex-col sm:flex-row items-center justify-between p-4 bg-gradient-to-r from-blue-50/90 via-indigo-50/70 to-blue-50/90 border border-blue-200/80 rounded-2xl shadow-soft gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-600 to-indigo-600 text-white flex items-center justify-center font-bold shadow-xs shrink-0">
                    <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="text-xs font-black text-slate-900">Grafik &amp; Diagram Analisis Follow Up Telah Dipusatkan ke Menu Laporan</h4>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold {{ $conversionRate > 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                            {{ $conversionRate }}% Jadwal FIX
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-500 mt-0.5">Seluruh visualisasi diagram timeline sosialisasi FIX, estimasi audiens siswa, sebaran kecamatan, dan capaian staf marketing kini disajikan pada Pusat Laporan &amp; Analitik.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-end shrink-0">
                <a href="{{ route('reports.index', ['category' => 'diagram']) }}" class="px-4 py-2 bg-brand-navy hover:bg-slate-900 text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center gap-1.5">
                    <i data-lucide="line-chart" class="w-4 h-4 text-amber-400"></i>
                    <span>Buka Laporan Diagram</span>
                </a>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft p-4 mb-6">
            <form method="GET" action="{{ route('follow-ups.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 text-xs">
                <input type="hidden" name="agenda_category" value="{{ request('agenda_category') }}">
                <!-- Search -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Cari Sekolah / PIC</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                        <input type="search" name="search" value="{{ request('search') }}" 
                               placeholder="Nama sekolah, PIC, kontak..." 
                               class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">
                        @if(request('agenda_category') === 'mengantar_undangan')
                            Status Respon Undangan
                        @elseif(request('agenda_category') === 'kunjungan_lainnya')
                            Status Tindak Lanjut
                        @else
                            Status Penetapan Jadwal
                        @endif
                    </label>
                    <select name="schedule_status" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold">
                        @if(request('agenda_category') === 'mengantar_undangan')
                            <option value="">Semua Respon Undangan</option>
                            <option value="fixed" {{ request('schedule_status') === 'fixed' ? 'selected' : '' }}>Akan Hadir (Terkonfirmasi)</option>
                            <option value="progress" {{ request('schedule_status') === 'progress' ? 'selected' : '' }}>Minta Ijin Dulu</option>
                            <option value="unfixed" {{ request('schedule_status') === 'unfixed' ? 'selected' : '' }}>Menunggu Respon</option>
                            <option value="failed" {{ request('schedule_status') === 'failed' ? 'selected' : '' }}>Tidak Bisa Hadir</option>
                        @elseif(request('agenda_category') === 'kunjungan_lainnya')
                            <option value="">Semua Status Tindak Lanjut</option>
                            <option value="fixed" {{ request('schedule_status') === 'fixed' ? 'selected' : '' }}>Tuntas / Selesai</option>
                            <option value="progress" {{ request('schedule_status') === 'progress' ? 'selected' : '' }}>Sedang Diproses</option>
                            <option value="unfixed" {{ request('schedule_status') === 'unfixed' ? 'selected' : '' }}>Perlu Tindak Lanjut</option>
                            <option value="failed" {{ request('schedule_status') === 'failed' ? 'selected' : '' }}>Ditutup / Selesai</option>
                        @else
                            <option value="">Semua Status Jadwal</option>
                            <option value="fixed" {{ request('schedule_status') === 'fixed' ? 'selected' : '' }}>Jadwal Sudah FIX Terkonfirmasi</option>
                            <option value="unfixed" {{ request('schedule_status') === 'unfixed' ? 'selected' : '' }}>Belum FIX (Menunggu/Koordinasi)</option>
                        @endif
                    </select>
                </div>

                <!-- Staf Marketing -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Staf Marketing PIC</label>
                    <select name="user_id" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        <option value="">Semua Staf Marketing</option>
                        @foreach($marketingUsers as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Metode Komunikasi -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Metode Kontak</label>
                    <select name="method" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        <option value="">Semua Metode</option>
                        <option value="WhatsApp" {{ request('method') === 'WhatsApp' ? 'selected' : '' }}>WhatsApp</option>
                        <option value="Telepon" {{ request('method') === 'Telepon' ? 'selected' : '' }}>Telepon Langsung</option>
                        <option value="Kunjungan Lapangan" {{ request('method') === 'Kunjungan Lapangan' ? 'selected' : '' }}>Kunjungan Langsung</option>
                        <option value="Email" {{ request('method') === 'Email' ? 'selected' : '' }}>Email</option>
                    </select>
                </div>

                <!-- Action Button -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    <a href="{{ route('follow-ups.export', request()->query()) }}" class="py-2 px-2.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 font-bold text-xs transition flex items-center justify-center gap-1" title="Unduh hasil filter ke Excel">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span class="hidden sm:inline">Excel</span>
                    </a>
                    @if(request()->anyFilled(['search', 'schedule_status', 'user_id', 'method', 'agenda_category']))
                        <a href="{{ route('follow-ups.index') }}" class="py-2 px-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @php
            $catKey = request('agenda_category', $selectedCategory ?? 'all');
            $columnConfigs = [
                'mengantar_undangan' => [
                    'col1' => ['title' => 'Menunggu Respon', 'badge' => 'Baru Masuk', 'border' => 'border-amber-200', 'header' => 'text-amber-800', 'badge_bg' => 'bg-amber-200/70 text-amber-900', 'dot' => 'bg-amber-500', 'empty_icon' => 'mail', 'empty' => 'Tidak ada undangan baru'],
                    'col2' => ['title' => 'Minta Ijin Dulu', 'badge' => 'Proses Ijin', 'border' => 'border-blue-200', 'header' => 'text-blue-800', 'badge_bg' => 'bg-blue-200/70 text-blue-900', 'dot' => 'bg-blue-500', 'empty_icon' => 'clock', 'empty' => 'Tidak ada koordinasi ijin'],
                    'col3' => ['title' => 'Akan Hadir', 'badge' => 'Terkonfirmasi', 'border' => 'border-emerald-300', 'header' => 'text-emerald-800', 'badge_bg' => 'bg-emerald-200/80 text-emerald-900', 'dot' => 'bg-emerald-500', 'empty_icon' => 'check-circle-2', 'empty' => 'Belum ada konfirmasi hadir'],
                    'col4' => ['title' => 'Tidak Bisa Hadir', 'badge' => 'Berhalangan', 'border' => 'border-rose-200', 'header' => 'text-rose-800', 'badge_bg' => 'bg-rose-200/70 text-rose-900', 'dot' => 'bg-rose-500', 'empty_icon' => 'x-circle', 'empty' => 'Tidak ada penolakan hadir'],
                ],
                'kunjungan_lainnya' => [
                    'col1' => ['title' => 'Perlu Tindak Lanjut', 'badge' => 'Baru Masuk', 'border' => 'border-amber-200', 'header' => 'text-amber-800', 'badge_bg' => 'bg-amber-200/70 text-amber-900', 'dot' => 'bg-amber-500', 'empty_icon' => 'clipboard-list', 'empty' => 'Tidak ada tindak lanjut baru'],
                    'col2' => ['title' => 'Sedang Diproses', 'badge' => 'Koordinasi', 'border' => 'border-blue-200', 'header' => 'text-blue-800', 'badge_bg' => 'bg-blue-200/70 text-blue-900', 'dot' => 'bg-blue-500', 'empty_icon' => 'refresh-cw', 'empty' => 'Tidak ada proses berjalan'],
                    'col3' => ['title' => 'Tindak Lanjut Selesai', 'badge' => 'Tuntas', 'border' => 'border-emerald-300', 'header' => 'text-emerald-800', 'badge_bg' => 'bg-emerald-200/80 text-emerald-900', 'dot' => 'bg-emerald-500', 'empty_icon' => 'check-check', 'empty' => 'Belum ada agenda tuntas'],
                    'col4' => ['title' => 'Ditutup / Selesai', 'badge' => 'Arsip', 'border' => 'border-slate-300', 'header' => 'text-slate-800', 'badge_bg' => 'bg-slate-200/80 text-slate-800', 'dot' => 'bg-slate-500', 'empty_icon' => 'archive', 'empty' => 'Tidak ada agenda ditutup'],
                ],
                'penjadwalan_sosialisasi' => [
                    'col1' => ['title' => 'Menunggu Konfirmasi', 'badge' => 'Baru Masuk', 'border' => 'border-amber-200', 'header' => 'text-amber-800', 'badge_bg' => 'bg-amber-200/70 text-amber-900', 'dot' => 'bg-amber-500', 'empty_icon' => 'inbox', 'empty' => 'Tidak ada antrean jadwal'],
                    'col2' => ['title' => 'Koordinasi Jadwal', 'badge' => 'Diskusi', 'border' => 'border-blue-200', 'header' => 'text-blue-800', 'badge_bg' => 'bg-blue-200/70 text-blue-900', 'dot' => 'bg-blue-500', 'empty_icon' => 'message-square', 'empty' => 'Tidak ada diskusi aktif'],
                    'col3' => ['title' => 'Jadwal FIX', 'badge' => 'Terkonfirmasi', 'border' => 'border-emerald-300', 'header' => 'text-emerald-800', 'badge_bg' => 'bg-emerald-200/80 text-emerald-900', 'dot' => 'bg-emerald-500', 'empty_icon' => 'calendar-check', 'empty' => 'Belum ada jadwal yang dikunci FIX'],
                    'col4' => ['title' => 'Belum Diizinkan', 'badge' => 'Batal', 'border' => 'border-rose-200', 'header' => 'text-rose-800', 'badge_bg' => 'bg-rose-200/70 text-rose-900', 'dot' => 'bg-rose-500', 'empty_icon' => 'calendar-x', 'empty' => 'Tidak ada jadwal dibatalkan'],
                ],
                'all' => [
                    'col1' => ['title' => 'Menunggu Konfirmasi', 'badge' => 'Baru Masuk', 'border' => 'border-amber-200', 'header' => 'text-amber-800', 'badge_bg' => 'bg-amber-200/70 text-amber-900', 'dot' => 'bg-amber-500', 'empty_icon' => 'inbox', 'empty' => 'Tidak ada data antrean'],
                    'col2' => ['title' => 'Koordinasi & Proses', 'badge' => 'Diskusi', 'border' => 'border-blue-200', 'header' => 'text-blue-800', 'badge_bg' => 'bg-blue-200/70 text-blue-900', 'dot' => 'bg-blue-500', 'empty_icon' => 'refresh-cw', 'empty' => 'Tidak ada diskusi aktif'],
                    'col3' => ['title' => 'FIX / Hadir / Selesai', 'badge' => 'Terkonfirmasi', 'border' => 'border-emerald-300', 'header' => 'text-emerald-800', 'badge_bg' => 'bg-emerald-200/80 text-emerald-900', 'dot' => 'bg-emerald-500', 'empty_icon' => 'check-circle-2', 'empty' => 'Belum ada data selesai'],
                    'col4' => ['title' => 'Batal / Tidak Hadir', 'badge' => 'Batal/Ditutup', 'border' => 'border-rose-200', 'header' => 'text-rose-800', 'badge_bg' => 'bg-rose-200/70 text-rose-900', 'dot' => 'bg-rose-500', 'empty_icon' => 'x-circle', 'empty' => 'Tidak ada data dibatalkan'],
                ]
            ];
            $col = $columnConfigs[$catKey] ?? $columnConfigs['all'];
        @endphp

        <!-- Kanban Grid (4 Columns) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Column 1: Pending -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border {{ $col['col1']['border'] }} flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-black uppercase {{ $col['col1']['header'] }} flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $col['col1']['dot'] }}"></span>
                        <span>{{ $col['col1']['title'] }} ({{ $pending->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold {{ $col['col1']['badge_bg'] }} px-2 py-0.5 rounded-full">{{ $col['col1']['badge'] }}</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($pending as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => $col['col1']['border']])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="{{ $col['col1']['empty_icon'] }}" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">{{ $col['col1']['empty'] }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 2: Progress -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border {{ $col['col2']['border'] }} flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-black uppercase {{ $col['col2']['header'] }} flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $col['col2']['dot'] }}"></span>
                        <span>{{ $col['col2']['title'] }} ({{ $progress->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold {{ $col['col2']['badge_bg'] }} px-2 py-0.5 rounded-full">{{ $col['col2']['badge'] }}</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($progress as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => $col['col2']['border']])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="{{ $col['col2']['empty_icon'] }}" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">{{ $col['col2']['empty'] }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 3: Completed / Fixed -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border {{ $col['col3']['border'] }} flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-black uppercase {{ $col['col3']['header'] }} flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $col['col3']['dot'] }}"></span>
                        <span>{{ $col['col3']['title'] }} ({{ $completed->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold {{ $col['col3']['badge_bg'] }} px-2 py-0.5 rounded-full">{{ $col['col3']['badge'] }}</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($completed as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => $col['col3']['border'] . ' bg-emerald-50/20'])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="{{ $col['col3']['empty_icon'] }}" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">{{ $col['col3']['empty'] }}</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Column 4: Failed / Batal -->
            <div class="bg-slate-100/80 p-4 rounded-2xl border {{ $col['col4']['border'] }} flex flex-col min-h-[500px]">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-200">
                    <h3 class="text-xs font-black uppercase {{ $col['col4']['header'] }} flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full {{ $col['col4']['dot'] }}"></span>
                        <span>{{ $col['col4']['title'] }} ({{ $failed->count() }})</span>
                    </h3>
                    <span class="text-[10px] font-bold {{ $col['col4']['badge_bg'] }} px-2 py-0.5 rounded-full">{{ $col['col4']['badge'] }}</span>
                </div>
                <div class="space-y-3 flex-1 overflow-y-auto">
                    @forelse($failed as $item)
                        @include('follow-ups._card', ['item' => $item, 'borderClass' => $col['col4']['border']])
                    @empty
                        <div class="text-center py-12 text-slate-400">
                            <i data-lucide="{{ $col['col4']['empty_icon'] }}" class="w-6 h-6 mx-auto mb-1 opacity-50"></i>
                            <p class="text-xs font-medium">{{ $col['col4']['empty'] }}</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- MODAL 1: FORM KUNCI & UPDATE JADWAL SOSIALISASI (FIX JADWAL) -->
        <div id="scheduleModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 rounded-xl bg-emerald-600 text-white">
                            <i data-lucide="calendar-check" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-sm tracking-tight">Kunci & Update Jadwal Sosialisasi</h3>
                            <p class="text-xs text-amber-400 mt-0.5" id="modalSchoolName">Nama Sekolah</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeScheduleModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="scheduleForm" method="POST" action="" class="p-6 space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <!-- Penetapan Status -->
                    <div>
                        <label class="block font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Status Tindak Lanjut Jadwal <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="formStatus" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                            <option value="pending">Menunggu Konfirmasi (Belum Pasti)</option>
                            <option value="progress">Sedang Koordinasi Jadwal (Diskusi Aktif)</option>
                            <option value="completed" class="text-emerald-700 font-black">Jadwal Sosialisasi FIX (Sudah Terkunci)</option>
                            <option value="failed">Belum Diizinkan / Batal</option>
                        </select>
                    </div>

                    <!-- Tanggal & Jam FIX -->
                    <div class="p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-emerald-900 text-xs flex items-center gap-1.5">
                                <i data-lucide="calendar" class="w-4 h-4 text-emerald-600"></i>
                                Tanggal & Waktu Pelaksanaan FIX
                            </span>
                            <label class="flex items-center gap-1.5 font-bold text-emerald-800 cursor-pointer text-[11px]">
                                <input type="checkbox" name="is_schedule_fixed" id="formIsFixed" value="1" class="text-emerald-600 rounded">
                                <span>Kunci Jadwal FIX</span>
                            </label>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-emerald-900 mb-1">Tanggal FIX</label>
                                <input type="date" name="fixed_date" id="formFixedDate" class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white font-bold text-emerald-800">
                            </div>
                            <div>
                                <label class="block font-bold text-emerald-900 mb-1">Jam / Waktu FIX</label>
                                <input type="text" name="fixed_time" id="formFixedTime" placeholder="Contoh: 08:30 WIB" class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white font-semibold">
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-emerald-900 mb-1">Ruangan / Tempat Sosialisasi</label>
                            <input type="text" name="socialization_location" id="formLocation" placeholder="Contoh: Ruang Audio Visual / Kelas 12" class="w-full px-3 py-2 rounded-xl border border-emerald-300 bg-white">
                        </div>
                    </div>

                    <!-- PIC Kontak Sekolah -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama PIC Sekolah</label>
                            <input type="text" name="pic_name" id="formPicName" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. HP / WhatsApp PIC</label>
                            <input type="text" name="pic_phone" id="formPicPhone" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <!-- Catatan Hasil Koordinasi -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Hasil Koordinasi & Ketentuan</label>
                        <textarea name="notes" id="formNotes" rows="2.5" placeholder="Contoh: Sekolah menyetujui tanggal 15 Nov jam 08:30 WIB di Aula, infocus disiapkan sekolah..." class="w-full px-3 py-2 rounded-xl border border-slate-300"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeScheduleModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Jadwal Sosialisasi</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL RESPO & KONFIRMASI KEHADIRAN UNDANGAN -->
        <div id="invitationModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 rounded-xl bg-amber-500 text-white">
                            <i data-lucide="mail-check" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-sm tracking-tight">Respon Kehadiran Undangan Acara</h3>
                            <p class="text-xs text-amber-400 mt-0.5" id="invitationModalSchoolName">Nama Sekolah</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeInvitationModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="invitationForm" method="POST" action="" class="p-6 space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="agenda_category" value="mengantar_undangan">
                    <input type="hidden" name="status" id="invitationStatus" value="pending">

                    <!-- Pilihan Respon Kehadiran -->
                    <div>
                        <label class="block font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Status Konfirmasi Kehadiran Sekolah <span class="text-rose-500">*</span>
                        </label>
                        <select name="invitation_attendance_status" id="formAttendanceStatus" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-800">
                            <option value="akan_hadir" class="text-emerald-700 font-bold">🟢 Akan Hadir (Konfirmasi Hadir di Acara)</option>
                            <option value="minta_ijin_dulu" class="text-blue-700 font-bold">🔵 Minta Ijin Dulu (Sedang Koordinasi Internal)</option>
                            <option value="tidak_bisa_hadir" class="text-rose-700 font-bold">🔴 Tidak Bisa Hadir (Berhalangan)</option>
                            <option value="belum_dikonfirmasi" class="text-slate-600">⚪ Belum Dikonfirmasi / Menunggu Respon</option>
                        </select>
                    </div>

                    <!-- Estimasi Jumlah Delegasi / Peserta -->
                    <div class="p-3.5 rounded-2xl bg-amber-50/80 border border-amber-200 space-y-2">
                        <label class="block font-bold text-amber-900 mb-1 flex items-center gap-1.5">
                            <i data-lucide="users" class="w-4 h-4 text-amber-600"></i>
                            Estimasi Jumlah Hadir (Guru / Siswa Delegasi)
                        </label>
                        <input type="number" name="target_audience_count" id="formAttendanceCount" min="0" placeholder="Contoh: 5 (Isi perkiraan jika akan hadir)" class="w-full px-3 py-2 rounded-xl border border-amber-300 bg-white font-bold text-amber-900">
                    </div>

                    <!-- PIC Kontak Penerima Undangan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama PIC Penerima</label>
                            <input type="text" name="pic_name" id="formInvitationPicName" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. HP / WhatsApp PIC</label>
                            <input type="text" name="pic_phone" id="formInvitationPicPhone" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <!-- Catatan Respon Sekolah -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Respon / Pesan dari Pihak Sekolah</label>
                        <textarea name="notes" id="formInvitationNotes" rows="2.5" placeholder="Contoh: Surat undangan telah diteruskan ke Kepala Sekolah, konfirmasi delegasi diinfokan H-3..." class="w-full px-3 py-2 rounded-xl border border-slate-300"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeInvitationModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Respon Kehadiran</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL TINDAK LANJUT KUNJUNGAN LAINNYA -->
        <div id="generalVisitModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all">
                <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="p-1.5 rounded-xl bg-purple-600 text-white">
                            <i data-lucide="clipboard-edit" class="w-4 h-4"></i>
                        </span>
                        <div>
                            <h3 class="font-extrabold text-sm tracking-tight">Tindak Lanjut Kunjungan &amp; Audiensi</h3>
                            <p class="text-xs text-purple-300 mt-0.5" id="generalVisitModalSchoolName">Nama Sekolah</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeGeneralVisitModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form id="generalVisitForm" method="POST" action="" class="p-6 space-y-4 text-xs">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="agenda_category" value="kunjungan_lainnya">

                    <!-- Status Tindak Lanjut -->
                    <div>
                        <label class="block font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                            Status Progres Tindak Lanjut <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" id="formGeneralStatus" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-800">
                            <option value="pending" class="text-amber-700 font-bold">🟡 Perlu Tindak Lanjut (Baru Masuk / Ada Arahan)</option>
                            <option value="progress" class="text-blue-700 font-bold">🔵 Sedang Diproses (Koordinasi / Penyiapan Berkas)</option>
                            <option value="completed" class="text-emerald-700 font-bold">🟢 Tindak Lanjut Selesai (Tuntas / Selesai)</option>
                            <option value="failed" class="text-slate-600">⚪ Ditutup / Selesai Tanpa Lanjutan</option>
                        </select>
                    </div>

                    <!-- Pihak Sekolah yang Ditemui -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama Pihak Sekolah</label>
                            <input type="text" name="pic_name" id="formGeneralPicName" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                            <input type="text" name="pic_phone" id="formGeneralPicPhone" class="w-full px-3 py-2 rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <!-- Catatan Hasil Audiensi -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Hasil Audiensi / Tindak Lanjut</label>
                        <textarea name="notes" id="formGeneralNotes" rows="3" placeholder="Tuliskan catatan progres atau hasil arahan dari kunjungan ke sekolah..." class="w-full px-3 py-2 rounded-xl border border-slate-300"></textarea>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button type="button" onclick="closeGeneralVisitModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl shadow-md transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Tindak Lanjut</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: FORM TAMBAH FOLLOW UP MANUAL -->
        <div x-show="showAddModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
            style="display: none;">
            
            <div @click.away="showAddModal = false" class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-slate-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="phone-outgoing" class="w-5 h-5 text-amber-400"></i>
                        <h3 class="font-extrabold text-sm tracking-tight">Tambah Follow Up Baru</h3>
                    </div>
                    <button @click="showAddModal = false" type="button" class="text-slate-400 hover:text-white p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('follow-ups.store') }}" class="p-6 space-y-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kategori Agenda Kunjungan <span class="text-rose-500">*</span></label>
                        <select name="agenda_category" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                            <option value="penjadwalan_sosialisasi">1. Penjadwalan Sosialisasi</option>
                            <option value="mengantar_undangan">2. Mengantar Undangan Acara</option>
                            <option value="kunjungan_lainnya">3. Kunjungan Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sekolah Tujuan <span class="text-rose-500">*</span></label>
                        <select name="school_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                            <option value="">-- Pilih Sekolah --</option>
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} (Kec. {{ $s->district ? $s->district->name : '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Staf Marketing PIC <span class="text-rose-500">*</span></label>
                            <select name="user_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                @foreach($marketingUsers as $u)
                                    <option value="{{ $u->id }}" {{ auth()->id() == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tanggal Follow Up <span class="text-rose-500">*</span></label>
                            <input type="date" name="followup_date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Metode Komunikasi <span class="text-rose-500">*</span></label>
                            <select name="method" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                <option value="WhatsApp" selected>WhatsApp Chat</option>
                                <option value="Telepon">Telepon Langsung</option>
                                <option value="Kunjungan Lapangan">Kunjungan Langsung</option>
                                <option value="Email">Email Resmi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Follow Up <span class="text-rose-500">*</span></label>
                            <select name="status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                                <option value="pending" selected>Menunggu Konfirmasi</option>
                                <option value="progress">Koordinasi Jadwal (In Progress)</option>
                                <option value="completed">Jadwal Sosialisasi FIX</option>
                                <option value="failed">Belum Diizinkan / Batal</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nama PIC Sekolah</label>
                            <input type="text" name="pic_name" placeholder="Contoh: Bpk. Budi Santoso" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">No. WhatsApp PIC</label>
                            <input type="text" name="pic_phone" placeholder="Contoh: 081234567890" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Catatan Pesan / Isi Diskusi <span class="text-rose-500">*</span></label>
                        <textarea name="notes" rows="3" required placeholder="Tuliskan catatan hasil pesan atau koordinasi jadwal dengan pihak sekolah..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300"></textarea>
                    </div>

                    <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                        <button @click="showAddModal = false" type="button" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl shadow-md transition">
                            Simpan Follow Up
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: KONFIRMASI HAPUS HISTORI FOLLOW UP -->
        <div id="deleteFollowUpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl max-w-sm w-full overflow-hidden transform transition-all animate-in fade-in zoom-in-95 duration-150">
                <div class="p-6 text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center mb-4 shadow-xs">
                        <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                    </div>

                    <h3 class="text-base font-extrabold text-slate-900">Konfirmasi Hapus Follow Up</h3>
                    
                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">
                        Apakah Anda yakin ingin menghapus riwayat follow-up sekolah <span id="deleteModalSchoolName" class="font-extrabold text-slate-900 underline decoration-rose-300"></span>?
                    </p>
                    
                    <p class="text-[11px] text-slate-400 mt-1.5 bg-slate-50 p-2 rounded-xl border border-slate-100">
                        Data riwayat follow-up dan antrean jadwal sekolah ini akan dihapus dari sistem.
                    </p>

                    <form id="deleteFollowUpForm" method="POST" action="" class="mt-5 flex items-center justify-center gap-2">
                        @csrf
                        @method('DELETE')

                        <button type="button" onclick="closeDeleteModal()" class="w-1/2 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition cursor-pointer">
                            Batal
                        </button>
                        
                        <button type="submit" class="w-1/2 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Ya, Hapus</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function openScheduleModal(id, schoolName, status, fixedDate, fixedTime, picName, picPhone, location, notes, isFixed) {
            document.getElementById('modalSchoolName').textContent = schoolName;
            document.getElementById('scheduleForm').action = '/follow-ups/' + id + '/schedule';
            
            // Ketika menekan tombol Kunci Jadwal FIX, otomatis jadikan status 'completed' (Jadwal FIX)
            // agar kartu langsung berpindah ke kolom kategori Jadwal FIX
            const targetStatus = (isFixed == 1 || status === 'completed') ? 'completed' : 'completed';

            document.getElementById('formStatus').value = targetStatus;
            document.getElementById('formFixedDate').value = fixedDate || '';
            document.getElementById('formFixedTime').value = fixedTime || '';
            document.getElementById('formPicName').value = picName || '';
            document.getElementById('formPicPhone').value = picPhone || '';
            document.getElementById('formLocation').value = location || '';
            document.getElementById('formNotes').value = notes || '';
            document.getElementById('formIsFixed').checked = (targetStatus === 'completed');

            document.getElementById('scheduleModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeScheduleModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }

        function openInvitationModal(id, schoolName, attStatus, delegatesCount, picName, picPhone, notes) {
            document.getElementById('invitationModalSchoolName').textContent = schoolName;
            document.getElementById('invitationForm').action = '/follow-ups/' + id + '/schedule';
            document.getElementById('formAttendanceStatus').value = attStatus || 'belum_dikonfirmasi';
            document.getElementById('formAttendanceCount').value = (delegatesCount && delegatesCount > 0) ? delegatesCount : '';
            document.getElementById('formInvitationPicName').value = picName || '';
            document.getElementById('formInvitationPicPhone').value = picPhone || '';
            document.getElementById('formInvitationNotes').value = notes || '';

            document.getElementById('invitationModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeInvitationModal() {
            document.getElementById('invitationModal').classList.add('hidden');
        }

        function openGeneralVisitModal(id, schoolName, status, picName, picPhone, notes) {
            document.getElementById('generalVisitModalSchoolName').textContent = schoolName;
            document.getElementById('generalVisitForm').action = '/follow-ups/' + id + '/schedule';
            document.getElementById('formGeneralStatus').value = status || 'pending';
            document.getElementById('formGeneralPicName').value = picName || '';
            document.getElementById('formGeneralPicPhone').value = picPhone || '';
            document.getElementById('formGeneralNotes').value = notes || '';

            document.getElementById('generalVisitModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeGeneralVisitModal() {
            document.getElementById('generalVisitModal').classList.add('hidden');
        }

        // Sinkronisasi status dropdown dengan checkbox Kunci Jadwal FIX
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.getElementById('formStatus');
            const fixedCheckbox = document.getElementById('formIsFixed');

            if (statusSelect && fixedCheckbox) {
                statusSelect.addEventListener('change', function() {
                    fixedCheckbox.checked = (this.value === 'completed');
                });

                fixedCheckbox.addEventListener('change', function() {
                    if (this.checked) {
                        statusSelect.value = 'completed';
                    } else if (statusSelect.value === 'completed') {
                        statusSelect.value = 'progress';
                    }
                });
            }
        });

        function openDeleteModal(id, schoolName) {
            document.getElementById('deleteModalSchoolName').textContent = schoolName;
            document.getElementById('deleteFollowUpForm').action = '/follow-ups/' + id;
            document.getElementById('deleteFollowUpModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeDeleteModal() {
            document.getElementById('deleteFollowUpModal').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
