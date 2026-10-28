@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengajuan Support & Sponsorship Event Sekolah - {{ $appSettings['app_name'] }}</title>

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
<body class="font-sans antialiased text-slate-800 bg-slate-950 min-h-screen py-8 px-4 sm:px-6 relative overflow-x-hidden selection:bg-brand-500 selection:text-white">

    <!-- Background Decorative Gradients -->
    <div class="fixed top-0 left-1/4 w-96 h-96 bg-brand-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed bottom-0 right-1/4 w-96 h-96 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="fixed top-1/2 right-10 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-4xl mx-auto space-y-6 relative z-10" x-data="{
        allSchools: {{ json_encode($schools->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'district' => $s->district->name ?? ''])) }},
        schoolMode: '{{ old('school_id') ? 'select' : (old('school_name') ? 'manual' : 'select') }}',
        selectedSchoolId: '{{ old('school_id', '') }}',
        manualSchoolName: '{{ old('school_name', '') }}',
        schoolSearch: '',
        fileName: '',
        fileSizeFormatted: '',
        isSubmitting: false,
        otherSupportChecked: {{ old('support_type_other') ? 'true' : 'false' }},
        get filteredSchools() {
            if (!this.schoolSearch) return this.allSchools;
            const q = this.schoolSearch.toLowerCase();
            return this.allSchools.filter(s => s.name.toLowerCase().includes(q) || (s.district && s.district.toLowerCase().includes(q)));
        },
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.fileName = file.name;
                const sizeInMb = (file.size / (1024 * 1024)).toFixed(2);
                this.fileSizeFormatted = sizeInMb + ' MB';
            } else {
                this.fileName = '';
                this.fileSizeFormatted = '';
            }
        }
    }">

        <!-- Header Branding Bar -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-brand-950 text-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center space-x-4">
                @if(!empty($appSettings['app_logo_url']))
                    <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-14 h-14 object-contain rounded-2xl bg-white p-1.5 shadow-md">
                @else
                    <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-14 h-14 object-contain rounded-2xl bg-white p-1.5 shadow-md">
                @endif
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            <i data-lucide="sparkles" class="w-3 h-3 mr-1 text-amber-400"></i> Portal Kerjasama Event
                        </span>
                        <span class="text-xs text-slate-400 font-medium">BSI Peduli Pendidikan</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white mt-1">{{ $appSettings['app_name'] }}</h1>
                    <p class="text-xs sm:text-sm text-slate-300">Pengajuan Dukungan & Sponsorship Event Sekolah</p>
                </div>
            </div>

            <div class="text-center md:text-right border-t md:border-t-0 pt-3 md:pt-0 border-slate-800 text-xs text-slate-400 flex flex-row md:flex-col items-center md:items-end justify-between w-full md:w-auto gap-2">
                <span class="font-bold text-amber-300 bg-amber-400/10 px-3 py-1 rounded-full border border-amber-400/20">
                    {{ $appSettings['campus_name'] ?? 'Universitas BSI' }}
                </span>
                <span class="text-[11px] text-slate-400">Proses Cepat & Terpantau Realtime</span>
            </div>
        </div>

        <!-- Alert Validation Errors -->
        @if ($errors->any())
            <div class="bg-rose-500/10 border border-rose-500/30 rounded-2xl p-5 text-rose-200 shadow-lg">
                <div class="flex items-start space-x-3">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-400 mt-0.5 shrink-0"></i>
                    <div>
                        <h4 class="font-bold text-sm text-rose-300">Mohon lengkapi formulir pengajuan dengan benar:</h4>
                        <ul class="list-disc list-inside mt-2 text-xs space-y-1 text-rose-200/90">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Submission Form Card -->
        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-800 rounded-3xl p-6 sm:p-10 shadow-2xl text-slate-200">
            <div class="mb-8 border-b border-slate-800 pb-6">
                <h2 class="text-xl sm:text-2xl font-extrabold text-white flex items-center gap-2">
                    <i data-lucide="file-plus-2" class="w-6 h-6 text-brand-400"></i>
                    Formulir Pengajuan Support Kegiatan
                </h2>
                <p class="text-sm text-slate-400 mt-1.5 leading-relaxed">
                    Silakan isi rincian kegiatan sekolah Anda dan lampirkan berkas proposal. Pengajuan Anda akan langsung diteruskan ke tim marketing & pimpinan kampus untuk ditinjau.
                </p>
            </div>

            <form action="{{ route('school-events.store') }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true" class="space-y-8">
                @csrf

                <!-- SECTION 1: Asal Sekolah & PIC -->
                <div class="space-y-5">
                    <div class="flex items-center space-x-2 text-brand-400 font-bold text-sm uppercase tracking-wider">
                        <span class="w-6 h-6 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center text-xs">1</span>
                        <span>Asal Sekolah & Kontak Narahubung (PIC)</span>
                    </div>

                    <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-5 space-y-4">
                        <!-- Pilihan Sekolah Terdaftar / Input Manual -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="text-sm font-semibold text-slate-300">
                                    Sekolah Pengaju <span class="text-rose-400">*</span>
                                </label>
                                <div class="flex items-center space-x-2 text-xs">
                                    <button type="button" @click="schoolMode = 'select'; manualSchoolName = ''" 
                                        :class="schoolMode === 'select' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'"
                                        class="px-2.5 py-1 rounded-lg transition">
                                        Pilih Dari Daftar
                                    </button>
                                    <button type="button" @click="schoolMode = 'manual'; selectedSchoolId = ''" 
                                        :class="schoolMode === 'manual' ? 'bg-brand-600 text-white font-bold' : 'bg-slate-800 text-slate-400 hover:text-white'"
                                        class="px-2.5 py-1 rounded-lg transition">
                                        Sekolah Lain / Manual
                                    </button>
                                </div>
                            </div>

                            <div x-show="schoolMode === 'select'" class="space-y-2">
                                <select name="school_id" x-model="selectedSchoolId" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                                    <option value="">-- Pilih Sekolah Asal Anda --</option>
                                    @foreach($schools as $school)
                                        <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                                            {{ $school->name }} {{ $school->district ? '('.$school->district->name.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-400">Jika nama sekolah tidak ada di daftar, klik tombol "Sekolah Lain / Manual" di atas.</p>
                            </div>

                            <div x-show="schoolMode === 'manual'" class="space-y-1" style="display: none;">
                                <input type="text" name="school_name" x-model="manualSchoolName" placeholder="Contoh: SMA Negeri 1 Karawang / SMK Taruna Karya"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition"
                                    value="{{ old('school_name') }}">
                                <p class="text-[11px] text-amber-400/90">Ketik nama lengkap sekolah Anda dengan jelas beserta jenjangnya.</p>
                            </div>
                        </div>

                        <!-- Data Narahubung -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Nama Narahubung (PIC) <span class="text-rose-400">*</span>
                                </label>
                                <input type="text" name="pic_name" value="{{ old('pic_name') }}" required
                                    placeholder="Nama Lengkap Panitia / Guru"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Jabatan / Peran di Sekolah
                                </label>
                                <input type="text" name="pic_position" value="{{ old('pic_position') }}"
                                    placeholder="Contoh: Guru BK / Pembina OSIS / Ketua Panitia"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    No. WhatsApp Aktif <span class="text-rose-400">*</span>
                                </label>
                                <input type="tel" name="pic_phone" value="{{ old('pic_phone') }}" required
                                    placeholder="081234567890"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Detail Event / Kegiatan -->
                <div class="space-y-5">
                    <div class="flex items-center space-x-2 text-brand-400 font-bold text-sm uppercase tracking-wider">
                        <span class="w-6 h-6 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center text-xs">2</span>
                        <span>Detail Kegiatan / Event</span>
                    </div>

                    <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Nama Event / Kegiatan <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" name="event_name" value="{{ old('event_name') }}" required
                                placeholder="Contoh: PORSENI 2026 / Pentas Seni Akbar / Edu Fair Karir Siswa"
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-4 py-3 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Tanggal Pelaksanaan <span class="text-rose-400">*</span>
                                </label>
                                <input type="date" name="event_date" value="{{ old('event_date') }}" required
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Tanggal Selesai (Opsional)
                                </label>
                                <input type="date" name="event_end_date" value="{{ old('event_end_date') }}"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                                <p class="text-[10px] text-slate-500 mt-1">Kosongkan jika hanya 1 hari</p>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Waktu Pelaksanaan <span class="text-rose-400">*</span>
                                </label>
                                <input type="text" name="event_time" value="{{ old('event_time', '08:00 - 15:00 WIB') }}" required
                                    placeholder="Contoh: 08:00 - 15:00 WIB"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Lokasi / Tempat Acara <span class="text-rose-400">*</span>
                                </label>
                                <input type="text" name="event_location" value="{{ old('event_location') }}" required
                                    placeholder="Contoh: Lapangan Utama / Gedung Olahraga Sekolah"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                    Target & Jumlah Peserta <span class="text-rose-400">*</span>
                                </label>
                                <input type="text" name="target_participants" value="{{ old('target_participants') }}" required
                                    placeholder="Contoh: 500 Siswa Kelas XII & 50 Guru / Umum"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: Bentuk Support yang Diharapkan -->
                <div class="space-y-5">
                    <div class="flex items-center space-x-2 text-brand-400 font-bold text-sm uppercase tracking-wider">
                        <span class="w-6 h-6 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center text-xs">3</span>
                        <span>Bentuk Dukungan / Support yang Diharapkan</span>
                    </div>

                    <div class="bg-slate-950/60 border border-slate-800/80 rounded-2xl p-5 space-y-4">
                        <p class="text-xs text-slate-400">Pilih satu atau lebih bentuk kolaborasi yang dibutuhkan oleh kegiatan sekolah:</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="flex items-center space-x-3 p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="support_type_requested[]" value="Sponsorship Dana" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 bg-slate-950 border-slate-700">
                                <span class="text-xs font-medium text-slate-200">💰 Sponsorship Dana</span>
                            </label>

                            <label class="flex items-center space-x-3 p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="support_type_requested[]" value="Hadiah / Doorprize" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 bg-slate-950 border-slate-700">
                                <span class="text-xs font-medium text-slate-200">🎁 Hadiah & Doorprize</span>
                            </label>

                            <label class="flex items-center space-x-3 p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="support_type_requested[]" value="Narasumber / Pemateri" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 bg-slate-950 border-slate-700">
                                <span class="text-xs font-medium text-slate-200">🎤 Narasumber / Pemateri</span>
                            </label>

                            <label class="flex items-center space-x-3 p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="support_type_requested[]" value="Booth / Stand Pameran" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 bg-slate-950 border-slate-700">
                                <span class="text-xs font-medium text-slate-200">🎪 Stand / Booth Pameran</span>
                            </label>

                            <label class="flex items-center space-x-3 p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="support_type_requested[]" value="Media Partner" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 bg-slate-950 border-slate-700">
                                <span class="text-xs font-medium text-slate-200">📢 Media Partner & Promosi</span>
                            </label>

                            <label class="flex items-center space-x-3 p-3 rounded-xl bg-slate-900/80 border border-slate-800 hover:border-slate-700 cursor-pointer transition">
                                <input type="checkbox" name="support_type_requested[]" value="Merchandise & Souvenir" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 bg-slate-950 border-slate-700">
                                <span class="text-xs font-medium text-slate-200">🎒 Merchandise & Souvenir</span>
                            </label>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
                            <label class="flex items-center space-x-2 text-xs text-slate-400 cursor-pointer shrink-0">
                                <input type="checkbox" x-model="otherSupportChecked" class="w-4 h-4 rounded text-brand-500 focus:ring-brand-400 bg-slate-950 border-slate-700">
                                <span>Bentuk dukungan lainnya:</span>
                            </label>
                            <input type="text" name="support_type_other" value="{{ old('support_type_other') }}"
                                :disabled="!otherSupportChecked"
                                placeholder="Tuliskan bentuk dukungan lain yang diharapkan..."
                                class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-brand-500 transition disabled:opacity-40 disabled:cursor-not-allowed">
                        </div>

                        <div class="pt-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Estimasi Anggaran Dukungan yang Diajukan (Rp) - Opsional
                            </label>
                            <div class="relative max-w-sm">
                                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400 font-bold text-sm">Rp</span>
                                <input type="number" name="estimated_budget" value="{{ old('estimated_budget') }}" min="0" step="50000"
                                    placeholder="Contoh: 1500000"
                                    class="w-full bg-slate-900 border border-slate-700 rounded-xl pl-11 pr-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: Upload Berkas Proposal -->
                <div class="space-y-5">
                    <div class="flex items-center space-x-2 text-brand-400 font-bold text-sm uppercase tracking-wider">
                        <span class="w-6 h-6 rounded-full bg-brand-500/20 text-brand-400 flex items-center justify-center text-xs">4</span>
                        <span>Unggah Berkas Proposal Kegiatan</span>
                    </div>

                    <div class="bg-slate-950/60 border-2 border-dashed border-slate-700 hover:border-brand-500 rounded-2xl p-8 text-center transition group relative">
                        <input type="file" name="proposal_file" id="proposal_file" required
                            accept=".pdf,.doc,.docx,.zip,.rar"
                            @change="handleFileSelect($event)"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                        <div class="space-y-3 pointer-events-none">
                            <div class="w-16 h-16 rounded-2xl bg-brand-500/10 text-brand-400 border border-brand-500/20 flex items-center justify-center mx-auto group-hover:scale-110 transition duration-200">
                                <i data-lucide="upload-cloud" class="w-8 h-8"></i>
                            </div>

                            <div x-show="!fileName">
                                <p class="text-sm font-semibold text-slate-200">
                                    <span class="text-brand-400 underline">Klik untuk memilih file</span> atau drag & drop file proposal ke sini
                                </p>
                                <p class="text-xs text-slate-500 mt-1">
                                    Mendukung format PDF, DOC, DOCX, ZIP, atau RAR (Maksimal 25 MB)
                                </p>
                            </div>

                            <div x-show="fileName" style="display: none;" class="pt-2">
                                <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl bg-brand-500/20 border border-brand-400/40 text-brand-200">
                                    <i data-lucide="file-check" class="w-5 h-5 text-brand-400"></i>
                                    <span class="text-sm font-bold" x-text="fileName"></span>
                                    <span class="text-xs text-slate-400" x-text="'(' + fileSizeFormatted + ')'"></span>
                                </div>
                                <p class="text-[11px] text-emerald-400 mt-2 font-medium">Berkas siap diunggah. Klik lagi jika ingin mengganti file.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submission Actions & Commitment -->
                <div class="pt-4 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center space-x-2 text-xs text-slate-400">
                        <i data-lucide="shield-check" class="w-4 h-4 text-emerald-400 shrink-0"></i>
                        <span>Data dan proposal Anda aman dan hanya digunakan untuk evaluasi kerjasama.</span>
                    </div>

                    <button type="submit" :disabled="isSubmitting"
                        class="w-full sm:w-auto inline-flex items-center justify-center space-x-2.5 px-8 py-3.5 rounded-2xl text-base font-bold text-white bg-gradient-to-r from-brand-600 via-brand-500 to-amber-500 hover:from-brand-500 hover:to-amber-400 shadow-xl shadow-brand-500/25 transition-all duration-200 hover:scale-[1.02] disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="send" class="w-5 h-5" x-show="!isSubmitting"></i>
                        <span x-show="!isSubmitting">Kirim Pengajuan Proposal</span>
                        <span x-show="isSubmitting" style="display: none;" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Mengunggah Proposal...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Footer / Contact Info -->
        <div class="text-center text-xs text-slate-500 space-y-1 pb-8">
            <p>© {{ date('Y') }} {{ $appSettings['app_name'] }} • {{ $appSettings['campus_name'] ?? 'Universitas Bina Sarana Informatika' }}</p>
            <p>Butuh bantuan cepat atau konfirmasi langsung? Hubungi WhatsApp Layanan Kerjasama Kampus.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
