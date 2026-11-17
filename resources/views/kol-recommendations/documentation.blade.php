<x-app-layout title="Hasil Dokumentasi &amp; Karya KOL">
    <div x-data="kolDocManager()" class="space-y-6">

        <!-- Header & Navigasi -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-1">
                    <a href="{{ route('kol-recommendations.index') }}" class="hover:text-pink-600 transition flex items-center gap-1">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5 text-pink-500"></i>
                        <span>Rekomendasi KOL</span>
                    </a>
                    <span>/</span>
                    <span class="text-slate-800 font-bold">Hasil Dokumentasi KOL</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2.5">
                    <span class="w-9 h-9 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-purple-600/30">
                        <i data-lucide="video" class="w-5 h-5 text-amber-300"></i>
                    </span>
                    <span>Hasil Dokumentasi &amp; Karya Brand Ambassador</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Portofolio karya kreatif, video reels/TikTok promosi PMB, dan rekap penayangan konten Brand Ambassador resmi kampus.
                </p>
            </div>

            <!-- Tombol Navigasi Cepat -->
            <div class="flex items-center gap-2">
                <a href="{{ route('kol-recommendations.index') }}"
                    class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition shadow-2xs flex items-center gap-1.5">
                    <i data-lucide="users" class="w-4 h-4 text-pink-600"></i>
                    <span>Daftar Calon KOL</span>
                </a>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <!-- 1. Total Brand Ambassador -->
            <div class="p-4 rounded-2xl bg-gradient-to-br from-purple-900 to-indigo-950 text-white shadow-lg shadow-purple-950/20 relative overflow-hidden">
                <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-purple-500/10 rounded-full blur-xl pointer-events-none"></div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-purple-200">Brand Ambassador Aktif</span>
                    <span class="w-8 h-8 rounded-xl bg-purple-500/30 text-amber-300 flex items-center justify-center">
                        <i data-lucide="crown" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-black">{{ number_format($stats['total_ambassadors']) }}</span>
                    <span class="text-[11px] text-purple-300">Siswa Duta Kampus</span>
                </div>
            </div>

            <!-- 2. Karya Instagram -->
            <div class="p-4 rounded-2xl bg-white border border-pink-100 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600">Karya Instagram</span>
                    <span class="w-8 h-8 rounded-xl bg-gradient-to-br from-pink-500 to-rose-600 text-white flex items-center justify-center shadow-xs">
                        <i data-lucide="instagram" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_ig_works']) }}</span>
                    <span class="text-[11px] text-slate-400">Post &amp; Reels</span>
                </div>
            </div>

            <!-- 3. Karya TikTok -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600">Video TikTok</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-900 text-cyan-400 flex items-center justify-center shadow-xs">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_tiktok_works']) }}</span>
                    <span class="text-[11px] text-slate-400">Video Promosi</span>
                </div>
            </div>

            <!-- 4. Karya YT & FB -->
            <div class="p-4 rounded-2xl bg-white border border-rose-100 shadow-soft">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-600">YouTube &amp; FB</span>
                    <span class="w-8 h-8 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-xs">
                        <i data-lucide="youtube" class="w-4 h-4"></i>
                    </span>
                </div>
                <div class="mt-3 flex items-baseline space-x-2">
                    <span class="text-2xl font-black text-slate-900">{{ number_format($stats['total_yt_fb_works']) }}</span>
                    <span class="text-[11px] text-slate-400">Shorts &amp; Konten</span>
                </div>
            </div>
        </div>

        <!-- Filter & Pencarian -->
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-soft">
            <form method="GET" action="{{ route('kol-recommendations.documentation') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari nama, sekolah, username..."
                        class="w-full pl-10 pr-3.5 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 font-medium">
                </div>

                <!-- Filter Platform -->
                <div>
                    <select name="platform" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 font-medium text-slate-700">
                        <option value="all">Semua Platform Konten</option>
                        <option value="instagram" {{ request('platform') === 'instagram' ? 'selected' : '' }}>Khusus Karya Instagram</option>
                        <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>Khusus Karya TikTok</option>
                        <option value="facebook" {{ request('platform') === 'facebook' ? 'selected' : '' }}>Khusus Karya Facebook</option>
                        <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>Khusus Karya YouTube</option>
                    </select>
                </div>

                <!-- Filter Status -->
                <div>
                    <select name="status" onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 font-medium text-slate-700">
                        <option value="all">Semua Status Duta Kampus</option>
                        <option value="brand_ambassador" {{ request('status') === 'brand_ambassador' ? 'selected' : '' }}>Brand Ambassador Resmi</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif KOL UBSI</option>
                        <option value="bersedia" {{ request('status') === 'bersedia' ? 'selected' : '' }}>Bersedia / Deal</option>
                    </select>
                </div>

                <!-- Tombol Filter & Reset -->
                <div class="flex items-center gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold rounded-xl text-xs transition shadow-xs flex items-center justify-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Terapkan</span>
                    </button>
                    @if(request()->hasAny(['search', 'platform', 'status']))
                        <a href="{{ route('kol-recommendations.documentation') }}"
                            class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold rounded-xl text-xs transition" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Daftar Dokumentasi Karya (Card Grid Layout) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($items as $item)
                @php
                    $wl = $item->work_links ?? [];
                    $hasIg = !empty($wl['instagram']);
                    $hasTt = !empty($wl['tiktok']);
                    $hasFb = !empty($wl['facebook']);
                    $hasYt = !empty($wl['youtube']);
                    $hasWork = ($hasIg || $hasTt || $hasFb || $hasYt);
                    $cleanHandle = $item->clean_instagram_handle;
                    $waNumber = $item->formatted_student_phone;

                    $waMessage = "Halo {$item->student_name}, kami dari tim Brand Ambassador Kampus {$item->campus?->name}. Kami ingin mengecek progres dan jadwal penayangan konten promosi PMB UBSI selanjutnya. Apakah ada update atau kendala dalam pembuatan karyanya?";
                    $waUrl = $waNumber ? "https://wa.me/{$waNumber}?text=" . urlencode($waMessage) : '#';
                @endphp

                <div class="bg-white rounded-3xl border border-slate-200/90 hover:border-purple-300 shadow-soft hover:shadow-md transition duration-200 flex flex-col justify-between overflow-hidden group">
                    <!-- Card Top Banner / Profil Siswa -->
                    <div class="p-5 space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-purple-600 to-indigo-600 text-white font-black flex items-center justify-center text-sm shadow-md shadow-purple-600/20 shrink-0">
                                    {{ strtoupper(substr($item->student_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-extrabold text-slate-900 text-sm truncate group-hover:text-purple-700 transition">
                                        {{ $item->student_name }}
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium truncate">
                                        {{ $item->school_display_name }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-slate-400">
                                        <span>{{ $item->student_class ?: 'Kelas XII' }}</span>
                                        <span>&bull;</span>
                                        <span>{{ $item->campus?->name ?? 'UBSI' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Badge Status -->
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-900 border border-purple-200 shrink-0">
                                <i data-lucide="crown" class="w-3 h-3 text-amber-500"></i>
                                <span>{{ $item->kol_status_label }}</span>
                            </span>
                        </div>

                        <!-- Stats Followers & Handle Media Sosial -->
                        <div class="grid grid-cols-2 gap-2 p-2.5 rounded-2xl bg-slate-50 border border-slate-100 text-xs">
                            <div class="space-y-0.5">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Followers IG</span>
                                <div class="font-black text-slate-800 flex items-center gap-1">
                                    <i data-lucide="instagram" class="w-3.5 h-3.5 text-pink-600"></i>
                                    <span>{{ number_format($item->instagram_followers) }}</span>
                                </div>
                                @if($cleanHandle)
                                    <p class="text-[10px] text-pink-600 font-bold truncate">@<span>{{ $cleanHandle }}</span></p>
                                @endif
                            </div>
                            <div class="space-y-0.5">
                                <span class="text-[10px] uppercase tracking-wider font-bold text-slate-400">Followers TikTok</span>
                                <div class="font-black text-slate-800 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 fill-current text-slate-900" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                                    <span>{{ number_format($item->tiktok_followers ?: 0) }}</span>
                                </div>
                                @if($item->tiktok_handle)
                                    <p class="text-[10px] text-slate-600 font-bold truncate">{{ $item->tiktok_handle }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Link Publikasi Karya (Interactive Tiles) -->
                        <div class="space-y-2">
                            <span class="text-[11px] font-bold text-slate-700 flex items-center justify-between">
                                <span>Tautan Karya Publikasi:</span>
                                @if($hasWork)
                                    <span class="text-[10px] text-emerald-600 font-black flex items-center gap-1">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Sudah Ada Karya
                                    </span>
                                @else
                                    <span class="text-[10px] text-amber-600 font-semibold italic">Belum diunggah</span>
                                @endif
                            </span>

                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <!-- Instagram Work Link -->
                                @if($hasIg)
                                    <a href="{{ $wl['instagram'] }}" target="_blank"
                                        class="flex items-center justify-between p-2 rounded-xl bg-pink-50 hover:bg-pink-100/80 text-pink-700 border border-pink-200 font-bold transition group/link"
                                        title="Buka Post / Reels Instagram: {{ $wl['instagram'] }}">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <i data-lucide="instagram" class="w-3.5 h-3.5 text-pink-600 shrink-0"></i>
                                            <span class="truncate">Instagram</span>
                                        </div>
                                        <i data-lucide="external-link" class="w-3 h-3 text-pink-400 group-hover/link:translate-x-0.5 transition shrink-0"></i>
                                    </a>
                                @else
                                    <div class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-100 text-[11px]">
                                        <i data-lucide="instagram" class="w-3.5 h-3.5 text-slate-300"></i>
                                        <span>IG: Kosong</span>
                                    </div>
                                @endif

                                <!-- TikTok Work Link -->
                                @if($hasTt)
                                    <a href="{{ $wl['tiktok'] }}" target="_blank"
                                        class="flex items-center justify-between p-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold transition group/link"
                                        title="Buka Video TikTok: {{ $wl['tiktok'] }}">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <svg class="w-3.5 h-3.5 fill-current text-cyan-400 shrink-0" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                                            <span class="truncate">TikTok</span>
                                        </div>
                                        <i data-lucide="external-link" class="w-3 h-3 text-slate-400 group-hover/link:translate-x-0.5 transition shrink-0"></i>
                                    </a>
                                @else
                                    <div class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-100 text-[11px]">
                                        <svg class="w-3.5 h-3.5 fill-current text-slate-300" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1.04-.1z"/></svg>
                                        <span>TikTok: Kosong</span>
                                    </div>
                                @endif

                                <!-- Facebook Work Link -->
                                @if($hasFb)
                                    <a href="{{ $wl['facebook'] }}" target="_blank"
                                        class="flex items-center justify-between p-2 rounded-xl bg-blue-50 hover:bg-blue-100/80 text-blue-700 border border-blue-200 font-bold transition group/link"
                                        title="Buka Facebook: {{ $wl['facebook'] }}">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <i data-lucide="facebook" class="w-3.5 h-3.5 text-blue-600 shrink-0"></i>
                                            <span class="truncate">Facebook</span>
                                        </div>
                                        <i data-lucide="external-link" class="w-3 h-3 text-blue-400 group-hover/link:translate-x-0.5 transition shrink-0"></i>
                                    </a>
                                @else
                                    <div class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-100 text-[11px]">
                                        <i data-lucide="facebook" class="w-3.5 h-3.5 text-slate-300"></i>
                                        <span>FB: Kosong</span>
                                    </div>
                                @endif

                                <!-- YouTube Work Link -->
                                @if($hasYt)
                                    <a href="{{ $wl['youtube'] }}" target="_blank"
                                        class="flex items-center justify-between p-2 rounded-xl bg-rose-50 hover:bg-rose-100/80 text-rose-700 border border-rose-200 font-bold transition group/link"
                                        title="Buka YouTube: {{ $wl['youtube'] }}">
                                        <div class="flex items-center gap-1.5 truncate">
                                            <i data-lucide="youtube" class="w-3.5 h-3.5 text-rose-600 shrink-0"></i>
                                            <span class="truncate">YouTube</span>
                                        </div>
                                        <i data-lucide="external-link" class="w-3 h-3 text-rose-400 group-hover/link:translate-x-0.5 transition shrink-0"></i>
                                    </a>
                                @else
                                    <div class="flex items-center gap-1.5 p-2 rounded-xl bg-slate-50 text-slate-400 border border-slate-100 text-[11px]">
                                        <i data-lucide="youtube" class="w-3.5 h-3.5 text-slate-300"></i>
                                        <span>YT: Kosong</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Catatan Laporan Hasil Penayangan -->
                        @if(!empty($wl['notes']) || !empty($item->kol_notes))
                            <div class="p-3 rounded-2xl bg-purple-50/60 border border-purple-100 text-xs text-purple-950 space-y-1">
                                <div class="font-extrabold text-[11px] text-purple-900 flex items-center gap-1">
                                    <i data-lucide="file-text" class="w-3 h-3 text-purple-600"></i>
                                    <span>Laporan / Keterangan Konten:</span>
                                </div>
                                <p class="text-[11px] leading-relaxed line-clamp-2 italic text-purple-800">
                                    &ldquo;{{ $wl['notes'] ?: $item->kol_notes }}&rdquo;
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Card Bottom Actions -->
                    <div class="p-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <!-- WhatsApp Hubungi Siswa -->
                            @if($waNumber)
                                <a href="{{ $waUrl }}" target="_blank"
                                    class="p-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white shadow-xs transition"
                                    title="Hubungi WhatsApp Brand Ambassador">
                                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                                </a>
                            @endif

                            <!-- Cek Rate Card Pasaran -->
                            <button type="button"
                                @click="openRateCardModal({{ json_encode($item->estimated_rate_card) }}, '{{ addslashes($item->student_name) }}', '{{ addslashes($item->clean_instagram_handle) }}')"
                                class="px-2.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-900 border border-amber-200 text-xs font-bold transition flex items-center gap-1"
                                title="Cek Nilai Rate Card Pasaran Akun">
                                <i data-lucide="receipt" class="w-3.5 h-3.5 text-amber-600"></i>
                                <span class="hidden sm:inline">Rate Card</span>
                            </button>
                        </div>

                        <!-- Tombol Kelola / Update Link Karya -->
                        <button type="button"
                            @click="openWorkLinksModal({{ json_encode($item->work_links ?? []) }}, {{ $item->id }}, '{{ addslashes($item->student_name) }}')"
                            class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white text-xs font-black shadow-xs transition flex items-center gap-1.5">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span>{{ $hasWork ? 'Kelola Karya' : '+ Upload Link Karya' }}</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200 p-8">
                    <div class="max-w-md mx-auto space-y-3">
                        <div class="w-16 h-16 mx-auto rounded-3xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-inner">
                            <i data-lucide="video" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-base font-bold text-slate-800">
                            Belum Ada Dokumentasi Karya Brand Ambassador
                        </h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Siswa yang status rekrutmennya telah menjadi <strong>Brand Ambassador Resmi</strong> atau <strong>Aktif</strong> di menu <em>Daftar Calon KOL</em> akan tampil di sini untuk pemantauan dan pengelolaan link karya.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('kol-recommendations.index') }}"
                                class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 text-white text-xs font-bold shadow-md shadow-pink-600/30 transition">
                                <i data-lucide="users" class="w-4 h-4"></i>
                                <span>Buka Daftar Calon KOL</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($items->hasPages())
            <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
                {{ $items->links() }}
            </div>
        @endif

        <!-- Modal Kelola & Upload Link Karya (IG, TikTok, FB, YouTube) -->
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
                            <h3 class="text-sm font-extrabold text-slate-900">Kelola Link Karya &amp; Publikasi</h3>
                            <p class="text-xs text-slate-500" x-text="'Brand Ambassador: ' + workLinksForm.student_name"></p>
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
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Laporan / Jumlah Tayangan</label>
                        <textarea name="notes" x-model="workLinksForm.notes" rows="2"
                            placeholder="Contoh: Sudah tayang 2 reels promosi PMB UBSI, total viewers mencapai 18.000..."
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
                            * Estimasi rate card ini dihitung otomatis berdasarkan standar benchmark industri media sosial di Indonesia untuk acuan penawaran beasiswa atau insentif konten promosi UBSI.
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

    </div>

    @push('scripts')
    <script>
        function kolDocManager() {
            return {
                showWorkLinksModal: false,
                showRateCardModal: false,
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
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
