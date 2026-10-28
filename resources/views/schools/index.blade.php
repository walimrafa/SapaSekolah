<x-app-layout title="Data Sekolah SLTA Karawang">
    <!-- Header Title & Action Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6"
        x-data="{
            showImportModal: false,
            showIntegrateModal: false,
            showDeleteAllModal: false,
            isIntegrating: false,
            integrationError: null,
            isDeletingAll: false,
            forceDeleteAll: false,
            allRegencies: {{ json_encode($availableRegencies) }},
            allProvinces: {{ json_encode($availableProvinces ?? array_values(array_unique(array_column($availableRegencies, 'province')))) }},
            selectedProvince: 'Jawa Barat',
            selectedRegency: '3215',
            districtsList: {{ json_encode($initialDistricts ?? []) }},
            selectedDistricts: {{ json_encode(array_column($initialDistricts ?? [], 'code')) }},
            districtSearch: '',
            selectedCriteria: 'ALL',
            selectedYear: '{{ date('Y') }}',
            loadingDistricts: false,
            regenciesMap: {{ json_encode(collect($availableRegencies)->keyBy('code')) }},
            get filteredRegencies() {
                return this.allRegencies.filter(r => r.province === this.selectedProvince);
            },
            onProvinceChange(prov) {
                this.selectedProvince = prov;
                const regList = this.filteredRegencies;
                if (regList && regList.length > 0) {
                    const defaultReg = (prov === 'Jawa Barat')
                        ? (regList.find(r => r.code === '3215') || regList[0])
                        : regList[0];
                    this.onRegencyChange(defaultReg.code);
                } else {
                    this.selectedRegency = '';
                    this.districtsList = [];
                    this.selectedDistricts = [];
                }
            },
            get filteredDistricts() {
                if (!this.districtSearch) return this.districtsList;
                const q = this.districtSearch.toLowerCase().trim();
                return this.districtsList.filter(d => d.name.toLowerCase().includes(q) || d.code.includes(q));
            },
            toggleAllDistricts() {
                if (this.selectedDistricts.length === this.districtsList.length) {
                    this.selectedDistricts = [];
                } else {
                    this.selectedDistricts = this.districtsList.map(d => d.code);
                }
            },
            selectAllDistricts() {
                this.selectedDistricts = this.districtsList.map(d => d.code);
            },
            deselectAllDistricts() {
                this.selectedDistricts = [];
            },
            async onRegencyChange(code) {
                this.selectedRegency = code;
                this.loadingDistricts = true;
                this.districtSearch = '';
                try {
                    const res = await fetch('{{ route('schools.districts') }}?regency_code=' + encodeURIComponent(code));
                    const data = await res.json();
                    if (data && data.status && Array.isArray(data.districts)) {
                        this.districtsList = data.districts;
                        this.selectedDistricts = this.districtsList.map(d => d.code);
                    } else {
                        this.districtsList = [];
                        this.selectedDistricts = [];
                    }
                } catch (err) {
                    console.error('Failed to load districts:', err);
                    this.districtsList = [];
                    this.selectedDistricts = [];
                } finally {
                    this.loadingDistricts = false;
                }
            },
            getSelectedRegencyName() {
                return this.regenciesMap[this.selectedRegency]?.name || 'Kabupaten / Kota';
            },
            async submitIntegration() {
                if (this.isIntegrating) return;
                this.integrationError = null;

                if (!this.selectedDistricts || this.selectedDistricts.length === 0) {
                    this.integrationError = 'Silakan pilih/centang minimal 1 kecamatan target integrasi.';
                    return;
                }

                this.isIntegrating = true;

                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('regency_code', this.selectedRegency);
                    formData.append('criteria', this.selectedCriteria || 'ALL');
                    formData.append('integration_year', this.selectedYear || '{{ date('Y') }}');
                    this.selectedDistricts.forEach(code => {
                        formData.append('district_codes[]', code);
                    });

                    const res = await fetch('{{ route('schools.integrate') }}', {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (res.ok && data.status) {
                        window.location.reload();
                    } else {
                        this.integrationError = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Gagal menyinkronkan data sekolah.');
                        this.isIntegrating = false;
                    }
                } catch (err) {
                    console.error('Integration request failed:', err);
                    this.integrationError = 'Terjadi kendala koneksi saat sinkronisasi Dapodik & Kemenag: ' + err.message;
                    this.isIntegrating = false;
                }
            }
        }">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Data Sekolah SLTA Sederajat</h1>
            <p class="text-xs text-slate-500 mt-0.5">Kelola data SMA, SMK, MA &amp; SLB di Kabupaten Karawang dan sekitarnya</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Integrasi Dapodik & Kemenag Button -->
            <button type="button" @click="showIntegrateModal = true"
                style="background-color: #4f46e5; color: #ffffff;"
                class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow-md shadow-indigo-600/30 flex items-center space-x-2 transition hover-lift cursor-pointer">
                <i data-lucide="cloud-download" class="w-4 h-4 text-white"></i>
                <span class="font-extrabold text-white">Integrasi Dapodik &amp; Kemenag</span>
            </button>

            <!-- Download Master Data Sekolah Button -->
            <a href="{{ route('schools.export', request()->query()) }}"
                title="Download seluruh data master sekolah (CSV / Excel)"
                class="px-3.5 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-1.5 transition">
                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                <span>Download Data Sekolah</span>
            </a>

            <!-- Direct Download Template Button -->
            <a href="{{ route('schools.export-template') }}"
                class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs border border-slate-300 flex items-center space-x-1.5 transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                <span>Template Excel</span>
            </a>

            <!-- Import Modal Trigger Button -->
            <button @click="showImportModal = true"
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md shadow-emerald-600/20 flex items-center space-x-2 transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Import Excel / CSV</span>
            </button>

            <!-- Add School Button -->
            <a href="{{ route('schools.create') }}"
                class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-md shadow-brand-600/20 flex items-center space-x-2 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Tambah Sekolah</span>
            </a>

            <!-- Hapus Semua Data Sekolah Button -->
            <button type="button" @click="showDeleteAllModal = true"
                style="background-color: #dc2626 !important; color: #ffffff !important;"
                title="Hapus Seluruh Data Sekolah"
                class="px-3.5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-extrabold rounded-xl text-xs shadow-md shadow-red-600/30 flex items-center space-x-1.5 transition hover-lift cursor-pointer">
                <i data-lucide="trash-2" class="w-4 h-4 text-white"></i>
                <span class="text-white font-extrabold">Hapus Semua</span>
            </button>
        </div>

        <!-- Modal Integrasi Dapodik & Kemenag -->
        <div x-show="showIntegrateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-integrate-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showIntegrateModal" @click="if(!isIntegrating) showIntegrateModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showIntegrateModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-50 border border-indigo-200 flex items-center justify-center text-indigo-600">
                                <i data-lucide="cloud-download" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Integrasi Data Dapodik &amp; Kemenag</h3>
                                <p class="text-[11px] text-slate-500">Sinkronisasi otomatis basis data sekolah SLTA aktif resmi</p>
                            </div>
                        </div>
                        <button type="button" :disabled="isIntegrating" @click="showIntegrateModal = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form @submit.prevent="submitIntegration()" class="mt-4 space-y-4">
                        @csrf

                        <!-- Sumber Basis Data Info Badge -->
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="p-2.5 bg-blue-50/70 rounded-xl border border-blue-200 flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                                <div>
                                    <span class="font-extrabold text-blue-900 block text-[11px]">Dapodik Kemendikbud</span>
                                    <span class="text-[10px] text-blue-700">Jenjang SMA &amp; SMK Aktif</span>
                                </div>
                            </div>
                            <div class="p-2.5 bg-emerald-50/70 rounded-xl border border-emerald-200 flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                                <div>
                                    <span class="font-extrabold text-emerald-950 block text-[11px]">EMIS Kemenag RI</span>
                                    <span class="text-[10px] text-emerald-800">Jenjang Madrasah Aliyah (MA)</span>
                                </div>
                            </div>
                        </div>

                        <!-- 1. Pilih Provinsi Target -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    1. Pilih Provinsi Target <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[10px] font-semibold text-slate-500" x-text="allProvinces.length + ' Provinsi Tersedia'"></span>
                            </div>
                            <div class="relative">
                                <select x-model="selectedProvince" @change="onProvinceChange($event.target.value)" required
                                    class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 font-bold text-slate-800">
                                    <template x-for="prov in allProvinces" :key="prov">
                                        <option :value="prov" :selected="prov === selectedProvince"
                                                x-text="prov === 'Kalimantan Barat' ? 'Kalimantan Barat (Pontianak)' : (prov === 'Jawa Tengah' ? 'Jawa Tengah (Purwokerto)' : prov)">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- 2. Pilih Kabupaten / Kota Target -->
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-xs font-bold text-slate-700">
                                    2. Pilih Kabupaten / Kota Target <span class="text-red-500">*</span>
                                </label>
                                <span class="text-[10px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-200"
                                      x-text="filteredRegencies.length + ' Kab/Kota di ' + selectedProvince">
                                </span>
                            </div>
                            <select name="regency_code" x-model="selectedRegency" @change="onRegencyChange($event.target.value)" required
                                class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-slate-50/50 font-semibold text-slate-800">
                                <template x-for="reg in filteredRegencies" :key="reg.code">
                                    <option :value="reg.code" :selected="reg.code === selectedRegency"
                                            x-text="reg.name + ' - Kode ' + reg.code + (reg.kec_count ? ' (' + reg.kec_count + ' Kec)' : '')">
                                    </option>
                                </template>
                            </select>
                        </div>

                        <!-- 3. Pilih Kecamatan Target dengan Checkbox & Pilih Semua -->
                        <div class="transition-all duration-200">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                                <div class="flex items-center space-x-2">
                                    <label class="block text-xs font-bold text-slate-700">
                                        3. Pilih Kecamatan Target <span class="text-xs text-indigo-600 font-semibold">(Centang Wilayah yang Diinginkan)</span>
                                    </label>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border transition"
                                          :class="selectedDistricts.length > 0 ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-red-50 text-red-600 border-red-200'"
                                          x-text="selectedDistricts.length + ' dari ' + districtsList.length + ' Kecamatan Terpilih'">
                                    </span>
                                </div>
                                
                                <div x-show="loadingDistricts" class="flex items-center space-x-1.5 text-[11px] text-indigo-600 font-semibold">
                                    <svg class="animate-spin h-3.5 w-3.5 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    <span>Memuat daftar kecamatan...</span>
                                </div>
                            </div>

                            <!-- Toolbar Checkbox: Select All, Deselect, Search -->
                            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-t-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                                <!-- Checkbox Pilih Semua -->
                                <label class="flex items-center space-x-2 cursor-pointer font-bold text-slate-800 select-none">
                                    <input type="checkbox"
                                           :checked="districtsList.length > 0 && selectedDistricts.length === districtsList.length"
                                           @change="toggleAllDistricts()"
                                           class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                    <span>Pilih Semua Kecamatan</span>
                                    <span class="text-[11px] text-slate-400 font-normal" x-text="'(Total ' + districtsList.length + ')'"></span>
                                </label>

                                <div class="flex items-center space-x-2">
                                    <!-- Search input filter kecamatan -->
                                    <div class="relative flex-1 sm:w-44">
                                        <input type="text"
                                               x-model="districtSearch"
                                               placeholder="Cari kecamatan..."
                                               class="w-full pl-7 pr-2.5 py-1 text-[11px] rounded-lg border border-slate-300 focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none text-slate-400">
                                            <i data-lucide="search" class="w-3 h-3"></i>
                                        </div>
                                    </div>

                                    <!-- Quick Clear / Select Buttons -->
                                    <button type="button" @click="selectAllDistricts()"
                                            class="px-2 py-1 text-[11px] font-bold text-indigo-700 hover:text-indigo-900 hover:bg-indigo-50 rounded-lg transition border border-indigo-200 bg-white cursor-pointer"
                                            title="Centang semua kecamatan">
                                        Pilih Semua
                                    </button>
                                    <button type="button" @click="deselectAllDistricts()"
                                            class="px-2 py-1 text-[11px] font-semibold text-slate-600 hover:text-red-600 hover:bg-red-50 rounded-lg transition border border-slate-200 bg-white cursor-pointer"
                                            title="Batalkan semua centang kecamatan">
                                        Kosongkan
                                    </button>
                                </div>
                            </div>

                            <!-- Checkbox List Grid -->
                            <div class="max-h-48 overflow-y-auto p-2 bg-slate-100/60 border-x border-b border-slate-200 rounded-b-2xl">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                                    <template x-for="d in filteredDistricts" :key="d.code">
                                        <label class="flex items-center p-2 rounded-xl border cursor-pointer transition select-none text-xs"
                                               :class="selectedDistricts.includes(d.code) ? 'bg-indigo-50 border-indigo-300 text-indigo-950 font-bold shadow-xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50'">
                                            <input type="checkbox"
                                                   :value="d.code"
                                                   x-model="selectedDistricts"
                                                   class="rounded text-indigo-600 focus:ring-indigo-500 mr-2.5 h-4 w-4">
                                            <span class="flex-1 truncate" x-text="'Kec. ' + d.name"></span>
                                            <span class="text-[10px] font-mono text-slate-400" x-text="d.code"></span>
                                        </label>
                                    </template>
                                </div>

                                <div x-show="filteredDistricts.length === 0" class="py-6 text-center text-xs text-slate-400">
                                    <span x-text="districtSearch ? 'Tidak ada kecamatan yang cocok dengan \'' + districtSearch + '\'' : 'Daftar kecamatan belum tersedia.'"></span>
                                </div>
                            </div>

                            <!-- Helper Text Below Checkboxes -->
                            <div class="mt-1.5 flex items-center justify-between text-[11px]">
                                <span x-show="districtsList.length > 0 && selectedDistricts.length === districtsList.length" class="text-indigo-600 font-semibold flex items-center gap-1">
                                    <i data-lucide="check-check" class="w-3.5 h-3.5 text-indigo-500"></i>
                                    <span>Menyinkronkan seluruh kecamatan di <strong x-text="getSelectedRegencyName()"></strong>.</span>
                                </span>
                                <span x-show="selectedDistricts.length > 0 && selectedDistricts.length < districtsList.length" class="text-emerald-700 font-semibold flex items-center gap-1">
                                    <i data-lucide="filter" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>Fokus sinkronisasi pada <strong x-text="selectedDistricts.length + ' kecamatan terpilih'"></strong>. Kecamatan yang tidak dicentang tidak akan diproses.</span>
                                </span>
                                <span x-show="selectedDistricts.length === 0" class="text-red-600 font-bold flex items-center gap-1">
                                    <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-red-500"></i>
                                    <span>Peringatan: Belum ada kecamatan yang dipilih! Silakan centang minimal 1 kecamatan.</span>
                                </span>
                            </div>
                        </div>

                        <!-- 4. Kriteria Jenjang Sekolah -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                4. Kriteria Jenjang Sekolah yang Diintegrasikan <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <label class="relative flex items-center p-3 rounded-xl border border-slate-200 hover:border-indigo-400 cursor-pointer bg-white has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 transition">
                                    <input type="radio" name="criteria" value="ALL" x-model="selectedCriteria" class="text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-2.5">
                                        <span class="font-bold text-slate-900 block text-xs">ALL SLTA Sederajat</span>
                                        <span class="text-[10px] text-slate-500">SMA, SMK, dan MA</span>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3 rounded-xl border border-slate-200 hover:border-indigo-400 cursor-pointer bg-white has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 transition">
                                    <input type="radio" name="criteria" value="SMK" x-model="selectedCriteria" class="text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-2.5">
                                        <span class="font-bold text-slate-900 block text-xs">SMK Saja</span>
                                        <span class="text-[10px] text-slate-500">Dapodik Kemendikbud</span>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3 rounded-xl border border-slate-200 hover:border-indigo-400 cursor-pointer bg-white has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 transition">
                                    <input type="radio" name="criteria" value="SMA" x-model="selectedCriteria" class="text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-2.5">
                                        <span class="font-bold text-slate-900 block text-xs">SMA Saja</span>
                                        <span class="text-[10px] text-slate-500">Dapodik Kemendikbud</span>
                                    </div>
                                </label>

                                <label class="relative flex items-center p-3 rounded-xl border border-slate-200 hover:border-indigo-400 cursor-pointer bg-white has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/50 transition">
                                    <input type="radio" name="criteria" value="MA" x-model="selectedCriteria" class="text-indigo-600 focus:ring-indigo-500">
                                    <div class="ml-2.5">
                                        <span class="font-bold text-slate-900 block text-xs">MA Saja</span>
                                        <span class="text-[10px] text-slate-500">Kementerian Agama</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 5. Tahun Integrasi Target & Otomatisasi Input Siswa Kelas 12 -->
                        <div class="p-3.5 bg-indigo-50/60 rounded-2xl border border-indigo-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="calendar" class="w-4 h-4 text-indigo-600"></i>
                                    <span>5. Tahun Integrasi Target (Input Otomatis Siswa Kelas 12)</span>
                                    <span class="text-red-500">*</span>
                                </label>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-indigo-100 text-indigo-800 border border-indigo-300">
                                    Auto-Input Siswa Kelas 12
                                </span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-700 mb-1">Pilih Tahun Ajaran Integrasi:</label>
                                    @php
                                        $curYear = (int) date('Y');
                                    @endphp
                                    <select name="integration_year" x-model="selectedYear"
                                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white font-bold text-slate-800">
                                        <option value="{{ $curYear }}">{{ $curYear }} / {{ $curYear + 1 }} (Tahun Ajaran Aktif {{ $curYear }})</option>
                                        <option value="{{ $curYear - 1 }}">{{ $curYear - 1 }} / {{ $curYear }} (Tahun Ajaran {{ $curYear - 1 }})</option>
                                        <option value="{{ $curYear - 2 }}">{{ $curYear - 2 }} / {{ $curYear - 1 }} (Tahun Ajaran {{ $curYear - 2 }})</option>
                                        <option value="{{ $curYear + 1 }}">{{ $curYear + 1 }} / {{ $curYear + 2 }} (Tahun Depan {{ $curYear + 1 }})</option>
                                    </select>
                                </div>

                                <div class="p-2.5 bg-white rounded-xl border border-indigo-100 flex flex-col justify-center">
                                    <div class="flex items-center space-x-1 font-bold text-[11px] text-indigo-950">
                                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-600"></i>
                                        <span>Perhitungan Cerdas Kelas 12</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 mt-0.5 leading-snug">
                                        Sistem mengkalkulasi otomatis jumlah rombel &amp; siswa Kelas 12 standar Permendikbud per jenjang (SMA/SMK/MA), status, dan akreditasi pada tahun tersebut.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- 6. Ketentuan & Keaktifan Otomatis -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-200 text-[11px] text-slate-600 space-y-1">
                            <div class="flex items-center space-x-1.5 font-bold text-slate-800">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                <span>Otomatisasi Sistem Integrasi:</span>
                            </div>
                            <p class="text-[10px] text-slate-500 pl-5">
                                • <strong>Auto-Input Siswa Kelas 12:</strong> Menghitung dan menyimpan otomatis estimasi jumlah rombel serta siswa kelas 12 berdasarkan tahun integrasi terpilih.<br>
                                • <strong>Otomatis Memulihkan (Restore):</strong> Sekolah yang sebelumnya terhapus akan otomatis diaktifkan dan dimunculkan kembali ke tabel.<br>
                                • <strong>Sinkronisasi Resmi:</strong> Update NPSN, akreditasi, koordinat GIS lintang/bujur, serta alamat lengkap dari Dapodik &amp; Kemenag.<br>
                                • <strong>Hanya Sekolah Aktif:</strong> Memastikan seluruh satuan pendidikan SLTA memiliki izin operasional aktif.<br>
                                • <strong>Scoring Presisi:</strong> Menghitung ulang jarak ke Kampus UBSI dan memperbarui level prioritas target (A, B, C) dengan bobot siswa kelas 12.
                            </p>
                        </div>

                        <!-- Error State Banner inside Modal -->
                        <div x-show="integrationError" class="p-3.5 bg-red-50 rounded-2xl border border-red-200 flex items-start space-x-3 text-xs text-red-800">
                            <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600 shrink-0 mt-0.5"></i>
                            <div>
                                <span class="font-bold block text-red-900">Integrasi Belum Berhasil</span>
                                <span x-text="integrationError" class="text-[11px]"></span>
                            </div>
                        </div>

                        <!-- Loading State Notice when Submitting -->
                        <div x-show="isIntegrating" class="p-3.5 bg-indigo-50 rounded-2xl border border-indigo-200 flex items-center space-x-3">
                            <svg class="animate-spin h-5 w-5 text-indigo-600 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            <div>
                                <span class="font-bold text-indigo-950 block text-xs">Menghubungkan Server Dapodik &amp; Kemenag...</span>
                                <span class="text-[10px] text-indigo-700">Mohon tunggu beberapa saat selagi sistem memetakan dan menyinkronkan data sekolah aktif.</span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                            <button type="button" :disabled="isIntegrating" @click="showIntegrateModal = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="isIntegrating || loadingDistricts || selectedDistricts.length === 0"
                                :class="(selectedDistricts.length === 0 || isIntegrating || loadingDistricts) ? 'opacity-60 cursor-not-allowed bg-slate-400' : 'bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-600/20 cursor-pointer'"
                                class="px-5 py-2.5 text-white font-bold text-xs rounded-xl flex items-center space-x-2 transition">
                                <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="{ 'animate-spin': isIntegrating }"></i>
                                <span x-text="isIntegrating ? 'Sedang Sinkronisasi...' : (selectedDistricts.length === 0 ? 'Pilih Minimal 1 Kecamatan' : (districtsList.length > 0 && selectedDistricts.length === districtsList.length ? 'Mulai Integrasi Semua Kecamatan (' + districtsList.length + ')' : 'Mulai Integrasi (' + selectedDistricts.length + ' Kecamatan Terpilih)'))"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Import Excel / CSV -->
        <div x-show="showImportModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showImportModal" @click="showImportModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showImportModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                            <span>Import Data Sekolah via Excel / CSV</span>
                        </h3>
                        <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('schools.import') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf

                        <!-- Prominent Template Download Section -->
                        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <span class="font-bold text-emerald-950 block text-xs">Format File Excel</span>
                                <span class="text-[11px] text-emerald-800">Unduh template CSV/Excel untuk kemudahan pengisian data.</span>
                            </div>
                            <a href="{{ route('schools.export-template') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-md shrink-0 transition flex items-center justify-center space-x-1.5">
                                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                                <span>Download Template</span>
                            </a>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Pilih File Excel / CSV <span class="text-red-500">*</span></label>
                            <input type="file" name="excel_file" accept=".csv, .xlsx, .xls, .txt" required
                                class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                        </div>

                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 space-y-1">
                            <p class="font-bold">Kolom Excel yang Didukung:</p>
                            <p>NPSN, Nama Sekolah, Jenjang, Status (Negeri/Swasta), Kecamatan, Alamat, Latitude, Longitude, Jumlah Siswa, Siswa Kelas 12, Guru BK, No HP BK.</p>
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showImportModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow flex items-center space-x-1.5">
                                <i data-lucide="upload" class="w-3.5 h-3.5"></i>
                                <span>Mulai Import Data</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Hapus Semua Data Sekolah -->
        <div x-show="showDeleteAllModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-delete-all-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showDeleteAllModal" @click="if(!isDeletingAll) showDeleteAllModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showDeleteAllModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-red-200 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-red-50 border border-red-200 flex items-center justify-center text-red-600 shrink-0">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900">Konfirmasi Hapus Semua Data Sekolah</h3>
                                <p class="text-[11px] text-slate-500">Tindakan massal menghapus data sekolah dari sistem</p>
                            </div>
                        </div>
                        <button type="button" :disabled="isDeletingAll" @click="showDeleteAllModal = false" class="text-slate-400 hover:text-slate-600 transition">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('schools.destroy-all') }}" method="POST" @submit="isDeletingAll = true" class="mt-4 space-y-4">
                        @csrf
                        @method('DELETE')

                        <div class="p-3.5 bg-red-50 rounded-2xl border border-red-200 text-xs text-red-900 space-y-1.5">
                            <p class="font-bold flex items-center gap-1.5">
                                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                                <span>Peringatan Penghapusan Data Massal</span>
                            </p>
                            <p class="text-[11px] leading-relaxed text-red-800">
                                Saat ini terdapat <strong class="text-red-950 font-extrabold">{{ $schools->total() }} data sekolah</strong> dalam basis data. Anda dapat memilih memindahkan data ke <strong>Tempat Sampah</strong> (aman dan dapat dipulihkan kapan saja) atau menghapusnya secara permanen.
                            </p>
                        </div>

                        <!-- Opsi Penghapusan -->
                        <div class="space-y-2 text-xs">
                            <label class="block font-bold text-slate-700">Pilih Metode Penghapusan:</label>
                            
                            <label class="flex items-start p-3 rounded-xl border border-slate-200 hover:border-slate-300 cursor-pointer bg-slate-50/50 has-[:checked]:border-indigo-600 has-[:checked]:bg-indigo-50/40 transition">
                                <input type="radio" name="force_delete" value="0" checked x-model="forceDeleteAll" :value="false" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div class="ml-2.5">
                                    <span class="font-extrabold text-slate-900 block text-xs">Pindahkan ke Tempat Sampah (Direkomendasikan)</span>
                                    <span class="text-[11px] text-slate-500">Data aman disembunyikan dan dapat dipulihkan kembali kapan saja lewat menu Review Sampah atau Sinkronisasi Dapodik &amp; Kemenag.</span>
                                </div>
                            </label>

                            <label class="flex items-start p-3 rounded-xl border border-slate-200 hover:border-red-300 cursor-pointer bg-slate-50/50 has-[:checked]:border-red-600 has-[:checked]:bg-red-50/40 transition">
                                <input type="radio" name="force_delete" value="1" x-model="forceDeleteAll" :value="true" class="mt-0.5 text-red-600 focus:ring-red-500">
                                <div class="ml-2.5">
                                    <span class="font-extrabold text-red-700 block text-xs">Hapus Permanen (Kosongkan Database)</span>
                                    <span class="text-[11px] text-red-600/80">Data sekolah akan dibersihkan total dari database secara permanen.</span>
                                </div>
                            </label>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center justify-end space-x-2 pt-3 border-t border-slate-100">
                            <button type="button" :disabled="isDeletingAll" @click="showDeleteAllModal = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="isDeletingAll"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-md shadow-red-600/20 flex items-center space-x-2 transition">
                                <i data-lucide="trash-2" class="w-4 h-4" :class="{ 'animate-spin': isDeletingAll }"></i>
                                <span x-text="isDeletingAll ? 'Sedang Memproses...' : (forceDeleteAll ? 'Ya, Hapus Permanen Semua' : 'Ya, Pindahkan Semua ke Sampah')"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Integration Success Summary Banner -->
    @if(session('integration_summary'))
        @php $summary = session('integration_summary'); @endphp
        <div class="mb-6 p-4 bg-indigo-50 border border-indigo-200 rounded-2xl text-xs text-indigo-950 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-start space-x-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-md">
                    <i data-lucide="check-check" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-extrabold text-sm text-indigo-900">Integrasi Dapodik &amp; Kemenag Berhasil!</h4>
                    <p class="text-[11px] text-indigo-800 mt-0.5">
                        Wilayah: <span class="font-bold">{{ $summary['regency'] ?? '-' }}</span>
                        @if(!empty($summary['district']))
                            • Kecamatan: <span class="font-extrabold text-indigo-950">Kecamatan {{ $summary['district'] }}</span>
                        @endif
                        • Kriteria: <span class="font-bold">{{ $summary['criteria'] ?? 'ALL' }}</span> • 
                        Total: <span class="font-extrabold text-indigo-950">{{ $summary['total_count'] ?? 0 }} Sekolah</span>
                        ({{ $summary['created_count'] ?? 0 }} data baru ditambahkan, {{ $summary['updated_count'] ?? 0 }} diperbarui)
                        @if(!empty($summary['restored_count']) && $summary['restored_count'] > 0)
                            • <span class="inline-flex items-center px-2 py-0.5 bg-amber-100 border border-amber-300 text-amber-900 font-extrabold rounded-md text-[10px]">
                                <i data-lucide="rotate-ccw" class="w-3 h-3 mr-1"></i>
                                {{ $summary['restored_count'] }} Data Terhapus Dipulihkan
                            </span>
                        @endif.
                    </p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($summary['type_breakdown'] ?? [] as $type => $count)
                            @if($count > 0)
                                <span class="px-2 py-0.5 bg-white text-indigo-700 font-bold rounded-md border border-indigo-200 text-[10px]">
                                    {{ $type }}: {{ $count }}
                                </span>
                            @endif
                        @endforeach
                        @foreach($summary['source_breakdown'] ?? [] as $src => $count)
                            @if($count > 0)
                                <span class="px-2 py-0.5 bg-indigo-100 text-indigo-800 font-semibold rounded-md text-[10px]">
                                    {{ $src }}: {{ $count }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <a href="{{ route('schools.index') }}" class="px-3.5 py-1.5 bg-white hover:bg-slate-50 text-indigo-700 font-bold border border-indigo-200 rounded-xl text-xs shrink-0 self-start md:self-center shadow-sm">
                Tutup Notifikasi
            </a>
        </div>
    @endif

    @if(session('school_import_errors'))
        <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-950">
            <p class="font-bold mb-2">Beberapa baris sekolah dilewati:</p>
            <ul class="list-disc pl-5 space-y-1">
                @foreach(session('school_import_errors') as $importError)
                    <li>{{ $importError }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Banner Data Terhapus & Review Modal -->
    @if(isset($trashedCount) && $trashedCount > 0)
        <div x-data="{ showReviewModal: false }" class="mb-6">
            <!-- Banner Notification -->
            <div class="p-4 bg-amber-50 border border-amber-200/90 rounded-2xl text-xs text-amber-950 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <i data-lucide="archive-restore" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-sm text-amber-900 flex items-center gap-1.5">
                            <span>Terdapat {{ $trashedCount }} Data Sekolah di Tempat Sampah (Terhapus)</span>
                        </h4>
                        <p class="text-[11px] text-amber-700 mt-0.5">
                            Tinjau daftar sekolah yang terhapus sebelum dipulihkan, atau pulihkan secara otomatis saat integrasi.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Tombol Review Data -->
                    <button type="button" id="btn-review-trash" onclick="openReviewModal()" @click="showReviewModal = true"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold rounded-xl text-xs shadow-md shadow-amber-600/20 flex items-center space-x-2 transition cursor-pointer">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                        <span>Review Data Terhapus ({{ $trashedCount }})</span>
                    </button>

                    <!-- Tombol Pulihkan Semua Langsung -->
                    <form action="{{ route('schools.restore-all') }}" method="POST" onsubmit="return confirm('Pulihkan seluruh {{ $trashedCount }} data sekolah yang terhapus kembali ke status aktif?')">
                        @csrf
                        <button type="submit" class="px-3.5 py-2 bg-white hover:bg-amber-100/80 text-amber-900 border border-amber-300 font-bold rounded-xl text-xs flex items-center space-x-1.5 transition cursor-pointer shadow-sm">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5 text-amber-700"></i>
                            <span>Pulihkan Semua</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modal Review Data Sekolah Terhapus -->
            <div id="modal-review-trash" x-show="showReviewModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto" role="dialog" aria-modal="true">
                <!-- Backdrop Background -->
                <div onclick="closeReviewModal()" @click="showReviewModal = false" class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity"></div>

                <!-- Modal Dialog Box -->
                <div class="relative z-10 w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 flex flex-col max-h-[90vh] overflow-hidden my-auto text-left">
                    <!-- Header Modal -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-600 shrink-0">
                                <i data-lucide="archive-restore" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-sm font-extrabold text-slate-900 flex items-center gap-2">
                                    <span>Daftar Sekolah yang Terhapus</span>
                                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold">
                                        {{ $trashedCount }} Sekolah
                                    </span>
                                </h3>
                                <p class="text-[11px] text-slate-500">Tinjau data sekolah sebelum memulihkan satu per satu atau sekaligus.</p>
                            </div>
                        </div>
                        <button type="button" onclick="closeReviewModal()" @click="showReviewModal = false" class="text-slate-400 hover:text-slate-600 transition cursor-pointer">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Daftar Sekolah Terhapus -->
                    <div class="mt-4 max-h-96 overflow-y-auto divide-y divide-slate-100 pr-1">
                        @forelse($trashedSchools ?? [] as $ts)
                            @php /** @var \App\Models\School $ts */ @endphp
                            <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/70 p-2.5 rounded-xl transition">
                                <div class="space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-extrabold text-xs text-slate-900">{{ $ts->name }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $ts->schoolType->name ?? 'SLTA' }}
                                        </span>
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $ts->status === 'negeri' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                            {{ strtoupper($ts->status ?? 'SWASTA') }}
                                        </span>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-slate-500">
                                        <span>NPSN: <strong class="text-slate-700">{{ $ts->npsn ?: '-' }}</strong></span>
                                        <span>•</span>
                                        <span>Kecamatan: <strong class="text-slate-700">{{ $ts->district->name ?? '-' }}</strong></span>
                                        <span>•</span>
                                        <span class="text-amber-700">Dihapus: {{ $ts->deleted_at ? $ts->deleted_at->diffForHumans() : '-' }}</span>
                                    </div>
                                </div>

                                <!-- Tombol Pulihkan Spesifik -->
                                <form action="{{ route('schools.restore', $ts->id) }}" method="POST" onsubmit="return confirm('Pulihkan sekolah {{ addslashes($ts->name) }} kembali ke data aktif?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm flex items-center space-x-1.5 transition shrink-0 cursor-pointer">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>Pulihkan Sekolah Ini</span>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <div class="py-8 text-center text-xs text-slate-400">
                                Tidak ada data sekolah di tempat sampah.
                            </div>
                        @endforelse
                    </div>

                    <!-- Footer Modal Actions -->
                    <div class="mt-6 pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                        <span class="text-[11px] text-slate-400">
                            Data yang dipulihkan akan langsung kembali aktif di tabel master sekolah.
                        </span>

                        <div class="flex items-center space-x-2 w-full sm:w-auto justify-end">
                            <button type="button" onclick="closeReviewModal()" @click="showReviewModal = false"
                                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                                Tutup
                            </button>

                            <form action="{{ route('schools.restore-all') }}" method="POST" onsubmit="return confirm('Pulihkan seluruh {{ $trashedCount }} data sekolah yang terhapus?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white font-extrabold text-xs rounded-xl shadow-md shadow-amber-600/20 flex items-center space-x-1.5 transition cursor-pointer">
                                    <i data-lucide="check-check" class="w-4 h-4"></i>
                                    <span>Pulihkan Semua ({{ $trashedCount }})</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                function openReviewModal() {
                    const m = document.getElementById('modal-review-trash');
                    if (m) {
                        m.style.setProperty('display', 'flex', 'important');
                    }
                }
                function closeReviewModal() {
                    const m = document.getElementById('modal-review-trash');
                    if (m) {
                        m.style.setProperty('display', 'none', 'important');
                    }
                }
            </script>
        </div>
    @endif

    <!-- Filter & Search Bar Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-soft mb-6">
        <form method="GET" action="{{ route('schools.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
            <!-- Search Input (3 Cols) -->
            <div class="lg:col-span-3 relative">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama sekolah atau NPSN..."
                    class="w-full pl-10 pr-4 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <i data-lucide="search" class="w-4 h-4"></i>
                </div>
            </div>

            <!-- Filter Kecamatan (3 Cols) -->
            <div class="lg:col-span-3">
                <select name="district_id" onchange="this.form.submit()"
                    class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-300 focus:border-brand-500 text-slate-800">
                    <option value="">-- Semua Kecamatan --</option>
                    @foreach($districts as $d)
                        <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Jenjang (2 Cols) -->
            <div class="lg:col-span-2">
                <select name="school_type_id" onchange="this.form.submit()"
                    class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-300 focus:border-brand-500 text-slate-800">
                    <option value="">-- Jenjang --</option>
                    @foreach($schoolTypes as $st)
                        <option value="{{ $st->id }}" {{ request('school_type_id') == $st->id ? 'selected' : '' }}>
                            {{ $st->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status (1 Col) -->
            <div class="lg:col-span-1">
                <select name="status" onchange="this.form.submit()"
                    class="w-full py-2.5 px-3 text-xs rounded-xl border border-slate-300 focus:border-brand-500 text-slate-800">
                    <option value="">-- Status --</option>
                    <option value="negeri" {{ request('status') == 'negeri' ? 'selected' : '' }}>NEGERI</option>
                    <option value="swasta" {{ request('status') == 'swasta' ? 'selected' : '' }}>SWASTA</option>
                </select>
            </div>

            <!-- Filter Tampilkan Per Halaman (1 Col) -->
            <div class="lg:col-span-1">
                <select name="per_page" onchange="this.form.submit()" title="Jumlah data yang ditampilkan"
                    class="w-full py-2.5 px-2 text-xs rounded-xl border border-indigo-200 bg-indigo-50/40 text-indigo-900 font-bold focus:border-indigo-500">
                    <option value="10" {{ request('per_page', $perPage ?? 25) == 10 ? 'selected' : '' }}>10 / hal</option>
                    <option value="25" {{ request('per_page', $perPage ?? 25) == 25 ? 'selected' : '' }}>25 / hal</option>
                    <option value="50" {{ request('per_page', $perPage ?? 25) == 50 ? 'selected' : '' }}>50 / hal</option>
                    <option value="100" {{ request('per_page', $perPage ?? 25) == 100 ? 'selected' : '' }}>100 / hal</option>
                    <option value="250" {{ request('per_page', $perPage ?? 25) == 250 ? 'selected' : '' }}>250 / hal</option>
                </select>
            </div>

            <!-- Action Buttons (2 Cols) -->
            <div class="lg:col-span-2 flex items-center gap-2">
                <button type="submit" class="flex-1 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    <span>Terapkan</span>
                </button>
                <button type="button" onclick="window.location.assign('{{ route('schools.index') }}')"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                    title="Kosongkan pencarian dan semua filter">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Reset</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Range Info & Per-Page Toolbar -->
    <div class="bg-white px-5 py-3 rounded-2xl border border-slate-200/80 shadow-soft mb-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center space-x-2 text-xs text-slate-600">
            <span class="inline-flex items-center justify-center w-7 h-7 rounded-xl bg-indigo-50 text-indigo-600 font-bold border border-indigo-100">
                <i data-lucide="list-filter" class="w-4 h-4"></i>
            </span>
            <span>
                Menampilkan data ke <strong class="text-slate-900 font-extrabold">{{ $schools->firstItem() ?? 0 }} - {{ $schools->lastItem() ?? 0 }}</strong> dari total <strong class="text-indigo-600 font-extrabold">{{ number_format($schools->total(), 0, ',', '.') }}</strong> sekolah
            </span>
        </div>

        <div class="flex items-center gap-1.5 self-start sm:self-auto">
            <span class="text-xs text-slate-500 font-bold mr-1">Tampilkan Data:</span>
            @foreach([25, 50, 100, 250] as $option)
                @php
                    $isCurrent = request('per_page', $perPage ?? 25) == $option;
                    $queryParams = array_merge(request()->query(), ['per_page' => $option, 'page' => 1]);
                @endphp
                <a href="{{ route('schools.index', $queryParams) }}"
                   title="Tampilkan {{ $option }} data sekolah per halaman"
                   class="px-3 py-1.5 text-xs font-bold rounded-xl border transition {{ $isCurrent ? 'bg-indigo-600 text-white border-indigo-600 shadow-sm' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                    {{ $option }}
                </a>
            @endforeach
            <span class="text-[11px] text-slate-400 font-medium ml-1">/ halaman</span>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden" x-data="{ openCoordModal: false, activeSchoolId: null, activeSchoolName: '', currentLat: '', currentLng: '' }">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900 text-white font-bold uppercase tracking-wider">
                    <tr>
                        <th class="py-3.5 px-4">No</th>
                        <th class="py-3.5 px-4">NPSN</th>
                        <th class="py-3.5 px-4">Nama Sekolah</th>
                        <th class="py-3.5 px-4">Jenjang</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4 text-center">Kelas 12</th>
                        <th class="py-3.5 px-4 text-center">Koordinat GIS</th>
                        <th class="py-3.5 px-4 text-center">Prioritas</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($schools as $index => $school)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-semibold text-slate-500">
                                {{ $schools->firstItem() + $index }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-700">
                                {{ $school->npsn }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="{{ route('schools.show', $school->id) }}" class="font-bold text-slate-900 hover:text-brand-600 block leading-tight">
                                    {{ $school->name }}
                                </a>
                                <span class="text-[10px] text-slate-400 block mt-0.5">
                                    {{ $school->contacts_count }} Kontak &bull; {{ $school->visits_count }} Kunjungan &bull; {{ $school->prospects_count }} Prospek
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $school->schoolType ? $school->schoolType->name : '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-600">
                                <span class="uppercase text-[11px]">{{ $school->status }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                {{ $school->district ? $school->district->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($school->grade_12_count > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ number_format($school->grade_12_count) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5 font-medium">
                                        ±{{ ceil($school->grade_12_count / 34) }} Rombel
                                    </span>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($school->latitude && $school->longitude)
                                    <a href="{{ $school->gmaps_search_url }}"
                                       target="_blank"
                                       title="Buka Lokasi {{ $school->name }} di Google Maps"
                                       class="font-mono text-[10px] text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-2 py-1 rounded border border-emerald-200 inline-flex items-center gap-1 transition shadow-xs">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-emerald-600"></i>
                                        <span>{{ number_format($school->latitude, 4) }}, {{ number_format($school->longitude, 4) }}</span>
                                        <i data-lucide="external-link" class="w-2.5 h-2.5 text-emerald-500"></i>
                                    </a>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">{{ number_format($school->distance_from_campus, 1) }} km dari UBSI</span>
                                @else
                                    <span class="text-[10px] text-red-500 font-bold block">Belum ada</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $priorityClass = match($school->priority_level) {
                                        'A' => 'bg-red-100 text-red-700 border-red-200',
                                        'B' => 'bg-amber-100 text-amber-700 border-amber-200',
                                        'C' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                        default => 'bg-slate-100 text-slate-500 border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full border {{ $priorityClass }}">
                                    {{ $school->priority_level ?? 'Belum' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    <button type="button"
                                        @click="openCoordModal = true; activeSchoolId = {{ $school->id }}; activeSchoolName = '{{ addslashes($school->name) }}'; currentLat = '{{ $school->latitude ?? '-6.3054' }}'; currentLng = '{{ $school->longitude ?? '107.2995' }}';"
                                        title="Set Koordinat GIS (Lat/Lng)"
                                        class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 transition">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <a href="{{ route('schools.show', $school->id) }}" title="Detail Profile"
                                        class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 transition">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="{{ route('schools.edit', $school->id) }}" title="Edit Sekolah"
                                        class="p-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form method="POST" action="{{ route('schools.destroy', $school->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $school->name }}?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Sekolah" class="p-1.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-slate-400">
                                <i data-lucide="search-x" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                                <p class="text-xs font-semibold text-slate-500">Tidak ada sekolah ditemukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="text-xs text-slate-500 font-medium">
                Menampilkan data ke <strong class="text-slate-800">{{ $schools->firstItem() ?? 0 }}</strong> sampai <strong class="text-slate-800">{{ $schools->lastItem() ?? 0 }}</strong> dari total <strong class="text-slate-800">{{ number_format($schools->total(), 0, ',', '.') }}</strong> sekolah
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex items-center space-x-1.5 text-xs text-slate-600">
                    <span class="text-slate-500 font-semibold">Tampilkan:</span>
                    <select onchange="window.location.href = this.value" class="py-1 px-2 text-xs rounded-xl border border-slate-300 font-bold bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                        @foreach([10, 25, 50, 100, 250] as $opt)
                            <option value="{{ route('schools.index', array_merge(request()->query(), ['per_page' => $opt, 'page' => 1])) }}" {{ request('per_page', $perPage ?? 25) == $opt ? 'selected' : '' }}>
                                {{ $opt }} data
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    {{ $schools->links() }}
                </div>
            </div>
        </div>

        <!-- Quick Modal Edit Koordinat GIS -->
        <div x-show="openCoordModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="openCoordModal" @click="openCoordModal = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="openCoordModal" class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-slate-200 p-6">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-600"></i>
                            <span>Update Koordinat GIS Sekolah</span>
                        </h3>
                        <button type="button" @click="openCoordModal = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form :action="'/schools/' + activeSchoolId + '/coordinates'" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <p class="text-xs text-slate-600">Masukkan nilai Latitude &amp; Longitude posisi fisik untuk <strong x-text="activeSchoolName"></strong>:</p>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (Lintang)</label>
                            <input type="text" name="latitude" x-model="currentLat" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (Bujur)</label>
                            <input type="text" name="longitude" x-model="currentLng" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-semibold">
                        </div>

                        <div class="flex items-center justify-end space-x-2 pt-2">
                            <button type="button" @click="openCoordModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl">Batal</button>
                            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow">Simpan Koordinat</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
