@php
    $appSettings = \App\Services\AppSettingService::all();
    $scaleLabels = [
        1 => 'Sangat tidak setuju',
        2 => 'Tidak setuju',
        3 => 'Netral',
        4 => 'Setuju',
        5 => 'Sangat setuju',
    ];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kuisioner Siswa - {{ $appSettings['app_name'] }}</title>

    <!-- Favicon / Logo Title SAPA Sekolah -->
    <link rel="icon" type="image/png" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ !empty($appSettings['app_logo_url']) ? asset($appSettings['app_logo_url']) : asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-100 min-h-screen py-6 px-4 sm:px-6">
    <main class="max-w-3xl mx-auto space-y-6">
        <header class="bg-slate-900 text-white rounded-3xl p-6 shadow-xl border border-slate-800 flex items-center gap-4">
            @if(!empty($appSettings['app_logo_url']))
                <img src="{{ $appSettings['app_logo_url'] }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-white p-1 shadow-md">
            @else
                <img src="{{ asset('logo-sapa-sekolah.png') }}" alt="Logo" class="w-12 h-12 object-contain rounded-2xl bg-white p-1 shadow-md">
            @endif
            <div>
                <h1 class="text-xl font-extrabold tracking-tight">{{ $appSettings['app_name'] }}</h1>
                <p class="text-xs text-amber-400 font-semibold uppercase tracking-wider mt-1">Kuisioner Minat Kuliah Siswa</p>
            </div>
        </header>

        @if(session('success_message'))
            <div class="p-4 rounded-2xl bg-emerald-500 text-white shadow-lg flex items-center gap-3 text-sm font-bold" role="status">
                <i data-lucide="check-circle-2" class="w-6 h-6 shrink-0"></i>
                <p>{{ session('success_message') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm" role="alert">
                <p class="font-bold">Mohon periksa kembali isian Anda.</p>
                <ul class="list-disc pl-5 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($selectedEvent))
            <div class="p-4 rounded-2xl bg-gradient-to-r from-brand-900 to-indigo-900 text-white shadow-md border border-brand-700/50 flex items-center justify-between gap-3 text-xs">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-xl bg-amber-400/20 text-amber-300 flex items-center justify-center shrink-0">
                        <i data-lucide="sparkles" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <p class="font-extrabold text-amber-300 text-sm">{{ $selectedEvent->event_name }}</p>
                        <p class="text-slate-300">{{ $selectedEvent->school_name }} &bull; {{ $selectedEvent->event_date ? \Carbon\Carbon::parse($selectedEvent->event_date)->translatedFormat('d F Y') : '' }}</p>
                    </div>
                </div>
                <span class="hidden sm:inline-block px-2.5 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-[11px] font-bold border border-emerald-500/30">
                    Support Event
                </span>
            </div>
        @endif

        <form method="POST" action="{{ route('student-questionnaire.store') }}" class="space-y-6">
            @csrf
            @if(!empty($selectedEventId))
                <input type="hidden" name="school_event_proposal_id" value="{{ $selectedEventId }}">
            @endif
            <section class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Identitas Siswa dan Wali</h2>
                    <p class="text-xs text-slate-500 mt-1">Akun media sosial bersifat opsional. Cantumkan nama platform dan akun, bila ada.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label for="student_name" class="block font-bold text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input id="student_name" name="student_name" value="{{ old('student_name') }}" required maxlength="150" autocomplete="name" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        @error('student_name')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div x-data="{ 
                        selectedSchool: '{{ old('school_id', $selectedSchoolId ?? '') }}',
                        isOther: '{{ old('school_id', $selectedSchoolId ?? '') }}' === 'other' || '{{ old('school_name') ? 'true' : 'false' }}' === 'true',
                        init() {
                            if (!this.selectedSchool && '{{ old('school_name') }}') {
                                this.selectedSchool = 'other';
                                this.isOther = true;
                            }
                        }
                    }">
                        <label for="school_id" class="block font-bold text-slate-700 mb-1">Asal Sekolah <span class="text-red-500">*</span></label>
                        <select id="school_id" name="school_id" x-model="selectedSchool" @change="isOther = (selectedSchool === 'other')" required class="w-full px-3.5 py-3 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                            <option value="">-- Pilih Asal Sekolah --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}" {{ (string)old('school_id', $selectedSchoolId ?? '') === (string)$sch->id ? 'selected' : '' }}>
                                    {{ $sch->name }} {{ $sch->district ? '(' . $sch->district->name . ')' : '' }}
                                </option>
                            @endforeach
                            <option value="other" {{ old('school_id') === 'other' || old('school_name') ? 'selected' : '' }}>-- Sekolah Lainnya (Tulis Manual) --</option>
                        </select>
                        @error('school_id')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror

                        <div x-show="isOther" x-transition class="mt-2">
                            <input id="school_name" name="school_name" value="{{ old('school_name') }}" maxlength="150" placeholder="Ketik nama sekolah Anda di sini..." class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-amber-50/40 text-xs font-semibold focus:border-brand-500">
                            @error('school_name')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label for="student_class" class="block font-bold text-slate-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                        <input id="student_class" name="student_class" value="{{ old('student_class') }}" required maxlength="50" placeholder="Contoh: XII IPA 1" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        @error('student_class')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_major" class="block font-bold text-slate-700 mb-1">Jurusan <span class="text-red-500">*</span></label>
                        <input id="student_major" name="student_major" value="{{ old('student_major') }}" required maxlength="100" placeholder="Contoh: IPA, IPS, RPL" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        @error('student_major')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_phone" class="block font-bold text-slate-700 mb-1">No. HP Siswa <span class="text-red-500">*</span></label>
                        <input id="student_phone" name="student_phone" type="tel" value="{{ old('student_phone') }}" required maxlength="30" autocomplete="tel" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        @error('student_phone')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_email" class="block font-bold text-slate-700 mb-1">Email Siswa <span class="text-red-500">*</span></label>
                        <input id="student_email" name="student_email" type="email" value="{{ old('student_email') }}" required maxlength="150" autocomplete="email" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        @error('student_email')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="student_social_media" class="block font-bold text-slate-700 mb-1">Akun Medsos Siswa</label>
                        <input id="student_social_media" name="student_social_media" value="{{ old('student_social_media') }}" maxlength="255" placeholder="IG / TikTok / YouTube / Facebook" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label for="guardian_name" class="block font-bold text-slate-700 mb-1">Nama Wali <span class="text-red-500">*</span></label>
                        <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required maxlength="150" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        @error('guardian_name')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="guardian_phone" class="block font-bold text-slate-700 mb-1">No. HP Wali <span class="text-red-500">*</span></label>
                        <input id="guardian_phone" name="guardian_phone" type="tel" value="{{ old('guardian_phone') }}" required maxlength="30" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                        @error('guardian_phone')<p class="mt-1 text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="guardian_social_media" class="block font-bold text-slate-700 mb-1">Akun Medsos Wali</label>
                        <input id="guardian_social_media" name="guardian_social_media" value="{{ old('guardian_social_media') }}" maxlength="255" placeholder="IG / TikTok / YouTube / Facebook" class="w-full px-3.5 py-3 rounded-xl border border-slate-300">
                    </div>
                </div>
            </section>

            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-xs text-blue-900">
                Pilih satu jawaban untuk setiap pernyataan. Skala jawaban: 1 = Sangat tidak setuju, 2 = Tidak setuju, 3 = Netral, 4 = Setuju, 5 = Sangat setuju.
            </div>

            @foreach($questionSections as $sectionTitle => $questions)
                <section class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-5">
                    <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100">{{ $sectionTitle }}</h2>
                    @foreach($questions as $key => $question)
                        <fieldset class="space-y-3">
                            <legend class="text-xs sm:text-sm font-semibold text-slate-800">{{ $question }} <span class="text-red-500">*</span></legend>
                            <div class="grid grid-cols-1 sm:grid-cols-5 gap-2">
                                @foreach($scaleLabels as $value => $label)
                                    <label class="flex sm:flex-col items-center gap-3 sm:gap-1.5 p-3 rounded-xl border border-slate-200 hover:border-brand-400 hover:bg-brand-50/40 cursor-pointer text-xs text-slate-700 text-center">
                                        <input type="radio" name="answers[{{ $key }}]" value="{{ $value }}" required {{ old("answers.{$key}") == $value ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                                        <span class="font-bold">{{ $value }}</span>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error("answers.{$key}")<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                        </fieldset>
                    @endforeach
                </section>
            @endforeach

            <section class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft space-y-4">
                <h2 class="text-sm font-bold text-slate-900 pb-3 border-b border-slate-100">Minat Program Beasiswa UBSI</h2>
                <fieldset class="space-y-3">
                    <legend class="text-xs sm:text-sm font-semibold text-slate-800">Apakah Anda berminat mengikuti program beasiswa UBSI? <span class="text-red-500">*</span></legend>
                    <div class="flex flex-col sm:flex-row gap-3 text-sm">
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 hover:border-brand-400 cursor-pointer">
                            <input type="radio" name="scholarship_interest" value="ya" required {{ old('scholarship_interest') === 'ya' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span>Ya</span>
                        </label>
                        <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-200 hover:border-brand-400 cursor-pointer">
                            <input type="radio" name="scholarship_interest" value="tidak" required {{ old('scholarship_interest') === 'tidak' ? 'checked' : '' }} class="text-brand-600 focus:ring-brand-500">
                            <span>Tidak</span>
                        </label>
                    </div>
                    @error('scholarship_interest')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                </fieldset>
            </section>

            <div class="flex justify-center pb-4">
                <button type="submit" class="w-full sm:w-auto px-10 py-4 bg-brand-navy hover:bg-slate-900 text-white font-extrabold text-sm rounded-2xl shadow-xl transition flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-5 h-5"></i>
                    <span>Kirim Kuisioner</span>
                </button>
            </div>
        </form>
        <footer class="text-center text-xs text-slate-500 pt-4 pb-8 border-t border-slate-200">
            <p>{{ $appSettings['footer_text'] }}</p>
            <p class="text-slate-400 mt-0.5">{{ $appSettings['footer_subtext'] }}</p>
        </footer>
    </main>
</body>
</html>
