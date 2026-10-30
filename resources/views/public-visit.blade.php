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

    <div class="max-w-3xl mx-auto space-y-6" x-data="{
        selectedMethod: '{{ old('socialization_method', '') }}'
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

            <!-- Card 2: Identitas Penerima & Kepala Sekolah -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-amber-500"></i>
                        <span>Detail Penerima Kunjungan &amp; Kepala Sekolah</span>
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
                            1. Nama Penerima Beserta Gelarnya <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Contoh: Drs. H. Mulyadi, M.Pd." required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            2. Jabatan Penerima <span class="text-red-500">*</span>
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
                        <input type="number" name="grade_12_class_count" min="1" value="{{ old('grade_12_class_count') }}" placeholder="Contoh: 6" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">7. Rata-Rata Jumlah Siswa Per Kelas <span class="text-red-500">*</span></label>
                        <input type="number" name="students_per_class" min="1" value="{{ old('students_per_class') }}" placeholder="Contoh: 36" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold focus:border-brand-500">
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
                        <input type="text" name="target_month_year" value="{{ old('target_month_year') }}" placeholder="Contoh: Oktober 2026" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">5. Target Tanggal <span class="text-amber-600 font-normal">(Opsional)</span></label>
                        <input type="date" name="target_date" value="{{ old('target_date') }}" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">6. Target Waktu Sosialisasi <span class="text-red-500">*</span></label>
                        <input type="text" name="target_time" value="{{ old('target_time') }}" placeholder="Contoh: 08:30 WIB" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
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
                        <select name="socialization_method" x-model="selectedMethod" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900 focus:border-brand-500">
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
                        <input type="text" name="other_activity_name" value="{{ old('other_activity_name') }}" placeholder="Tuliskan nama kegiatan sosialisasi khusus..." class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-white font-semibold text-slate-900">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Hasil Visit <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold focus:border-brand-500">
                                <option value="berhasil" selected>Berhasil (Diizinkan Sosialisasi)</option>
                                <option value="tidak_bertemu">Tidak Bertemu (Jadwal Ulang)</option>
                                <option value="tidak_berhasil">Tidak Berhasil (Ditolak)</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan Visit</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan respon penerima..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 6: Unggah Dokumentasi Kunjungan (Foto / Berkas) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-emerald-600"></i>
                        <span>Dokumentasi Kunjungan Lapangan</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Foto Kegiatan / Berkas Surat</span>
                </div>

                <div class="text-xs space-y-2">
                    <label for="visit_photo" class="block font-bold text-slate-800">
                        Unggah Foto atau Berkas Bukti Kunjungan <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <input id="visit_photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp,application/pdf,.rar,.zip,.doc,.docx" 
                        class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition">
                    <p class="text-[10px] text-slate-400">
                        Format didukung: Foto (JPG, PNG, WEBP), Dokumen (PDF, Word), atau Arsip (RAR, ZIP) hingga 50 MB.
                    </p>
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

        <!-- Footer -->
        <div class="text-center text-xs text-slate-500 pt-4 pb-8 border-t border-slate-200">
            <p>{{ $appSettings['footer_text'] }}</p>
            <p class="text-slate-400 mt-0.5">{{ $appSettings['footer_subtext'] }}</p>
        </div>
    </div>

    <!-- Select2 Initialization & Synchronization Script -->
    <script>
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
