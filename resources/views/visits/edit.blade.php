<x-app-layout title="Edit Data Kunjungan Lapangan">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Edit Data Kunjungan Lapangan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Perbarui data kunjungan marketing ke sekolah SLTA</p>
        </div>
        <a href="{{ route('visits.index') }}" class="px-4 py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition flex items-center space-x-1.5">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Kembali ke List Kunjungan</span>
        </a>
    </div>

    <div class="max-w-4xl" @school-selected.window="selectedSchoolId = $event.detail.schoolId; schoolChanged()" x-data="{
        districts: {{ json_encode($districts) }},
        allSchools: {{ json_encode($schools) }},
        selectedDistrictId: '{{ old('district_id', $visit->school->district_id ?? '') }}',
        selectedSchoolId: '{{ old('school_id', $visit->school_id) }}',
        selectedMethod: '{{ old('socialization_method', $visit->socialization_method) }}',
        grade12ClassCount: '{{ old('grade_12_class_count', $visit->grade_12_class_count) }}',
        studentsPerClass: '{{ old('students_per_class', $visit->students_per_class) }}',
        checkinLatitude: '',
        checkinLongitude: '',
        locationReady: false,
        locationStatus: 'Jika sekolah diubah, ambil GPS baru dalam radius 500 meter.',
        captureCheckIn() {
            const school = this.allSchools.find(item => String(item.id) === String(this.selectedSchoolId));
            if (!school || school.latitude === null || school.longitude === null || school.latitude === '' || school.longitude === '') {
                this.locationReady = false;
                this.locationStatus = 'Sekolah belum memiliki koordinat. Lengkapi koordinat sebelum check-in.';
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
        <form method="POST" action="{{ route('visits.update', $visit->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="latitude" x-model="checkinLatitude">
            <input type="hidden" name="longitude" x-model="checkinLongitude">

            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-emerald-600"></i>
                        <span>Dokumentasi Kunjungan</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Tersimpan di Data Kunjungan</span>
                </div>

                @if($visit->photo)
                    @php
                        $ext = strtolower(pathinfo($visit->photo, PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                        $fileUrl = asset('storage/' . $visit->photo);
                    @endphp
                    <div class="mb-4 p-3.5 bg-slate-50 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($isImage)
                                <a href="{{ $fileUrl }}" target="_blank" title="Klik untuk memperbesar" class="shrink-0 group relative block">
                                    <img src="{{ $fileUrl }}" alt="Dokumentasi Saat Ini" class="w-16 h-16 object-cover rounded-xl border border-slate-300 shadow-xs group-hover:scale-105 transition">
                                </a>
                            @else
                                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-extrabold text-xs uppercase shrink-0">
                                    {{ $ext ?: 'DOC' }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-800 truncate">Berkas Dokumentasi Tersimpan</p>
                                <p class="text-[10px] text-slate-400 font-mono truncate">{{ basename($visit->photo) }}</p>
                                <a href="{{ $fileUrl }}" target="_blank" class="text-xs text-brand-600 font-bold hover:underline inline-flex items-center gap-1 mt-1">
                                    <i data-lucide="external-link" class="w-3 h-3"></i>
                                    <span>Buka / Lihat Berkas Penuh ↗</span>
                                </a>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full shrink-0">
                            Tersedia
                        </span>
                    </div>
                @endif

                <label for="visit_photo" class="block text-xs font-bold text-slate-700 mb-1">
                    {{ $visit->photo ? 'Unggah berkas baru untuk mengganti dokumentasi saat ini (opsional)' : 'Unggah berkas foto/dokumen dokumentasi (opsional)' }}
                </label>
                <input id="visit_photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp,application/pdf,.rar,.zip,.pdf,.doc,.docx" class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 transition">
                <p class="text-[10px] text-slate-500 mt-1">Dapat berupa Foto (JPG, PNG, WEBP), Dokumen (PDF, Word), atau Arsip (RAR, ZIP); maksimal 50 MB.</p>
                @error('photo')
                    <span class="text-xs text-red-600 mt-1 block font-semibold">{{ $message }}</span>
                @enderror
            </div>

            {{-- Form Card 1: Informasi Sekolah & Tim Marketing --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="building-2" class="w-4 h-4 text-brand-600"></i>
                        <span>Informasi Sekolah Tujuan &amp; Tim Marketing</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Sinkronisasi Data Dapodik &amp; Kemenag</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label for="district_filter" class="block font-bold text-slate-700 mb-1">
                            Filter Kecamatan Sekolah <span class="text-amber-600 font-normal">(Pilih Wilayah Terlebih Dahulu)</span>
                        </label>
                        <select id="district_filter" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-700 bg-amber-50/40 focus:border-brand-500">
                            <option value="">-- Semua Kecamatan (Filter Wilayah) --</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" {{ old('district_filter', $visit->school->district_id ?? '') == $d->id ? 'selected' : '' }}>
                                    Kecamatan {{ $d->name }} ({{ $d->schools_count }} Sekolah)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="school_id" class="block font-bold text-slate-700 mb-1">
                            Sekolah Kunjungan SLTA <span class="text-red-500">*</span> <span class="text-slate-400 font-normal">(Ketik / Cari Select2)</span>
                        </label>
                        <select name="school_id" id="school_id" required class="select2-school w-full">
                            <option value="">-- Cari &amp; Pilih Sekolah (Find) --</option>
                            @foreach($schools as $s)
                                <option value="{{ $s->id }}" 
                                    data-district="{{ $s->district_id }}"
                                    data-district-name="{{ $s->district?->name }}"
                                    data-headmaster="{{ $s->headmaster_name }}"
                                    data-npsn="{{ $s->npsn }}"
                                    data-type="{{ $s->schoolType?->name }}"
                                    data-prio="{{ $s->priority_level }}"
                                    {{ old('school_id', $visit->school_id) == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }} (Kec. {{ $s->district ? $s->district->name : '-' }}) {{ $s->npsn ? '- NPSN ' . $s->npsn : '' }}
                                </option>
                            @endforeach
                        </select>
                        <p id="school_count_info" class="text-[10px] text-slate-400 mt-1">
                            Menampilkan {{ $schools->count() }} sekolah dari integrasi Dapodik &amp; Kemenag.
                        </p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft mt-4">
                    <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                        <i data-lucide="map-pin-check" class="w-4 h-4 text-emerald-600"></i>
                        Check-in Lokasi Sekolah
                    </h3>
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <button type="button" @click="captureCheckIn()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center gap-2">
                            <i data-lucide="locate-fixed" class="w-4 h-4"></i>
                            Ambil GPS Check-in
                        </button>
                        <span class="font-semibold" :class="locationReady ? 'text-emerald-700' : 'text-amber-700'" x-text="locationStatus" aria-live="polite"></span>
                        @if($visit->latitude && $visit->longitude)
                            <span class="w-full text-[10px] text-slate-500">Koordinat tersimpan: {{ $visit->latitude }}, {{ $visit->longitude }}. Check-in baru hanya diwajibkan jika sekolah diganti.</span>
                        @endif
                    </div>
                    @error('latitude')
                        <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Staf Marketing PIC <span class="text-red-500">*</span></label>
                        <select name="user_id" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                            @foreach($marketingUsers as $u)
                                <option value="{{ $u->id }}" {{ old('user_id', $visit->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Visit Lapangan <span class="text-red-500">*</span></label>
                        <input type="date" name="visit_date" value="{{ old('visit_date', \Carbon\Carbon::parse($visit->visit_date)->format('Y-m-d')) }}" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600 focus:border-brand-500">
                    </div>
                </div>
            </div>

            {{-- Form Card 2: Identitas Penerima & Kepala Sekolah --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="user-check" class="w-4 h-4 text-amber-500"></i>
                        <span>Detail Penerima Kunjungan &amp; Kepala Sekolah</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Kontak PIC &amp; Pimpinan Sekolah</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            Nama Kepala Sekolah <span class="text-slate-400 font-normal">(Opsional / Otomatis)</span>
                        </label>
                        <input type="text" id="headmaster_name" name="headmaster_name" value="{{ old('headmaster_name', $visit->headmaster_name ?? $visit->school?->headmaster_name) }}" placeholder="Contoh: Dr. H. Ahmad Sudrajat, M.M." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            1. Nama Penerima Beserta Gelarnya <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $visit->recipient_name) }}" placeholder="Contoh: Drs. H. Mulyadi, M.Pd." required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            2. Jabatan Penerima <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_position" value="{{ old('recipient_position', $visit->recipient_position) }}" placeholder="Contoh: Kepala Sekolah / Guru BK / Wakasek Humas" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            Nomor HP / WhatsApp Penerima <span class="text-amber-600 font-normal">(Untuk Follow-Up)</span>
                        </label>
                        <input type="tel" name="recipient_phone" value="{{ old('recipient_phone', $visit->recipient_phone) }}" placeholder="Contoh: 081234567890" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                </div>
            </div>

            {{-- Form Card 3: Estimasi Siswa Kelas 12 --}}
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

            {{-- Form Card 4: Target Jadwal Waktu Sosialisasi --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-emerald-600"></i>
                    <span>Target Jadwal Waktu Sosialisasi</span>
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">4. Target Bulan &amp; Tahun <span class="text-red-500">*</span></label>
                        <input type="text" name="target_month_year" value="{{ old('target_month_year', $visit->target_month_year) }}" placeholder="Contoh: Oktober 2026" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">5. Target Tanggal <span class="text-amber-600 font-normal">(Opsional)</span></label>
                        <input type="date" name="target_date" value="{{ old('target_date', $visit->target_date ? \Carbon\Carbon::parse($visit->target_date)->format('Y-m-d') : '') }}" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">6. Target Waktu Sosialisasi <span class="text-red-500">*</span></label>
                        <input type="text" name="target_time" value="{{ old('target_time', $visit->target_time) }}" placeholder="Contoh: 08:30 WIB" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
                    </div>
                </div>
            </div>

            {{-- Form Card 5: Metode Kegiatan Sosialisasi --}}
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
                            <option value="Sosialisasi di Kelas" {{ old('socialization_method', $visit->socialization_method) == 'Sosialisasi di Kelas' ? 'selected' : '' }}>a. Sosialisasi di Kelas</option>
                            <option value="Sosialisasi di Aula" {{ old('socialization_method', $visit->socialization_method) == 'Sosialisasi di Aula' ? 'selected' : '' }}>b. Sosialisasi di Aula</option>
                            <option value="Workshop Digital Kreatif" {{ old('socialization_method', $visit->socialization_method) == 'Workshop Digital Kreatif' ? 'selected' : '' }}>c. Workshop Digital Kreatif</option>
                            <option value="Edufair" {{ old('socialization_method', $visit->socialization_method) == 'Edufair' ? 'selected' : '' }}>d. Edufair</option>
                            <option value="Lainnya" {{ old('socialization_method', $visit->socialization_method) == 'Lainnya' ? 'selected' : '' }}>e. Lainnya (Input Kegiatan Khusus)</option>
                        </select>
                    </div>

                    {{-- Dynamic Input Field if 'Lainnya' is selected --}}
                    <div x-show="selectedMethod === 'Lainnya'" x-transition class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                        <label class="block font-bold text-amber-900 mb-1">Input Nama Kegiatan Khusus / Lainnya <span class="text-red-500">*</span></label>
                        <input type="text" name="other_activity_name" value="{{ old('other_activity_name', $visit->other_activity_name) }}" placeholder="Tuliskan nama kegiatan sosialisasi khusus..." class="w-full px-3 py-2.5 rounded-xl border border-amber-300 bg-white font-semibold text-slate-900">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Hasil Visit <span class="text-red-500">*</span></label>
                            <select name="status" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                                <option value="berhasil" {{ old('status', $visit->status) == 'berhasil' ? 'selected' : '' }}>Berhasil (Diizinkan Sosialisasi)</option>
                                <option value="tidak_bertemu" {{ old('status', $visit->status) == 'tidak_bertemu' ? 'selected' : '' }}>Tidak Bertemu (Jadwal Ulang)</option>
                                <option value="tidak_berhasil" {{ old('status', $visit->status) == 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil (Ditolak)</option>
                                <option value="pending" {{ old('status', $visit->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Catatan Tambahan Visit</label>
                            <input type="text" name="notes" value="{{ old('notes', $visit->notes) }}" placeholder="Catatan respon penerima..." class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
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
                    <span>Simpan Perubahan Kunjungan</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Select2 Initialization & Synchronization Script -->
    <script>
        $(document).ready(function() {
            const $districtSelect = $('#district_filter');
            const $schoolSelect = $('#school_id');
            const $headmasterInput = $('#headmaster_name');

            // Cache all school options
            const allSchools = [];
            $schoolSelect.find('option').each(function() {
                const val = $(this).val();
                if (val) {
                    allSchools.push({
                        val: val,
                        text: $(this).text().trim(),
                        districtId: String($(this).data('district') || ''),
                        districtName: $(this).data('district-name') || '',
                        headmaster: $(this).data('headmaster') || '',
                        npsn: $(this).data('npsn') || '',
                        type: $(this).data('type') || '',
                        prio: $(this).data('prio') || 'C',
                        selected: $(this).is(':selected')
                    });
                }
            });

            // Initialize Select2 with search
            $schoolSelect.select2({
                placeholder: '-- Cari & Pilih Sekolah (Find) --',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return "Sekolah tidak ditemukan pada wilayah ini";
                    },
                    searching: function() {
                        return "Mencari data sekolah...";
                    }
                }
            });

            // Filter school options in Select2 based on district
            function filterSchools(districtId, keepSelectionVal = null) {
                const targetVal = keepSelectionVal !== null ? keepSelectionVal : $schoolSelect.val();
                
                $schoolSelect.empty();
                $schoolSelect.append(new Option('-- Cari & Pilih Sekolah (Find) --', ''));

                let count = 0;
                let foundTarget = false;

                allSchools.forEach(function(item) {
                    if (!districtId || item.districtId === String(districtId)) {
                        const isSelected = String(item.val) === String(targetVal);
                        if (isSelected) foundTarget = true;
                        
                        const newOption = new Option(item.text, item.val, false, isSelected);
                        $(newOption).attr('data-district', item.districtId);
                        $(newOption).attr('data-headmaster', item.headmaster);
                        $schoolSelect.append(newOption);
                        count++;
                    }
                });

                if (!foundTarget && keepSelectionVal !== null) {
                    $schoolSelect.val('');
                }

                $schoolSelect.trigger('change.select2');

                // Update UI hint badge
                if (districtId) {
                    const districtName = $districtSelect.find('option:selected').text().trim();
                    $('#school_count_info').html(`Menampilkan <span class="font-bold text-brand-600">${count}</span> sekolah di ${districtName}.`);
                } else {
                    $('#school_count_info').html(`Menampilkan <span class="font-bold text-brand-600">${count}</span> sekolah dari data Dapodik &amp; Kemenag.`);
                }
            }

            // District change listener
            $districtSelect.on('change', function() {
                const districtId = $(this).val();
                filterSchools(districtId, null);
                window.dispatchEvent(new CustomEvent('school-selected', { detail: { schoolId: '' } }));
            });

            // School selection listener (auto-sync district, prefill headmaster, notify Alpine)
            $schoolSelect.on('change', function() {
                const schoolId = $(this).val();
                window.dispatchEvent(new CustomEvent('school-selected', { detail: { schoolId: schoolId || '' } }));

                if (schoolId) {
                    const schoolData = allSchools.find(s => String(s.val) === String(schoolId));
                    if (schoolData) {
                        // Auto sync district dropdown if empty or different
                        if (schoolData.districtId && $districtSelect.val() !== schoolData.districtId) {
                            $districtSelect.val(schoolData.districtId);
                            const districtName = $districtSelect.find('option:selected').text().trim();
                            const countInDistrict = allSchools.filter(s => s.districtId === schoolData.districtId).length;
                            $('#school_count_info').html(`Menampilkan <span class="font-bold text-brand-600">${countInDistrict}</span> sekolah di ${districtName}.`);
                        }

                        // Auto prefill headmaster name if field is empty and school has recorded headmaster
                        if (schoolData.headmaster && !$headmasterInput.val()) {
                            $headmasterInput.val(schoolData.headmaster);
                        }
                    }
                }
            });

            // Initial load
            const initDistrict = $districtSelect.val();
            const initSchool = '{{ old('school_id', $visit->school_id) }}';
            if (initDistrict) {
                filterSchools(initDistrict, initSchool);
            } else if (initSchool) {
                const schoolData = allSchools.find(s => String(s.val) === String(initSchool));
                if (schoolData && schoolData.districtId) {
                    $districtSelect.val(schoolData.districtId);
                    filterSchools(schoolData.districtId, initSchool);
                } else {
                    filterSchools('', initSchool);
                }
            }
        });
    </script>
</x-app-layout>
