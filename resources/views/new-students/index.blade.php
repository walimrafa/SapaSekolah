<x-app-layout title="Data Mahasiswa Baru Resmi">
    <!-- Header Title & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wide">
                    Direktori Resmi
                </span>
                <span class="text-xs text-slate-400">• PMB UBSI Karawang</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight mt-1">Data Mahasiswa Baru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Daftar calon mahasiswa yang telah menyelesaikan Tahap 5 dan berhasil divalidasi resmi</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('prospects.index', ['status' => 'berkas_lengkap']) }}"
                class="px-3.5 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 font-bold rounded-xl text-xs shadow-sm flex items-center space-x-2 transition">
                <i data-lucide="user-check" class="w-4 h-4 text-amber-600"></i>
                <span>Antrean Validasi (Tahap 5)</span>
                @if($readyForValidationCount > 0)
                    <span class="px-1.5 py-0.2 bg-amber-600 text-white rounded-full text-[10px] font-extrabold">{{ $readyForValidationCount }}</span>
                @endif
            </a>

            <a href="{{ route('new-students.export', request()->query()) }}"
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 flex items-center space-x-2 transition">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export Data Excel</span>
            </a>

            <a href="{{ route('prospects.index') }}"
                class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-400"></i>
                <span>Kembali ke Pipeline Prospek</span>
            </a>
        </div>
    </div>

    <!-- Alert jika ada data Tahap 5 siap validasi -->
    @if($readyForValidationCount > 0)
        <div class="mb-6 p-4 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-950">Ada {{ $readyForValidationCount }} Calon Mahasiswa Menunggu Validasi!</h4>
                    <p class="text-[11px] text-amber-800">Calon siswa ini telah melengkapi berkas di Tahap 5 dan siap divalidasi untuk dipindahkan ke menu Mahasiswa Baru ini.</p>
                </div>
            </div>
            <a href="{{ route('prospects.index', ['status' => 'berkas_lengkap']) }}"
                class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow shrink-0 text-center transition">
                Buka Antrean Validasi
            </a>
        </div>
    @endif

    <!-- Key Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">Total Maba Resmi</span>
                <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                    <i data-lucide="graduation-cap" class="w-4 h-4"></i>
                </div>
            </div>
            <span class="text-2xl font-extrabold text-slate-900 mt-2 block">{{ number_format($totalStudents) }}</span>
            <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1 mt-1">
                <i data-lucide="check-circle" class="w-3 h-3"></i> Tervalidasi Resmi
            </span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-soft bg-emerald-50/20">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-800">Jalur Beasiswa</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <i data-lucide="award" class="w-4 h-4"></i>
                </div>
            </div>
            <span class="text-2xl font-extrabold text-emerald-900 mt-2 block">{{ number_format($totalBeasiswa) }}</span>
            <span class="text-[10px] text-emerald-700 font-semibold block mt-1">
                {{ $totalStudents > 0 ? round(($totalBeasiswa / $totalStudents) * 100, 1) : 0 }}% dari total maba
            </span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-600">Non Beasiswa (Mandiri)</span>
                <div class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
            </div>
            <span class="text-2xl font-extrabold text-slate-800 mt-2 block">{{ number_format($totalNonBeasiswa) }}</span>
            <span class="text-[10px] text-slate-500 font-semibold block mt-1">
                {{ $totalStudents > 0 ? round(($totalNonBeasiswa / $totalStudents) * 100, 1) : 0 }}% jalur reguler
            </span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">Sekolah Asal</span>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                </div>
            </div>
            <span class="text-2xl font-extrabold text-indigo-900 mt-2 block">{{ number_format($totalSchools) }}</span>
            <span class="text-[10px] text-slate-400 block mt-1">Basis SLTA mitra</span>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500">Gender (L / P)</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <span class="text-xl font-extrabold text-slate-900 mt-2 block">{{ $genderL }} L • {{ $genderP }} P</span>
            <span class="text-[10px] text-slate-500 font-semibold block mt-1">
                {{ $totalStudents > 0 ? round(($genderL / $totalStudents) * 100, 0) : 0 }}% L / {{ $totalStudents > 0 ? round(($genderP / $totalStudents) * 100, 0) : 0 }}% P
            </span>
        </div>
    </div>

    <!-- Table Card with Search & Filters -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden" x-data="{ selectedStudent: null }">
        <div class="p-4 border-b border-slate-100">
            <form method="GET" action="{{ route('new-students.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <div class="relative flex-1 min-w-[200px]">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-2.5"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIM, no HP, email..."
                        class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <select name="school_id" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Sekolah Asal</option>
                    @foreach($schools as $s)
                        <option value="{{ $s->id }}" {{ request('school_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>

                <select name="interest_program" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Program Studi</option>
                    @foreach($studyPrograms as $sp)
                        <option value="{{ $sp->name }}" {{ request('interest_program') == $sp->name ? 'selected' : '' }}>{{ $sp->name }}</option>
                    @endforeach
                </select>

                <select name="study_time" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Waktu Kuliah</option>
                    <option value="reguler_pagi" {{ request('study_time') == 'reguler_pagi' ? 'selected' : '' }}>Reguler (Pagi - Siang)</option>
                    <option value="reguler_malam" {{ request('study_time') == 'reguler_malam' ? 'selected' : '' }}>Reguler (Sore - Malam)</option>
                    <option value="karyawan" {{ request('study_time') == 'karyawan' ? 'selected' : '' }}>Waktu Kuliah Karyawan (Jumat - Sabtu)</option>
                </select>

                <select name="scholarship" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Semua Program &amp; Beasiswa</option>
                    <option value="beasiswa" {{ request('scholarship') === 'beasiswa' ? 'selected' : '' }}>🎓 Semua Mahasiswa Beasiswa</option>
                    <option value="non_beasiswa" {{ request('scholarship') === 'non_beasiswa' ? 'selected' : '' }}>🏷️ Non Beasiswa (Reguler Mandiri)</option>
                    <option value="Beasiswa KIP-Kuliah" {{ request('scholarship') === 'Beasiswa KIP-Kuliah' ? 'selected' : '' }}>• Beasiswa KIP-Kuliah</option>
                    <option value="Beasiswa Jalur Sekolah" {{ request('scholarship') === 'Beasiswa Jalur Sekolah' ? 'selected' : '' }}>• Beasiswa Jalur Sekolah</option>
                    <option value="Beasiswa Prestasi" {{ request('scholarship') === 'Beasiswa Prestasi' ? 'selected' : '' }}>• Beasiswa Prestasi</option>
                    <option value="Beasiswa Talenta" {{ request('scholarship') === 'Beasiswa Talenta' ? 'selected' : '' }}>• Beasiswa Talenta</option>
                </select>

                <select name="gender" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                    <option value="">Gender (L/P)</option>
                    <option value="L" {{ request('gender') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                    <option value="P" {{ request('gender') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                </select>

                @if(request()->anyFilled(['search', 'school_id', 'interest_program', 'study_time', 'scholarship', 'gender', 'graduation_year']))
                    <a href="{{ route('new-students.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition">
                        Reset Filter
                    </a>
                @endif
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">NIM</th>
                        <th class="py-3.5 px-4">Nama Mahasiswa</th>
                        <th class="py-3.5 px-4">Sekolah Asal</th>
                        <th class="py-3.5 px-4">Program Studi</th>
                        <th class="py-3.5 px-4">No HP / WhatsApp</th>
                        <th class="py-3.5 px-4">Status &amp; Validasi</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($students as $s)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-900">
                                @if($s->nim)
                                    <span class="px-2 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200 text-xs font-mono font-bold">
                                        {{ $s->nim }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic font-sans text-[11px]">Belum diisi</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 text-xs">{{ $s->name }}</div>
                                <div class="flex items-center space-x-1.5 text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-semibold">{{ $s->gender === 'L' ? 'Laki-laki' : ($s->gender === 'P' ? 'Perempuan' : '-') }}</span>
                                    @if($s->graduation_year)
                                        <span>• Lulus {{ $s->graduation_year }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-semibold text-slate-800 block">{{ $s->school?->name ?? '-' }}</span>
                                <span class="text-[10px] text-slate-500 block">
                                    {{ $s->school?->district?->name ? 'Kec. ' . $s->school->district->name : '' }}
                                    {{ $s->school?->district?->regency?->name ? '• ' . $s->school->district->regency->name : '' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($s->interest_program)
                                    <span class="px-2.5 py-1 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-bold inline-block">
                                        {{ $s->interest_program }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic text-[11px]">-</span>
                                @endif

                                @if($s->study_time_label)
                                    @php
                                        $tb = $s->study_time_badge;
                                    @endphp
                                    <div class="mt-1">
                                        <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-[10px] font-semibold border {{ $tb['badge_class'] ?? 'bg-slate-100 text-slate-700 border-slate-200' }}" title="Waktu Kuliah">
                                            @if(($tb['icon'] ?? '') === 'sun')
                                                <i data-lucide="sun" class="w-3 h-3 text-amber-600"></i>
                                            @elseif(($tb['icon'] ?? '') === 'moon')
                                                <i data-lucide="moon" class="w-3 h-3 text-indigo-600"></i>
                                            @elseif(($tb['icon'] ?? '') === 'briefcase')
                                                <i data-lucide="briefcase" class="w-3 h-3 text-emerald-600"></i>
                                            @else
                                                <i data-lucide="clock" class="w-3 h-3 text-slate-500"></i>
                                            @endif
                                            <span>{{ $s->study_time_label }}</span>
                                        </span>
                                    </div>
                                @endif

                                @php
                                    $sb = $s->scholarship_badge;
                                @endphp
                                <div class="mt-1">
                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-md text-[10px] font-bold border {{ $sb['badge_class'] }}" title="Kategori Program / Beasiswa">
                                        @if($sb['is_scholarship'])
                                            <i data-lucide="award" class="w-3 h-3 text-emerald-600"></i>
                                        @else
                                            <i data-lucide="user-check" class="w-3 h-3 text-slate-500"></i>
                                        @endif
                                        <span>{{ $sb['label'] }}</span>
                                    </span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($s->phone)
                                    @php
                                        $cleanPhone = preg_replace('/[^0-9]/', '', $s->phone);
                                        if (str_starts_with($cleanPhone, '0')) {
                                            $cleanPhone = '62' . substr($cleanPhone, 1);
                                        }
                                    @endphp
                                    <a href="https://wa.me/{{ $cleanPhone }}" target="_blank"
                                        class="inline-flex items-center space-x-1 text-emerald-600 hover:text-emerald-700 font-semibold font-mono text-[11px]">
                                        <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                        <span>{{ $s->phone }}</span>
                                    </a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                                @if($s->email)
                                    <span class="text-[10px] text-slate-400 block truncate max-w-[150px]">{{ $s->email }}</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-800 font-extrabold text-[10px] border border-emerald-200">
                                    <i data-lucide="badge-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Mahasiswa Baru Resmi</span>
                                </div>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    @if($s->validated_at)
                                        Divalidasi: {{ $s->validated_at->format('d/m/Y') }}
                                    @else
                                        Terdaftar: {{ $s->created_at->format('d/m/Y') }}
                                    @endif
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <button type="button" @click="selectedStudent = {{ json_encode($s) }}"
                                        title="Lihat Detail Mahasiswa"
                                        class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>

                                    <!-- Batalkan Validasi Form -->
                                    <form action="{{ route('new-students.unvalidate', $s->id) }}" method="POST"
                                        onsubmit="return confirm('Apakah Anda yakin ingin membatalkan validasi untuk {{ $s->name }}? Data akan dikembalikan ke Antrean Prospek Tahap 5.');">
                                        @csrf
                                        <button type="submit" title="Batalkan Validasi (Kembalikan ke Tahap 5 Prospek)"
                                            class="p-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg border border-amber-200 transition">
                                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="graduation-cap" class="w-6 h-6"></i>
                                </div>
                                <h3 class="text-sm font-bold text-slate-700">Belum Ada Mahasiswa Baru yang Tervalidasi</h3>
                                <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">
                                    Calon siswa yang telah menyelesaikan Tahap 5 (Sudah Melengkapi Berkas) dapat divalidasi di Data Prospek untuk masuk ke daftar ini.
                                </p>
                                <a href="{{ route('prospects.index') }}" class="inline-flex items-center space-x-1.5 mt-3 px-3.5 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold shadow hover:bg-indigo-700">
                                    <span>Buka Pipeline Prospek</span>
                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($students->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $students->links() }}
            </div>
        @endif

        <!-- Student Detail Modal -->
        <div x-show="selectedStudent" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <div x-show="selectedStudent" @click="selectedStudent = null" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <div x-show="selectedStudent" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-md sm:w-full border border-slate-200 p-6 z-10">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                <i data-lucide="badge-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Detail Mahasiswa Baru</h3>
                                <p class="text-[10px] text-slate-400">Status Resmi Aktif</p>
                            </div>
                        </div>
                        <button type="button" @click="selectedStudent = null" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <template x-if="selectedStudent">
                        <div class="mt-4 space-y-3 text-xs">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Nama Lengkap</span>
                                <span class="font-bold text-slate-900 text-sm block" x-text="selectedStudent.name"></span>
                                <div class="flex items-center gap-2 text-[11px] text-slate-600 pt-1">
                                    <span>NIM: <strong class="font-mono text-indigo-700" x-text="selectedStudent.nim || '-'"></strong></span>
                                    <span>•</span>
                                    <span x-text="selectedStudent.gender === 'L' ? 'Laki-laki' : (selectedStudent.gender === 'P' ? 'Perempuan' : '-')"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Program Studi</span>
                                    <span class="font-bold text-slate-800 text-xs block mt-0.5" x-text="selectedStudent.interest_program || '-'"></span>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Waktu Kuliah / Shift</span>
                                    <span class="font-bold text-slate-800 text-xs block mt-0.5" x-text="selectedStudent.study_time_label || '-'"></span>
                                </div>
                            </div>

                            <!-- Kategori Beasiswa / Non Beasiswa Card -->
                            <div class="p-2.5 rounded-xl border"
                                :class="(selectedStudent.scholarship_badge && selectedStudent.scholarship_badge.is_scholarship) ? 'bg-emerald-50/70 border-emerald-300' : 'bg-slate-50 border-slate-200'">
                                <span class="text-[10px] uppercase font-bold block"
                                    :class="(selectedStudent.scholarship_badge && selectedStudent.scholarship_badge.is_scholarship) ? 'text-emerald-800' : 'text-slate-400'">
                                    Kategori Program / Beasiswa
                                </span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <i data-lucide="award" class="w-4 h-4 text-emerald-600" x-show="selectedStudent.scholarship_badge && selectedStudent.scholarship_badge.is_scholarship"></i>
                                    <i data-lucide="user-check" class="w-4 h-4 text-slate-500" x-show="!selectedStudent.scholarship_badge || !selectedStudent.scholarship_badge.is_scholarship"></i>
                                    <span class="font-extrabold text-xs"
                                        :class="(selectedStudent.scholarship_badge && selectedStudent.scholarship_badge.is_scholarship) ? 'text-emerald-900' : 'text-slate-800'"
                                        x-text="selectedStudent.scholarship_badge ? selectedStudent.scholarship_badge.label : (selectedStudent.scholarship_type || 'Non Beasiswa')"></span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Sekolah Asal</span>
                                    <span class="font-bold text-slate-800 text-xs block mt-0.5" x-text="selectedStudent.school ? selectedStudent.school.name : '-'"></span>
                                </div>
                                <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                                    <span class="text-[10px] text-slate-400 uppercase font-bold block">Tahun Lulus SLTA</span>
                                    <span class="font-bold text-slate-800 text-xs block mt-0.5" x-text="selectedStudent.graduation_year || '-'"></span>
                                </div>
                            </div>

                            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-200 space-y-1">
                                <span class="text-[10px] text-slate-400 uppercase font-bold block">Kontak Mahasiswa</span>
                                <div class="flex items-center space-x-2">
                                    <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span class="font-mono" x-text="selectedStudent.phone || '-'"></span>
                                </div>
                                <div class="flex items-center space-x-2" x-show="selectedStudent.email">
                                    <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span class="text-slate-600" x-text="selectedStudent.email"></span>
                                </div>
                            </div>

                            <div class="p-2.5 bg-emerald-50 rounded-xl border border-emerald-200 text-emerald-950 text-[11px]" x-show="selectedStudent.notes">
                                <span class="font-bold block mb-0.5">Catatan Validasi:</span>
                                <p x-text="selectedStudent.notes"></p>
                            </div>

                            <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                                <button type="button" @click="selectedStudent = null"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
