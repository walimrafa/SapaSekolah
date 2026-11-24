@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Input Hasil Kunjungan Lapangan - {{ $appSettings['app_name'] }}</title>

    <!-- Favicon / Logo Title SAPA Sekolah -->
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- jQuery & Select2 Assets (Offline Local) -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2-tailwind.css') }}">
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-100 min-h-screen py-6 px-4 sm:px-6">

    <div class="max-w-3xl mx-auto space-y-6" x-data="publicVisitManager()">
        <!-- Standalone Public Header Branding Bar -->
        <div class="bg-slate-900 text-white rounded-3xl p-6 shadow-xl border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                @if(!empty($appSettings['app_logo_url']))
                    <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-white p-1 shadow-md">
                @else
                    <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-white p-1 shadow-md">
                @endif
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-white leading-none">{{ $appSettings['app_name'] }}</h1>
                    <p class="text-xs text-amber-400 font-semibold uppercase tracking-wider mt-1">Portal Input Kunjungan Lapangan (Tanpa Login)</p>
                </div>
            </div>
            <div class="flex items-center space-x-3 text-center sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-slate-800 text-[11px] text-slate-400">
                <div class="hidden sm:block p-1 bg-white rounded-xl shadow-sm shrink-0">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(route('public-visit.index')) }}" alt="QR Code" class="w-10 h-10 rounded-lg">
                </div>
                <div>
                    <span class="font-bold text-slate-200 block">{{ $appSettings['campus_name'] }}</span>
                    <span>{{ date('l, d F Y') }}</span>
                </div>
            </div>
        </div>

        <!-- Success Notification Banner -->
        @if(session('success_message'))
            <div class="p-4 rounded-2xl bg-emerald-500 text-white shadow-lg flex items-center space-x-3 text-xs font-bold animate-fade-in">
                <i data-lucide="check-circle-2" class="w-6 h-6 shrink-0"></i>
                <div class="flex-1">
                    <p class="text-sm font-extrabold">{{ session('success_message') }}</p>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                <p class="font-bold mb-1">Perhatian Form:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Public Form Container -->
        <form method="POST" action="{{ route('public-visit.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Selector / Banner Kampus Operasional -->
            @if(!empty($isCampusLocked) && $activeCampus)
            <div class="bg-white p-4.5 rounded-3xl border border-slate-200/80 shadow-soft flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0 shadow-sm border border-brand-100">
                        <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Kampus UBSI Operasional:</span>
                        <span class="text-sm sm:text-base font-extrabold text-brand-900 block leading-tight">{{ $activeCampus->name }}</span>
                        <span class="text-[11px] text-slate-500 font-medium">{{ $activeCampus->code === 'CKP' ? 'Wilayah UBSI Kampus Cikampek & Purwakarta' : 'Wilayah UBSI Kampus Karawang Barat & Sekitarnya' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 self-end sm:self-auto">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[11px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider shadow-2xs">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-emerald-600"></i>
                        <span>Akses Terkunci: {{ $activeCampus->code }}</span>
                    </span>
                </div>
            </div>
            @elseif(isset($campuses) && $campuses->count() > 1)
            <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-soft flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">Pilih Kampus UBSI Operasional:</span>
                        <span class="text-[11px] text-slate-500">Daftar sekolah &amp; kunjungan otomatis disesuaikan dengan kampus pilihan</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @foreach($campuses as $c)
                        <a href="{{ route('public-visit.index', ['campus_id' => $c->id]) }}" 
                           class="flex-1 sm:flex-none text-center px-4 py-2 rounded-xl text-xs font-bold transition {{ ($activeCampus?->id == $c->id) ? 'bg-brand-600 text-white shadow-md' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $c->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
            <input type="hidden" name="campus_id" value="{{ $activeCampus?->id }}">

            <!-- Card 0: Pilihan Kategori Agenda Kunjungan Lapangan (Multi-Select Combobox) -->
            <div class="bg-white p-4 sm:p-5 rounded-3xl border border-slate-200/80 shadow-soft" x-data="{ openDropdown: false }" @click.outside="openDropdown = false">
                <!-- Hidden inputs to submit array and joined string -->
                <template x-for="cat in agendaCategories" :key="cat">
                    <input type="hidden" name="agenda_categories[]" :value="cat">
                </template>
                <input type="hidden" name="agenda_category" :value="agendaCategories.join(',')">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 shrink-0">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <label class="block text-xs font-extrabold text-slate-900">
                                    Kategori Agenda Kunjungan <span class="text-rose-500">*</span>
                                </label>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-100 text-brand-800">
                                    Bisa Pilih Lebih Dari 1
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500">Centang agenda kunjungan untuk menggabungkan isian formulir tanpa duplikasi</p>
                        </div>
                    </div>

                    <!-- Combobox Multi-Select Dropdown Container -->
                    <div class="relative w-full sm:w-96">
                        <button type="button" @click="openDropdown = !openDropdown"
                            class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-500/50 bg-white font-extrabold text-xs text-slate-800 shadow-xs hover:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition flex items-center justify-between gap-2 text-left">
                            <span class="truncate" x-text="agendaCategoriesText"></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-brand-600 shrink-0 transition-transform duration-200" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="openDropdown" x-transition.origin.top.right x-cloak
                            class="absolute right-0 mt-2 w-full sm:w-[380px] bg-white rounded-2xl border border-slate-200 shadow-xl z-30 p-2 space-y-1.5">
                            <div class="px-2.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 flex items-center justify-between">
                                <span>Pilihan Agenda (Multi-Select)</span>
                                <span class="text-brand-600 font-extrabold" x-text="agendaCategories.length + ' Terpilih'"></span>
                            </div>

                            <!-- Opsi 1 -->
                            <div @click="toggleCat('penjadwalan_sosialisasi')" 
                                class="p-2.5 rounded-xl cursor-pointer transition flex items-start gap-2.5"
                                :class="hasCat('penjadwalan_sosialisasi') ? 'bg-brand-50/80 border border-brand-200 text-brand-950 font-bold' : 'hover:bg-slate-50 border border-transparent text-slate-700'">
                                <input type="checkbox" :checked="hasCat('penjadwalan_sosialisasi')" class="mt-0.5 rounded text-brand-600 focus:ring-brand-500 pointer-events-none">
                                <div class="text-xs">
                                    <div class="flex items-center gap-1.5 font-extrabold">
                                        <i data-lucide="presentation" class="w-3.5 h-3.5 text-brand-600"></i>
                                        <span>1. Penjadwalan Sosialisasi</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-normal mt-0.5">Form estimasi rombel kelas 12, target jadwal waktu &amp; metode sosialisasi</p>
                                </div>
                            </div>

                            <!-- Opsi 2 -->
                            <div @click="toggleCat('mengantar_undangan')" 
                                class="p-2.5 rounded-xl cursor-pointer transition flex items-start gap-2.5"
                                :class="hasCat('mengantar_undangan') ? 'bg-amber-50/80 border border-amber-200 text-amber-950 font-bold' : 'hover:bg-slate-50 border border-transparent text-slate-700'">
                                <input type="checkbox" :checked="hasCat('mengantar_undangan')" class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 pointer-events-none">
                                <div class="text-xs">
                                    <div class="flex items-center gap-1.5 font-extrabold">
                                        <i data-lucide="mail" class="w-3.5 h-3.5 text-amber-600"></i>
                                        <span>2. Mengantar Undangan Acara</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-normal mt-0.5">Penerima undangan, konfirmasi kehadiran (Akan Hadir / Ijin / Tidak)</p>
                                </div>
                            </div>

                            <!-- Opsi 3 -->
                            <div @click="toggleCat('kunjungan_lainnya')" 
                                class="p-2.5 rounded-xl cursor-pointer transition flex items-start gap-2.5"
                                :class="hasCat('kunjungan_lainnya') ? 'bg-purple-50/80 border border-purple-200 text-purple-950 font-bold' : 'hover:bg-slate-50 border border-transparent text-slate-700'">
                                <input type="checkbox" :checked="hasCat('kunjungan_lainnya')" class="mt-0.5 rounded text-purple-600 focus:ring-purple-500 pointer-events-none">
                                <div class="text-xs">
                                    <div class="flex items-center gap-1.5 font-extrabold">
                                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-purple-600"></i>
                                        <span>3. Kunjungan Lainnya</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-normal mt-0.5">Silaturahmi, audiensi kepala sekolah &amp; urusan dinas lainnya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Toggle Pills & Status Terpilih -->
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mr-1">Klik Cepat:</span>

                    <button type="button" @click="toggleCat('penjadwalan_sosialisasi')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="hasCat('penjadwalan_sosialisasi') ? 'bg-brand-600 text-white border-brand-600 shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'">
                        <i data-lucide="presentation" class="w-3.5 h-3.5"></i>
                        <span>1. Penjadwalan Sosialisasi</span>
                        <span x-show="hasCat('penjadwalan_sosialisasi')" class="text-brand-200 text-[10px]">✓</span>
                    </button>

                    <button type="button" @click="toggleCat('mengantar_undangan')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="hasCat('mengantar_undangan') ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        <span>2. Mengantar Undangan Acara</span>
                        <span x-show="hasCat('mengantar_undangan')" class="text-amber-200 text-[10px]">✓</span>
                    </button>

                    <button type="button" @click="toggleCat('kunjungan_lainnya')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="hasCat('kunjungan_lainnya') ? 'bg-purple-600 text-white border-purple-600 shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'">
                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                        <span>3. Kunjungan Lainnya</span>
                        <span x-show="hasCat('kunjungan_lainnya')" class="text-purple-200 text-[10px]">✓</span>
                    </button>
                </div>
            </div>

            <!-- Card 1: Filter Wilayah & Informasi Sekolah (Select2 Searchable & Synced) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-brand-600"></i>
                        <span>Informasi Sekolah Tujuan &amp; Tim Marketing</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Sinkronisasi Data Dapodik &amp; Kemenag</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label for="district_filter" class="block font-bold text-slate-700 mb-1">
                            Filter Kecamatan Sekolah <span class="text-amber-600 font-normal">(Pilih Wilayah Terlebih Dahulu)</span>
                        </label>
                        <select id="district_filter" name="district_filter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-700 bg-amber-50/40 focus:border-brand-500">
                            <option value="">-- Semua Kecamatan (Filter Wilayah) --</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" {{ old('district_filter') == $d->id ? 'selected' : '' }}>
                                    Kecamatan {{ $d->name }} ({{ $d->schools_count }} Sekolah)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="school_id" class="block font-bold text-slate-700 mb-1">
                            Sekolah Kunjungan SLTA <span class="text-red-500">*</span> <span class="text-slate-400 font-normal">(Ketik / Cari Select2)</span>
                        </label>
                        <select name="school_id" id="school_id" required class="select2-school w-full">
                            <option value="">-- Cari &amp; Pilih Sekolah (Find) --</option>
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}" 
                                    data-district="{{ $s->district_id }}"
                                    data-district-name="{{ $s->district?->name }}"
                                    data-headmaster="{{ $s->headmaster_name }}"
                                    data-npsn="{{ $s->npsn }}"
                                    data-type="{{ $s->schoolType?->name }}"
                                    data-prio="{{ $s->priority_level }}"
                                    {{ old('school_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} (Kec. {{ $s->district ? $s->district->name : '-' }}) {{ $s->npsn ? '- NPSN ' . $s->npsn : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p id="school_count_info" class="text-[10px] text-slate-400 mt-1">
                            Menampilkan {{ $schools->count() }} sekolah dari integrasi Dapodik &amp; Kemenag.
                        </p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Staf Marketing Penanggung Jawab <span class="text-red-500">*</span></label>
                        <select name="user_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                            <option value="">-- Pilih Staf Marketing --</option>
                            @foreach($marketingUsers as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->role->name ?? 'Marketing' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kunjungan Lapangan <span class="text-red-500">*</span></label>
                        <input type="date" name="visit_date" value="{{ old('visit_date', date('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600 focus:border-brand-500">
                    </div>
                </div>
            </div>

            <!-- Card 2: Identitas Penerima & Kepala Sekolah (Menyesuaikan Kategori Agenda) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-amber-500"></i>
                        <span x-text="agendaCategories.length === 1 && hasCat('mengantar_undangan') ? 'Detail Penerima Undangan Acara' : 'Detail Penerima Kunjungan & Kepala Sekolah'"></span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Kontak PIC &amp; Pimpinan Sekolah</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            Nama Kepala Sekolah <span class="text-slate-400 font-normal">(Opsional / Otomatis)</span>
                        </label>
                        <input type="text" id="headmaster_name" name="headmaster_name" value="{{ old('headmaster_name') }}" placeholder="Contoh: Dr. H. Ahmad Sudrajat, M.M." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            <span x-text="agendaCategories.length === 1 && hasCat('mengantar_undangan') ? 'Nama Penerima Undangan Beserta Gelarnya' : '1. Nama Penerima Beserta Gelarnya'"></span> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Contoh: Drs. H. Mulyadi, M.Pd." required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            <span x-text="agendaCategories.length === 1 && hasCat('mengantar_undangan') ? 'Jabatan Penerima Undangan' : '2. Jabatan Penerima'"></span> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_position" value="{{ old('recipient_position') }}" placeholder="Contoh: Kepala Sekolah / Guru BK / Wakasek Humas" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            Nomor HP / WhatsApp Penerima <span class="text-amber-600 font-normal">(Untuk Follow-Up)</span>
                        </label>
                        <input type="tel" name="recipient_phone" value="{{ old('recipient_phone') }}" placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                </div>

                <!-- Bagian Konfirmasi Kehadiran Khusus: Mengantar Undangan Acara (Opsi 2) -->
                <div x-show="hasCat('mengantar_undangan')" x-transition class="pt-4 border-t border-slate-100 space-y-3">
                    <label class="block font-bold text-slate-800 text-xs">
                        Konfirmasi Kehadiran Undangan Acara <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                        <label class="flex items-center p-3 rounded-2xl border-2 cursor-pointer transition"
                            :class="invitationStatus === 'akan_hadir' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 font-bold shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                            <input type="radio" name="invitation_attendance_status" value="akan_hadir" x-model="invitationStatus" :disabled="!hasCat('mengantar_undangan')" class="sr-only">
                            <i data-lucide="check-circle-2" class="w-4 h-4 mr-2" :class="invitationStatus === 'akan_hadir' ? 'text-emerald-600' : 'text-slate-400'"></i>
                            <span>Akan Hadir</span>
                        </label>

                        <label class="flex items-center p-3 rounded-2xl border-2 cursor-pointer transition"
                            :class="invitationStatus === 'minta_ijin_dulu' ? 'border-amber-500 bg-amber-50 text-amber-900 font-bold shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                            <input type="radio" name="invitation_attendance_status" value="minta_ijin_dulu" x-model="invitationStatus" :disabled="!hasCat('mengantar_undangan')" class="sr-only">
                            <i data-lucide="clock" class="w-4 h-4 mr-2" :class="invitationStatus === 'minta_ijin_dulu' ? 'text-amber-600' : 'text-slate-400'"></i>
                            <span>Minta Ijin Dulu</span>
                        </label>

                        <label class="flex items-center p-3 rounded-2xl border-2 cursor-pointer transition"
                            :class="invitationStatus === 'tidak_bisa_hadir' ? 'border-rose-500 bg-rose-50 text-rose-900 font-bold shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                            <input type="radio" name="invitation_attendance_status" value="tidak_bisa_hadir" x-model="invitationStatus" :disabled="!hasCat('mengantar_undangan')" class="sr-only">
                            <i data-lucide="x-circle" class="w-4 h-4 mr-2" :class="invitationStatus === 'tidak_bisa_hadir' ? 'text-rose-600' : 'text-slate-400'"></i>
                            <span>Tidak Bisa Hadir</span>
                        </label>
                    </div>
                </div>

                <!-- Bagian Khusus: Kunjungan Lainnya (Opsi 3) -->
                <div x-show="hasCat('kunjungan_lainnya')" x-transition class="pt-4 border-t border-slate-100">
                    <div class="p-3 bg-purple-50/70 rounded-2xl border border-purple-200/70 text-xs text-purple-900 flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-purple-600 shrink-0"></i>
                        <span><strong>Agenda Kunjungan Lainnya Aktif:</strong> Keperluan audiensi, silaturahmi &amp; catatan dinas dapat dituliskan pada kolom catatan terpadu di bawah.</span>
                    </div>
                </div>
            </div>

            <!-- KONTEN KHUSUS OPSI 1 (PENJADWALAN SOSIALISASI) -->
            <div x-show="hasCat('penjadwalan_sosialisasi')" x-transition class="space-y-6">
                <!-- Card 3: Jumlah Siswa & Rombel (Field 3 & 7) -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                        <span>Estimasi Siswa Kelas 12</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">3. Jumlah Kelas 12 <span class="text-red-500">*</span></label>
                            <input type="number" name="grade_12_class_count" min="1" value="{{ old('grade_12_class_count') }}" placeholder="Contoh: 6" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">7. Rata-Rata Jumlah Siswa Per Kelas <span class="text-red-500">*</span></label>
                            <input type="number" name="students_per_class" min="1" value="{{ old('students_per_class') }}" placeholder="Contoh: 36" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold focus:border-brand-500">
                        </div>
                    </div>
                </div>

                <!-- Card 4: Target Waktu & Jadwal Sosialisasi (Field 4, 5, 6) -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <i data-lucide="clock" class="w-4 h-4 text-emerald-600"></i>
                        <span>Target Jadwal Waktu Sosialisasi</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">4. Target Bulan &amp; Tahun <span class="text-red-500">*</span></label>
                            <input type="text" name="target_month_year" value="{{ old('target_month_year') }}" placeholder="Contoh: Oktober 2026" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">5. Target Tanggal <span class="text-amber-600 font-normal">(Opsional)</span></label>
                            <input type="date" name="target_date" value="{{ old('target_date') }}" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">6. Target Waktu Sosialisasi <span class="text-red-500">*</span></label>
                            <input type="text" name="target_time" value="{{ old('target_time') }}" placeholder="Contoh: 08:30 WIB" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                        </div>
                    </div>
                </div>

                <!-- Card 5: Metode Sosialisasi (Field 8 & Dynamic Input) -->
                <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                    <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                        <i data-lucide="presentation" class="w-4 h-4 text-rose-500"></i>
                        <span>Metode Kegiatan Sosialisasi</span>
                    </h3>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-900 mb-1">8. Metode Sosialisasi <span class="text-red-500">*</span></label>
                            <select name="socialization_method" x-model="selectedMethod" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 focus:border-brand-500">
                                <option value="">-- Pilih Metode Sosialisasi --</option>
                                <option value="Sosialisasi di Kelas">a. Sosialisasi di Kelas</option>
                                <option value="Sosialisasi di Aula">b. Sosialisasi di Aula</option>
                                <option value="Workshop Digital Kreatif">c. Workshop Digital Kreatif</option>
                                <option value="Edufair">d. Edufair</option>
                                <option value="Lainnya">e. Lainnya (Input Kegiatan Khusus)</option>
                            </select>
                        </div>

                        <!-- Dynamic Input Field if 'Lainnya' is selected -->
                        <div x-show="selectedMethod === 'Lainnya'" x-transition class="p-3 bg-amber-50 rounded-2xl border border-amber-200">
                            <label class="block font-bold text-amber-900 mb-1">Input Nama Kegiatan Khusus / Lainnya <span class="text-red-500">*</span></label>
                            <input type="text" name="other_activity_name" value="{{ old('other_activity_name') }}" :disabled="!hasCat('penjadwalan_sosialisasi')" placeholder="Tuliskan nama kegiatan sosialisasi khusus..." class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-white font-semibold text-slate-900">
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Hasil Visit <span class="text-red-500">*</span></label>
                            <select name="status" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold focus:border-brand-500">
                                <option value="berhasil" selected>Berhasil (Diizinkan Sosialisasi)</option>
                                <option value="tidak_bertemu">Tidak Bertemu (Jadwal Ulang)</option>
                                <option value="tidak_berhasil">Tidak Berhasil (Ditolak)</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Catatan Tambahan & Hasil Kunjungan Lapangan (Terpadu / Tanpa Duplikasi) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-brand-600"></i>
                        <span>Catatan Tambahan &amp; Hasil Kunjungan Lapangan</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Hasil Respon / Catatan Terpadu</span>
                </div>

                <div class="text-xs">
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Catatan Hasil Kunjungan / Respon Penerima <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="notes" rows="3" placeholder="Tuliskan catatan hasil kunjungan lapangan, respon penerima (undangan/sosialisasi/audiensi), atau tindak lanjut yang diperlukan..." class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500 font-medium">{{ old('notes') }}</textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Catatan ini terpadu untuk semua agenda yang Anda pilih di atas tanpa duplikasi isian.</p>
                </div>
            </div>

            <!-- Card 6: Dokumentasi Kunjungan Lapangan & Fitur Kamera Otomatis -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-emerald-600"></i>
                        <span>Dokumentasi Kunjungan Lapangan</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Foto Kegiatan / Berkas Bukti Kunjungan</span>
                </div>

                <div class="space-y-4 text-xs">
                    <!-- Tombol Aksi Kamera & Galeri -->
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" @click="openCamera()"
                            class="px-4 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Buka Kamera Langsung</span>
                        </button>

                        <button type="button" @click="document.getElementById('visit_photo').click()"
                            class="px-4 py-2.5 rounded-2xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition border border-slate-200 flex items-center space-x-2">
                            <i data-lucide="upload" class="w-4 h-4 text-slate-500"></i>
                            <span>Pilih dari Galeri / Berkas</span>
                        </button>

                        <span class="text-[11px] text-slate-500 italic">
                            (Format Foto JPG/PNG atau Dokumen PDF/ZIP hingga 50 MB)
                        </span>
                    </div>

                    <!-- Hidden Inputs for File and Camera Base64 -->
                    <input id="visit_photo" type="file" name="photo" @change="handleFileSelect($event)" 
                        accept="image/jpeg,image/png,image/webp,application/pdf,.rar,.zip,.doc,.docx" class="hidden">
                    <input type="hidden" name="photo_camera_base64" x-model="photoCameraBase64">

                    <!-- Kartu Bukti Dokumentasi Terpilih / Terfoto -->
                    <div x-show="photoPreviewUrl || photoFileName" x-transition class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="font-extrabold text-slate-800 text-xs">Bukti Dokumentasi Terpilih:</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="photoSource === 'camera' ? 'bg-emerald-100 text-emerald-800' : 'bg-brand-100 text-brand-800'"
                                    x-text="photoSource === 'camera' ? '📸 Hasil Jepret Kamera' : '📁 Berkas File'"></span>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium truncate max-w-xs" x-text="photoFileName"></span>
                        </div>

                        <!-- Pratinjau Gambar Jika Format Foto -->
                        <div x-show="photoPreviewUrl" class="relative max-w-sm rounded-xl overflow-hidden border border-slate-200 bg-black/5 shadow-inner">
                            <img :src="photoPreviewUrl" alt="Pratinjau Dokumentasi" class="w-full h-48 object-cover rounded-xl">
                        </div>

                        <!-- Bar Tombol Aksi Dokumentasi (Unduh, Ambil Ulang, Hapus) -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200/80">
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="downloadPhoto()" x-show="photoPreviewUrl"
                                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-brand-700 border border-slate-200 font-bold text-xs shadow-2xs transition inline-flex items-center space-x-1.5"
                                    title="Unduh foto ini ke perangkat Anda">
                                    <i data-lucide="download" class="w-3.5 h-3.5 text-brand-600"></i>
                                    <span>Unduh Foto</span>
                                </button>

                                <button type="button" @click="openCamera()"
                                    class="px-3 py-1.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs shadow-2xs transition inline-flex items-center space-x-1.5">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                                    <span>Ambil Ulang</span>
                                </button>
                            </div>

                            <button type="button" @click="clearPhoto()"
                                class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition inline-flex items-center space-x-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Hapus</span>
                            </button>
                        </div>

                        <p class="text-[11px] text-emerald-700 font-medium flex items-center gap-1.5">
                            <i data-lucide="cloud" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Foto ini akan otomatis diunggah ke Google Drive sekolah dan server saat formulir dikirim.</span>
                        </p>
                    </div>

                    @error('photo')
                        <span class="text-xs text-red-600 mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="flex items-center justify-center pt-2 pb-8">
                <button type="submit" class="w-full sm:w-auto px-10 py-4 bg-brand-navy hover:bg-slate-900 text-white font-extrabold text-sm rounded-2xl shadow-xl transition transform active:scale-95 flex items-center justify-center space-x-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span>Kirim Hasil Kunjungan Lapangan</span>
                </button>
            </div>
        </form>

        <!-- Modal Kamera Langsung Interaktif -->
        <div x-show="showCameraModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div class="bg-slate-900 rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-800 text-white flex flex-col">
                <!-- Modal Camera Header -->
                <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-extrabold text-white">Kamera Dokumentasi Lapangan</h4>
                            <p class="text-[10px] text-slate-400">Arahkan kamera ke kegiatan/penerima kunjungan</p>
                        </div>
                    </div>
                    <button type="button" @click="closeCamera()" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <!-- Video Stream Viewport -->
                <div class="relative bg-black flex items-center justify-center min-h-[320px] max-h-[460px] overflow-hidden">
                    <div x-show="isCameraLoading" class="absolute inset-0 flex flex-col items-center justify-center space-y-2 bg-slate-950 text-slate-400 text-xs">
                        <div class="w-8 h-8 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                        <span>Menghubungkan ke kamera...</span>
                    </div>

                    <div x-show="cameraError" class="p-6 text-center text-xs text-rose-400 space-y-2">
                        <i data-lucide="alert-triangle" class="w-8 h-8 mx-auto text-rose-500"></i>
                        <p x-text="cameraError"></p>
                    </div>

                    <video id="camera_video_feed" playsinline autoplay muted class="w-full h-full object-cover max-h-[460px]"></video>

                    <!-- Camera Grid Overlay Indicator -->
                    <div class="absolute inset-0 pointer-events-none border border-white/10 grid grid-cols-3 grid-rows-3">
                        <div class="border-r border-b border-white/5"></div>
                        <div class="border-r border-b border-white/5"></div>
                        <div class="border-b border-white/5"></div>
                        <div class="border-r border-b border-white/5"></div>
                        <div class="border-r border-b border-white/5"></div>
                        <div class="border-b border-white/5"></div>
                        <div class="border-r border-white/5"></div>
                        <div class="border-r border-white/5"></div>
                        <div></div>
                    </div>
                </div>

                <!-- Modal Camera Footer Controls -->
                <div class="p-4 bg-slate-950 flex items-center justify-between border-t border-slate-800">
                    <button type="button" @click="switchCamera()" 
                        class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition inline-flex items-center space-x-1.5"
                        title="Balik kamera depan / belakang">
                        <i data-lucide="flip-horizontal" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Balik Kamera</span>
                    </button>

                    <!-- Tombol Jepret Shutter -->
                    <button type="button" @click="capturePhoto()"
                        class="w-16 h-16 rounded-full bg-white hover:bg-slate-200 text-slate-900 shadow-xl shadow-white/20 p-1 flex items-center justify-center transform active:scale-90 transition">
                        <div class="w-13 h-13 rounded-full border-2 border-slate-900 flex items-center justify-center bg-emerald-500 text-white">
                            <i data-lucide="camera" class="w-6 h-6"></i>
                        </div>
                    </button>

                    <button type="button" @click="closeCamera()"
                        class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold text-xs transition">
                        Batal
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-500 pt-4 pb-8 border-t border-slate-200">
            <p>{{ $appSettings['footer_text'] }}</p>
            <p class="text-slate-400 mt-0.5">{{ $appSettings['footer_subtext'] }}</p>
        </div>
    </div>

    @php
        $initAgendaCategories = old('agenda_categories', old('agenda_category') ? (is_array(old('agenda_category')) ? old('agenda_category') : explode(',', old('agenda_category'))) : ['penjadwalan_sosialisasi']);
    @endphp
    <!-- Select2 Initialization, Synchronization Script, & Alpine Component -->
    <script>
        function publicVisitManager() {
            return {
                agendaCategories: @json($initAgendaCategories),
                invitationStatus: '{{ old('invitation_attendance_status', 'akan_hadir') }}',
                selectedMethod: '{{ old('socialization_method', '') }}',
                showCameraModal: false,
                cameraStream: null,
                facingMode: 'environment', // Default ke kamera belakang HP
                photoPreviewUrl: null,
                photoCameraBase64: '',
                photoFileName: '',
                photoSource: '',
                isCameraLoading: false,
                cameraError: null,

                hasCat(cat) {
                    return this.agendaCategories.includes(cat);
                },

                toggleCat(cat) {
                    if (this.hasCat(cat)) {
                        if (this.agendaCategories.length > 1) {
                            this.agendaCategories = this.agendaCategories.filter(c => c !== cat);
                        }
                    } else {
                        this.agendaCategories.push(cat);
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                get agendaCategoriesText() {
                    const labels = {
                        'penjadwalan_sosialisasi': '1. Penjadwalan Sosialisasi',
                        'mengantar_undangan': '2. Mengantar Undangan Acara',
                        'kunjungan_lainnya': '3. Kunjungan Lainnya'
                    };
                    if (this.agendaCategories.length === 3) {
                        return 'Semua Agenda Kunjungan Terpilih (3 Agenda)';
                    }
                    if (this.agendaCategories.length === 0) {
                        return '-- Pilih Kategori Agenda --';
                    }
                    return this.agendaCategories.map(c => labels[c] || c).join(' + ');
                },

                init() {
                    this.$watch('agendaCategories', () => {
                        this.$nextTick(() => {
                            if (window.lucide) {
                                window.lucide.createIcons();
                            }
                        });
                    });
                },

                async openCamera() {
                    this.showCameraModal = true;
                    this.isCameraLoading = true;
                    this.cameraError = null;
                    this.$nextTick(async () => {
                        if (window.lucide) window.lucide.createIcons();
                        await this.startStream();
                    });
                },

                async startStream() {
                    try {
                        this.stopStream();
                        const constraints = {
                            video: {
                                facingMode: { ideal: this.facingMode },
                                width: { ideal: 1920 },
                                height: { ideal: 1080 }
                            },
                            audio: false
                        };
                        this.cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                        const videoEl = document.getElementById('camera_video_feed');
                        if (videoEl) {
                            videoEl.srcObject = this.cameraStream;
                            await videoEl.play();
                        }
                        this.isCameraLoading = false;
                    } catch (err) {
                        console.error('Camera access error:', err);
                        this.isCameraLoading = false;
                        this.cameraError = 'Tidak dapat mengakses kamera. Pastikan izin kamera telah diizinkan di browser Anda.';
                    }
                },

                async switchCamera() {
                    this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
                    this.isCameraLoading = true;
                    await this.startStream();
                },

                capturePhoto() {
                    const videoEl = document.getElementById('camera_video_feed');
                    if (!videoEl || !this.cameraStream) return;

                    const canvas = document.createElement('canvas');
                    canvas.width = videoEl.videoWidth || 1280;
                    canvas.height = videoEl.videoHeight || 720;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                    this.photoPreviewUrl = dataUrl;
                    this.photoCameraBase64 = dataUrl;
                    this.photoFileName = 'kamera_kunjungan_' + (new Date().toISOString().replace(/[:.-]/g, '_')) + '.jpg';
                    this.photoSource = 'camera';

                    // Kosongkan file input agar prioritas kamera aktif
                    const fileInput = document.getElementById('visit_photo');
                    if (fileInput) fileInput.value = '';

                    this.closeCamera();
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                closeCamera() {
                    this.stopStream();
                    this.showCameraModal = false;
                },

                stopStream() {
                    if (this.cameraStream) {
                        this.cameraStream.getTracks().forEach(track => track.stop());
                        this.cameraStream = null;
                    }
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.photoFileName = file.name;
                    this.photoSource = 'file';
                    this.photoCameraBase64 = ''; // Reset kamera base64

                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.photoPreviewUrl = e.target.result;
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.photoPreviewUrl = null;
                    }
                },

                downloadPhoto() {
                    if (!this.photoPreviewUrl) return;
                    const link = document.createElement('a');
                    link.href = this.photoPreviewUrl;
                    link.download = this.photoFileName || 'bukti_dokumentasi_kunjungan.jpg';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                clearPhoto() {
                    this.photoPreviewUrl = null;
                    this.photoCameraBase64 = '';
                    this.photoFileName = '';
                    this.photoSource = '';
                    const fileInput = document.getElementById('visit_photo');
                    if (fileInput) fileInput.value = '';
                }
            };
        }

        $(document).ready(function() {
            const $districtSelect = $('#district_filter');
            const $schoolSelect = $('#school_id');
            const $headmasterInput = $('#headmaster_name');

            // Cache all school options
            const allSchools = [];
            $schoolSelect.find('option').each(function() {
                const val = $(this).val();
                if (val) {
                    allSchools.push({
                        val: val,
                        text: $(this).text().trim(),
                        districtId: String($(this).data('district') || ''),
                        districtName: $(this).data('district-name') || '',
                        headmaster: $(this).data('headmaster') || '',
                        npsn: $(this).data('npsn') || '',
                        type: $(this).data('type') || '',
                        prio: $(this).data('prio') || 'C',
                        selected: $(this).is(':selected')
                    });
                }
            });

            // Initialize Select2 with search
            $schoolSelect.select2({
                placeholder: '-- Cari & Pilih Sekolah (Find) --',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Sekolah tidak ditemukan pada wilayah ini";
                    },
                    searching: function() {
                        return "Mencari data sekolah...";
                    }
                }
            });

            // Filter school options in Select2 based on district
            function filterSchools(districtId, keepSelectionVal = null) {
                const targetVal = keepSelectionVal !== null ? keepSelectionVal : $schoolSelect.val();
                
                $schoolSelect.empty();
                $schoolSelect.append(new Option('-- Cari & Pilih Sekolah (Find) --', ''));

                let count = 0;
                let foundTarget = false;

                allSchools.forEach(function(item) {
                    if (!districtId || item.districtId === String(districtId)) {
                        const isSelected = String(item.val) === String(targetVal);
                        if (isSelected) foundTarget = true;
                        
                        const newOption = new Option(item.text, item.val, false, isSelected);
                        $(newOption).attr('data-district', item.districtId);
                        $(newOption).attr('data-headmaster', item.headmaster);
                        $schoolSelect.append(newOption);
                        count++;
                    }
                });

                if (!foundTarget && keepSelectionVal !== null) {
                    $schoolSelect.val('');
                }

                $schoolSelect.trigger('change.select2');

                // Update UI hint badge
                if (districtId) {
                    const districtName = $districtSelect.find('option:selected').text().trim();
                    $('#school_count_info').html(`Menampilkan <span class="font-bold text-brand-600">${count}</span> sekolah di ${districtName}.`);
                } else {
                    $('#school_count_info').html(`Menampilkan <span class="font-bold text-brand-600">${count}</span> sekolah dari data Dapodik &amp; Kemenag.`);
                }
            }

            // District change listener
            $districtSelect.on('change', function() {
                const districtId = $(this).val();
                filterSchools(districtId, null);
            });

            // School selection listener (auto-sync district & prefill headmaster)
            $schoolSelect.on('change', function() {
                const schoolId = $(this).val();
                if (schoolId) {
                    const schoolData = allSchools.find(s => String(s.val) === String(schoolId));
                    if (schoolData) {
                        // Auto sync district dropdown if empty or different
                        if (schoolData.districtId && $districtSelect.val() !== schoolData.districtId) {
                            $districtSelect.val(schoolData.districtId);
                            // Update the count info without wiping school selection
                            const districtName = $districtSelect.find('option:selected').text().trim();
                            const countInDistrict = allSchools.filter(s => s.districtId === schoolData.districtId).length;
                            $('#school_count_info').html(`Menampilkan <span class="font-bold text-brand-600">${countInDistrict}</span> sekolah di ${districtName}.`);
                        }

                        // Auto prefill headmaster name if field is empty and school has recorded headmaster
                        if (schoolData.headmaster && !$headmasterInput.val()) {
                            $headmasterInput.val(schoolData.headmaster);
                        }
                    }
                }
            });

            // Initial load
            const initDistrict = $districtSelect.val();
            const initSchool = '{{ old('school_id', '') }}';
            if (initDistrict) {
                filterSchools(initDistrict, initSchool);
            } else if (initSchool) {
                const schoolData = allSchools.find(s => String(s.val) === String(initSchool));
                if (schoolData && schoolData.districtId) {
                    $districtSelect.val(schoolData.districtId);
                    filterSchools(schoolData.districtId, initSchool);
                } else {
                    filterSchools('', initSchool);
                }
            }
        });
    </script>
</body>
</html>
