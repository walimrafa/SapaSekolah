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
                            <i data-lucide="zap" class="w-3.5 h-3.5 mr-1.5 text-emerald-400"></i> Otomatis &ge; 2.000 Followers
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-800/80 text-slate-300 border border-slate-700">
                            <i data-lucide="shield-check" class="w-3 h-3 mr-1 text-amber-400"></i> Administrator, Pimpinan &amp; Koordinator
                        </span>
                    </div>

                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Rekomendasi KOL &amp; Student Influencer
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Data siswa yang memiliki akun Instagram dengan minimal <strong>2.000 followers</strong> secara otomatis tersaring dari Kuisioner Sosialisasi dan Pendaftaran Siswa Support Event. Cocok untuk direkrut menjadi Brand Ambassador dan Duta Kampus UBSI.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
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
                <span class="text-[11px] text-pink-600 font-semibold">&ge; 2.000 Followers</span>
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
                        <option value="2000" {{ request('min_followers', '2000') == '2000' ? 'selected' : '' }}>Min &ge; 2.000 Follower</option>
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

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4 min-w-[200px]">Siswa &amp; Kontak</th>
                            <th class="py-3 px-4 min-w-[180px]">Asal Sekolah &amp; Kelas</th>
                            <th class="py-3 px-4 min-w-[180px]">Instagram &amp; Followers</th>
                            <th class="py-3 px-4 min-w-[140px]">Sumber Data</th>
                            <th class="py-3 px-4 min-w-[140px]">Status Rekrutmen</th>
                            <th class="py-3 px-4 min-w-[140px] text-center">Aksi &amp; WhatsApp</th>
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
                            <tr class="hover:bg-pink-50/30 transition">
                                <!-- No -->
                                <td class="py-3 px-4 text-center font-bold text-slate-400">
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

                                <!-- Instagram & Followers -->
                                <td class="py-3 px-4">
                                    <div class="space-y-1.5">
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

                                        <div>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-black bg-gradient-to-r from-pink-600 to-rose-600 text-white shadow-sm">
                                                <i data-lucide="award" class="w-3 h-3 mr-1 text-amber-300"></i>
                                                {{ number_format($candidate->instagram_followers) }} Followers
                                            </span>
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
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs border {{ $statusBadgeClass }}">
                                            {{ $candidate->kol_status_label }}
                                        </span>
                                        @if($candidate->kol_notes)
                                            <p class="text-[10px] text-slate-500 italic line-clamp-2 max-w-[160px]" title="{{ $candidate->kol_notes }}">
                                                &ldquo;{{ $candidate->kol_notes }}&rdquo;
                                            </p>
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi & WhatsApp -->
                                <td class="py-3 px-4 text-center">
                                    <div class="flex items-center justify-center space-x-1.5">
                                        <!-- WhatsApp Recruitment Quick Button -->
                                        @if($waNumber)
                                            <a href="{{ $waUrl }}" target="_blank"
                                                class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition hover:scale-105"
                                                title="Hubungi Siswa via WhatsApp dengan draft tawaran Brand Ambassador">
                                                <i data-lucide="message-circle" class="w-3.5 h-3.5"></i>
                                                <span class="hidden sm:inline">Hubungi WA</span>
                                            </a>
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
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="max-w-md mx-auto space-y-3">
                                        <div class="w-16 h-16 mx-auto rounded-full bg-pink-50 border border-pink-100 flex items-center justify-center text-pink-400">
                                            <i data-lucide="sparkles" class="w-8 h-8"></i>
                                        </div>
                                        <h3 class="text-base font-bold text-slate-800">
                                            Belum Ada Calon KOL yang Memenuhi Kriteria
                                        </h3>
                                        <p class="text-xs text-slate-500 leading-relaxed">
                                            Siswa yang mengisi kuisioner sosialisasi atau formulir pendaftaran event dengan followers Instagram &ge; 2.000 akan otomatis masuk ke daftar ini.
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

                    <!-- Status Rekrutmen -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Status Rekrutmen KOL</label>
                        <select name="kol_status" x-model="form.kol_status" required
                            class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500 font-semibold text-slate-800">
                            <option value="kandidat">Kandidat (Baru Masuk)</option>
                            <option value="dihubungi">Sudah Dihubungi (Follow Up WA / DM)</option>
                            <option value="bersedia">Bersedia / Deal Menjadi Brand Ambassador</option>
                            <option value="ditolak">Tidak Bersedia / Menolak Tawaran</option>
                            <option value="aktif">Aktif KOL UBSI (Sedang Berjalan)</option>
                        </select>
                    </div>

                    <!-- Catatan Rekrutmen / Hasil Kontak -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Negosiasi / Rencana Kerjasama</label>
                        <textarea name="kol_notes" x-model="form.kol_notes" rows="3"
                            placeholder="Contoh: Siswa menyambut baik tawaran beasiswa, bersedia membuat konten reels promosi penerimaan mahasiswa baru..."
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 focus:ring-2 focus:ring-pink-500/20 focus:border-pink-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showModal = false"
                            class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2 rounded-xl text-xs font-bold bg-pink-600 hover:bg-pink-500 text-white shadow-md shadow-pink-600/30 transition">
                            Simpan Perubahan
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
                openModal(data) {
                    this.form = Object.assign({}, data);
                    this.showModal = true;
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

