<x-app-layout title="Follow Up Siswa Kuisioner">
    <!-- Header Page -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-gradient-to-tr from-brand-600 to-amber-500 text-white shadow-md">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                </span>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Follow Up Siswa Kuisioner</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Sistem tindak lanjut prospek siswa minat kuliah dan beasiswa UBSI berdasarkan kriteria hasil respon.</p>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if($canPlot)
                <button type="button" onclick="openPlotingModal()" class="px-3.5 py-2.5 bg-gradient-to-r from-brand-600 via-indigo-600 to-brand-700 hover:from-brand-700 hover:to-indigo-800 text-white font-black rounded-xl text-xs shadow-lg shadow-brand-600/25 flex items-center gap-2 transition duration-150 transform hover:-translate-y-0.5">
                    <i data-lucide="split" class="w-4 h-4 text-amber-300"></i>
                    <span>Ploting & Pembagian Data</span>
                    @if(($plottingMetrics['total_unassigned'] ?? 0) > 0)
                        <span class="px-2 py-0.5 rounded-full bg-amber-400 text-slate-950 font-black text-[10px] animate-pulse">
                            {{ number_format($plottingMetrics['total_unassigned']) }} Belum Terbagi
                        </span>
                    @endif
                </button>
            @endif
            <button type="button" onclick="openImportModal()" class="px-3.5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md shadow-blue-600/20 flex items-center gap-2 transition duration-150">
                <i data-lucide="file-up" class="w-4 h-4"></i>
                <span>Import Data Manual</span>
            </button>
            <a href="{{ route('student-follow-ups.import-template') }}" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs shadow-soft flex items-center gap-2 transition duration-150" title="Unduh Format Template Excel">
                <i data-lucide="download" class="w-4 h-4 text-emerald-600"></i>
                <span>Template Excel</span>
            </a>
            <a href="{{ route('student-follow-ups.export', request()->query()) }}" class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 flex items-center gap-2 transition duration-150">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Unduh Laporan Excel</span>
            </a>
            <a href="{{ route('student-questionnaires.index') }}" class="px-3.5 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs shadow-soft flex items-center gap-2 transition duration-150">
                <i data-lucide="clipboard-list" class="w-4 h-4 text-brand-600"></i>
                <span>Lihat Analisis Kuisioner</span>
            </a>
            <a href="{{ route('student-questionnaire.create') }}" target="_blank" rel="noopener" class="px-3.5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2 transition duration-150">
                <i data-lucide="external-link" class="w-4 h-4 text-amber-400"></i>
                <span>Buka Form Siswa</span>
            </a>
        </div>
    </div>

    <!-- Alert / Flash Messages -->
    @if(session('toast'))
        <div class="p-4 mb-6 rounded-2xl {{ (session('toast')['type'] ?? '') === 'error' ? 'bg-rose-50 border border-rose-200 text-rose-800' : ((session('toast')['type'] ?? '') === 'warning' ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-emerald-50 border border-emerald-200 text-emerald-800') }} text-sm font-semibold flex items-center gap-3 shadow-sm">
            <i data-lucide="{{ (session('toast')['type'] ?? '') === 'error' ? 'alert-triangle' : ((session('toast')['type'] ?? '') === 'warning' ? 'alert-circle' : 'check-circle') }}" class="w-5 h-5 {{ (session('toast')['type'] ?? '') === 'error' ? 'text-rose-600' : ((session('toast')['type'] ?? '') === 'warning' ? 'text-amber-600' : 'text-emerald-600') }} shrink-0"></i>
            <span>{{ session('toast')['message'] ?? 'Data berhasil diproses.' }}</span>
        </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="p-4 mb-6 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-sm">
            <div class="flex items-center gap-2 font-bold mb-2 text-amber-800">
                <i data-lucide="alert-circle" class="w-4 h-4 text-amber-600"></i>
                <span>Beberapa baris data dilewati selama proses import:</span>
            </div>
            <ul class="list-disc pl-5 space-y-1 text-slate-700 max-h-36 overflow-y-auto">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($canPlot)
        <!-- Ploting & Distribution Oversight Banner (Khusus Pimpinan & Koordinator Marketing) -->
        <div class="p-4 sm:p-5 mb-6 rounded-2xl bg-gradient-to-r from-slate-900 via-brand-950 to-indigo-950 text-white border border-slate-850 shadow-xl relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-48 h-48 bg-brand-500/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-brand-500/20 border border-brand-400/30 flex items-center justify-center text-amber-300 shrink-0 shadow-inner">
                        <i data-lucide="sliders-horizontal" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-black text-amber-400 tracking-wide uppercase">Panel Ploting Follow Up Siswa</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/10 text-slate-200 border border-white/10">Pimpinan & Koordinator</span>
                        </div>
                        <p class="text-xs text-slate-300 mt-0.5">Kelola dan distribusikan kuota data follow up siswa (kuisioner & hasil support event) ke staf marketing.</p>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2.5 text-xs">
                    <div class="bg-white/5 border border-white/10 px-3.5 py-2 rounded-xl">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Belum Di-ploting</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-lg font-black text-amber-400">{{ number_format($plottingMetrics['total_unassigned'] ?? 0) }}</span>
                            <span class="text-[10px] text-slate-400">Data ({{ $plottingMetrics['unassigned_kuisioner'] ?? 0 }} Kuisioner &bull; {{ $plottingMetrics['unassigned_event'] ?? 0 }} Event)</span>
                        </div>
                    </div>
                    <div class="bg-white/5 border border-white/10 px-3.5 py-2 rounded-xl">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Sudah Di-ploting</span>
                        <div class="flex items-baseline gap-1 mt-0.5">
                            <span class="text-lg font-black text-emerald-400">{{ number_format($plottingMetrics['total_assigned'] ?? 0) }}</span>
                            <span class="text-[10px] text-slate-400">Siswa Aktif</span>
                        </div>
                    </div>
                    <button type="button" onclick="openPlotingModal()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black shadow-md flex items-center gap-2 transition transform hover:scale-105">
                        <i data-lucide="shuffle" class="w-4 h-4"></i>
                        <span>Bagi Data Sekarang</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- KPI & Criteria Option Report Summary Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <!-- Card 1: Total Responden -->
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa</span>
                <i data-lucide="users" class="w-4 h-4 text-slate-400"></i>
            </div>
            <p class="text-2xl font-black text-slate-900 mt-2">{{ number_format($metrics['total']) }}</p>
            <p class="text-[11px] text-slate-500 mt-0.5">Responden Kuisioner</p>
        </div>

        <!-- Card 2: Minat Beasiswa UBSI -->
        <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-teal-500/5 border border-emerald-300 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Minat Beasiswa</span>
                <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <p class="text-2xl font-black text-emerald-700 mt-2">{{ number_format($metrics['scholarship_yes']) }}</p>
            <p class="text-[11px] font-bold text-emerald-600 mt-0.5">
                {{ $metrics['total'] > 0 ? round(($metrics['scholarship_yes'] / $metrics['total']) * 100, 1) : 0 }}% Siswa Target
            </p>
        </div>

        <!-- Card 3: Belum Dihubungi -->
        <a href="{{ route('student-follow-ups.index', array_merge(request()->query(), ['status' => 'belum_dihubungi'])) }}" 
           class="p-4 rounded-2xl bg-white border border-slate-200 hover:border-slate-400 shadow-soft transition block group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider group-hover:text-slate-800">Belum Hubungi</span>
                <span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span>
            </div>
            <p class="text-2xl font-black text-slate-700 mt-2">{{ number_format($metrics['status_counts']['belum_dihubungi']) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Antrean Kontak</p>
        </a>

        <!-- Card 4: No Respon -->
        <a href="{{ route('student-follow-ups.index', array_merge(request()->query(), ['status' => 'no_respon'])) }}" 
           class="p-4 rounded-2xl bg-white border border-rose-200 hover:border-rose-400 shadow-soft transition block group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">No Respon</span>
                <i data-lucide="phone-missed" class="w-4 h-4 text-rose-500"></i>
            </div>
            <p class="text-2xl font-black text-rose-600 mt-2">{{ number_format($metrics['status_counts']['no_respon']) }}</p>
            <p class="text-[11px] text-rose-500/80 mt-0.5">Tidak Menjawab</p>
        </a>

        <!-- Card 5: Respon -->
        <a href="{{ route('student-follow-ups.index', array_merge(request()->query(), ['status' => 'respon'])) }}" 
           class="p-4 rounded-2xl bg-white border border-blue-200 hover:border-blue-400 shadow-soft transition block group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Respon</span>
                <i data-lucide="message-circle" class="w-4 h-4 text-blue-500"></i>
            </div>
            <p class="text-2xl font-black text-blue-600 mt-2">{{ number_format($metrics['status_counts']['respon']) }}</p>
            <p class="text-[11px] text-blue-500/80 mt-0.5">Diskusi / Tanya Info</p>
        </a>

        <!-- Card 6: Daftar Aplikasi & Minat -->
        <a href="{{ route('student-follow-ups.index', array_merge(request()->query(), ['status' => 'daftar_aplikasi'])) }}" 
           class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 to-emerald-500/10 border border-emerald-300 hover:border-emerald-500 shadow-soft transition block group">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold text-emerald-800 uppercase tracking-wider">Daftar Aplikasi</span>
                <i data-lucide="check-check" class="w-4 h-4 text-emerald-600"></i>
            </div>
            <div class="flex items-baseline gap-2 mt-2">
                <p class="text-2xl font-black text-emerald-700">{{ number_format($metrics['status_counts']['daftar_aplikasi'] ?? $metrics['status_counts']['closing']) }}</p>
                <span class="text-xs font-bold text-amber-700">({{ number_format($metrics['status_counts']['berminat']) }} Minat)</span>
            </div>
            <p class="text-[11px] font-bold text-emerald-700 mt-0.5">Masuk Data Prospek</p>
        </a>
    </div>

    <!-- Criteria Option Quick Filter Bar -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft p-4 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <!-- Left Tabs: Kriteria Option Report Filter -->
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                <span class="text-slate-400 mr-2 text-[11px] uppercase tracking-wider flex items-center gap-1">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    Kriteria:
                </span>
                
                <a href="{{ route('student-follow-ups.index', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}"
                   class="px-3 py-1.5 rounded-xl transition {{ (!request()->filled('status') || request('status') === 'all') ? 'bg-slate-900 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua ({{ number_format($metrics['total']) }})
                </a>

                <a href="{{ route('student-follow-ups.index', array_merge(request()->except('status', 'page'), ['status' => 'belum_dihubungi'])) }}"
                   class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'belum_dihubungi' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                    Belum Dihubungi ({{ $metrics['status_counts']['belum_dihubungi'] }})
                </a>

                <a href="{{ route('student-follow-ups.index', array_merge(request()->except('status', 'page'), ['status' => 'no_respon'])) }}"
                   class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'no_respon' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 text-rose-700 hover:bg-rose-100' }}">
                    No Respon ({{ $metrics['status_counts']['no_respon'] }})
                </a>

                <a href="{{ route('student-follow-ups.index', array_merge(request()->except('status', 'page'), ['status' => 'respon'])) }}"
                   class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'respon' ? 'bg-blue-600 text-white shadow-sm' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                    Respon ({{ $metrics['status_counts']['respon'] }})
                </a>

                <a href="{{ route('student-follow-ups.index', array_merge(request()->except('status', 'page'), ['status' => 'berminat'])) }}"
                   class="px-3 py-1.5 rounded-xl transition {{ request('status') === 'berminat' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-800 hover:bg-amber-100' }}">
                    Berminat ({{ $metrics['status_counts']['berminat'] }})
                </a>

                <a href="{{ route('student-follow-ups.index', array_merge(request()->except('status', 'page'), ['status' => 'daftar_aplikasi'])) }}"
                   class="px-3 py-1.5 rounded-xl transition {{ in_array(request('status'), ['daftar_aplikasi', 'closing']) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                    Daftar Aplikasi ({{ $metrics['status_counts']['daftar_aplikasi'] ?? $metrics['status_counts']['closing'] }})
                </a>
            </div>

            <!-- Scholarship Fast Toggle -->
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Filter Beasiswa:</span>
                <a href="{{ route('student-follow-ups.index', array_merge(request()->except('scholarship', 'page'), ['scholarship' => request('scholarship') === 'ya' ? 'all' : 'ya'])) }}"
                   class="px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 transition {{ request('scholarship') === 'ya' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100' }}">
                    <i data-lucide="award" class="w-3.5 h-3.5"></i>
                    <span>Target Beasiswa ({{ $metrics['scholarship_yes'] }})</span>
                </a>
            </div>
        </div>

        <!-- Expanded Filter Form -->
        <form method="GET" action="{{ route('student-follow-ups.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-3 mt-4 pt-4 border-t border-slate-100">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif

            <!-- Search Field -->
            <div class="sm:col-span-2 lg:col-span-2">
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Pencarian Siswa</label>
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3"></i>
                    <input type="search" name="search" value="{{ request('search') }}" 
                           placeholder="Cari nama siswa, nomor HP, wali, atau petugas..." 
                           class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-brand-500 focus:border-brand-500">
                </div>
            </div>

            <!-- Petugas Follow Up Option (Khusus Pimpinan, Koordinator, Admin) -->
            @if($canPlot)
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Petugas Follow Up</label>
                    <select name="assigned_to" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-brand-500 focus:border-brand-500 bg-white">
                        <option value="all">Semua Penugasan</option>
                        <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>⚠️ Belum Di-ploting ({{ $plottingMetrics['total_unassigned'] ?? 0 }})</option>
                        <optgroup label="Petugas Marketing & CS">
                            @foreach($assignableOfficers as $off)
                                <option value="{{ $off->id }}" {{ request('assigned_to') == $off->id ? 'selected' : '' }}>
                                    {{ $off->name }} ({{ $off->total_assigned_count ?? 0 }} Data)
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
            @endif

            <!-- Minat Beasiswa Option -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Minat Beasiswa</label>
                <select name="scholarship" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-brand-500 focus:border-brand-500">
                    <option value="all">Semua Respon</option>
                    <option value="ya" {{ request('scholarship') === 'ya' ? 'selected' : '' }}>Berminat Beasiswa (Ya)</option>
                    <option value="tidak" {{ request('scholarship') === 'tidak' ? 'selected' : '' }}>Tidak Berminat</option>
                </select>
            </div>

            <!-- Sumber Data Option (Kuisioner vs Support Event) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Sumber Data</label>
                <select name="source" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-brand-500 focus:border-brand-500">
                    <option value="all">Semua Sumber</option>
                    <option value="support_event" {{ request('source') === 'support_event' ? 'selected' : '' }}>Support Event</option>
                    <option value="kuisioner" {{ request('source') === 'kuisioner' ? 'selected' : '' }}>Kuisioner Siswa</option>
                </select>
            </div>

            <!-- Minat Prodi Option (Tersinkron Master Prodi) -->
            <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase mb-1">Peminatan Prodi</label>
                <select name="interest_program" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-brand-500 focus:border-brand-500 bg-white">
                    <option value="all">Semua Minat Prodi</option>
                    @foreach($studyPrograms as $sp)
                        <option value="{{ $sp->name }}" {{ request('interest_program') === $sp->name ? 'selected' : '' }}>{{ $sp->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action buttons -->
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 py-2 px-4 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Terapkan</span>
                </button>
                @if(request()->anyFilled(['search', 'scholarship', 'source', 'major', 'class', 'status', 'interest_program', 'assigned_to']))
                    <a href="{{ route('student-follow-ups.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-xs transition" title="Reset Filter">
                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Student Table List -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <h2 class="text-sm font-extrabold text-slate-900">Daftar Siswa Kuisioner & Status Follow Up</h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                    {{ $students->total() }} Siswa Ditemukan
                </span>
            </div>
            <p class="text-xs text-slate-400">Klik tombol WA untuk langsung chat dengan template khusus minat beasiswa.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        @if($canPlot)
                            <th class="py-3.5 px-3 text-center w-10">
                                <input type="checkbox" id="checkAllRows" onchange="toggleSelectAll(this)" class="rounded border-slate-700 text-brand-600 focus:ring-brand-500 cursor-pointer" title="Pilih Semua Siswa di Halaman Ini">
                            </th>
                        @endif
                        <th class="py-3.5 px-4">Siswa & Kelas</th>
                        <th class="py-3.5 px-4">Minat Beasiswa & Sumber</th>
                        <th class="py-3.5 px-4">Kontak & Direct Action</th>
                        <th class="py-3.5 px-4">Petugas Follow Up (Ploting)</th>
                        <th class="py-3.5 px-4">Kriteria Laporan</th>
                        <th class="py-3.5 px-4">Hasil Terakhir</th>
                        <th class="py-3.5 px-4 text-center min-w-32">Aksi Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $student)
                        @php
                            $waTextStudent = urlencode("Halo Kak {$student->student_name}, salam hangat dari Universitas BSI (UBSI). Kami telah menerima data Kakak. Terkait minat pada Program Beasiswa UBSI, kami ingin berbagi informasi detail syarat dan kuota beasiswa. Apakah ada waktu luang untuk berdiskusi sebentar?");
                            $waTextGuardian = urlencode("Selamat pagi/siang Bapak/Ibu {$student->guardian_name}, wali dari ananda {$student->student_name}. Kami dari Tim Admisi Universitas BSI ingin bersilaturahmi dan menginformasikan peluang Program Beasiswa Kuliah UBSI untuk ananda. Terima kasih.");
                            $studentWaLink = $student->formatted_student_phone ? "https://wa.me/{$student->formatted_student_phone}?text={$waTextStudent}" : null;
                            $guardianWaLink = $student->formatted_guardian_phone ? "https://wa.me/{$student->formatted_guardian_phone}?text={$waTextGuardian}" : null;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition duration-150 align-top" id="student-row-{{ $student->id }}">
                            @if($canPlot)
                                <td class="py-3.5 px-3 text-center">
                                    <input type="checkbox" name="selected_student_ids[]" value="{{ $student->id }}" onchange="updateSelectedCount()" class="student-checkbox rounded border-slate-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                </td>
                            @endif

                            <!-- Siswa & Kelas -->
                            <td class="py-3.5 px-4">
                                <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5 flex-wrap">
                                    {{ $student->student_name }}
                                    @if($student->source === 'support_event' || $student->school_event_proposal_id)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-purple-100 text-purple-700 border border-purple-200" title="{{ $student->eventProposal ? $student->eventProposal->event_name : 'Support Event' }}">
                                            <i data-lucide="sparkles" class="w-3 h-3 text-purple-600"></i>
                                            Support Event
                                        </span>
                                    @endif
                                </div>
                                @if($student->school_display_name && $student->school_display_name !== '-')
                                    <div class="text-xs text-brand-700 font-bold mt-0.5 flex items-center gap-1">
                                        <i data-lucide="building-2" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                                        <span class="truncate max-w-[220px]" title="{{ $student->school_display_name }}">{{ $student->school_display_name }}</span>
                                    </div>
                                @endif
                                @if($student->eventProposal)
                                    <div class="text-[11px] text-purple-700 font-semibold mt-0.5 flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3 text-purple-500 shrink-0"></i>
                                        <span class="truncate max-w-[220px]" title="{{ $student->eventProposal->event_name }}">{{ $student->eventProposal->event_name }}</span>
                                    </div>
                                @endif
                                <div class="text-slate-600 font-semibold mt-0.5">
                                    {{ $student->student_class ?: '-' }} &bull; <span class="text-slate-500 font-normal">{{ $student->student_major ?: '-' }}</span>
                                </div>
                                <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                    <i data-lucide="clock" class="w-3 h-3"></i>
                                    <span>{{ $student->source === 'support_event' ? 'Event' : 'Isi' }}: {{ $student->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                            </td>

                            <!-- Minat Beasiswa & Kuliah -->
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="flex items-center gap-1.5 mb-1.5">
                                    @if($student->scholarship_interest === 'ya')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                                            <i data-lucide="award" class="w-3 h-3 text-emerald-700"></i>
                                            Minat Beasiswa UBSI
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Tidak Minat Beasiswa
                                        </span>
                                    @endif
                                </div>

                                @if($student->scholarship_notes)
                                    @php
                                        $isNonBeasiswa = str_contains(strtolower($student->scholarship_notes), 'non beasiswa') || str_contains(strtolower($student->scholarship_notes), 'reguler mandiri');
                                    @endphp
                                    <p class="text-[11px] {{ $isNonBeasiswa ? 'text-slate-700 bg-slate-100/80 border-slate-200' : 'text-emerald-800 bg-emerald-50/70 border-emerald-100' }} font-semibold rounded-lg p-1.5 border mb-1">
                                        <span class="font-bold">{{ $isNonBeasiswa ? 'Program:' : 'Program Beasiswa:' }}</span> {{ $student->scholarship_notes }}
                                    </p>
                                @endif

                                @if($student->college_interest_notes)
                                    <p class="text-[11px] text-blue-800 font-medium bg-blue-50/70 rounded-lg p-1.5 border border-blue-100">
                                        <span class="font-bold">Minat Kuliah:</span> {{ $student->college_interest_notes }}
                                    </p>
                                @endif
                            </td>

                            <!-- Kontak & Direct Action -->
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    <!-- Siswa WA -->
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-slate-700">{{ $student->student_phone }}</span>
                                        @if($studentWaLink)
                                            <a href="{{ $studentWaLink }}" target="_blank" rel="noopener" 
                                               class="p-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition" 
                                               title="Chat WhatsApp Siswa">
                                                <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                                            </a>
                                            <a href="tel:{{ $student->student_phone }}" 
                                               class="p-1 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition" 
                                               title="Telepon Siswa">
                                                <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                                            </a>
                                        @endif
                                    </div>

                                    <!-- Wali -->
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1.5">
                                        <span>Wali: {{ $student->guardian_name }} ({{ $student->guardian_phone }})</span>
                                        @if($guardianWaLink)
                                            <a href="{{ $guardianWaLink }}" target="_blank" rel="noopener" 
                                               class="p-0.5 rounded text-emerald-600 hover:text-emerald-800" 
                                               title="Chat WhatsApp Wali">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i>
                                            </a>
                                        @endif
                                    </div>

                                    @if($student->student_email)
                                        <div class="text-[11px] text-slate-400 truncate max-w-44" title="{{ $student->student_email }}">
                                            {{ $student->student_email }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Petugas Follow Up (Ploting) -->
                            <td class="py-3.5 px-4" id="assigned-col-{{ $student->id }}">
                                @if($student->assignedUser)
                                    <div class="flex items-center gap-2">
                                        <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 font-black flex items-center justify-center text-xs shrink-0 border border-brand-200 shadow-xs">
                                            {{ strtoupper(substr($student->assignedUser->name, 0, 1)) }}
                                        </span>
                                        <div>
                                            <div class="font-extrabold text-slate-900 text-xs">
                                                {{ $student->assignedUser->name }}
                                            </div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $student->assigned_at ? $student->assigned_at->format('d/m/y H:i') : 'Di-ploting' }}
                                                @if($student->assignedBy)
                                                    &bull; oleh {{ Str::limit($student->assignedBy->name, 12) }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @if($canPlot)
                                        <button type="button" onclick="openSingleAssignModal({{ $student->id }}, '{{ addslashes($student->student_name) }}', {{ $student->assigned_to }})" class="mt-1 text-[10px] font-bold text-brand-600 hover:text-brand-800 hover:underline flex items-center gap-1">
                                            <i data-lucide="refresh-cw" class="w-2.5 h-2.5"></i>
                                            <span>Ganti Petugas</span>
                                        </button>
                                    @endif
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                        <i data-lucide="help-circle" class="w-3 h-3 text-amber-500"></i>
                                        Belum Di-ploting
                                    </span>
                                    @if($canPlot)
                                        <div class="mt-1">
                                            <button type="button" onclick="openSingleAssignModal({{ $student->id }}, '{{ addslashes($student->student_name) }}', null)" class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 transition flex items-center gap-1">
                                                <i data-lucide="user-plus" class="w-3 h-3 text-indigo-600"></i>
                                                <span>+ Tugaskan</span>
                                            </button>
                                        </div>
                                    @endif
                                @endif
                            </td>

                            <!-- Kriteria Laporan -->
                            <td class="py-3.5 px-4" id="status-badge-container-{{ $student->id }}">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold {{ $student->followup_badge_class }}">
                                    @if($student->followup_status === 'no_respon')
                                        <i data-lucide="phone-missed" class="w-3 h-3 text-rose-600"></i>
                                    @elseif($student->followup_status === 'respon')
                                        <i data-lucide="message-circle" class="w-3 h-3 text-blue-600"></i>
                                    @elseif($student->followup_status === 'berminat')
                                        <i data-lucide="sparkles" class="w-3 h-3 text-amber-600"></i>
                                    @elseif($student->followup_status === 'daftar_aplikasi' || $student->followup_status === 'closing')
                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-600"></i>
                                    @else
                                        <i data-lucide="circle-dot" class="w-3 h-3 text-slate-400"></i>
                                    @endif
                                    {{ $student->followup_status_label }}
                                </span>

                                @if($student->followup_status === 'daftar_aplikasi' || $student->followup_status === 'closing')
                                    <div class="mt-1">
                                        <a href="{{ route('prospects.index', ['search' => $student->student_name, 'status' => 'daftar']) }}" 
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300 transition" 
                                            title="Buka data siswa ini di Menu Data Prospek untuk follow up intensif">
                                            <i data-lucide="arrow-up-right" class="w-3 h-3 text-emerald-700"></i>
                                            <span>Ke Data Prospek</span>
                                        </a>
                                    </div>
                                @endif

                                <div class="text-[10px] text-slate-400 mt-1">
                                    {{ $student->follow_ups_count }}x Interaksi
                                </div>
                            </td>

                            <!-- Hasil Terakhir -->
                            <td class="py-3.5 px-4">
                                @if($student->last_followup_at)
                                    <div class="text-[11px] font-bold text-slate-800">
                                        {{ $student->last_followup_at->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        Oleh: <span class="font-semibold">{{ $student->lastFollowedUpBy?->name ?? 'Admin' }}</span>
                                    </div>
                                    @if($student->followup_notes)
                                        <p class="text-[11px] text-slate-600 line-clamp-2 mt-1 italic bg-slate-50 p-1 rounded border border-slate-100">
                                            "{{ $student->followup_notes }}"
                                        </p>
                                    @endif
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Belum ada catatan follow up</span>
                                @endif
                            </td>

                            <!-- Aksi Tindak Lanjut -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" 
                                            onclick="openFollowUpModal({{ $student->id }}, '{{ addslashes($student->student_name) }}', '{{ $student->student_phone }}', '{{ $student->followup_status }}', '{{ addslashes($student->scholarship_notes ?? '') }}', '{{ addslashes($student->college_interest_notes ?? '') }}')"
                                            class="px-2.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs flex items-center gap-1 transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        <span>Update</span>
                                    </button>

                                    <button type="button" 
                                            onclick="openHistoryModal({{ $student->id }})"
                                            class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold transition"
                                            title="Lihat Detail Jawaban & Riwayat Lengkap">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canPlot ? 8 : 7 }}" class="py-12 px-4 text-center">
                                <div class="max-w-sm mx-auto text-center space-y-2">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto">
                                        <i data-lucide="search-x" class="w-6 h-6"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-800">Tidak ada data siswa ditemukan</h3>
                                    <p class="text-xs text-slate-500">Silakan sesuaikan kriteria pencarian atau filter status laporan di atas.</p>
                                    <a href="{{ route('student-follow-ups.index') }}" class="inline-block mt-2 px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs">Reset Semua Filter</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif
    </div>

    @if($canPlot)
        <!-- Floating Bulk Assignment Action Bar (Pimpinan, Koordinator Marketing, & Admin) -->
        <div id="bulkActionBar" class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 hidden w-11/12 max-w-2xl bg-slate-900/95 backdrop-blur-md text-white px-5 py-3.5 rounded-2xl shadow-2xl border border-slate-700 items-center justify-between gap-4 transition-all duration-300">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-8 h-8 rounded-xl bg-brand-500/30 text-amber-300 flex items-center justify-center font-bold text-sm shrink-0">
                    <i data-lucide="check-square" class="w-4 h-4"></i>
                </span>
                <div class="min-w-0">
                    <span class="text-xs font-bold text-white block truncate">
                        <span id="selectedCountDisplay" class="text-amber-400 font-black text-sm">0</span> Siswa Terpilih
                    </span>
                    <p class="text-[10px] text-slate-300 hidden sm:block">Tugaskan data terpilih ke petugas:</p>
                </div>
            </div>
            <form id="bulkAssignForm" method="POST" action="{{ route('student-follow-ups.bulk-assign') }}" class="flex items-center gap-2 shrink-0">
                @csrf
                <div id="bulkSelectedInputsContainer"></div>
                <select name="assigned_to" required class="py-1.5 px-3 rounded-xl bg-slate-800 text-white border border-slate-600 text-xs focus:ring-amber-400 focus:border-amber-400 max-w-[160px] sm:max-w-[200px]">
                    <option value="" disabled selected>Pilih Petugas...</option>
                    @foreach($assignableOfficers as $off)
                        <option value="{{ $off->id }}">{{ $off->name }} ({{ $off->total_assigned_count ?? 0 }})</option>
                    @endforeach
                </select>
                <button type="submit" class="px-3.5 py-1.5 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 font-black rounded-xl text-xs shadow-md transition flex items-center gap-1.5">
                    <i data-lucide="user-check" class="w-3.5 h-3.5"></i>
                    <span>Tugaskan</span>
                </button>
                <button type="button" onclick="cancelSelection()" class="p-1.5 text-slate-400 hover:text-white rounded-lg transition" title="Batal Pilih">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </form>
        </div>
    @endif

    <!-- MODAL 1: FORM CATAT / UPDATE HASIL FOLLOW UP -->
    <div id="followUpModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-base font-extrabold tracking-tight">Tindak Lanjut Siswa (Follow Up)</h3>
                    <p class="text-xs text-amber-400 mt-0.5" id="modalStudentTitle">Nama Siswa</p>
                </div>
                <button type="button" onclick="closeFollowUpModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form id="followUpForm" method="POST" action="" onsubmit="handleFollowUpSubmit(event)" class="p-6 space-y-4">
                @csrf
                <input type="hidden" id="formStudentId" value="">

                <!-- Kriteria Option Report (User Required) -->
                <div>
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1.5">
                        Kriteria Hasil Follow Up <span class="text-rose-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <!-- No Respon -->
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-rose-200 hover:bg-rose-50/50 cursor-pointer font-bold text-rose-700">
                            <input type="radio" name="status" value="no_respon" required class="text-rose-600 focus:ring-rose-500">
                            <span>No Respon</span>
                        </label>

                        <!-- Respon -->
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-blue-200 hover:bg-blue-50/50 cursor-pointer font-bold text-blue-700">
                            <input type="radio" name="status" value="respon" required class="text-blue-600 focus:ring-blue-500">
                            <span>Respon</span>
                        </label>

                        <!-- Berminat -->
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-amber-300 hover:bg-amber-50/50 cursor-pointer font-bold text-amber-800">
                            <input type="radio" name="status" value="berminat" required class="text-amber-600 focus:ring-amber-500">
                            <span>Berminat</span>
                        </label>

                        <!-- Daftar Aplikasi -->
                        <label class="flex items-center gap-2 p-2.5 rounded-xl border border-emerald-300 hover:bg-emerald-50/50 cursor-pointer font-bold text-emerald-800 bg-emerald-50/20">
                            <input type="radio" name="status" value="daftar_aplikasi" required class="text-emerald-600 focus:ring-emerald-500">
                            <span>Daftar Aplikasi</span>
                        </label>
                    </div>
                </div>

                <!-- Metode & Tanggal Kontak -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Metode Kontak <span class="text-rose-500">*</span></label>
                        <select name="contact_method" id="formContactMethod" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                            <option value="wa">WhatsApp</option>
                            <option value="telepon">Telepon Suara</option>
                            <option value="email">Email</option>
                            <option value="tatap_muka">Tatap Muka / Sekolah</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Interaksi <span class="text-rose-500">*</span></label>
                        <input type="date" name="followup_date" id="formFollowupDate" value="{{ date('Y-m-d') }}" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                    </div>
                </div>

                <!-- Pilihan Program Kuliah: 2 Pilihan (Beasiswa atau Non Beasiswa) -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                        <span>Pilihan Program Kuliah</span>
                        <span class="text-[10px] text-slate-500 font-normal">Pilih Jalur Beasiswa atau Non Beasiswa</span>
                    </label>

                    <!-- 2 Pilihan Program Card -->
                    <div class="grid grid-cols-2 gap-2 mb-2">
                        <label id="labelProgramBeasiswa" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50/70 text-emerald-900 font-bold text-xs cursor-pointer shadow-xs transition">
                            <input type="radio" name="program_type" value="beasiswa" id="radioProgramBeasiswa" onchange="handleProgramTypeChange('beasiswa')" checked class="text-emerald-600 focus:ring-emerald-500">
                            <i data-lucide="award" class="w-4 h-4 text-emerald-600"></i>
                            <span>Beasiswa</span>
                        </label>

                        <label id="labelProgramNonBeasiswa" class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-bold text-xs cursor-pointer transition hover:bg-slate-100">
                            <input type="radio" name="program_type" value="non_beasiswa" id="radioProgramNonBeasiswa" onchange="handleProgramTypeChange('non_beasiswa')" class="text-slate-600 focus:ring-slate-500">
                            <i data-lucide="user-check" class="w-4 h-4 text-slate-500"></i>
                            <span>Non Beasiswa</span>
                        </label>
                    </div>

                    <!-- Hidden input submitted to server -->
                    <input type="hidden" name="scholarship_interest_notes" id="formScholarshipNotes">

                    <!-- Sub-pilihan jika memilih Beasiswa -->
                    <div id="wrapperBeasiswaDetail" class="space-y-1.5 p-2.5 bg-emerald-50/40 border border-emerald-200 rounded-xl">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-bold text-emerald-900">Jenis / Jalur Beasiswa:</label>
                            <span class="text-[10px] text-emerald-700">KIP-K / Prestasi / Jalur Sekolah / Talenta</span>
                        </div>
                        <select id="selectBeasiswaType" onchange="handleBeasiswaSelectChange(this)" class="w-full py-2 px-3 rounded-xl border border-emerald-300 text-xs bg-white focus:ring-2 focus:ring-emerald-500 focus:outline-none font-medium text-slate-800">
                            <option value="Beasiswa KIP-Kuliah">Beasiswa KIP-Kuliah</option>
                            <option value="Beasiswa Jalur Sekolah">Beasiswa Jalur Sekolah</option>
                            <option value="Beasiswa Prestasi">Beasiswa Prestasi</option>
                            <option value="Beasiswa Talenta">Beasiswa Talenta</option>
                            <option value="__custom_beasiswa__">Lainnya (Tulis Manual)...</option>
                        </select>
                        <div id="wrapperCustomBeasiswa" class="hidden mt-1.5">
                            <input type="text" id="formCustomBeasiswa" oninput="updateScholarshipHiddenValue()" placeholder="Tuliskan nama atau catatan beasiswa..." class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                    </div>

                    <!-- Sub-keterangan jika memilih Non Beasiswa -->
                    <div id="wrapperNonBeasiswaDetail" class="hidden p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 space-y-1">
                        <div class="flex items-center gap-1.5 font-bold text-slate-800">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-brand-600"></i>
                            <span>Program Non Beasiswa (Reguler Mandiri)</span>
                        </div>
                        <p class="text-[11px] text-slate-500">Jalur pendaftaran mahasiswa umum / reguler dengan pembiayaan mandiri.</p>
                    </div>
                </div>

                <!-- Catatan Minat Kuliah / Program Studi (Tersinkronisasi dengan Master Prodi) -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-xs font-bold text-slate-700">Minat Kuliah & Program Studi Pilihan</label>
                        <span class="text-[10px] text-brand-600 font-bold flex items-center gap-1 bg-brand-50 px-2 py-0.5 rounded-md">
                            <i data-lucide="graduation-cap" class="w-3 h-3 text-brand-600"></i>
                            Tersinkron Master Prodi
                        </span>
                    </div>

                    <select id="formCollegeNotesSelect" onchange="handleCollegeSelectChange(this)" 
                            class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-white focus:ring-2 focus:ring-brand-500 focus:outline-none font-medium text-slate-800">
                        <option value="">-- Pilih Program Studi yang Diminati --</option>
                        @foreach($studyPrograms as $sp)
                            @php
                                $activeTimes = $sp->getActiveStudyTimesDetails();
                            @endphp
                            @if(!empty($activeTimes))
                                <optgroup label="{{ $sp->name }} ({{ $sp->code }})">
                                    @foreach($activeTimes as $tKey => $t)
                                        <option value="{{ $sp->name }} - {{ $t['label'] }}" data-program="{{ $sp->name }}" data-code="{{ $sp->code }}" data-study-time="{{ $tKey }}">
                                            {{ $sp->name }} — {{ $t['label'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @else
                                <option value="{{ $sp->name }}" data-program="{{ $sp->name }}" data-code="{{ $sp->code }}">
                                    {{ $sp->name }} [{{ $sp->code }}]
                                </option>
                            @endif
                        @endforeach
                        <option value="__custom__">Pilihan Lainnya / Tulis Manual...</option>
                    </select>

                    <!-- Actual input that will be submitted to the backend -->
                    <input type="hidden" name="college_interest_notes" id="formCollegeNotes">

                    <div id="wrapperCustomCollegeNotes" class="mt-2 hidden">
                        <input type="text" id="formCustomCollegeNotes" 
                               oninput="document.getElementById('formCollegeNotes').value = this.value"
                               placeholder="Tuliskan catatan minat program studi khusus atau kampus lain..." 
                               class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-slate-50">
                    </div>
                </div>

                <!-- Catatan Hasil Percakapan -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Hasil & Respon Siswa</label>
                    <textarea name="notes" id="formNotes" rows="2" 
                              placeholder="Tuliskan respon siswa, kendala, atau poin penting percakapan..." 
                              class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs"></textarea>
                </div>

                <!-- Rencana Tindak Lanjut Berikutnya -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jadwal Follow Up Berikutnya (Opsional)</label>
                    <input type="date" name="next_followup_date" id="formNextDate" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                </div>

                <!-- Submit / Cancel -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeFollowUpModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitFollowUp" class="px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Hasil Follow Up</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: DETAIL JAWABAN KUISIONER & TIMELINE LOG RIWAYAT -->
    <div id="historyModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all">
            <!-- Header -->
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-brand-600 to-amber-500 flex items-center justify-center font-bold text-sm">
                        <i data-lucide="clipboard-list" class="w-4 h-4 text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-extrabold tracking-tight" id="histStudentName">Nama Siswa</h3>
                        <p class="text-xs text-amber-400" id="histStudentMeta">Kelas / Jurusan</p>
                    </div>
                </div>
                <button type="button" onclick="closeHistoryModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Tab Switcher -->
            <div class="flex items-center border-b border-slate-200 bg-slate-50 px-6 shrink-0" x-data="{ tab: 'history' }">
                <button type="button" onclick="switchDetailTab('history')" id="tabBtnHistory" 
                        class="py-3 px-4 font-bold text-xs border-b-2 border-brand-600 text-brand-700 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Riwayat Follow Up (<span id="histCount">0</span>)</span>
                </button>
                <button type="button" onclick="switchDetailTab('answers')" id="tabBtnAnswers" 
                        class="py-3 px-4 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2">
                    <i data-lucide="list-checks" class="w-4 h-4"></i>
                    <span>Jawaban Kuisioner (16 Skala)</span>
                </button>
            </div>

            <!-- Scrollable Body Content -->
            <div class="p-6 overflow-y-auto flex-1 space-y-4">
                <!-- Quick Student Info Card -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">No. HP Siswa</span>
                        <span class="font-extrabold text-slate-900" id="histPhone">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Nama & Kontak Wali</span>
                        <span class="font-semibold text-slate-800" id="histGuardian">-</span>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase font-bold block">Minat Beasiswa Awal</span>
                        <span class="font-extrabold" id="histScholarshipInterest">-</span>
                    </div>
                </div>

                <!-- Tab 1: Riwayat Follow Up Timeline -->
                <div id="tabContentHistory" class="space-y-3">
                    <div id="histTimelineContainer" class="space-y-3">
                        <!-- Populated by JS -->
                    </div>
                </div>

                <!-- Tab 2: Jawaban Kuisioner -->
                <div id="tabContentAnswers" class="hidden space-y-3">
                    <div class="text-xs text-slate-500 mb-2">Skor 1 (Sangat Tidak Setuju) sampai 5 (Sangat Setuju):</div>
                    <div id="histAnswersContainer" class="space-y-2 text-xs">
                        <!-- Populated by JS -->
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <span class="text-xs text-slate-400">SAPA Sekolah Student PMB CRM</span>
                <button type="button" onclick="closeHistoryModal()" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 3: IMPORT MANUAL SISWA KUISIONER -->
    <div id="importModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-blue-600 text-white">
                        <i data-lucide="file-up" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold tracking-tight">Import Data Siswa Manual</h3>
                        <p class="text-xs text-amber-400 mt-0.5">Unggah data siswa kuisioner kertas / form manual</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form action="{{ route('student-follow-ups.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-2">
                    <p class="font-bold flex items-center gap-1.5 text-blue-800">
                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                        Petunjuk Import Siswa Manual:
                    </p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-700">
                        <li>Kolom wajib hanya <strong>Nama Lengkap Siswa</strong> dan <strong>No. HP Siswa</strong>.</li>
                        <li>Kolom Kelas, Jurusan, Minat Beasiswa, Minat Prodi, dan Wali bersifat opsional.</li>
                        <li>Data siswa yang diimpor langsung dapat di-follow up dengan kriteria status laporan.</li>
                    </ul>
                    <div class="pt-2">
                        <a href="{{ route('student-follow-ups.import-template') }}" class="inline-flex items-center gap-1.5 font-bold text-blue-700 hover:text-blue-900 underline">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            Download Template Excel (.xlsx)
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Pilih File Excel / CSV (.xlsx, .xls, .csv) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                           class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-2xl p-2 bg-slate-50">
                </div>

                <!-- Submit / Cancel -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Mulai Import Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($canPlot)
        <!-- MODAL PLOTING & PEMBAGIAN DATA SISWA (Pimpinan, Koordinator Marketing, & Admin) -->
        <div id="plotingModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden transform transition-all animate-in fade-in zoom-in-95">
                <!-- Header Modal -->
                <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-brand-950 to-indigo-950 text-white flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="p-2 rounded-xl bg-amber-400 text-slate-950 shadow-md">
                            <i data-lucide="split" class="w-5 h-5"></i>
                        </span>
                        <div>
                            <h3 class="text-base font-extrabold tracking-tight">Ploting & Pembagian Data Follow Up</h3>
                            <p class="text-xs text-slate-300">Distribusikan data leads siswa (Kuisioner & Support Event) ke tim marketing.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closePlotingModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <!-- Tab Pemilihan Mode Ploting -->
                <div class="flex border-b border-slate-200 bg-slate-50 shrink-0">
                    <button type="button" id="tabPlotingEqualBtn" onclick="switchPlotingTab('equal')" class="flex-1 py-3 px-4 font-bold text-xs border-b-2 border-brand-600 text-brand-700 bg-white flex items-center justify-center gap-2 transition">
                        <i data-lucide="shuffle" class="w-4 h-4"></i>
                        <span>Bagi Rata Otomatis</span>
                    </button>
                    <button type="button" id="tabPlotingCustomBtn" onclick="switchPlotingTab('custom')" class="flex-1 py-3 px-4 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center justify-center gap-2 transition">
                        <i data-lucide="sliders" class="w-4 h-4"></i>
                        <span>Atur Kuota Kustom Per Petugas</span>
                    </button>
                </div>

                <!-- Form Ploting -->
                <form id="plotingForm" method="POST" action="{{ route('student-follow-ups.distribute') }}" class="flex flex-col flex-1 overflow-y-auto p-6 space-y-5">
                    @csrf
                    <input type="hidden" name="distribution_type" id="inputDistributionType" value="equal">

                    <!-- Pilihan Sumber Data yang Akan Di-ploting -->
                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200/80">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">1. Pilih Sumber Data Siswa</label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-brand-500 transition">
                                <input type="radio" name="source_filter" value="all" checked onchange="updatePlotingQueueInfo()" class="text-brand-600 focus:ring-brand-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Semua Sumber</span>
                                    <span class="text-[10px] text-slate-500">{{ $plottingMetrics['total_unassigned'] ?? 0 }} Data Belum Di-plot</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-brand-500 transition">
                                <input type="radio" name="source_filter" value="kuisioner" onchange="updatePlotingQueueInfo()" class="text-brand-600 focus:ring-brand-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Kuisioner Siswa</span>
                                    <span class="text-[10px] text-slate-500">{{ $plottingMetrics['unassigned_kuisioner'] ?? 0 }} Data Belum Di-plot</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-brand-500 transition">
                                <input type="radio" name="source_filter" value="support_event" onchange="updatePlotingQueueInfo()" class="text-brand-600 focus:ring-brand-500">
                                <div>
                                    <span class="text-xs font-bold text-slate-900 block">Support Event</span>
                                    <span class="text-[10px] text-slate-500">{{ $plottingMetrics['unassigned_event'] ?? 0 }} Data Belum Di-plot</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Panel Mode 1: Bagi Rata Otomatis -->
                    <div id="panelPlotingEqual" class="space-y-4">
                        <!-- Opsi Batasan Jumlah -->
                        <div>
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">2. Jumlah Data yang Dibagikan</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label class="flex items-center gap-2.5 p-3 rounded-xl border border-slate-200 bg-slate-50/50 cursor-pointer">
                                    <input type="radio" name="equal_limit_choice" value="all" checked onchange="toggleEqualLimitInput(false)" class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-bold text-slate-800">Bagi Semua Antrean yang Tersedia</span>
                                </label>
                                <div class="flex items-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50/50">
                                    <input type="radio" name="equal_limit_choice" value="limited" id="radioEqualLimited" onchange="toggleEqualLimitInput(true)" class="text-brand-600 focus:ring-brand-500">
                                    <span class="text-xs font-bold text-slate-800 shrink-0">Batasi Jumlah:</span>
                                    <input type="number" id="inputEqualTotalLimit" name="total_limit" min="1" placeholder="Misal: 50" disabled oninput="calculatePlotingEstimate()" class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-300 disabled:bg-slate-100 disabled:text-slate-400">
                                </div>
                            </div>
                        </div>

                        <!-- Pilih Petugas Penerima Ploting -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">3. Pilih Petugas Tim Marketing & CS</label>
                                <button type="button" onclick="toggleAllOfficersCheckboxes()" class="text-xs text-brand-600 font-bold hover:underline">Pilih / Hapus Semua</button>
                            </div>
                            <div class="space-y-2 max-h-56 overflow-y-auto p-1">
                                @forelse($assignableOfficers as $officer)
                                    <label class="flex items-center justify-between p-3 rounded-2xl border border-slate-200 bg-white hover:bg-slate-50 cursor-pointer transition">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="officer_ids[]" value="{{ $officer->id }}" checked onchange="calculatePlotingEstimate()" class="officer-check rounded border-slate-300 text-brand-600 focus:ring-brand-500">
                                            <span class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-xs border border-brand-200">
                                                {{ strtoupper(substr($officer->name, 0, 1)) }}
                                            </span>
                                            <div>
                                                <span class="text-xs font-bold text-slate-900 block">{{ $officer->name }}</span>
                                                <span class="text-[10px] text-slate-400">{{ $officer->role->name ?? 'Marketing' }}</span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                Beban: {{ $officer->total_assigned_count ?? 0 }} Data
                                            </span>
                                            @if(($officer->pending_count ?? 0) > 0)
                                                <span class="block text-[10px] text-amber-600 font-semibold">{{ $officer->pending_count }} belum dihubungi</span>
                                            @endif
                                        </div>
                                    </label>
                                @empty
                                    <div class="p-4 text-center text-xs text-slate-400">Tidak ada staf marketing/CS aktif.</div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Live Calculation Preview Box -->
                        <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-950 text-xs">
                            <div class="flex items-center gap-2 font-bold mb-1 text-indigo-900">
                                <i data-lucide="calculator" class="w-4 h-4 text-indigo-600"></i>
                                <span>Estimasi Pembagian Data:</span>
                            </div>
                            <p id="plotingEstimateText" class="text-xs text-indigo-800">
                                Memilih seluruh data yang tersedia untuk dibagi rata ke petugas terpilih.
                            </p>
                        </div>
                    </div>

                    <!-- Panel Mode 2: Atur Kuota Kustom Per Petugas -->
                    <div id="panelPlotingCustom" class="space-y-4 hidden">
                        <div>
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">2. Tentukan Alokasi Jumlah Siswa Per Petugas</label>
                            <p class="text-xs text-slate-400 mb-3">Tentukan berapa data siswa yang ingin diberikan secara spesifik ke masing-masing staf.</p>
                            <div class="space-y-2.5 max-h-72 overflow-y-auto p-1">
                                @forelse($assignableOfficers as $officer)
                                    <div class="flex items-center justify-between p-3 rounded-2xl border border-slate-200 bg-white">
                                        <div class="flex items-center gap-3">
                                            <span class="w-8 h-8 rounded-full bg-brand-50 text-brand-700 font-bold flex items-center justify-center text-xs border border-brand-200">
                                                {{ strtoupper(substr($officer->name, 0, 1)) }}
                                            </span>
                                            <div>
                                                <span class="text-xs font-bold text-slate-900 block">{{ $officer->name }}</span>
                                                <span class="text-[10px] text-slate-400">Saat ini: {{ $officer->total_assigned_count ?? 0 }} Data ({{ $officer->pending_count ?? 0 }} Pending)</span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <label class="text-[11px] font-bold text-slate-500">Alokasi:</label>
                                            <input type="number" name="allocations[{{ $officer->id }}]" min="0" placeholder="0" oninput="calculateCustomTotal()" class="input-custom-allocation w-24 px-3 py-1.5 text-xs rounded-xl border border-slate-300 font-bold text-center focus:ring-brand-500 focus:border-brand-500">
                                            <span class="text-xs text-slate-500 font-semibold">Data</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-slate-400">Tidak ada staf marketing/CS aktif.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-950 text-xs flex items-center justify-between">
                            <span class="font-bold">Total Kuota yang Dialokasikan:</span>
                            <span id="customTotalAllocated" class="text-base font-black text-amber-700">0 Data</span>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5 shrink-0">
                        <button type="button" onclick="closePlotingModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 via-indigo-600 to-brand-700 hover:from-brand-700 hover:to-indigo-800 text-white font-black text-xs shadow-md transition flex items-center gap-2">
                            <i data-lucide="check" class="w-4 h-4 text-amber-300"></i>
                            <span>Terapkan Pembagian Data Sekarang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL SINGLE QUICK ASSIGN (Tugaskan / Ganti Petugas Satuan) -->
        <div id="singleAssignModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-md overflow-hidden transform transition-all animate-in fade-in zoom-in-95">
                <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold tracking-tight">Tugaskan Petugas Follow Up</h3>
                        <p class="text-[11px] text-slate-300" id="singleAssignStudentName">Memuat...</p>
                    </div>
                    <button type="button" onclick="closeSingleAssignModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>
                <form id="singleAssignForm" method="POST" action="" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Petugas Marketing / CS</label>
                        <select name="assigned_to" id="singleAssignSelect" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-brand-500 focus:border-brand-500 bg-white">
                            <option value="">-- Kosongkan (Belum Di-ploting) --</option>
                            @foreach($assignableOfficers as $off)
                                <option value="{{ $off->id }}">{{ $off->name }} ({{ $off->role->name ?? 'Marketing' }} &bull; {{ $off->total_assigned_count ?? 0 }} Data)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" onclick="closeSingleAssignModal()" class="px-3.5 py-2 rounded-xl border border-slate-300 text-slate-700 font-bold text-xs hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-md transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Simpan Penugasan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @push('scripts')
    <script>
        // Modal Import Logic
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }

        function handleCollegeSelectChange(selectElem) {
            const val = selectElem.value;
            const hiddenCollege = document.getElementById('formCollegeNotes');
            const customWrapper = document.getElementById('wrapperCustomCollegeNotes');
            const customInput = document.getElementById('formCustomCollegeNotes');

            if (val === '__custom__') {
                customWrapper.classList.remove('hidden');
                hiddenCollege.value = customInput.value;
                customInput.focus();
            } else {
                customWrapper.classList.add('hidden');
                hiddenCollege.value = val;
            }
        }

        // Program Type (Beasiswa vs Non Beasiswa) Logic
        function handleProgramTypeChange(type) {
            const radioBeasiswa = document.getElementById('radioProgramBeasiswa');
            const radioNon = document.getElementById('radioProgramNonBeasiswa');
            const labelBeasiswa = document.getElementById('labelProgramBeasiswa');
            const labelNon = document.getElementById('labelProgramNonBeasiswa');
            const wrapperBeasiswa = document.getElementById('wrapperBeasiswaDetail');
            const wrapperNon = document.getElementById('wrapperNonBeasiswaDetail');

            if (type === 'beasiswa') {
                radioBeasiswa.checked = true;
                labelBeasiswa.className = "flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-emerald-500 bg-emerald-50/70 text-emerald-900 font-bold text-xs cursor-pointer shadow-xs transition";
                labelNon.className = "flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-bold text-xs cursor-pointer transition hover:bg-slate-100";
                wrapperBeasiswa.classList.remove('hidden');
                wrapperNon.classList.add('hidden');
                updateScholarshipHiddenValue();
            } else {
                radioNon.checked = true;
                labelNon.className = "flex items-center justify-center gap-2 p-2.5 rounded-xl border-2 border-brand-500 bg-brand-50/70 text-brand-900 font-bold text-xs cursor-pointer shadow-xs transition";
                labelBeasiswa.className = "flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 font-bold text-xs cursor-pointer transition hover:bg-slate-100";
                wrapperBeasiswa.classList.add('hidden');
                wrapperNon.classList.remove('hidden');
                document.getElementById('formScholarshipNotes').value = 'Non Beasiswa';
            }
        }

        function handleBeasiswaSelectChange(selectElem) {
            const val = selectElem.value;
            const customWrapper = document.getElementById('wrapperCustomBeasiswa');
            const customInput = document.getElementById('formCustomBeasiswa');

            if (val === '__custom_beasiswa__') {
                customWrapper.classList.remove('hidden');
                customInput.focus();
            } else {
                customWrapper.classList.add('hidden');
            }
            updateScholarshipHiddenValue();
        }

        function updateScholarshipHiddenValue() {
            const radioBeasiswa = document.getElementById('radioProgramBeasiswa');
            const hiddenInput = document.getElementById('formScholarshipNotes');
            if (radioBeasiswa && radioBeasiswa.checked) {
                const select = document.getElementById('selectBeasiswaType');
                if (select.value === '__custom_beasiswa__') {
                    hiddenInput.value = document.getElementById('formCustomBeasiswa').value.trim() || 'Beasiswa';
                } else {
                    hiddenInput.value = select.value;
                }
            } else {
                hiddenInput.value = 'Non Beasiswa';
            }
        }

        function handleCollegeSelectChange(selectElem) {
            const val = selectElem.value;
            const customWrapper = document.getElementById('wrapperCustomCollegeNotes');
            const customInput = document.getElementById('formCustomCollegeNotes');
            const hiddenCollege = document.getElementById('formCollegeNotes');

            if (val === '__custom__') {
                customWrapper.classList.remove('hidden');
                hiddenCollege.value = customInput.value;
                customInput.focus();
            } else {
                customWrapper.classList.add('hidden');
                hiddenCollege.value = val;
            }
        }

        // Modal Follow Up Logic
        function openFollowUpModal(studentId, name, phone, currentStatus, scholarshipNotes, collegeNotes) {
            document.getElementById('formStudentId').value = studentId;
            document.getElementById('modalStudentTitle').textContent = name + ' (' + (phone || '-') + ')';
            document.getElementById('followUpForm').action = '/student-follow-ups/' + studentId;

            // Set current status radio
            const statusRadios = document.getElementsByName('status');
            for (let r of statusRadios) {
                if (r.value === currentStatus || ((currentStatus === 'closing' || currentStatus === 'daftar_aplikasi') && r.value === 'daftar_aplikasi')) {
                    r.checked = true;
                }
            }

            // Sync Program Type: Beasiswa atau Non Beasiswa
            const beasiswaSelect = document.getElementById('selectBeasiswaType');
            const customBeasiswaWrapper = document.getElementById('wrapperCustomBeasiswa');
            const customBeasiswaInput = document.getElementById('formCustomBeasiswa');
            const sNotes = (scholarshipNotes || '').trim();
            const lowerS = sNotes.toLowerCase();
            const isNonBeasiswa = lowerS.includes('non beasiswa') || lowerS.includes('reguler mandiri') || lowerS === 'mandiri';

            if (isNonBeasiswa) {
                handleProgramTypeChange('non_beasiswa');
            } else {
                handleProgramTypeChange('beasiswa');
                let foundPreset = false;
                if (sNotes !== '') {
                    for (let opt of beasiswaSelect.options) {
                        if (opt.value && opt.value !== '__custom_beasiswa__' && (opt.value.toLowerCase() === lowerS || lowerS.includes(opt.value.toLowerCase()) || opt.value.toLowerCase().includes(lowerS))) {
                            beasiswaSelect.value = opt.value;
                            foundPreset = true;
                            break;
                        }
                    }
                }
                if (!foundPreset && sNotes !== '') {
                    beasiswaSelect.value = '__custom_beasiswa__';
                    customBeasiswaWrapper.classList.remove('hidden');
                    customBeasiswaInput.value = sNotes;
                } else {
                    customBeasiswaWrapper.classList.add('hidden');
                    customBeasiswaInput.value = '';
                    if (!foundPreset) {
                        beasiswaSelect.selectedIndex = 0; // default Beasiswa KIP-Kuliah
                    }
                }
                updateScholarshipHiddenValue();
            }
            
            // Sync Minat Program Studi dengan Dropdown Master Prodi
            const collegeSelect = document.getElementById('formCollegeNotesSelect');
            const hiddenCollege = document.getElementById('formCollegeNotes');
            const customWrapper = document.getElementById('wrapperCustomCollegeNotes');
            const customInput = document.getElementById('formCustomCollegeNotes');

            hiddenCollege.value = collegeNotes || '';
            let matched = false;

            if (collegeNotes && collegeNotes.trim() !== '') {
                const cleanNotes = collegeNotes.trim().toLowerCase();
                for (let i = 0; i < collegeSelect.options.length; i++) {
                    const optVal = collegeSelect.options[i].value;
                    const optText = (collegeSelect.options[i].text || '').toLowerCase();
                    if (optVal && optVal !== '__custom__' && (optVal.toLowerCase() === cleanNotes || cleanNotes.includes(optVal.toLowerCase()) || optText.includes(cleanNotes) || cleanNotes.includes(optText))) {
                        collegeSelect.selectedIndex = i;
                        hiddenCollege.value = optVal;
                        matched = true;
                        break;
                    }
                }
            }

            if (matched) {
                customWrapper.classList.add('hidden');
                customInput.value = '';
            } else if (collegeNotes && collegeNotes.trim() !== '') {
                collegeSelect.value = '__custom__';
                customWrapper.classList.remove('hidden');
                customInput.value = collegeNotes;
                hiddenCollege.value = collegeNotes;
            } else {
                collegeSelect.value = '';
                customWrapper.classList.add('hidden');
                customInput.value = '';
                hiddenCollege.value = '';
            }

            document.getElementById('formNotes').value = '';
            document.getElementById('formNextDate').value = '';
            document.getElementById('formFollowupDate').value = new Date().toISOString().split('T')[0];

            document.getElementById('followUpModal').classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeFollowUpModal() {
            document.getElementById('followUpModal').classList.add('hidden');
        }

        async function handleFollowUpSubmit(e) {
            e.preventDefault();
            const form = document.getElementById('followUpForm');
            const submitBtn = document.getElementById('btnSubmitFollowUp');
            const studentId = document.getElementById('formStudentId').value;
            const originalText = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="animate-spin inline-block mr-1">⌛</span> Menyimpan...';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    closeFollowUpModal();

                    // If status is 'daftar_aplikasi', notify and redirect automatically to Data Prospek
                    if (data.is_daftar_aplikasi && data.redirect_url) {
                        alert(data.message + "\n\nAnda akan otomatis dialihkan ke menu Data Prospek untuk tindak lanjut intensif.");
                        window.location.href = data.redirect_url;
                        return;
                    }

                    // Update UI badge in table without full reload
                    const badgeContainer = document.getElementById('status-badge-container-' + studentId);
                    if (badgeContainer) {
                        badgeContainer.innerHTML = `
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold ${data.badge_class}">
                                ${data.status_label}
                            </span>
                            <div class="text-[10px] text-emerald-600 font-bold mt-1">Baru saja diupdate!</div>
                        `;
                    }

                    // Reload page after a brief moment to update counters accurately
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    alert(data.message || 'Gagal menyimpan follow up.');
                }
            } catch (err) {
                console.error(err);
                form.submit(); // fallback to normal submit
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }

        // Modal History Logic
        async function openHistoryModal(studentId) {
            const modal = document.getElementById('historyModal');
            modal.classList.remove('hidden');

            document.getElementById('histStudentName').textContent = 'Memuat data siswa...';
            document.getElementById('histStudentMeta').textContent = '';
            document.getElementById('histTimelineContainer').innerHTML = '<div class="text-center py-8 text-slate-400">Sedang mengambil riwayat...</div>';
            document.getElementById('histAnswersContainer').innerHTML = '';

            try {
                const res = await fetch('/student-follow-ups/' + studentId + '/history', {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await res.json();

                // Fill details
                const s = data.student;
                document.getElementById('histStudentName').textContent = s.name;
                document.getElementById('histStudentMeta').textContent = s.class + ' • ' + s.major + ' • Tanggal Isi: ' + s.created_at;
                document.getElementById('histPhone').textContent = s.phone;
                document.getElementById('histGuardian').textContent = s.guardian_name + ' (' + s.guardian_phone + ')';
                
                const schEl = document.getElementById('histScholarshipInterest');
                if (s.scholarship_interest === 'ya') {
                    schEl.innerHTML = '<span class="text-emerald-700 font-bold">Ya (Berminat Beasiswa UBSI)</span>';
                } else {
                    schEl.innerHTML = '<span class="text-slate-600">Tidak</span>';
                }

                document.getElementById('histCount').textContent = data.follow_ups.length;

                // Populate Timeline
                const timelineContainer = document.getElementById('histTimelineContainer');
                if (data.follow_ups.length === 0) {
                    timelineContainer.innerHTML = `
                        <div class="text-center py-8 text-slate-400 bg-slate-50/50 rounded-2xl border border-dashed border-slate-200">
                            <p class="font-semibold text-slate-600">Belum ada riwayat follow up tercatat.</p>
                            <p class="text-[11px] mt-1">Gunakan tombol "Update" untuk mencatat respon siswa ini.</p>
                        </div>
                    `;
                } else {
                    let html = '';
                    data.follow_ups.forEach((fu) => {
                        html += `
                            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-xs space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-bold ${fu.badge_class}">
                                        ${fu.status_label}
                                    </span>
                                    <span class="text-[11px] text-slate-400">${fu.date} &bull; ${fu.method}</span>
                                </div>
                                ${fu.notes ? `<p class="text-xs text-slate-700 font-medium">"${fu.notes}"</p>` : ''}
                                ${fu.scholarship_interest_notes ? `<p class="text-[11px] text-emerald-800 bg-emerald-50 p-1.5 rounded font-semibold">Beasiswa: ${fu.scholarship_interest_notes}</p>` : ''}
                                ${fu.college_interest_notes ? `<p class="text-[11px] text-blue-800 bg-blue-50 p-1.5 rounded font-medium">Minat Prodi: ${fu.college_interest_notes}</p>` : ''}
                                <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-100">
                                    <span>Petugas: <strong>${fu.officer}</strong></span>
                                    ${fu.next_date ? `<span class="text-amber-600 font-bold">Jadwal lanjut: ${fu.next_date}</span>` : ''}
                                </div>
                            </div>
                        `;
                    });
                    timelineContainer.innerHTML = html;
                }

                // Populate Answers
                const answersContainer = document.getElementById('histAnswersContainer');
                let aHtml = '';
                const scaleNames = {
                    1: 'Sangat tidak setuju',
                    2: 'Tidak setuju',
                    3: 'Netral',
                    4: 'Setuju',
                    5: 'Sangat setuju'
                };

                for (let key in data.questions) {
                    const q = data.questions[key];
                    const score = q.score || '-';
                    const label = scaleNames[score] ? `(${scaleNames[score]})` : '';
                    const scoreColor = score >= 4 ? 'bg-emerald-100 text-emerald-800 font-extrabold' : (score <= 2 ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-800');

                    aHtml += `
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block">${q.section}</span>
                                <p class="text-xs text-slate-800">${q.text}</p>
                            </div>
                            <span class="px-2 py-1 rounded-lg text-xs ${scoreColor} shrink-0">
                                Skor: ${score} ${label}
                            </span>
                        </div>
                    `;
                }
                answersContainer.innerHTML = aHtml;

                if (window.lucide) { window.lucide.createIcons(); }
            } catch (err) {
                console.error(err);
                document.getElementById('histTimelineContainer').innerHTML = '<div class="text-red-500 py-4 text-center">Gagal memuat data.</div>';
            }
        }

        function closeHistoryModal() {
            document.getElementById('historyModal').classList.add('hidden');
        }

        function switchDetailTab(tabName) {
            const histContent = document.getElementById('tabContentHistory');
            const answersContent = document.getElementById('tabContentAnswers');
            const tabBtnHist = document.getElementById('tabBtnHistory');
            const tabBtnAnswers = document.getElementById('tabBtnAnswers');

            if (tabName === 'history') {
                histContent.classList.remove('hidden');
                answersContent.classList.add('hidden');
                tabBtnHist.className = 'py-3 px-4 font-bold text-xs border-b-2 border-brand-600 text-brand-700 flex items-center gap-2';
                tabBtnAnswers.className = 'py-3 px-4 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2';
            } else {
                histContent.classList.add('hidden');
                answersContent.classList.remove('hidden');
                tabBtnAnswers.className = 'py-3 px-4 font-bold text-xs border-b-2 border-brand-600 text-brand-700 flex items-center gap-2';
                tabBtnHist.className = 'py-3 px-4 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center gap-2';
            }
        }

        // ================= PLOTING & DISTRIBUTION SCRIPTS =================
        const queueMetrics = {
            all: {{ $plottingMetrics['total_unassigned'] ?? 0 }},
            kuisioner: {{ $plottingMetrics['unassigned_kuisioner'] ?? 0 }},
            support_event: {{ $plottingMetrics['unassigned_event'] ?? 0 }}
        };

        function openPlotingModal() {
            const modal = document.getElementById('plotingModal');
            if (modal) {
                modal.classList.remove('hidden');
                calculatePlotingEstimate();
                if (window.lucide) { window.lucide.createIcons(); }
            }
        }

        function closePlotingModal() {
            const modal = document.getElementById('plotingModal');
            if (modal) { modal.classList.add('hidden'); }
        }

        function switchPlotingTab(tab) {
            const equalBtn = document.getElementById('tabPlotingEqualBtn');
            const customBtn = document.getElementById('tabPlotingCustomBtn');
            const panelEqual = document.getElementById('panelPlotingEqual');
            const panelCustom = document.getElementById('panelPlotingCustom');
            const inputDistType = document.getElementById('inputDistributionType');

            if (!equalBtn || !customBtn) return;

            if (tab === 'equal') {
                inputDistType.value = 'equal';
                equalBtn.className = 'flex-1 py-3 px-4 font-bold text-xs border-b-2 border-brand-600 text-brand-700 bg-white flex items-center justify-center gap-2 transition';
                customBtn.className = 'flex-1 py-3 px-4 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center justify-center gap-2 transition';
                panelEqual.classList.remove('hidden');
                panelCustom.classList.add('hidden');
                calculatePlotingEstimate();
            } else {
                inputDistType.value = 'custom';
                customBtn.className = 'flex-1 py-3 px-4 font-bold text-xs border-b-2 border-brand-600 text-brand-700 bg-white flex items-center justify-center gap-2 transition';
                equalBtn.className = 'flex-1 py-3 px-4 font-bold text-xs border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center justify-center gap-2 transition';
                panelCustom.classList.remove('hidden');
                panelEqual.classList.add('hidden');
                calculateCustomTotal();
            }
        }

        function toggleEqualLimitInput(enable) {
            const input = document.getElementById('inputEqualTotalLimit');
            if (input) {
                input.disabled = !enable;
                if (enable) {
                    input.focus();
                } else {
                    input.value = '';
                }
                calculatePlotingEstimate();
            }
        }

        function toggleAllOfficersCheckboxes() {
            const checkboxes = document.querySelectorAll('.officer-check');
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
            calculatePlotingEstimate();
        }

        function updatePlotingQueueInfo() {
            calculatePlotingEstimate();
        }

        function calculatePlotingEstimate() {
            const sourceRadio = document.querySelector('input[name="source_filter"]:checked');
            const source = sourceRadio ? sourceRadio.value : 'all';
            const availableQueue = queueMetrics[source] || 0;

            const limitChoice = document.querySelector('input[name="equal_limit_choice"]:checked');
            let dataToDistribute = availableQueue;
            if (limitChoice && limitChoice.value === 'limited') {
                const limitVal = parseInt(document.getElementById('inputEqualTotalLimit')?.value || '0', 10);
                if (limitVal > 0) {
                    dataToDistribute = Math.min(limitVal, availableQueue);
                }
            }

            const checkedOfficers = document.querySelectorAll('.officer-check:checked');
            const officerCount = checkedOfficers.length;

            const estimateEl = document.getElementById('plotingEstimateText');
            if (!estimateEl) return;

            if (availableQueue === 0) {
                estimateEl.innerHTML = '<span class="text-rose-600 font-bold">Tidak ada antrean data baru yang belum di-ploting pada sumber data ini.</span>';
                return;
            }

            if (officerCount === 0) {
                estimateEl.innerHTML = '<span class="text-amber-600 font-bold">Silakan centang minimal satu petugas marketing/CS tujuan.</span>';
                return;
            }

            const perOfficer = Math.floor(dataToDistribute / officerCount);
            const remainder = dataToDistribute % officerCount;

            estimateEl.innerHTML = `Total <strong>${dataToDistribute} data</strong> akan dibagi rata ke <strong>${officerCount} petugas terpilih</strong> (~${perOfficer} ${remainder > 0 ? 'hingga ' + (perOfficer + 1) : ''} data/petugas). Tersisa <strong>${availableQueue - dataToDistribute} data</strong> dalam antrean.`;
        }

        function calculateCustomTotal() {
            const inputs = document.querySelectorAll('.input-custom-allocation');
            let total = 0;
            inputs.forEach(inp => {
                const val = parseInt(inp.value || '0', 10);
                if (val > 0) total += val;
            });
            const display = document.getElementById('customTotalAllocated');
            if (display) {
                display.textContent = total + ' Data';
            }
        }

        // Single Quick Assign Modal
        function openSingleAssignModal(studentId, studentName, currentAssignedTo) {
            const modal = document.getElementById('singleAssignModal');
            if (!modal) return;
            document.getElementById('singleAssignStudentName').textContent = studentName;
            document.getElementById('singleAssignForm').action = '/student-follow-ups/' + studentId + '/assign';
            const select = document.getElementById('singleAssignSelect');
            if (select) {
                select.value = currentAssignedTo ? currentAssignedTo : '';
            }
            modal.classList.remove('hidden');
            if (window.lucide) { window.lucide.createIcons(); }
        }

        function closeSingleAssignModal() {
            const modal = document.getElementById('singleAssignModal');
            if (modal) { modal.classList.add('hidden'); }
        }

        // Checkbox Bulk Selection Logic
        function toggleSelectAll(masterCb) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = masterCb.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checkboxes = document.querySelectorAll('.student-checkbox:checked');
            const count = checkboxes.length;
            const bar = document.getElementById('bulkActionBar');
            const countDisplay = document.getElementById('selectedCountDisplay');
            const container = document.getElementById('bulkSelectedInputsContainer');

            if (!bar) return;

            if (count > 0) {
                bar.classList.remove('hidden');
                bar.classList.add('flex');
                if (countDisplay) countDisplay.textContent = count;

                // Sync inputs to bulk form
                if (container) {
                    container.innerHTML = '';
                    checkboxes.forEach(cb => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'selected_ids[]';
                        input.value = cb.value;
                        container.appendChild(input);
                    });
                }
            } else {
                bar.classList.add('hidden');
                bar.classList.remove('flex');
                if (container) container.innerHTML = '';
                const master = document.getElementById('checkAllRows');
                if (master) master.checked = false;
            }
        }

        function cancelSelection() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
            const master = document.getElementById('checkAllRows');
            if (master) master.checked = false;
            updateSelectedCount();
        }
    </script>
    @endpush
</x-app-layout>
