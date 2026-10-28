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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-100 min-h-screen py-6 px-4 sm:px-6">

    <div class="max-w-3xl mx-auto space-y-6" x-data="{
        districts: {{ json_encode($districts) }},
        allSchools: {{ json_encode($schools) }},
        selectedDistrictId: '{{ old('district_id', '') }}',
        selectedSchoolId: '{{ old('school_id', '') }}',
        selectedMethod: '{{ old('socialization_method', '') }}',
        get filteredSchools() {
            if (!this.selectedDistrictId) return this.allSchools;
            return this.allSchools.filter(s => s.district_id == this.selectedDistrictId);
        }
    }">
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

        <!-- Public Form Container -->
        <form method="POST" action="{{ route('public-visit.store') }}" class="space-y-6">
            @csrf

            <!-- Card 1: Filter Wilayah & Informasi Sekolah -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-brand-600"></i>
                    <span>Informasi Sekolah Tujuan &amp; Tim Marketing</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Filter Kecamatan Sekolah <span class="text-amber-600 font-normal">(Pilih Kecamatan Terlebih Dahulu)</span></label>
                        <select x-model="selectedDistrictId" @change="selectedSchoolId = ''" class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-bold text-brand-700 bg-amber-50/40 focus:border-brand-500">
                            <option value="">-- Semua Kecamatan (Filter Wilayah) --</option>
                            <template x-for="d in districts" :key="d.id">
                                <option :value="d.id" x-text="'Kecamatan ' + d.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sekolah Kunjungan SLTA <span class="text-red-500">*</span></label>
                        <select name="school_id" x-model="selectedSchoolId" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                            <option value="">-- Pilih Sekolah Tujuan --</option>
                            <template x-for="s in filteredSchools" :key="s.id">
                                <option :value="s.id" x-text="s.name + (s.district ? ' (Kec. ' + s.district.name + ')' : '')"></option>
                            </template>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1" x-show="selectedDistrictId">Menampilkan <span class="font-bold text-brand-600" x-text="filteredSchools.length"></span> sekolah di kecamatan terpilih.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Staf Marketing Penanggung Jawab <span class="text-red-500">*</span></label>
                        <select name="user_id" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold">
                            <option value="">-- Pilih Staf Marketing --</option>
                            @foreach($marketingUsers as $u)
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Kunjungan Lapangan <span class="text-red-500">*</span></label>
                        <input type="date" name="visit_date" value="{{ old('visit_date', date('Y-m-d')) }}" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-bold text-brand-600">
                    </div>
                </div>
            </div>

            <!-- Card 2: Identitas Penerima (Field 1 & 2) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-amber-500"></i>
                    <span>Detail Penerima Kunjungan</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">1. Nama Penerima Beserta Gelarnya <span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Contoh: Drs. H. Mulyadi, M.Pd." required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">2. Jabatan Penerima <span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_position" value="{{ old('recipient_position') }}" placeholder="Contoh: Kepala Sekolah / Guru BK / Wakasek Humas" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold">
                    </div>
                </div>
            </div>

            <!-- Card 3: Jumlah Siswa & Rombel (Field 3 & 7) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <h3 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                    <span>Estimasi Siswa Kelas 12</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">3. Jumlah Kelas 12 <span class="text-red-500">*</span></label>
                        <input type="number" name="grade_12_class_count" min="1" value="{{ old('grade_12_class_count') }}" placeholder="Contoh: 6" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">7. Rata-Rata Jumlah Siswa Per Kelas <span class="text-red-500">*</span></label>
                        <input type="number" name="students_per_class" min="1" value="{{ old('students_per_class') }}" placeholder="Contoh: 36" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-bold">
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
                        <input type="text" name="target_month_year" value="{{ old('target_month_year') }}" placeholder="Contoh: Oktober 2026" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-bold text-brand-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">5. Target Tanggal <span class="text-amber-600 font-normal">(Opsional)</span></label>
                        <input type="date" name="target_date" value="{{ old('target_date') }}" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">6. Target Waktu Sosialisasi <span class="text-red-500">*</span></label>
                        <input type="text" name="target_time" value="{{ old('target_time') }}" placeholder="Contoh: 08:30 WIB" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold">
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
                        <select name="socialization_method" x-model="selectedMethod" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-bold text-slate-900">
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
                        <input type="text" name="other_activity_name" value="{{ old('other_activity_name') }}" placeholder="Tuliskan nama kegiatan sosialisasi khusus..." class="w-full px-3.5 py-3 rounded-xl border border-amber-300 bg-white font-semibold text-slate-900">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Hasil Visit <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-bold">
                                <option value="berhasil" selected>Berhasil (Diizinkan Sosialisasi)</option>
                                <option value="tidak_bertemu">Tidak Bertemu (Jadwal Ulang)</option>
                                <option value="tidak_berhasil">Tidak Berhasil (Ditolak)</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan Visit</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan respon penerima..." class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-center pt-2 pb-8">
                <button type="submit" class="w-full sm:w-auto px-10 py-4 bg-brand-navy hover:bg-slate-900 text-white font-extrabold text-sm rounded-2xl shadow-xl transition transform active:scale-95 flex items-center justify-center space-x-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span>Kirim Hasil Kunjungan Lapangan</span>
                </button>
            </div>
        </form>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-500 pt-4 pb-8 border-t border-slate-200">
            <p>{{ $appSettings['footer_text'] }}</p>
            <p class="text-slate-400 mt-0.5">{{ $appSettings['footer_subtext'] }}</p>
        </div>
    </div>

</body>
</html>
