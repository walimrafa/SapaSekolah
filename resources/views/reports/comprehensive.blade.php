<x-app-layout title="Pusat Laporan Khusus & Ekspor Komprehensif">
    <div class="space-y-6" x-data="{
        dateMode: '{{ $dateMode }}',
        selectedCategory: '{{ $selectedCategory }}'
    }">

        <!-- Header -->
        <div class="p-6 lg:p-8 rounded-3xl bg-gradient-to-r from-slate-950 via-brand-navy to-slate-900 text-white shadow-xl relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-400/20 text-amber-300 text-xs font-bold mb-3 border border-amber-400/30">
                        <i data-lucide="file-spreadsheet" class="w-3.5 h-3.5"></i>
                        <span>Pusat Laporan & Data Export Master</span>
                    </div>
                    <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Laporan Khusus & Ekspor Data</h1>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-2xl">
                        Unduh rekaman operasional lengkap dari awal hingga akhir atau berdasarkan rentang tanggal, periode PMB, dan kategori spesifik dalam format spreadsheet Excel resmi (.xlsx).
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition flex items-center gap-2 border border-slate-700">
                        <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                        <span>Analytics Dashboard</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Generator Form Card -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft">
            <h2 class="text-base font-extrabold text-slate-900 mb-4 flex items-center gap-2">
                <i data-lucide="sliders" class="w-4 h-4 text-brand-600"></i>
                <span>Pengaturan Parameter Laporan</span>
            </h2>

            <form method="GET" action="{{ route('comprehensive-reports.index') }}" id="filterForm" class="space-y-6">
                <!-- Row 1: Mode Rentang Waktu -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <label class="block text-xs font-extrabold text-slate-800 uppercase tracking-wider">
                        1. Rentang Waktu Data Masuk
                    </label>
                    
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="radio" name="date_mode" value="all" x-model="dateMode" class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                            <span>Semua Data Masuk (Dari Awal s/d Saat Ini)</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-slate-700">
                            <input type="radio" name="date_mode" value="custom" x-model="dateMode" class="text-brand-600 focus:ring-brand-500 w-4 h-4">
                            <span>Rentang Tanggal Kustom (Mulai - Selesai)</span>
                        </label>
                    </div>

                    <!-- Custom Date Range Inputs -->
                    <div x-show="dateMode === 'custom'" x-transition class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-200" style="display: none;">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Tanggal Selesai</label>
                            <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:border-brand-500">
                        </div>
                    </div>
                </div>

                <!-- Row 2: Filter Kampus & Periode -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 mb-1">2. Kampus Operasional</label>
                        @if(\App\Services\AppContextService::canSwitchCampus())
                            <select name="campus_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:border-brand-500">
                                <option value="ALL" {{ $campusId === 'ALL' || empty($campusId) ? 'selected' : '' }}>-- Seluruh Kampus (Nasional / Regional) --</option>
                                @foreach($campuses as $c)
                                    <option value="{{ $c->id }}" {{ (string)$campusId === (string)$c->id ? 'selected' : '' }}>
                                        {{ $c->name }} ({{ $c->code }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <div class="px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-700 flex items-center space-x-2">
                                <i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-600"></i>
                                <span>{{ $campuses->first()?->name ?? 'Kampus Penugasan' }}</span>
                            </div>
                            <input type="hidden" name="campus_id" value="{{ $campusId }}">
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 mb-1">3. Filter Periode Akademik / PMB</label>
                        <select name="academic_period_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:border-brand-500">
                            <option value="ALL" {{ $periodId === 'ALL' || empty($periodId) ? 'selected' : '' }}>-- Seluruh Periode (Historis Lengkap) --</option>
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" {{ (string)$periodId === (string)$p->id ? 'selected' : '' }}>
                                    Periode {{ $p->name }} {{ $p->is_current ? '(Aktif Saat Ini)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-extrabold text-slate-800 mb-1">4. Kategori Laporan</label>
                        <select name="category" x-model="selectedCategory" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-bold text-brand-700 bg-amber-50/40 focus:border-brand-500">
                            <option value="all">★ Seluruh Data (Master Multi-Sheet Workbook)</option>
                            <option value="visits">Kunjungan Sekolah & Verifikasi GPS 500m</option>
                            <option value="socializations">Sosialisasi PMB Terlaksana (Jadwal FIX)</option>
                            <option value="questionnaires">Hasil Kuisioner Siswa & Follow Up CRM</option>
                            <option value="prospects">Pipeline Prospek & Mahasiswa Baru Resmi (NIM)</option>
                            <option value="events">Support Event Sekolah & Pendaftar Siswa</option>
                            <option value="schools">Master Data Sekolah & Skor Prioritas</option>
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center justify-between gap-4 pt-4 border-t border-slate-100">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                        <span>Segarkan Preview Data</span>
                    </button>

                    <div class="flex items-center gap-3">
                        <button type="submit" formaction="{{ route('comprehensive-reports.download') }}" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs shadow-lg shadow-emerald-600/20 transition flex items-center gap-2">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh Laporan Excel Sesuai Filter (.xlsx)</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Metric Cards of Available Data -->
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="database" class="w-4 h-4 text-amber-500"></i>
                <span>Data Tersedia Sesuai Parameter Filter Saat Ini</span>
            </h3>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Kunjungan</p>
                    <p class="text-lg font-black text-brand-600">{{ number_format($counts['visits']) }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Sosialisasi FIX</p>
                    <p class="text-lg font-black text-emerald-600">{{ number_format($counts['socializations']) }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Kuisioner</p>
                    <p class="text-lg font-black text-purple-600">{{ number_format($counts['questionnaires']) }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Follow Up</p>
                    <p class="text-lg font-black text-blue-600">{{ number_format($counts['followups']) }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Prospek</p>
                    <p class="text-lg font-black text-amber-600">{{ number_format($counts['prospects']) }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Maba (NIM)</p>
                    <p class="text-lg font-black text-emerald-700">{{ number_format($counts['new_students']) }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Event Sekolah</p>
                    <p class="text-lg font-black text-rose-600">{{ number_format($counts['events']) }}</p>
                </div>
                <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-center">
                    <p class="text-[10px] font-bold text-slate-400 uppercase">Sekolah</p>
                    <p class="text-lg font-black text-slate-800">{{ number_format($counts['schools']) }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Download Cards per Category -->
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 mb-3 flex items-center gap-2">
                <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                <span>Unduh Cepat Berdasarkan Kategori Spesifik</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Card 1: All in One -->
                <div class="bg-gradient-to-br from-slate-900 to-brand-navy p-5 rounded-3xl text-white shadow-lg space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center text-xs font-bold mb-2">★</div>
                        <h4 class="font-extrabold text-sm">Master Multi-Sheet All-In-One</h4>
                        <p class="text-xs text-slate-300 mt-1">Seluruh 7 kategori dalam 1 file Excel lengkap dengan ringkasan eksekutif.</p>
                    </div>
                    <a href="{{ route('comprehensive-reports.download', ['category' => 'all', 'date_mode' => $dateMode, 'start_date' => $startDate, 'end_date' => $endDate, 'campus_id' => $campusId, 'academic_period_id' => $periodId]) }}" class="w-full py-2 px-3 rounded-xl bg-amber-400 hover:bg-amber-300 text-slate-950 font-extrabold text-xs text-center transition">
                        Unduh Rekap Lengkap
                    </a>
                </div>

                <!-- Card 2: Kunjungan -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-soft space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center text-xs font-bold mb-2"><i data-lucide="map-pin" class="w-4 h-4"></i></div>
                        <h4 class="font-extrabold text-sm text-slate-900">Kunjungan Sekolah (GPS 500m)</h4>
                        <p class="text-xs text-slate-500 mt-1">Data PIC sekolah, status, tanggal kunjungan, dan link Google Drive dokumentasi.</p>
                    </div>
                    <a href="{{ route('comprehensive-reports.download', ['category' => 'visits', 'date_mode' => $dateMode, 'start_date' => $startDate, 'end_date' => $endDate, 'campus_id' => $campusId, 'academic_period_id' => $periodId]) }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-brand-600 hover:text-white text-slate-700 font-bold text-xs text-center transition">
                        Unduh Data Kunjungan
                    </a>
                </div>

                <!-- Card 3: Sosialisasi FIX -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-soft space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs font-bold mb-2"><i data-lucide="presentation" class="w-4 h-4"></i></div>
                        <h4 class="font-extrabold text-sm text-slate-900">Sosialisasi PMB Terlaksana</h4>
                        <p class="text-xs text-slate-500 mt-1">Rekapitulasi sekolah yang sudah dilakukan presentasi sosialisasi beserta foto kegiatan.</p>
                    </div>
                    <a href="{{ route('comprehensive-reports.download', ['category' => 'socializations', 'date_mode' => $dateMode, 'start_date' => $startDate, 'end_date' => $endDate, 'campus_id' => $campusId, 'academic_period_id' => $periodId]) }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-emerald-600 hover:text-white text-slate-700 font-bold text-xs text-center transition">
                        Unduh Sosialisasi Terlaksana
                    </a>
                </div>

                <!-- Card 4: Kuisioner & Follow Up Siswa -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-soft space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xs font-bold mb-2"><i data-lucide="clipboard-list" class="w-4 h-4"></i></div>
                        <h4 class="font-extrabold text-sm text-slate-900">Kuisioner & Follow Up Siswa</h4>
                        <p class="text-xs text-slate-500 mt-1">Daftar kontak siswa, wali, minat beasiswa, status follow up CS, dan petugas yang ditugaskan.</p>
                    </div>
                    <a href="{{ route('comprehensive-reports.download', ['category' => 'questionnaires', 'date_mode' => $dateMode, 'start_date' => $startDate, 'end_date' => $endDate, 'campus_id' => $campusId, 'academic_period_id' => $periodId]) }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-purple-600 hover:text-white text-slate-700 font-bold text-xs text-center transition">
                        Unduh Kuisioner & Follow Up
                    </a>
                </div>

                <!-- Card 5: Prospek & Maba -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-soft space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xs font-bold mb-2"><i data-lucide="graduation-cap" class="w-4 h-4"></i></div>
                        <h4 class="font-extrabold text-sm text-slate-900">Pipeline Prospek & Mahasiswa Baru</h4>
                        <p class="text-xs text-slate-500 mt-1">Calon mahasiswa dari tahap pendaftaran akun hingga resmi tervalidasi dengan NIM.</p>
                    </div>
                    <a href="{{ route('comprehensive-reports.download', ['category' => 'prospects', 'date_mode' => $dateMode, 'start_date' => $startDate, 'end_date' => $endDate, 'campus_id' => $campusId, 'academic_period_id' => $periodId]) }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-amber-600 hover:text-white text-slate-700 font-bold text-xs text-center transition">
                        Unduh Prospek & Mahasiswa Baru
                    </a>
                </div>

                <!-- Card 6: Support Event Sekolah -->
                <div class="bg-white p-5 rounded-3xl border border-slate-200 shadow-soft space-y-3 flex flex-col justify-between">
                    <div>
                        <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-xs font-bold mb-2"><i data-lucide="sparkles" class="w-4 h-4"></i></div>
                        <h4 class="font-extrabold text-sm text-slate-900">Support Event & Sponsorship</h4>
                        <p class="text-xs text-slate-500 mt-1">Daftar proposal masuk, dana yang disetujui pimpinan, dan peserta yang terakuisisi.</p>
                    </div>
                    <a href="{{ route('comprehensive-reports.download', ['category' => 'events', 'date_mode' => $dateMode, 'start_date' => $startDate, 'end_date' => $endDate, 'campus_id' => $campusId, 'academic_period_id' => $periodId]) }}" class="w-full py-2 px-3 rounded-xl bg-slate-100 hover:bg-rose-600 hover:text-white text-slate-700 font-bold text-xs text-center transition">
                        Unduh Data Event
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

