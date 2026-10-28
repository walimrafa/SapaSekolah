<x-app-layout title="Data Prospek CRM">
    <div x-data="{
        showAddModal: false,
        showImportModal: {{ $errors->has('student_file') ? 'true' : 'false' }},
        validateModalOpen: false,
        validatingProspect: null,
        prospectNim: '',
        prospectProgram: '',
        prospectStudyTime: '',
        prospectScholarshipType: '',
        prospectIsScholarship: 'beasiswa',
        prospectNotes: '',
        openValidateModal(p) {
            this.validatingProspect = p;
            this.prospectNim = p.nim || '';
            this.prospectProgram = p.interest_program || '';
            this.prospectStudyTime = p.study_time || 'reguler_pagi';
            this.prospectScholarshipType = p.scholarship_type || 'Beasiswa KIP-Kuliah';
            let lower = (p.scholarship_type || '').toLowerCase();
            if (lower.includes('non beasiswa') || lower.includes('mandiri') || !p.scholarship_type) {
                this.prospectIsScholarship = 'non_beasiswa';
                if (!p.scholarship_type) {
                    this.prospectScholarshipType = 'Non Beasiswa (Reguler Mandiri)';
                }
            } else {
                this.prospectIsScholarship = 'beasiswa';
            }
            this.prospectNotes = '';
            this.validateModalOpen = true;
        }
    }">
        <!-- Header Title & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-200 uppercase tracking-wide">
                        CRM Pipeline PMB
                    </span>
                    <span class="text-xs text-slate-400">• UBSI Karawang</span>
                </div>
                <h1 class="text-xl font-extrabold text-slate-900 tracking-tight mt-1">Data Prospek &amp; Lead Calon Mahasiswa</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pantau alur 5 tahapan konversi calon mahasiswa hingga validasi menjadi Mahasiswa Baru resmi</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <!-- Link to Mahasiswa Baru Menu -->
                <a href="{{ route('new-students.index') }}"
                    class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-extrabold rounded-xl text-xs shadow-md shadow-amber-500/20 flex items-center space-x-2 transition">
                    <i data-lucide="graduation-cap" class="w-4 h-4 text-white"></i>
                    <span>Menu Mahasiswa Baru</span>
                    <span class="px-1.5 py-0.2 bg-white/25 rounded-md text-[10px]">{{ $statusCounts['mahasiswa'] }}</span>
                </a>

                <button type="button" @click="showImportModal = true"
                    class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                    <span>Import Excel</span>
                </button>

                <button type="button" @click="showAddModal = true"
                    class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
                    <i data-lucide="user-plus" class="w-4 h-4 text-amber-400"></i>
                    <span>Tambah Prospek</span>
                </button>

                <!-- Modal Import Mahasiswa -->
                <div x-show="showImportModal" x-transition.opacity style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="student-import-title">
                    <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity" @click="showImportModal = false"></div>
                    <div class="relative z-10 flex min-h-full items-center justify-center p-4">
                        <div class="relative w-full max-w-xl rounded-3xl border border-slate-200 bg-white p-6 text-left shadow-2xl">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <h2 id="student-import-title" class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                                    Import Mahasiswa UBSI via Excel
                                </h2>
                                <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-600" aria-label="Tutup">
                                    <i data-lucide="x" class="w-5 h-5"></i>
                                </button>
                            </div>

                            <form action="{{ route('prospects.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                                @csrf
                                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between gap-3">
                                    <div>
                                        <span class="font-bold text-emerald-950 block text-xs">Gunakan template Excel resmi</span>
                                        <span class="text-[11px] text-emerald-800">Sekolah asal harus sudah terdaftar di data sekolah.</span>
                                    </div>
                                    <a href="{{ route('prospects.import-template') }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shrink-0">
                                        Unduh Template
                                    </a>
                                </div>
                                <div>
                                    <label for="student_file" class="block text-xs font-bold text-slate-700 mb-1">File mahasiswa <span class="text-red-500">*</span></label>
                                    <input id="student_file" type="file" name="student_file" accept=".xlsx,.xls,.csv" required
                                        class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 cursor-pointer">
                                    @error('student_file')
                                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 space-y-1">
                                    <p class="font-bold">Kolom wajib: NIM, Nama Mahasiswa, dan NPSN atau Sekolah Asal.</p>
                                    <p>Data dengan NIM yang sama akan diperbarui, bukan ditambahkan ulang. Data yang memiliki NIM otomatis masuk sebagai Mahasiswa Baru tervalidasi.</p>
                                </div>
                                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                                    <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Batal</button>
                                    <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow flex items-center gap-1.5">
                                        <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                        Mulai Import
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if(session('import_errors'))
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-950">
                <p class="font-bold mb-2">Beberapa baris tidak diimpor:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach(session('import_errors') as $importError)
                        <li>{{ $importError }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Banner jika ada Tahap 5 Siap Validasi -->
        @if($statusCounts['berkas_lengkap'] > 0)
            <div class="mb-6 p-4 bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-indigo-500/10 border-2 border-emerald-500/40 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-emerald-600/30">
                        <i data-lucide="badge-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-extrabold text-emerald-950">Ada {{ $statusCounts['berkas_lengkap'] }} Calon Mahasiswa Selesai Tahap 5 (Siap Divalidasi)!</h4>
                        <p class="text-[11px] text-emerald-800">Calon mahasiswa ini telah melengkapi berkas. Klik tombol <strong>Validasi</strong> pada tabel untuk memindahkan data mereka ke Menu Mahasiswa Baru.</p>
                    </div>
                </div>
                <a href="{{ route('prospects.index', ['status' => 'berkas_lengkap']) }}"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold rounded-xl text-xs shadow-md shadow-emerald-600/20 shrink-0 text-center transition">
                    Lihat Calon Siap Divalidasi ({{ $statusCounts['berkas_lengkap'] }})
                </a>
            </div>
        @endif

        <!-- CRM Funnel Pipeline: 5 Tahapan Resmi + Menu Mahasiswa Baru -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-7 gap-2 mb-6">
            <!-- 1. Sudah Daftar Akun -->
            <a href="{{ route('prospects.index', ['status' => 'daftar_akun']) }}"
                class="p-3 rounded-2xl bg-white border {{ request('status') === 'daftar_akun' ? 'border-blue-600 ring-2 ring-blue-600 bg-blue-50/20' : 'border-slate-200/80' }} hover:border-blue-400 text-center transition block group shadow-sm">
                <span class="text-[10px] font-extrabold text-blue-600 block uppercase tracking-tight">1. DAFTAR AKUN</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5 block group-hover:scale-105 transition">{{ $statusCounts['daftar_akun'] }}</span>
                <span class="text-[9px] text-slate-400 block mt-0.5">Sudah buat akun</span>
            </a>

            <!-- 2. Sudah Bayar Registrasi -->
            <a href="{{ route('prospects.index', ['status' => 'bayar_registrasi']) }}"
                class="p-3 rounded-2xl bg-white border {{ request('status') === 'bayar_registrasi' ? 'border-cyan-600 ring-2 ring-cyan-600 bg-cyan-50/20' : 'border-slate-200/80' }} hover:border-cyan-400 text-center transition block group shadow-sm">
                <span class="text-[10px] font-extrabold text-cyan-700 block uppercase tracking-tight">2. BAYAR REGISTRASI</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5 block group-hover:scale-105 transition">{{ $statusCounts['bayar_registrasi'] }}</span>
                <span class="text-[9px] text-slate-400 block mt-0.5">Biaya pendaftaran</span>
            </a>

            <!-- 3. Sudah Ujian Saringan -->
            <a href="{{ route('prospects.index', ['status' => 'ujian_saringan']) }}"
                class="p-3 rounded-2xl bg-white border {{ request('status') === 'ujian_saringan' ? 'border-purple-600 ring-2 ring-purple-600 bg-purple-50/20' : 'border-slate-200/80' }} hover:border-purple-400 text-center transition block group shadow-sm">
                <span class="text-[10px] font-extrabold text-purple-700 block uppercase tracking-tight">3. UJIAN SARINGAN</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5 block group-hover:scale-105 transition">{{ $statusCounts['ujian_saringan'] }}</span>
                <span class="text-[9px] text-slate-400 block mt-0.5">Tes seleksi masuk</span>
            </a>

            <!-- 4. Sudah Bayar Daftar Ulang -->
            <a href="{{ route('prospects.index', ['status' => 'bayar_daftar_ulang']) }}"
                class="p-3 rounded-2xl bg-white border {{ request('status') === 'bayar_daftar_ulang' ? 'border-amber-600 ring-2 ring-amber-600 bg-amber-50/20' : 'border-slate-200/80' }} hover:border-amber-400 text-center transition block group shadow-sm">
                <span class="text-[10px] font-extrabold text-amber-700 block uppercase tracking-tight">4. DAFTAR ULANG</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5 block group-hover:scale-105 transition">{{ $statusCounts['bayar_daftar_ulang'] }}</span>
                <span class="text-[9px] text-slate-400 block mt-0.5">Biaya kuliah awal</span>
            </a>

            <!-- 5. Sudah Melengkapi Berkas (Siap Divalidasi) -->
            <a href="{{ route('prospects.index', ['status' => 'berkas_lengkap']) }}"
                class="p-3 rounded-2xl bg-white border-2 {{ request('status') === 'berkas_lengkap' ? 'border-emerald-600 ring-2 ring-emerald-600 bg-emerald-50/30' : 'border-emerald-300 bg-emerald-50/10' }} hover:border-emerald-500 text-center transition block group shadow-sm relative">
                @if($statusCounts['berkas_lengkap'] > 0)
                    <span class="absolute -top-1.5 -right-1.5 px-1.5 py-0.2 bg-emerald-600 text-white rounded-full text-[9px] font-extrabold animate-bounce">SIAP</span>
                @endif
                <span class="text-[10px] font-extrabold text-emerald-800 block uppercase tracking-tight">5. LENGKAP BERKAS</span>
                <span class="text-xl font-extrabold text-emerald-700 mt-0.5 block group-hover:scale-105 transition">{{ $statusCounts['berkas_lengkap'] }}</span>
                <span class="text-[9px] text-emerald-600 font-bold block mt-0.5">Siap Validasi Maba</span>
            </a>

            <!-- Menjadi Mahasiswa Baru (Tervalidasi / Pindah ke Menu) -->
            <a href="{{ route('new-students.index') }}"
                class="p-3 rounded-2xl bg-gradient-to-br from-slate-900 via-brand-navy to-indigo-950 text-white text-center rounded-2xl shadow-md hover:shadow-indigo-900/30 transition block group relative overflow-hidden">
                <div class="absolute -right-2 -bottom-2 opacity-10">
                    <i data-lucide="graduation-cap" class="w-16 h-16 text-white"></i>
                </div>
                <div class="flex items-center justify-center space-x-1">
                    <span class="text-[10px] font-extrabold text-amber-300 block uppercase tracking-tight">MAHASISWA BARU</span>
                    <i data-lucide="external-link" class="w-3 h-3 text-amber-300 inline"></i>
                </div>
                <span class="text-xl font-extrabold text-white mt-0.5 block group-hover:scale-105 transition">{{ $statusCounts['mahasiswa'] }}</span>
                <span class="text-[9px] text-amber-200/90 font-semibold block mt-0.5">Buka Menu Maba &rarr;</span>
            </a>

            <!-- Tidak Lanjut -->
            <a href="{{ route('prospects.index', ['status' => 'tidak_lanjut']) }}"
                class="p-3 rounded-2xl bg-white border {{ request('status') === 'tidak_lanjut' ? 'border-red-600 ring-2 ring-red-600 bg-red-50/20' : 'border-slate-200/80' }} hover:border-red-400 text-center transition block group shadow-sm">
                <span class="text-[10px] font-extrabold text-red-500 block uppercase tracking-tight">TIDAK LANJUT</span>
                <span class="text-xl font-extrabold text-slate-900 mt-0.5 block group-hover:scale-105 transition">{{ $statusCounts['tidak_lanjut'] }}</span>
                <span class="text-[9px] text-slate-400 block mt-0.5">Batal / Gugur</span>
            </a>
        </div>

        <!-- Prospects Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <form method="GET" action="{{ route('prospects.index') }}" class="flex flex-wrap items-center gap-2 text-xs w-full lg:w-auto">
                    <div class="relative min-w-[220px]">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, HP, email..."
                            class="w-full py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                    </div>

                    <select name="status" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                        <option value="pipeline" {{ request('status', 'pipeline') === 'pipeline' ? 'selected' : '' }}>Semua Tahap Pipeline Aktif (1 s/d 5)</option>
                        <option value="daftar_akun" {{ request('status') === 'daftar_akun' ? 'selected' : '' }}>Tahap 1: Sudah Daftar Akun</option>
                        <option value="bayar_registrasi" {{ request('status') === 'bayar_registrasi' ? 'selected' : '' }}>Tahap 2: Sudah Bayar Registrasi</option>
                        <option value="ujian_saringan" {{ request('status') === 'ujian_saringan' ? 'selected' : '' }}>Tahap 3: Sudah Ujian Saringan</option>
                        <option value="bayar_daftar_ulang" {{ request('status') === 'bayar_daftar_ulang' ? 'selected' : '' }}>Tahap 4: Sudah Bayar Daftar Ulang</option>
                        <option value="berkas_lengkap" {{ request('status') === 'berkas_lengkap' ? 'selected' : '' }}>Tahap 5: Sudah Melengkapi Berkas (Siap Validasi)</option>
                        <option value="tidak_lanjut" {{ request('status') === 'tidak_lanjut' ? 'selected' : '' }}>Tidak Lanjut / Batal</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>-- Tampilkan Semua Termasuk Maba --</option>
                    </select>

                    <select name="school_id" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                        <option value="">Semua Sekolah Asal</option>
                        @foreach($schools as $s)
                            <option value="{{ $s->id }}" {{ request('school_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>

                    <select name="scholarship" onchange="this.form.submit()" class="py-2 px-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                        <option value="">Semua Program & Beasiswa</option>
                        <option value="beasiswa" {{ request('scholarship') === 'beasiswa' ? 'selected' : '' }}>🎓 Semua Mahasiswa Beasiswa</option>
                        <option value="non_beasiswa" {{ request('scholarship') === 'non_beasiswa' ? 'selected' : '' }}>🏷️ Non Beasiswa (Reguler Mandiri)</option>
                        <option value="Beasiswa KIP-Kuliah" {{ request('scholarship') === 'Beasiswa KIP-Kuliah' ? 'selected' : '' }}>• Beasiswa KIP-Kuliah</option>
                        <option value="Beasiswa Jalur Sekolah" {{ request('scholarship') === 'Beasiswa Jalur Sekolah' ? 'selected' : '' }}>• Beasiswa Jalur Sekolah</option>
                        <option value="Beasiswa Prestasi" {{ request('scholarship') === 'Beasiswa Prestasi' ? 'selected' : '' }}>• Beasiswa Prestasi</option>
                        <option value="Beasiswa Talenta" {{ request('scholarship') === 'Beasiswa Talenta' ? 'selected' : '' }}>• Beasiswa Talenta</option>
                    </select>

                    @if(request()->anyFilled(['search', 'school_id', 'marketing_user_id', 'scholarship']) || (request('status') && request('status') !== 'pipeline'))
                        <a href="{{ route('prospects.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold transition">
                            Reset Filter
                        </a>
                    @endif
                </form>

                <div class="text-[11px] text-slate-500 font-semibold">
                    Menampilkan <strong class="text-slate-800">{{ $prospects->total() }}</strong> calon mahasiswa
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3.5 px-4">Nama Calon Siswa</th>
                            <th class="py-3.5 px-4">No HP / WhatsApp</th>
                            <th class="py-3.5 px-4">Sekolah Asal</th>
                            <th class="py-3.5 px-4">Program &amp; Beasiswa</th>
                            <th class="py-3.5 px-4">PIC Marketing</th>
                            <th class="py-3.5 px-4 text-center">Tahapan Status</th>
                            <th class="py-3.5 px-4 text-center">Aksi / Validasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($prospects as $p)
                            <tr class="hover:bg-slate-50/80 transition {{ $p->status === 'berkas_lengkap' ? 'bg-emerald-50/30' : '' }}">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-xs">{{ $p->name }}</div>
                                    <div class="flex items-center space-x-1.5 text-[10px] text-slate-500 mt-0.5">
                                        @if($p->nim)
                                            <span class="font-mono font-bold text-indigo-700">NIM {{ $p->nim }}</span>
                                            <span>•</span>
                                        @endif
                                        <span>{{ $p->gender === 'L' ? 'L' : ($p->gender === 'P' ? 'P' : '-') }}</span>
                                        @if($p->graduation_year)
                                            <span>• Lulus {{ $p->graduation_year }}</span>
                                        @endif
                                    </div>
                                    @if($p->notes)
                                        <p class="text-[10px] text-slate-400 italic mt-0.5 max-w-xs truncate" title="{{ $p->notes }}">
                                            {{ $p->notes }}
                                        </p>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-700">
                                    @if($p->phone)
                                        @php
                                            $digits = preg_replace('/[^0-9]/', '', $p->phone);
                                            if (str_starts_with($digits, '0')) { $digits = '62' . substr($digits, 1); }
                                            elseif (str_starts_with($digits, '8')) { $digits = '62' . $digits; }
                                        @endphp
                                        <div class="flex items-center gap-1.5">
                                            <span>{{ $p->phone }}</span>
                                            <a href="https://wa.me/{{ $digits }}?text={{ urlencode('Halo ' . $p->name . ', kami dari Tim PMB UBSI Karawang ingin mengonfirmasi pendaftaran Anda. Apakah ada kendala atau berkas yang perlu dibantu?') }}" 
                                               target="_blank" rel="noopener" 
                                               class="p-1 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-100 border border-emerald-200 transition" 
                                               title="Chat WhatsApp">
                                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ $p->school ? $p->school->name : '-' }}
                                    @if($p->school?->district)
                                        <span class="text-[10px] text-slate-400 block font-normal">Kec. {{ $p->school->district->name }}</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-brand-600 font-bold text-xs">{{ $p->interest_program ?? '-' }}</div>
                                    <div class="flex flex-wrap items-center gap-1 mt-1">
                                        @if($p->study_time_label)
                                            @php $tb = $p->study_time_badge; @endphp
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-semibold border {{ $tb['badge_class'] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}" title="Waktu Kuliah">
                                                @if(($tb['icon'] ?? '') === 'sun')
                                                    <i data-lucide="sun" class="w-2.5 h-2.5 text-amber-600"></i>
                                                @elseif(($tb['icon'] ?? '') === 'moon')
                                                    <i data-lucide="moon" class="w-2.5 h-2.5 text-indigo-600"></i>
                                                @elseif(($tb['icon'] ?? '') === 'briefcase')
                                                    <i data-lucide="briefcase" class="w-2.5 h-2.5 text-emerald-600"></i>
                                                @else
                                                    <i data-lucide="clock" class="w-2.5 h-2.5 text-slate-400"></i>
                                                @endif
                                                <span>{{ $tb['short_label'] ?? $p->study_time_label }}</span>
                                            </span>
                                        @endif

                                        @php $sb = $p->scholarship_badge; @endphp
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold border {{ $sb['badge_class'] }}" title="Kategori Program / Beasiswa">
                                            @if($sb['is_scholarship'])
                                                <i data-lucide="award" class="w-2.5 h-2.5 text-emerald-600"></i>
                                            @else
                                                <i data-lucide="user-check" class="w-2.5 h-2.5 text-slate-500"></i>
                                            @endif
                                            <span>{{ $sb['label'] }}</span>
                                        </span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600">
                                    {{ $p->marketingUser ? $p->marketingUser->name : '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <!-- Status Dropdown (Tahap 1 - 5) -->
                                    <form method="POST" action="{{ route('prospects.update-status', $p->id) }}">
                                        @csrf
                                        @method('PUT')
                                        <select name="status" onchange="this.form.submit()"
                                            class="px-2.5 py-1.5 text-[11px] font-bold rounded-xl border focus:outline-none focus:ring-2
                                            {{ $p->status === 'berkas_lengkap' ? 'bg-emerald-100 text-emerald-800 border-emerald-300 ring-1 ring-emerald-400' : '' }}
                                            {{ $p->status === 'bayar_daftar_ulang' ? 'bg-amber-100 text-amber-800 border-amber-300' : '' }}
                                            {{ $p->status === 'ujian_saringan' ? 'bg-purple-100 text-purple-800 border-purple-300' : '' }}
                                            {{ $p->status === 'bayar_registrasi' ? 'bg-cyan-100 text-cyan-800 border-cyan-300' : '' }}
                                            {{ $p->status === 'daftar_akun' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}
                                            {{ $p->status === 'tidak_lanjut' ? 'bg-red-100 text-red-800 border-red-300' : '' }}
                                            {{ $p->status === 'mahasiswa' ? 'bg-slate-900 text-amber-300 border-slate-900' : '' }}">
                                            <option value="daftar_akun" {{ $p->status == 'daftar_akun' ? 'selected' : '' }}>1. Sudah Daftar Akun</option>
                                            <option value="bayar_registrasi" {{ $p->status == 'bayar_registrasi' ? 'selected' : '' }}>2. Sudah Bayar Registrasi</option>
                                            <option value="ujian_saringan" {{ $p->status == 'ujian_saringan' ? 'selected' : '' }}>3. Sudah Ujian Saringan</option>
                                            <option value="bayar_daftar_ulang" {{ $p->status == 'bayar_daftar_ulang' ? 'selected' : '' }}>4. Sudah Bayar Daftar Ulang</option>
                                            <option value="berkas_lengkap" {{ $p->status == 'berkas_lengkap' ? 'selected' : '' }}>5. Sudah Melengkapi Berkas</option>
                                            <option value="tidak_lanjut" {{ $p->status == 'tidak_lanjut' ? 'selected' : '' }}>Tidak Lanjut / Batal</option>
                                            <option value="mahasiswa" {{ $p->status == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa Baru (Tervalidasi)</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <!-- Tombol Validasi Mahasiswa Baru (Khusus Tahap 5) -->
                                        @if($p->status === 'berkas_lengkap')
                                            <button type="button" @click="openValidateModal({{ json_encode($p) }})"
                                                title="Validasi Menjadi Mahasiswa Baru Resmi"
                                                class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-[11px] rounded-xl shadow-md shadow-emerald-600/30 flex items-center space-x-1 transition hover-lift">
                                                <i data-lucide="badge-check" class="w-3.5 h-3.5 text-white"></i>
                                                <span>Validasi Maba</span>
                                            </button>
                                        @elseif($p->status === 'mahasiswa')
                                            <a href="{{ route('new-students.index', ['search' => $p->nim ?: $p->name]) }}"
                                                title="Lihat di Menu Mahasiswa Baru"
                                                class="px-2 py-1 bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 font-extrabold text-[10px] rounded-lg transition inline-flex items-center gap-1">
                                                <i data-lucide="graduation-cap" class="w-3 h-3 text-amber-700"></i>
                                                <span>Di Menu Maba</span>
                                            </a>
                                        @endif

                                        <!-- Hapus Prospek -->
                                        <form method="POST" action="{{ route('prospects.destroy', $p->id) }}" onsubmit="return confirm('Hapus prospek {{ $p->name }}?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Data" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                        <i data-lucide="users" class="w-6 h-6"></i>
                                    </div>
                                    <h3 class="text-sm font-bold text-slate-700">Tidak Ada Data Prospek</h3>
                                    <p class="text-xs text-slate-400 mt-1">
                                        {{ request()->filled('status') ? 'Tidak ada data untuk filter status yang dipilih.' : 'Semua calon mahasiswa aktif telah tervalidasi atau belum ada prospek baru.' }}
                                    </p>
                                    <div class="mt-3 flex items-center justify-center gap-2">
                                        <button type="button" @click="showAddModal = true" class="px-3 py-1.5 bg-indigo-600 text-white rounded-xl text-xs font-bold hover:bg-indigo-700">
                                            + Tambah Prospek Baru
                                        </button>
                                        <a href="{{ route('new-students.index') }}" class="px-3 py-1.5 bg-slate-900 text-amber-300 rounded-xl text-xs font-bold hover:bg-slate-800">
                                            Buka Menu Mahasiswa Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($prospects->hasPages())
                <div class="p-4 bg-slate-50 border-t border-slate-100">
                    {{ $prospects->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL VALIDASI MAHASISWA BARU (Untuk Tahap 5) -->
        <div x-show="validateModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-validate-title">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <div x-show="validateModalOpen" @click="validateModalOpen = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

                <div x-show="validateModalOpen" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border border-slate-200 p-6 z-10">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                                <i data-lucide="badge-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 id="modal-validate-title" class="text-sm font-extrabold text-slate-900">Validasi Mahasiswa Baru Resmi</h3>
                                <p class="text-[11px] text-slate-500">Tahap 5: Berkas Lengkap &rarr; Pindah ke Menu Mahasiswa Baru</p>
                            </div>
                        </div>
                        <button type="button" @click="validateModalOpen = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <template x-if="validatingProspect">
                        <form :action="'{{ url('/prospects') }}/' + validatingProspect.id + '/validate'" method="POST" class="mt-4 space-y-4">
                            @csrf
                            
                            <!-- Student Quick Summary -->
                            <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 space-y-1.5 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Nama Calon Siswa:</span>
                                    <strong class="text-slate-900 text-sm" x-text="validatingProspect.name"></strong>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">Sekolah Asal:</span>
                                    <span class="font-semibold text-slate-800" x-text="validatingProspect.school ? validatingProspect.school.name : '-'"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-slate-500">No. WhatsApp:</span>
                                    <span class="font-mono font-semibold text-emerald-700" x-text="validatingProspect.phone || '-'"></span>
                                </div>
                            </div>

                            <!-- NIM Input -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">
                                    Nomor Induk Mahasiswa (NIM) Resmi <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="nim" x-model="prospectNim" required placeholder="Contoh: 12240123"
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-mono font-bold bg-slate-50/50">
                                <span class="text-[10px] text-slate-400 block mt-1">NIM resmi yang diterbitkan untuk mahasiswa baru ini.</span>
                            </div>

                            <!-- Program Studi & Waktu Kuliah Confirm -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Program Studi Diterima <span class="text-red-500">*</span>
                                    </label>
                                    <select name="interest_program" x-model="prospectProgram" required
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-slate-50/50">
                                        <option value="">Pilih Program Studi</option>
                                        @foreach($studyPrograms as $sp)
                                            <option value="{{ $sp->name }}">{{ $sp->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">
                                        Waktu Kuliah / Kelas <span class="text-red-500">*</span>
                                    </label>
                                    <select name="study_time" x-model="prospectStudyTime" required
                                        class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-slate-50/50">
                                        <option value="">Pilih Waktu Kuliah</option>
                                        <optgroup label="Waktu Kuliah Reguler">
                                            <option value="reguler_pagi">Reguler (Pagi - Siang)</option>
                                            <option value="reguler_malam">Reguler (Sore - Malam)</option>
                                        </optgroup>
                                        <optgroup label="Waktu Kuliah Karyawan">
                                            <option value="karyawan">Waktu Kuliah Karyawan (Jumat - Sabtu)</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>

                            <!-- Kategori Program & Beasiswa Konfirmasi -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                    <span>Kategori Mahasiswa &amp; Beasiswa <span class="text-red-500">*</span></span>
                                    <span class="text-[10px] text-slate-400 font-normal">Konfirmasi status beasiswa resmi</span>
                                </label>

                                <div class="grid grid-cols-2 gap-2 mb-2">
                                    <label class="flex items-center justify-center gap-2 p-2 rounded-xl border text-xs font-bold cursor-pointer transition"
                                        :class="prospectIsScholarship === 'beasiswa' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-slate-200 bg-slate-50 text-slate-600'">
                                        <input type="radio" value="beasiswa" x-model="prospectIsScholarship"
                                            @change="prospectScholarshipType = 'Beasiswa KIP-Kuliah'" class="sr-only">
                                        <i data-lucide="award" class="w-3.5 h-3.5" :class="prospectIsScholarship === 'beasiswa' ? 'text-emerald-600' : 'text-slate-400'"></i>
                                        <span>Jalur Beasiswa</span>
                                    </label>

                                    <label class="flex items-center justify-center gap-2 p-2 rounded-xl border text-xs font-bold cursor-pointer transition"
                                        :class="prospectIsScholarship === 'non_beasiswa' ? 'border-indigo-500 bg-indigo-50 text-indigo-900 ring-1 ring-indigo-500' : 'border-slate-200 bg-slate-50 text-slate-600'">
                                        <input type="radio" value="non_beasiswa" x-model="prospectIsScholarship"
                                            @change="prospectScholarshipType = 'Non Beasiswa (Reguler Mandiri)'" class="sr-only">
                                        <i data-lucide="user-check" class="w-3.5 h-3.5" :class="prospectIsScholarship === 'non_beasiswa' ? 'text-indigo-600' : 'text-slate-400'"></i>
                                        <span>Non Beasiswa (Mandiri)</span>
                                    </label>
                                </div>

                                <div x-show="prospectIsScholarship === 'beasiswa'" class="p-2.5 bg-emerald-50/50 border border-emerald-200 rounded-xl space-y-1">
                                    <label class="block text-[11px] font-bold text-emerald-900">Pilih Jenis / Kategori Beasiswa:</label>
                                    <select x-model="prospectScholarshipType" class="w-full px-3 py-2 text-xs rounded-xl border border-emerald-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500">
                                        <option value="Beasiswa KIP-Kuliah">🎓 Beasiswa KIP-Kuliah</option>
                                        <option value="Beasiswa Jalur Sekolah">🎓 Beasiswa Jalur Sekolah</option>
                                        <option value="Beasiswa Prestasi">🎓 Beasiswa Prestasi</option>
                                        <option value="Beasiswa Talenta">🎓 Beasiswa Talenta</option>
                                        <option value="Beasiswa UBSI Peduli">🎓 Beasiswa UBSI Peduli</option>
                                    </select>
                                </div>

                                <div x-show="prospectIsScholarship === 'non_beasiswa'" class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600 flex items-center gap-2">
                                    <i data-lucide="info" class="w-4 h-4 text-slate-400 shrink-0"></i>
                                    <span>Mahasiswa terdaftar sebagai <strong>Non Beasiswa (Reguler Mandiri)</strong> dengan pembiayaan mandiri.</span>
                                </div>

                                <input type="hidden" name="scholarship_type" :value="prospectScholarshipType">
                            </div>

                            <!-- Catatan Validasi -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Validasi Berkas (Opsional)</label>
                                <textarea name="notes" x-model="prospectNotes" rows="2" placeholder="Catatan kelengkapan berkas, nomor ijazah, atau beasiswa..."
                                    class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500"></textarea>
                            </div>

                            <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-[11px] text-emerald-900 flex items-start space-x-2">
                                <i data-lucide="info" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5"></i>
                                <span>Setelah divalidasi, calon mahasiswa ini resmi menjadi <strong>Mahasiswa Baru</strong> dan datanya otomatis dipindahkan dari antrean prospek ke <strong>Menu Mahasiswa Baru</strong>.</span>
                            </div>

                            <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                                <button type="button" @click="validateModalOpen = false"
                                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-emerald-600/30 flex items-center space-x-2 transition">
                                    <i data-lucide="check-check" class="w-4 h-4 text-white"></i>
                                    <span>Validasi &amp; Pindahkan ke Mahasiswa Baru</span>
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>
        </div>

        <!-- MODAL TAMBAH PROSPEK -->
        <div x-show="showAddModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true" aria-labelledby="modal-add-title">
            <div class="flex items-center justify-center min-h-screen p-4 text-center">
                <div x-show="showAddModal" @click="showAddModal = false" class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>

                <div x-show="showAddModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full border border-slate-200 p-6 z-10">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 text-indigo-700 flex items-center justify-center">
                                <i data-lucide="user-plus" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 id="modal-add-title" class="text-sm font-extrabold text-slate-900">Tambah Prospek Calon Mahasiswa</h3>
                                <p class="text-[11px] text-slate-500">Input data calon siswa baru ke dalam pipeline PMB</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('prospects.store') }}" method="POST" class="mt-4 space-y-3">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: Ahmad Fauzi"
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">No. WhatsApp / HP <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" required placeholder="Contoh: 08123456789"
                                    class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Kelamin</label>
                                <select name="gender" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                                    <option value="">Pilih Gender</option>
                                    <option value="L">Laki-laki (L)</option>
                                    <option value="P">Perempuan (P)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Sekolah Asal <span class="text-red-500">*</span></label>
                            <select name="school_id" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                                <option value="">Pilih Sekolah Asal</option>
                                @foreach($schools as $sc)
                                    <option value="{{ $sc->id }}">{{ $sc->name }} ({{ $sc->district?->name ?? 'Karawang' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Tahun Lulus <span class="text-red-500">*</span></label>
                                <input type="number" name="graduation_year" value="{{ date('Y') }}" required
                                    class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Minat Program Studi</label>
                                <select name="interest_program" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                                    <option value="">Pilih Prodi</option>
                                    @foreach($studyPrograms as $sp)
                                        <option value="{{ $sp->name }}">{{ $sp->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Pilihan Waktu Kuliah -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilihan Waktu Kuliah (Shift)</label>
                            <select name="study_time" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                                <option value="">Belum Memilih</option>
                                <optgroup label="Waktu Kuliah Reguler">
                                    <option value="reguler_pagi">Reguler (Pagi - Siang)</option>
                                    <option value="reguler_malam">Reguler (Sore - Malam)</option>
                                </optgroup>
                                <optgroup label="Waktu Kuliah Karyawan">
                                    <option value="karyawan">Waktu Kuliah Karyawan (Jumat - Sabtu)</option>
                                </optgroup>
                            </select>
                        </div>

                        <!-- Kategori Program & Beasiswa -->
                        <div x-data="{ addIsScholarship: 'beasiswa', addScholarshipType: 'Beasiswa KIP-Kuliah' }">
                            <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                                <span>Kategori Program &amp; Beasiswa <span class="text-red-500">*</span></span>
                                <span class="text-[10px] text-slate-400">Pilih Jalur Beasiswa atau Mandiri</span>
                            </label>

                            <div class="grid grid-cols-2 gap-2 mb-2">
                                <label class="flex items-center justify-center gap-2 p-2 rounded-xl border text-xs font-bold cursor-pointer transition"
                                    :class="addIsScholarship === 'beasiswa' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 ring-1 ring-emerald-500' : 'border-slate-200 bg-slate-50 text-slate-600'">
                                    <input type="radio" value="beasiswa" x-model="addIsScholarship"
                                        @change="addScholarshipType = 'Beasiswa KIP-Kuliah'" class="sr-only">
                                    <i data-lucide="award" class="w-3.5 h-3.5" :class="addIsScholarship === 'beasiswa' ? 'text-emerald-600' : 'text-slate-400'"></i>
                                    <span>Jalur Beasiswa</span>
                                </label>

                                <label class="flex items-center justify-center gap-2 p-2 rounded-xl border text-xs font-bold cursor-pointer transition"
                                    :class="addIsScholarship === 'non_beasiswa' ? 'border-indigo-500 bg-indigo-50 text-indigo-900 ring-1 ring-indigo-500' : 'border-slate-200 bg-slate-50 text-slate-600'">
                                    <input type="radio" value="non_beasiswa" x-model="addIsScholarship"
                                        @change="addScholarshipType = 'Non Beasiswa (Reguler Mandiri)'" class="sr-only">
                                    <i data-lucide="user-check" class="w-3.5 h-3.5" :class="addIsScholarship === 'non_beasiswa' ? 'text-indigo-600' : 'text-slate-400'"></i>
                                    <span>Non Beasiswa</span>
                                </label>
                            </div>

                            <div x-show="addIsScholarship === 'beasiswa'" class="p-2.5 bg-emerald-50/50 border border-emerald-200 rounded-xl space-y-1">
                                <label class="block text-[11px] font-bold text-emerald-900">Jenis / Jalur Beasiswa:</label>
                                <select x-model="addScholarshipType" class="w-full px-3 py-2 text-xs rounded-xl border border-emerald-300 bg-white font-medium focus:ring-2 focus:ring-emerald-500">
                                    <option value="Beasiswa KIP-Kuliah">🎓 Beasiswa KIP-Kuliah</option>
                                    <option value="Beasiswa Jalur Sekolah">🎓 Beasiswa Jalur Sekolah</option>
                                    <option value="Beasiswa Prestasi">🎓 Beasiswa Prestasi</option>
                                    <option value="Beasiswa Talenta">🎓 Beasiswa Talenta</option>
                                    <option value="Beasiswa UBSI Peduli">🎓 Beasiswa UBSI Peduli</option>
                                </select>
                            </div>

                            <div x-show="addIsScholarship === 'non_beasiswa'" class="p-2 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-600">
                                Calon mahasiswa terdaftar sebagai <strong>Non Beasiswa (Reguler Mandiri)</strong>.
                            </div>

                            <input type="hidden" name="scholarship_type" :value="addScholarshipType">
                        </div>

                        <!-- 5 Kategori Status Awal -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Status Tahapan Pendaftaran <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                                <option value="daftar_akun" selected>1. Sudah Daftar Akun</option>
                                <option value="bayar_registrasi">2. Sudah Bayar Registrasi</option>
                                <option value="ujian_saringan">3. Sudah Ujian Saringan</option>
                                <option value="bayar_daftar_ulang">4. Sudah Bayar Daftar Ulang</option>
                                <option value="berkas_lengkap">5. Sudah Melengkapi Berkas dan Menjadi Mahasiswa Baru</option>
                                <option value="tidak_lanjut">Tidak Lanjut / Batal</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">PIC Marketing</label>
                                <select name="marketing_user_id" class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                                    <option value="">Pilih PIC</option>
                                    @foreach($marketingUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Sumber Prospek</label>
                                <input type="text" name="source" placeholder="Contoh: Kunjungan Sekolah"
                                    class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan</label>
                            <textarea name="notes" rows="2" placeholder="Catatan prospek..."
                                class="w-full px-3.5 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showAddModal = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow transition">
                                Simpan Prospek
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
