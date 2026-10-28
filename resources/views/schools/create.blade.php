<x-app-layout title="Tambah Sekolah Baru">
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        #picker-map {
            height: 260px;
            width: 100%;
            border-radius: 1rem;
            z-index: 1;
        }
    </style>
    @endpush

    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">Tambah Data Sekolah Baru</h1>
                <p class="text-xs text-slate-500">Masukkan informasi lengkap sekolah SLTA di Karawang</p>
            </div>
            <a href="{{ route('schools.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl text-xs flex items-center space-x-1.5 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Kembali</span>
            </a>
        </div>

        <form method="POST" action="{{ route('schools.store') }}" class="space-y-6">
            @csrf

            <!-- Form Card 1: Identitas Sekolah -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-800 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="building" class="w-4 h-4 text-brand-600"></i>
                    <span>Identitas Utama Sekolah</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">NPSN <span class="text-red-500">*</span></label>
                        <input type="text" name="npsn" value="{{ old('npsn') }}" required
                            class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                            placeholder="Contoh: 20237453">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Sekolah <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"
                            placeholder="Contoh: SMAN 1 CIAMPEL">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenjang Sekolah</label>
                        <select name="school_type_id" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">
                            <option value="">-- Pilih Jenjang --</option>
                            @foreach($schoolTypes as $st)
                                <option value="{{ $st->id }}" {{ old('school_type_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Kelembagaan <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">
                            <option value="negeri" {{ old('status') == 'negeri' ? 'selected' : '' }}>NEGERI</option>
                            <option value="swasta" {{ old('status') == 'swasta' ? 'selected' : '' }}>SWASTA</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Akreditasi</label>
                        <select name="accreditation_id" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">
                            <option value="">-- Pilih Akreditasi --</option>
                            @foreach($accreditations as $acc)
                                <option value="{{ $acc->id }}" {{ old('accreditation_id') == $acc->id ? 'selected' : '' }}>{{ $acc->name }} ({{ $acc->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Operasional Sekolah</label>
                        <select name="status_school" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">
                            <option value="aktif" {{ old('status_school', 'aktif') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="tidak_aktif" {{ old('status_school') == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Form Card 2: Wilayah & Lokasi GIS (Peta Picker Koordinat) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="map-pin" class="w-4 h-4 text-amber-500"></i>
                        <span>Wilayah &amp; Input Koordinat GIS Sekolah</span>
                    </h3>
                    <button type="button" onclick="getCurrentLocation()" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[11px] rounded-lg border border-amber-200 transition flex items-center space-x-1">
                        <i data-lucide="locate-fixed" class="w-3.5 h-3.5 text-amber-600"></i>
                        <span>Ambil Lokasi GPS Saya</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kabupaten/Kota</label>
                        <select name="regency_id" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">
                            @foreach($regencies as $r)
                                <option value="{{ $r->id }}" {{ old('regency_id', 1) == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kecamatan</label>
                        <select name="district_id" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">
                            <option value="">-- Pilih Kecamatan --</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" {{ old('district_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode Pos</label>
                        <input type="text" name="postal_code" value="{{ old('postal_code') }}" placeholder="Contoh: 41361"
                            class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="2" placeholder="Jalan, RT/RW, Dusun..." class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500">{{ old('address') }}</textarea>
                </div>

                <!-- Input Koordinat & Leaflet Pin Picker Map -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (Lintang) <span class="text-amber-600 font-normal">(Klik peta atau isi angka)</span></label>
                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude', '-6.3054') }}" placeholder="-6.3123456" required
                            class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 font-mono font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (Bujur) <span class="text-amber-600 font-normal">(Klik peta atau isi angka)</span></label>
                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude', '107.2995') }}" placeholder="107.3123456" required
                            class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 font-mono font-semibold focus:border-brand-500">
                    </div>
                </div>

                <div class="mb-2">
                    <p class="text-[11px] text-slate-500 mb-2">Petunjuk: Klik pada peta di bawah ini atau geser pin merah untuk memasukkan koordinat lokasi fisik sekolah secara presisi.</p>
                    <div id="picker-map" class="shadow-inner border border-slate-200"></div>
                </div>
            </div>

            <!-- Form Card 3: Statistik Siswa & Kontak -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-800 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                    <span>Data Siswa &amp; Kontak Resmi</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Total Siswa</label>
                        <input type="number" name="student_count" value="{{ old('student_count', 0) }}" min="0" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Siswa Kelas X</label>
                        <input type="number" name="grade_10_count" value="{{ old('grade_10_count', 0) }}" min="0" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Siswa Kelas XI</label>
                        <input type="number" name="grade_11_count" value="{{ old('grade_11_count', 0) }}" min="0" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Siswa Kelas XII</label>
                        <input type="number" name="grade_12_count" value="{{ old('grade_12_count', 0) }}" min="0" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 font-bold text-brand-600">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Telepon Sekolah</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="0267-XXXXXX" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Sekolah</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="info@sekolah.sch.id" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Website</label>
                        <input type="text" name="website" value="{{ old('website') }}" placeholder="www.sekolah.sch.id" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300">
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('schools.index') }}" class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">Batal</a>
                <button type="submit" class="px-6 py-3 bg-brand-navy hover:bg-brand-900 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center space-x-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Simpan Sekolah Baru</span>
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        let latInput = document.getElementById('latitude');
        let lngInput = document.getElementById('longitude');

        let initLat = parseFloat(latInput.value) || -6.3054;
        let initLng = parseFloat(lngInput.value) || 107.2995;

        let pickerMap = L.map('picker-map').setView([initLat, initLng], 13);

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
        }).addTo(pickerMap);

        googleRoadmap.addTo(pickerMap);

        let marker = L.marker([initLat, initLng], { draggable: true }).addTo(pickerMap);

        function updateInputs(lat, lng) {
            latInput.value = lat.toFixed(7);
            lngInput.value = lng.toFixed(7);
        }

        marker.on('dragend', function (e) {
            let position = marker.getLatLng();
            updateInputs(position.lat, position.lng);
        });

        pickerMap.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng.lat, e.latlng.lng);
        });

        latInput.addEventListener('input', function() {
            let lat = parseFloat(this.value);
            let lng = parseFloat(lngInput.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                pickerMap.panTo([lat, lng]);
            }
        });

        lngInput.addEventListener('input', function() {
            let lat = parseFloat(latInput.value);
            let lng = parseFloat(this.value);
            if (!isNaN(lat) && !isNaN(lng)) {
                marker.setLatLng([lat, lng]);
                pickerMap.panTo([lat, lng]);
            }
        });

        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    let lat = pos.coords.latitude;
                    let lng = pos.coords.longitude;
                    marker.setLatLng([lat, lng]);
                    pickerMap.setView([lat, lng], 15);
                    updateInputs(lat, lng);
                }, function() {
                    alert("Gagal mengambil posisi GPS dari browser Anda.");
                });
            } else {
                alert("Browser Anda tidak mendukung geolokasi GPS.");
            }
        }
    </script>
    @endpush
</x-app-layout>
