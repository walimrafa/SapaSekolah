<x-app-layout :title="$school->name">
    <!-- Top Action Bar & Title -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6" x-data="{ openCoordModal: false }">
        <div class="flex items-center space-x-3">
            <a href="{{ route('schools.index') }}" class="p-2 bg-white rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center space-x-2">
                    <h1 class="text-xl font-extrabold text-slate-900">{{ $school->name }}</h1>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-blue-100 text-blue-800">NPSN: {{ $school->npsn }}</span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $school->schoolType ? $school->schoolType->name : 'SLTA' }} &bull; {{ strtoupper($school->status) }} &bull; Kecamatan {{ $school->district ? $school->district->name : '-' }}
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <button type="button" @click="openCoordModal = true" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs flex items-center space-x-1.5 shadow-sm transition">
                <i data-lucide="map-pin" class="w-4 h-4"></i>
                <span>Input / Edit Koordinat GIS</span>
            </button>
            <a href="{{ route('schools.edit', $school->id) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs flex items-center space-x-1.5 shadow-sm transition">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span>Edit Sekolah</span>
            </a>
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
                            <span>Update Koordinat GIS {{ $school->name }}</span>
                        </h3>
                        <button type="button" @click="openCoordModal = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('schools.update-coordinates', $school->id) }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Latitude (Lintang)</label>
                            <input type="text" name="latitude" value="{{ old('latitude', $school->latitude ?? '-6.3054') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Longitude (Bujur)</label>
                            <input type="text" name="longitude" value="{{ old('longitude', $school->longitude ?? '107.2995') }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 font-mono font-semibold">
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

    <!-- Header Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Prioritas Marketing</p>
            <div class="flex items-center justify-between mt-1">
                @php
                    $pColor = match($school->priority_level) {
                        'A' => 'text-red-600 bg-red-50 border-red-200',
                        'B' => 'text-amber-600 bg-amber-50 border-amber-200',
                        'C' => 'text-emerald-600 bg-emerald-50 border-emerald-200',
                        default => 'text-slate-600 bg-slate-50 border-slate-200',
                    };
                @endphp
                <span class="text-2xl font-black {{ strtok($pColor, ' ') }}">Prioritas {{ $school->priority_level ?? 'Belum' }}</span>
                <span class="px-2.5 py-1 text-xs font-bold rounded-full border {{ $pColor }}">{{ $school->priority_level ?? '-' }}</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Total Score Potensi</p>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-black text-brand-600">{{ number_format($school->potential_score, 1) }}</span>
                <span class="text-xs font-semibold text-slate-500">/ 100 Poin</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Siswa Kelas XII</p>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-black text-slate-900">{{ number_format($school->grade_12_count) }}</span>
                <span class="text-xs font-semibold text-slate-500">dari {{ number_format($school->student_count) }} Total</span>
            </div>
        </div>

        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Akreditasi</p>
            <div class="flex items-center justify-between mt-1">
                <span class="text-2xl font-black text-slate-900">{{ $school->accreditation ? $school->accreditation->code : 'N/A' }}</span>
                <span class="text-xs font-semibold text-slate-500">{{ $school->accreditation ? $school->accreditation->name : '-' }}</span>
            </div>
        </div>
    </div>

    <!-- Main Grid Content -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Left Column: Details & Contacts (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- Informasi Umum -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <i data-lucide="info" class="w-4 h-4 text-brand-600"></i>
                        <span>Informasi Profil Sekolah</span>
                    </span>
                </h3>

                <div class="grid grid-cols-2 gap-y-3 text-xs">
                    <div>
                        <span class="text-slate-400 block font-semibold">Alamat:</span>
                        <span class="text-slate-800 font-medium">{{ $school->address ?? 'Belum diisi' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold">Kecamatan &amp; Desa:</span>
                        <span class="text-slate-800 font-medium">{{ $school->district ? $school->district->name : '-' }}, {{ $school->village ? $school->village->name : '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold">Koordinat GPS:</span>
                        @if($school->latitude && $school->longitude)
                            <div class="flex flex-wrap items-center gap-2 mt-0.5">
                                <a href="{{ $school->gmaps_search_url }}" target="_blank" title="Buka di Google Maps" class="text-emerald-700 hover:text-emerald-800 font-mono font-bold hover:underline inline-flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3.5 h-3.5 text-emerald-600"></i>
                                    <span>{{ $school->latitude }}, {{ $school->longitude }} (Google Maps)</span>
                                </a>
                                <a href="{{ $school->waze_url }}" target="_blank" class="px-2 py-0.5 bg-cyan-600 hover:bg-cyan-700 text-white font-bold rounded text-[10px] inline-flex items-center gap-1 transition">
                                    <i data-lucide="navigation" class="w-3 h-3"></i>
                                    <span>Navigasi Waze 🚗</span>
                                </a>
                            </div>
                        @else
                            <span class="text-slate-500 font-mono">Belum dipetakan</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold">Jarak dari UBSI Karawang:</span>
                        <span class="text-slate-800 font-bold text-brand-600">{{ number_format($school->distance_from_campus, 1) }} km</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold">Telepon / Email:</span>
                        <span class="text-slate-800 font-medium">{{ $school->phone ?? '-' }} &bull; {{ $school->email ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block font-semibold">Website / Sosmed:</span>
                        <span class="text-slate-800 font-medium">{{ $school->website ?? '-' }} &bull; {{ $school->instagram ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Rekomendasi Sekolah Terdekat Sekitar (Lintas Kecamatan) -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="map-pin-line" class="w-4 h-4 text-emerald-600"></i>
                        <span>Rekomendasi Sekolah Terdekat Sekitar (Lintas Kecamatan)</span>
                    </h3>
                    <span class="text-[10px] font-bold px-2 py-0.5 bg-emerald-50 text-emerald-700 rounded-full border border-emerald-100">Spasial Haversine</span>
                </div>

                <div class="space-y-3">
                    @forelse($nearestSchools as $neighbor)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 hover:border-brand-200 hover:bg-brand-50/20 transition flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('schools.show', $neighbor->id) }}" class="text-xs font-bold text-slate-900 hover:text-brand-600 truncate block">
                                        {{ $neighbor->name }}
                                    </a>
                                    @php
                                        $pColor = match($neighbor->priority_level) {
                                            'A' => 'bg-red-100 text-red-700',
                                            'B' => 'bg-amber-100 text-amber-700',
                                            'C' => 'bg-emerald-100 text-emerald-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="px-1.5 py-0.5 text-[9px] font-bold rounded uppercase {{ $pColor }}">
                                        Prio {{ $neighbor->priority_level ?? '-' }}
                                    </span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Kecamatan {{ $neighbor->district ? $neighbor->district->name : '-' }} &bull; Status: {{ strtoupper($neighbor->status) }}
                                </p>
                                <p class="text-[10px] font-bold text-emerald-700 mt-0.5">
                                    📍 Jarak: {{ number_format($neighbor->neighbor_distance_km, 2) }} km dari {{ $school->name }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-1.5 shrink-0">
                                <a href="https://www.google.com/maps/dir/?api=1&origin={{ $school->latitude }},{{ $school->longitude }}&destination={{ $neighbor->latitude }},{{ $neighbor->longitude }}&travelmode=driving"
                                   target="_blank"
                                   title="Petunjuk Rute Mengemudi Mobil di Google Maps"
                                   class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-[10px] font-bold flex items-center space-x-1 shadow-xs transition">
                                    <i data-lucide="navigation" class="w-3 h-3"></i>
                                    <span>Rute GMaps</span>
                                </a>
                                <a href="{{ route('schools.show', $neighbor->id) }}"
                                   title="Lihat Profil Sekolah"
                                   class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 rounded-lg text-[10px] font-bold transition">
                                    Detail Profile
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada data lokasi koordinat untuk rekomendasi sekolah sekitar.</p>
                    @endforelse
                </div>
            </div>

            <!-- Kontak PIC Sekolah -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="contact-2" class="w-4 h-4 text-amber-500"></i>
                        <span>Kontak PIC Sekolah ({{ $school->contacts->count() }})</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @forelse($school->contacts as $contact)
                        <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-xs font-bold text-slate-900">{{ $contact->name }}</span>
                                    @if($contact->is_primary)
                                        <span class="px-2 py-0.5 text-[9px] font-bold bg-amber-100 text-amber-800 rounded-full">Utama</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    {{ $contact->position ? $contact->position->name : 'Jabatan' }} &bull; WA/HP: {{ $contact->whatsapp ?? $contact->phone ?? '-' }}
                                </p>
                            </div>
                            <div class="flex items-center space-x-1">
                                @if($contact->whatsapp || $contact->phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp ?? $contact->phone) }}" target="_blank"
                                        class="px-2.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-[10px] font-bold flex items-center space-x-1">
                                        <i data-lucide="message-square" class="w-3 h-3"></i>
                                        <span>Chat WA</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada data kontak PIC sekolah.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column: Timeline Kunjungan & Follow Up (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Timeline Kunjungan Terbaru -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="navigation-2" class="w-4 h-4 text-brand-600"></i>
                    <span>Riwayat Kunjungan Marketing</span>
                </h3>

                <div class="space-y-4">
                    @forelse($school->visits as $visit)
                        <div class="relative pl-4 border-l-2 border-brand-500 pb-3">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</span>
                                <span class="text-[10px] px-2 py-0.5 bg-blue-50 text-blue-700 font-bold rounded">{{ $visit->status }}</span>
                            </div>
                            <p class="text-[11px] text-slate-600 mt-1">Marketing: {{ $visit->user ? $visit->user->name : '-' }}</p>
                            <p class="text-[11px] text-slate-500 mt-0.5 italic">"{{ Str::limit($visit->result ?? $visit->purpose ?? '-', 80) }}"</p>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Sekolah ini belum memiliki riwayat kunjungan.</p>
                    @endforelse
                </div>
            </div>

            <!-- Data Prospek dari Sekolah Ini -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/80 shadow-soft">
                <h3 class="text-sm font-bold text-slate-900 pb-3 mb-4 border-b border-slate-100 flex items-center gap-2">
                    <i data-lucide="users" class="w-4 h-4 text-purple-600"></i>
                    <span>Prospek Siswa PMB ({{ $school->prospects->count() }})</span>
                </h3>

                <div class="space-y-2">
                    @forelse($school->prospects->take(5) as $prospect)
                        <div class="p-2.5 rounded-xl bg-slate-50 flex items-center justify-between text-xs">
                            <div>
                                <span class="font-bold text-slate-800">{{ $prospect->name }}</span>
                                <span class="text-[10px] text-slate-400 block">Prodi: {{ $prospect->interest_program ?? '-' }}</span>
                            </div>
                            <span class="px-2 py-0.5 text-[9px] font-bold rounded uppercase bg-purple-100 text-purple-800">
                                {{ $prospect->status }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Belum ada prospek terdaftar dari sekolah ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
