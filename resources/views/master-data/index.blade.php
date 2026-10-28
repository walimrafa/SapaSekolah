<x-app-layout title="Master Data System">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Data System</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola data wilayah aktif, jenis sekolah, akreditasi, jabatan kontak &amp; scoring criteria</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap gap-2 border-b border-slate-200 mb-6 bg-white rounded-2xl p-2 shadow-sm border border-slate-200/80">
        <a href="{{ route('master-data.index', ['tab' => 'wilayah']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'wilayah' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="map-pin" class="w-4 h-4 text-amber-400"></i>
            <span>Master Wilayah (Kecamatan &amp; Desa)</span>
        </a>

        <a href="{{ route('master-data.index', ['tab' => 'school_types']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'school_types' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="building" class="w-4 h-4 text-blue-400"></i>
            <span>Jenis Sekolah</span>
        </a>

        <a href="{{ route('master-data.index', ['tab' => 'accreditations']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'accreditations' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="award" class="w-4 h-4 text-emerald-400"></i>
            <span>Akreditasi</span>
        </a>

        <a href="{{ route('master-data.index', ['tab' => 'positions']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'positions' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="user-check" class="w-4 h-4 text-purple-400"></i>
            <span>Jabatan Kontak PIC</span>
        </a>

        <a href="{{ route('master-data.index', ['tab' => 'scoring']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'scoring' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="sliders" class="w-4 h-4 text-rose-400"></i>
            <span>Scoring Criteria &amp; Bobot</span>
        </a>
    </div>

    <!-- Tab 1: Master Wilayah (Kecamatan & Desa) -->
    @if($tab === 'wilayah')
        <!-- Metric Cards: Singkron dengan Data & GIS Sekolah -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                    <i data-lucide="building-2" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sekolah Terintegrasi</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ number_format($wilayahStats['total_schools']) }} <span class="text-xs font-normal text-slate-500">Sekolah</span></p>
                </div>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="map" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kabupaten / Kota</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $wilayahStats['total_regencies'] }} <span class="text-xs font-normal text-slate-500">Wilayah</span></p>
                </div>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="map-pin" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kecamatan Bersekolah</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $wilayahStats['total_districts_with_schools'] }} <span class="text-xs font-normal text-slate-500">Kecamatan</span></p>
                </div>
            </div>

            <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm flex items-center space-x-3.5">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <i data-lucide="navigation" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Desa / Kelurahan</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ number_format($wilayahStats['total_villages']) }} <span class="text-xs font-normal text-slate-500">Desa</span></p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm mb-6">
            <form method="GET" action="{{ route('master-data.index') }}" class="flex flex-col md:flex-row gap-3 items-center">
                <input type="hidden" name="tab" value="wilayah">

                <!-- Filter Kabupaten / Kota -->
                <div class="w-full md:w-64">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Kabupaten / Kota</label>
                    <select name="regency_id" onchange="this.form.submit()"
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                        <option value="ALL">Semua Kabupaten / Kota Terintegrasi</option>
                        @foreach($activeRegencies as $reg)
                            <option value="{{ $reg->id }}" {{ $selectedRegencyId == $reg->id ? 'selected' : '' }}>
                                {{ $reg->name }} ({{ $reg->schools_count }} Sekolah)
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status Sekolah di Kecamatan -->
                <div class="w-full md:w-56">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Status Sekolah</label>
                    <select name="with_schools" onchange="this.form.submit()"
                        class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                        <option value="">Semua Kecamatan Terintegrasi</option>
                        <option value="1" {{ $withSchoolsFilter === '1' ? 'selected' : '' }}>Hanya yang Memiliki Sekolah ({{ $wilayahStats['total_districts_with_schools'] }})</option>
                        <option value="0" {{ $withSchoolsFilter === '0' ? 'selected' : '' }}>Belum Ada Data Sekolah</option>
                    </select>
                </div>

                <!-- Search Kecamatan -->
                <div class="flex-1 w-full">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Cari Kecamatan</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="district_q" value="{{ $districtSearch }}" placeholder="Cari nama kecamatan..."
                            class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>

                <div class="flex items-end gap-2 w-full md:w-auto pt-4 md:pt-0">
                    <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-sm transition">
                        Filter
                    </button>
                    @if(request()->hasAny(['regency_id', 'with_schools', 'district_q', 'village_q']))
                        <a href="{{ route('master-data.index', ['tab' => 'wilayah']) }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition" title="Reset Filter">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            <!-- Districts Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="map" class="w-4 h-4 text-brand-600"></i>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Kecamatan Aktif Target</h3>
                            <p class="text-[11px] text-slate-400">Wilayah tersingkronisasi dengan data sekolah</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-[11px] font-bold bg-brand-100 text-brand-700 rounded-full border border-brand-200">
                        {{ $districts->total() }} Kecamatan
                    </span>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Nama Kecamatan</th>
                                <th class="py-3.5 px-4">Kabupaten / Kota</th>
                                <th class="py-3.5 px-4 text-center">Jumlah Desa</th>
                                <th class="py-3.5 px-4 text-center">Sekolah Terintegrasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($districts as $d)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            @if($d->schools_count > 0)
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Aktif Bersekolah"></span>
                                            @else
                                                <span class="w-2 h-2 rounded-full bg-slate-300 shrink-0" title="Belum Ada Sekolah"></span>
                                            @endif
                                            <span>{{ $d->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-600 whitespace-nowrap">
                                        <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $d->regency ? $d->regency->name : '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-slate-600 whitespace-nowrap">
                                        <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md">{{ $d->villages_count }} Desa</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-extrabold whitespace-nowrap">
                                        @if($d->schools_count > 0)
                                            <span class="px-2.5 py-0.5 bg-brand-50 text-brand-700 rounded-md border border-brand-100 shadow-sm font-bold">
                                                {{ $d->schools_count }} Sekolah
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-slate-100 text-slate-400 rounded-md text-[11px] font-medium">
                                                0 Sekolah
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-6 text-center text-slate-400">Tidak ada kecamatan yang sesuai dengan filter.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-100">
                    {{ $districts->links() }}
                </div>
            </div>

            <!-- Villages Table Card -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="navigation" class="w-4 h-4 text-emerald-600"></i>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Desa / Kelurahan Aktif</h3>
                            <p class="text-[11px] text-slate-400">Desa di wilayah kabupaten terintegrasi</p>
                        </div>
                    </div>
                    <span class="px-2.5 py-1 text-[11px] font-bold bg-emerald-100 text-emerald-700 rounded-full border border-emerald-200">
                        {{ $villages->total() }} Desa
                    </span>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Nama Desa / Kelurahan</th>
                                <th class="py-3.5 px-4">Kecamatan</th>
                                <th class="py-3.5 px-4">Kabupaten / Kota</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($villages as $v)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $v->name }}</td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-600 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-md border border-slate-200">
                                            {{ $v->district ? $v->district->name : '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 font-semibold text-slate-500 whitespace-nowrap">
                                        <span class="text-[10px] text-slate-500">
                                            {{ $v->district && $v->district->regency ? $v->district->regency->name : '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-6 text-center text-slate-400">Tidak ada desa terdaftar pada filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-100">
                    {{ $villages->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- Tab 2: Jenis Sekolah -->
    @if($tab === 'school_types')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">Daftar Jenis / Jenjang Sekolah</h3>
                    <span class="text-xs font-bold text-slate-500">{{ $schoolTypes->count() }} Jenis</span>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Nama Jenjang</th>
                                <th class="py-3.5 px-4 text-center">Jumlah Sekolah Terdaftar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($schoolTypes as $st)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-extrabold text-slate-900 whitespace-nowrap">{{ $st->name }}</td>
                                    <td class="py-3.5 px-4 text-center font-extrabold text-brand-600 whitespace-nowrap">
                                        <span class="px-3 py-1 bg-brand-50 text-brand-700 rounded-full border border-brand-100">{{ $st->schools_count }} Sekolah</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Form Tambah -->
            <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-brand-600"></i>
                    <span>Tambah Jenis Sekolah</span>
                </h3>
                <form method="POST" action="{{ route('master-data.school-types.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jenis (SMA/SMK/MA/SLB)</label>
                        <input type="text" name="name" placeholder="Contoh: MAK" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Simpan Jenis Sekolah
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Tab 3: Akreditasi -->
    @if($tab === 'accreditations')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">Master Data Akreditasi Sekolah</h3>
                    <span class="text-xs font-bold text-slate-500">{{ $accreditations->count() }} Data</span>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Kode</th>
                                <th class="py-3.5 px-4">Nama Akreditasi</th>
                                <th class="py-3.5 px-4">Keterangan</th>
                                <th class="py-3.5 px-4 text-center">Jumlah Sekolah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($accreditations as $acc)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-mono font-extrabold text-brand-600 whitespace-nowrap">{{ $acc->code }}</td>
                                    <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $acc->name }}</td>
                                    <td class="py-3.5 px-4 text-slate-500">{{ $acc->description ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-center font-extrabold text-slate-800 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-md border border-slate-200">{{ $acc->schools_count }} Sekolah</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                    <span>Tambah Akreditasi</span>
                </h3>
                <form method="POST" action="{{ route('master-data.accreditations.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Akreditasi</label>
                        <input type="text" name="name" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300" placeholder="Akreditasi Unggul">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Singkatan</label>
                        <input type="text" name="code" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300" placeholder="A+">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Keterangan</label>
                        <input type="text" name="description" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-brand-navy text-white font-bold text-xs rounded-xl shadow-md transition">
                        Simpan Akreditasi
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Tab 4: Jabatan Kontak -->
    @if($tab === 'positions')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-8 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">Master Posisi / Jabatan Kontak PIC Sekolah</h3>
                    <span class="text-xs font-bold text-slate-500">{{ $contactPositions->count() }} Jabatan</span>
                </div>
                <div class="overflow-x-auto w-full">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Nama Jabatan</th>
                                <th class="py-3.5 px-4">Deskripsi Peran</th>
                                <th class="py-3.5 px-4 text-center">Jumlah PIC Terdaftar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($contactPositions as $pos)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $pos->name }}</td>
                                    <td class="py-3.5 px-4 text-slate-500">{{ $pos->description ?? '-' }}</td>
                                    <td class="py-3.5 px-4 text-center font-extrabold text-brand-600 whitespace-nowrap">
                                        <span class="px-2.5 py-0.5 bg-purple-50 text-purple-700 rounded-md border border-purple-100">{{ $pos->school_contacts_count }} PIC</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-purple-600"></i>
                    <span>Tambah Jabatan PIC</span>
                </h3>
                <form method="POST" action="{{ route('master-data.positions.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Jabatan</label>
                        <input type="text" name="name" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300" placeholder="Kepala Sekolah, BK, dll">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                        <input type="text" name="description" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-brand-navy text-white font-bold text-xs rounded-xl shadow-md transition">
                        Simpan Jabatan PIC
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Tab 5: Scoring Criteria -->
    @if($tab === 'scoring')
        <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="flex items-center justify-between mb-6 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Konfigurasi Bobot &amp; Kriteria Scoring SAPA Sekolah</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Bobot menentukan perhitungan otomatis Priority Score sekolah A/B/C (Total Bobot harus = 100%)</p>
                </div>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 text-xs font-bold rounded-lg border border-amber-200">
                    Total Bobot: {{ $scoringCriteria->sum('weight') }}%
                </span>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4">#</th>
                            <th class="py-3.5 px-4">Nama Kriteria Scoring</th>
                            <th class="py-3.5 px-4 text-center">Bobot Persentase (%)</th>
                            <th class="py-3.5 px-4 text-center">Max Score</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi Update</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($scoringCriteria as $c)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="py-3.5 px-4 font-bold text-slate-400 whitespace-nowrap">{{ $c->id }}</td>
                                <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">{{ $c->name }}</td>
                                <form method="POST" action="{{ route('master-data.scoring.update', $c->id) }}">
                                    @csrf
                                    @method('PUT')
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <input type="number" name="weight" step="0.01" value="{{ $c->weight }}" required
                                            class="w-24 text-center px-2 py-1 text-xs font-extrabold text-brand-600 rounded-lg border border-slate-300 focus:border-brand-500"> %
                                    </td>
                                    <td class="py-3.5 px-4 text-center font-semibold text-slate-700 whitespace-nowrap">{{ $c->max_score }} Poin</td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <select name="status" class="px-2 py-1 text-xs rounded-lg border border-slate-300">
                                            <option value="1" {{ $c->status ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ !$c->status ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <button type="submit" class="px-3 py-1.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-lg text-xs shadow-xs transition">
                                            Update Bobot
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-app-layout>
