<x-app-layout title="Master Data System">
    <div class="mb-6">
        <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Master Data System</h1>
        <p class="text-xs text-slate-500 mt-0.5">Kelola data wilayah aktif, jenis sekolah, akreditasi, jabatan kontak &amp; scoring criteria</p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex flex-wrap gap-2 border-b border-slate-200 mb-6 bg-white rounded-2xl p-2 shadow-sm border border-slate-200/80">
        <a href="{{ route('master-data.index', ['tab' => 'campuses']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'campuses' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-400"></i>
            <span>Master Kampus</span>
        </a>

        <a href="{{ route('master-data.index', ['tab' => 'periods']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'periods' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="calendar" class="w-4 h-4 text-sky-400"></i>
            <span>Master Periode Akademik</span>
        </a>

        <a href="{{ route('master-data.index', ['tab' => 'portals']) }}"
            class="px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center space-x-2 {{ $tab === 'portals' ? 'bg-brand-navy text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400"></i>
            <span>Kontrol Portal Publik</span>
        </a>

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

    <!-- Tab: Master Kampus -->
    @if($tab === 'campuses')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="graduation-cap" class="w-4 h-4 text-emerald-600"></i>
                            <span>Daftar Kampus Operasional</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Seluruh data sekolah, kunjungan, dan prospek diklasifikasikan berdasarkan kampus.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-lg border border-emerald-200">
                        Total: {{ $campuses->count() }} Kampus
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-900 text-white font-bold uppercase text-[10px]">
                            <tr>
                                <th class="py-3 px-3.5">Kode</th>
                                <th class="py-3 px-3.5">Nama Kampus &amp; Kota</th>
                                <th class="py-3 px-3.5">Data Terkait</th>
                                <th class="py-3 px-3.5 text-center">Status</th>
                                <th class="py-3 px-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($campuses as $campus)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3.5 font-bold text-slate-900 whitespace-nowrap">
                                        <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-[11px]">
                                            {{ $campus->code }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5">
                                        <div class="flex items-center space-x-3">
                                            <img src="{{ $campus->building_image_url }}" alt="{{ $campus->name }}" class="w-12 h-10 object-cover rounded-lg border border-slate-200 shrink-0 shadow-xs">
                                            <div>
                                                <p class="font-bold text-slate-900">{{ $campus->name }}</p>
                                                <div class="flex flex-wrap items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                                                    <span>{{ $campus->city ?? '-' }}</span>
                                                    <span>&bull;</span>
                                                    <span class="font-bold text-emerald-600 font-mono flex items-center gap-1">
                                                        <i data-lucide="phone" class="w-3 h-3 text-emerald-600"></i>
                                                        <span>{{ $campus->phone ?? 'Belum ada kontak' }}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-3.5 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700">
                                            {{ $campus->schools_count }} Sekolah
                                        </span>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700 ml-1">
                                            {{ $campus->prospects_count }} Prospek
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                        @if($campus->is_default)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-200">
                                                DEFAULT
                                            </span>
                                        @endif
                                        @if($campus->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 ml-1">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 ml-1">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center space-x-1">
                                            @if(auth()->user()->isAdmin() || auth()->user()->campus_id === $campus->id)
                                                <button type="button" 
                                                    onclick="openEditCampusModal({{ json_encode($campus) }})"
                                                    class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition" 
                                                    title="Edit Data Kampus">
                                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                </button>
                                            @endif

                                            @if(auth()->user()->isAdmin())
                                                @if($campus->schools_count == 0 && $campus->prospects_count == 0 && !$campus->is_default)
                                                    <form method="POST" action="{{ route('master-data.campuses.destroy', $campus->id) }}" onsubmit="return confirm('Hapus kampus {{ $campus->name }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-[10px] text-slate-400 italic">Terkunci</span>
                                                @endif
                                            @else
                                                <span class="text-[10px] text-emerald-600 font-semibold">Kampus Anda</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data kampus.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(auth()->user()->isAdmin())
                <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-4 h-4 text-emerald-600"></i>
                        <span>Tambah Kampus Baru</span>
                    </h3>
                    <form method="POST" action="{{ route('master-data.campuses.store') }}" enctype="multipart/form-data" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kode Kampus (Singkatan)</label>
                            <input type="text" name="code" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono uppercase" placeholder="KRW, CKP, CKR">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kampus</label>
                            <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="UBSI Kampus Karawang">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten</label>
                            <input type="text" name="city" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="Karawang">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                            <textarea name="address" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="Jl. Banten No. 1..."></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (GPS)</label>
                                <input type="number" step="0.00000001" name="latitude" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono" placeholder="-6.305400">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (GPS)</label>
                                <input type="number" step="0.00000001" name="longitude" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono" placeholder="107.299500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Resmi Kampus / WhatsApp Hotline BSI</label>
                            <div class="relative">
                                <input type="text" name="phone" class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-300 font-mono" placeholder="0812xxxxxxxx">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600 absolute left-2.5 top-2.5"></i>
                            </div>
                            <p class="text-[10px] text-slate-400 mt-0.5">Digunakan otomatis untuk konfirmasi proposal, pendaftaran event, dan WhatsApp Hotline.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi</label>
                            <input type="email" name="email" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="info@bsi.ac.id">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Foto Gedung Kampus</label>
                            <input type="file" name="building_image" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-0.5">Format: JPG, PNG, WEBP (Maks. 5 MB). Ditampilkan pada halaman login & profile.</p>
                        </div>
                        <div class="pt-2 space-y-2 border-t border-slate-100">
                            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                                <span>Status Kampus Aktif</span>
                            </label>
                            <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                <input type="checkbox" name="is_default" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                <span>Jadikan Kampus Utama (Default)</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full mt-2 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition">
                            Simpan Kampus Baru
                        </button>
                    </form>
                </div>
            @else
                <div class="lg:col-span-4 bg-gradient-to-br from-slate-900 to-brand-navy text-white p-5 rounded-2xl shadow-soft">
                    <div class="flex items-center space-x-2 text-emerald-400 font-bold text-xs uppercase tracking-wider mb-3">
                        <i data-lucide="shield-check" class="w-4 h-4"></i>
                        <span>Isolasi Kampus Aktif</span>
                    </div>
                    <h4 class="text-sm font-bold text-white mb-2">{{ auth()->user()->campus?->name ?? 'Kampus Penugasan' }}</h4>
                    <p class="text-xs text-slate-300 leading-relaxed">
                        Akun Anda dikhususkan untuk mengelola operasional, data sekolah, kunjungan, prospek, dan staf pada kampus penugasan Anda. Data master antar kampus terisolasi sepenuhnya dan tidak tercampur.
                    </p>
                    <div class="mt-4 pt-4 border-t border-white/10 text-[11px] text-slate-400 flex items-center justify-between">
                        <span>Hak Akses:</span>
                        <span class="font-bold text-amber-300">{{ auth()->user()->role?->name ?? 'Pimpinan' }}</span>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Tab: Master Periode Akademik -->
    @if($tab === 'periods')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-8 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-sky-600"></i>
                            <span>Daftar Periode Akademik / Intake</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Sistem memfilter prospek, kunjungan, dan data follow-up sesuai periode yang aktif.</p>
                    </div>
                    <span class="px-2.5 py-1 bg-sky-50 text-sky-700 text-xs font-bold rounded-lg border border-sky-200">
                        Total: {{ $academicPeriods->count() }} Periode
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-slate-900 text-white font-bold uppercase text-[10px]">
                            <tr>
                                <th class="py-3 px-3.5">Nama Periode</th>
                                <th class="py-3 px-3.5">Rentang Tanggal</th>
                                <th class="py-3 px-3.5">Data Terkait</th>
                                <th class="py-3 px-3.5 text-center">Status</th>
                                <th class="py-3 px-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($academicPeriods as $period)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-3.5 font-bold text-slate-900 whitespace-nowrap">
                                        <span class="text-sm font-extrabold text-brand-600">{{ $period->name }}</span>
                                        @if($period->notes)
                                            <p class="text-[10px] text-slate-400 mt-0.5">{{ $period->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 whitespace-nowrap text-slate-600">
                                        {{ $period->start_date ? $period->start_date->format('d M Y') : '-' }} s/d {{ $period->end_date ? $period->end_date->format('d M Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-3.5 whitespace-nowrap">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-700">
                                            {{ $period->prospects_count }} Prospek
                                        </span>
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 ml-1">
                                            {{ $period->school_visits_count }} Kunjungan
                                        </span>
                                    </td>
                                    <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                        @if($period->is_current)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                SEDANG BERJALAN
                                            </span>
                                        @endif
                                        @if($period->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-sky-100 text-sky-800 ml-1">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500 ml-1">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-3.5 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center space-x-1">
                                            @if(auth()->user()->isAdmin())
                                                <button type="button" 
                                                    onclick="openEditPeriodModal({{ json_encode($period) }})"
                                                    class="p-1.5 text-amber-600 hover:text-amber-800 hover:bg-amber-50 rounded-lg transition" 
                                                    title="Edit Periode">
                                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                                </button>

                                                @if($period->prospects_count == 0 && $period->school_visits_count == 0 && !$period->is_current)
                                                    <form method="POST" action="{{ route('master-data.academic-periods.destroy', $period->id) }}" onsubmit="return confirm('Hapus periode {{ $period->name }}?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-[10px] text-slate-400 italic">Terkunci</span>
                                                @endif
                                            @else
                                                <span class="text-[10px] text-slate-400 italic">Hanya Lihat</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada periode akademik.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="lg:col-span-4 bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-sky-600"></i>
                    <span>Tambah Periode Baru</span>
                </h3>
                <form method="POST" action="{{ route('master-data.academic-periods.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Periode Akademik</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-semibold" placeholder="Contoh: 2027/2028">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                            <input type="date" name="start_date" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                            <input type="date" name="end_date" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="Penerimaan Maba Gelombang Utama...">
                    </div>
                    <div class="pt-2 space-y-2 border-t border-slate-100">
                        <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                            <span>Status Periode Aktif</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" name="is_current" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Jadikan Periode Berjalan (Current)</span>
                        </label>
                    </div>
                    <button type="submit" class="w-full mt-2 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-md transition">
                        Simpan Periode Baru
                    </button>
                </form>
            </div>
        </div>
    @endif

    <!-- Tab: Kontrol Portal Publik (Tanpa Login) -->
    @if($tab === 'portals')
        <div class="space-y-6">
            <div class="bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent border border-amber-200/80 rounded-2xl p-5 flex items-start space-x-4">
                <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <i data-lucide="shield-alert" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Kontrol Keamanan Akses Formulir Publik (Tanpa Login)</h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Halaman publik dapat diakses langsung oleh masyarakat/siswa sekolah luar tanpa login.
                        Hak akses kontrol aktif/tidak aktif ini diberikan secara khusus kepada <strong>Admin, Pimpinan, dan Koordinator Marketing</strong>
                        untuk mencegah terjadinya pengisian otomatis liar (spam) dan menutup akses saat masa kunjungan atau periode event telah berakhir.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($publicPortals as $key => $portal)
                    <div class="bg-white rounded-2xl border {{ $portal['is_active'] ? 'border-emerald-200 shadow-soft' : 'border-slate-200 bg-slate-50/50' }} p-5 flex flex-col justify-between transition-all">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[10px] font-mono px-2 py-0.5 rounded {{ $portal['is_active'] ? 'bg-emerald-50 text-emerald-700 font-bold' : 'bg-slate-100 text-slate-500' }}">
                                    KEY: {{ $key }}
                                </span>
                                @if($portal['is_active'])
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping mr-1.5"></span>
                                        PORTAL AKTIF
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800">
                                        <span class="w-2 h-2 rounded-full bg-rose-500 mr-1.5"></span>
                                        PORTAL DITUTUP
                                    </span>
                                @endif
                            </div>

                            <h4 class="text-sm font-extrabold text-slate-900">{{ $portal['name'] }}</h4>
                            <p class="text-xs text-slate-500 mt-1 mb-3">{{ $portal['description'] }}</p>

                            <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs mb-4">
                                <span class="text-slate-400 font-mono text-[11px] truncate mr-2">{{ $portal['url'] }}</span>
                                <a href="{{ $portal['url'] }}" target="_blank" class="text-brand-600 hover:text-brand-800 shrink-0 font-bold inline-flex items-center gap-1">
                                    <span>Buka</span>
                                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                </a>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('portal-status.toggle') }}" class="pt-3 border-t border-slate-100">
                            @csrf
                            <input type="hidden" name="portal_key" value="{{ $key }}">
                            <input type="hidden" name="is_active" value="{{ $portal['is_active'] ? 0 : 1 }}">
                            @if($portal['is_active'])
                                <button type="submit" class="w-full py-2.5 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition flex items-center justify-center space-x-2">
                                    <i data-lucide="power" class="w-4 h-4 text-rose-600"></i>
                                    <span>Tutup / Nonaktifkan Akses Publik</span>
                                </button>
                            @else
                                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-2">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    <span>Buka / Aktifkan Kembali Akses Publik</span>
                                </button>
                            @endif
                        </form>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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

    <!-- Modal Edit Kampus -->
    <div id="modalEditCampus" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-amber-600"></i>
                    <span>Edit Profil Kampus</span>
                </h3>
                <button type="button" onclick="closeEditCampusModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditCampus" method="POST" action="" enctype="multipart/form-data" class="space-y-3">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode</label>
                        <input type="text" id="edit_campus_code" name="code" required 
                            @if(!auth()->user()->isAdmin()) readonly @endif
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono uppercase bg-slate-50">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kampus</label>
                        <input type="text" id="edit_campus_name" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kota / Kabupaten</label>
                    <input type="text" id="edit_campus_city" name="city" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                    <textarea id="edit_campus_address" name="address" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (GPS)</label>
                        <input type="number" step="0.00000001" id="edit_campus_latitude" name="latitude" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (GPS)</label>
                        <input type="number" step="0.00000001" id="edit_campus_longitude" name="longitude" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nomor Resmi Kampus / WhatsApp</label>
                        <div class="relative">
                            <input type="text" id="edit_campus_phone" name="phone" class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-300 font-mono">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-emerald-600 absolute left-2.5 top-2.5"></i>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5">Kontak WhatsApp resmi untuk konfirmasi proposal sekolah & WhatsApp Hotline.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Resmi</label>
                        <input type="email" id="edit_campus_email" name="email" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>

                <!-- Foto Gedung Kampus -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Foto Gedung Kampus</label>
                    <div class="flex items-center space-x-3">
                        <img id="edit_campus_building_preview" src="" alt="Gedung Kampus" class="w-20 h-14 object-cover rounded-xl border border-slate-300 shadow-xs bg-slate-200 shrink-0">
                        <div class="flex-1 min-w-0">
                            <input type="file" name="building_image" accept="image/*" class="w-full text-xs text-slate-600 file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-white file:text-slate-700 hover:file:bg-slate-100 cursor-pointer">
                            <p class="text-[10px] text-slate-400 mt-1">Format: JPG, PNG, WEBP (Maks. 5 MB). Ditampilkan di login & profil kampus.</p>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->isAdmin())
                    <div class="pt-2 space-y-2 border-t border-slate-100">
                        <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="edit_campus_is_active" name="is_active" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            <span>Status Kampus Aktif</span>
                        </label>
                        <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                            <input type="checkbox" id="edit_campus_is_default" name="is_default" value="1" class="rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                            <span>Jadikan Kampus Utama (Default)</span>
                        </label>
                    </div>
                @endif

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditCampusModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-brand-navy hover:bg-slate-900 text-white rounded-xl shadow-soft transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Periode Akademik -->
    <div id="modalEditPeriod" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="edit-3" class="w-4 h-4 text-sky-600"></i>
                    <span>Edit Periode Akademik</span>
                </h3>
                <button type="button" onclick="closeEditPeriodModal()" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form id="formEditPeriod" method="POST" action="" class="space-y-3">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Periode / Intake PMB <span class="text-red-500">*</span></label>
                    <input type="text" id="edit_period_name" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold" placeholder="2026/2027">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Mulai</label>
                        <input type="date" id="edit_period_start_date" name="start_date" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Selesai</label>
                        <input type="date" id="edit_period_end_date" name="end_date" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Keterangan</label>
                    <textarea id="edit_period_notes" name="notes" rows="2" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300"></textarea>
                </div>

                <div class="pt-2 space-y-2 border-t border-slate-100">
                    <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="edit_period_is_active" name="is_active" value="1" class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span>Status Periode Aktif</span>
                    </label>
                    <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                        <input type="checkbox" id="edit_period_is_current" name="is_current" value="1" class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Jadikan Periode Sedang Berjalan (Current)</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" onclick="closeEditPeriodModal()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-bold bg-brand-navy hover:bg-slate-900 text-white rounded-xl shadow-soft transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openEditCampusModal(campus) {
            const form = document.getElementById('formEditCampus');
            form.action = `/master-data/campuses/${campus.id}`;

            document.getElementById('edit_campus_code').value = campus.code || '';
            document.getElementById('edit_campus_name').value = campus.name || '';
            document.getElementById('edit_campus_city').value = campus.city || '';
            document.getElementById('edit_campus_address').value = campus.address || '';
            document.getElementById('edit_campus_latitude').value = campus.latitude || '';
            document.getElementById('edit_campus_longitude').value = campus.longitude || '';
            document.getElementById('edit_campus_phone').value = campus.phone || '';
            document.getElementById('edit_campus_email').value = campus.email || '';

            const previewImg = document.getElementById('edit_campus_building_preview');
            if (previewImg) {
                previewImg.src = campus.image_url 
                    ? (campus.image_url.startsWith('http') ? campus.image_url : campus.image_url) 
                    : (campus.code === 'CKP' ? '/storage/branding/ubsi_cikampek_building.jpg' : '/storage/branding/ubsi_karawang_building.jpg');
            }

            const isActiveCheckbox = document.getElementById('edit_campus_is_active');
            if (isActiveCheckbox) {
                isActiveCheckbox.checked = Boolean(campus.is_active);
            }

            const isDefaultCheckbox = document.getElementById('edit_campus_is_default');
            if (isDefaultCheckbox) {
                isDefaultCheckbox.checked = Boolean(campus.is_default);
            }

            document.getElementById('modalEditCampus').classList.remove('hidden');
        }

        function closeEditCampusModal() {
            document.getElementById('modalEditCampus').classList.add('hidden');
        }

        function openEditPeriodModal(period) {
            const form = document.getElementById('formEditPeriod');
            form.action = `/master-data/academic-periods/${period.id}`;

            document.getElementById('edit_period_name').value = period.name || '';
            document.getElementById('edit_period_notes').value = period.notes || '';

            if (period.start_date) {
                document.getElementById('edit_period_start_date').value = period.start_date.split('T')[0];
            } else {
                document.getElementById('edit_period_start_date').value = '';
            }

            if (period.end_date) {
                document.getElementById('edit_period_end_date').value = period.end_date.split('T')[0];
            } else {
                document.getElementById('edit_period_end_date').value = '';
            }

            const isActiveCheckbox = document.getElementById('edit_period_is_active');
            if (isActiveCheckbox) {
                isActiveCheckbox.checked = Boolean(period.is_active);
            }

            const isCurrentCheckbox = document.getElementById('edit_period_is_current');
            if (isCurrentCheckbox) {
                isCurrentCheckbox.checked = Boolean(period.is_current);
            }

            document.getElementById('modalEditPeriod').classList.remove('hidden');
        }

        function closeEditPeriodModal() {
            document.getElementById('modalEditPeriod').classList.add('hidden');
        }
    </script>
    @endpush
</x-app-layout>
