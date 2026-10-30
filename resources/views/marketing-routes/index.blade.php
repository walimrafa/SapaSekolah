<x-app-layout title="Rute Mobil Marketing & Outing 10 Hari">
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        #route-map {
            height: 440px;
            width: 100%;
            border-radius: 1.25rem;
            z-index: 1;
        }
        .seq-icon {
            background: #0f172a;
            color: #ffffff;
            border-radius: 9999px;
            font-weight: 800;
            font-size: 11px;
            display: inline-flex;
            align-items: center;
            border: 2px solid #ffffff;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.4);
            white-space: nowrap;
            padding: 2px 10px 2px 4px;
            line-height: 1.3;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .seq-icon:hover {
            transform: scale(1.08);
            background: #1e3a8a;
            z-index: 1000 !important;
        }
        .seq-icon .badge-num {
            background: #f59e0b;
            color: #0f172a;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 900;
            margin-right: 6px;
            flex-shrink: 0;
        }
        .school-map-tooltip {
            background: #0f172a !important;
            color: #ffffff !important;
            border-radius: 8px !important;
            border: 1px solid #334155 !important;
            font-size: 11px !important;
            font-weight: 700 !important;
            padding: 5px 9px !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.3) !important;
        }
        .school-map-tooltip::before {
            border-top-color: #0f172a !important;
        }
        .ubsi-icon {
            background: #f59e0b;
            color: #0f172a;
            border-radius: 9999px;
            font-weight: 900;
            font-size: 11px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            padding: 3px 10px;
            white-space: nowrap;
        }
    </style>
    @endpush

    <!-- Header & 10-Day Campaign Trigger -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Rute Mobil Marketing &amp; Kampanye Outing 10 Hari</h1>
            <p class="text-xs text-slate-500 mt-0.5">Navigasi mobil harian (10 sekolah/hari) berpusat dari Kampus UBSI Karawang</p>
        </div>

        <form method="POST" action="{{ route('marketing-routes.store-campaign') }}" onsubmit="return confirm('Generate otomatis kampanye rute mobil 10 Hari (Hari #1 s/d Hari #10)?')">
            @csrf
            <input type="hidden" name="user_id" value="{{ auth()->id() }}">
            <button type="submit" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs shadow-md shadow-amber-500/20 flex items-center space-x-2 transition">
                <i data-lucide="car" class="w-4 h-4"></i>
                <span>Generate Kampanye Outing Mobil 10 Hari</span>
            </button>
        </form>
    </div>

    <!-- Main Grid: Interactive Map & Route Builder -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8" x-data="routeBuilderApp()">
        <!-- Left: Interactive Leaflet Route Map (7 Cols) -->
        <div class="lg:col-span-7 bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="navigation" class="w-4 h-4 text-brand-600"></i>
                        <span>Visualisasi Peta Rute Mobil (Driving Route)</span>
                    </h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Start: Kampus UBSI Karawang &rarr; Sekolah Terpilih &rarr; Kampus UBSI Karawang</p>
                </div>

                <div x-show="routePreview" class="text-right">
                    <span class="text-xs font-bold text-brand-600 block" x-text="routePreview.total_distance_km + ' km'"></span>
                    <span class="text-[10px] text-slate-500" x-text="'Drive: ' + (routePreview.driving_minutes || Math.round((routePreview.total_distance_km / 30) * 60)) + ' mnt | Total: ' + routePreview.estimated_duration_minutes + ' mnt'"></span>
                </div>
            </div>

            <!-- Mode Banner: Viewing Saved Route -->
            <div x-show="activeSavedRoute" x-transition style="display: none;" class="mb-3 p-3 bg-indigo-50 border border-indigo-200 rounded-2xl flex items-center justify-between text-xs text-indigo-950">
                <div class="flex items-center gap-2 truncate pr-2">
                    <span class="px-2 py-0.5 bg-indigo-600 text-white font-extrabold rounded-md text-[10px] shrink-0">MODE LIHAT RUTE</span>
                    <span class="truncate">Menampilkan: <b x-text="activeSavedRoute ? activeSavedRoute.name : ''"></b> (<span x-text="activeSavedRoute && activeSavedRoute.sequence ? activeSavedRoute.sequence.length : 0"></span> Sekolah)</span>
                </div>
                <button type="button" @click="resetToBuilderMode()" class="px-2.5 py-1 bg-white hover:bg-slate-100 text-slate-700 font-bold rounded-xl border border-slate-300 text-[10px] shrink-0 transition flex items-center gap-1 shadow-2xs cursor-pointer">
                    <i data-lucide="x" class="w-3 h-3 text-red-500"></i>
                    <span>Tutup &amp; Buat Rute</span>
                </button>
            </div>

            <div id="route-map" class="shadow-inner border border-slate-200"></div>

            <div class="mt-4 p-3 bg-amber-50 rounded-2xl border border-amber-200 text-xs flex items-center justify-between text-amber-900">
                <div class="flex items-center gap-2">
                    <i data-lucide="car" class="w-4 h-4 text-amber-600 shrink-0"></i>
                    <span>Parameter Mobil: Kecepatan rata-rata 30 km/jam di Karawang + 25 menit durasi pertemuan per sekolah.</span>
                </div>
                <template x-if="routePreview && routePreview.google_maps_driving_url">
                    <a :href="routePreview.google_maps_driving_url" target="_blank" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-[10px] shrink-0 transition flex items-center gap-1">
                        <i data-lucide="map-pin" class="w-3 h-3"></i>
                        <span>Buka Google Maps</span>
                    </a>
                </template>
            </div>
        </div>

        <!-- Right: Generator Form & Sequence List (5 Cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-4 pb-2 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                    <span>Generate Rute Mobil Klaster (10 Sekolah)</span>
                </h3>

                <form method="POST" action="{{ route('marketing-routes.store-cluster') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Rute Klaster <span class="text-red-500">*</span></label>
                        <input type="text" name="name" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300" placeholder="Rute Mobil Outing Hari #1">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Kunjungan</label>
                            <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Staff Marketing PIC</label>
                            <select name="user_id" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300">
                                @foreach($marketingUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <!-- Combobox Titik Awal Kunjungan -->
                        <div class="relative" @click.outside="startOpen = false">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Titik Awal Kunjungan (Start Point)</label>
                            <input type="hidden" name="start_school_id" :value="startSchoolId">

                            <button type="button" @click="startOpen = !startOpen" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 bg-amber-50/50 font-semibold text-slate-800 flex items-center justify-between shadow-xs transition hover:border-amber-400">
                                <span class="truncate pr-2 font-bold text-brand-700" x-text="startLabel"></span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                            </button>

                            <div x-show="startOpen" x-transition style="display: none;" class="absolute left-0 right-0 z-40 mt-1 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 text-xs space-y-1.5">
                                <div class="relative">
                                    <input type="text" x-model="startSearch" placeholder="Cari nama sekolah atau opsi..." class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                                </div>
                                <div class="max-h-44 overflow-y-auto space-y-0.5 pr-0.5">
                                    <template x-for="opt in startOptions" :key="'opt-start-' + opt.id">
                                        <button type="button" @click="startSchoolId = opt.id; startOpen = false; startSearch = ''; previewCluster()" 
                                            class="w-full text-left px-2.5 py-2 rounded-xl hover:bg-amber-50 transition flex items-center justify-between"
                                            :class="startSchoolId === opt.id ? 'bg-amber-100/70 font-bold text-amber-900' : 'text-slate-700'">
                                            <span class="truncate" x-text="opt.label"></span>
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-amber-600 shrink-0 ml-2" x-show="startSchoolId === opt.id"></i>
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[10px] text-slate-400 px-1" x-show="selectedDistricts.length === 0">💡 Centang kecamatan di bawah untuk memunculkan pilihan sekolah spesifik.</p>
                            </div>
                        </div>

                        <!-- Combobox Titik Akhir Kunjungan -->
                        <div class="relative" @click.outside="endOpen = false">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Titik Akhir Kunjungan (End Point)</label>
                            <input type="hidden" name="end_school_id" :value="endSchoolId">

                            <button type="button" @click="endOpen = !endOpen" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 bg-emerald-50/50 font-semibold text-slate-800 flex items-center justify-between shadow-xs transition hover:border-emerald-400">
                                <span class="truncate pr-2 font-bold text-emerald-700" x-text="endLabel"></span>
                                <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                            </button>

                            <div x-show="endOpen" x-transition style="display: none;" class="absolute left-0 right-0 z-40 mt-1 bg-white rounded-2xl shadow-2xl border border-slate-200 p-2 text-xs space-y-1.5">
                                <div class="relative">
                                    <input type="text" x-model="endSearch" placeholder="Cari nama sekolah atau opsi..." class="w-full pl-8 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
                                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5"></i>
                                </div>
                                <div class="max-h-44 overflow-y-auto space-y-0.5 pr-0.5">
                                    <template x-for="opt in endOptions" :key="'opt-end-' + opt.id">
                                        <button type="button" @click="endSchoolId = opt.id; endOpen = false; endSearch = ''; previewCluster()" 
                                            class="w-full text-left px-2.5 py-2 rounded-xl hover:bg-emerald-50 transition flex items-center justify-between"
                                            :class="endSchoolId === opt.id ? 'bg-emerald-100/70 font-bold text-emerald-900' : 'text-slate-700'">
                                            <span class="truncate" x-text="opt.label"></span>
                                            <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 shrink-0 ml-2" x-show="endSchoolId === opt.id"></i>
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[10px] text-slate-400 px-1" x-show="selectedDistricts.length === 0">💡 Centang kecamatan di bawah untuk memunculkan pilihan sekolah spesifik.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 1. Filter Kota / Kabupaten Target -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-brand-600"></i>
                                <span>Kabupaten / Kota Target</span>
                                <span class="text-red-500">*</span>
                            </span>
                            <span class="text-[10px] text-brand-700 font-bold bg-brand-50 px-2 py-0.5 rounded-full border border-brand-200">
                                Sinkron Data Sekolah
                            </span>
                        </label>
                        <select name="regency_id" x-model="selectedRegency" @change="onRegencyChange()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white shadow-2xs">
                            <option value="ALL">-- Seluruh Wilayah Terintegrasi --</option>
                            @foreach($regencies as $reg)
                                <option value="{{ $reg->id }}">
                                    {{ $reg->name }} ({{ $reg->schools_count }} Sekolah)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Filter Wilayah Klaster (Kecamatan) -->
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-xs font-bold text-slate-700">
                                Filter Kecamatan <span class="text-amber-600 font-normal">— Centang wilayah target</span>
                            </label>
                            <span class="text-[10px] text-slate-500 font-bold bg-slate-100 px-2 py-0.5 rounded-md" x-text="availableDistricts.length + ' kecamatan tersedia'"></span>
                        </div>
                        <div class="flex items-center justify-between gap-2 mb-2 text-[10px]">
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="selectAllDistricts()" class="font-bold text-brand-600 hover:text-brand-800 underline cursor-pointer">Pilih Semua</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="clearAllDistricts()" class="font-bold text-red-500 hover:text-red-700 underline cursor-pointer">Hapus Semua</button>
                            </div>
                            <span class="font-bold text-slate-600" x-text="selectedDistricts.length + ' kecamatan dipilih'"></span>
                        </div>

                        <!-- District Search -->
                        <div class="relative mb-2">
                            <input type="text" x-model="districtSearch" placeholder="Cari nama kecamatan..." class="w-full pl-7 pr-3 py-1.5 text-[11px] rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-2 pointer-events-none"></i>
                        </div>

                        <!-- Scrollable Checkbox List -->
                        <div class="max-h-44 overflow-y-auto border border-slate-200 rounded-xl p-2 bg-slate-50/50 grid grid-cols-2 gap-1.5">
                            <template x-for="d in filteredDistrictsForDisplay" :key="d.id">
                                <label class="flex items-center justify-between px-2 py-1.5 rounded-lg border transition text-xs select-none cursor-pointer"
                                       :class="selectedDistricts.includes(String(d.id)) ? 'bg-amber-50 border-amber-300 text-amber-950 font-bold shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    <div class="flex items-center space-x-1.5 truncate pr-1">
                                        <input type="checkbox" name="district_ids[]" :value="d.id"
                                            x-model="selectedDistricts"
                                            @change="previewCluster()"
                                            class="w-3.5 h-3.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                        <span class="truncate" x-text="d.name"></span>
                                    </div>
                                    <span class="text-[9px] font-mono px-1 py-0.2 rounded shrink-0"
                                          :class="d.schools_count > 0 ? 'bg-indigo-50 text-indigo-700 font-bold' : 'text-slate-300'"
                                          x-text="d.schools_count + ' sek'">
                                    </span>
                                </label>
                            </template>
                        </div>
                        <div x-show="filteredDistrictsForDisplay.length === 0" class="py-4 text-center text-xs text-slate-400">
                            Tidak ada kecamatan yang cocok.
                        </div>
                        <p class="text-[10px] text-amber-600 font-medium mt-1">Silakan centang satu atau lebih kecamatan di atas untuk menampilkan rute sekolah.</p>
                    </div>

                    <!-- 3. Pilihan Sekolah Target Spesifik (Berdasarkan Kecamatan Terpilih) -->
                    <div x-show="selectedDistricts.length > 0" class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-700">
                                🏫 Sekolah Target Rute <span class="text-amber-600 font-normal">— Centang sekolah</span>
                            </label>
                            <span class="text-[10px] font-bold text-brand-700 bg-brand-50 px-2 py-0.5 rounded-full border border-brand-200" 
                                  x-text="selectedSchoolIds.length > 0 ? (selectedSchoolIds.length + ' dipilih') : 'Semua di Kec. Terpilih (' + filteredSchoolsForPoint.length + ')'"></span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-500 mb-1">
                            <span x-text="filteredSchoolsForPoint.length + ' sekolah berkoordinat di kecamatan terpilih'"></span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" @click="selectAllSchoolsInPoint()" class="text-brand-600 font-bold hover:underline cursor-pointer">Pilih Semua</button>
                                <span class="text-slate-300">|</span>
                                <button type="button" @click="clearSchoolSelection()" class="text-slate-500 hover:text-slate-700 hover:underline cursor-pointer">Otomatis (Semua)</button>
                            </div>
                        </div>

                        <!-- School Search within selected districts -->
                        <div class="relative mb-1.5" x-show="filteredSchoolsForPoint.length > 5">
                            <input type="text" x-model="schoolSearchInDistricts" placeholder="Cari nama sekolah target..." class="w-full pl-7 pr-3 py-1 text-[11px] rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-1 focus:ring-brand-500">
                            <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-2 top-1.5 pointer-events-none"></i>
                        </div>

                        <div class="max-h-40 overflow-y-auto space-y-1 p-2 bg-slate-50/70 rounded-xl border border-slate-200 text-xs">
                            <template x-for="s in displayedSchoolsInDistricts" :key="'sch-chk-' + s.id">
                                <label class="flex items-center justify-between p-1.5 rounded-lg border transition cursor-pointer select-none"
                                       :class="selectedSchoolIds.includes(String(s.id)) ? 'bg-amber-50 border-amber-300 text-amber-950 font-bold shadow-2xs' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    <div class="flex items-center space-x-2 truncate pr-1">
                                        <input type="checkbox" :value="String(s.id)" x-model="selectedSchoolIds" @change="previewCluster()" class="w-3.5 h-3.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500">
                                        <span class="truncate" x-text="s.name"></span>
                                    </div>
                                    <span class="text-[9px] font-mono text-slate-400 shrink-0" x-text="s.district ? s.district.name : ''"></span>
                                </label>
                            </template>
                        </div>
                        <template x-for="id in selectedSchoolIds" :key="'hidden-sch-' + id">
                            <input type="hidden" name="school_ids[]" :value="id">
                        </template>
                    </div>

                    <!-- Sequence Preview -->
                    <div x-show="routePreview && routePreview.sequence && routePreview.sequence.length > 0" class="space-y-2 pt-2 border-t border-slate-100">
                        <p class="text-[11px] font-bold text-slate-700">Urutan <span x-text="routePreview.sequence.length"></span> Sekolah Rute Mobil:</p>
                        <div class="max-h-36 overflow-y-auto space-y-1.5 pr-1">
                            <template x-for="(s, idx) in routePreview.sequence" :key="s.id">
                                <div class="p-2 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                                    <div class="flex items-center space-x-2">
                                        <span class="w-5 h-5 rounded-full bg-brand-navy text-white font-extrabold text-[10px] flex items-center justify-center" x-text="idx + 1"></span>
                                        <span class="font-bold text-slate-800 truncate max-w-[170px]" x-text="s.name"></span>
                                    </div>
                                    <span class="text-[10px] font-semibold text-brand-600" x-text="s.leg_distance + ' km'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <button type="submit" class="w-full py-3 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center justify-center space-x-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Simpan Rute Mobil Klaster</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Table Saved Routes -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Daftar Rute Mobil &amp; Kampanye Outing Terdaftar</h3>
                <p class="text-xs text-slate-500">Jadwal kunjungan outing 10 hari tim marketing ke sekolah SLTA</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-bold bg-brand-50 text-brand-700 rounded-lg">{{ $routes->total() }} Rute</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900 text-white font-bold uppercase">
                    <tr>
                        <th class="py-3.5 px-4">Nama Rute Outing</th>
                        <th class="py-3.5 px-4">Tanggal &amp; Staff</th>
                        <th class="py-3.5 px-4 text-center">Jumlah Sekolah</th>
                        <th class="py-3.5 px-4 text-center">Total Jarak</th>
                        <th class="py-3.5 px-4 text-center">Est. Durasi</th>
                        <th class="py-3.5 px-4 text-center">Navigasi Google Maps</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($routes as $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                {{ $r->name }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($r->date)->format('d M Y') }}
                                <span class="text-[10px] text-slate-400 block">PIC: {{ $r->user ? $r->user->name : '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center font-extrabold text-brand-600">
                                {{ $r->routeSchools->count() }} Sekolah
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-slate-800">
                                {{ number_format($r->total_distance, 1) }} km
                            </td>
                            <td class="py-3.5 px-4 text-center font-semibold text-slate-600">
                                {{ number_format($r->estimated_duration) }} Menit
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @php
                                    $seqStr = '';
                                    foreach($r->routeSchools as $rs) {
                                        if ($rs->school && $rs->school->latitude) {
                                            $seqStr .= '|' . $rs->school->latitude . ',' . $rs->school->longitude;
                                        }
                                    }
                                    $ubsiStr = \App\Services\SchoolScoringService::UBSI_LATITUDE . ',' . \App\Services\SchoolScoringService::UBSI_LONGITUDE;
                                    $navUrl = "https://www.google.com/maps/dir/?api=1&origin={$ubsiStr}&destination={$ubsiStr}&waypoints=" . ltrim($seqStr, '|') . "&travelmode=driving";
                                @endphp
                                <div class="inline-flex items-center gap-1.5 justify-center">
                                    <button type="button" @click="viewSavedRoute({{ json_encode([
                                        'id' => $r->id,
                                        'name' => $r->name,
                                        'total_distance_km' => (float)$r->total_distance,
                                        'estimated_duration_minutes' => (int)$r->estimated_duration,
                                        'driving_minutes' => round(((float)$r->total_distance / 30) * 60),
                                        'google_maps_driving_url' => $navUrl,
                                        'sequence' => $r->routeSchools->map(fn($rs, $idx) => [
                                            'id' => $rs->school_id,
                                            'name' => $rs->school?->name ?? 'Sekolah',
                                            'latitude' => (float)($rs->school?->latitude ?? 0),
                                            'longitude' => (float)($rs->school?->longitude ?? 0),
                                            'npsn' => $rs->school?->npsn ?? '-',
                                            'district' => $rs->school?->district ? ['name' => $rs->school->district->name] : null,
                                            'regency' => $rs->school?->regency ? ['name' => $rs->school->regency->name] : null,
                                            'leg_distance' => $rs->leg_distance ?? 0,
                                        ])->values()
                                    ]) }})" class="px-2.5 py-1 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-lg text-[10px] transition inline-flex items-center gap-1 shadow-2xs cursor-pointer" title="Tampilkan rute dan koordinat sekolah pada peta di atas">
                                        <i data-lucide="map" class="w-3 h-3 text-amber-400"></i>
                                        <span>Lihat di Peta</span>
                                    </button>

                                    <a href="{{ $navUrl }}" target="_blank" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-[10px] transition inline-flex items-center gap-1 shadow-2xs" title="Buka navigasi Google Maps">
                                        <i data-lucide="navigation-2" class="w-3 h-3"></i>
                                        <span>Google Maps</span>
                                    </a>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <form method="POST" action="{{ route('marketing-routes.destroy', $r->id) }}" onsubmit="return confirm('Hapus rute {{ $r->name }}?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada rute mobil / outing tersimpan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $routes->links() }}
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        function routeBuilderApp() {
            return {
                map: null,
                startSchoolId: 'ubsi',
                endSchoolId: 'ubsi',
                startOpen: false,
                startSearch: '',
                endOpen: false,
                endSearch: '',
                selectedRegency: '{{ $regencies->firstWhere('schools_count', '>', 0)?->id ?? ($regencies->first()?->id ?? 'ALL') }}',
                districtSearch: '',
                selectedDistricts: [],
                selectedSchoolIds: [],
                schoolSearchInDistricts: '',
                activeSavedRoute: null,
                allDistricts: {!! json_encode($districts->map(fn($d) => [
                    'id' => (string) $d->id,
                    'name' => $d->name,
                    'regency_id' => (string) $d->regency_id,
                    'regency_name' => $d->regency->name ?? '',
                    'schools_count' => (int) $d->schools_count,
                ])->values()) !!},
                allRegencies: {!! json_encode($regencies->map(fn($r) => [
                    'id' => (string) $r->id,
                    'name' => $r->name,
                    'schools_count' => (int) $r->schools_count,
                ])->values()) !!},
                allSchools: {!! json_encode($schools) !!},
                routePreview: null,
                markers: [],
                polyline: null,
                roadPolylines: [],

                get availableDistricts() {
                    if (!this.selectedRegency || this.selectedRegency === 'ALL') {
                        return this.allDistricts;
                    }
                    return this.allDistricts.filter(d => d.regency_id === String(this.selectedRegency));
                },

                get filteredDistrictsForDisplay() {
                    let list = this.availableDistricts;
                    if (!this.districtSearch) return list;
                    let q = this.districtSearch.toLowerCase().trim();
                    return list.filter(d => d.name.toLowerCase().includes(q));
                },

                get filteredSchoolsForPoint() {
                    let list = this.allSchools;
                    if (this.selectedRegency && this.selectedRegency !== 'ALL') {
                        list = list.filter(s => String(s.regency_id) === String(this.selectedRegency));
                    }
                    if (this.selectedDistricts && this.selectedDistricts.length > 0) {
                        list = list.filter(s => s.district_id && this.selectedDistricts.includes(String(s.district_id)));
                    }
                    return list;
                },

                get displayedSchoolsInDistricts() {
                    let list = this.filteredSchoolsForPoint;
                    if (!this.schoolSearchInDistricts) return list;
                    let q = this.schoolSearchInDistricts.toLowerCase().trim();
                    return list.filter(s => s.name.toLowerCase().includes(q) || (s.district && s.district.name.toLowerCase().includes(q)));
                },

                selectAllSchoolsInPoint() {
                    this.selectedSchoolIds = this.filteredSchoolsForPoint.map(s => String(s.id));
                    this.previewCluster();
                },

                clearSchoolSelection() {
                    this.selectedSchoolIds = [];
                    this.previewCluster();
                },

                onRegencyChange() {
                    this.selectedDistricts = [];
                    this.selectedSchoolIds = [];
                    this.districtSearch = '';
                    this.schoolSearchInDistricts = '';
                    this.startSchoolId = 'ubsi';
                    this.endSchoolId = 'ubsi';
                    this.activeSavedRoute = null;
                    this.previewCluster();
                },

                get startLabel() {
                    if (this.startSchoolId === 'ubsi') return '🚩 Start dari Kampus UBSI (Default)';
                    if (this.startSchoolId === 'top_potential') return '⭐ Start dari Sekolah Potensi Tertinggi';
                    let s = this.allSchools.find(sc => String(sc.id) === String(this.startSchoolId));
                    return s ? `🏫 ${s.name}` + (s.district ? ` (Kec. ${s.district.name})` : '') : '🚩 Start dari Kampus UBSI';
                },

                get endLabel() {
                    if (this.endSchoolId === 'ubsi') return '🏁 Kembali ke Kampus UBSI (Default Loop)';
                    if (this.endSchoolId === 'no_return') return '🛑 Finish di Sekolah Terakhir (Tanpa Kembali)';
                    let s = this.allSchools.find(sc => String(sc.id) === String(this.endSchoolId));
                    return s ? `🏫 ${s.name}` + (s.district ? ` (Kec. ${s.district.name})` : '') : '🏁 Kembali ke Kampus UBSI';
                },

                get startOptions() {
                    let base = [
                        { id: 'ubsi', label: '🚩 Start dari Kampus UBSI (Default)' },
                        { id: 'top_potential', label: '⭐ Start dari Sekolah Potensi Tertinggi' }
                    ];
                    let schools = this.filteredSchoolsForPoint.map(s => ({
                        id: String(s.id),
                        label: `🏫 ${s.name}` + (s.district ? ` (Kec. ${s.district.name})` : '')
                    }));
                    let all = [...base, ...schools];
                    if (!this.startSearch) return all;
                    let q = this.startSearch.toLowerCase();
                    return all.filter(o => o.label.toLowerCase().includes(q));
                },

                get endOptions() {
                    let base = [
                        { id: 'ubsi', label: '🏁 Kembali ke Kampus UBSI (Default Loop)' },
                        { id: 'no_return', label: '🛑 Finish di Sekolah Terakhir (Tanpa Kembali)' }
                    ];
                    let schools = this.filteredSchoolsForPoint.map(s => ({
                        id: String(s.id),
                        label: `🏫 ${s.name}` + (s.district ? ` (Kec. ${s.district.name})` : '')
                    }));
                    let all = [...base, ...schools];
                    if (!this.endSearch) return all;
                    let q = this.endSearch.toLowerCase();
                    return all.filter(o => o.label.toLowerCase().includes(q));
                },

                init() {
                    this.initMap();
                    this.previewCluster();
                },

                selectAllDistricts() {
                    let ids = this.availableDistricts.map(d => String(d.id));
                    this.selectedDistricts = Array.from(new Set([...this.selectedDistricts, ...ids]));
                    this.selectedSchoolIds = [];
                    this.previewCluster();
                },

                clearAllDistricts() {
                    let availIds = new Set(this.availableDistricts.map(d => String(d.id)));
                    this.selectedDistricts = this.selectedDistricts.filter(id => !availIds.has(String(id)));
                    this.selectedSchoolIds = [];
                    this.previewCluster();
                },

                initMap() {
                    this.map = L.map('route-map').setView([{{ $ubsiLat }}, {{ $ubsiLng }}], 12);

                    const googleRoadmap = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps'
                    });

                    const googleSatellite = L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps'
                    });

                    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    });

                    L.control.layers({
                        "Google Maps (Peta Jalan)": googleRoadmap,
                        "Google Maps (Satelit)": googleSatellite,
                        "OpenStreetMap": osmLayer
                    }).addTo(this.map);

                    googleRoadmap.addTo(this.map);
                },

                viewSavedRoute(savedRoute) {
                    this.activeSavedRoute = savedRoute;
                    this.routePreview = savedRoute;
                    this.drawRoute();
                    document.getElementById('route-map')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                },

                resetToBuilderMode() {
                    this.activeSavedRoute = null;
                    this.previewCluster();
                },

                previewCluster() {
                    if (this.activeSavedRoute) {
                        this.activeSavedRoute = null;
                    }

                    if (this.selectedDistricts.length === 0) {
                        this.startSchoolId = 'ubsi';
                        this.endSchoolId = 'ubsi';
                        this.selectedSchoolIds = [];
                        this.routePreview = null;
                        this.drawRoute();
                        return;
                    }

                    // Reset custom start/end school ID if no longer in filtered list
                    if (this.startSchoolId !== 'ubsi' && this.startSchoolId !== 'top_potential') {
                        let exists = this.filteredSchoolsForPoint.some(s => String(s.id) === String(this.startSchoolId));
                        if (!exists) this.startSchoolId = 'ubsi';
                    }
                    if (this.endSchoolId !== 'ubsi' && this.endSchoolId !== 'no_return') {
                        let exists = this.filteredSchoolsForPoint.some(s => String(s.id) === String(this.endSchoolId));
                        if (!exists) this.endSchoolId = 'ubsi';
                    }

                    let params = new URLSearchParams();
                    params.append('limit', '100');
                    if (this.selectedRegency && this.selectedRegency !== 'ALL') {
                        params.append('regency_id', this.selectedRegency);
                    }
                    if (this.startSchoolId) {
                        params.append('start_school_id', this.startSchoolId);
                    }
                    if (this.endSchoolId) {
                        params.append('end_school_id', this.endSchoolId);
                    }
                    this.selectedDistricts.forEach(id => {
                        params.append('district_ids[]', id);
                    });
                    this.selectedSchoolIds.forEach(id => {
                        params.append('school_ids[]', id);
                    });

                    fetch(`/api/marketing-routes/cluster-preview?${params.toString()}`)
                        .then(res => res.json())
                        .then(res => {
                            this.routePreview = res.data;
                            this.drawRoute();
                        });
                },

                drawRoute() {
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];
                    if (this.polyline) {
                        this.map.removeLayer(this.polyline);
                        this.polyline = null;
                    }
                    // Remove previous road polylines
                    if (this.roadPolylines) {
                        this.roadPolylines.forEach(p => this.map.removeLayer(p));
                    }
                    this.roadPolylines = [];

                    let ubsiLat = {{ $ubsiLat }};
                    let ubsiLng = {{ $ubsiLng }};

                    // UBSI Marker
                    let ubsiIcon = L.divIcon({
                        html: '<div class="seq-icon" style="background:#0f172a;"><span class="badge-num" style="background:#ef4444;color:#fff;">★</span><span>🚩 Start/End: Kampus UBSI</span></div>',
                        className: '',
                        iconSize: [175, 26],
                        iconAnchor: [20, 13]
                    });
                    let ubsiMarker = L.marker([ubsiLat, ubsiLng], { icon: ubsiIcon }).addTo(this.map);
                    ubsiMarker.bindPopup(`
                        <div class="p-2 text-xs">
                            <b class="text-brand-navy text-sm">Kampus UBSI Karawang</b><br>
                            <span class="text-slate-600">Jl. Banten No.1, Karawang Barat</span><br>
                            <span class="font-mono text-[11px] text-slate-500">📍 Lat: ${ubsiLat}, Lng: ${ubsiLng}</span><br>
                            <span class="text-amber-600 font-bold mt-1 inline-block">Titik Start & End Rute Mobil</span>
                        </div>
                    `);
                    ubsiMarker.bindTooltip("🚩 Kampus UBSI (Start & End)", { direction: 'top', className: 'school-map-tooltip' });
                    this.markers.push(ubsiMarker);

                    // Case 1: Route Preview (either calculated or saved)
                    if (this.routePreview && this.routePreview.sequence && this.routePreview.sequence.length > 0) {
                        let allPoints = [[ubsiLat, ubsiLng]];
                        this.routePreview.sequence.forEach((s, idx) => {
                            let lat = parseFloat(s.latitude);
                            let lng = parseFloat(s.longitude);
                            let point = [lat, lng];
                            allPoints.push(point);

                            let shortName = s.name.length > 22 ? s.name.substring(0, 20) + '…' : s.name;
                            let labelWidth = Math.max(100, Math.min(shortName.length * 7 + 36, 220));

                            let seqIcon = L.divIcon({
                                html: `<div class="seq-icon"><span class="badge-num">${idx + 1}</span><span class="truncate">${s.name}</span></div>`,
                                className: '',
                                iconSize: [labelWidth, 24],
                                iconAnchor: [15, 12]
                            });

                            let districtName = s.district ? (s.district.name || '') : '';
                            let regencyName = s.regency ? (s.regency.name || '') : '';

                            let m = L.marker(point, { icon: seqIcon }).addTo(this.map);

                            // Prominent Tooltip with school name and coordinates
                            m.bindTooltip(`
                                <div style="font-weight:bold;font-size:11px;color:#f8fafc;">
                                    #${idx + 1} ${s.name}
                                </div>
                                <div style="font-size:10px;color:#94a3b8;font-family:monospace;margin-top:2px;">
                                    📍 Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}
                                </div>
                                ${districtName ? `<div style="font-size:9px;color:#fbbf24;margin-top:1px;">Kec. ${districtName}</div>` : ''}
                            `, { direction: 'top', offset: [0, -10], className: 'school-map-tooltip' });

                            // Detailed Popup
                            m.bindPopup(`
                                <div class="p-2.5 max-w-[270px] text-xs">
                                    <div class="inline-block px-2 py-0.5 rounded-full bg-amber-500 text-slate-900 font-black text-[10px] mb-1.5">
                                        Stop #${idx + 1} Rute Outing
                                    </div>
                                    <div class="font-extrabold text-slate-900 text-sm leading-snug mb-1">
                                        ${s.name}
                                    </div>
                                    <div class="text-slate-500 text-[11px] mb-2">
                                        Kec. ${districtName} ${regencyName ? '• ' + regencyName : ''}
                                    </div>
                                    <div class="bg-slate-100 p-2 rounded-xl text-[11px] font-mono text-slate-700 mb-2 border border-slate-200">
                                        <div class="font-bold text-slate-500 text-[9px] uppercase tracking-wider mb-0.5">Koordinat GPS:</div>
                                        <div>Latitude: <b>${lat.toFixed(6)}</b></div>
                                        <div>Longitude: <b>${lng.toFixed(6)}</b></div>
                                    </div>
                                    ${s.leg_distance ? `<div class="text-brand-700 font-bold text-[11px] mb-2.5">Jarak Tempuh: ${s.leg_distance} km</div>` : ''}
                                    <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lng}" target="_blank"
                                       class="w-full text-center px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-[11px] transition inline-flex items-center justify-center gap-1 shadow-2xs">
                                        <span>Navigasi Google Maps</span> ↗
                                    </a>
                                </div>
                            `);

                            this.markers.push(m);
                        });

                        // Add return to UBSI unless 'no_return' is selected
                        if (this.endSchoolId !== 'no_return') {
                            allPoints.push([ubsiLat, ubsiLng]);
                        }

                        // Fit map bounds
                        let bounds = L.latLngBounds(allPoints.map(p => L.latLng(p[0], p[1])));
                        this.map.fitBounds(bounds, { padding: [30, 30] });

                        // Fetch actual driving route from OSRM
                        this.fetchDrivingRoute(allPoints);
                        return;
                    }

                    // Case 2: No route sequence yet, but schools are selected or districts are filtered
                    let schoolsToShow = [];
                    if (this.selectedSchoolIds.length > 0) {
                        schoolsToShow = this.allSchools.filter(s => this.selectedSchoolIds.includes(String(s.id)));
                    } else if (this.selectedDistricts.length > 0) {
                        schoolsToShow = this.filteredSchoolsForPoint;
                    }

                    if (schoolsToShow.length > 0) {
                        let allPoints = [[ubsiLat, ubsiLng]];
                        schoolsToShow.forEach((s) => {
                            let lat = parseFloat(s.latitude);
                            let lng = parseFloat(s.longitude);
                            if (!lat || !lng) return;
                            let point = [lat, lng];
                            allPoints.push(point);

                            let shortName = s.name.length > 20 ? s.name.substring(0, 18) + '…' : s.name;
                            let isChecked = this.selectedSchoolIds.includes(String(s.id));

                            let iconHtml = `
                                <div class="seq-icon" style="${isChecked ? 'background:#d97706;border-color:#fef3c7;' : 'background:#334155;'}">
                                    <span class="badge-num" style="${isChecked ? 'background:#fff;color:#d97706;' : 'background:#64748b;color:#fff;'}">🏫</span>
                                    <span class="truncate">${s.name}</span>
                                </div>
                            `;

                            let icon = L.divIcon({
                                html: iconHtml,
                                className: '',
                                iconSize: [Math.max(100, Math.min(shortName.length * 7 + 36, 220)), 24],
                                iconAnchor: [15, 12]
                            });

                            let districtName = s.district ? (s.district.name || '') : '';
                            let m = L.marker(point, { icon: icon }).addTo(this.map);

                            m.bindTooltip(`
                                <div style="font-weight:bold;font-size:11px;color:#f8fafc;">
                                    🏫 ${s.name}
                                </div>
                                <div style="font-size:10px;color:#94a3b8;font-family:monospace;margin-top:2px;">
                                    📍 Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}
                                </div>
                                ${districtName ? `<div style="font-size:9px;color:#fbbf24;margin-top:1px;">Kec. ${districtName}</div>` : ''}
                            `, { direction: 'top', offset: [0, -10], className: 'school-map-tooltip' });

                            m.bindPopup(`
                                <div class="p-2.5 max-w-[260px] text-xs">
                                    <div class="font-extrabold text-slate-900 text-sm leading-snug mb-1">
                                        🏫 ${s.name}
                                    </div>
                                    <div class="text-slate-500 text-[11px] mb-2">
                                        Kec. ${districtName}
                                    </div>
                                    <div class="bg-slate-100 p-2 rounded-xl text-[11px] font-mono text-slate-700 mb-2 border border-slate-200">
                                        <div class="font-bold text-slate-500 text-[9px] uppercase tracking-wider mb-0.5">Koordinat GPS:</div>
                                        <div>Latitude: <b>${lat.toFixed(6)}</b></div>
                                        <div>Longitude: <b>${lng.toFixed(6)}</b></div>
                                    </div>
                                    <a href="https://www.google.com/maps/search/?api=1&query=${lat},${lng}" target="_blank"
                                       class="w-full text-center px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-[11px] transition inline-flex items-center justify-center gap-1 shadow-2xs">
                                        <span>Buka di Google Maps</span> ↗
                                    </a>
                                </div>
                            `);

                            this.markers.push(m);
                        });

                        let bounds = L.latLngBounds(allPoints.map(p => L.latLng(p[0], p[1])));
                        this.map.fitBounds(bounds, { padding: [30, 30] });
                    } else {
                        this.map.setView([ubsiLat, ubsiLng], 12);
                    }
                },

                async fetchDrivingRoute(points) {
                    // OSRM expects lng,lat format
                    let coords = points.map(p => `${p[1]},${p[0]}`).join(';');
                    let url = `https://router.project-osrm.org/route/v1/driving/${coords}?overview=full&geometries=polyline`;

                    try {
                        let res = await fetch(url);
                        let data = await res.json();

                        if (data.code === 'Ok' && data.routes && data.routes.length > 0) {
                            let decoded = this.decodePolyline(data.routes[0].geometry);
                            let routeLine = L.polyline(decoded, {
                                color: '#1e3a5f',
                                weight: 5,
                                opacity: 0.85
                            }).addTo(this.map);
                            this.roadPolylines.push(routeLine);

                            this.map.fitBounds(routeLine.getBounds(), { padding: [30, 30] });
                        } else {
                            // Fallback: draw straight dashed lines if OSRM fails
                            this.polyline = L.polyline(points, { color: '#1e3a5f', weight: 4, opacity: 0.8, dashArray: '6, 6' }).addTo(this.map);
                        }
                    } catch (err) {
                        console.warn('OSRM routing failed, using straight lines:', err);
                        this.polyline = L.polyline(points, { color: '#1e3a5f', weight: 4, opacity: 0.8, dashArray: '6, 6' }).addTo(this.map);
                    }
                },

                // Decode Google/OSRM encoded polyline format
                decodePolyline(encoded) {
                    let points = [];
                    let index = 0, lat = 0, lng = 0;

                    while (index < encoded.length) {
                        let b, shift = 0, result = 0;
                        do {
                            b = encoded.charCodeAt(index++) - 63;
                            result |= (b & 0x1f) << shift;
                            shift += 5;
                        } while (b >= 0x20);
                        let dlat = ((result & 1) ? ~(result >> 1) : (result >> 1));
                        lat += dlat;

                        shift = 0;
                        result = 0;
                        do {
                            b = encoded.charCodeAt(index++) - 63;
                            result |= (b & 0x1f) << shift;
                            shift += 5;
                        } while (b >= 0x20);
                        let dlng = ((result & 1) ? ~(result >> 1) : (result >> 1));
                        lng += dlng;

                        points.push([lat / 1e5, lng / 1e5]);
                    }
                    return points;
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
