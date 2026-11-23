@php
    $kolMinFollowers = \App\Models\StudentQuestionnaire::getKolMinFollowers();
    $formattedMinFollowers = number_format($kolMinFollowers, 0, ',', '.');
@endphp
<x-app-layout title="Rekomendasi KOL Siswa - SAPA Sekolah">
    <div x-data="kolManager()" class="space-y-6">

        <!-- Top Header & Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-pink-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-pink-500/20 text-pink-300 border border-pink-500/30">
                            <i data-lucide="sparkles" class="w-3.5 h-3.5 mr-1.5 text-pink-400"></i> Rekomendasi KOL Siswa
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <i data-lucide="zap" class="w-3.5 h-3.5 mr-1.5 text-emerald-400"></i> Otomatis &ge; {{ $formattedMinFollowers }} Followers
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800/80 text-slate-300 border border-slate-700">
                            <i data-lucide="shield-check" class="w-3 h-3 mr-1 text-amber-400"></i> Administrator, Pimpinan &amp; Koordinator
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Rekomendasi KOL &amp; Student Influencer
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Data siswa yang memiliki akun Instagram dengan minimal <strong>{{ $formattedMinFollowers }} followers</strong> secara otomatis tersaring dari Kuisioner Sosialisasi dan Pendaftaran Siswa Support Event. Cocok untuk direkrut menjadi Brand Ambassador dan Duta Kampus UBSI.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('kol-recommendations.documentation') }}"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white shadow-lg shadow-purple-600/30 transition hover:scale-105"
                        title="Buka Portofolio & Hasil Dokumentasi Karya Brand Ambassador">
                        <i data-lucide="video" class="w-4 h-4 text-amber-300"></i>
                        <span>Hasil Dokumentasi KOL</span>
                    </a>

                    <button type="button" @click="openSettingModal = true"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-white/10 hover:bg-white/20 text-white border border-white/20 shadow-lg backdrop-blur-sm transition hover:scale-105"
                        title="Atur Syarat Minimal Follower Instagram untuk Rekomendasi KOL">
                        <i data-lucide="sliders" class="w-4 h-4 text-pink-300"></i>
                        <span>Master Syarat Follower (&ge; {{ $formattedMinFollowers }})</span>
                    </button>

                    <a href="{{ route('kol-recommendations.export', request()->query()) }}"
                        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-lg shadow-emerald-600/30 transition hover:scale-105"
                        title="Download Rekap Calon KOL dalam format Microsoft Excel">
                        <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                        <span>Export Excel (.xlsx)</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- Total Calon KOL -->
            <div class="p-4 bg-white rounded-2xl border border-pink-200/80 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-pink-700 uppercase tracking-wider">Total Calon KOL</p>
                    <span class="w-7 h-7 rounded-lg bg-pink-50 flex items-center justify-center text-pink-600">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">{{ number_format($stats['total_kandidat']) }}</p>
                <span class="text-[11px] text-pink-600 font-semibold">&ge; {{ $formattedMinFollowers }} Followers</span>
            </div>

            <!-- Dari Kuisioner Sosialisasi -->
            <div class="p-4 bg-white rounded-2xl border border-blue-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">Dari Sosialisasi</p>
                    <span class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600">
                        <i data-lucide="clipboard-list" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-blue-700 mt-1">{{ number_format($stats['asal_kuisioner']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Kuisioner Siswa</span>
            </div>

            <!-- Dari Support Event -->
            <div class="p-4 bg-white rounded-2xl border border-purple-200/80 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-purple-700 uppercase tracking-wider">Dari Hasil Event</p>
                    <span class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center text-purple-600">
                        <i data-lucide="trophy" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-purple-700 mt-1">{{ number_format($stats['asal_event']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Support Event Sekolah</span>
            </div>

            <!-- Total Potensi Reach (Followers) -->
            <div class="p-4 bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl border border-amber-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Potensi Jangkauan</p>
                    <span class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center text-amber-700">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-amber-700 mt-1">
                    @if($stats['total_reach'] >= 1000000)
                        {{ number_format($stats['total_reach'] / 1000000, 1) }}M
                    @elseif($stats['total_reach'] >= 1000)
                        {{ number_format($stats['total_reach'] / 1000, 1) }}K
                    @else
                        {{ number_format($stats['total_reach']) }}
                    @endif
                </p>
                <span class="text-[11px] text-amber-800 font-medium">Total Akumulasi Follower</span>
            </div>

            <!-- Status Rekrutmen Deal/Aktif -->
            <div class="p-4 bg-gradient-to-br from-emerald-50 to-teal-50/50 rounded-2xl border border-emerald-200 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Siap / Aktif KOL</p>
                    <span class="w-7 h-7 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-700">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                    </span>
                </div>
                <p class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1">
                    {{ number_format($stats['status_bersedia'] + $stats['status_aktif']) }}
                </p>
                <span class="text-[11px] text-emerald-800 font-medium">
                    {{ $stats['status_aktif'] }} Aktif, {{ $stats['status_bersedia'] }} Deal
                </span>
            </div>
        </div>

        <!-- Filter & Search Panel -->
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80 shadow-sm space-y-3">
            <form method="GET" action="{{ route('kol-recommendations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3">
                <!-- Search Keyword -->
                <div class="lg:col-span-4 relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="q" value="{{ request('q') }}"
                        placeholder="Cari nama siswa, sekolah, IG, no. WA..."
                        class="w-full pl-9 pr-3.5 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
                </div>

                <!-- Filter Sumber Data -->
                <div class="lg:col-span-2">
                    <select name="source" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-slate-700">
                        <option value="">Semua Sumber ({{ $stats['total_kandidat'] }})</option>
                        <option value="kuisioner" {{ request('source') === 'kuisioner' ? 'selected' : '' }}>Sosialisasi ({{ $stats['asal_kuisioner'] }})</option>
                        <option value="support_event" {{ request('source') === 'support_event' ? 'selected' : '' }}>Support Event ({{ $stats['asal_event'] }})</option>
                    </select>
                </div>

                <!-- Filter Status KOL -->
                <div class="lg:col-span-2">
                    <select name="kol_status" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-slate-700">
                        <option value="">Semua Status Rekrutmen</option>
                        @foreach($statusOptions as $key => $label)
                            <option value="{{ $key }}" {{ request('kol_status') === $key ? 'selected' : '' }}>
                                {{ $label }} ({{ $stats['status_' . $key] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Minimum Followers -->
                <div class="lg:col-span-2">
                    <select name="min_followers" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 text-slate-700">
                        <option value="{{ $kolMinFollowers }}" {{ request('min_followers', (string) $kolMinFollowers) == (string) $kolMinFollowers ? 'selected' : '' }}>Min &ge; {{ $formattedMinFollowers }} Follower (Standar)</option>
                        @if($kolMinFollowers != 2000)
                            <option value="2000" {{ request('min_followers') == '2000' ? 'selected' : '' }}>Min &ge; 2.000 Follower</option>
                        @endif
                        <option value="5000" {{ request('min_followers') == '5000' ? 'selected' : '' }}>Min &ge; 5.000 Follower</option>
                        <option value="10000" {{ request('min_followers') == '10000' ? 'selected' : '' }}>Min &ge; 10.000 (10K+)</option>
                        <option value="0" {{ request('min_followers') === '0' ? 'selected' : '' }}>Tampilkan Semua (Semua Follower)</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="lg:col-span-2 flex items-center gap-2">
                    <button type="submit"
                        class="flex-1 inline-flex items-center justify-center space-x-1.5 px-3 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white transition">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['q', 'source', 'kol_status', 'min_followers', 'sort']))
                        <a href="{{ route('kol-recommendations.index') }}"
                            class="px-3 py-2.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                            title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-pink-500 animate-ping"></span>
                    <h2 class="text-base font-bold text-slate-900">
                        Daftar Calon Brand Ambassador / KOL Siswa
                    </h2>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        {{ $candidates->total() }} Siswa Ditemukan
                    </span>
                </div>
                <div class="text-xs text-slate-500">
                    Kampus Aktif: <strong class="text-slate-800">{{ $activeCampus ? $activeCampus->name : 'Semua Kampus' }}</strong>
                </div>
            </div>

            <!-- Toolbar Aksi Masal KOL (Bulk Action Bar) -->
            <div x-show="selectedIds.length > 0" x-cloak
                class="p-3.5 bg-gradient-to-r from-slate-950 via-purple-950 to-slate-900 text-white rounded-2xl shadow-xl flex flex-wrap items-center justify-between gap-3 mb-4 border border-pink-500/30 animate-in fade-in slide-in-from-top-2">
                <div class="flex items-center gap-2.5">
                    <span class="w-7 h-7 rounded-full bg-pink-600 text-white text-xs font-black flex items-center justify-center shadow" x-text="selectedIds.length"></span>
                    <div>
                        <p class="text-xs font-extrabold tracking-tight">Kandidat KOL Terpilih</p>
                        <p class="text-[10px] text-pink-300">Pilih aksi masal untuk data yang diceklis</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <!-- Form Bulk Status Update -->
                    <form method="POST" action="{{ route('kol-recommendations.bulk-status') }}" class="flex items-center gap-1.5"
                        onsubmit="return confirm('Perbarui status untuk ' + selectedIds.length + ' kandidat KOL terpilih?')">
                        @csrf
                        <template x-for="id in selectedIds" :key="'status-'+id">
                            <input type="hidden" name="selected_ids[]" :value="id">
                        </template>
                        <select name="kol_status" required class="py-1.5 px-3 rounded-xl text-xs bg-slate-900 text-white border border-pink-500/40 font-bold focus:ring-pink-500">
                            <option value="kandidat">Kandidat (Baru)</option>
                            <option value="dihubungi">Sudah Dihubungi</option>
                            <option value="bersedia">Bersedia / Deal</option>
                            <option value="brand_ambassador">Brand Ambassador (Fix)</option>
                            <option value="aktif">Aktif KOL UBSI</option>
                            <option value="ditolak">Tolak / Tidak Sesuai</option>
                        </select>
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-pink-600 hover:bg-pink-500 text-white transition shadow-sm flex items-center gap-1">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Ubah Status</span>
                        </button>
                    </form>

                    <!-- Form Bulk Verify Followers -->
                    <form method="POST" action="{{ route('kol-recommendations.bulk-verify') }}" class="inline"
                        onsubmit="return confirm('Lakukan pengecekan real followers Instagram masal untuk ' + selectedIds.length + ' akun terpilih?')">
                        @csrf
                        <template x-for="id in selectedIds" :key="'v-'+id">
                            <input type="hidden" name="selected_ids[]" :value="id">
                        </template>
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-blue-600 hover:bg-blue-500 text-white transition shadow-sm flex items-center gap-1" title="Verifikasi otomatis followers riil Instagram">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span>Cek Real Followers</span>
                        </button>
                    </form>

                    <!-- Form Bulk Delete / Reject -->
                    <form method="POST" action="{{ route('kol-recommendations.bulk-delete') }}" class="inline"
                        onsubmit="return confirm('Keluarkan ' + selectedIds.length + ' data siswa yang tidak sesuai dari daftar Rekomendasi KOL?')">
                        @csrf
                        <template x-for="id in selectedIds" :key="'del-'+id">
                            <input type="hidden" name="selected_ids[]" :value="id">
                        </template>
                        <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-black bg-rose-600 hover:bg-rose-500 text-white transition shadow-sm flex items-center gap-1">
                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            <span>Hapus Terpilih</span>
                        </button>
                    </form>

                    <button type="button" @click="selectedIds = []" class="px-3 py-1.5 rounded-xl text-xs font-bold text-slate-300 hover:text-white hover:bg-slate-800 transition">
                        Batal
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-3 w-10 text-center">
                                <input type="checkbox" :checked="selectedIds.length === allIds.length && allIds.length > 0"
                                    @change="toggleSelectAll($event)"
                                    class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500 border-slate-300 cursor-pointer"
                                    title="Pilih Semua di Halaman Ini">
                            </th>
                            <th class="py-3 px-3 w-12 text-center">No</th>
                            <th class="py-3 px-4 min-w-[200px]">Siswa &amp; Kontak</th>
                            <th class="py-3 px-4 min-w-[180px]">Asal Sekolah &amp; Kelas</th>
                            <th class="py-3 px-4 min-w-[200px]">Instagram, Followers &amp; Validasi</th>
                            <th class="py-3 px-4 min-w-[130px]">Sumber Data</th>
                            <th class="py-3 px-4 min-w-[140px]">Status Rekrutmen</th>
                            <th class="py-3 px-4 min-w-[180px] text-center">Aksi, Rate Card &amp; Karya</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($candidates as $index => $candidate)
                            @php
                                $isEvent = $candidate->source === 'support_event' || !empty($candidate->school_event_proposal_id);
                                $cleanHandle = $candidate->clean_instagram_handle;
                                $igUrl = $candidate->instagram_url;
                                $waNumber = $candidate->formatted_student_phone;

                                // Template Pesan Ajakan KOL untuk WhatsApp
                                $campusName = $candidate->campus?->name ?? 'UBSI';
                                $schoolName = $candidate->school_display_name;
                                $waMessage = "Halo {$candidate->student_name}, perkenalkan kami dari tim Manajemen Kampus {$campusName}. Kami melihat profil media sosial kamu yang aktif dan inspiratif di {$schoolName}. Kami ingin mengajak kamu untuk bergabung dalam program Brand Ambassador / Duta Kampus UBSI dengan berbagai benefit dan beasiswa kuliah. Apakah ada waktu luang untuk kami jelaskan lebih lanjut?";
                                $waUrl = $waNumber ? "https://wa.me/{$waNumber}?text=" . urlencode($waMessage) : '#';

                                $statusBadgeClass = match($candidate->kol_status) {
                                    'bersedia' => 'bg-emerald-100 text-emerald-800 border-emerald-300 font-bold',
                                    'aktif' => 'bg-purple-100 text-purple-800 border-purple-300 font-extrabold',
                                    'dihubungi' => 'bg-blue-100 text-blue-800 border-blue-200',
                                    'ditolak' => 'bg-rose-100 text-rose-800 border-rose-200',
                                    default => 'bg-amber-100 text-amber-800 border-amber-200 font-semibold',
                                };
                            @endphp
                            <tr class="hover:bg-pink-50/30 transition" :class="selectedIds.includes({{ $candidate->id }}) ? 'bg-pink-50/70' : ''">
                                <!-- Checkbox -->
                                <td class="py-3 px-3 text-center">
                                    <input type="checkbox" :value="{{ $candidate->id }}"
                                        :checked="selectedIds.includes({{ $candidate->id }})"
                                        @change="toggleSelect({{ $candidate->id }})"
                                        class="w-4 h-4 rounded text-pink-600 focus:ring-pink-500 border-slate-300 cursor-pointer">
                                </td>

                                <!-- No -->
                                <td class="py-3 px-3 text-center font-bold text-slate-400">
                                    {{ $candidates->firstItem() + $index }}
                                </td>

                                <!-- Siswa & Kontak -->
                                <td class="py-3 px-4">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-pink-500 to-rose-600 text-white font-bold flex items-center justify-center shrink-0 shadow-sm text-sm">
                                            {{ strtoupper(substr($candidate->student_name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 text-sm hover:text-brand-600">
                                                {{ $candidate->student_name }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                                <span class="inline-flex items-center">
                                                    <i data-lucide="phone" class="w-3 h-3 mr-1 text-emerald-600"></i>
                                                    {{ $candidate->student_phone }}
                                                </span>
                                                <span class="text-slate-300">&bull;</span>
                                                <span>{{ $candidate->gender_label }}</span>
                                            </div>
                                            @if($candidate->student_email)
                                                <p class="text-[11px] text-slate-400 truncate max-w-[180px] mt-0.5">
                                                    {{ $candidate->student_email }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Asal Sekolah & Kelas -->
                                <td class="py-3 px-4">
                                    <p class="font-bold text-slate-800">
                                        {{ $candidate->school_display_name }}
                                    </p>
                                    <div class="mt-0.5 text-[11px] text-slate-500">
                                        <span>Kelas: <strong>{{ $candidate->student_class ?: '-' }}</strong></span>
                                        <span class="text-slate-300 mx-1">&bull;</span>
                                        <span>Jurusan: {{ $candidate->student_major ?: '-' }}</span>
                                    </div>
                                    <span class="inline-block mt-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600">
                                        {{ $candidate->campus?->name ?? 'UBSI' }}
                                    </span>
                                </td>

                                <!-- Instagram, Followers & Validasi Otomatis -->
                                <td class="py-3 px-4">
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-1.5">
                                            @if($cleanHandle)
                                                <a href="{{ $igUrl }}" target="_blank"
                                                    class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg bg-gradient-to-r from-pink-500/10 to-rose-500/10 text-pink-700 hover:text-pink-900 border border-pink-200 font-bold transition group">
                                                    <svg class="w-3.5 h-3.5 text-pink-600 fill-current" viewBox="0 0 24 24">
                                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.13-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                                    </svg>
                                                    <span>{{ '@' . $cleanHandle }}</span>
                                                    <i data-lucide="external-link" class="w-3 h-3 text-pink-400 group-hover:translate-x-0.5 transition"></i>
                                                </a>
                                            @else
                                                <span class="text-slate-400 italic">Belum diisi</span>
                                            @endif

                                            <!-- Tombol Trigger Cek Real IG Otomatis -->
                                            <form method="POST" action="{{ route('kol-recommendations.verify-followers', $candidate->id) }}" class="inline">
                                                @csrf
                                                <button type="submit" title="Cek Real Followers IG Otomatis" class="p-1 rounded-lg bg-slate-100 hover:bg-pink-100 text-slate-600 hover:text-pink-600 border border-slate-200 transition">
                                                    <i data-lucide="scan-search" class="w-3 h-3"></i>
                                                </button>
                                            </form>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-1.5">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-gradient-to-r from-pink-600 to-rose-600 text-white shadow-sm">
                                                <i data-lucide="award" class="w-3 h-3 mr-1 text-amber-300"></i>
                                                {{ number_format($candidate->instagram_followers) }} Followers
                                            </span>

                                            <!-- Status Verifikasi Follower Otomatis -->
                                            @if($candidate->kol_verification_status === 'verified')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-300" title="{{ $candidate->kol_verification_notes }}">
                                                    <i data-lucide="check-check" class="w-3 h-3 mr-1 text-emerald-600"></i>
                                                    Real: {{ number_format($candidate->instagram_followers_verified ?: $candidate->instagram_followers) }}
                                                </span>
                                            @elseif($candidate->kol_verification_status === 'rejected')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300" title="{{ $candidate->kol_verification_notes }}">
                                                    <i data-lucide="alert-triangle" class="w-3 h-3 mr-1 text-rose-600"></i>
                                                    Tertolak Otomatis
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800 border border-amber-200" title="{{ $candidate->kol_verification_notes ?: 'Belum dicek' }}">
                                                    <i data-lucide="clock" class="w-2.5 h-2.5 mr-1 text-amber-600"></i>
                                                    Unverified
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Sumber Data -->
                                <td class="py-3 px-4">
                                    @if($isEvent)
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                                <i data-lucide="trophy" class="w-3 h-3 mr-1 text-purple-600"></i> Support Event
                                            </span>
                                            @if($candidate->eventProposal)
                                                <p class="text-[10px] text-slate-500 font-medium truncate max-w-[150px]" title="{{ $candidate->eventProposal->event_name }}">
                                                    {{ $candidate->eventProposal->event_name }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-cyan-100 text-cyan-800 border border-cyan-200">
                                            <i data-lucide="clipboard-list" class="w-3 h-3 mr-1 text-cyan-600"></i> Sosialisasi
                                        </span>
                                    @endif
                                </td>

                                <!-- Status Rekrutmen KOL -->
                                <td class="py-3 px-4">
                                    <div class="space-y-1">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs border {{ $statusBadgeClass }}">
                                            @if($candidate->kol_status === 'brand_ambassador')
                                                <i data-lucide="crown" class="w-3 h-3 mr-1 text-amber-600"></i>
                                            @endif
                                            {{ $candidate->kol_status_label }}
                                        </span>
                                        @if($candidate->kol_notes)
                                            <p class="text-[10px] text-slate-500 italic line-clamp-2 max-w-[160px]" title="{{ $candidate->kol_notes }}">
                                                &ldquo;{{ $candidate->kol_notes }}&rdquo;
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi, Rate Card & Kelola Karya -->
                                <td class="py-3 px-4 text-center">
                                    <div class="flex flex-wrap items-center justify-center gap-1.5">
                                        <!-- WhatsApp Recruitment Quick Button -->
                                        @if($waNumber)
                                            <a href="{{ $waUrl }}" target="_blank"
                                                class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition hover:scale-105"
                                                title="Hubungi Siswa via WhatsApp dengan draft ajakan Brand Ambassador">
                                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                                <span class="hidden xl:inline">WA</span>
                                            </a>
                                        @endif

                                        <!-- Tombol Cek Rate Card Pasaran Otomatis -->
                                        <button type="button"
                                            @click="openRateCardModal({{ json_encode($candidate->estimated_rate_card) }}, '{{ addslashes($candidate->student_name) }}', '{{ addslashes($candidate->clean_instagram_handle) }}')"
                                            class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-amber-500/15 hover:bg-amber-500/25 text-amber-900 border border-amber-300 transition"
                                            title="Cek Rate Card Pasaran Akun Ini Secara Otomatis">
                                            <i data-lucide="receipt" class="w-3.5 h-3.5 text-amber-600"></i>
                                            <span>Rate Card</span>
                                        </button>

                                        <!-- Tombol Khusus Brand Ambassador: Kelola Link Karya (IG/TikTok/FB/YT) -->
                                        @if($candidate->kol_status === 'brand_ambassador' || $candidate->kol_status === 'aktif')
                                            @php
                                                $workCount = !empty($candidate->work_links) ? count(array_filter([
                                                    $candidate->work_links['instagram'] ?? null,
                                                    $candidate->work_links['tiktok'] ?? null,
                                                    $candidate->work_links['facebook'] ?? null,
                                                    $candidate->work_links['youtube'] ?? null,
                                                ])) : 0;
                                            @endphp
                                            <button type="button"
                                                @click="openWorkLinksModal({{ json_encode($candidate->work_links ?? []) }}, {{ $candidate->id }}, '{{ addslashes($candidate->student_name) }}')"
                                                class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-lg text-xs font-extrabold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white shadow-sm transition hover:scale-105"
                                                title="Upload & Kelola Link Karya Siswa Brand Ambassador (IG, TikTok, FB, YouTube)">
                                                <i data-lucide="video" class="w-3.5 h-3.5 text-amber-300"></i>
                                                <span>Link Karya</span>
                                                @if($workCount > 0)
                                                    <span class="px-1.5 py-0.2 rounded-full text-[9px] bg-white/30 text-white">{{ $workCount }}</span>
                                                @endif
                                            </button>
                                        @endif

                                        <!-- Edit Status Button -->
                                        <button type="button"
                                            @click="openModal({{ json_encode([
                                                'id' => $candidate->id,
                                                'name' => $candidate->student_name,
                                                'school' => $candidate->school_display_name,
                                                'instagram_handle' => $candidate->clean_instagram_handle,
                                                'instagram_followers' => $candidate->instagram_followers,
                                                'kol_status' => $candidate->kol_status ?? 'kandidat',
                                                'kol_notes' => $candidate->kol_notes ?? '',
                                                'update_url' => route('kol-recommendations.update-status', $candidate->id),
                                            ]) }})"
                                            class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition"
                                            title="Ubah Status & Catatan Rekrutmen KOL">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-slate-600"></i>
                                            <span>Kelola</span>
                                        </button>

                                        <!-- Hapus / Keluarkan dari KOL Button -->
                                        <button type="button"
                                            @click="openDeleteModal({{ json_encode([
                                                'id' => $candidate->id,
                                                'name' => $candidate->student_name,
                                                'school' => $candidate->school_display_name,
                                                'followers' => $candidate->instagram_followers,
                                                'delete_url' => route('kol-recommendations.destroy', $candidate->id),
                                            ]) }})"
                                            class="inline-flex items-center space-x-1 px-2 py-1.5 rounded-lg text-xs font-bold bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition"
                                            title="Hapus / Keluarkan dari Rekomendasi KOL">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                            <span>Hapus</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 px-4 text-center">
                                    <div class="max-w-md mx-auto space-y-3">
                                        <div class="w-16 h-16 mx-auto rounded-full bg-pink-50 border border-pink-100 flex items-center justify-center text-pink-400">
                                            <i data-lucide="sparkles" class="w-8 h-8"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-800">
                                            Belum Ada Calon KOL yang Memenuhi Kriteria
                                        </h3>
                                        <p class="text-xs text-slate-500 leading-relaxed">
                                            Siswa yang mengisi kuisioner sosialisasi atau formulir pendaftaran event dengan followers Instagram &ge; {{ $formattedMinFollowers }} akan otomatis masuk ke daftar ini.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($candidates->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $candidates->links() }}
                </div>
            @endif
        </div>

        <!-- Modal Master Pengaturan Syarat Minimal Follower KOL -->
        <div x-show="openSettingModal" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;">
            
            <div @click.outside="openSettingModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 overflow-hidden transform transition-all">
                
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-pink-100 text-pink-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="sliders" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Master Syarat Minimal Follower</h3>
                            <p class="text-xs text-slate-500">Konfigurasi batas minimal kualifikasi KOL</p>
                        </div>
                    </div>
                    <button type="button" @click="openSettingModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('kol-recommendations.settings.update') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Jumlah Minimal Follower Media Sosial
                        </label>
                        <div class="relative">
                            <input type="number" name="kol_min_followers" min="1" max="10000000"
                                value="{{ $kolMinFollowers }}" required
                                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 font-bold text-sm text-slate-900 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-semibold text-slate-400">Followers</span>
                        </div>
                        <p class="text-[11px] text-slate-500 mt-1">
                            Siswa baru yang mengisi kuisioner / mendaftar event dengan follower &ge; batas ini akan otomatis masuk ke daftar Rekomendasi KOL.
                        </p>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="sync_existing" value="1" checked
                                class="mt-0.5 rounded text-pink-600 focus:ring-pink-500">
                            <div class="text-xs">
                                <span class="font-bold text-slate-800">Sinkronkan data siswa yang sudah ada</span>
                                <p class="text-[11px] text-slate-500 mt-0.5">
                                    Otomatis perbarui siswa di database yang memiliki followers &ge; batas baru ini menjadi kandidat KOL.
                                </p>
                            </div>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="openSettingModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-500 text-white shadow-md shadow-pink-600/30 transition">
                            Simpan Batas Follower
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Status Rekrutmen KOL -->
        <div x-show="showModal" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            
            <div @click.away="showModal = false"
                class="bg-white rounded-2xl max-w-lg w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                
                <form :action="form.update_url" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center space-x-2">
                            <span class="w-8 h-8 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center font-bold">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                            </span>
                            <div>
                                <h3 class="text-base font-bold text-slate-900" x-text="'Kelola Rekrutmen: ' + form.name"></h3>
                                <p class="text-xs text-slate-500" x-text="form.school"></p>
                            </div>
                        </div>
                        <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <!-- Instagram Handle & Followers -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Username Instagram</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center text-slate-400 font-bold text-xs">@</span>
                                <input type="text" name="instagram_handle" x-model="form.instagram_handle"
                                    class="w-full pl-7 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah Followers</label>
                            <input type="number" name="instagram_followers" x-model="form.instagram_followers" min="0"
                                class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500">
                        </div>
                    </div>

                    <!-- Peringatan Kelayakan Minimal -->
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl flex items-start space-x-2.5 text-amber-900 text-xs">
                        <i data-lucide="info" class="w-4 h-4 text-amber-600 mt-0.5 flex-shrink-0"></i>
                        <div>
                            <p class="font-bold">Ketentuan Kelayakan: &ge; {{ $formattedMinFollowers }} Followers</p>
                            <p class="text-[11px] text-amber-700 mt-0.5">Jika jumlah followers diubah menjadi kurang dari {{ $formattedMinFollowers }}, data otomatis dikeluarkan dari daftar Rekomendasi KOL karena tidak memenuhi kriteria kelayakan.</p>
                        </div>
                    </div>

                    <!-- Status Rekrutmen -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Rekrutmen KOL</label>
                        <select name="kol_status" x-model="form.kol_status" required
                            class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 font-semibold text-slate-800">
                            <option value="kandidat">Kandidat (Baru Masuk)</option>
                            <option value="dihubungi">Sudah Dihubungi (Follow Up WA / DM)</option>
                            <option value="bersedia">Bersedia / Deal Menjadi Brand Ambassador</option>
                            <option value="brand_ambassador">Brand Ambassador Resmi (Aktif Kontrak)</option>
                            <option value="aktif">Aktif KOL UBSI (Sedang Berjalan)</option>
                            <option value="ditolak">Tidak Bersedia / Ditolak</option>
                        </select>
                    </div>

                    <!-- Catatan Rekrutmen / Hasil Kontak -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Negosiasi / Rencana Kerjasama</label>
                        <textarea name="kol_notes" x-model="form.kol_notes" rows="3"
                            placeholder="Contoh: Siswa menyambut baik tawaran beasiswa, bersedia membuat konten reels promosi penerimaan mahasiswa baru..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <button type="button"
                            @click="showModal = false; openDeleteModal({ id: form.id, name: form.name, school: form.school, followers: form.instagram_followers, delete_url: '{{ url('/kol-recommendations') }}/' + form.id })"
                            class="px-3 py-2 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition inline-flex items-center space-x-1.5 border border-rose-200">
                            <i data-lucide="user-x" class="w-3.5 h-3.5"></i>
                            <span>Keluarkan dari KOL</span>
                        </button>
                        <div class="flex items-center space-x-2">
                            <button type="button" @click="showModal = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-500 text-white shadow-md shadow-pink-600/30 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Konfirmasi Hapus / Keluarkan dari KOL -->
        <div x-show="showDeleteModal" x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            <div @click.away="showDeleteModal = false"
                class="bg-white rounded-2xl max-w-md w-full shadow-2xl border border-slate-100 overflow-hidden transform transition-all">
                <form :action="deleteCandidate ? deleteCandidate.delete_url : ''" method="POST">
                    @csrf
                    @method('DELETE')
                    
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-rose-50/50">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Hapus / Keluarkan Data KOL</h3>
                                <p class="text-xs text-slate-500" x-text="deleteCandidate ? deleteCandidate.name + ' (' + deleteCandidate.school + ')' : ''"></p>
                            </div>
                        </div>
                        <button type="button" @click="showDeleteModal = false" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="p-5 space-y-4">
                        <p class="text-xs text-slate-600">
                            Pilih tindakan yang ingin dilakukan untuk data calon KOL ini:
                        </p>

                        <div class="space-y-2.5">
                            <label class="flex items-start p-3 rounded-xl border border-pink-200 bg-pink-50/30 hover:bg-pink-50/60 cursor-pointer transition">
                                <input type="radio" name="mode" value="remove_kol" checked class="mt-0.5 text-pink-600 focus:ring-pink-500">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-slate-900">Keluarkan dari Daftar Rekomendasi KOL</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Siswa ditandai tidak layak / dikeluarkan dari KOL. Data pengisian kuisioner & prospek siswa tetap tersimpan di sistem.</span>
                                </div>
                            </label>

                            <label class="flex items-start p-3 rounded-xl border border-rose-200 bg-rose-50/20 hover:bg-rose-50/50 cursor-pointer transition">
                                <input type="radio" name="mode" value="delete_permanent" class="mt-0.5 text-rose-600 focus:ring-rose-500">
                                <div class="ml-3">
                                    <span class="block text-xs font-bold text-rose-700">Hapus Permanen Data Siswa</span>
                                    <span class="block text-[11px] text-rose-600/80 mt-0.5">Menghapus seluruh rekaman kuisioner & riwayat follow-up siswa ini secara permanen dari database.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showDeleteModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-200 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-md shadow-rose-600/30 transition">
                            Eksekusi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Cek Rate Card Pasaran Otomatis -->
        <div x-show="showRateCardModal" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;">
            
            <div @click.outside="showRateCardModal = false"
                class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 overflow-hidden transform transition-all space-y-4">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="receipt" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Estimasi Rate Card Pasaran KOL</h3>
                            <p class="text-xs text-slate-500" x-text="rateCardStudentName + (rateCardHandle ? ' (@' + rateCardHandle + ')' : '')"></p>
                        </div>
                    </div>
                    <button type="button" @click="showRateCardModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <template x-if="rateCardData">
                    <div class="space-y-3.5">
                        <div class="flex items-center justify-between p-3.5 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200">
                            <div>
                                <span class="text-[10px] uppercase tracking-wider font-extrabold text-amber-700">Kategori / Tier Influencer</span>
                                <p class="text-base font-black text-slate-900" x-text="rateCardData.tier"></p>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-slate-500">Basis Follower Acuan</span>
                                <p class="text-sm font-extrabold text-amber-800" x-text="new Intl.NumberFormat('id-ID').format(rateCardData.followers_used) + ' Followers'"></p>
                            </div>
                        </div>

                        <div class="space-y-2 text-xs">
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 transition">
                                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                    <i data-lucide="image" class="w-4 h-4 text-pink-600"></i>
                                    Feed Instagram (1 Post)
                                </span>
                                <span class="font-black text-slate-900" x-text="rateCardData.feed_post"></span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 transition">
                                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                    <i data-lucide="clock" class="w-4 h-4 text-amber-600"></i>
                                    Story Instagram (1x - 3x)
                                </span>
                                <span class="font-black text-slate-900" x-text="rateCardData.story_post"></span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 hover:bg-slate-100/80 transition">
                                <span class="font-bold text-slate-700 flex items-center gap-1.5">
                                    <i data-lucide="video" class="w-4 h-4 text-purple-600"></i>
                                    Reels IG / TikTok Video (Short-form)
                                </span>
                                <span class="font-black text-slate-900" x-text="rateCardData.reels_tiktok"></span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-purple-50/70 border border-purple-200">
                                <span class="font-bold text-purple-900 flex items-center gap-1.5">
                                    <i data-lucide="crown" class="w-4 h-4 text-amber-500"></i>
                                    Paket Brand Ambassador Kampus
                                </span>
                                <span class="font-black text-purple-900" x-text="rateCardData.brand_ambassador_package"></span>
                            </div>
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50">
                                <span class="font-medium text-slate-600">Benchmark Engagement Rate:</span>
                                <span class="font-bold text-emerald-700" x-text="rateCardData.engagement_benchmark"></span>
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 italic leading-relaxed">
                            * Estimasi rate card ini dihitung otomatis berdasarkan standar benchmark industri media sosial di Indonesia untuk acuan negosiasi beasiswa atau insentif konten promosi UBSI.
                        </p>
                    </div>
                </template>

                <div class="pt-2 flex justify-end">
                    <button type="button" @click="showRateCardModal = false"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold bg-slate-900 text-white hover:bg-slate-800 transition">
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Link Karya Brand Ambassador (IG, TikTok, FB, YouTube) -->
        <div x-show="showWorkLinksModal" x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
            style="display: none;">
            
            <div @click.outside="showWorkLinksModal = false"
                class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 overflow-hidden transform transition-all space-y-4">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 text-white flex items-center justify-center shadow-md">
                            <i data-lucide="video" class="w-5 h-5 text-amber-300"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-extrabold text-slate-900">Link Karya Brand Ambassador</h3>
                            <p class="text-xs text-slate-500" x-text="'Portofolio & Publikasi: ' + workLinksForm.student_name"></p>
                        </div>
                    </div>
                    <button type="button" @click="showWorkLinksModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="workLinksForm.update_url" method="POST" class="space-y-3.5">
                    @csrf

                    <!-- Instagram Link -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-pink-700">
                                <i data-lucide="instagram" class="w-3.5 h-3.5"></i>
                                Link Karya Instagram (Reels / Postingan Feed)
                            </span>
                            <template x-if="workLinksForm.instagram">
                                <a :href="workLinksForm.instagram" target="_blank" class="text-[11px] text-pink-600 hover:underline inline-flex items-center gap-0.5">
                                    Buka <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </template>
                        </label>
                        <input type="url" name="instagram_link" x-model="workLinksForm.instagram" placeholder="https://www.instagram.com/reel/..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>

                    <!-- TikTok Link -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-slate-900">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                                Link Karya TikTok
                            </span>
                            <template x-if="workLinksForm.tiktok">
                                <a :href="workLinksForm.tiktok" target="_blank" class="text-[11px] text-slate-800 hover:underline inline-flex items-center gap-0.5">
                                    Buka <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </template>
                        </label>
                        <input type="url" name="tiktok_link" x-model="workLinksForm.tiktok" placeholder="https://www.tiktok.com/@.../video/..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>

                    <!-- Facebook Link -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-blue-700">
                                <i data-lucide="facebook" class="w-3.5 h-3.5"></i>
                                Link Karya Facebook
                            </span>
                            <template x-if="workLinksForm.facebook">
                                <a :href="workLinksForm.facebook" target="_blank" class="text-[11px] text-blue-600 hover:underline inline-flex items-center gap-0.5">
                                    Buka <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </template>
                        </label>
                        <input type="url" name="facebook_link" x-model="workLinksForm.facebook" placeholder="https://www.facebook.com/watch/..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>

                    <!-- YouTube Link -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1 flex items-center justify-between">
                            <span class="flex items-center gap-1.5 text-rose-700">
                                <i data-lucide="youtube" class="w-3.5 h-3.5"></i>
                                Link Karya YouTube (Shorts / Video)
                            </span>
                            <template x-if="workLinksForm.youtube">
                                <a :href="workLinksForm.youtube" target="_blank" class="text-[11px] text-rose-600 hover:underline inline-flex items-center gap-0.5">
                                    Buka <i data-lucide="external-link" class="w-3 h-3"></i>
                                </a>
                            </template>
                        </label>
                        <input type="url" name="youtube_link" x-model="workLinksForm.youtube" placeholder="https://youtube.com/shorts/... atau watch?v=..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                    </div>

                    <!-- Catatan / Laporan Konten -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan / Keterangan Konten</label>
                        <textarea name="notes" x-model="workLinksForm.notes" rows="2"
                            placeholder="Contoh: Sudah tayang 2 video promosi PMB UBSI gelombang 1, viewers mencapai 15k..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showWorkLinksModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white shadow-md shadow-purple-600/30 transition">
                            Simpan Link Karya
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
        function kolManager() {
            return {
                showModal: false,
                showDeleteModal: false,
                deleteCandidate: null,
                openSettingModal: false,
                showRateCardModal: false,
                showWorkLinksModal: false,
                selectedIds: [],
                allIds: {{ json_encode($candidates->pluck('id')->toArray()) }},
                rateCardData: null,
                rateCardStudentName: '',
                rateCardHandle: '',
                workLinksForm: {
                    instagram: '',
                    tiktok: '',
                    facebook: '',
                    youtube: '',
                    notes: '',
                    update_url: '',
                    student_name: '',
                },
                form: {
                    id: null,
                    name: '',
                    school: '',
                    instagram_handle: '',
                    instagram_followers: 0,
                    kol_status: 'kandidat',
                    kol_notes: '',
                    update_url: '',
                },
                toggleSelectAll(e) {
                    this.selectedIds = e.target.checked ? [...this.allIds] : [];
                },
                toggleSelect(id) {
                    if (this.selectedIds.includes(id)) {
                        this.selectedIds = this.selectedIds.filter(i => i !== id);
                    } else {
                        this.selectedIds.push(id);
                    }
                },
                openRateCardModal(data, name, handle) {
                    this.rateCardData = data || null;
                    this.rateCardStudentName = name || '';
                    this.rateCardHandle = handle || '';
                    this.showRateCardModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },
                openWorkLinksModal(data, studentId, studentName) {
                    const d = data || {};
                    this.workLinksForm = {
                        instagram: d.instagram || '',
                        tiktok: d.tiktok || '',
                        facebook: d.facebook || '',
                        youtube: d.youtube || '',
                        notes: d.notes || '',
                        update_url: '/kol-recommendations/' + studentId + '/save-work-links',
                        student_name: studentName || '',
                    };
                    this.showWorkLinksModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },
                openModal(data) {
                    this.form = Object.assign({}, data);
                    this.showModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                },
                openDeleteModal(data) {
                    this.deleteCandidate = Object.assign({}, data);
                    this.showDeleteModal = true;
                    this.$nextTick(() => {
                        if (window.lucide) {
                            window.lucide.createIcons();
                        }
                    });
                }
            }
        }
    </script>
    @endpush
</x-app-layout>

