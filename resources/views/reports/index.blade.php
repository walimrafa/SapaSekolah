<x-app-layout title="Laporan & Analytics">
@php
    $appSettings = \App\Services\AppSettingService::all();
    $exportParams = array_merge(request()->query(), ['tab' => $activeTab]);
    $exportUrl = route('reports.export', $exportParams);
@endphp

@push('styles')
<style>
@media print {
    /* Sembunyikan elemen navigasi dan kontrol saat mencetak */
    aside, header, nav, footer, .no-print, button, form, .pagination {
        display: none !important;
    }
    body {
        background: #ffffff !important;
        color: #0f172a !important;
        font-size: 11px !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .print-only {
        display: block !important;
    }
    .main-content, main {
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .table-print {
        border-collapse: collapse !important;
        width: 100% !important;
        font-size: 10px !important;
    }
    .table-print th, .table-print td {
        border: 1px solid #cbd5e1 !important;
        padding: 4px 6px !important;
    }
    .table-print th {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
        font-weight: 700 !important;
    }
    .page-break-avoid {
        page-break-inside: avoid !important;
    }
    .shadow-soft, .shadow-md, .shadow-xl, .shadow {
        box-shadow: none !important;
        border: 1px solid #e2e8f0 !important;
    }
}
.print-only {
    display: none;
}
</style>
@endpush

    <!-- ==================== PRINT ONLY: OFFICIAL LETTERHEAD KOP SURAT ==================== -->
    <div class="print-only mb-6 pb-4 border-b-2 border-slate-900">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                @if(!empty($appSettings['app_logo_url']))
                    <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-16 h-16 object-contain">
                @else
                    <div class="w-14 h-14 bg-slate-900 text-amber-400 font-extrabold flex items-center justify-center rounded-xl text-xl border border-slate-700">
                        UBSI
                    </div>
                @endif
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 uppercase tracking-tight">{{ $appSettings['campus_name'] ?? 'UNIVERSITAS BINA SARANA INFORMATIKA (UBSI) KAMPUS KARAWANG' }}</h2>
                    <p class="text-xs text-slate-600 mt-0.5">{{ $appSettings['campus_address'] ?? 'Jl. Banten No.1, Karangpawitan, Karawang Barat, Karawang, Jawa Barat 41315' }}</p>
                    <p class="text-xs text-slate-500">Telp: {{ $appSettings['campus_phone'] ?? '(0267) 8451234' }} • Email: {{ $appSettings['campus_email'] ?? 'karawang@bsi.ac.id' }} • Web: bsi.ac.id</p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-2.5 py-1 bg-slate-100 text-slate-800 font-extrabold text-[11px] rounded border border-slate-300 uppercase">
                    SAPA SEKOLAH REPORT
                </span>
                <p class="text-[10px] text-slate-500 mt-1">Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</p>
            </div>
        </div>
        <div class="text-center mt-4 pt-2 border-t border-slate-200">
            <h1 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">
                @if($activeTab === 'overview')
                    LAPORAN REKAPITULASI KESELURUHAN MARKETING & ANALYTICS PMB
                @elseif($activeTab === 'visits')
                    LAPORAN DETAIL PROGRES KUNJUNGAN SEKOLAH
                @elseif($activeTab === 'followups')
                    LAPORAN DETAIL PROGRES FOLLOW UP & JADWAL SOSIALISASI
                @elseif($activeTab === 'activities')
                    LAPORAN DETAIL PROGRES AKTIVITAS & EVENT MARKETING
                @elseif($activeTab === 'prospects')
                    LAPORAN DETAIL PROGRES PROSPEK SISWA & PIPELINE CRM PMB
                @elseif($activeTab === 'questionnaires')
                    LAPORAN DETAIL PROGRES HASIL KUISIONER & MINAT BEASISWA
                @elseif($activeTab === 'schools')
                    LAPORAN DETAIL PROGRES PEMETAAN & PRIORITAS SEKOLAH
                @endif
            </h1>
            <p class="text-[11px] text-slate-500">
                Periode: {{ $filter['start_date'] ? \Carbon\Carbon::parse($filter['start_date'])->format('d/m/Y') : 'Awal' }} s/d {{ $filter['end_date'] ? \Carbon\Carbon::parse($filter['end_date'])->format('d/m/Y') : 'Sekarang' }}
                @if($filter['district_id'])
                    • Wilayah: {{ optional($districts->firstWhere('id', $filter['district_id']))->name }}
                @endif
            </p>
        </div>
    </div>

    <!-- ==================== TOP ACTION HEADER ==================== -->
    <div class="no-print flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 text-[10px] font-extrabold bg-brand-50 text-brand-700 border border-brand-200 rounded-lg uppercase tracking-wider">
                    Executive Analytics
                </span>
                <span class="text-xs text-slate-400">•</span>
                <span class="text-xs font-semibold text-slate-500">UBSI Kampus Karawang</span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight mt-1">Laporan &amp; Analytics Marketing</h1>
            <p class="text-xs text-slate-500 mt-0.5">Laporan komprehensif keseluruhan dan rekapitulasi detail per progres operasional lapangan &amp; pipeline konversi PMB.</p>
        </div>
        
        <div class="flex items-center space-x-2.5">
            <button onclick="window.print()" class="px-4 py-2.5 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 font-bold rounded-xl text-xs shadow-sm flex items-center space-x-2 transition hover-lift">
                <i data-lucide="printer" class="w-4 h-4 text-slate-500"></i>
                <span>Cetak / Cetak PDF</span>
            </button>
            <a href="{{ $exportUrl }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 flex items-center space-x-2 transition hover-lift">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Export Excel (.xlsx)</span>
            </a>
        </div>
    </div>

    <!-- ==================== EXECUTIVE KPI CARDS (GLOBAL SUMMARY) ==================== -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <!-- 1. Total Sekolah -->
        <a href="{{ route('reports.index', ['tab' => 'schools']) }}" class="no-underline block group">
            <div class="p-3.5 rounded-2xl bg-white border {{ $activeTab === 'schools' ? 'border-brand-500 ring-2 ring-brand-500/20 shadow-md' : 'border-slate-200/80 shadow-soft' }} hover:border-brand-300 transition">
                <div class="flex items-center justify-between text-slate-400 group-hover:text-brand-600 transition">
                    <p class="text-[10px] font-bold uppercase tracking-wider">1. Sekolah Terdata</p>
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </div>
                <h3 class="text-xl font-black text-slate-900 mt-1">{{ number_format($funnelData['total_schools']) }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5"><span class="font-bold text-brand-600">{{ $funnelData['visited_schools'] }}</span> dikunjungi ({{ $funnelData['visited_percentage'] }}%)</p>
            </div>
        </a>

        <!-- 2. Kunjungan -->
        <a href="{{ route('reports.index', ['tab' => 'visits']) }}" class="no-underline block group">
            <div class="p-3.5 rounded-2xl bg-white border {{ $activeTab === 'visits' ? 'border-blue-500 ring-2 ring-blue-500/20 shadow-md' : 'border-slate-200/80 shadow-soft' }} hover:border-blue-300 transition">
                <div class="flex items-center justify-between text-slate-400 group-hover:text-blue-600 transition">
                    <p class="text-[10px] font-bold uppercase tracking-wider">2. Kunjungan</p>
                    <i data-lucide="navigation-2" class="w-4 h-4"></i>
                </div>
                <h3 class="text-xl font-black text-blue-600 mt-1">{{ number_format($funnelData['total_visits']) }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Realisasi lapangan</p>
            </div>
        </a>

        <!-- 3. Follow Up & Fix -->
        <a href="{{ route('reports.index', ['tab' => 'followups']) }}" class="no-underline block group">
            <div class="p-3.5 rounded-2xl bg-white border {{ $activeTab === 'followups' ? 'border-amber-500 ring-2 ring-amber-500/20 shadow-md' : 'border-slate-200/80 shadow-soft' }} hover:border-amber-300 transition">
                <div class="flex items-center justify-between text-slate-400 group-hover:text-amber-600 transition">
                    <p class="text-[10px] font-bold uppercase tracking-wider">3. Follow Up</p>
                    <i data-lucide="phone-call" class="w-4 h-4"></i>
                </div>
                <h3 class="text-xl font-black text-amber-600 mt-1">{{ number_format($funnelData['total_followups']) }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5"><span class="font-bold text-emerald-600">{{ $funnelData['fixed_schedules'] }}</span> Jadwal FIX</p>
            </div>
        </a>

        <!-- 4. Total Prospek Siswa -->
        <a href="{{ route('reports.index', ['tab' => 'prospects']) }}" class="no-underline block group">
            <div class="p-3.5 rounded-2xl bg-white border {{ $activeTab === 'prospects' ? 'border-purple-500 ring-2 ring-purple-500/20 shadow-md' : 'border-slate-200/80 shadow-soft' }} hover:border-purple-300 transition">
                <div class="flex items-center justify-between text-slate-400 group-hover:text-purple-600 transition">
                    <p class="text-[10px] font-bold uppercase tracking-wider">4. Total Prospek</p>
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
                <h3 class="text-xl font-black text-purple-600 mt-1">{{ number_format($funnelData['total_prospects']) }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Leads terjaring</p>
            </div>
        </a>

        <!-- 5. Registrasi PMB -->
        <a href="{{ route('reports.index', ['tab' => 'prospects', 'status' => 'bayar_registrasi']) }}" class="no-underline block group">
            <div class="p-3.5 rounded-2xl bg-white border border-slate-200/80 shadow-soft hover:border-teal-300 transition">
                <div class="flex items-center justify-between text-slate-400 group-hover:text-teal-600 transition">
                    <p class="text-[10px] font-bold uppercase tracking-wider">5. Registrasi PMB</p>
                    <i data-lucide="file-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-xl font-black text-teal-600 mt-1">{{ number_format($funnelData['registered']) }}</h3>
                <p class="text-[10px] text-slate-500 mt-0.5">Beli formulir / reg</p>
            </div>
        </a>

        <!-- 6. Mahasiswa Baru Fix -->
        <a href="{{ route('reports.index', ['tab' => 'prospects', 'status' => 'mahasiswa']) }}" class="no-underline block group">
            <div class="p-3.5 rounded-2xl bg-gradient-to-br from-slate-900 to-brand-navy text-white shadow-md hover:shadow-lg transition">
                <div class="flex items-center justify-between text-amber-300">
                    <p class="text-[10px] font-bold uppercase tracking-wider">6. Mahasiswa FIX</p>
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                </div>
                <h3 class="text-xl font-black text-white mt-1">{{ number_format($funnelData['mahasiswa']) }}</h3>
                <p class="text-[10px] text-amber-300/90 mt-0.5">Konversi: <span class="font-extrabold text-white">{{ $funnelData['conversion_rate'] }}%</span></p>
            </div>
        </a>
    </div>

    <!-- ==================== TAB NAVIGATION (PILIHAN LAPORAN) ==================== -->
    <div class="no-print mb-6">
        <div class="flex flex-wrap items-center gap-1.5 p-1.5 bg-slate-200/70 backdrop-blur-sm rounded-2xl border border-slate-300/60">
            <!-- Tab: Laporan Keseluruhan -->
            <a href="{{ route('reports.index', array_merge(request()->except('tab', 'page'), ['tab' => 'overview'])) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-2 transition {{ $activeTab === 'overview' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4 {{ $activeTab === 'overview' ? 'text-brand-600' : 'text-slate-400' }}"></i>
                <span>Laporan Keseluruhan</span>
            </a>

            <!-- Tab: Progres Kunjungan -->
            <a href="{{ route('reports.index', array_merge(request()->except('tab', 'page'), ['tab' => 'visits'])) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-2 transition {{ $activeTab === 'visits' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                <i data-lucide="navigation-2" class="w-4 h-4 {{ $activeTab === 'visits' ? 'text-blue-600' : 'text-slate-400' }}"></i>
                <span>Progres Kunjungan</span>
            </a>

            <!-- Tab: Progres Follow Up -->
            <a href="{{ route('reports.index', array_merge(request()->except('tab', 'page'), ['tab' => 'followups'])) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-2 transition {{ $activeTab === 'followups' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                <i data-lucide="phone-call" class="w-4 h-4 {{ $activeTab === 'followups' ? 'text-amber-600' : 'text-slate-400' }}"></i>
                <span>Progres Follow Up</span>
            </a>

            <!-- Tab: Progres Aktivitas -->
            <a href="{{ route('reports.index', array_merge(request()->except('tab', 'page'), ['tab' => 'activities'])) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-2 transition {{ $activeTab === 'activities' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                <i data-lucide="megaphone" class="w-4 h-4 {{ $activeTab === 'activities' ? 'text-rose-600' : 'text-slate-400' }}"></i>
                <span>Progres Aktivitas</span>
            </a>

            <!-- Tab: Progres Prospek -->
            <a href="{{ route('reports.index', array_merge(request()->except('tab', 'page'), ['tab' => 'prospects'])) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-2 transition {{ $activeTab === 'prospects' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                <i data-lucide="users" class="w-4 h-4 {{ $activeTab === 'prospects' ? 'text-purple-600' : 'text-slate-400' }}"></i>
                <span>Progres Siswa / Prospek</span>
            </a>

            <!-- Tab: Progres Kuisioner -->
            <a href="{{ route('reports.index', array_merge(request()->except('tab', 'page'), ['tab' => 'questionnaires'])) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-2 transition {{ $activeTab === 'questionnaires' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                <i data-lucide="clipboard-list" class="w-4 h-4 {{ $activeTab === 'questionnaires' ? 'text-emerald-600' : 'text-slate-400' }}"></i>
                <span>Progres Kuisioner</span>
            </a>

            <!-- Tab: Progres Pemetaan Sekolah -->
            <a href="{{ route('reports.index', array_merge(request()->except('tab', 'page'), ['tab' => 'schools'])) }}"
                class="px-3.5 py-2 rounded-xl text-xs font-bold flex items-center space-x-2 transition {{ $activeTab === 'schools' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-600 hover:text-slate-900 hover:bg-white/50' }}">
                <i data-lucide="building-2" class="w-4 h-4 {{ $activeTab === 'schools' ? 'text-brand-600' : 'text-slate-400' }}"></i>
                <span>Progres Pemetaan Sekolah</span>
            </a>
        </div>
    </div>

    <!-- ==================== FILTER TOOLBAR ==================== -->
    <div class="no-print bg-white rounded-2xl border border-slate-200/80 shadow-soft p-4 mb-6">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            <input type="hidden" name="tab" value="{{ $activeTab }}">

            <!-- Periode Tanggal Mulai -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Mulai Dari</label>
                <input type="date" name="start_date" value="{{ $filter['start_date'] }}"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
            </div>

            <!-- Periode Tanggal Sampai -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Dengan</label>
                <input type="date" name="end_date" value="{{ $filter['end_date'] }}"
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
            </div>

            <!-- Filter Kecamatan (kecuali activities & questionnaires) -->
            @if(in_array($activeTab, ['overview', 'visits', 'followups', 'prospects', 'schools']))
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Kecamatan</label>
                <select name="district_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="">Semua Kecamatan</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ $filter['district_id'] == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Filter Petugas Marketing -->
            @if(in_array($activeTab, ['overview', 'visits', 'followups', 'prospects', 'questionnaires']))
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Petugas Marketing</label>
                <select name="user_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="">Semua Petugas</option>
                    @foreach($marketingUsers as $u)
                        <option value="{{ $u->id }}" {{ $filter['user_id'] == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Filter Status Dinamis Sesuai Tab -->
            @if($activeTab === 'visits')
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Kunjungan</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="all">Semua Status</option>
                    <option value="berhasil" {{ $filter['status'] === 'berhasil' ? 'selected' : '' }}>Berhasil</option>
                    <option value="pending" {{ $filter['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="tidak_berhasil" {{ $filter['status'] === 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil</option>
                    <option value="tidak_bertemu" {{ $filter['status'] === 'tidak_bertemu' ? 'selected' : '' }}>Tidak Bertemu</option>
                </select>
            </div>
            @elseif($activeTab === 'followups')
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Follow Up</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="all">Semua Status</option>
                    <option value="completed" {{ $filter['status'] === 'completed' ? 'selected' : '' }}>Jadwal FIX</option>
                    <option value="progress" {{ $filter['status'] === 'progress' ? 'selected' : '' }}>Koordinasi Jadwal</option>
                    <option value="pending" {{ $filter['status'] === 'pending' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                    <option value="failed" {{ $filter['status'] === 'failed' ? 'selected' : '' }}>Ditolak / Batal</option>
                </select>
            </div>
            @elseif($activeTab === 'prospects')
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status CRM</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="all">Semua Status</option>
                    <option value="daftar_akun" {{ $filter['status'] === 'daftar_akun' ? 'selected' : '' }}>1. Sudah Daftar Akun</option>
                    <option value="bayar_registrasi" {{ $filter['status'] === 'bayar_registrasi' ? 'selected' : '' }}>2. Sudah Bayar Registrasi</option>
                    <option value="ujian_saringan" {{ $filter['status'] === 'ujian_saringan' ? 'selected' : '' }}>3. Sudah Ujian Saringan</option>
                    <option value="bayar_daftar_ulang" {{ $filter['status'] === 'bayar_daftar_ulang' ? 'selected' : '' }}>4. Sudah Bayar Daftar Ulang</option>
                    <option value="berkas_lengkap" {{ $filter['status'] === 'berkas_lengkap' ? 'selected' : '' }}>5. Sudah Melengkapi Berkas</option>
                    <option value="mahasiswa" {{ $filter['status'] === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Baru (Tervalidasi)</option>
                    <option value="tidak_lanjut" {{ $filter['status'] === 'tidak_lanjut' ? 'selected' : '' }}>Tidak Lanjut</option>
                </select>
            </div>
            @elseif($activeTab === 'schools')
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Prioritas Sekolah</label>
                <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
                    <option value="all">Semua Prioritas</option>
                    <option value="A" {{ $filter['status'] === 'A' ? 'selected' : '' }}>Prioritas A</option>
                    <option value="B" {{ $filter['status'] === 'B' ? 'selected' : '' }}>Prioritas B</option>
                    <option value="C" {{ $filter['status'] === 'C' ? 'selected' : '' }}>Prioritas C</option>
                </select>
            </div>
            @endif

            <!-- Pencarian Kata Kunci -->
            <div class="lg:col-span-2">
                <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Cari Data</label>
                <input type="text" name="search" value="{{ $filter['search'] }}" placeholder="Ketik kata kunci..."
                    class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-slate-50/50">
            </div>

            <!-- Tombol Action Filter -->
            <div class="lg:col-span-2 flex items-center space-x-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center space-x-1 shadow-sm">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filter</span>
                </button>
                <a href="{{ route('reports.index', ['tab' => $activeTab]) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded-xl text-xs transition" title="Reset Filter">
                    <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- ==================== TAB CONTENT SECTIONS ==================== -->

    <!-- ==================== 1. TAB: LAPORAN KESELURUHAN (OVERVIEW) ==================== -->
    @if($activeTab === 'overview')
    <div class="space-y-6">

        <!-- Marketing Pipeline Conversion Funnel Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft p-5 page-break-avoid">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight">Conversion Funnel PMB UBSI</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Alur konversi dari pemetaan sekolah sasaran hingga mahasiswa baru closing</p>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
                    Pipeline Karawang
                </span>
            </div>

            <!-- Funnel Steps Visualizer -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
                <!-- Step 1 -->
                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Tahap 1</span>
                        <i data-lucide="building" class="w-3.5 h-3.5 text-slate-400"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-800 mt-1">Pemetaan Sekolah</p>
                    <h4 class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($funnelData['total_schools']) }}</h4>
                    <div class="w-full bg-slate-200 h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-slate-600 h-full rounded-full" style="width: 100%"></div>
                    </div>
                    <span class="text-[10px] text-slate-500 block mt-1">100% Target Pemetaan</span>
                </div>

                <!-- Step 2 -->
                <div class="p-3.5 bg-blue-50/60 rounded-xl border border-blue-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-blue-400 uppercase">Tahap 2</span>
                        <i data-lucide="navigation-2" class="w-3.5 h-3.5 text-blue-500"></i>
                    </div>
                    <p class="text-xs font-bold text-blue-900 mt-1">Kunjungan Sekolah</p>
                    <h4 class="text-xl font-black text-blue-600 mt-0.5">{{ number_format($funnelData['visited_schools']) }}</h4>
                    <div class="w-full bg-blue-200 h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-blue-600 h-full rounded-full" style="width: {{ $funnelData['visited_percentage'] }}%"></div>
                    </div>
                    <span class="text-[10px] text-blue-600 font-bold block mt-1">{{ $funnelData['visited_percentage'] }}% dari Total Target</span>
                </div>

                <!-- Step 3 -->
                <div class="p-3.5 bg-purple-50/60 rounded-xl border border-purple-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-purple-400 uppercase">Tahap 3</span>
                        <i data-lucide="users" class="w-3.5 h-3.5 text-purple-500"></i>
                    </div>
                    <p class="text-xs font-bold text-purple-900 mt-1">Prospek Terkumpul</p>
                    <h4 class="text-xl font-black text-purple-600 mt-0.5">{{ number_format($funnelData['total_prospects']) }}</h4>
                    <div class="w-full bg-purple-200 h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-purple-600 h-full rounded-full" style="width: 100%"></div>
                    </div>
                    <span class="text-[10px] text-purple-600 font-bold block mt-1">{{ number_format($funnelData['total_prospects']) }} Siswa Potensial</span>
                </div>

                <!-- Step 4 -->
                <div class="p-3.5 bg-teal-50/60 rounded-xl border border-teal-100">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-teal-400 uppercase">Tahap 4</span>
                        <i data-lucide="file-check" class="w-3.5 h-3.5 text-teal-500"></i>
                    </div>
                    <p class="text-xs font-bold text-teal-900 mt-1">Registrasi / Bayar</p>
                    <h4 class="text-xl font-black text-teal-600 mt-0.5">{{ number_format($funnelData['registered']) }}</h4>
                    <div class="w-full bg-teal-200 h-1.5 rounded-full mt-2 overflow-hidden">
                        @php $regPct = $funnelData['total_prospects'] > 0 ? round(($funnelData['registered'] / $funnelData['total_prospects']) * 100) : 0; @endphp
                        <div class="bg-teal-600 h-full rounded-full" style="width: {{ $regPct }}%"></div>
                    </div>
                    <span class="text-[10px] text-teal-700 font-bold block mt-1">{{ $regPct }}% dari Prospek</span>
                </div>

                <!-- Step 5 -->
                <div class="p-3.5 bg-gradient-to-br from-slate-900 to-brand-navy rounded-xl text-white">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-amber-300 uppercase">Tahap 5 (Closing)</span>
                        <i data-lucide="award" class="w-3.5 h-3.5 text-amber-400"></i>
                    </div>
                    <p class="text-xs font-bold text-white mt-1">Mahasiswa Baru FIX</p>
                    <h4 class="text-xl font-black text-white mt-0.5">{{ number_format($funnelData['mahasiswa']) }}</h4>
                    <div class="w-full bg-slate-700 h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-amber-400 h-full rounded-full" style="width: {{ $funnelData['conversion_rate'] }}%"></div>
                    </div>
                    <span class="text-[10px] text-amber-300 font-bold block mt-1">{{ $funnelData['conversion_rate'] }}% Rasio Konversi</span>
                </div>
            </div>
        </div>

        <!-- Visualisasi Diagram Analytics & Progres (Charts Section) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
            <!-- Diagram 1: Progres Kunjungan Sekolah per Kecamatan (Bar Chart - 7 Cols) -->
            <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 text-brand-600"></i>
                            <span>Distribusi Sekolah &amp; Realisasi Kunjungan per Wilayah</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Perbandingan total sekolah target vs sekolah yang telah dikunjungi (Top Wilayah)</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-brand-50 text-brand-700 border border-brand-200 rounded-lg">
                        Wilayah Karawang
                    </span>
                </div>
                <div class="h-72">
                    <canvas id="districtVisitChart"></canvas>
                </div>
            </div>

            <!-- Diagram 2: Komposisi Pipeline Prospek CRM (Doughnut Chart - 5 Cols) -->
            <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-4 h-4 text-purple-600"></i>
                            <span>Pipeline Status Prospek CRM</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Sebaran tahapan calon mahasiswa PMB</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg">
                        {{ number_format($funnelData['total_prospects']) }} Prospek
                    </span>
                </div>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="prospectPipelineChart"></canvas>
                </div>
            </div>

            <!-- Diagram 3: Performa Tim Marketing (Bar Chart - 8 Cols) -->
            <div class="lg:col-span-8 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-emerald-600"></i>
                            <span>Performa Capaian Tim Marketing</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Perbandingan jumlah kunjungan sekolah, follow up, dan konversi closing mahasiswa</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                        Staf Lapangan
                    </span>
                </div>
                <div class="h-72">
                    <canvas id="marketingPerformanceChart"></canvas>
                </div>
            </div>

            <!-- Diagram 4: Komposisi Prioritas Sekolah (Doughnut Chart - 4 Cols) -->
            <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                            <span>Proporsi Prioritas Sekolah</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Klasifikasi target scoring A, B, dan C</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
                        {{ number_format($funnelData['total_schools']) }} Sekolah
                    </span>
                </div>
                <div class="h-64 flex items-center justify-center">
                    <canvas id="priorityDistributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 2 Grid Tables: Rekapitulasi per Kecamatan & Performa Tim Marketing -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: Rekapitulasi Sekolah & Potensi per Kecamatan (7 Cols) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden page-break-avoid">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Rekapitulasi Progres per Wilayah</h3>
                        <p class="text-[11px] text-slate-500">Kabupaten Karawang (Kecamatan)</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 bg-slate-100 text-slate-700 rounded-lg">
                        {{ $schoolsSummary->count() }} Kecamatan
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs table-print">
                        <thead class="bg-slate-900 text-white font-bold uppercase">
                            <tr>
                                <th class="py-3 px-3">Kecamatan</th>
                                <th class="py-3 px-2 text-center">Sekolah</th>
                                <th class="py-3 px-2 text-center text-red-300">Prio A</th>
                                <th class="py-3 px-2 text-center text-amber-300">Prio B</th>
                                <th class="py-3 px-2 text-center text-emerald-300">Prio C</th>
                                <th class="py-3 px-2 text-center">Siswa XII</th>
                                <th class="py-3 px-2 text-center text-blue-300">Dikunjungi</th>
                                <th class="py-3 px-2 text-center text-purple-300">Prospek</th>
                                <th class="py-3 px-2 text-center text-amber-300">Mhs Fix</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @php
                                $sumSchools = 0; $sumA = 0; $sumB = 0; $sumC = 0; $sumXII = 0;
                                $sumVisited = 0; $sumProspect = 0; $sumMhs = 0;
                            @endphp
                            @forelse($schoolsSummary as $s)
                                @php
                                    $sumSchools += $s->total_schools;
                                    $sumA += $s->priority_a;
                                    $sumB += $s->priority_b;
                                    $sumC += $s->priority_c;
                                    $sumXII += $s->total_grade_12;
                                    $sumVisited += $s->visited_schools_count;
                                    $sumProspect += $s->prospects_count;
                                    $sumMhs += $s->mahasiswa_count;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-2.5 px-3 font-bold text-slate-900">{{ $s->name }}</td>
                                    <td class="py-2.5 px-2 text-center font-extrabold text-slate-900">{{ $s->total_schools }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-red-600">{{ $s->priority_a }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-amber-600">{{ $s->priority_b }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-emerald-600">{{ $s->priority_c }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-brand-700">{{ number_format($s->total_grade_12) }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-blue-600">{{ $s->visited_schools_count }}</td>
                                    <td class="py-2.5 px-2 text-center font-bold text-purple-600">{{ $s->prospects_count }}</td>
                                    <td class="py-2.5 px-2 text-center font-extrabold text-emerald-600">{{ $s->mahasiswa_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-6 text-center text-slate-400">Belum ada data wilayah.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-slate-100 font-extrabold border-t-2 border-slate-300">
                            <tr>
                                <td class="py-3 px-3 text-slate-900 uppercase">TOTAL KESELURUHAN</td>
                                <td class="py-3 px-2 text-center text-slate-900">{{ number_format($sumSchools) }}</td>
                                <td class="py-3 px-2 text-center text-red-700">{{ number_format($sumA) }}</td>
                                <td class="py-3 px-2 text-center text-amber-700">{{ number_format($sumB) }}</td>
                                <td class="py-3 px-2 text-center text-emerald-700">{{ number_format($sumC) }}</td>
                                <td class="py-3 px-2 text-center text-brand-700">{{ number_format($sumXII) }}</td>
                                <td class="py-3 px-2 text-center text-blue-700">{{ number_format($sumVisited) }}</td>
                                <td class="py-3 px-2 text-center text-purple-700">{{ number_format($sumProspect) }}</td>
                                <td class="py-3 px-2 text-center text-emerald-700">{{ number_format($sumMhs) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Right: Performa Tim Marketing Keseluruhan (5 Cols) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden page-break-avoid">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900">Performa Tim Marketing</h3>
                        <p class="text-[11px] text-slate-500">Aktivitas kunjungan, follow up &amp; konversi</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">
                        {{ $marketingPerformance->count() }} Staf
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs table-print">
                        <thead class="bg-slate-900 text-white font-bold uppercase">
                            <tr>
                                <th class="py-3 px-3">Nama Staf</th>
                                <th class="py-3 px-2 text-center text-blue-300">Kunjungan</th>
                                <th class="py-3 px-2 text-center text-amber-300">Follow Up</th>
                                <th class="py-3 px-2 text-center text-emerald-300">Jadwal Fix</th>
                                <th class="py-3 px-2 text-center text-purple-300">Prospek</th>
                                <th class="py-3 px-2 text-center text-white">Mhs Fix</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($marketingPerformance as $m)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3">
                                        <p class="font-bold text-slate-900 leading-tight">{{ $m->name }}</p>
                                        <span class="text-[10px] text-slate-400 block">{{ $m->email }}</span>
                                    </td>
                                    <td class="py-3 px-2 text-center font-extrabold text-blue-600">{{ $m->school_visits_count }}</td>
                                    <td class="py-3 px-2 text-center font-extrabold text-amber-600">{{ $m->follow_ups_count }}</td>
                                    <td class="py-3 px-2 text-center font-extrabold text-emerald-600">{{ $m->fixed_schedules_count }}</td>
                                    <td class="py-3 px-2 text-center font-extrabold text-purple-600">{{ $m->prospects_count }}</td>
                                    <td class="py-3 px-2 text-center font-black text-slate-900">
                                        <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-900">{{ $m->converted_mahasiswa_count }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400">Belum ada staf marketing terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Visits & Follow Ups Snippet -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 page-break-avoid">
            <!-- Latest Visits -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft p-4">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                        <i data-lucide="navigation-2" class="w-4 h-4 text-blue-600"></i>
                        <span>Kunjungan Lapangan Terkini</span>
                    </h4>
                    <a href="{{ route('reports.index', ['tab' => 'visits']) }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-800">Lihat Semua &rarr;</a>
                </div>
                <div class="space-y-2.5">
                    @forelse($recentVisits as $rv)
                        <div class="p-2.5 bg-slate-50 rounded-xl flex items-center justify-between border border-slate-100">
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $rv->school->name ?? 'Sekolah' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $rv->user->name ?? 'Marketing' }} • {{ $rv->visit_date ? $rv->visit_date->format('d M Y') : '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $rv->status === 'berhasil' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $rv->status ?? 'pending')) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-2">Belum ada kunjungan terbaru.</p>
                    @endforelse
                </div>
            </div>

            <!-- Latest Follow Ups -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft p-4">
                <div class="flex items-center justify-between mb-3 pb-2 border-b border-slate-100">
                    <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center space-x-2">
                        <i data-lucide="phone-call" class="w-4 h-4 text-amber-600"></i>
                        <span>Follow Up &amp; Jadwal Terkini</span>
                    </h4>
                    <a href="{{ route('reports.index', ['tab' => 'followups']) }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-800">Lihat Semua &rarr;</a>
                </div>
                <div class="space-y-2.5">
                    @forelse($recentFollowups as $rf)
                        <div class="p-2.5 bg-slate-50 rounded-xl flex items-center justify-between border border-slate-100">
                            <div>
                                <p class="text-xs font-bold text-slate-900">{{ $rf->school->name ?? 'Sekolah' }}</p>
                                <p class="text-[10px] text-slate-500">{{ $rf->user->name ?? 'Marketing' }} • {{ $rf->followup_date ? $rf->followup_date->format('d M Y') : '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded {{ $rf->is_schedule_fixed || $rf->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ \App\Models\FollowUp::statusLabels()[$rf->status] ?? ucfirst($rf->status) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 py-2">Belum ada follow up terbaru.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ==================== 2. TAB: PROGRES KUNJUNGAN SEKOLAH ==================== -->
    @if($activeTab === 'visits')
    <div class="space-y-6">
        <!-- Sub-KPI Banner Kunjungan -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 no-print">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Total Kunjungan</p>
                <h4 class="text-lg font-black text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</h4>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                <p class="text-[10px] font-bold text-emerald-600 uppercase">Kunjungan Berhasil</p>
                <h4 class="text-lg font-black text-emerald-700 mt-0.5">{{ number_format($stats['berhasil']) }}</h4>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 shadow-sm">
                <p class="text-[10px] font-bold text-amber-600 uppercase">Pending / Jadwal</p>
                <h4 class="text-lg font-black text-amber-700 mt-0.5">{{ number_format($stats['pending']) }}</h4>
            </div>
            <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 shadow-sm">
                <p class="text-[10px] font-bold text-rose-600 uppercase">Tidak Berhasil</p>
                <h4 class="text-lg font-black text-rose-700 mt-0.5">{{ number_format($stats['tidak_berhasil']) }}</h4>
            </div>
            <div class="p-3 bg-slate-100 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-500 uppercase">Tidak Bertemu</p>
                <h4 class="text-lg font-black text-slate-700 mt-0.5">{{ number_format($stats['tidak_bertemu']) }}</h4>
            </div>
        </div>

        <!-- Diagram Status Kunjungan -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
            <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-600"></i>
                        <span>Proporsi Status Keberhasilan Kunjungan</span>
                    </h3>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                        {{ number_format($stats['total']) }} Total Log
                    </span>
                </div>
                <div class="h-60 flex items-center justify-center">
                    <canvas id="visitStatusChart"></canvas>
                </div>
            </div>
            <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-center">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Ringkasan Efektivitas Kunjungan Lapangan</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-emerald-700">Tingkat Keberhasilan Kunjungan Lapangan</span>
                            <span class="text-emerald-800 font-extrabold">{{ $stats['total'] > 0 ? round(($stats['berhasil'] / $stats['total']) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $stats['total'] > 0 ? round(($stats['berhasil'] / $stats['total']) * 100, 1) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3 pt-2 text-center text-xs">
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-emerald-600 font-bold block uppercase">Berhasil</span>
                            <span class="text-lg font-black text-emerald-800">{{ number_format($stats['berhasil']) }}</span>
                            <span class="text-[10px] text-emerald-600/80 block mt-0.5">Sosialisasi Terlaksana</span>
                        </div>
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-100">
                            <span class="text-[10px] text-amber-600 font-bold block uppercase">Pending</span>
                            <span class="text-lg font-black text-amber-800">{{ number_format($stats['pending']) }}</span>
                            <span class="text-[10px] text-amber-600/80 block mt-0.5">Tindak Lanjut Jadwal</span>
                        </div>
                        <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                            <span class="text-[10px] text-rose-600 font-bold block uppercase">Kendala / Batal</span>
                            <span class="text-lg font-black text-rose-800">{{ number_format($stats['tidak_berhasil'] + $stats['tidak_bertemu']) }}</span>
                            <span class="text-[10px] text-rose-600/80 block mt-0.5">Tidak Bertemu / Gagal</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Detail Kunjungan -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Data Detail Kunjungan Sekolah</h3>
                    <p class="text-[11px] text-slate-500">Rekapitulasi log kunjungan lapangan oleh tim marketing UBSI</p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">
                    Menampilkan {{ $visits->total() }} Data
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs table-print">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3 px-3">Tanggal</th>
                            <th class="py-3 px-3">Nama Sekolah</th>
                            <th class="py-3 px-3">Kecamatan</th>
                            <th class="py-3 px-3">Petugas</th>
                            <th class="py-3 px-3">Penerima / PIC</th>
                            <th class="py-3 px-2 text-center">Kelas 12</th>
                            <th class="py-3 px-3">Metode Sosialisasi</th>
                            <th class="py-3 px-3">Hasil / Catatan</th>
                            <th class="py-3 px-2 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($visits as $v)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $v->visit_date ? $v->visit_date->format('d/m/Y') : '-' }}
                                    @if($v->start_time)
                                        <span class="block text-[10px] text-slate-400">{{ substr($v->start_time, 0, 5) }} WIB</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-extrabold text-slate-900 block">{{ $v->school->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-400">NPSN: {{ $v->school->npsn ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3 font-medium text-slate-700">{{ $v->school->district->name ?? '-' }}</td>
                                <td class="py-3 px-3 font-semibold text-brand-700">{{ $v->user->name ?? '-' }}</td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-800 block">{{ $v->recipient_name ?: ($v->contact_person ?: '-') }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $v->recipient_position ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    @if($v->grade_12_class_count)
                                        <span class="font-extrabold text-slate-900">{{ $v->grade_12_class_count }} Kls</span>
                                        @if($v->students_per_class)
                                            <span class="block text-[10px] text-slate-500">({{ $v->grade_12_class_count * $v->students_per_class }} Siswa)</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-700">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px] font-semibold text-slate-800">
                                        {{ $v->socialization_method ?: ($v->purpose ?: 'Kunjungan') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-xs">
                                    <p class="line-clamp-2">{{ $v->result ?: ($v->notes ?: '-') }}</p>
                                    @if($v->next_followup_date)
                                        <span class="text-[10px] text-amber-600 font-bold block mt-0.5">Follow up: {{ $v->next_followup_date->format('d/m/Y') }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center whitespace-nowrap">
                                    @if($v->status === 'berhasil')
                                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 font-bold rounded-lg text-[10px] border border-emerald-200">
                                            Berhasil
                                        </span>
                                    @elseif($v->status === 'pending')
                                        <span class="px-2.5 py-1 bg-amber-100 text-amber-800 font-bold rounded-lg text-[10px] border border-amber-200">
                                            Pending
                                        </span>
                                    @elseif($v->status === 'tidak_berhasil')
                                        <span class="px-2.5 py-1 bg-rose-100 text-rose-800 font-bold rounded-lg text-[10px] border border-rose-200">
                                            Gagal
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-slate-100 text-slate-700 font-bold rounded-lg text-[10px] border border-slate-300">
                                            {{ ucfirst(str_replace('_', ' ', $v->status)) }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    Tidak ada data kunjungan yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($visits->hasPages())
                <div class="p-4 border-t border-slate-100 no-print">
                    {{ $visits->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ==================== 3. TAB: PROGRES FOLLOW UP SEKOLAH ==================== -->
    @if($activeTab === 'followups')
    <div class="space-y-6">
        <!-- Sub-KPI Banner Follow Up -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 no-print">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Total Follow Up</p>
                <h4 class="text-lg font-black text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</h4>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                <p class="text-[10px] font-bold text-emerald-600 uppercase">Jadwal FIX Terbit</p>
                <h4 class="text-lg font-black text-emerald-700 mt-0.5">{{ number_format($stats['completed']) }}</h4>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 shadow-sm">
                <p class="text-[10px] font-bold text-blue-600 uppercase">Koordinasi Jadwal</p>
                <h4 class="text-lg font-black text-blue-700 mt-0.5">{{ number_format($stats['progress']) }}</h4>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 shadow-sm">
                <p class="text-[10px] font-bold text-amber-600 uppercase">Menunggu Konfirmasi</p>
                <h4 class="text-lg font-black text-amber-700 mt-0.5">{{ number_format($stats['pending']) }}</h4>
            </div>
            <div class="p-3 bg-purple-50 rounded-xl border border-purple-200 shadow-sm">
                <p class="text-[10px] font-bold text-purple-600 uppercase">Target Siswa Terkunci</p>
                <h4 class="text-lg font-black text-purple-700 mt-0.5">{{ number_format($stats['total_audience']) }}</h4>
            </div>
        </div>

        <!-- Diagram Status Follow Up & Jadwal -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
            <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-amber-500"></i>
                        <span>Proporsi Status Follow Up &amp; Jadwal</span>
                    </h3>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
                        {{ number_format($stats['total']) }} Follow Up
                    </span>
                </div>
                <div class="h-60 flex items-center justify-center">
                    <canvas id="followupStatusChart"></canvas>
                </div>
            </div>
            <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-center">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Capaian Closing Jadwal Sosialisasi</h4>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-bold mb-1">
                            <span class="text-emerald-700">Persentase Jadwal FIXED Terbit</span>
                            <span class="text-emerald-800 font-extrabold">{{ $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2.5 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $stats['total'] > 0 ? round(($stats['completed'] / $stats['total']) * 100, 1) : 0 }}%"></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3 pt-2 text-center text-xs">
                        <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                            <span class="text-[10px] text-emerald-600 font-bold block uppercase">Jadwal Fix</span>
                            <span class="text-lg font-black text-emerald-800">{{ number_format($stats['completed']) }}</span>
                            <span class="text-[10px] text-emerald-600/80 block mt-0.5">Siap Sosialisasi</span>
                        </div>
                        <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                            <span class="text-[10px] text-blue-600 font-bold block uppercase">Koordinasi</span>
                            <span class="text-lg font-black text-blue-800">{{ number_format($stats['progress']) }}</span>
                            <span class="text-[10px] text-blue-600/80 block mt-0.5">Penetapan Tanggal</span>
                        </div>
                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-100">
                            <span class="text-[10px] text-purple-600 font-bold block uppercase">Target Audience</span>
                            <span class="text-lg font-black text-purple-800">{{ number_format($stats['total_audience']) }}</span>
                            <span class="text-[10px] text-purple-600/80 block mt-0.5">Siswa Kls XII</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Detail Follow Up -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Data Detail Follow Up &amp; Jadwal Sosialisasi</h3>
                    <p class="text-[11px] text-slate-500">Hasil koordinasi jadwal presentasi kelas, edufair, dan MoU dengan sekolah</p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
                    Menampilkan {{ $followups->total() }} Data
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs table-print">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3 px-3">Tgl Follow Up</th>
                            <th class="py-3 px-3">Nama Sekolah</th>
                            <th class="py-3 px-3">Petugas</th>
                            <th class="py-3 px-2 text-center">Metode</th>
                            <th class="py-3 px-3">PIC Sekolah</th>
                            <th class="py-3 px-3">Status Follow Up</th>
                            <th class="py-3 px-3">Jadwal Sosialisasi FIX</th>
                            <th class="py-3 px-2 text-center">Est. Siswa</th>
                            <th class="py-3 px-3">Catatan / Hasil</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($followups as $f)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $f->followup_date ? $f->followup_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-extrabold text-slate-900 block">{{ $f->school->name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $f->school->district->name ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3 font-semibold text-brand-700">{{ $f->user->name ?? '-' }}</td>
                                <td class="py-3 px-2 text-center">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-700 uppercase">
                                        {{ $f->method ?? 'WA' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-bold text-slate-900 block">{{ $f->pic_name ?? '-' }}</span>
                                    <span class="text-[10px] text-slate-500">{{ $f->pic_position ?? '-' }} • {{ $f->pic_phone ?? '-' }}</span>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg border {{ $f->status_badge_class ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ \App\Models\FollowUp::statusLabels()[$f->status] ?? ucfirst($f->status) }}
                                    </span>
                                </td>
                                <td class="py-3 px-3">
                                    @if($f->is_schedule_fixed || $f->fixed_date)
                                        <div class="p-1.5 bg-emerald-50 rounded-lg border border-emerald-200">
                                            <span class="text-xs font-black text-emerald-800 block">
                                                {{ $f->fixed_date ? $f->fixed_date->format('d/m/Y') : '-' }} {{ $f->fixed_time ? '(' . $f->fixed_time . ')' : '' }}
                                            </span>
                                            <span class="text-[10px] text-emerald-700 block">Lokasi: {{ $f->socialization_location ?? 'Sekolah' }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Belum terkunci</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center font-extrabold text-slate-800">
                                    {{ $f->target_audience_count ? number_format($f->target_audience_count) : '-' }}
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-xs">
                                    <p class="line-clamp-2">{{ $f->notes ?: ($f->result ?: '-') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    Tidak ada data follow up yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($followups->hasPages())
                <div class="p-4 border-t border-slate-100 no-print">
                    {{ $followups->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ==================== 4. TAB: PROGRES AKTIVITAS MARKETING ==================== -->
    @if($activeTab === 'activities')
    <div class="space-y-6">
        <!-- Sub-KPI Banner Aktivitas -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 no-print">
            <div class="p-3.5 bg-white rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Total Event / Kegiatan</p>
                <h4 class="text-xl font-black text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</h4>
            </div>
            <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-200 shadow-sm">
                <p class="text-[10px] font-bold text-rose-600 uppercase">Total Estimasi Partisipan</p>
                <h4 class="text-xl font-black text-rose-700 mt-0.5">{{ number_format($stats['total_participants']) }} Siswa</h4>
            </div>
            <div class="p-3.5 bg-brand-50 rounded-xl border border-brand-200 shadow-sm">
                <p class="text-[10px] font-bold text-brand-600 uppercase">Total Sekolah Terlibat</p>
                <h4 class="text-xl font-black text-brand-700 mt-0.5">{{ number_format($stats['total_schools']) }} Sekolah</h4>
            </div>
        </div>

        <!-- Tabel Detail Aktivitas -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Data Aktivitas &amp; Event Sosialisasi</h3>
                    <p class="text-[11px] text-slate-500">Rekap kegiatan expo, sponsorship, seminar, presentasi dan MoU</p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg">
                    Menampilkan {{ $activities->total() }} Kegiatan
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs table-print">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3 px-3">Nama Kegiatan</th>
                            <th class="py-3 px-3">Tipe Aktivitas</th>
                            <th class="py-3 px-3">Periode Pelaksanaan</th>
                            <th class="py-3 px-3">Sekolah yang Terlibat</th>
                            <th class="py-3 px-2 text-center">Partisipan</th>
                            <th class="py-3 px-3">Deskripsi / Capaian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($activities as $act)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3">
                                    <span class="font-extrabold text-slate-900 block text-sm">{{ $act->name }}</span>
                                    @if($act->document_name)
                                        <span class="inline-flex items-center space-x-1 text-[10px] text-blue-600 font-bold mt-1">
                                            <i data-lucide="paperclip" class="w-3 h-3"></i>
                                            <span>{{ $act->document_name }}</span>
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-3">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-800 font-bold rounded-lg text-[10px]">
                                        {{ ucfirst($act->activity_type ?? 'Sosialisasi') }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $act->start_date ? $act->start_date->format('d/m/Y') : '-' }}
                                    @if($act->end_date && $act->end_date != $act->start_date)
                                        s/d {{ $act->end_date->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-800">
                                    @if($act->schoolActivities->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($act->schoolActivities as $sa)
                                                <span class="px-2 py-0.5 bg-brand-50 text-brand-700 text-[10px] font-bold rounded">
                                                    {{ $sa->school->name ?? 'Sekolah' }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400">Umum / Semua Sekolah</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center font-black text-rose-600">
                                    {{ $act->schoolActivities->sum('participants') ?: '-' }}
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-sm">
                                    <p class="line-clamp-2">{{ $act->description ?? '-' }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    Tidak ada data aktivitas yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($activities->hasPages())
                <div class="p-4 border-t border-slate-100 no-print">
                    {{ $activities->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ==================== 5. TAB: PROGRES SISWA / CRM PROSPEK ==================== -->
    @if($activeTab === 'prospects')
    <div class="space-y-6">
        <!-- Sub-KPI Banner Pipeline Prospek: 5 Tahapan Resmi + Maba -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2.5 no-print">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[9px] font-bold text-slate-400 uppercase">Total Calon</p>
                <h4 class="text-base font-black text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</h4>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 shadow-sm">
                <p class="text-[9px] font-bold text-blue-600 uppercase">1. Daftar Akun</p>
                <h4 class="text-base font-black text-blue-700 mt-0.5">{{ number_format($stats['daftar_akun'] ?? 0) }}</h4>
            </div>
            <div class="p-3 bg-cyan-50 rounded-xl border border-cyan-200 shadow-sm">
                <p class="text-[9px] font-bold text-cyan-600 uppercase">2. Bayar Registrasi</p>
                <h4 class="text-base font-black text-cyan-700 mt-0.5">{{ number_format($stats['bayar_registrasi'] ?? 0) }}</h4>
            </div>
            <div class="p-3 bg-purple-50 rounded-xl border border-purple-200 shadow-sm">
                <p class="text-[9px] font-bold text-purple-600 uppercase">3. Ujian Saringan</p>
                <h4 class="text-base font-black text-purple-700 mt-0.5">{{ number_format($stats['ujian_saringan'] ?? 0) }}</h4>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 shadow-sm">
                <p class="text-[9px] font-bold text-amber-600 uppercase">4. Daftar Ulang</p>
                <h4 class="text-base font-black text-amber-700 mt-0.5">{{ number_format($stats['bayar_daftar_ulang'] ?? 0) }}</h4>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-300 shadow-sm">
                <p class="text-[9px] font-bold text-emerald-700 uppercase">5. Lengkap Berkas</p>
                <h4 class="text-base font-black text-emerald-700 mt-0.5">{{ number_format($stats['berkas_lengkap'] ?? 0) }}</h4>
            </div>
            <div class="p-3 bg-slate-900 text-white rounded-xl border border-slate-800 shadow-sm">
                <p class="text-[9px] font-bold text-amber-300 uppercase">Maba Tervalidasi</p>
                <h4 class="text-base font-black text-white mt-0.5">{{ number_format($stats['mahasiswa'] ?? 0) }}</h4>
            </div>
        </div>

        <!-- Diagram Pipeline Prospek Siswa PMB -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
            <!-- Left: Bar Chart Tahapan Pipeline -->
            <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="bar-chart-3" class="w-4 h-4 text-purple-600"></i>
                            <span>Distribusi Tahapan Prospek Siswa</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Jumlah calon mahasiswa pada masing-masing tahapan funnel CRM</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg">
                        {{ number_format($stats['total']) }} Total Leads
                    </span>
                </div>
                <div class="h-64">
                    <canvas id="prospectStageChart"></canvas>
                </div>
            </div>

            <!-- Right: Doughnut Chart Konversi Closing -->
            <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-600"></i>
                            <span>Rasio Konversi PMB</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Proporsi Mahasiswa Fix vs Dalam Proses vs Tidak Lanjut</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                        {{ $stats['total'] > 0 ? round(($stats['mahasiswa'] / $stats['total']) * 100, 1) : 0 }}% Closing
                    </span>
                </div>
                <div class="h-60 flex items-center justify-center">
                    <canvas id="prospectConversionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabel Detail Prospek -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Database Detail Prospek Siswa</h3>
                    <p class="text-[11px] text-slate-500">Pipeline CRM dan status konversi pendaftaran mahasiswa baru</p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg">
                    Menampilkan {{ $prospects->total() }} Siswa
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs table-print">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3 px-3">Tgl Masuk</th>
                            <th class="py-3 px-3">Nama Siswa</th>
                            <th class="py-3 px-2 text-center">JK</th>
                            <th class="py-3 px-3">Asal Sekolah</th>
                            <th class="py-3 px-3">Kecamatan</th>
                            <th class="py-3 px-3">Program Studi Minat</th>
                            <th class="py-3 px-3">No. WhatsApp</th>
                            <th class="py-3 px-3">Petugas Handler</th>
                            <th class="py-3 px-2 text-center">Status Pipeline</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($prospects as $p)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $p->created_at ? $p->created_at->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-extrabold text-slate-900 block">{{ $p->name }}</span>
                                    @if($p->email)
                                        <span class="text-[10px] text-slate-400">{{ $p->email }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center font-bold text-slate-600">{{ $p->gender ?? '-' }}</td>
                                <td class="py-3 px-3 font-medium text-slate-800">{{ $p->school->name ?? '-' }}</td>
                                <td class="py-3 px-3 text-slate-600">{{ $p->school->district->name ?? '-' }}</td>
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 bg-brand-50 text-brand-700 font-bold rounded text-[11px]">
                                        {{ $p->interest_program ?: 'Belum Memilih' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-mono font-semibold text-slate-800">
                                    @if($p->phone)
                                        <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $p->phone)) }}" target="_blank" class="text-emerald-700 hover:underline flex items-center space-x-1">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>{{ $p->phone }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 font-semibold text-brand-700">{{ $p->marketingUser->name ?? '-' }}</td>
                                <td class="py-3 px-2 text-center whitespace-nowrap">
                                    @php
                                        $badgeColor = match($p->status) {
                                            'mahasiswa' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-extrabold',
                                            'berkas_lengkap' => 'bg-purple-100 text-purple-800 border-purple-300 font-bold',
                                            'bayar_daftar_ulang' => 'bg-indigo-100 text-indigo-800 border-indigo-300 font-bold',
                                            'ujian_saringan' => 'bg-cyan-100 text-cyan-800 border-cyan-300 font-bold',
                                            'bayar_registrasi' => 'bg-amber-100 text-amber-800 border-amber-300 font-bold',
                                            'daftar_akun' => 'bg-blue-100 text-blue-800 border-blue-300',
                                            'tidak_lanjut' => 'bg-rose-100 text-rose-800 border-rose-300',
                                            default => 'bg-slate-100 text-slate-700 border-slate-300',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-[10px] rounded-lg border {{ $badgeColor }}">
                                        {{ $p->status_label }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    Tidak ada data prospek siswa yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($prospects->hasPages())
                <div class="p-4 border-t border-slate-100 no-print">
                    {{ $prospects->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ==================== 6. TAB: PROGRES KUISIONER & BEASISWA ==================== -->
    @if($activeTab === 'questionnaires')
    <div class="space-y-6">
        <!-- Sub-KPI Banner Kuisioner -->
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 no-print">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Total Kuisioner</p>
                <h4 class="text-lg font-black text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</h4>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-500 uppercase">Belum Dihubungi</p>
                <h4 class="text-lg font-black text-slate-700 mt-0.5">{{ number_format($stats['belum_dihubungi']) }}</h4>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 shadow-sm">
                <p class="text-[10px] font-bold text-blue-600 uppercase">Respon</p>
                <h4 class="text-lg font-black text-blue-700 mt-0.5">{{ number_format($stats['respon']) }}</h4>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 shadow-sm">
                <p class="text-[10px] font-bold text-amber-600 uppercase">Berminat</p>
                <h4 class="text-lg font-black text-amber-700 mt-0.5">{{ number_format($stats['berminat']) }}</h4>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                <p class="text-[10px] font-bold text-emerald-700 uppercase">Closing Mahasiswa</p>
                <h4 class="text-lg font-black text-emerald-700 mt-0.5">{{ number_format($stats['closing']) }}</h4>
            </div>
            <div class="p-3 bg-purple-50 rounded-xl border border-purple-200 shadow-sm">
                <p class="text-[10px] font-bold text-purple-700 uppercase">Minat Beasiswa</p>
                <h4 class="text-lg font-black text-purple-700 mt-0.5">{{ number_format($stats['scholarship_interested']) }}</h4>
            </div>
        </div>

        <!-- Diagram Respon Kuisioner & Minat Beasiswa -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
            <!-- Left: Status Follow Up Kuisioner -->
            <div class="lg:col-span-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="pie-chart" class="w-4 h-4 text-emerald-600"></i>
                            <span>Status Tindak Lanjut Kuisioner</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Sebaran progres follow-up respon survei siswa</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                        {{ number_format($stats['total']) }} Survei
                    </span>
                </div>
                <div class="h-60 flex items-center justify-center">
                    <canvas id="questionnaireFollowupChart"></canvas>
                </div>
            </div>

            <!-- Right: Proporsi Minat Beasiswa -->
            <div class="lg:col-span-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-purple-600"></i>
                            <span>Proporsi Minat Beasiswa Siswa</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Siswa yang mengajukan minat program beasiswa</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-purple-50 text-purple-700 border border-purple-200 rounded-lg">
                        {{ number_format($stats['scholarship_interested']) }} Siswa
                    </span>
                </div>
                <div class="h-60 flex items-center justify-center">
                    <canvas id="questionnaireScholarshipChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabel Detail Kuisioner -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Data Detail Kuisioner Minat &amp; Beasiswa</h3>
                    <p class="text-[11px] text-slate-500">Respon survei siswa SMA/SMK sederajat dan tindak lanjut marketing</p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg">
                    Menampilkan {{ $questionnaires->total() }} Data
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs table-print">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3 px-3">Waktu Masuk</th>
                            <th class="py-3 px-3">Nama Siswa</th>
                            <th class="py-3 px-3">Kelas / Jurusan</th>
                            <th class="py-3 px-3">No. WhatsApp</th>
                            <th class="py-3 px-2 text-center">Minat Beasiswa</th>
                            <th class="py-3 px-3">Status Follow Up</th>
                            <th class="py-3 px-3">Petugas Handler</th>
                            <th class="py-3 px-3">Catatan Follow Up</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($questionnaires as $q)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3 font-semibold text-slate-700 whitespace-nowrap">
                                    {{ $q->created_at ? $q->created_at->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="py-3 px-3">
                                    <span class="font-extrabold text-slate-900 block">{{ $q->student_name }}</span>
                                    @if($q->guardian_name)
                                        <span class="text-[10px] text-slate-400">Wali: {{ $q->guardian_name }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 text-slate-800">
                                    {{ $q->student_class ?? '-' }} {{ $q->student_major ? '(' . $q->student_major . ')' : '' }}
                                </td>
                                <td class="py-3 px-3 font-mono font-semibold text-slate-800">
                                    @if($q->student_phone)
                                        <a href="https://wa.me/{{ $q->formatted_student_phone }}" target="_blank" class="text-emerald-700 hover:underline flex items-center space-x-1">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>{{ $q->student_phone }}</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center font-bold">
                                    @if(strtolower($q->scholarship_interest ?? '') === 'ya')
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 rounded font-bold text-[10px]">YA</span>
                                    @else
                                        <span class="text-slate-400">Tidak</span>
                                    @endif
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg {{ $q->followup_badge_class }}">
                                        {{ $q->followup_status_label }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 font-semibold text-brand-700">
                                    {{ $q->lastFollowedUpBy->name ?? 'Belum Ada' }}
                                </td>
                                <td class="py-3 px-3 text-slate-600 max-w-xs">
                                    <p class="line-clamp-2">{{ $q->followup_notes ?: ($q->college_interest_notes ?: '-') }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    Tidak ada data kuisioner yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($questionnaires->hasPages())
                <div class="p-4 border-t border-slate-100 no-print">
                    {{ $questionnaires->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ==================== 7. TAB: PROGRES PEMETAAN SEKOLAH & PRIORITAS ==================== -->
    @if($activeTab === 'schools')
    <div class="space-y-6">
        <!-- Sub-KPI Banner Sekolah -->
        <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 no-print">
            <div class="p-3 bg-white rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase">Total Sekolah</p>
                <h4 class="text-lg font-black text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</h4>
            </div>
            <div class="p-3 bg-red-50 rounded-xl border border-red-200 shadow-sm">
                <p class="text-[10px] font-bold text-red-600 uppercase">Prioritas A</p>
                <h4 class="text-lg font-black text-red-700 mt-0.5">{{ number_format($stats['prio_a']) }}</h4>
            </div>
            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 shadow-sm">
                <p class="text-[10px] font-bold text-amber-600 uppercase">Prioritas B</p>
                <h4 class="text-lg font-black text-amber-700 mt-0.5">{{ number_format($stats['prio_b']) }}</h4>
            </div>
            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 shadow-sm">
                <p class="text-[10px] font-bold text-emerald-600 uppercase">Prioritas C</p>
                <h4 class="text-lg font-black text-emerald-700 mt-0.5">{{ number_format($stats['prio_c']) }}</h4>
            </div>
            <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 shadow-sm">
                <p class="text-[10px] font-bold text-blue-600 uppercase">Sudah Dikunjungi</p>
                <h4 class="text-lg font-black text-blue-700 mt-0.5">{{ number_format($stats['visited']) }}</h4>
            </div>
            <div class="p-3 bg-slate-100 rounded-xl border border-slate-200 shadow-sm">
                <p class="text-[10px] font-bold text-slate-500 uppercase">Belum Dikunjungi</p>
                <h4 class="text-lg font-black text-slate-700 mt-0.5">{{ number_format($stats['unvisited']) }}</h4>
            </div>
        </div>

        <!-- Diagram Pemetaan & Prioritas Sekolah -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 no-print">
            <!-- Left: Proporsi Prioritas Sekolah -->
            <div class="lg:col-span-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="award" class="w-4 h-4 text-amber-500"></i>
                            <span>Proporsi Level Prioritas Sekolah Target</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Sebaran sekolah sasaran prioritas A, B, dan C</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">
                        {{ number_format($stats['total']) }} Sekolah
                    </span>
                </div>
                <div class="h-60 flex items-center justify-center">
                    <canvas id="schoolPriorityChart"></canvas>
                </div>
            </div>

            <!-- Right: Status Kunjungan Sekolah -->
            <div class="lg:col-span-6 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h3 class="text-sm font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                            <i data-lucide="navigation-2" class="w-4 h-4 text-blue-600"></i>
                            <span>Cakupan Realisasi Kunjungan Sekolah</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Persentase sekolah target yang sudah dan belum dikunjungi</p>
                    </div>
                    <span class="text-[11px] font-bold px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded-lg">
                        {{ $stats['total'] > 0 ? round(($stats['visited'] / $stats['total']) * 100, 1) : 0 }}% Terjangkau
                    </span>
                </div>
                <div class="h-60 flex items-center justify-center">
                    <canvas id="schoolVisitedChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Tabel Detail Pemetaan Sekolah -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Data Pemetaan &amp; Status Prioritas Sekolah</h3>
                    <p class="text-[11px] text-slate-500">Daftar sekolah target Kabupaten Karawang lengkap dengan akreditasi dan riwayat kunjungan</p>
                </div>
                <span class="text-xs font-bold px-2 py-0.5 bg-brand-50 text-brand-700 border border-brand-200 rounded-lg">
                    Menampilkan {{ $schools->total() }} Sekolah
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs table-print">
                    <thead class="bg-slate-900 text-white font-bold uppercase">
                        <tr>
                            <th class="py-3 px-3">NPSN</th>
                            <th class="py-3 px-3">Nama Sekolah</th>
                            <th class="py-3 px-2 text-center">Status</th>
                            <th class="py-3 px-3">Kecamatan</th>
                            <th class="py-3 px-2 text-center">Akreditasi</th>
                            <th class="py-3 px-2 text-center">Prioritas</th>
                            <th class="py-3 px-2 text-center">Siswa XII</th>
                            <th class="py-3 px-2 text-center">Status Kunjungan</th>
                            <th class="py-3 px-2 text-center">Follow Up</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($schools as $s)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-3 font-mono text-slate-600">{{ $s->npsn ?? '-' }}</td>
                                <td class="py-3 px-3 font-extrabold text-slate-900">
                                    <a href="{{ route('schools.show', $s->id) }}" class="text-slate-900 hover:text-brand-600">
                                        {{ $s->name }}
                                    </a>
                                </td>
                                <td class="py-3 px-2 text-center">
                                    <span class="px-2 py-0.5 bg-slate-100 rounded text-[10px] font-bold text-slate-700">
                                        {{ $s->status ?? 'Swasta' }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-slate-700 font-medium">{{ $s->district->name ?? '-' }}</td>
                                <td class="py-3 px-2 text-center font-bold text-slate-800">
                                    {{ $s->accreditation->code ?? ($s->accreditation->name ?? '-') }}
                                </td>
                                <td class="py-3 px-2 text-center">
                                    @if($s->priority_level === 'A')
                                        <span class="px-2 py-0.5 bg-red-100 text-red-700 font-extrabold rounded text-[10px] border border-red-200">Prio A</span>
                                    @elseif($s->priority_level === 'B')
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 font-extrabold rounded text-[10px] border border-amber-200">Prio B</span>
                                    @else
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 font-extrabold rounded text-[10px] border border-emerald-200">Prio C</span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center font-bold text-brand-700">
                                    {{ $s->grade_12_count ? number_format($s->grade_12_count) : '-' }}
                                </td>
                                <td class="py-3 px-2 text-center whitespace-nowrap">
                                    @if($s->visits_count > 0)
                                        <span class="px-2 py-0.5 bg-blue-100 text-blue-800 font-bold rounded text-[10px]">
                                            Sudah ({{ $s->visits_count }}x)
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-500 rounded text-[10px]">
                                            Belum
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 px-2 text-center font-extrabold text-amber-600">
                                    {{ $s->follow_ups_count ?: '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    <i data-lucide="inbox" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                    Tidak ada data sekolah yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($schools->hasPages())
                <div class="p-4 border-t border-slate-100 no-print">
                    {{ $schools->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- ==================== PRINT ONLY: OFFICIAL SIGNATURE SHEET ==================== -->
    <div class="print-only mt-10 pt-6 border-t border-slate-300 page-break-avoid">
        <div class="grid grid-cols-2 gap-8 text-center text-xs">
            <div>
                <p class="text-slate-600">Dibuat oleh,</p>
                <p class="font-bold text-slate-900 mt-0.5">Tim Pelaksana Marketing UBSI</p>
                <div class="h-20"></div>
                <p class="font-extrabold text-slate-900 underline">{{ auth()->user()->name ?? 'Staf Marketing' }}</p>
                <p class="text-[10px] text-slate-500">Marketing &amp; Public Relations</p>
            </div>
            <div>
                <p class="text-slate-600">Karawang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
                <p class="font-bold text-slate-900 mt-0.5">Mengetahui &amp; Menyetujui,</p>
                <div class="h-20"></div>
                <p class="font-extrabold text-slate-900 underline">Kepala Kampus / Koordinator Marketing</p>
                <p class="text-[10px] text-slate-500">{{ $appSettings['campus_name'] ?? 'UBSI Kampus Karawang' }}</p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded');
            return;
        }

        const defaultFontFamily = "'Inter', system-ui, -apple-system, sans-serif";
        Chart.defaults.font.family = defaultFontFamily;
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#64748b';

        // 1. OVERVIEW CHARTS
        @if($activeTab === 'overview')
            // District Visit Chart (Bar)
            const districtCanvas = document.getElementById('districtVisitChart');
            if (districtCanvas) {
                @php
                    $topDistricts = $schoolsSummary->take(10);
                    $districtLabels = $topDistricts->pluck('name')->toArray();
                    $districtTotalSchools = $topDistricts->pluck('total_schools')->toArray();
                    $districtVisitedSchools = $topDistricts->pluck('visited_schools_count')->toArray();
                @endphp
                new Chart(districtCanvas, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($districtLabels) !!},
                        datasets: [
                            {
                                label: 'Total Sekolah',
                                data: {!! json_encode($districtTotalSchools) !!},
                                backgroundColor: '#cbd5e1',
                                borderRadius: 6,
                                barPercentage: 0.7,
                                categoryPercentage: 0.8
                            },
                            {
                                label: 'Sekolah Dikunjungi',
                                data: {!! json_encode($districtVisitedSchools) !!},
                                backgroundColor: '#2563eb',
                                borderRadius: 6,
                                barPercentage: 0.7,
                                categoryPercentage: 0.8
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', align: 'end', labels: { boxWidth: 12, usePointStyle: true } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Prospect Pipeline (Doughnut)
            const pipelineCanvas = document.getElementById('prospectPipelineChart');
            if (pipelineCanvas) {
                new Chart(pipelineCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['1. Sudah Daftar Akun', '2. Sudah Bayar Registrasi', '3. Sudah Ujian Saringan', '4. Sudah Bayar Daftar Ulang', '5. Sudah Lengkap Berkas', 'Mahasiswa Tervalidasi', 'Tidak Lanjut'],
                        datasets: [{
                            data: [
                                {{ $prospectStatusBreakdown['daftar_akun'] ?? 0 }},
                                {{ $prospectStatusBreakdown['bayar_registrasi'] ?? 0 }},
                                {{ $prospectStatusBreakdown['ujian_saringan'] ?? 0 }},
                                {{ $prospectStatusBreakdown['bayar_daftar_ulang'] ?? 0 }},
                                {{ $prospectStatusBreakdown['berkas_lengkap'] ?? 0 }},
                                {{ $prospectStatusBreakdown['mahasiswa'] ?? 0 }},
                                {{ $prospectStatusBreakdown['tidak_lanjut'] ?? 0 }}
                            ],
                            backgroundColor: ['#3b82f6', '#f59e0b', '#06b6d4', '#6366f1', '#a855f7', '#10b981', '#f43f5e'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }

            // Marketing Performance (Bar)
            const marketingCanvas = document.getElementById('marketingPerformanceChart');
            if (marketingCanvas) {
                @php
                    $mLabels = $marketingPerformance->pluck('name')->toArray();
                    $mVisits = $marketingPerformance->pluck('school_visits_count')->toArray();
                    $mFollowups = $marketingPerformance->pluck('follow_ups_count')->toArray();
                    $mClosing = $marketingPerformance->pluck('converted_mahasiswa_count')->toArray();
                @endphp
                new Chart(marketingCanvas, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($mLabels) !!},
                        datasets: [
                            {
                                label: 'Kunjungan',
                                data: {!! json_encode($mVisits) !!},
                                backgroundColor: '#3b82f6',
                                borderRadius: 6
                            },
                            {
                                label: 'Follow Up',
                                data: {!! json_encode($mFollowups) !!},
                                backgroundColor: '#f59e0b',
                                borderRadius: 6
                            },
                            {
                                label: 'Mahasiswa Closing',
                                data: {!! json_encode($mClosing) !!},
                                backgroundColor: '#10b981',
                                borderRadius: 6
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', align: 'end', labels: { boxWidth: 12, usePointStyle: true } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Priority Distribution (Doughnut)
            const priorityCanvas = document.getElementById('priorityDistributionChart');
            if (priorityCanvas) {
                new Chart(priorityCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Prioritas A (Tinggi)', 'Prioritas B (Menengah)', 'Prioritas C (Standar)'],
                        datasets: [{
                            data: [
                                {{ $priorityDistribution['A'] ?? 0 }},
                                {{ $priorityDistribution['B'] ?? 0 }},
                                {{ $priorityDistribution['C'] ?? 0 }}
                            ],
                            backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }
        @endif

        // 2. VISITS CHARTS
        @if($activeTab === 'visits')
            const visitCanvas = document.getElementById('visitStatusChart');
            if (visitCanvas) {
                new Chart(visitCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Berhasil', 'Pending / Jadwal', 'Tidak Berhasil', 'Tidak Bertemu'],
                        datasets: [{
                            data: [
                                {{ $stats['berhasil'] ?? 0 }},
                                {{ $stats['pending'] ?? 0 }},
                                {{ $stats['tidak_berhasil'] ?? 0 }},
                                {{ $stats['tidak_bertemu'] ?? 0 }}
                            ],
                            backgroundColor: ['#10b981', '#f59e0b', '#f43f5e', '#64748b'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }
        @endif

        // 3. FOLLOWUPS CHARTS
        @if($activeTab === 'followups')
            const followupCanvas = document.getElementById('followupStatusChart');
            if (followupCanvas) {
                new Chart(followupCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Jadwal FIX Terbit', 'Koordinasi Jadwal', 'Menunggu Konfirmasi', 'Gagal / Kendala'],
                        datasets: [{
                            data: [
                                {{ $stats['completed'] ?? 0 }},
                                {{ $stats['progress'] ?? 0 }},
                                {{ $stats['pending'] ?? 0 }},
                                {{ $stats['failed'] ?? 0 }}
                            ],
                            backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#f43f5e'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }
        @endif

        // 4. PROSPECTS CHARTS
        @if($activeTab === 'prospects')
            const stageCanvas = document.getElementById('prospectStageChart');
            if (stageCanvas) {
                new Chart(stageCanvas, {
                    type: 'bar',
                    data: {
                        labels: ['1. Daftar Akun', '2. Bayar Registrasi', '3. Ujian Saringan', '4. Daftar Ulang', '5. Lengkap Berkas', 'Maba Resmi', 'Tidak Lanjut'],
                        datasets: [{
                            label: 'Jumlah Siswa',
                            data: [
                                {{ $stats['daftar_akun'] ?? 0 }},
                                {{ $stats['bayar_registrasi'] ?? 0 }},
                                {{ $stats['ujian_saringan'] ?? 0 }},
                                {{ $stats['bayar_daftar_ulang'] ?? 0 }},
                                {{ $stats['berkas_lengkap'] ?? 0 }},
                                {{ $stats['mahasiswa'] ?? 0 }},
                                {{ $stats['tidak_lanjut'] ?? 0 }}
                            ],
                            backgroundColor: [
                                '#3b82f6',
                                '#06b6d4',
                                '#8b5cf6',
                                '#f59e0b',
                                '#10b981',
                                '#0f172a',
                                '#f43f5e'
                            ],
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { precision: 0 } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            const convCanvas = document.getElementById('prospectConversionChart');
            if (convCanvas) {
                @php
                    $inProcess = ($stats['total'] ?? 0) - ($stats['mahasiswa'] ?? 0) - ($stats['tidak_lanjut'] ?? 0);
                    if ($inProcess < 0) $inProcess = 0;
                @endphp
                new Chart(convCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Mahasiswa FIX', 'Dalam Pipeline CRM', 'Tidak Lanjut'],
                        datasets: [{
                            data: [
                                {{ $stats['mahasiswa'] ?? 0 }},
                                {{ $inProcess }},
                                {{ $stats['tidak_lanjut'] ?? 0 }}
                            ],
                            backgroundColor: ['#10b981', '#3b82f6', '#f43f5e'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }
        @endif

        // 5. QUESTIONNAIRES CHARTS
        @if($activeTab === 'questionnaires')
            const qFollowupCanvas = document.getElementById('questionnaireFollowupChart');
            if (qFollowupCanvas) {
                new Chart(qFollowupCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Belum Dihubungi', 'Respon', 'Berminat', 'Closing Mahasiswa'],
                        datasets: [{
                            data: [
                                {{ $stats['belum_dihubungi'] ?? 0 }},
                                {{ $stats['respon'] ?? 0 }},
                                {{ $stats['berminat'] ?? 0 }},
                                {{ $stats['closing'] ?? 0 }}
                            ],
                            backgroundColor: ['#94a3b8', '#3b82f6', '#f59e0b', '#10b981'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }

            const qScholarshipCanvas = document.getElementById('questionnaireScholarshipChart');
            if (qScholarshipCanvas) {
                @php
                    $scholarshipYes = $stats['scholarship_interested'] ?? 0;
                    $scholarshipNo = max(0, ($stats['total'] ?? 0) - $scholarshipYes);
                @endphp
                new Chart(qScholarshipCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Berminat Beasiswa', 'Reguler / Tidak Mengajukan'],
                        datasets: [{
                            data: [
                                {{ $scholarshipYes }},
                                {{ $scholarshipNo }}
                            ],
                            backgroundColor: ['#8b5cf6', '#cbd5e1'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }
        @endif

        // 6. SCHOOLS CHARTS
        @if($activeTab === 'schools')
            const schPriorityCanvas = document.getElementById('schoolPriorityChart');
            if (schPriorityCanvas) {
                new Chart(schPriorityCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Prioritas A (Tinggi)', 'Prioritas B (Menengah)', 'Prioritas C (Standar)'],
                        datasets: [{
                            data: [
                                {{ $stats['prio_a'] ?? 0 }},
                                {{ $stats['prio_b'] ?? 0 }},
                                {{ $stats['prio_c'] ?? 0 }}
                            ],
                            backgroundColor: ['#ef4444', '#f59e0b', '#10b981'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }

            const schVisitedCanvas = document.getElementById('schoolVisitedChart');
            if (schVisitedCanvas) {
                new Chart(schVisitedCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: ['Sudah Dikunjungi', 'Belum Dikunjungi'],
                        datasets: [{
                            data: [
                                {{ $stats['visited'] ?? 0 }},
                                {{ $stats['unvisited'] ?? 0 }}
                            ],
                            backgroundColor: ['#2563eb', '#e2e8f0'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { boxWidth: 10, usePointStyle: true, padding: 12 } },
                            tooltip: { padding: 10, cornerRadius: 8 }
                        },
                        cutout: '68%'
                    }
                });
            }
        @endif
    });
    </script>
    @endpush

</x-app-layout>
