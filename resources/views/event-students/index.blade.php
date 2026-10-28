<x-app-layout title="Hasil Support Event - Data Siswa Event">
    <div x-data="{ showQrModal: false, showImportModal: false, copied: false }" class="space-y-6">

        <!-- Notification Messages -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500 text-white shadow-lg flex items-center justify-between text-sm font-bold">
                <div class="flex items-center space-x-2.5">
                    <i data-lucide="check-circle" class="w-5 h-5"></i>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-500 text-white shadow-lg flex items-center justify-between text-sm font-bold">
                <div class="flex items-center space-x-2.5">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if(session('import_errors') && count(session('import_errors')) > 0)
            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs shadow-sm">
                <p class="font-bold text-sm mb-1 text-amber-950">Catatan Baris Dilewati saat Import:</p>
                <ul class="list-disc pl-5 space-y-0.5 max-h-36 overflow-y-auto">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Header & Action Bar -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-400/20 text-indigo-300 border border-indigo-400/30">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 mr-1.5 text-indigo-400"></i> Hasil Support Event
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Tersinkron ke Follow Up Siswa
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Data Siswa Hasil Support Event
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Data pendaftaran siswa yang terjaring langsung dari stand booth atau kegiatan support event sekolah. Seluruh data tersinkronisasi otomatis ke sistem Follow Up Siswa untuk ditindaklanjuti tim marketing & admisi.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    @if($canPlot)
                        <a href="{{ route('student-follow-ups.index', ['source' => 'support_event', 'assigned_to' => 'unassigned']) }}"
                            class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-black bg-gradient-to-r from-brand-600 via-indigo-600 to-brand-700 hover:from-brand-700 hover:to-indigo-800 text-white shadow-lg shadow-brand-600/30 transition hover:scale-105">
                            <i data-lucide="split" class="w-4 h-4 text-amber-300"></i>
                            <span>Ploting Petugas ({{ $metrics['unassigned'] ?? 0 }} Belum)</span>
                        </a>
                    @endif

                    <a href="{{ route('event-students.create') }}" target="_blank"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 shadow-md shadow-amber-400/20 transition hover:scale-105">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Buka Form Siswa</span>
                    </a>

                    <button type="button" @click="showQrModal = true"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-slate-700 shadow-sm transition hover:scale-105">
                        <i data-lucide="qr-code" class="w-4 h-4 text-cyan-400"></i>
                        <span>QR Code Form</span>
                    </button>

                    <button type="button" @click="showImportModal = true"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-emerald-300 border border-slate-700 shadow-sm transition hover:scale-105">
                        <i data-lucide="file-up" class="w-4 h-4 text-emerald-400"></i>
                        <span>Import Data Manual</span>
                    </button>

                    <a href="{{ route('event-students.export', request()->query()) }}"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition hover:scale-105">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Download Excel</span>
                    </a>

                    <a href="{{ route('student-follow-ups.index', ['source' => 'support_event']) }}"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800/80 hover:bg-slate-700 text-slate-300 border border-slate-700 shadow-sm transition">
                        <i data-lucide="phone-call" class="w-4 h-4 text-emerald-400"></i>
                        <span>Follow Up Siswa</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <a href="{{ route('event-students.index') }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ !request()->anyFilled(['scholarship_interest', 'status', 'assigned_to']) ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa Event</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($metrics['total']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Semua Lead Booth</span>
            </a>

            @if($canPlot)
                <a href="{{ route('event-students.index', ['assigned_to' => 'unassigned']) }}" 
                    class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('assigned_to') === 'unassigned' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200/90' }}">
                    <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider flex items-center justify-between">
                        <span>Belum Di-ploting</span>
                        <i data-lucide="user-x" class="w-4 h-4 text-amber-500"></i>
                    </p>
                    <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($metrics['unassigned']) }}</p>
                    <span class="text-[11px] text-amber-700 font-medium">Antrean Petugas</span>
                </a>
            @endif

            <a href="{{ route('event-students.index', ['scholarship_interest' => 'ya']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('scholarship_interest') === 'ya' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider flex items-center justify-between">
                    <span>Minat Beasiswa</span>
                    <i data-lucide="award" class="w-4 h-4 text-emerald-500"></i>
                </p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($metrics['scholarship_yes']) }}</p>
                <span class="text-[11px] text-emerald-700 font-medium">Prioritas Beasiswa</span>
            </a>

            <a href="{{ route('event-students.index', ['scholarship_interest' => 'tidak']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('scholarship_interest') === 'tidak' ? 'border-slate-500 ring-2 ring-slate-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Non-Beasiswa</p>
                <p class="text-2xl font-extrabold text-slate-700 mt-1">{{ number_format($metrics['scholarship_no']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Jalur Mandiri</span>
            </a>

            <a href="{{ route('event-students.index', ['status' => 'belum_dihubungi']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('status') === 'belum_dihubungi' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Belum Dihubungi</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($metrics['belum_dihubungi']) }}</p>
                <span class="text-[11px] text-amber-700 font-medium">Perlu Di-Follow Up</span>
            </a>

            <div class="p-4 bg-gradient-to-br from-indigo-50 to-purple-50/50 rounded-2xl border border-indigo-200">
                <p class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Sudah Di-Follow Up</p>
                <p class="text-2xl font-black text-indigo-900 mt-1">{{ number_format($metrics['terfollowup']) }}</p>
                <span class="text-[11px] text-indigo-600 font-medium">Respon / Closing</span>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-sm">
            <form method="GET" action="{{ route('event-students.index') }}" class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa, sekolah, no. HP, email, kelas, jurusan..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                    <!-- Filter Event -->
                    <select name="event_id" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500 max-w-[200px]">
                        <option value="">Semua Event</option>
                        @foreach($events as $e)
                            <option value="{{ $e->id }}" {{ request('event_id') == $e->id ? 'selected' : '' }}>
                                {{ $e->event_name }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Filter Minat Beasiswa -->
                    <select name="scholarship_interest" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Semua Minat Beasiswa</option>
                        <option value="ya" {{ request('scholarship_interest') === 'ya' ? 'selected' : '' }}>Ya (Berminat)</option>
                        <option value="tidak" {{ request('scholarship_interest') === 'tidak' ? 'selected' : '' }}>Tidak (Mandiri)</option>
                    </select>

                    <!-- Filter Status Follow Up -->
                    <select name="status" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Semua Status Follow Up</option>
                        <option value="belum_dihubungi" {{ request('status') === 'belum_dihubungi' ? 'selected' : '' }}>Belum Dihubungi</option>
                        <option value="respon" {{ request('status') === 'respon' ? 'selected' : '' }}>Respon</option>
                        <option value="berminat" {{ request('status') === 'berminat' ? 'selected' : '' }}>Berminat</option>
                        <option value="daftar_aplikasi" {{ request('status') === 'daftar_aplikasi' ? 'selected' : '' }}>Daftar Aplikasi</option>
                        <option value="no_respon" {{ request('status') === 'no_respon' ? 'selected' : '' }}>No Respon</option>
                    </select>

                    @if($canPlot)
                        <!-- Filter Petugas Follow Up -->
                        <select name="assigned_to" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="">Semua Penugasan Petugas</option>
                            <option value="unassigned" {{ request('assigned_to') === 'unassigned' ? 'selected' : '' }}>⚠️ Belum Di-ploting ({{ $metrics['unassigned'] ?? 0 }})</option>
                            <optgroup label="Petugas Marketing & CS">
                                @foreach($assignableOfficers as $off)
                                    <option value="{{ $off->id }}" {{ request('assigned_to') == $off->id ? 'selected' : '' }}>
                                        {{ $off->name }} ({{ $off->total_assigned_count ?? 0 }} Data)
                                    </option>
                                @endforeach
                            </optgroup>
                        </select>
                    @endif

                    <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-sm transition">
                        Filter
                    </button>

                    @if(request()->anyFilled(['search', 'event_id', 'scholarship_interest', 'status', 'assigned_to']))
                        <a href="{{ route('event-students.index') }}" class="px-3 py-2.5 rounded-xl text-xs font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Student Leads Table -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-extrabold text-slate-900">Daftar Siswa Pendaftar Event</h2>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                        {{ $students->total() }} Data Ditemukan
                    </span>
                </div>
                <p class="text-xs text-slate-400">Klik tombol Follow Up untuk membuka form pencatatan di CRM Follow Up Siswa.</p>
            </div>

            @if($students->isEmpty())
                <div class="py-16 text-center text-slate-400 space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                        <i data-lucide="user-x" class="w-8 h-8"></i>
                    </div>
                    <p class="text-base font-bold text-slate-700">Belum ada data siswa dari support event.</p>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Siswa dapat mengisi formulir pendaftaran saat event berlangsung melalui tautan form publik.</p>
                    <div class="pt-2">
                        <a href="{{ route('event-students.create') }}" target="_blank"
                            class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white hover:bg-brand-500 transition">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            <span>Buka Form Siswa Event</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider text-[11px]">
                            <tr>
                                <th class="py-3.5 px-4">Siswa, Sekolah & Kelas</th>
                                <th class="py-3.5 px-4">Event Asal</th>
                                <th class="py-3.5 px-4">Minat Beasiswa</th>
                                <th class="py-3.5 px-4">Kontak Siswa & Wali</th>
                                <th class="py-3.5 px-4">Petugas Follow Up</th>
                                <th class="py-3.5 px-4">Status Follow Up</th>
                                <th class="py-3.5 px-4 text-center min-w-36">Aksi Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $student)
                                @php
                                    $waNumberStudent = preg_replace('/[^0-9]/', '', $student->student_phone);
                                    if (str_starts_with($waNumberStudent, '0')) {
                                        $waNumberStudent = '62' . substr($waNumberStudent, 1);
                                    }
                                    $waText = urlencode("Halo Kak {$student->student_name}, salam hangat dari Universitas BSI (UBSI). Kami telah menerima pendaftaran Kakak saat support event. Terkait minat pada Program Beasiswa UBSI, apakah ada waktu luang untuk berdiskusi sebentar?");
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition duration-150 align-top">
                                    <!-- Siswa & Sekolah -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-extrabold text-slate-900 text-sm flex items-center gap-1.5">
                                            {{ $student->student_name }}
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-purple-100 text-purple-700 border border-purple-200">
                                                Event
                                            </span>
                                        </div>
                                        @if($student->school_display_name && $student->school_display_name !== '-')
                                            <div class="text-xs text-brand-700 font-bold mt-0.5 flex items-center gap-1">
                                                <i data-lucide="building-2" class="w-3.5 h-3.5 text-brand-500 shrink-0"></i>
                                                <span class="truncate max-w-[200px]" title="{{ $student->school_display_name }}">{{ $student->school_display_name }}</span>
                                            </div>
                                        @endif
                                        <div class="text-slate-600 font-semibold mt-0.5">
                                            {{ $student->student_class ?: '-' }} &bull; <span class="text-slate-500 font-normal">{{ $student->student_major ?: '-' }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 mt-1 flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <span>Daftar: {{ $student->created_at->translatedFormat('d M Y, H:i') }}</span>
                                        </div>
                                    </td>

                                    <!-- Event Asal -->
                                    <td class="py-3.5 px-4 max-w-xs">
                                        @if($student->eventProposal)
                                            <div class="font-bold text-slate-800">
                                                {{ $student->eventProposal->event_name }}
                                            </div>
                                            <span class="text-[10px] text-slate-500 font-mono block">
                                                Ref: {{ $student->eventProposal->reference_no }}
                                            </span>
                                        @elseif($student->college_interest_notes)
                                            <div class="text-xs font-semibold text-slate-700">
                                                {{ $student->college_interest_notes }}
                                            </div>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>

                                    <!-- Minat Beasiswa -->
                                    <td class="py-3.5 px-4">
                                        @if($student->scholarship_interest === 'ya')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-extrabold bg-emerald-50 text-emerald-800 border border-emerald-300 shadow-xs">
                                                <i data-lucide="award" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                Ya, Minat Beasiswa
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                Non-Beasiswa / Mandiri
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Kontak -->
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-1">
                                            <a href="https://wa.me/{{ $waNumberStudent }}?text={{ $waText }}" target="_blank"
                                                class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                                <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                <span>{{ $student->student_phone }}</span>
                                            </a>
                                            <div class="text-[11px] text-slate-500 truncate" title="{{ $student->student_email }}">
                                                ✉ {{ $student->student_email }}
                                            </div>
                                            @if($student->guardian_name)
                                                <div class="text-[11px] text-slate-600 pt-0.5">
                                                    Wali: <strong class="text-slate-800">{{ $student->guardian_name }}</strong> ({{ $student->guardian_phone }})
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Petugas Follow Up (Ploting) -->
                                    <td class="py-3.5 px-4">
                                        @if($student->assignedUser)
                                            <div class="flex items-center gap-2">
                                                <span class="w-7 h-7 rounded-full bg-brand-100 text-brand-700 font-black flex items-center justify-center text-xs shrink-0 border border-brand-200 shadow-xs">
                                                    {{ strtoupper(substr($student->assignedUser->name, 0, 1)) }}
                                                </span>
                                                <div>
                                                    <span class="font-extrabold text-slate-900 text-xs block">{{ $student->assignedUser->name }}</span>
                                                    <span class="text-[10px] text-slate-400">
                                                        {{ $student->assigned_at ? $student->assigned_at->format('d/m/y H:i') : 'Di-ploting' }}
                                                    </span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                                Belum Di-ploting
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status Follow Up -->
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $student->followup_badge_class }}">
                                            {{ $student->followup_status_label }}
                                        </span>
                                        @if($student->lastFollowedUpBy)
                                            <div class="text-[10px] text-slate-500 mt-1">
                                                Oleh: {{ $student->lastFollowedUpBy->name }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Aksi Tindak Lanjut -->
                                    <td class="py-3.5 px-4 text-center">
                                        <a href="{{ route('student-follow-ups.index', ['search' => $student->student_phone]) }}"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-sm transition hover:scale-105">
                                            <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                            <span>Follow Up Siswa</span>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $students->links() }}
                </div>
            @endif
        </div>

        <!-- Modal QR Code Form Siswa Event -->
        <div x-show="showQrModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showQrModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-800 space-y-5 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-cyan-100 text-cyan-700 flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">QR Code Form Siswa Event</h3>
                    </div>
                    <button type="button" @click="showQrModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                @php
                    $formUrl = url(route('event-students.create', [], false));
                    $qrImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=" . urlencode($formUrl);
                @endphp

                <p class="text-xs text-slate-600 leading-relaxed">
                    Siswa di booth / stand event cukup scan QR Code ini menggunakan kamera ponsel untuk mengisi form pendaftaran & minat beasiswa secara mandiri.
                </p>

                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center shadow-inner">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img src="{{ $qrImageUrl }}" alt="QR Code Form Siswa Event" class="w-48 h-48 mx-auto rounded-xl">
                    </div>
                    <p class="text-[11px] font-mono font-bold text-cyan-700 mt-2.5 truncate px-2">
                        {{ $formUrl }}
                    </p>
                </div>

                <div class="space-y-2 pt-1">
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('qr-codes.download', 'event-students') }}"
                            class="py-2.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh PNG HD</span>
                        </a>
                        <button type="button" @click="navigator.clipboard.writeText('{{ $formUrl }}'); copied = true; setTimeout(() => copied = false, 2000)"
                            class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            <span x-text="copied ? '✓ Disalin!' : 'Salin URL'"></span>
                        </button>
                    </div>

                    <a href="{{ route('event-students.create') }}" target="_blank"
                        class="w-full py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Halaman Form di Tab Baru</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Modal Import Data Manual -->
        <div x-show="showImportModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showImportModal = false"
                class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-800 space-y-5 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <i data-lucide="file-up" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">Import Data Siswa Event (Excel)</h3>
                    </div>
                    <button type="button" @click="showImportModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Unggah berkas Excel berisi data siswa yang mendaftar saat kegiatan offline. Data akan otomatis masuk ke database dan tersinkronisasi langsung ke menu <strong>Follow Up Siswa</strong>.
                </p>

                <!-- Download Template Banner -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 flex items-center justify-between gap-3">
                    <div class="space-y-0.5">
                        <p class="text-xs font-bold text-amber-900">Belum punya format file Excel?</p>
                        <p class="text-[11px] text-amber-700">Unduh template resmi dengan petunjuk pengisian.</p>
                    </div>
                    <a href="{{ route('event-students.template') }}"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow-sm transition shrink-0">
                        <i data-lucide="download" class="w-3.5 h-3.5"></i>
                        <span>Unduh Template</span>
                    </a>
                </div>

                <form action="{{ route('event-students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-1">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Pilih File Excel (.xlsx, .xls, .csv) <span class="text-rose-500">*</span>
                        </label>
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-5 text-center cursor-pointer transition">
                            <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                                class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="text-[11px] text-slate-400 mt-2">Maksimal ukuran file: 10 MB</p>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showImportModal = false"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-600/20 transition">
                            Mulai Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
