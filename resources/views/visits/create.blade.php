<x-app-layout title="Catat Hasil Kunjungan Lapangan">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Form Catat Hasil Kunjungan Lapangan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Isi data lengkap penerima PIC, target sosialisasi, estimasi siswa &amp; metode kegiatan</p>
        </div>
        <a href="{{ route('visits.index') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition flex items-center space-x-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke List Kunjungan</span>
        </a>
    </div>

    <div class="max-w-4xl" x-data="{
        districts: {{ json_encode($districts) }},
        allSchools: {{ json_encode($schools) }},
        selectedDistrictId: '{{ old('district_id', '') }}',
        selectedSchoolId: '{{ old('school_id', '') }}',
        selectedMethod: '{{ old('socialization_method', '') }}',
        grade12ClassCount: '{{ old('grade_12_class_count', '') }}',
        studentsPerClass: '{{ old('students_per_class', '') }}',
        checkinLatitude: '',
        checkinLongitude: '',
        locationReady: false,
        locationStatus: 'Check-in GPS diperlukan saat berada maksimal 500 meter dari sekolah.',
        captureCheckIn() {
            const school = this.allSchools.find(item => String(item.id) === String(this.selectedSchoolId));
            if (!school || school.latitude === null || school.longitude === null || school.latitude === '' || school.longitude === '') {
                this.locationReady = false;
                this.locationStatus = 'Sekolah belum memiliki koordinat. Lengkapi koordinat sekolah sebelum check-in.';
                return;
            }
            if (!navigator.geolocation) {
                this.locationReady = false;
                this.locationStatus = 'Browser ini tidak mendukung GPS.';
                return;
            }

            this.locationReady = false;
            this.locationStatus = 'Mengambil lokasi GPS...';
            navigator.geolocation.getCurrentPosition(position => {
                const toRadians = value => value * Math.PI / 180;
                const earthRadius = 6371000;
                const lat1 = toRadians(Number(school.latitude));
                const lat2 = toRadians(position.coords.latitude);
                const deltaLat = lat2 - lat1;
                const deltaLng = toRadians(position.coords.longitude - Number(school.longitude));
                const a = Math.sin(deltaLat / 2) ** 2 +
                    Math.cos(lat1) * Math.cos(lat2) * Math.sin(deltaLng / 2) ** 2;
                const distance = 2 * earthRadius * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

                if (distance > 500) {
                    this.checkinLatitude = '';
                    this.checkinLongitude = '';
                    this.locationStatus = `Lokasi berjarak ${Math.round(distance)} m dari sekolah. Check-in hanya tersedia dalam radius 500 m.`;
                    return;
                }

                this.checkinLatitude = position.coords.latitude;
                this.checkinLongitude = position.coords.longitude;
                this.locationReady = true;
                this.locationStatus = `Lokasi valid, ${Math.round(distance)} m dari sekolah.`;
            }, error => {
                this.locationReady = false;
                this.locationStatus = error.code === error.PERMISSION_DENIED
                    ? 'Izin GPS ditolak. Aktifkan izin lokasi untuk check-in.'
                    : 'Lokasi GPS tidak tersedia. Periksa sinyal dan coba lagi.';
            }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
        },
        submitVisit(event) {
            if (!this.locationReady) {
                this.locationStatus = 'Ambil dan validasi GPS sekolah sebelum menyimpan laporan.';
                return;
            }
            event.target.submit();
        },
        schoolChanged() {
            this.checkinLatitude = '';
            this.checkinLongitude = '';
            this.locationReady = false;
            this.locationStatus = 'Sekolah berubah. Ambil ulang GPS dalam radius 500 meter.';
        },
        get filteredSchools() {
            if (!this.selectedDistrictId) return this.allSchools;
            return this.allSchools.filter(s => s.district_id == this.selectedDistrictId);
        },
        get totalStudents() {
            const classes = parseInt(this.grade12ClassCount) || 0;
            const students = parseInt(this.studentsPerClass) || 0;
            return classes * students;
        }
    }">
        <form method="POST" action="{{ route('visits.store') }}" enctype="multipart/form-data" @submit.prevent="submitVisit($event)" class="space-y-6">
            @csrf
            <input type="hidden" name="latitude" x-model="checkinLatitude">
            <input type="hidden" name="longitude" x-model="checkinLongitude">

            <!-- Form Card 1: Filter Wilayah & Informasi Sekolah Utama -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="building-2" class="w-4 h-4 text-brand-600"></i>
                    <span>Informasi Sekolah Tujuan &amp; Tim Marketing</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Filter Kecamatan Sekolah <span class="text-amber-600 font-normal">(Pilih Kecamatan Terlebih Dahulu)</span></label>
                        <select x-model="selectedDistrictId" @change="selectedSchoolId = ''; schoolChanged()" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-700 bg-amber-50/40 focus:border-brand-500">
                            <option value="">-- Semua Kecamatan (Filter Wilayah) --</option>
                            <template x-for="d in districts" :key="d.id">
                                <option :value="d.id" x-text="'Kecamatan ' + d.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Sekolah Kunjungan SLTA <span class="text-red-500">*</span></label>
                        <select name="school_id" x-model="selectedSchoolId" @change="schoolChanged()" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                            <option value="">-- Pilih Sekolah Tujuan --</option>
                            <template x-for="s in filteredSchools" :key="s.id">
                                <option :value="s.id" x-text="s.name + (s.district ? ' (Kec. ' + s.district.name + ')' : '')"></option>
                            </template>
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1" x-show="selectedDistrictId">Menampilkan <span class="font-bold text-brand-600" x-text="filteredSchools.length"></span> sekolah di kecamatan terpilih.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Staf Marketing PIC <span class="text-red-500">*</span></label>
                        <select name="user_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                            @foreach($marketingUsers as $u)
                                <option value="{{ $u->id }}" {{ old('user_id', auth()->id()) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Visit Lapangan <span class="text-red-500">*</span></label>
                        <input type="date" name="visit_date" value="{{ old('visit_date', date('Y-m-d')) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600">
                    </div>
                </div>
            </div>

            <!-- Form Card 2: Identitas Penerima (Field 1 & 2) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-amber-500"></i>
                    <span>Detail Penerima Kunjungan</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">1. Nama Penerima Beserta Gelarnya <span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Contoh: Drs. H. Mulyadi, M.Pd." required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">2. Jabatan Penerima <span class="text-red-500">*</span></label>
                        <input type="text" name="recipient_position" value="{{ old('recipient_position') }}" placeholder="Contoh: Kepala Sekolah / Guru BK / Wakasek Humas" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                    </div>
                </div>
            </div>

            <!-- Form Card 3: Jumlah Siswa & Rombel (Field 3 & 7) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                    <span>Estimasi Siswa Kelas 12</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">3. Jumlah Kelas 12 <span class="text-red-500">*</span></label>
                        <input type="number" name="grade_12_class_count" x-model="grade12ClassCount" min="1" placeholder="Contoh: 6" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">7. Rata-Rata Jumlah Siswa Per Kelas <span class="text-red-500">*</span></label>
                        <input type="number" name="students_per_class" x-model="studentsPerClass" min="1" placeholder="Contoh: 36" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                    </div>
                </div>

                <div class="mt-4 p-3 bg-purple-50 rounded-xl border border-purple-200 flex items-center justify-between" x-show="totalStudents > 0" x-transition>
                    <span class="font-bold text-purple-800 text-xs">Total Estimasi Siswa Kelas 12:</span>
                    <span class="text-lg font-extrabold text-purple-900"><span x-text="totalStudents"></span> Siswa</span>
                </div>
            </div>

            <!-- Form Card 4: Target Waktu & Jadwal Sosialisasi (Field 4, 5, 6) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-emerald-600"></i>
                    <span>Target Jadwal Waktu Sosialisasi</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">4. Target Bulan &amp; Tahun <span class="text-red-500">*</span></label>
                        <input type="text" name="target_month_year" value="{{ old('target_month_year') }}" placeholder="Contoh: Oktober 2026" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">5. Target Tanggal <span class="text-amber-600 font-normal">(Opsional)</span></label>
                        <input type="date" name="target_date" value="{{ old('target_date') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">6. Target Waktu Sosialisasi <span class="text-red-500">*</span></label>
                        <input type="text" name="target_time" value="{{ old('target_time') }}" placeholder="Contoh: 08:30 WIB" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="map-pin-check" class="w-4 h-4 text-emerald-600"></i>
                    <span>Check-in Lokasi &amp; Dokumentasi</span>
                </h3>
                <div class="space-y-3 text-xs">
                    <p class="text-slate-600">Check-in hanya dapat disimpan jika GPS berada maksimal 500 meter dari koordinat sekolah tujuan.</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" @click="captureCheckIn()" :disabled="!selectedSchoolId" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-300 text-white font-bold rounded-xl flex items-center gap-2">
                            <i data-lucide="locate-fixed" class="w-4 h-4"></i>
                            Ambil GPS Check-in
                        </button>
                        <span class="font-semibold" :class="locationReady ? 'text-emerald-700' : 'text-amber-700'" x-text="locationStatus" aria-live="polite"></span>
                    </div>
                    <div>
                        <label for="visit_photo" class="block font-bold text-slate-700 mb-1">Foto dokumentasi / Berkas Lampiran (opsional)</label>
                        <input id="visit_photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp,application/pdf,.rar,.zip,.pdf,.doc,.docx" class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700">
                        <p class="text-[10px] text-slate-500 mt-1">Dapat berupa Foto (JPG, PNG), Dokumen (PDF, Word), atau Arsip (RAR, ZIP); maksimal 50 MB.</p>
                    </div>
                </div>
            </div>

            <!-- Form Card 5: Metode Sosialisasi (Field 8 & Dynamic Input) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="presentation" class="w-4 h-4 text-rose-500"></i>
                    <span>Metode Kegiatan Sosialisasi</span>
                </h3>

                <div class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-900 mb-1">8. Metode Sosialisasi <span class="text-red-500">*</span></label>
                        <select name="socialization_method" x-model="selectedMethod" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900">
                            <option value="">-- Pilih Metode Sosialisasi --</option>
                            <option value="Sosialisasi di Kelas">a. Sosialisasi di Kelas</option>
                            <option value="Sosialisasi di Aula">b. Sosialisasi di Aula</option>
                            <option value="Workshop Digital Kreatif">c. Workshop Digital Kreatif</option>
                            <option value="Edufair">d. Edufair</option>
                            <option value="Lainnya">e. Lainnya (Input Kegiatan Khusus)</option>
                        </select>
                    </div>

                    <!-- Dynamic Input Field if 'Lainnya' is selected -->
                    <div x-show="selectedMethod === 'Lainnya'" x-transition class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                        <label class="block font-bold text-amber-900 mb-1">Input Nama Kegiatan Khusus / Lainnya <span class="text-red-500">*</span></label>
                        <input type="text" name="other_activity_name" value="{{ old('other_activity_name') }}" placeholder="Tuliskan nama kegiatan sosialisasi khusus..." class="w-full px-3 py-2.5 rounded-xl border border-amber-300 bg-white font-semibold text-slate-900">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Hasil Visit <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                                <option value="berhasil" selected>Berhasil (Diizinkan Sosialisasi)</option>
                                <option value="tidak_bertemu">Tidak Bertemu (Jadwal Ulang)</option>
                                <option value="tidak_berhasil">Tidak Berhasil (Ditolak)</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan Visit</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Catatan respon penerima..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3">
                <a href="{{ route('visits.index') }}" class="px-5 py-3 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs rounded-xl transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-3 bg-brand-navy hover:bg-slate-900 text-white font-bold text-xs rounded-xl shadow-lg transition flex items-center space-x-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Simpan Hasil Kunjungan Lapangan</span>
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
