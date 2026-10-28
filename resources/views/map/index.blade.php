<x-app-layout title="Smart School GIS Mapping">
    @push('styles')
    <!-- Leaflet CSS & MarkerCluster CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        #gis-map {
            height: calc(100vh - 8rem);
            width: 100%;
            border-radius: 1.5rem;
            z-index: 1;
        }
        .custom-div-icon {
            background: transparent;
            border: none;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .leaflet-popup-content {
            margin: 0;
            width: 300px !important;
        }
    </style>
    @endpush

    <div class="relative overflow-hidden" x-data="gisMapApp()">
        <!-- Floating Header & Filters Toolbar -->
        <div class="absolute top-4 left-4 right-4 z-20 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl border border-slate-200/80 flex flex-wrap items-center justify-between gap-3">
            <!-- Search & Count -->
            <div class="flex items-center space-x-3 flex-1 min-w-[240px]">
                <div class="relative flex-1">
                    <input type="text" x-model="search" @input.debounce.300ms="fetchSchools()"
                        placeholder="Cari nama sekolah / NPSN di peta..."
                        class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 text-slate-800">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </div>
                </div>
                <span class="px-2.5 py-1.5 bg-brand-navy text-white text-xs font-extrabold rounded-xl shrink-0 shadow-sm">
                    <span x-text="schools.length">0</span> Sekolah Dipetakan
                </span>
            </div>

            <!-- Filters Bar -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <!-- 1. Kabupaten / Kota Terintegrasi -->
                <div class="flex items-center space-x-1.5 bg-indigo-50 border border-indigo-200/90 py-1 px-2.5 rounded-xl text-xs shadow-sm">
                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-indigo-600 shrink-0"></i>
                    <select x-model="regency_id" @change="onRegencyChange()" class="bg-transparent text-indigo-950 font-extrabold border-0 focus:ring-0 p-0 pr-5 text-xs cursor-pointer">
                        @foreach($regencies as $reg)
                            <option value="{{ $reg->id }}" {{ $reg->id == $defaultRegencyId ? 'selected' : '' }}>
                                {{ $reg->name }} ({{ $reg->schools_count }} Sekolah)
                            </option>
                        @endforeach
                        @if($regencies->count() > 1)
                            <option value="all">Semua Kabupaten Terintegrasi</option>
                        @endif
                    </select>
                </div>

                <!-- 2. Kecamatan (Otomatis Filter sesuai Kabupaten) -->
                <select x-model="district_id" @change="fetchSchools(true)" class="py-1.5 px-2.5 text-xs rounded-xl border border-slate-300">
                    <option value="">Semua Kecamatan</option>
                    <template x-for="d in filteredDistricts" :key="d.id">
                        <option :value="d.id" x-text="d.name"></option>
                    </template>
                </select>

                <!-- Prioritas -->
                <select x-model="priority_level" @change="fetchSchools()" class="py-1.5 px-2.5 text-xs rounded-xl border border-slate-300">
                    <option value="">Semua Prioritas</option>
                    <option value="A">Prioritas A (Merah)</option>
                    <option value="B">Prioritas B (Kuning)</option>
                    <option value="C">Prioritas C (Hijau)</option>
                </select>

                <!-- Jenjang -->
                <select x-model="school_type_id" @change="fetchSchools()" class="py-1.5 px-2.5 text-xs rounded-xl border border-slate-300">
                    <option value="">Semua Jenjang</option>
                    @foreach($schoolTypes as $st)
                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                    @endforeach
                </select>

                <!-- Status -->
                <select x-model="status" @change="fetchSchools()" class="py-1.5 px-2.5 text-xs rounded-xl border border-slate-300">
                    <option value="">Semua Status</option>
                    <option value="negeri">NEGERI</option>
                    <option value="swasta">SWASTA</option>
                </select>

                <!-- Toggle Panel Button -->
                <button @click="panelOpen = !panelOpen" class="px-3 py-1.5 bg-slate-900 text-white rounded-xl font-bold flex items-center space-x-1 hover:bg-slate-800 transition cursor-pointer">
                    <i data-lucide="list" class="w-4 h-4"></i>
                    <span class="hidden sm:inline" x-text="panelOpen ? 'Sembunyikan Panel' : 'Tampilkan Panel'"></span>
                </button>
            </div>
        </div>

        <!-- Main GIS Map Container -->
        <div id="gis-map" class="shadow-2xl border border-slate-200"></div>

        <!-- Floating Legend Box (Bottom Left) -->
        <div class="absolute bottom-6 left-6 z-20 bg-white/95 backdrop-blur-md p-3.5 rounded-2xl shadow-xl border border-slate-200 text-xs hidden sm:block">
            <p class="font-extrabold text-slate-800 uppercase tracking-wider text-[10px] mb-2">Legenda Prioritas Sekolah</p>
            <div class="space-y-1.5">
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-red-500 shadow-sm"></span>
                    <span class="font-bold text-slate-700">Prioritas A (Score ≥ 80)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-amber-400 shadow-sm"></span>
                    <span class="font-bold text-slate-700">Prioritas B (Score 65 - 79)</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 shadow-sm"></span>
                    <span class="font-bold text-slate-700">Prioritas C (Score < 65)</span>
                </div>
            </div>
        </div>

        <!-- Right Sliding School Leaderboard Panel -->
        <div x-show="panelOpen" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            class="absolute top-20 right-4 bottom-6 z-20 w-80 lg:w-96 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden">
            
            <div class="p-4 border-b border-slate-100 bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider">Hasil Filter Map</h3>
                    <p class="text-[10px] text-slate-300"><span x-text="schools.length">0</span> Sekolah Terurut Skor</p>
                </div>
                <button @click="panelOpen = false" class="text-slate-400 hover:text-white">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-slate-100 p-2">
                <template x-for="(s, idx) in schools" :key="s.id">
                    <div @click="focusSchool(s)" class="p-3 hover:bg-slate-50 rounded-xl transition cursor-pointer flex items-start justify-between">
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex items-center space-x-1.5">
                                <span class="font-bold text-xs text-slate-900 truncate" x-text="s.name"></span>
                            </div>
                            <p class="text-[10px] text-slate-500 mt-0.5" x-text="s.type + ' • ' + s.status + ' • ' + s.district"></p>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="text-[10px] font-bold text-brand-600" x-text="'Skor: ' + s.potential_score"></span>
                                <span class="text-[10px] text-slate-400" x-text="s.grade_12_count + ' Siswa XII'"></span>
                            </div>
                        </div>

                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-full border shrink-0"
                            :class="{
                                'bg-red-100 text-red-700 border-red-200': s.priority_level === 'A',
                                'bg-amber-100 text-amber-700 border-amber-200': s.priority_level === 'B',
                                'bg-emerald-100 text-emerald-700 border-emerald-200': s.priority_level === 'C'
                            }"
                            x-text="'Prio ' + s.priority_level">
                        </span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        function gisMapApp() {
            return {
                map: null,
                markers: [],
                schools: [],
                allDistricts: @json($districts),
                regency_id: '{{ $defaultRegencyId }}',
                district_id: '',
                search: '',
                priority_level: '',
                school_type_id: '',
                status: '',
                panelOpen: true,

                get filteredDistricts() {
                    if (!this.regency_id || this.regency_id === 'all') {
                        return this.allDistricts;
                    }
                    return this.allDistricts.filter(d => d.regency_id == this.regency_id);
                },

                onRegencyChange() {
                    let currentValid = this.filteredDistricts.some(d => d.id == this.district_id);
                    if (!currentValid) {
                        this.district_id = '';
                    }
                    this.fetchSchools(true);
                },

                init() {
                    this.initMap();
                    this.fetchSchools(true);
                },

                initMap() {
                    // Center of Karawang, West Java
                    this.map = L.map('gis-map').setView([-6.3073, 107.3006], 11);

                    // Google Maps Tile Layers
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

                    const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
                        maxZoom: 20,
                        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                        attribution: '&copy; Google Maps'
                    });

                    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap'
                    });

                    const baseMaps = {
                        "Google Maps (Peta Jalan)": googleRoadmap,
                        "Google Maps (Satelit Foto)": googleSatellite,
                        "Google Maps (Hibrid Satelit + Jalan)": googleHybrid,
                        "OpenStreetMap": osmLayer
                    };

                    // Add Layer Switcher Control to top right of map
                    L.control.layers(baseMaps).addTo(this.map);
                    
                    // Set Google Maps Peta Jalan as default map layer
                    googleRoadmap.addTo(this.map);
                },

                fetchSchools(fit = false) {
                    let params = new URLSearchParams({
                        regency_id: this.regency_id,
                        district_id: this.district_id,
                        search: this.search,
                        priority_level: this.priority_level,
                        school_type_id: this.school_type_id,
                        status: this.status,
                    });

                    fetch(`/api/map/schools?${params}`)
                        .then(res => res.json())
                        .then(res => {
                            this.schools = res.data;
                            this.renderMarkers(fit);
                        });
                },

                renderMarkers(fit = false) {
                    // Clear existing markers
                    this.markers.forEach(m => this.map.removeLayer(m));
                    this.markers = [];

                    this.schools.forEach(s => {
                        let color = s.priority_level === 'A' ? '#ef4444' : (s.priority_level === 'B' ? '#f59e0b' : '#10b981');
                        
                        let iconHtml = `
                            <div style="background-color: ${color}; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px; border: 2px solid white; box-shadow: 0 4px 10px rgba(0,0,0,0.3);">
                                ${s.priority_level}
                            </div>
                        `;

                        let customIcon = L.divIcon({
                            html: iconHtml,
                            className: 'custom-div-icon',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14]
                        });

                        let marker = L.marker([s.latitude, s.longitude], { icon: customIcon }).addTo(this.map);

                        // Popup content
                        let popupHtml = `
                            <div class="p-4 bg-white text-slate-800 font-sans">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-slate-100 text-slate-700">${s.type} &bull; ${s.status}</span>
                                    <span class="px-2 py-0.5 text-[9px] font-extrabold rounded bg-amber-100 text-amber-800">Skor: ${s.potential_score}</span>
                                </div>
                                <h4 class="font-bold text-sm text-slate-900 leading-tight">${s.name}</h4>
                                <p class="text-[11px] text-slate-500 mt-1">Kec. ${s.district} &bull; ${s.regency || ''}</p>
                                <p class="text-[10px] text-slate-400 mt-0.5">${s.grade_12_count} Siswa XII &bull; Kunjungan: ${s.last_visit}</p>
                                
                                <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-3 gap-1">
                                    <a href="/schools/${s.id}" class="px-2 py-1.5 bg-brand-navy hover:bg-slate-900 text-white text-center text-[10px] font-bold rounded-lg transition">
                                        Detail
                                    </a>
                                    <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(s.name + ', ' + (s.district ? s.district + ', ' : '') + (s.regency || 'Karawang'))}" target="_blank" class="px-2 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-center text-[10px] font-bold rounded-lg transition">
                                        GMaps
                                    </a>
                                    <a href="${s.latitude && s.longitude ? `https://waze.com/ul?ll=${s.latitude},${s.longitude}&navigate=yes` : `https://waze.com/ul?q=${encodeURIComponent(s.name + ', Karawang')}&navigate=yes`}" target="_blank" class="px-2 py-1.5 bg-cyan-600 hover:bg-cyan-700 text-white text-center text-[10px] font-bold rounded-lg transition">
                                        Waze 🚗
                                    </a>
                                </div>
                            </div>
                        `;

                        marker.bindPopup(popupHtml);
                        marker.schoolId = s.id;
                        this.markers.push(marker);
                    });

                    // Fit map bounds to current markers if fit requested
                    if (fit && this.markers.length > 0) {
                        let group = new L.featureGroup(this.markers);
                        this.map.fitBounds(group.getBounds().pad(0.08));
                    }
                },

                focusSchool(s) {
                    this.map.flyTo([s.latitude, s.longitude], 15, { duration: 1.2 });
                    let targetMarker = this.markers.find(m => m.schoolId === s.id);
                    if (targetMarker) {
                        setTimeout(() => targetMarker.openPopup(), 1200);
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
