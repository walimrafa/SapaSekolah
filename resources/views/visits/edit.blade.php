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

    @php
        $initAgendaCategories = old('agenda_categories', old('agenda_category') ? (is_array(old('agenda_category')) ? old('agenda_category') : explode(',', old('agenda_category'))) : (!empty($visit->agenda_categories) ? $visit->agenda_categories : ['penjadwalan_sosialisasi']));
        $upcomingEventsJson = ($upcomingEvents ?? collect())->map(function($ev) {
            return [
                'id' => $ev->id,
                'title' => $ev->title,
                'event_date' => $ev->event_date?->format('Y-m-d'),
                'event_date_formatted' => $ev->formatted_date,
                'event_time' => $ev->event_time,
                'location' => $ev->location ?? 'Kampus UBSI',
                'target_audience' => $ev->target_audience,
                'quota' => $ev->quota,
                'description' => $ev->description,
                'days_remaining' => $ev->days_remaining,
                'is_today' => $ev->is_today,
            ];
        })->values();
    @endphp

    <script>
        function visitEditManager() {
            return {
                districts: @json($districts),
                allSchools: @json($schools),
                agendaCategories: @json($initAgendaCategories),
                invitationStatus: '{{ old('invitation_attendance_status', $visit->invitation_attendance_status ?? 'akan_hadir') }}',
                upcomingEvents: @json($upcomingEventsJson),
                selectedEventPlanId: '{{ old('event_plan_id', $visit->event_plan_id ?? ($visit->event_name ? 'manual' : '')) }}',
                eventName: '{{ old('event_name', $visit->event_name ?? '') }}',
                selectedDistrictId: '{{ old('district_id', $visit->school->district_id ?? '') }}',
                selectedSchoolId: '{{ old('school_id', $visit->school_id) }}',
                selectedMethod: '{{ old('socialization_method', $visit->socialization_method) }}',
                grade12ClassCount: '{{ old('grade_12_class_count', $visit->grade_12_class_count) }}',
                studentsPerClass: '{{ old('students_per_class', $visit->students_per_class) }}',
                checkinLatitude: '',
                checkinLongitude: '',
                locationReady: false,
                locationStatus: 'Check-in GPS bersifat opsional.',
                showCameraModal: false,
                cameraStream: null,
                facingMode: 'environment',
                photoPreviewUrl: null,
                photoCameraBase64: '',
                photoFileName: '',
                photoSource: '',
                isCameraLoading: false,
                cameraError: null,

                hasCat(cat) {
                    return this.agendaCategories.includes(cat);
                },

                toggleCat(cat) {
                    if (this.hasCat(cat)) {
                        if (this.agendaCategories.length > 1) {
                            this.agendaCategories = this.agendaCategories.filter(c => c !== cat);
                        }
                    } else {
                        this.agendaCategories.push(cat);
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                get agendaCategoriesText() {
                    const labels = {
                        'penjadwalan_sosialisasi': '1. Penjadwalan Sosialisasi',
                        'mengantar_undangan': '2. Mengantar Undangan Acara',
                        'kunjungan_lainnya': '3. Kunjungan Lainnya'
                    };
                    if (this.agendaCategories.length === 3) {
                        return 'Semua Agenda Kunjungan Terpilih (3 Agenda)';
                    }
                    if (this.agendaCategories.length === 0) {
                        return '-- Pilih Kategori Agenda --';
                    }
                    return this.agendaCategories.map(c => labels[c] || c).join(' + ');
                },

                onEventPlanSelect(event) {
                    const val = event.target.value;
                    this.selectedEventPlanId = val;
                    if (val && val !== 'manual') {
                        const found = this.upcomingEvents.find(e => String(e.id) === String(val));
                        if (found) {
                            this.eventName = found.title;
                        }
                    } else if (val === 'manual') {
                        if (this.upcomingEvents.some(e => e.title === this.eventName)) {
                            this.eventName = '';
                        }
                    }
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                get selectedEventDetail() {
                    if (!this.selectedEventPlanId || this.selectedEventPlanId === 'manual') return null;
                    return this.upcomingEvents.find(e => String(e.id) === String(this.selectedEventPlanId)) || null;
                },

                init() {
                    if (this.selectedEventPlanId && this.selectedEventPlanId !== 'manual') {
                        const found = this.upcomingEvents.find(e => String(e.id) === String(this.selectedEventPlanId));
                        if (found && !this.eventName) {
                            this.eventName = found.title;
                        }
                    }

                    this.$watch('agendaCategories', () => {
                        this.$nextTick(() => {
                            if (window.lucide) window.lucide.createIcons();
                        });
                    });
                },

                async openCamera() {
                    this.showCameraModal = true;
                    this.isCameraLoading = true;
                    this.cameraError = null;
                    this.$nextTick(async () => {
                        if (window.lucide) window.lucide.createIcons();
                        await this.startStream();
                    });
                },

                async startStream() {
                    try {
                        this.stopStream();
                        const constraints = {
                            video: {
                                facingMode: { ideal: this.facingMode },
                                width: { ideal: 1920 },
                                height: { ideal: 1080 }
                            },
                            audio: false
                        };
                        this.cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                        const videoEl = document.getElementById('camera_video_feed_edit');
                        if (videoEl) {
                            videoEl.srcObject = this.cameraStream;
                            await videoEl.play();
                        }
                        this.isCameraLoading = false;
                    } catch (err) {
                        console.error('Camera access error:', err);
                        this.isCameraLoading = false;
                        this.cameraError = 'Tidak dapat mengakses kamera. Pastikan izin kamera telah diizinkan di browser Anda.';
                    }
                },

                async switchCamera() {
                    this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
                    this.isCameraLoading = true;
                    await this.startStream();
                },

                capturePhoto() {
                    const videoEl = document.getElementById('camera_video_feed_edit');
                    if (!videoEl || !this.cameraStream) return;

                    const canvas = document.createElement('canvas');
                    canvas.width = videoEl.videoWidth || 1280;
                    canvas.height = videoEl.videoHeight || 720;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                    this.photoPreviewUrl = dataUrl;
                    this.photoCameraBase64 = dataUrl;
                    this.photoFileName = 'kamera_edit_' + (new Date().toISOString().replace(/[:.-]/g, '_')) + '.jpg';
                    this.photoSource = 'camera';

                    const fileInput = document.getElementById('visit_photo_edit');
                    if (fileInput) fileInput.value = '';

                    this.closeCamera();
                    this.$nextTick(() => {
                        if (window.lucide) window.lucide.createIcons();
                    });
                },

                closeCamera() {
                    this.stopStream();
                    this.showCameraModal = false;
                },

                stopStream() {
                    if (this.cameraStream) {
                        this.cameraStream.getTracks().forEach(track => track.stop());
                        this.cameraStream = null;
                    }
                },

                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    this.photoFileName = file.name;
                    this.photoSource = 'file';
                    this.photoCameraBase64 = '';

                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = (e) => {
                            this.photoPreviewUrl = e.target.result;
                            this.$nextTick(() => {
                                if (window.lucide) window.lucide.createIcons();
                            });
                        };
                        reader.readAsDataURL(file);
                    } else {
                        this.photoPreviewUrl = null;
                    }
                },

                downloadPhoto() {
                    if (!this.photoPreviewUrl) return;
                    const link = document.createElement('a');
                    link.href = this.photoPreviewUrl;
                    link.download = this.photoFileName || 'bukti_dokumentasi_kunjungan.jpg';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },

                clearPhoto() {
                    this.photoPreviewUrl = null;
                    this.photoCameraBase64 = '';
                    this.photoFileName = '';
                    this.photoSource = '';
                    const fileInput = document.getElementById('visit_photo_edit');
                    if (fileInput) fileInput.value = '';
                },
                captureCheckIn() {
                    const school = this.allSchools.find(item => String(item.id) === String(this.selectedSchoolId));
                    if (!school || school.latitude === null || school.longitude === null || school.latitude === '' || school.longitude === '') {
                        this.locationReady = false;
                        this.locationStatus = 'Sekolah belum memiliki koordinat GPS (opsional). Anda tetap dapat memperbarui data.';
                        return;
                    }
                    if (!navigator.geolocation) {
                        this.locationReady = false;
                        this.locationStatus = 'Browser tidak mendukung GPS. Anda tetap dapat memperbarui data.';
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

                        this.checkinLatitude = position.coords.latitude;
                        this.checkinLongitude = position.coords.longitude;
                        this.locationReady = true;

                        if (distance > 500) {
                            this.locationStatus = `Lokasi GPS tercatat (${Math.round(distance)} m dari sekolah). Tetap dapat disimpan.`;
                        } else {
                            this.locationStatus = `Lokasi GPS valid (${Math.round(distance)} m dari sekolah).`;
                        }
                    }, error => {
                        this.locationReady = false;
                        this.locationStatus = error.code === error.PERMISSION_DENIED
                            ? 'Izin GPS ditolak. Anda tetap dapat memperbarui data tanpa GPS.'
                            : 'Lokasi GPS tidak tersedia. Anda tetap dapat memperbarui data tanpa GPS.';
                    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 });
                },
                schoolChanged() {
                    this.checkinLatitude = '';
                    this.checkinLongitude = '';
                    this.locationReady = false;
                    this.locationStatus = 'Sekolah berubah. Check-in GPS bersifat opsional.';
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
            };
        }
    </script>

    <div class="max-w-4xl" @school-selected.window="selectedSchoolId = $event.detail.schoolId; schoolChanged()" x-data="visitEditManager()">
        <form method="POST" action="{{ route('visits.update', $visit->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="latitude" x-model="checkinLatitude">
            <input type="hidden" name="longitude" x-model="checkinLongitude">

            <!-- Card 0: Pilihan Kategori Agenda Kunjungan (Multi-Select Combobox) -->
            <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-soft" x-data="{ openDropdown: false }" @click.outside="openDropdown = false">
                <!-- Hidden inputs to submit array and joined string -->
                <template x-for="cat in agendaCategories" :key="cat">
                    <input type="hidden" name="agenda_categories[]" :value="cat">
                </template>
                <input type="hidden" name="agenda_category" :value="agendaCategories.join(',')">

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-brand-50 border border-brand-100 flex items-center justify-center text-brand-600 shrink-0">
                            <i data-lucide="layers" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <label class="block text-xs font-extrabold text-slate-900">
                                    Kategori Agenda Kunjungan <span class="text-rose-500">*</span>
                                </label>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-brand-100 text-brand-800">
                                    Bisa Pilih Lebih Dari 1
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500">Centang agenda kunjungan untuk menggabungkan isian formulir tanpa duplikasi</p>
                        </div>
                    </div>

                    <!-- Combobox Multi-Select Dropdown Container -->
                    <div class="relative w-full sm:w-96">
                        <button type="button" @click="openDropdown = !openDropdown"
                            class="w-full px-3.5 py-2.5 rounded-xl border-2 border-brand-500/50 bg-white font-extrabold text-xs text-slate-800 shadow-xs hover:border-brand-600 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition flex items-center justify-between gap-2 text-left">
                            <span class="truncate" x-text="agendaCategoriesText"></span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-brand-600 shrink-0 transition-transform duration-200" :class="openDropdown ? 'rotate-180' : ''"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="openDropdown" x-transition.origin.top.right x-cloak
                            class="absolute right-0 mt-2 w-full sm:w-[380px] bg-white rounded-2xl border border-slate-200 shadow-xl z-30 p-2 space-y-1.5">
                            <div class="px-2.5 py-1.5 text-[10px] font-bold text-slate-400 uppercase tracking-wider border-b border-slate-100 flex items-center justify-between">
                                <span>Pilihan Agenda (Multi-Select)</span>
                                <span class="text-brand-600 font-extrabold" x-text="agendaCategories.length + ' Terpilih'"></span>
                            </div>

                            <!-- Opsi 1 -->
                            <div @click="toggleCat('penjadwalan_sosialisasi')" 
                                class="p-2.5 rounded-xl cursor-pointer transition flex items-start gap-2.5"
                                :class="hasCat('penjadwalan_sosialisasi') ? 'bg-brand-50/80 border border-brand-200 text-brand-950 font-bold' : 'hover:bg-slate-50 border border-transparent text-slate-700'">
                                <input type="checkbox" :checked="hasCat('penjadwalan_sosialisasi')" class="mt-0.5 rounded text-brand-600 focus:ring-brand-500 pointer-events-none">
                                <div class="text-xs">
                                    <div class="flex items-center gap-1.5 font-extrabold">
                                        <i data-lucide="presentation" class="w-3.5 h-3.5 text-brand-600"></i>
                                        <span>1. Penjadwalan Sosialisasi</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-normal mt-0.5">Form estimasi rombel kelas 12, target jadwal waktu &amp; metode sosialisasi</p>
                                </div>
                            </div>

                            <!-- Opsi 2 -->
                            <div @click="toggleCat('mengantar_undangan')" 
                                class="p-2.5 rounded-xl cursor-pointer transition flex items-start gap-2.5"
                                :class="hasCat('mengantar_undangan') ? 'bg-amber-50/80 border border-amber-200 text-amber-950 font-bold' : 'hover:bg-slate-50 border border-transparent text-slate-700'">
                                <input type="checkbox" :checked="hasCat('mengantar_undangan')" class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 pointer-events-none">
                                <div class="text-xs">
                                    <div class="flex items-center gap-1.5 font-extrabold">
                                        <i data-lucide="mail" class="w-3.5 h-3.5 text-amber-600"></i>
                                        <span>2. Mengantar Undangan Acara</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-normal mt-0.5">Penerima undangan, konfirmasi kehadiran (Akan Hadir / Ijin / Tidak)</p>
                                </div>
                            </div>

                            <!-- Opsi 3 -->
                            <div @click="toggleCat('kunjungan_lainnya')" 
                                class="p-2.5 rounded-xl cursor-pointer transition flex items-start gap-2.5"
                                :class="hasCat('kunjungan_lainnya') ? 'bg-purple-50/80 border border-purple-200 text-purple-950 font-bold' : 'hover:bg-slate-50 border border-transparent text-slate-700'">
                                <input type="checkbox" :checked="hasCat('kunjungan_lainnya')" class="mt-0.5 rounded text-purple-600 focus:ring-purple-500 pointer-events-none">
                                <div class="text-xs">
                                    <div class="flex items-center gap-1.5 font-extrabold">
                                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5 text-purple-600"></i>
                                        <span>3. Kunjungan Lainnya</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 font-normal mt-0.5">Silaturahmi, audiensi kepala sekolah &amp; urusan dinas lainnya</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Toggle Pills & Status Terpilih -->
                <div class="mt-3 pt-2.5 border-t border-slate-100 flex flex-wrap items-center gap-2">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider mr-1">Klik Cepat:</span>

                    <button type="button" @click="toggleCat('penjadwalan_sosialisasi')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="hasCat('penjadwalan_sosialisasi') ? 'bg-brand-600 text-white border-brand-600 shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'">
                        <i data-lucide="presentation" class="w-3.5 h-3.5"></i>
                        <span>1. Penjadwalan Sosialisasi</span>
                        <span x-show="hasCat('penjadwalan_sosialisasi')" class="text-brand-200 text-[10px]">✓</span>
                    </button>

                    <button type="button" @click="toggleCat('mengantar_undangan')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="hasCat('mengantar_undangan') ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'">
                        <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                        <span>2. Mengantar Undangan Acara</span>
                        <span x-show="hasCat('mengantar_undangan')" class="text-amber-200 text-[10px]">✓</span>
                    </button>

                    <button type="button" @click="toggleCat('kunjungan_lainnya')"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border"
                        :class="hasCat('kunjungan_lainnya') ? 'bg-purple-600 text-white border-purple-600 shadow-xs' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'">
                        <i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i>
                        <span>3. Kunjungan Lainnya</span>
                        <span x-show="hasCat('kunjungan_lainnya')" class="text-purple-200 text-[10px]">✓</span>
                    </button>
                </div>
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
                    <div class="pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="map-pin-check" class="w-4 h-4 text-emerald-600"></i>
                            Check-in Lokasi Sekolah
                        </h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">Opsional</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs">
                        <button type="button" @click="captureCheckIn()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl flex items-center gap-2">
                            <i data-lucide="locate-fixed" class="w-4 h-4"></i>
                            Ambil GPS Check-in (Opsional)
                        </button>
                        <span class="font-semibold" :class="locationReady ? 'text-emerald-700' : 'text-slate-600'" x-text="locationStatus" aria-live="polite"></span>
                        @if($visit->latitude && $visit->longitude)
                            <span class="w-full text-[10px] text-slate-500">Koordinat tersimpan: {{ $visit->latitude }}, {{ $visit->longitude }} (opsional untuk diperbarui).</span>
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
                                <option value="{{ $u->id }}" {{ old('user_id', $visit->user_id) == $u->id ? 'selected' : '' }}>
                                    {{ $u->name }} ({{ $u->role->name ?? 'Marketing' }})
                                </option>
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
                        <span x-text="agendaCategories.length === 1 && hasCat('mengantar_undangan') ? 'Detail Penerima Undangan Acara' : 'Detail Penerima Kunjungan & Kepala Sekolah'"></span>
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
                            <span x-text="agendaCategories.length === 1 && hasCat('mengantar_undangan') ? 'Nama Penerima Undangan Beserta Gelarnya' : '1. Nama Penerima Beserta Gelarnya'"></span> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="recipient_name" value="{{ old('recipient_name', $visit->recipient_name) }}" placeholder="Contoh: Drs. H. Mulyadi, M.Pd." required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold focus:border-brand-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-800 mb-1">
                            <span x-text="agendaCategories.length === 1 && hasCat('mengantar_undangan') ? 'Jabatan Penerima Undangan' : '2. Jabatan Penerima'"></span> <span class="text-red-500">*</span>
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

                <!-- Bagian Konfirmasi Kehadiran Khusus Undangan (Opsi 2) -->
                <div x-show="hasCat('mengantar_undangan')" x-transition class="pt-4 border-t border-slate-100 space-y-4">
                    <!-- Header Sub-Section -->
                    <div class="flex items-center justify-between pb-1">
                        <div class="flex items-center space-x-2">
                            <span class="p-1.5 rounded-lg bg-amber-100 text-amber-700">
                                <i data-lucide="ticket" class="w-4 h-4"></i>
                            </span>
                            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Detail Undangan Event Kampus</span>
                        </div>
                        <span class="text-[11px] font-semibold text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-200">
                            Agenda Mengantar Undangan
                        </span>
                    </div>

                    <!-- Pemilihan Event dari Master (Otomatis & Kadaluarsa Hilang Otomatis) -->
                    <div>
                        <label class="block font-bold text-slate-800 text-xs mb-1.5 flex items-center justify-between">
                            <span>Rencana Event Kampus (Master Acara) <span class="text-red-500" x-show="hasCat('mengantar_undangan')">*</span></span>
                            <template x-if="upcomingEvents.length > 0">
                                <span class="text-[11px] text-emerald-600 font-semibold flex items-center gap-1">
                                    <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                                    Otomatis dari Master Event (<span x-text="upcomingEvents.length"></span> Tersedia)
                                </span>
                            </template>
                        </label>

                        <template x-if="upcomingEvents.length > 0">
                            <div>
                                <select name="event_plan_id" 
                                    @change="onEventPlanSelect($event)" 
                                    x-model="selectedEventPlanId"
                                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-semibold text-slate-800 focus:border-amber-500 text-xs bg-white shadow-2xs">
                                    <option value="">-- Pilih Rencana Event Aktif --</option>
                                    <template x-for="ev in upcomingEvents" :key="ev.id">
                                        <option :value="ev.id" x-text="'[' + ev.event_date_formatted + '] ' + ev.title + (ev.location ? ' @ ' + ev.location : '')"></option>
                                    </template>
                                    <option value="manual">✏️ + Input Nama Event Manual / Acara Lainnya</option>
                                </select>
                                <p class="text-[11px] text-slate-500 mt-1 italic flex items-center gap-1">
                                    <i data-lucide="info" class="w-3 h-3 text-slate-400"></i>
                                    Event yang sudah kadaluarsa (lewat tanggal) otomatis tidak muncul pada daftar di atas.
                                </p>
                            </div>
                        </template>

                        <template x-if="upcomingEvents.length === 0">
                            <div class="p-3 rounded-xl bg-amber-50/80 border border-amber-200/80 text-amber-800 text-xs mb-2 flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <i data-lucide="calendar-x" class="w-4 h-4 text-amber-600 shrink-0"></i>
                                    <span>Belum ada rencana event mendatang di Master Event. Silakan ketik nama event secara manual.</span>
                                </div>
                                <input type="hidden" name="event_plan_id" value="">
                            </div>
                        </template>
                    </div>

                    <!-- Preview Card Rencana Event Terpilih (Tema, Tgl, Waktu, Lokasi) -->
                    <template x-if="selectedEventDetail">
                        <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-50/90 to-amber-100/40 border border-amber-200/80 space-y-3 shadow-2xs">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center space-x-2">
                                    <span class="p-1 rounded-md bg-amber-500 text-white shadow-2xs">
                                        <i data-lucide="award" class="w-4 h-4"></i>
                                    </span>
                                    <div>
                                        <span class="text-[10px] font-extrabold uppercase tracking-wider text-amber-800">Event Terpilih:</span>
                                        <h4 class="text-sm font-extrabold text-slate-900" x-text="selectedEventDetail.title"></h4>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border shrink-0"
                                    :class="selectedEventDetail.is_today ? 'bg-amber-500 text-white border-amber-600' : 'bg-emerald-100 text-emerald-800 border-emerald-200'"
                                    x-text="selectedEventDetail.is_today ? '🔥 Hari Ini' : (selectedEventDetail.days_remaining + ' Hari Lagi')">
                                </span>
                            </div>

                            <!-- Detail Grid: Tema, Tgl, Waktu, Lokasi -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2.5 text-xs pt-1">
                                <div class="bg-white/80 backdrop-blur-xs p-2.5 rounded-xl border border-amber-200/60">
                                    <span class="text-[10px] font-bold text-slate-500 block mb-0.5">TANGGAL PELAKSANAAN</span>
                                    <span class="font-extrabold text-slate-800 flex items-center gap-1.5" x-text="selectedEventDetail.event_date_formatted"></span>
                                </div>
                                <div class="bg-white/80 backdrop-blur-xs p-2.5 rounded-xl border border-amber-200/60">
                                    <span class="text-[10px] font-bold text-slate-500 block mb-0.5">WAKTU ACARA</span>
                                    <span class="font-extrabold text-slate-800" x-text="selectedEventDetail.event_time || 'Sesuai Jadwal'"></span>
                                </div>
                                <div class="bg-white/80 backdrop-blur-xs p-2.5 rounded-xl border border-amber-200/60">
                                    <span class="text-[10px] font-bold text-slate-500 block mb-0.5">LOKASI KEGIATAN</span>
                                    <span class="font-extrabold text-slate-800 truncate block" x-text="selectedEventDetail.location || 'Kampus UBSI'"></span>
                                </div>
                                <div class="bg-white/80 backdrop-blur-xs p-2.5 rounded-xl border border-amber-200/60">
                                    <span class="text-[10px] font-bold text-slate-500 block mb-0.5">TARGET & KUOTA</span>
                                    <span class="font-extrabold text-slate-800 block truncate" x-text="(selectedEventDetail.target_audience || 'SLTA') + (selectedEventDetail.quota ? ' (' + selectedEventDetail.quota + ' org)' : '')"></span>
                                </div>
                            </div>

                            <template x-if="selectedEventDetail.description">
                                <p class="text-xs text-slate-600 bg-white/60 p-2.5 rounded-xl border border-amber-100 italic" x-text="selectedEventDetail.description"></p>
                            </template>
                        </div>
                    </template>

                    <!-- Input Nama Event (Otomatis Terisi dari Event Terpilih atau Input Manual) -->
                    <div>
                        <label class="block font-bold text-slate-800 text-xs mb-1">
                            Nama Event / Acara Kampus yang Diundang <span class="text-red-500" x-show="hasCat('mengantar_undangan')">*</span>
                        </label>
                        <div class="relative">
                            <input type="text" name="event_name" x-model="eventName"
                                :readonly="selectedEventPlanId && selectedEventPlanId !== 'manual'"
                                placeholder="Contoh: BSI Flash 2026 / Workshop Guru BK / Seminar Nasional / Lomba..."
                                class="w-full px-3.5 py-2.5 rounded-xl border font-semibold text-xs transition"
                                :class="selectedEventPlanId && selectedEventPlanId !== 'manual' 
                                    ? 'border-emerald-300 bg-emerald-50/50 text-slate-900 cursor-not-allowed' 
                                    : 'border-slate-300 text-slate-800 focus:border-amber-500 bg-white'"
                                :required="hasCat('mengantar_undangan')">
                            <template x-if="selectedEventPlanId && selectedEventPlanId !== 'manual'">
                                <button type="button" @click="selectedEventPlanId = 'manual'" 
                                    class="absolute right-2 top-1/2 -translate-y-1/2 px-2.5 py-1 rounded-lg bg-white border border-slate-200 hover:bg-slate-50 text-[11px] font-bold text-amber-700 shadow-2xs">
                                    Ubah Manual
                                </button>
                            </template>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1" x-show="selectedEventPlanId && selectedEventPlanId !== 'manual'">
                            Nama event terisi & terkunci otomatis dari rencana event master terpilih.
                        </p>
                    </div>

                    <!-- Konfirmasi Kehadiran Undangan Acara -->
                    <div>
                        <label class="block font-bold text-slate-800 text-xs mb-1">
                            Konfirmasi Kehadiran Undangan Acara <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                            <label class="flex items-center p-3 rounded-xl border-2 cursor-pointer transition"
                                :class="invitationStatus === 'akan_hadir' ? 'border-emerald-500 bg-emerald-50 text-emerald-900 font-bold shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                                <input type="radio" name="invitation_attendance_status" value="akan_hadir" x-model="invitationStatus" :disabled="!hasCat('mengantar_undangan')" class="sr-only">
                                <i data-lucide="check-circle-2" class="w-4 h-4 mr-2" :class="invitationStatus === 'akan_hadir' ? 'text-emerald-600' : 'text-slate-400'"></i>
                                <span>Akan Hadir</span>
                            </label>

                            <label class="flex items-center p-3 rounded-xl border-2 cursor-pointer transition"
                                :class="invitationStatus === 'minta_ijin_dulu' ? 'border-amber-500 bg-amber-50 text-amber-900 font-bold shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                                <input type="radio" name="invitation_attendance_status" value="minta_ijin_dulu" x-model="invitationStatus" :disabled="!hasCat('mengantar_undangan')" class="sr-only">
                                <i data-lucide="clock" class="w-4 h-4 mr-2" :class="invitationStatus === 'minta_ijin_dulu' ? 'text-amber-600' : 'text-slate-400'"></i>
                                <span>Minta Ijin Dulu</span>
                            </label>

                            <label class="flex items-center p-3 rounded-xl border-2 cursor-pointer transition"
                                :class="invitationStatus === 'tidak_bisa_hadir' ? 'border-rose-500 bg-rose-50 text-rose-900 font-bold shadow-xs' : 'border-slate-200 hover:border-slate-300 bg-white text-slate-700'">
                                <input type="radio" name="invitation_attendance_status" value="tidak_bisa_hadir" x-model="invitationStatus" :disabled="!hasCat('mengantar_undangan')" class="sr-only">
                                <i data-lucide="x-circle" class="w-4 h-4 mr-2" :class="invitationStatus === 'tidak_bisa_hadir' ? 'text-rose-600' : 'text-slate-400'"></i>
                                <span>Tidak Bisa Hadir</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Bagian Catatan Khusus Kunjungan Lainnya (Opsi 3) -->
                <div x-show="hasCat('kunjungan_lainnya')" x-transition class="pt-4 border-t border-slate-100">
                    <div class="p-3 bg-purple-50/70 rounded-xl border border-purple-200/70 text-xs text-purple-900 flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-purple-600 shrink-0"></i>
                        <span><strong>Agenda Kunjungan Lainnya Aktif:</strong> Keperluan audiensi, silaturahmi &amp; catatan dinas dapat dicatat pada kolom catatan terpadu di bawah.</span>
                    </div>
                </div>
            </div>

            <!-- KONTEN KHUSUS OPSI 1 (PENJADWALAN SOSIALISASI) -->
            <div x-show="hasCat('penjadwalan_sosialisasi')" x-transition class="space-y-6">
                {{-- Form Card 3: Estimasi Siswa Kelas 12 --}}
                <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                    <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                        <span>Estimasi Siswa Kelas 12</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">3. Jumlah Kelas 12 <span class="text-red-500">*</span></label>
                            <input type="number" name="grade_12_class_count" x-model="grade12ClassCount" min="1" placeholder="Contoh: 6" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">7. Rata-Rata Jumlah Siswa Per Kelas <span class="text-red-500">*</span></label>
                            <input type="number" name="students_per_class" x-model="studentsPerClass" min="1" placeholder="Contoh: 36" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
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
                            <input type="text" name="target_month_year" value="{{ old('target_month_year', $visit->target_month_year) }}" placeholder="Contoh: Oktober 2026" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-brand-600">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">5. Target Tanggal <span class="text-amber-600 font-normal">(Opsional)</span></label>
                            <input type="date" name="target_date" value="{{ old('target_date', $visit->target_date ? \Carbon\Carbon::parse($visit->target_date)->format('Y-m-d') : '') }}" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3 py-2.5 rounded-xl border border-slate-300">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-800 mb-1">6. Target Waktu Sosialisasi <span class="text-red-500">*</span></label>
                            <input type="text" name="target_time" value="{{ old('target_time', $visit->target_time) }}" placeholder="Contoh: 08:30 WIB" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-semibold">
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
                            <select name="socialization_method" x-model="selectedMethod" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold text-slate-900">
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
                            <input type="text" name="other_activity_name" value="{{ old('other_activity_name', $visit->other_activity_name) }}" :disabled="!hasCat('penjadwalan_sosialisasi')" placeholder="Tuliskan nama kegiatan sosialisasi khusus..." class="w-full px-3.5 py-2.5 rounded-xl border border-amber-300 bg-white font-semibold text-slate-900">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Status Hasil Visit <span class="text-red-500">*</span></label>
                                <select name="status" :required="hasCat('penjadwalan_sosialisasi')" :disabled="!hasCat('penjadwalan_sosialisasi')" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 font-bold">
                                    <option value="berhasil" {{ old('status', $visit->status) == 'berhasil' ? 'selected' : '' }}>Berhasil (Diizinkan Sosialisasi)</option>
                                    <option value="tidak_bertemu" {{ old('status', $visit->status) == 'tidak_bertemu' ? 'selected' : '' }}>Tidak Bertemu (Jadwal Ulang)</option>
                                    <option value="tidak_berhasil" {{ old('status', $visit->status) == 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil (Ditolak)</option>
                                    <option value="pending" {{ old('status', $visit->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Card: Catatan Tambahan Visit & Hasil Kunjungan Lapangan (Terpadu / Tanpa Duplikasi) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft space-y-4">
                <div class="pb-3 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-brand-600"></i>
                        <span>Catatan Tambahan &amp; Hasil Kunjungan Lapangan</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Hasil Respon / Catatan Terpadu</span>
                </div>

                <div class="text-xs">
                    <label class="block font-bold text-slate-700 mb-1.5">
                        Catatan Hasil Kunjungan / Respon Penerima <span class="text-slate-400 font-normal">(Opsional)</span>
                    </label>
                    <textarea name="notes" rows="3" placeholder="Tuliskan catatan hasil kunjungan lapangan, respon penerima (undangan/sosialisasi/audiensi), atau tindak lanjut yang diperlukan..." class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:border-brand-500 font-medium">{{ old('notes', $visit->notes) }}</textarea>
                    <p class="text-[10px] text-slate-400 mt-1">Catatan ini terpadu untuk semua agenda yang Anda pilih di atas tanpa duplikasi isian.</p>
                </div>
            </div>

            {{-- Form Card: Dokumentasi Kunjungan Lapangan & Fitur Kamera --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="camera" class="w-4 h-4 text-emerald-600"></i>
                        <span>Dokumentasi Kunjungan Lapangan</span>
                    </h3>
                    <span class="text-[11px] text-slate-400 font-normal">Tersimpan di Server &amp; Google Drive</span>
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
                                <p class="text-xs font-bold text-slate-800 truncate">Berkas Dokumentasi Tersimpan Saat Ini</p>
                                <p class="text-[10px] text-slate-400 font-mono truncate">{{ basename($visit->photo) }}</p>
                                <div class="flex flex-wrap items-center gap-2 mt-1">
                                    <a href="{{ $fileUrl }}" target="_blank" class="text-xs text-brand-600 font-bold hover:underline inline-flex items-center gap-1">
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                        <span>Lihat Berkas ↗</span>
                                    </a>
                                    @if($visit->gdrive_url)
                                        <a href="{{ $visit->gdrive_url }}" target="_blank" class="text-xs text-emerald-600 font-bold hover:underline inline-flex items-center gap-1">
                                            <i data-lucide="cloud" class="w-3 h-3"></i>
                                            <span>Buka di Google Drive ↗</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded-full shrink-0">
                            Tersedia
                        </span>
                    </div>
                @endif

                <div class="space-y-3 text-xs">
                    <label class="block font-bold text-slate-800">
                        {{ $visit->photo ? 'Perbarui / Ganti Bukti Dokumentasi (Opsional)' : 'Unggah Bukti Dokumentasi Lapangan (Opsional)' }}
                    </label>

                    <!-- Tombol Buka Kamera & Pilih Berkas -->
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" @click="openCamera()"
                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center space-x-2">
                            <i data-lucide="camera" class="w-4 h-4"></i>
                            <span>Buka Kamera Langsung</span>
                        </button>

                        <button type="button" @click="document.getElementById('visit_photo_edit').click()"
                            class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition border border-slate-200 flex items-center space-x-2">
                            <i data-lucide="upload" class="w-4 h-4 text-slate-500"></i>
                            <span>Pilih dari Galeri / Berkas</span>
                        </button>
                    </div>

                    <!-- Hidden Inputs -->
                    <input id="visit_photo_edit" type="file" name="photo" @change="handleFileSelect($event)" accept="image/jpeg,image/png,image/webp,application/pdf,.rar,.zip,.pdf,.doc,.docx" class="hidden">
                    <input type="hidden" name="photo_camera_base64" x-model="photoCameraBase64">

                    <!-- Preview Card untuk Berkas / Foto Baru -->
                    <div x-show="photoPreviewUrl || photoFileName" x-transition class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span class="font-extrabold text-slate-800 text-xs">Dokumentasi Baru Dipilih:</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                                    :class="photoSource === 'camera' ? 'bg-emerald-100 text-emerald-800' : 'bg-brand-100 text-brand-800'"
                                    x-text="photoSource === 'camera' ? '📸 Hasil Kamera' : '📁 Berkas File'"></span>
                            </div>
                            <span class="text-[11px] text-slate-500 font-medium truncate max-w-xs" x-text="photoFileName"></span>
                        </div>

                        <div x-show="photoPreviewUrl" class="relative max-w-sm rounded-xl overflow-hidden border border-slate-200 bg-black/5 shadow-inner">
                            <img :src="photoPreviewUrl" alt="Pratinjau Dokumentasi Baru" class="w-full h-44 object-cover rounded-xl">
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-slate-200">
                            <div class="flex items-center space-x-2">
                                <button type="button" @click="downloadPhoto()" x-show="photoPreviewUrl"
                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-brand-700 border border-slate-200 font-bold text-xs shadow-2xs transition inline-flex items-center space-x-1.5"
                                    title="Unduh foto ini">
                                    <i data-lucide="download" class="w-3.5 h-3.5 text-brand-600"></i>
                                    <span>Unduh Foto</span>
                                </button>

                                <button type="button" @click="openCamera()"
                                    class="px-3 py-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 font-bold text-xs shadow-2xs transition inline-flex items-center space-x-1.5">
                                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5 text-slate-500"></i>
                                    <span>Ambil Ulang</span>
                                </button>
                            </div>

                            <button type="button" @click="clearPhoto()"
                                class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition inline-flex items-center space-x-1">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                <span>Batalkan Berkas Baru</span>
                            </button>
                        </div>

                        <p class="text-[11px] text-emerald-700 font-medium flex items-center gap-1.5">
                            <i data-lucide="cloud" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Foto/berkas baru akan otomatis disimpan &amp; diunggah ke Google Drive sekolah saat disimpan.</span>
                        </p>
                    </div>

                    <p class="text-[10px] text-slate-500">Dapat berupa Foto (JPG, PNG, WEBP), Dokumen (PDF, Word), atau Arsip (RAR, ZIP); maksimal 50 MB.</p>
                    @error('photo')
                        <span class="text-xs text-red-600 mt-1 block font-semibold">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Modal Kamera Internal Edit -->
            <div x-show="showCameraModal" x-cloak
                class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">
                <div class="bg-slate-900 rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-slate-800 text-white flex flex-col">
                    <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                <i data-lucide="camera" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="text-xs font-extrabold text-white">Kamera Dokumentasi Kunjungan</h4>
                                <p class="text-[10px] text-slate-400">Arahkan kamera ke kegiatan/penerima kunjungan</p>
                            </div>
                        </div>
                        <button type="button" @click="closeCamera()" class="w-8 h-8 rounded-xl bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center transition">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <div class="relative bg-black flex items-center justify-center min-h-[320px] max-h-[460px] overflow-hidden">
                        <div x-show="isCameraLoading" class="absolute inset-0 flex flex-col items-center justify-center space-y-2 bg-slate-950 text-slate-400 text-xs">
                            <div class="w-8 h-8 border-3 border-emerald-500 border-t-transparent rounded-full animate-spin"></div>
                            <span>Menghubungkan ke kamera...</span>
                        </div>

                        <div x-show="cameraError" class="p-6 text-center text-xs text-rose-400 space-y-2">
                            <i data-lucide="alert-triangle" class="w-8 h-8 mx-auto text-rose-500"></i>
                            <p x-text="cameraError"></p>
                        </div>

                        <video id="camera_video_feed_edit" playsinline autoplay muted class="w-full h-full object-cover max-h-[460px]"></video>
                    </div>

                    <div class="p-4 bg-slate-950 flex items-center justify-between border-t border-slate-800">
                        <button type="button" @click="switchCamera()" 
                            class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition inline-flex items-center space-x-1.5"
                            title="Balik kamera depan / belakang">
                            <i data-lucide="flip-horizontal" class="w-4 h-4"></i>
                            <span class="hidden sm:inline">Balik Kamera</span>
                        </button>

                        <button type="button" @click="capturePhoto()"
                            class="w-16 h-16 rounded-full bg-white hover:bg-slate-200 text-slate-900 shadow-xl shadow-white/20 p-1 flex items-center justify-center transform active:scale-90 transition">
                            <div class="w-13 h-13 rounded-full border-2 border-slate-900 flex items-center justify-center bg-emerald-500 text-white">
                                <i data-lucide="camera" class="w-6 h-6"></i>
                            </div>
                        </button>

                        <button type="button" @click="closeCamera()"
                            class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 font-bold text-xs transition">
                            Batal
                        </button>
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
