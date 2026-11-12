<x-app-layout title="Ploting & Pembagian Data Follow Up Siswa">
    <div class="space-y-6 max-w-6xl mx-auto pb-12">

        <!-- Top Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <nav class="flex items-center space-x-2 text-xs text-slate-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-600 transition">Dashboard</a>
                    <span>&bull;</span>
                    <a href="{{ route('student-follow-ups.index') }}" class="hover:text-brand-600 transition">Follow Up Siswa</a>
                    <span>&bull;</span>
                    <span class="text-slate-800 font-bold">Ploting &amp; Pembagian Data</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="p-2 rounded-2xl bg-amber-400 text-slate-950 shadow-md">
                        <i data-lucide="split" class="w-6 h-6"></i>
                    </span>
                    <span>Ploting &amp; Distribusi Data Leads Siswa</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 max-w-2xl">
                    Bagi dan distribusikan data siswa calon mahasiswa dari Kuisioner Sosialisasi dan Support Event secara proporsional kepada tim Marketing dan Customer Service.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('student-follow-ups.index') }}"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90 shadow-sm transition">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
                    <span>Kembali ke Follow Up</span>
                </a>
            </div>
        </div>

        <!-- Banner Info Kampus Aktif -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-brand-950 to-indigo-950 border border-slate-800 p-5 md:p-6 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5 mr-1 text-amber-400"></i> Hak Akses Pimpinan &amp; Koordinator
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-400/20 text-emerald-300 border border-emerald-400/30">
                            {{ $activeCampus ? $activeCampus->name : 'Semua Kampus' }}
                        </span>
                        @if($activePeriod)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                Periode: {{ $activePeriod->name }}
                            </span>
                        @endif
                    </div>
                    <p class="text-xs sm:text-sm text-slate-300">
                        Sistem hanya mendistribusikan data siswa yang berada dalam kewenangan <strong>{{ $activeCampus ? $activeCampus->name : 'UBSI' }}</strong> dan belum memiliki penugasan (unassigned).
                    </p>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <!-- Total Antrean Belum Dibagi -->
            <div class="p-4 bg-white rounded-2xl border border-amber-300/80 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Antrean Total</p>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center text-amber-600">
                        <i data-lucide="inbox" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-amber-600 mt-1.5">
                    {{ number_format($plottingMetrics['total_unassigned']) }}
                </p>
                <div class="mt-1 flex items-center gap-1.5 text-[11px] text-amber-700 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Belum Di-ploting</span>
                </div>
            </div>

            <!-- Minat Beasiswa (Diutamakan) -->
            <div class="p-4 bg-gradient-to-br from-amber-500/10 via-white to-amber-500/5 rounded-2xl border-2 border-amber-400 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-black text-amber-900 uppercase tracking-wider">Minat Beasiswa</p>
                    <span class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center text-amber-700">
                        <i data-lucide="award" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-amber-700 mt-1.5">
                    {{ number_format($plottingMetrics['unassigned_scholarship_yes']) }}
                </p>
                <div class="mt-1 inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-extrabold bg-amber-400 text-slate-950">
                    ★ Diutamakan Ploting
                </div>
            </div>

            <!-- Tidak Minat Beasiswa -->
            <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Tidak Minat</p>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600">
                        <i data-lucide="user-x" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-700 mt-1.5">
                    {{ number_format($plottingMetrics['unassigned_scholarship_no']) }}
                </p>
                <span class="text-[11px] text-slate-500 font-medium">Beasiswa Reguler</span>
            </div>

            <!-- Dari Kuisioner Sosialisasi -->
            <div class="p-4 bg-white rounded-2xl border border-blue-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Kuisioner</p>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-blue-700 mt-1.5">
                    {{ number_format($plottingMetrics['unassigned_kuisioner']) }}
                </p>
                <span class="text-[11px] text-slate-500 font-medium">Hasil Sosialisasi</span>
            </div>

            <!-- Dari Support Event -->
            <div class="p-4 bg-white rounded-2xl border border-purple-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Support Event</p>
                    <span class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                        <i data-lucide="trophy" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-purple-700 mt-1.5">
                    {{ number_format($plottingMetrics['unassigned_event']) }}
                </p>
                <span class="text-[11px] text-slate-500 font-medium">Peserta Event</span>
            </div>

            <!-- Total Sudah Dibagi -->
            <div class="p-4 bg-white rounded-2xl border border-emerald-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Sudah Di-plot</p>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1.5">
                    {{ number_format($plottingMetrics['total_assigned']) }}
                </p>
                <span class="text-[11px] text-emerald-700 font-medium">{{ $assignableOfficers->count() }} Petugas Aktif</span>
            </div>
        </div>

        <!-- Main Form Distribution Card -->
        <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
            <!-- Tabs Pemilihan Mode -->
            <div class="flex border-b border-slate-200 bg-slate-50/80">
                <button type="button" id="tabEqualBtn" onclick="switchMode('equal')"
                    class="flex-1 py-4 px-6 font-extrabold text-xs sm:text-sm border-b-2 border-brand-600 text-brand-700 bg-white flex items-center justify-center gap-2 transition">
                    <i data-lucide="shuffle" class="w-4 h-4"></i>
                    <span>Mode 1: Bagi Rata Otomatis (Equal Split)</span>
                </button>
                <button type="button" id="tabCustomBtn" onclick="switchMode('custom')"
                    class="flex-1 py-4 px-6 font-extrabold text-xs sm:text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center justify-center gap-2 transition">
                    <i data-lucide="sliders" class="w-4 h-4"></i>
                    <span>Mode 2: Atur Kuota Kustom Per Petugas</span>
                </button>
            </div>

            <form id="distributionMainForm" method="POST" action="{{ route('student-follow-ups.distribute') }}" class="p-6 sm:p-8 space-y-6">
                @csrf
                <input type="hidden" name="distribution_type" id="distributionTypeInput" value="equal">

                <!-- Step 1: Pilihan Sumber Data -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-3">
                    <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                        1. Pilih Sumber Data Leads Siswa yang Akan Di-ploting
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-brand-500 transition shadow-xs">
                            <input type="radio" name="source_filter" value="all" checked onchange="updateQueueStats()" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Semua Sumber Data</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">
                                    Total antrean: <strong class="text-slate-800">{{ number_format($plottingMetrics['total_unassigned']) }}</strong> siswa
                                </span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-brand-500 transition shadow-xs">
                            <input type="radio" name="source_filter" value="kuisioner" onchange="updateQueueStats()" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Hanya Kuisioner Sosialisasi</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">
                                    Tersedia: <strong class="text-blue-700">{{ number_format($plottingMetrics['unassigned_kuisioner']) }}</strong> siswa
                                </span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-brand-500 transition shadow-xs">
                            <input type="radio" name="source_filter" value="support_event" onchange="updateQueueStats()" class="mt-0.5 text-brand-600 focus:ring-brand-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Hanya Hasil Support Event</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">
                                    Tersedia: <strong class="text-purple-700">{{ number_format($plottingMetrics['unassigned_event']) }}</strong> siswa
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Step 2: Filter Minat Beasiswa (Diutamakan yang minat beasiswa) -->
                <div class="bg-amber-50/70 p-5 rounded-2xl border border-amber-300/80 space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <label class="block text-xs font-black text-amber-950 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="award" class="w-4 h-4 text-amber-600"></i>
                            <span>2. Filter Kategori Minat Beasiswa (Prioritas Utama)</span>
                        </label>
                        <span class="inline-flex items-center text-[11px] font-extrabold text-amber-900 bg-amber-300/80 px-2.5 py-0.5 rounded-full border border-amber-400">
                            ★ Diutamakan Minat Beasiswa
                        </span>
                    </div>
                    <p class="text-xs text-amber-900/90 leading-relaxed">
                        Pilih target peminatan beasiswa. Sistem secara otomatis memprioritaskan siswa yang berminat beasiswa (<code class="bg-white/80 px-1 py-0.5 rounded text-amber-900 font-bold">scholarship_interest = 'ya'</code>) pada urutan teratas pembagian.
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-amber-200 bg-white cursor-pointer hover:border-amber-500 transition shadow-xs">
                            <input type="radio" name="scholarship_filter" value="all" checked onchange="updateQueueStats()" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Semua Data (Utamakan Minat Beasiswa)</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">
                                    Membagi seluruh antrean dengan menempatkan yang minat beasiswa di urutan paling depan.
                                </span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-amber-300 bg-white cursor-pointer hover:border-amber-500 transition shadow-xs">
                            <input type="radio" name="scholarship_filter" value="ya" onchange="updateQueueStats()" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="block text-xs font-bold text-amber-900 flex items-center gap-1">
                                    <span>Hanya yang Minat Beasiswa</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-black bg-amber-100 text-amber-800">★ Prioritas</span>
                                </span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">
                                    Tersedia antrean: <strong class="text-amber-700">{{ number_format($plottingMetrics['unassigned_scholarship_yes']) }}</strong> siswa
                                </span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3.5 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-amber-500 transition shadow-xs">
                            <input type="radio" name="scholarship_filter" value="tidak" onchange="updateQueueStats()" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div>
                                <span class="block text-xs font-bold text-slate-800">Hanya yang Tidak/Belum Minat</span>
                                <span class="block text-[11px] text-slate-500 mt-0.5">
                                    Tersedia antrean: <strong class="text-slate-600">{{ number_format($plottingMetrics['unassigned_scholarship_no']) }}</strong> siswa
                                </span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Step 3: Batas Maksimal Data (Opsional) -->
                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80">
                    <label for="totalLimitInput" class="block text-xs font-black text-slate-800 uppercase tracking-wider mb-1">
                        3. Batas Maksimal Data yang Dibagikan (Opsional)
                    </label>
                    <p class="text-xs text-slate-500 mb-3">
                        Kosongkan jika Anda ingin membagikan <strong>seluruh data yang ada dalam antrean</strong> sekaligus.
                    </p>
                    <div class="max-w-xs relative">
                        <input type="number" id="totalLimitInput" name="total_limit" min="1" placeholder="Contoh: 100"
                            oninput="calculateEstimate()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>

                <!-- Mode 1: Equal Split Panel -->
                <div id="equalPanel" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                                4. Pilih Petugas yang Menerima Pembagian Rata
                            </label>
                            <p class="text-xs text-slate-500">
                                Centang petugas Marketing dan Customer Service yang akan bertugas mem-follow up data ini.
                            </p>
                        </div>
                        <button type="button" onclick="toggleSelectAllOfficers()" class="text-xs font-bold text-brand-600 hover:text-brand-800 transition">
                            Pilih Semua Petugas
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @forelse($assignableOfficers as $officer)
                            <label class="flex items-start gap-3 p-3.5 rounded-2xl border border-slate-200 bg-white cursor-pointer hover:border-brand-500 transition shadow-xs">
                                <input type="checkbox" name="officer_ids[]" value="{{ $officer->id }}" checked onchange="calculateEstimate()"
                                    class="officer-checkbox mt-1 text-brand-600 focus:ring-brand-500 rounded">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-1">
                                        <p class="text-xs font-bold text-slate-900 truncate">{{ $officer->name }}</p>
                                        <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                            {{ $officer->role?->name ?? 'Staff' }}
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 truncate">{{ $officer->email }}</p>
                                    <div class="mt-2 flex items-center gap-3 text-[11px] pt-2 border-t border-slate-100">
                                        <span class="text-slate-600">Beban: <strong>{{ number_format($officer->total_assigned_count) }}</strong></span>
                                        <span class="text-amber-600">Pending: <strong>{{ number_format($officer->pending_count) }}</strong></span>
                                    </div>
                                </div>
                            </label>
                        @empty
                            <div class="col-span-full py-8 text-center bg-slate-50 rounded-2xl border border-slate-200">
                                <p class="text-xs text-slate-500 font-semibold">Tidak ditemukan petugas Marketing / Customer Service aktif untuk kampus ini.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Live Estimate Box -->
                    <div id="liveEstimateBox" class="p-4 rounded-2xl bg-gradient-to-r from-blue-50 to-indigo-50/50 border border-blue-200 text-xs text-blue-900 flex items-start gap-3">
                        <i data-lucide="calculator" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5"></i>
                        <div id="estimateText">
                            Menghitung estimasi pembagian data...
                        </div>
                    </div>
                </div>

                <!-- Mode 2: Custom Allocation Panel -->
                <div id="customPanel" class="space-y-4 hidden">
                    <div>
                        <label class="block text-xs font-black text-slate-800 uppercase tracking-wider">
                            4. Masukkan Kuota Jumlah Data Khusus Per Petugas
                        </label>
                        <p class="text-xs text-slate-500">
                            Ketik jumlah data prospek siswa yang ingin Anda berikan kepada masing-masing petugas.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($assignableOfficers as $officer)
                            <div class="p-3.5 rounded-2xl border border-slate-200 bg-white space-y-2.5 shadow-xs">
                                <div class="flex items-center justify-between">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ $officer->name }}</p>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                        {{ $officer->role?->name ?? 'Staff' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="allocations[{{ $officer->id }}]" min="0" placeholder="0"
                                        oninput="calculateCustomTotal()"
                                        class="custom-allocation-input w-full px-3 py-2 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                                    <span class="text-xs text-slate-500 font-medium shrink-0">data</span>
                                </div>
                                <p class="text-[10px] text-slate-400">
                                    Saat ini memegang: <strong>{{ number_format($officer->total_assigned_count) }}</strong> data ({{ number_format($officer->pending_count) }} pending)
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div id="customEstimateBox" class="p-4 rounded-2xl bg-slate-100 border border-slate-200 text-xs text-slate-800 flex items-center justify-between">
                        <span>Total yang akan didistribusikan: <strong id="customTotalCount" class="text-brand-600 font-black">0</strong> data</span>
                        <span id="customRemainingCount" class="text-slate-500">Sisa dalam antrean: <strong>{{ number_format($plottingMetrics['total_unassigned']) }}</strong></span>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="pt-6 border-t border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <a href="{{ route('student-follow-ups.index') }}"
                        class="px-5 py-3 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 text-center transition">
                        Batal &amp; Kembali
                    </a>

                    <button type="submit"
                        class="inline-flex items-center justify-center space-x-2 px-6 py-3.5 rounded-xl text-xs font-black bg-gradient-to-r from-brand-600 via-indigo-600 to-brand-700 hover:from-brand-700 hover:to-indigo-800 text-white shadow-lg shadow-brand-600/30 transition transform hover:-translate-y-0.5">
                        <i data-lucide="send" class="w-4 h-4 text-amber-300"></i>
                        <span>Mulai Distribusikan &amp; Tugaskan Sekarang</span>
                    </button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        const queueData = {
            all: {{ (int) $plottingMetrics['total_unassigned'] }},
            kuisioner: {{ (int) $plottingMetrics['unassigned_kuisioner'] }},
            support_event: {{ (int) $plottingMetrics['unassigned_event'] }},
            scholarship_yes: {{ (int) $plottingMetrics['unassigned_scholarship_yes'] }},
            scholarship_no: {{ (int) $plottingMetrics['unassigned_scholarship_no'] }}
        };

        function switchMode(mode) {
            const equalBtn = document.getElementById('tabEqualBtn');
            const customBtn = document.getElementById('tabCustomBtn');
            const equalPanel = document.getElementById('equalPanel');
            const customPanel = document.getElementById('customPanel');
            const typeInput = document.getElementById('distributionTypeInput');

            if (mode === 'equal') {
                equalBtn.className = 'flex-1 py-4 px-6 font-extrabold text-xs sm:text-sm border-b-2 border-brand-600 text-brand-700 bg-white flex items-center justify-center gap-2 transition';
                customBtn.className = 'flex-1 py-4 px-6 font-extrabold text-xs sm:text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center justify-center gap-2 transition';
                equalPanel.classList.remove('hidden');
                customPanel.classList.add('hidden');
                typeInput.value = 'equal';
                calculateEstimate();
            } else {
                customBtn.className = 'flex-1 py-4 px-6 font-extrabold text-xs sm:text-sm border-b-2 border-brand-600 text-brand-700 bg-white flex items-center justify-center gap-2 transition';
                equalBtn.className = 'flex-1 py-4 px-6 font-extrabold text-xs sm:text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-800 flex items-center justify-center gap-2 transition';
                customPanel.classList.remove('hidden');
                equalPanel.classList.add('hidden');
                typeInput.value = 'custom';
                calculateCustomTotal();
            }
        }

        function getSelectedQueueCount() {
            const checkedSource = document.querySelector('input[name="source_filter"]:checked');
            const source = checkedSource ? checkedSource.value : 'all';
            const checkedScholarship = document.querySelector('input[name="scholarship_filter"]:checked');
            const scholarship = checkedScholarship ? checkedScholarship.value : 'all';

            if (scholarship === 'ya') {
                return queueData.scholarship_yes || 0;
            } else if (scholarship === 'tidak') {
                return queueData.scholarship_no || 0;
            }

            return queueData[source] || 0;
        }

        function updateQueueStats() {
            calculateEstimate();
            calculateCustomTotal();
        }

        function toggleSelectAllOfficers() {
            const checkboxes = document.querySelectorAll('.officer-checkbox');
            const anyUnchecked = Array.from(checkboxes).some(cb => !cb.checked);
            checkboxes.forEach(cb => cb.checked = anyUnchecked);
            calculateEstimate();
        }

        function calculateEstimate() {
            const availableQueue = getSelectedQueueCount();
            const checkedBoxes = document.querySelectorAll('.officer-checkbox:checked');
            const officerCount = checkedBoxes.length;
            const limitInput = document.getElementById('totalLimitInput');
            const estimateEl = document.getElementById('estimateText');

            let dataToDistribute = availableQueue;
            if (limitInput && limitInput.value && parseInt(limitInput.value) > 0) {
                dataToDistribute = Math.min(parseInt(limitInput.value), availableQueue);
            }

            if (availableQueue === 0) {
                estimateEl.innerHTML = `<span class="text-amber-800 font-bold">Tidak ada antrean data leads yang tersedia untuk sumber ini.</span>`;
                return;
            }

            if (officerCount === 0) {
                estimateEl.innerHTML = `<span class="text-rose-600 font-bold">Pilih minimal 1 petugas marketing/CS untuk membagikan data.</span>`;
                return;
            }

            const perOfficer = Math.floor(dataToDistribute / officerCount);
            const remainder = dataToDistribute % officerCount;
            const remainingQueue = availableQueue - dataToDistribute;

            estimateEl.innerHTML = `Total <strong>${dataToDistribute} data</strong> akan dibagi rata ke <strong>${officerCount} petugas terpilih</strong> (~${perOfficer} ${remainder > 0 ? 'hingga ' + (perOfficer + 1) : ''} data/petugas). Tersisa <strong>${remainingQueue} data</strong> dalam antrean.`;
        }

        function calculateCustomTotal() {
            const inputs = document.querySelectorAll('.custom-allocation-input');
            let total = 0;
            inputs.forEach(input => {
                const val = parseInt(input.value) || 0;
                total += val;
            });

            const availableQueue = getSelectedQueueCount();
            const remaining = Math.max(0, availableQueue - total);

            const totalEl = document.getElementById('customTotalCount');
            const remainingEl = document.getElementById('customRemainingCount');

            if (totalEl) totalEl.innerText = total;
            if (remainingEl) {
                if (total > availableQueue) {
                    remainingEl.innerHTML = `<span class="text-rose-600 font-bold">Peringatan: Total alokasi (${total}) melebihi data antrean (${availableQueue})!</span>`;
                } else {
                    remainingEl.innerHTML = `Sisa dalam antrean: <strong>${remaining}</strong> data`;
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            calculateEstimate();
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
    @endpush
</x-app-layout>

