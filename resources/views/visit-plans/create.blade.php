<x-app-layout title="Buat Agenda Rencana Kunjungan Baru">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Buat Agenda Rencana Kunjungan Baru</h1>
            <p class="text-xs text-slate-500 mt-0.5">Jadwalkan agenda kunjungan tim marketing ke satu atau beberapa sekolah SLTA</p>
        </div>
        <a href="{{ route('visit-plans.index') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition flex items-center space-x-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke List Agenda</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
            <p class="font-bold mb-1">Perhatian:</p>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="max-w-4xl" x-data="{
        districts: {{ json_encode($districts) }},
        allSchools: {{ json_encode($schools) }},
        selectedDistrictId: '{{ old('district_id', '') }}',
        selectedSchoolIds: {{ json_encode(array_map('intval', old('school_ids', []))) }},
        searchQuery: '',
        errorMessage: '',
        selectedPriority: '{{ old('priority', 'B') }}',
        priorityAutoFilled: false,

        get filteredSchools() {
            let list = this.allSchools;
            if (this.selectedDistrictId) {
                list = list.filter(s => s.district_id == this.selectedDistrictId);
            }
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                list = list.filter(s => {
                    const name = (s.name || '').toLowerCase();
                    const npsn = (s.npsn || '').toLowerCase();
                    const districtName = (s.district ? s.district.name : '').toLowerCase();
                    return name.includes(q) || npsn.includes(q) || districtName.includes(q);
                });
            }
            return list;
        },

        get selectedSchoolsList() {
            return this.allSchools.filter(s => this.selectedSchoolIds.includes(parseInt(s.id)));
        },

        get areAllFilteredSelected() {
            if (this.filteredSchools.length === 0) return false;
            return this.filteredSchools.every(s => this.selectedSchoolIds.includes(parseInt(s.id)));
        },

        // Auto-detect priority among selected schools (A > B > C)
        autoFillPriority() {
            if (this.selectedSchoolIds.length === 0) {
                this.priorityAutoFilled = false;
                return;
            }
            const selected = this.allSchools.filter(s => this.selectedSchoolIds.includes(parseInt(s.id)));
            const levels = selected.map(s => s.priority_level || 'C');
            if (levels.includes('A')) {
                this.selectedPriority = 'A';
            } else if (levels.includes('B')) {
                this.selectedPriority = 'B';
            } else {
                this.selectedPriority = 'C';
            }
            this.priorityAutoFilled = (this.selectedSchoolIds.length > 1);
        },

        toggleSchool(id) {
            id = parseInt(id);
            const index = this.selectedSchoolIds.indexOf(id);
            if (index > -1) {
                this.selectedSchoolIds.splice(index, 1);
            } else {
                this.selectedSchoolIds.push(id);
            }
            this.errorMessage = '';
            this.autoFillPriority();
        },

        isSchoolSelected(id) {
            return this.selectedSchoolIds.includes(parseInt(id));
        },

        selectAllFiltered() {
            const currentIds = this.filteredSchools.map(s => parseInt(s.id));
            currentIds.forEach(id => {
                if (!this.selectedSchoolIds.includes(id)) {
                    this.selectedSchoolIds.push(id);
                }
            });
            this.errorMessage = '';
            this.autoFillPriority();
        },

        deselectAllFiltered() {
            const currentIds = this.filteredSchools.map(s => parseInt(s.id));
            this.selectedSchoolIds = this.selectedSchoolIds.filter(id => !currentIds.includes(id));
            this.autoFillPriority();
        },

        clearAllSelections() {
            this.selectedSchoolIds = [];
            this.priorityAutoFilled = false;
        },

        removeSchool(id) {
            id = parseInt(id);
            this.selectedSchoolIds = this.selectedSchoolIds.filter(item => item !== id);
            this.autoFillPriority();
        },

        getDistrictName(districtId) {
            if (!districtId) return 'Semua Kecamatan';
            const d = this.districts.find(item => item.id == districtId);
            return d ? d.name : '';
        },

        handleSubmit(e) {
            if (this.selectedSchoolIds.length === 0) {
                e.preventDefault();
                this.errorMessage = 'Pilih minimal satu sekolah tujuan untuk diagendakan.';
                const el = document.getElementById('school_selection_card');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return false;
            }
            return true;
        }
    }">
        <form method="POST" action="{{ route('visit-plans.store') }}" @submit="handleSubmit($event)" class="space-y-6">
            @csrf

            <!-- Form Card 1: Pengaturan Wilayah & Pemilihan Sekolah (Bisa Lebih Dari 1) -->
            <div id="school_selection_card" class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-brand-50 text-brand-600 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Pemilihan Sekolah Tujuan Kunjungan</h3>
                            <p class="text-xs text-slate-500">Pilih kecamatan untuk memfilter dan tentukan satu atau beberapa sekolah yang ingin dikunjungi</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition"
                            :class="selectedSchoolIds.length > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                            <span x-text="selectedSchoolIds.length + ' Sekolah Dipilih'"></span>
                        </span>
                    </div>
                </div>

                <!-- Hidden inputs to submit selected school IDs -->
                <template x-for="id in selectedSchoolIds" :key="id">
                    <input type="hidden" name="school_ids[]" :value="id">
                </template>

                @error('school_ids')
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700 flex items-center gap-2">
                        <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror

                <div x-show="errorMessage" x-cloak class="p-3 bg-rose-50 border border-rose-200 rounded-xl text-xs font-bold text-rose-700 flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 shrink-0"></i>
                    <span x-text="errorMessage"></span>
                </div>

                <!-- District Filter Selector & Quick Search -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs pt-1">
                    <div class="sm:col-span-2">
                        <label for="district_filter" class="block font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Filter Berdasarkan Kecamatan <span class="text-brand-600 font-semibold">*</span></span>
                            <span class="text-[11px] text-slate-400 font-normal">Sinkronisasi otomatis ke daftar sekolah</span>
                        </label>
                        <select id="district_filter" x-model="selectedDistrictId" @change="searchQuery = ''"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-900 bg-amber-50/30 focus:border-brand-500 focus:ring-brand-500">
                            <option value="">-- Tampilkan Semua Kecamatan ({{ $schools->count() }} Sekolah Tersedia) --</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}">
                                    Kecamatan {{ $d->name }} ({{ $d->schools_count }} Sekolah)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Cari Sekolah
                        </label>
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Ketik nama sekolah..."
                                class="w-full pl-9 pr-8 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:border-brand-500">
                            <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-3 pointer-events-none"></i>
                            <button type="button" x-show="searchQuery" @click="searchQuery = ''" class="absolute right-2.5 top-2.5 text-slate-400 hover:text-slate-600">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Bar & School Count -->
                <div class="pt-2 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                    <div class="text-slate-500 flex items-center gap-1.5">
                        <span class="font-semibold" x-text="selectedDistrictId ? 'Menampilkan sekolah di Kec. ' + getDistrictName(selectedDistrictId) + ':' : 'Menampilkan semua sekolah:'"></span>
                        <span class="font-bold text-brand-700" x-text="filteredSchools.length + ' sekolah'"></span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                            x-show="!areAllFilteredSelected"
                            @click="selectAllFiltered()" 
                            class="px-3 py-1.5 bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 rounded-lg font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Pilih Semua yang Ditampilkan</span>
                        </button>
                        <button type="button" 
                            x-show="areAllFilteredSelected && filteredSchools.length > 0"
                            @click="deselectAllFiltered()" 
                            class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-200 rounded-lg font-bold text-xs transition flex items-center gap-1.5 shadow-2xs">
                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Batal Pilih Semua</span>
                        </button>
                        <button type="button" 
                            @click="clearAllSelections()" 
                            x-show="selectedSchoolIds.length > 0 && !areAllFilteredSelected" 
                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg font-semibold text-xs transition">
                            Reset Pilihan
                        </button>
                    </div>
                </div>

                <!-- Selectable Schools Container (Grid / List of Cards) -->
                <div class="border border-slate-200 rounded-2xl bg-slate-50/50 p-2.5">
                    <div class="max-h-72 overflow-y-auto space-y-2 pr-1">
                        <template x-for="s in filteredSchools" :key="s.id">
                            <div @click="toggleSchool(s.id)"
                                class="p-3 rounded-xl border transition cursor-pointer flex items-center justify-between gap-3 select-none"
                                :class="isSchoolSelected(s.id) 
                                    ? 'bg-white border-brand-500 ring-2 ring-brand-500/15 shadow-sm' 
                                    : 'bg-white/80 border-slate-200/80 hover:border-slate-300 hover:bg-white'">
                                
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-5 h-5 rounded-md border flex items-center justify-center shrink-0 transition"
                                        :class="isSchoolSelected(s.id) ? 'bg-brand-600 border-brand-600 text-white' : 'border-slate-300 bg-white'">
                                        <svg x-show="isSchoolSelected(s.id)" class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="font-bold text-slate-900 text-xs truncate" x-text="s.name"></p>
                                            <span x-show="s.npsn" class="text-[10px] text-slate-400 font-mono" x-text="'NPSN ' + s.npsn"></span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                                            <span x-text="'Kec. ' + (s.district ? s.district.name : '-')"></span>
                                            <span x-show="s.school_type" class="text-slate-300">&bull;</span>
                                            <span x-show="s.school_type" x-text="s.school_type ? s.school_type.name : ''"></span>
                                            <span x-show="s.status" class="text-slate-300">&bull;</span>
                                            <span x-show="s.status" class="uppercase text-[10px]" x-text="s.status"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="shrink-0 flex items-center gap-1.5">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800 border border-emerald-200': (s.priority_level || 'C') === 'A',
                                            'bg-amber-100 text-amber-800 border border-amber-200': (s.priority_level || 'C') === 'B',
                                            'bg-slate-100 text-slate-600 border border-slate-200': (s.priority_level || 'C') === 'C'
                                        }"
                                        x-text="'Prio ' + (s.priority_level || 'C')">
                                    </span>
                                </div>
                        </template>

                        <div x-show="filteredSchools.length === 0" class="py-8 text-center text-xs text-slate-400 space-y-2">
                            <i data-lucide="search-x" class="w-8 h-8 mx-auto text-slate-300"></i>
                            <p class="font-semibold text-slate-600">Tidak ada sekolah yang ditemukan.</p>
                            <p class="text-[11px]">Coba ganti filter kecamatan atau ubah kata kunci pencarian.</p>
                        </div>
                    </div>
                </div>

                <!-- Selected Schools Removable Tags Tray -->
                <div x-show="selectedSchoolIds.length > 0" class="pt-2 border-t border-slate-100 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700">Daftar Sekolah yang Dipilih (<span x-text="selectedSchoolIds.length"></span>):</span>
                        <button type="button" @click="clearAllSelections()" class="text-rose-600 hover:text-rose-700 font-semibold text-[11px]">
                            Hapus Semua Pilihan
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1.5 max-h-28 overflow-y-auto">
                        <template x-for="sch in selectedSchoolsList" :key="sch.id">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white border border-brand-200 text-brand-900 rounded-lg text-xs font-semibold shadow-xs">
                                <span class="w-1.5 h-1.5 rounded-full bg-brand-500"></span>
                                <span x-text="sch.name + ' (' + (sch.district ? sch.district.name : '') + ')'"></span>
                                <button type="button" @click="removeSchool(sch.id)" class="text-slate-400 hover:text-rose-600 ml-1 font-bold text-sm leading-none">&times;</button>
                            </span>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Form Card 2: Pengaturan Waktu, PIC, dan Detail Kegiatan -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-amber-500"></i>
                    <span>Jadwal &amp; Penanggung Jawab Agenda</span>
                </h3>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="user_id" class="block font-bold text-slate-700 mb-1">Staf Marketing PIC <span class="text-red-500">*</span></label>
                            <select id="user_id" name="user_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                                @foreach($marketingUsers as $u)
                                    <option value="{{ $u->id }}" {{ old('user_id', auth()->id()) == $u->id ? 'selected' : '' }}>
                                        {{ $u->name }} ({{ $u->role->name ?? 'Marketing' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="planned_date" class="block font-bold text-slate-700 mb-1">Tanggal Rencana Kunjungan <span class="text-red-500">*</span></label>
                            <input type="date" id="planned_date" name="planned_date" value="{{ old('planned_date', date('Y-m-d')) }}" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600 focus:border-brand-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="priority" class="block font-bold text-slate-700 mb-1 flex items-center gap-2">
                                <span>Level Prioritas Agenda <span class="text-red-500">*</span></span>
                                <span x-show="priorityAutoFilled" x-cloak
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold border border-amber-200">
                                    <i data-lucide="zap" class="w-3 h-3"></i> Otomatis dari Prioritas Sekolah
                                </span>
                            </label>
                            <select id="priority" name="priority" required
                                x-model="selectedPriority"
                                :class="priorityAutoFilled ? 'bg-amber-50 border-amber-400 text-amber-900 font-extrabold' : ''"
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold focus:border-brand-500 transition">
                                <option value="A">Prioritas A (Tinggi / Top School)</option>
                                <option value="B">Prioritas B (Sedang / Potensial)</option>
                                <option value="C">Prioritas C (Standar)</option>
                            </select>
                            <p x-show="priorityAutoFilled" x-cloak class="mt-1 text-[11px] text-amber-700 font-semibold">
                                Prioritas terisi otomatis berdasarkan data prioritas sekolah yang dipilih (tiap agenda sekolah akan tersimpan dengan prioritas aslinya).
                            </p>
                        </div>
                        <div>
                            <label for="purpose" class="block font-bold text-slate-700 mb-1">Tujuan / Agenda Kegiatan <span class="text-red-500">*</span></label>
                            <input type="text" id="purpose" name="purpose" value="{{ old('purpose') }}" placeholder="Misal: Sosialisasi PMB / Meeting Guru BK / Antar Brosur" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block font-bold text-slate-700 mb-1">Catatan Tambahan &amp; Bahan Materi (Opsional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Misal: Bertemu Pak Dani (Guru BK), bawa 100 brosur dan formulir pendaftaran..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-brand-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('visit-plans.index') }}" class="px-5 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center space-x-2">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    <span x-text="selectedSchoolIds.length > 1 ? 'Jadwalkan ' + selectedSchoolIds.length + ' Agenda Kunjungan' : 'Simpan Agenda Kunjungan'"></span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
