@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pendaftaran Siswa - Support Event | {{ $appSettings['app_name'] }}</title>

    <!-- Favicon / Logo Title SAPA Sekolah -->
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- PWA Progressive Web App Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0B023E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Select2 Searchable Dropdown Assets -->
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/select2/select2-tailwind.css') }}">
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-100 min-h-screen py-6 px-4 sm:px-6">

    <main class="max-w-3xl mx-auto space-y-6">

        <!-- Standalone Header Bar -->
        <header class="bg-slate-900 text-white rounded-3xl p-6 shadow-xl border border-slate-800 flex items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                @if(!empty($appSettings['app_logo_url']))
                    <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-white p-1 shadow-md">
                @else
                    <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-white p-1 shadow-md">
                @endif
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight">{{ $appSettings['app_name'] }}</h1>
                    <p class="text-xs text-amber-400 font-semibold uppercase tracking-wider mt-0.5">
                        Pendataan Siswa - Hasil Support Event
                    </p>
                </div>
            </div>

            <div class="hidden sm:block text-right text-xs text-slate-400">
                <span class="font-bold text-amber-300 block">Universitas Bina Sarana Informatika (UBSI)</span>
                <span>Booth &amp; Kegiatan Sekolah</span>
            </div>
        </header>

        <!-- Notification Banner -->
        @if(session('success_message'))
            <div class="p-4 rounded-2xl bg-emerald-500 text-white shadow-lg flex items-center gap-3 text-sm font-bold" role="status">
                <i data-lucide="check-circle-2" class="w-6 h-6 shrink-0"></i>
                <p>{{ session('success_message') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-sm" role="alert">
                <p class="font-bold text-sm text-rose-900 mb-1">Mohon lengkapi formulir pendaftaran dengan benar:</p>
                <ul class="list-disc pl-5 space-y-1 text-rose-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(isset($campuses) && $campuses->count() > 1 && empty($selectedEvent))
            <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-soft flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center shrink-0">
                        <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">Pilih Kampus UBSI Penyelenggara:</span>
                        <span class="text-[11px] text-slate-500">Pilihan event & sekolah disesuaikan dengan kampus pilihan</span>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    @foreach($campuses as $c)
                        <a href="{{ route('event-students.create', array_filter(['campus_id' => $c->id, 'school_id' => $selectedSchoolId, 'event_id' => $selectedEventId])) }}" 
                           class="flex-1 sm:flex-none text-center px-4 py-2 rounded-xl text-xs font-bold transition {{ ($activeCampus?->id == $c->id) ? 'bg-brand-600 text-white shadow-md' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                            {{ $c->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('event-students.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="campus_id" value="{{ $activeCampus?->id }}">

            <!-- SECTION 1: Event / Kegiatan -->
            <section class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Event / Kegiatan Sekolah</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Pilih event atau booth tempat Anda mengisi formulir ini.</p>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        Support Event
                    </span>
                </div>

                <div x-data="{
                    selectedEventId: '{{ old('school_event_proposal_id', $selectedEventId ?? '') }}',
                    isOtherEvent: false
                }" class="space-y-3 text-xs">
                    <div>
                        <label for="school_event_proposal_id" class="block font-bold text-slate-700 mb-1">
                            Pilih Nama Event / Kegiatan
                        </label>
                        <select id="school_event_proposal_id" name="school_event_proposal_id" x-model="selectedEventId"
                            @change="isOtherEvent = (selectedEventId === 'other')"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">-- Pilih Event / Kegiatan (Opsional) --</option>
                            @foreach($events as $evt)
                                <option value="{{ $evt->id }}" {{ (string)old('school_event_proposal_id', $selectedEventId ?? '') === (string)$evt->id ? 'selected' : '' }}>
                                    {{ $evt->event_name }} - {{ $evt->school_name }} ({{ $evt->event_date ? \Carbon\Carbon::parse($evt->event_date)->format('d/m/Y') : '-' }})
                                </option>
                            @endforeach
                            <option value="other" {{ old('school_event_proposal_id') === 'other' ? 'selected' : '' }}>-- Kegiatan / Event Lainnya --</option>
                        </select>
                    </div>

                    <div x-show="isOtherEvent" style="display: none;" class="pt-1">
                        <label for="event_name_other" class="block font-bold text-slate-700 mb-1">Nama Kegiatan Lainnya</label>
                        <input id="event_name_other" name="event_name_other" value="{{ old('event_name_other') }}" placeholder="Tuliskan nama acara / perlombaan / pameran..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-amber-50/40 text-xs font-semibold focus:border-brand-500">
                    </div>
                </div>
            </section>

            <!-- SECTION 2: Identitas Siswa dan Wali (Copy dari Kuisioner) -->
            <section class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Identitas Siswa dan Wali</h2>
                    <p class="text-xs text-slate-500 mt-1">Akun media sosial bersifat opsional. Cantumkan nama platform dan akun, bila ada.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="student_name" class="block font-bold text-slate-700 mb-1">
                            Nama Lengkap <span class="text-rose-500">*</span>
                        </label>
                        <input id="student_name" name="student_name" value="{{ old('student_name') }}" required maxlength="150" autocomplete="name"
                            placeholder="Nama Lengkap Siswa"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Jenis Kelamin <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-2 mt-0.5">
                            <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-300 bg-white cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="gender" value="L" {{ old('gender', 'L') === 'L' ? 'checked' : '' }} required class="text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-slate-800 text-xs">Laki-laki (L)</span>
                            </label>
                            <label class="flex items-center gap-2 px-3 py-2.5 rounded-xl border border-slate-300 bg-white cursor-pointer hover:bg-slate-50 transition">
                                <input type="radio" name="gender" value="P" {{ old('gender') === 'P' ? 'checked' : '' }} required class="text-brand-600 focus:ring-brand-500">
                                <span class="font-bold text-slate-800 text-xs">Perempuan (P)</span>
                            </label>
                        </div>
                        @error('gender')<p class="mt-1 text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Asal Sekolah -->
                    <div>
                        <label for="school_id" class="block font-bold text-slate-700 mb-1">
                            Asal Sekolah <span class="text-rose-500">*</span>
                        </label>
                        <select id="school_id" name="school_id" class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">-- Ketik / Cari Asal Sekolah --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}" 
                                    data-type="{{ $sch->schoolType?->name ?? '' }}" 
                                    data-name="{{ $sch->name }}"
                                    {{ (string)old('school_id', $selectedSchoolId ?? '') === (string)$sch->id ? 'selected' : '' }}>
                                    {{ $sch->name }} {{ $sch->district ? '(' . $sch->district->name . ')' : '' }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('school_id', $selectedSchoolId ?? '') === 'other' || old('school_name') ? 'selected' : '' }}>-- Sekolah Lainnya (Tulis Manual) --</option>
                        </select>
                        @error('school_id')<p class="mt-1 text-rose-600">{{ $message }}</p>@enderror

                        <div id="manual_school_box" class="mt-2" style="{{ (old('school_id', $selectedSchoolId ?? '') === 'other' || old('school_name')) ? 'display: block;' : 'display: none;' }}">
                            <input id="school_name" name="school_name" value="{{ old('school_name') }}" maxlength="150"
                                placeholder="Ketik nama lengkap sekolah Anda di sini..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-amber-50/40 text-xs font-semibold focus:border-brand-500"
                                {{ (old('school_id', $selectedSchoolId ?? '') === 'other' || old('school_name')) ? 'required' : '' }}>
                            @error('school_name')<p class="mt-1 text-rose-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <!-- Kelas -->
                    <div>
                        <label for="student_class" class="block font-bold text-slate-700 mb-1">
                            Kelas <span class="text-rose-500">*</span>
                        </label>
                        <select id="student_class" name="student_class" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold focus:border-brand-500 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">-- Pilih Kelas --</option>
                            <option value="Kelas X" {{ old('student_class') === 'Kelas X' ? 'selected' : '' }}>Kelas X</option>
                            <option value="Kelas XI" {{ old('student_class') === 'Kelas XI' ? 'selected' : '' }}>Kelas XI</option>
                            <option value="Kelas XII" {{ old('student_class', 'Kelas XII') === 'Kelas XII' ? 'selected' : '' }}>Kelas XII</option>
                        </select>
                        @error('student_class')<p class="mt-1 text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- Jurusan -->
                    <div>
                        <label for="student_major_select" class="block font-bold text-slate-700 mb-1">
                            Jurusan <span class="text-rose-500">*</span>
                        </label>
                        <select id="student_major_select" class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold focus:border-brand-500 text-xs focus:outline-none focus:ring-2 focus:ring-brand-500/20">
                            <option value="">-- Pilih Jurusan --</option>
                            <option value="other">-- Pilihan Lain (Ketik Manual) --</option>
                        </select>
                        <input type="hidden" id="student_major" name="student_major" value="{{ old('student_major') }}" required>

                        <div id="manual_major_box" class="mt-2" style="display: none;">
                            <input type="text" id="custom_major" value="{{ old('student_major') }}" maxlength="100"
                                placeholder="Ketik nama jurusan Anda di sini..."
                                class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-amber-50/40 text-xs font-semibold focus:border-brand-500">
                            <p class="text-[11px] text-amber-700 mt-1 font-medium">* Silakan ketik nama jurusan jika tidak tersedia pada pilihan di atas.</p>
                        </div>
                        @error('student_major')<p class="mt-1 text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <!-- No HP Siswa -->
                    <div>
                        <label for="student_phone" class="block font-bold text-slate-700 mb-1">
                            No. HP Siswa <span class="text-rose-500">*</span>
                        </label>
                        <input id="student_phone" name="student_phone" type="tel" value="{{ old('student_phone') }}" required maxlength="30" autocomplete="tel"
                            placeholder="081234567890"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>

                    <!-- Email Siswa -->
                    <div>
                        <label for="student_email" class="block font-bold text-slate-700 mb-1">
                            Email Siswa <span class="text-rose-500">*</span>
                        </label>
                        <input id="student_email" name="student_email" type="email" value="{{ old('student_email') }}" required maxlength="150" autocomplete="email"
                            placeholder="nama@gmail.com"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>

                    <!-- Medsos Siswa -->
                    <div>
                        <label for="student_social_media" class="block font-bold text-slate-700 mb-1">
                            Akun Medsos Siswa
                        </label>
                        <input id="student_social_media" name="student_social_media" value="{{ old('student_social_media') }}" maxlength="255"
                            placeholder="IG / TikTok / YouTube / Facebook"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>

                    <!-- Nama Wali -->
                    <div>
                        <label for="guardian_name" class="block font-bold text-slate-700 mb-1">
                            Nama Wali <span class="text-rose-500">*</span>
                        </label>
                        <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required maxlength="150"
                            placeholder="Nama Orang Tua / Wali"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>

                    <!-- No HP Wali -->
                    <div>
                        <label for="guardian_phone" class="block font-bold text-slate-700 mb-1">
                            No. HP Wali <span class="text-rose-500">*</span>
                        </label>
                        <input id="guardian_phone" name="guardian_phone" type="tel" value="{{ old('guardian_phone') }}" required maxlength="30"
                            placeholder="081234567890"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>

                    <!-- Medsos Wali -->
                    <div>
                        <label for="guardian_social_media" class="block font-bold text-slate-700 mb-1">
                            Akun Medsos Wali
                        </label>
                        <input id="guardian_social_media" name="guardian_social_media" value="{{ old('guardian_social_media') }}" maxlength="255"
                            placeholder="IG / TikTok / YouTube / Facebook"
                            class="w-full px-3.5 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                    </div>
                </div>
            </section>

            <!-- SECTION 3: Minat Program Beasiswa UBSI (Sesuai Permintaan User: Ya atau Tidak) -->
            <section class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">Minat Program Beasiswa UBSI</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Tentukan ketertarikan Anda untuk mendapatkan beasiswa kuliah di Universitas BSI.</p>
                    </div>
                    <span class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                        <i data-lucide="award" class="w-4 h-4 text-amber-600"></i>
                    </span>
                </div>

                <fieldset class="space-y-3">
                    <legend class="text-xs sm:text-sm font-semibold text-slate-800">
                        Apakah Anda berminat mengikuti program beasiswa UBSI? <span class="text-rose-500">*</span>
                    </legend>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <!-- Pilihan YA -->
                        <label class="relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition {{ old('scholarship_interest', 'ya') === 'ya' ? 'border-emerald-500 bg-emerald-50/50' : 'border-slate-200 hover:border-emerald-300' }}">
                            <input type="radio" name="scholarship_interest" value="ya" required {{ old('scholarship_interest', 'ya') === 'ya' ? 'checked' : '' }}
                                class="w-5 h-5 text-emerald-600 focus:ring-emerald-500 border-slate-300">
                            <div class="ml-3.5">
                                <span class="block text-sm font-extrabold text-slate-900 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                    Ya, Berminat Beasiswa
                                </span>
                                <span class="block text-xs text-slate-500 mt-0.5">
                                    Beasiswa Prestasi, KIP Kuliah, Talenta & Beasiswa Khusus UBSI
                                </span>
                            </div>
                        </label>

                        <!-- Pilihan TIDAK -->
                        <label class="relative flex items-center p-4 rounded-2xl border-2 cursor-pointer transition {{ old('scholarship_interest') === 'tidak' ? 'border-slate-500 bg-slate-50' : 'border-slate-200 hover:border-slate-400' }}">
                            <input type="radio" name="scholarship_interest" value="tidak" required {{ old('scholarship_interest') === 'tidak' ? 'checked' : '' }}
                                class="w-5 h-5 text-slate-600 focus:ring-slate-500 border-slate-300">
                            <div class="ml-3.5">
                                <span class="block text-sm font-extrabold text-slate-900">
                                    Tidak / Jalur Reguler Mandiri
                                </span>
                                <span class="block text-xs text-slate-500 mt-0.5">
                                    Berminat informasi biaya kuliah reguler & cicilan tanpa beasiswa
                                </span>
                            </div>
                        </label>
                    </div>
                </fieldset>
            </section>

            <!-- Submit Button -->
            <div class="flex justify-center pb-4">
                <button type="submit"
                    class="w-full sm:w-auto px-10 py-4 bg-brand-navy hover:bg-slate-900 text-white font-extrabold text-sm rounded-2xl shadow-xl hover:shadow-2xl transition flex items-center justify-center gap-2 hover:scale-[1.02]">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span>Kirim Formulir Pendaftaran</span>
                </button>
            </div>
        </form>

        <footer class="text-center text-xs text-slate-500 pt-4 pb-8 border-t border-slate-200">
            <p>{{ $appSettings['footer_text'] ?? '© 2026 SAPA Sekolah • Universitas Bina Sarana Informatika' }}</p>
            <p class="text-slate-400 mt-0.5">Data terintegrasi langsung ke sistem informasi penerimaan mahasiswa baru.</p>
        </footer>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            const $schoolSelect = $('#school_id');
            const manualBox = document.getElementById('manual_school_box');
            const manualInput = document.getElementById('school_name');

            // Major & Dapodik/Kemenag integration elements
            const majorSelect = document.getElementById('student_major_select');
            const majorHidden = document.getElementById('student_major');
            const manualMajorBox = document.getElementById('manual_major_box');
            const customMajorInput = document.getElementById('custom_major');
            let initialSavedMajor = "{{ addslashes(old('student_major', '')) }}";

            function getDapodikKemenagMajors(schoolName, schoolType) {
                const sName = (schoolName || '').toUpperCase();
                const sType = (schoolType || '').toUpperCase();

                // 1. Kemenag MA (Madrasah Aliyah)
                if (sType === 'MA' || sName.includes(' MA ') || sName.startsWith('MA ') || sName.includes('MADRASAH ALIYAH') || sName.startsWith('MAS ')) {
                    return [
                        'Ilmu-Ilmu Keagamaan (IIK / Keagamaan)',
                        'MIPA / IPA (Ilmu Pengetahuan Alam)',
                        'IPS (Ilmu Pengetahuan Sosial)',
                        'Bahasa dan Budaya',
                        'Kurikulum Merdeka'
                    ];
                }

                // 2. Dapodik SMK / MAK (Kejuruan)
                if (sType === 'SMK' || sName.includes('SMK') || sName.includes('KEJURUAN')) {
                    if (sName.includes('FARMASI') || sName.includes('KESEHATAN')) {
                        return [
                            'Farmasi Klinis dan Komunitas',
                            'Asisten Keperawatan dan Caregiver',
                            'Teknologi Laboratorium Medik',
                            'Dental Asisten',
                            'Teknik Komputer dan Jaringan (TKJ)'
                        ];
                    }
                    if (sName.includes('TI ') || sName.includes('KOMPUTER') || sName.includes('INFORMATIKA') || sName.includes('TELEKOMUNIKASI')) {
                        return [
                            'Rekayasa Perangkat Lunak (RPL)',
                            'Teknik Komputer dan Jaringan (TKJ)',
                            'Desain Komunikasi Visual (DKV)',
                            'Sistem Informatika Jaringan dan Aplikasi (SIJA)',
                            'Teknik Jaringan Akses Telekomunikasi',
                            'Manajemen Perkantoran dan Layanan Bisnis (MPLB)',
                            'Akuntansi dan Keuangan Lembaga (AKL)'
                        ];
                    }
                    if (sName.includes('BISNIS') || sName.includes('MANAJEMEN') || sName.includes('EKONOMI') || sName.includes('AKUNTANSI')) {
                        return [
                            'Akuntansi dan Keuangan Lembaga (AKL)',
                            'Manajemen Perkantoran dan Layanan Bisnis (MPLB)',
                            'Pemasaran / Bisnis Digital',
                            'Perbankan dan Keuangan Mikro',
                            'Rekayasa Perangkat Lunak (RPL)'
                        ];
                    }
                    return [
                        'Teknik Komputer dan Jaringan (TKJ)',
                        'Rekayasa Perangkat Lunak (RPL)',
                        'Desain Komunikasi Visual (DKV)',
                        'Akuntansi dan Keuangan Lembaga (AKL)',
                        'Manajemen Perkantoran dan Layanan Bisnis (MPLB)',
                        'Pemasaran dan Bisnis Digital',
                        'Teknik Kendaraan Ringan Otomotif (TKRO)',
                        'Teknik dan Bisnis Sepeda Motor (TBSM)',
                        'Teknik Pemesinan (TP)',
                        'Teknik Pengelasan (Welding)',
                        'Teknik Instalasi Tenaga Listrik (TITL)',
                        'Teknik Elektronika Industri',
                        'Perhotelan dan Jasa Pariwisata',
                        'Kuliner / Tata Boga'
                    ];
                }

                // 3. Dapodik SMA
                if (sType === 'SMA' || sName.includes('SMA') || sName.startsWith('SMAN') || sName.startsWith('SMAS')) {
                    return [
                        'MIPA / IPA (Matematika & Ilmu Pengetahuan Alam)',
                        'IPS (Ilmu Pengetahuan Sosial)',
                        'Bahasa dan Budaya',
                        'Kurikulum Merdeka (Peminatan Fase F)'
                    ];
                }

                // 4. Default Gabungan
                return [
                    'MIPA / IPA',
                    'IPS',
                    'Teknik Komputer dan Jaringan (TKJ)',
                    'Rekayasa Perangkat Lunak (RPL)',
                    'Akuntansi dan Keuangan Lembaga (AKL)',
                    'Manajemen Perkantoran dan Layanan Bisnis (MPLB)',
                    'Teknik Kendaraan Ringan (TKRO)',
                    'Ilmu Keagamaan (IIK)'
                ];
            }

            function syncMajors(targetValue = '') {
                if (!majorSelect) return;

                let sName = '';
                let sType = '';

                const selectedVal = $schoolSelect.val();
                if (selectedVal && selectedVal !== 'other') {
                    const opt = $schoolSelect.find('option:selected');
                    sName = opt.data('name') || opt.text();
                    sType = opt.data('type') || '';
                } else if (selectedVal === 'other' && manualInput) {
                    sName = manualInput.value;
                }

                const majors = getDapodikKemenagMajors(sName, sType);

                // Rebuild select options
                majorSelect.innerHTML = '<option value="">-- Pilih Jurusan --</option>';
                majors.forEach(function(m) {
                    const opt = document.createElement('option');
                    opt.value = m;
                    opt.textContent = m;
                    majorSelect.appendChild(opt);
                });

                const otherOpt = document.createElement('option');
                otherOpt.value = 'other';
                otherOpt.textContent = '-- Pilihan Lain (Ketik Manual) --';
                majorSelect.appendChild(otherOpt);

                if (targetValue) {
                    let matched = false;
                    for (let i = 0; i < majorSelect.options.length; i++) {
                        if (majorSelect.options[i].value.toLowerCase() === targetValue.toLowerCase()) {
                            majorSelect.selectedIndex = i;
                            majorHidden.value = majorSelect.options[i].value;
                            matched = true;
                            break;
                        }
                    }
                    if (!matched) {
                        majorSelect.value = 'other';
                        manualMajorBox.style.display = 'block';
                        customMajorInput.value = targetValue;
                        majorHidden.value = targetValue;
                    } else {
                        manualMajorBox.style.display = 'none';
                    }
                } else {
                    majorSelect.value = '';
                    majorHidden.value = '';
                    manualMajorBox.style.display = 'none';
                }
            }

            if (majorSelect) {
                majorSelect.addEventListener('change', function() {
                    if (this.value === 'other') {
                        manualMajorBox.style.display = 'block';
                        customMajorInput.focus();
                        majorHidden.value = customMajorInput.value.trim();
                    } else if (this.value === '') {
                        manualMajorBox.style.display = 'none';
                        majorHidden.value = '';
                    } else {
                        manualMajorBox.style.display = 'none';
                        majorHidden.value = this.value;
                    }
                });
            }

            if (customMajorInput) {
                customMajorInput.addEventListener('input', function() {
                    if (majorSelect && majorSelect.value === 'other') {
                        majorHidden.value = this.value.trim();
                    }
                });
            }

            if ($schoolSelect.length && typeof $.fn.select2 !== 'undefined') {
                $schoolSelect.select2({
                    placeholder: '-- Ketik / Cari Asal Sekolah --',
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function () {
                            return 'Sekolah tidak ditemukan. Silakan pilih opsi \"-- Sekolah Lainnya (Tulis Manual) --\"';
                        },
                        searching: function () {
                            return 'Mencari sekolah...';
                        }
                    }
                });

                function syncSchoolVisibility() {
                    const val = $schoolSelect.val();
                    const isOther = (val === 'other');
                    if (manualBox) {
                        if (isOther) {
                            $(manualBox).slideDown(150);
                            if (manualInput) {
                                manualInput.required = true;
                                setTimeout(() => manualInput.focus(), 150);
                            }
                        } else {
                            $(manualBox).slideUp(150);
                            if (manualInput) {
                                manualInput.required = false;
                            }
                        }
                    }

                    const container = $schoolSelect.next('.select2-container');
                    if (val) {
                        container.find('.select2-selection').removeClass('select2-invalid');
                    }

                    // Dynamically refresh majors based on selected school
                    syncMajors(majorHidden.value || initialSavedMajor);
                    initialSavedMajor = '';
                }

                $schoolSelect.on('change', syncSchoolVisibility);
                if (manualInput) {
                    manualInput.addEventListener('input', function() {
                        if ($schoolSelect.val() === 'other') {
                            syncMajors(majorHidden.value || customMajorInput.value);
                        }
                    });
                }

                // Initial sync on load
                syncSchoolVisibility();

                // Form submit validation handler
                const form = $schoolSelect.closest('form')[0];
                if (form) {
                    form.addEventListener('submit', function (e) {
                        const val = $schoolSelect.val();
                        const manualVal = manualInput ? manualInput.value.trim() : '';

                        if (!val) {
                            e.preventDefault();
                            const container = $schoolSelect.next('.select2-container');
                            container.find('.select2-selection').addClass('select2-invalid');
                            $schoolSelect.select2('open');
                            return false;
                        }

                        if (val === 'other' && !manualVal) {
                            e.preventDefault();
                            if (manualInput) {
                                manualInput.focus();
                                manualInput.classList.add('border-red-500');
                            }
                            return false;
                        }

                        // Validate major
                        if (majorSelect && majorSelect.value === 'other') {
                            const customVal = customMajorInput ? customMajorInput.value.trim() : '';
                            if (!customVal) {
                                e.preventDefault();
                                customMajorInput.focus();
                                customMajorInput.classList.add('border-red-500');
                                return false;
                            }
                            majorHidden.value = customVal;
                        } else if (majorSelect && !majorSelect.value) {
                            e.preventDefault();
                            majorSelect.focus();
                            majorSelect.classList.add('border-red-500');
                            return false;
                        } else if (majorSelect) {
                            majorHidden.value = majorSelect.value;
                        }
                    });
                }
            } else {
                syncMajors(initialSavedMajor);
            }
        });
    </script>
</body>
</html>
