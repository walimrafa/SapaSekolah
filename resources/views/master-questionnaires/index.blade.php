<x-app-layout title="Master Edit Kuisioner Siswa - SAPA Sekolah">
    <div x-data="questionnaireMaster()" class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Top Header & Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1">
                <nav class="flex items-center space-x-2 text-xs text-slate-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-brand-600 transition">Dashboard</a>
                    <span>&bull;</span>
                    <a href="{{ route('student-questionnaires.index') }}" class="hover:text-brand-600 transition">Kuisioner</a>
                    <span>&bull;</span>
                    <span class="text-slate-800 font-bold">Master Edit Kuisioner</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="p-2 rounded-2xl bg-indigo-500 text-white shadow-md">
                        <i data-lucide="clipboard-edit" class="w-6 h-6"></i>
                    </span>
                    <span>Master &amp; Edit Kuisioner Siswa</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-500 max-w-3xl">
                    Kelola dan sesuaikan daftar bagian (section) serta butir pertanyaan pada formulir kuisioner sosialisasi siswa. Perubahan akan langsung tampil pada formulir kuisioner publik secara real-time.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('student-questionnaire.create') }}" target="_blank"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-white text-slate-700 hover:bg-slate-50 border border-slate-200/90 shadow-sm transition"
                    title="Buka pratinjau tampilan publik formulir kuisioner siswa">
                    <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                    <span>Pratinjau Formulir</span>
                </a>

                <button type="button" @click="openResetModal = true"
                    class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-white text-rose-600 hover:bg-rose-50 border border-rose-200 shadow-sm transition"
                    title="Kembalikan semua pertanyaan ke template standar bawaan UBSI">
                    <i data-lucide="rotate-ccw" class="w-4 h-4 text-rose-500"></i>
                    <span>Reset ke Default</span>
                </button>

                <button type="button" @click="openAddModal('')"
                    class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-black bg-brand-600 hover:bg-brand-500 text-white shadow-md shadow-brand-600/30 transition transform hover:-translate-y-0.5">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-amber-300"></i>
                    <span>+ Tambah Pertanyaan</span>
                </button>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- Total Pertanyaan -->
            <div class="p-4 bg-white rounded-2xl border border-indigo-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-indigo-700 uppercase tracking-wider">Total Pertanyaan</p>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600">
                        <i data-lucide="help-circle" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1.5">{{ number_format($stats['total_questions']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">{{ $stats['total_active'] }} butir aktif</span>
            </div>

            <!-- Total Bagian / Sections -->
            <div class="p-4 bg-white rounded-2xl border border-purple-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Bagian (Sections)</p>
                    <span class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center text-purple-600">
                        <i data-lucide="folder" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-purple-700 mt-1.5">{{ number_format($stats['total_sections']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Kategori Pertanyaan</span>
            </div>

            <!-- Status Aktif -->
            <div class="p-4 bg-white rounded-2xl border border-emerald-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Pertanyaan Aktif</p>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1.5">{{ number_format($stats['total_active']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Tampil di Formulir Publik</span>
            </div>

            <!-- Total Responden Kuisioner -->
            <div class="p-4 bg-white rounded-2xl border border-blue-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Total Responden</p>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-blue-700 mt-1.5">{{ number_format($stats['total_responses']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Data Siswa Tersimpan</span>
            </div>
        </div>

        <!-- Daftar Pertanyaan Berdasarkan Section -->
        <div class="space-y-6">
            @forelse($sections as $sectionTitle => $questions)
                <div class="bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
                    <!-- Section Header -->
                    <div class="px-5 py-4 bg-gradient-to-r from-slate-900 to-indigo-950 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center space-x-3">
                            <span class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center text-amber-300 font-black text-xs">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h2 class="text-sm sm:text-base font-extrabold text-white tracking-tight">{{ $sectionTitle }}</h2>
                                <p class="text-[11px] text-slate-300">{{ count($questions) }} butir pertanyaan dalam bagian ini</p>
                            </div>
                        </div>

                        <button type="button" @click="openAddModal('{{ addslashes($sectionTitle) }}')"
                            class="inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition self-start sm:self-auto">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Tambah Butir di Bagian Ini</span>
                        </button>
                    </div>

                    <!-- Questions Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-3 px-4 w-12 text-center">Urutan</th>
                                    <th class="py-3 px-4 w-44">Kode Kunci (Key)</th>
                                    <th class="py-3 px-4">Teks Pernyataan Kuisioner (Skala Likert 1-5)</th>
                                    <th class="py-3 px-4 w-28 text-center">Status</th>
                                    <th class="py-3 px-4 w-28 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium">
                                @foreach($questions as $question)
                                    <tr class="hover:bg-slate-50/80 transition">
                                        <td class="py-3.5 px-4 text-center font-bold text-slate-500">
                                            {{ $question->order_index }}
                                        </td>
                                        <td class="py-3.5 px-4 font-mono text-[11px]">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-800 font-semibold border border-slate-200">
                                                {{ $question->question_key }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-800 leading-relaxed">
                                            {{ $question->question_text }}
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @if($question->is_active)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            <div class="flex items-center justify-center space-x-1.5">
                                                <button type="button"
                                                    @click="openEditModal({{ json_encode($question) }})"
                                                    class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition"
                                                    title="Edit Pertanyaan">
                                                    <i data-lucide="edit" class="w-4 h-4"></i>
                                                </button>

                                                <form method="POST" action="{{ route('master-questionnaires.destroy', $question) }}"
                                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus butir pertanyaan ini?')"
                                                    class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="p-1.5 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 transition"
                                                        title="Hapus Pertanyaan">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @empty
                <div class="py-16 text-center bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
                    <div class="w-16 h-16 mx-auto rounded-2xl bg-indigo-50 text-indigo-500 flex items-center justify-center mb-4">
                        <i data-lucide="clipboard-list" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-base font-bold text-slate-800">Belum Ada Pertanyaan Kuisioner</h3>
                    <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                        Klik tombol di bawah untuk mengisi kuisioner dengan template standar bawaan UBSI atau tambahkan pertanyaan baru secara manual.
                    </p>
                    <div class="mt-4 flex items-center justify-center gap-3">
                        <form method="POST" action="{{ route('master-questionnaires.reset-defaults') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-md hover:bg-indigo-700 transition">
                                Muat Template Bawaan UBSI
                            </button>
                        </form>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- ==================== MODAL TAMBAH PERTANYAAN ==================== -->
        <div x-show="openAdd" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;">
            
            <div @click.outside="openAdd = false"
                class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="plus-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Tambah Pertanyaan Kuisioner</h3>
                            <p class="text-xs text-slate-500">Tambahkan butir baru ke kuisioner siswa</p>
                        </div>
                    </div>
                    <button type="button" @click="openAdd = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('master-questionnaires.store') }}" class="mt-4 space-y-4 text-xs">
                    @csrf

                    <!-- Bagian / Section -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Pilih Bagian (Section)</label>
                        <select name="section_title" x-model="selectedSection" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                            @foreach($existingSections as $sec)
                                <option value="{{ $sec }}">{{ $sec }}</option>
                            @endforeach
                            <option value="__NEW__">+ Buat Bagian / Kategori Baru...</option>
                        </select>
                    </div>

                    <!-- Input Nama Bagian Baru jika dipilih __NEW__ -->
                    <div x-show="selectedSection === '__NEW__'" class="space-y-1">
                        <label class="block font-bold text-indigo-700">Nama Bagian Baru</label>
                        <input type="text" name="new_section_title" placeholder="Contoh: F. Minat Program Studi"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-indigo-300 font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                    </div>

                    <!-- Teks Pertanyaan -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Teks Pernyataan Kuisioner *</label>
                        <textarea name="question_text" rows="3" required
                            placeholder="Contoh: Saya mempertimbangkan beasiswa kuliah sebagai faktor utama dalam memilih perguruan tinggi..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Siswa akan menjawab dengan pilihan skala 1 (Sangat Tidak Setuju) sampai 5 (Sangat Setuju).</p>
                    </div>

                    <!-- Kode Kunci (Opsional) & Urutan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kode Kunci (Key)</label>
                            <input type="text" name="question_key" placeholder="Otomatis jika kosong"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-[11px] focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Urutan</label>
                            <input type="number" name="order_index" min="0" placeholder="Contoh: 1"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="font-bold text-slate-700">Aktifkan butir pertanyaan ini (Tampil di formulir)</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="openAdd = false"
                            class="px-4 py-2 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-indigo-600 hover:bg-indigo-500 text-white shadow-md shadow-indigo-600/30 transition">
                            Simpan Pertanyaan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL EDIT PERTANYAAN ==================== -->
        <div x-show="openEdit" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;">
            
            <div @click.outside="openEdit = false"
                class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="edit" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Edit Pertanyaan Kuisioner</h3>
                            <p class="text-xs text-slate-500">Ubah detail atau status butir pertanyaan</p>
                        </div>
                    </div>
                    <button type="button" @click="openEdit = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'/master-kuisioner/' + editForm.id" method="POST" class="mt-4 space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <!-- Bagian / Section -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Bagian / Section *</label>
                        <input type="text" name="section_title" x-model="editForm.section_title" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                    </div>

                    <!-- Teks Pertanyaan -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Teks Pernyataan Kuisioner *</label>
                        <textarea name="question_text" x-model="editForm.question_text" rows="3" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"></textarea>
                    </div>

                    <!-- Kode Kunci & Urutan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kode Kunci (Key) *</label>
                            <input type="text" name="question_key" x-model="editForm.question_key" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-mono text-[11px] focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Nomor Urutan</label>
                            <input type="number" name="order_index" x-model="editForm.order_index" min="0"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                        </div>
                    </div>

                    <!-- Status Aktif -->
                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" :checked="editForm.is_active"
                                class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="font-bold text-slate-700">Aktifkan butir pertanyaan ini (Tampil di formulir)</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="openEdit = false"
                            class="px-4 py-2 rounded-xl font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl font-bold bg-blue-600 hover:bg-blue-500 text-white shadow-md shadow-blue-600/30 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL RESET DEFAULT ==================== -->
        <div x-show="openResetModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;">
            <div @click.outside="openResetModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Reset ke Template Bawaan?</h3>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                    Tindakan ini akan mengembalikan seluruh daftar pertanyaan dan bagian kuisioner ke 16 butir pertanyaan standar bawaan UBSI. Butir kustom yang telah dibuat sebelumnya akan dihapus.
                </p>
                <div class="mt-6 flex items-center justify-center gap-2.5">
                    <button type="button" @click="openResetModal = false"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                        Batal
                    </button>
                    <form method="POST" action="{{ route('master-questionnaires.reset-defaults') }}">
                        @csrf
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/30 transition">
                            Ya, Reset Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function questionnaireMaster() {
            return {
                openAdd: false,
                openEdit: false,
                openResetModal: false,
                selectedSection: '{{ $existingSections[0] ?? "A. Motivasi" }}',
                editForm: {
                    id: null,
                    section_title: '',
                    question_key: '',
                    question_text: '',
                    order_index: 0,
                    is_active: true,
                },
                openAddModal(sectionTitle) {
                    if (sectionTitle) {
                        this.selectedSection = sectionTitle;
                    }
                    this.openAdd = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },
                openEditModal(q) {
                    this.editForm = Object.assign({}, q);
                    this.openEdit = true;
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>

