<div class="p-3.5 bg-white rounded-2xl shadow-sm border {{ $borderClass ?? 'border-slate-200' }} text-xs space-y-2.5 transition duration-150 hover:shadow-md">
    <!-- School & District -->
    <div class="flex items-start justify-between gap-2">
        <div>
            <h4 class="font-extrabold text-slate-900 text-sm leading-snug">
                {{ $item->school ? $item->school->name : 'Sekolah' }}
            </h4>
            <div class="flex items-center gap-1.5 text-[11px] text-slate-500 mt-0.5">
                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400 shrink-0"></i>
                <span>{{ $item->school?->district ? $item->school->district->name : '-' }}</span>
            </div>
        </div>

        @php
            $cats = $item->agenda_categories;
            if (empty($cats) && $item->visit) {
                $cats = $item->visit->agenda_categories;
            }
            if (empty($cats)) {
                $cats = ['penjadwalan_sosialisasi'];
            }
            $cat = in_array(request('agenda_category'), $cats) ? request('agenda_category') : $cats[0];
            $attStatus = $item->invitation_attendance_status ?: ($item->visit?->invitation_attendance_status ?? null);
            $picPhone = $item->pic_phone ?: ($item->visit?->recipient_phone ?? '');
            $cleanPhone = preg_replace('/[^0-9]/', '', $picPhone);
            if (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            $picName = $item->pic_name ?: ($item->visit?->recipient_name ?: 'Bapak/Ibu');
            $schName = $item->school?->name ?: 'Sekolah';

            if (in_array('mengantar_undangan', $cats) && request('agenda_category') === 'mengantar_undangan') {
                $waMsg = "Halo {$picName}, kami dari Tim Marketing Universitas BSI (UBSI). Kami ingin mengonfirmasi respon/kehadiran terkait surat undangan acara kampus yang telah kami serahkan ke {$schName}. Apakah sudah ada konfirmasi kehadiran dari sekolah? Terima kasih.";
            } elseif (in_array('kunjungan_lainnya', $cats) && request('agenda_category') === 'kunjungan_lainnya') {
                $waMsg = "Halo {$picName}, kami dari Tim Marketing Universitas BSI (UBSI). Menindaklanjuti kunjungan silaturahmi/audiensi kami ke {$schName}, kami ingin mengonfirmasi tindak lanjut terkait pembicaraan kemarin. Terima kasih.";
            } else {
                $waMsg = "Halo {$picName}, kami dari Tim Marketing Universitas BSI (UBSI). Kami ingin menindaklanjuti rencana jadwal sosialisasi PMB & Beasiswa kampus UBSI di {$schName}. Terima kasih.";
            }
            $customWaLink = $cleanPhone ? "https://wa.me/{$cleanPhone}?text=" . rawurlencode($waMsg) : null;
        @endphp

        <div class="flex flex-col gap-1 items-end shrink-0">
            @foreach($cats as $c)
                @if($c === 'mengantar_undangan')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Kategori: Mengantar Undangan Acara">
                        <i data-lucide="mail" class="w-3 h-3 text-amber-600"></i>
                        <span>Antar Undangan</span>
                    </span>
                @elseif($c === 'kunjungan_lainnya')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200" title="Kategori: Kunjungan Lainnya">
                        <i data-lucide="clipboard-list" class="w-3 h-3 text-purple-600"></i>
                        <span>Kunjungan Lain</span>
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200" title="Kategori: Penjadwalan Sosialisasi">
                        <i data-lucide="calendar" class="w-3 h-3 text-blue-600"></i>
                        <span>Sosialisasi</span>
                    </span>
                @endif
            @endforeach
        </div>
    </div>

    @if($cat === 'mengantar_undangan')
        <!-- Box Mengantar Undangan -->
        <div class="p-2.5 rounded-xl bg-amber-50/70 border border-amber-200 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-800 flex items-center gap-1">
                    <i data-lucide="mail-check" class="w-3 h-3 text-amber-600"></i>
                    Konfirmasi Kehadiran
                </span>
                @if($attStatus === 'akan_hadir')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                        <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-600"></i>
                        Akan Hadir
                    </span>
                @elseif($attStatus === 'minta_ijin_dulu')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-blue-100 text-blue-900 border border-blue-300 flex items-center gap-1">
                        <i data-lucide="clock" class="w-3 h-3 text-blue-600"></i>
                        Minta Ijin Dulu
                    </span>
                @elseif($attStatus === 'tidak_bisa_hadir')
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-rose-100 text-rose-800 border border-rose-300 flex items-center gap-1">
                        <i data-lucide="x-circle" class="w-3 h-3 text-rose-600"></i>
                        Tidak Bisa Hadir
                    </span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                        Belum Dikonfirmasi
                    </span>
                @endif
            </div>
            <p class="text-[10.5px] text-amber-950">
                {{ $item->notes ?: ($item->description ?: ($item->visit?->notes ?: 'Undangan acara kampus UBSI telah diantar ke pihak sekolah.')) }}
            </p>
            @if($item->target_audience_count > 0)
                <div class="text-[10px] text-amber-850 font-bold flex items-center gap-1 pt-1 border-t border-amber-200/60">
                    <i data-lucide="users" class="w-3 h-3 text-amber-600"></i>
                    <span>Estimasi Delegasi: {{ number_format($item->target_audience_count) }} Peserta/Siswa</span>
                </div>
            @endif
        </div>
    @elseif($cat === 'kunjungan_lainnya')
        <!-- Box Kunjungan Lainnya -->
        <div class="p-2.5 rounded-xl bg-purple-50/70 border border-purple-200 space-y-1.5">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-800 flex items-center gap-1">
                    <i data-lucide="clipboard-list" class="w-3 h-3 text-purple-600"></i>
                    Tindak Lanjut Audiensi
                </span>
                @if($item->status === 'completed')
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300">Tuntas</span>
                @elseif($item->status === 'progress')
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold bg-blue-100 text-blue-800 border border-blue-300">Diproses</span>
                @else
                    <span class="px-2 py-0.5 rounded-full text-[9.5px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300">Perlu Tindak Lanjut</span>
                @endif
            </div>
            <p class="text-[10.5px] text-purple-950">
                {{ $item->notes ?: ($item->description ?: ($item->visit?->notes ?: 'Tindak lanjut audiensi, perizinan, atau silaturahmi formal dengan pihak sekolah.')) }}
            </p>
            @if($item->visit?->photo)
                <div class="pt-1 border-t border-purple-200/60 flex items-center justify-between text-[10px]">
                    <span class="text-purple-700 font-semibold flex items-center gap-1">
                        <i data-lucide="camera" class="w-3 h-3 text-purple-600"></i>
                        Foto Bukti Kunjungan
                    </span>
                    <a href="{{ asset('storage/' . $item->visit->photo) }}" target="_blank" class="px-1.5 py-0.5 rounded bg-purple-600 hover:bg-purple-700 text-white font-bold transition">
                        Buka Foto
                    </a>
                </div>
            @endif
        </div>
    @else
        <!-- Target / Fixed Schedule Box untuk Sosialisasi -->
        <div class="p-2.5 rounded-xl {{ $item->is_schedule_fixed ? 'bg-emerald-50 border border-emerald-300' : 'bg-slate-50 border border-slate-200' }}">
            <div class="flex items-center justify-between mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider {{ $item->is_schedule_fixed ? 'text-emerald-800' : 'text-slate-500' }}">
                    {{ $item->is_schedule_fixed ? 'Jadwal Sosialisasi FIX' : 'Target Sosialisasi' }}
                </span>
                @if($item->is_schedule_fixed)
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                @endif
            </div>

            @if($item->is_schedule_fixed)
                <div class="font-extrabold text-emerald-900 text-xs flex items-center gap-1">
                    <i data-lucide="calendar-check" class="w-3.5 h-3.5 text-emerald-600"></i>
                    <span>{{ $item->fixed_date ? $item->fixed_date->format('d M Y') : '-' }} &bull; {{ $item->fixed_time ?: 'Pagi' }}</span>
                </div>
                @if($item->socialization_location)
                    <p class="text-[11px] text-emerald-700 mt-0.5">Lokasi: {{ $item->socialization_location }}</p>
                @endif
            @else
                <div class="font-bold text-slate-800 text-xs flex items-center gap-1">
                    <i data-lucide="calendar" class="w-3.5 h-3.5 text-amber-500"></i>
                    <span>
                        @if($item->fixed_date)
                            {{ $item->fixed_date->format('d M Y') }}
                        @elseif($item->visit?->target_date)
                            {{ $item->visit->target_date->format('d M Y') }}
                        @else
                            {{ $item->visit?->target_month_year ?: 'Belum ada target' }}
                        @endif
                        @if($item->fixed_time || $item->visit?->target_time)
                            &bull; {{ $item->fixed_time ?: $item->visit?->target_time }}
                        @endif
                    </span>
                </div>
            @endif

            @if($item->target_audience_count || $item->visit?->grade_12_class_count)
                <div class="text-[10px] text-slate-500 mt-1 flex items-center gap-1">
                    <i data-lucide="users" class="w-3 h-3 text-slate-400"></i>
                    <span>
                        @if($item->target_audience_count)
                            Estimasi {{ number_format($item->target_audience_count) }} Siswa
                        @elseif($item->visit?->grade_12_class_count)
                            {{ $item->visit->grade_12_class_count }} Kelas 12
                        @endif
                        @if($item->socialization_location)
                            &bull; {{ $item->socialization_location }}
                        @elseif($item->visit?->socialization_method)
                            &bull; {{ $item->visit->socialization_method === 'Lainnya' ? $item->visit->other_activity_name : $item->visit->socialization_method }}
                        @endif
                    </span>
                </div>
            @endif
        </div>
    @endif

    <!-- PIC & WhatsApp Action -->
    <div class="pt-1 border-t border-slate-100 flex items-center justify-between">
        <div class="min-w-0 flex-1 pr-2">
            <p class="font-bold text-slate-800 truncate text-[11px]" title="{{ $picName }}">
                {{ $picName }}
            </p>
            <p class="text-[10px] text-slate-400 truncate">
                {{ $item->pic_position ?: ($item->visit?->recipient_position ?: 'Kontak Sekolah') }}
            </p>
        </div>

        <div class="flex items-center gap-1 shrink-0">
            @if($customWaLink)
                <a href="{{ $customWaLink }}" target="_blank" rel="noopener" 
                   class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition" 
                   title="Kirim WhatsApp ke PIC">
                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                </a>
            @endif
            @if($picPhone)
                <a href="tel:{{ $picPhone }}" 
                   class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition" 
                   title="Telepon PIC">
                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Notes snippet if any (for sosialisasi) -->
    @if($cat === 'penjadwalan_sosialisasi' && ($item->notes || $item->description))
        <p class="text-[11px] text-slate-600 italic bg-slate-50/70 p-1.5 rounded-lg border border-slate-100 line-clamp-2">
            "{{ Str::limit($item->notes ?: $item->description, 80) }}"
        </p>
    @endif

    <!-- Card Action Buttons -->
    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5">
        <div class="text-[10px] text-slate-400 truncate">
            {{ $item->followup_date ? $item->followup_date->format('d/m/Y') : '' }} &bull; {{ $item->user?->name ?: 'Marketing' }}
        </div>

        <div class="flex items-center gap-1.5 shrink-0">
            <!-- Pindahkan Status Dropdown -->
            <div x-data="{ openMove: false }" class="relative inline-block text-left">
                <button @click="openMove = !openMove" type="button" 
                    class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition cursor-pointer"
                    title="Pindahkan Status">
                    <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                </button>
                <div x-show="openMove" @click.away="openMove = false" x-cloak 
                    class="absolute right-0 bottom-full mb-1 w-52 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 border border-slate-100 z-50 py-1.5 text-xs">
                    <p class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pindahkan Status:</p>

                    @if($cat === 'mengantar_undangan')
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-semibold flex items-center gap-2 {{ $item->status === 'pending' && empty($attStatus) ? 'bg-amber-50 text-amber-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Menunggu Respon</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="progress">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-blue-50 text-slate-700 hover:text-blue-800 font-semibold flex items-center gap-2 {{ $attStatus === 'minta_ijin_dulu' || $item->status === 'progress' ? 'bg-blue-50 text-blue-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>Minta Ijin Dulu</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-semibold flex items-center gap-2 {{ $attStatus === 'akan_hadir' || $item->status === 'completed' ? 'bg-emerald-50 text-emerald-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Akan Hadir</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="failed">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-rose-50 text-slate-700 hover:text-rose-800 font-semibold flex items-center gap-2 {{ $attStatus === 'tidak_bisa_hadir' || $item->status === 'failed' ? 'bg-rose-50 text-rose-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <span>Tidak Bisa Hadir</span>
                            </button>
                        </form>
                    @elseif($cat === 'kunjungan_lainnya')
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-semibold flex items-center gap-2 {{ $item->status === 'pending' ? 'bg-amber-50 text-amber-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Perlu Tindak Lanjut</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="progress">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-blue-50 text-slate-700 hover:text-blue-800 font-semibold flex items-center gap-2 {{ $item->status === 'progress' ? 'bg-blue-50 text-blue-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>Sedang Diproses</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-semibold flex items-center gap-2 {{ $item->status === 'completed' ? 'bg-emerald-50 text-emerald-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Tindak Lanjut Selesai</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="failed">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-slate-50 text-slate-700 hover:text-slate-800 font-semibold flex items-center gap-2 {{ $item->status === 'failed' ? 'bg-slate-50 text-slate-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-slate-500"></span>
                                <span>Ditutup / Selesai</span>
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="pending">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-semibold flex items-center gap-2 {{ $item->status === 'pending' && !$item->is_schedule_fixed ? 'bg-amber-50 text-amber-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Menunggu Konfirmasi</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="progress">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-blue-50 text-slate-700 hover:text-blue-800 font-semibold flex items-center gap-2 {{ $item->status === 'progress' && !$item->is_schedule_fixed ? 'bg-blue-50 text-blue-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                <span>Koordinasi Jadwal</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-semibold flex items-center gap-2 {{ $item->status === 'completed' || $item->is_schedule_fixed ? 'bg-emerald-50 text-emerald-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Jadwal FIX Terkunci</span>
                            </button>
                        </form>
                        <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="agenda_category" value="{{ request('agenda_category', $cat) }}">
                            <input type="hidden" name="status" value="failed">
                            <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-rose-50 text-slate-700 hover:text-rose-800 font-semibold flex items-center gap-2 {{ $item->status === 'failed' ? 'bg-rose-50 text-rose-800 font-bold' : '' }}">
                                <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                <span>Belum Diizinkan</span>
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Delete Button -->
            <button type="button" 
                    onclick="openDeleteModal({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}')"
                    class="p-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-400 transition cursor-pointer" 
                    title="Hapus riwayat follow up sekolah ini">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>

            <!-- Main Action Button according to category -->
            @if($cat === 'mengantar_undangan')
                <button type="button" 
                        onclick="openInvitationModal(
                            {{ $item->id }}, 
                            '{{ addslashes($item->school?->name ?? 'Sekolah') }}', 
                            '{{ $attStatus ?: 'belum_dikonfirmasi' }}', 
                            {{ (int)($item->target_audience_count ?? 0) }}, 
                            '{{ addslashes($picName) }}', 
                            '{{ addslashes($picPhone) }}', 
                            '{{ addslashes($item->notes ?: ($item->description ?? '')) }}'
                        )"
                        class="px-2.5 py-1 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-[11px] shadow-xs flex items-center gap-1 transition cursor-pointer"
                        title="Update Status Kehadiran Undangan">
                    <i data-lucide="mail-check" class="w-3 h-3 text-white"></i>
                    <span>Respon Undangan</span>
                </button>
            @elseif($cat === 'kunjungan_lainnya')
                <button type="button" 
                        onclick="openGeneralVisitModal(
                            {{ $item->id }}, 
                            '{{ addslashes($item->school?->name ?? 'Sekolah') }}', 
                            '{{ $item->status }}', 
                            '{{ addslashes($picName) }}', 
                            '{{ addslashes($picPhone) }}', 
                            '{{ addslashes($item->notes ?: ($item->description ?? '')) }}'
                        )"
                        class="px-2.5 py-1 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-[11px] shadow-xs flex items-center gap-1 transition cursor-pointer"
                        title="Update Hasil Tindak Lanjut Audiensi">
                    <i data-lucide="clipboard-edit" class="w-3 h-3 text-white"></i>
                    <span>Tindak Lanjut</span>
                </button>
            @else
                <!-- Kunci / Ubah Jadwal FIX Sosialisasi -->
                <button type="button" 
                        onclick="openScheduleModal(
                            {{ $item->id }}, 
                            '{{ addslashes($item->school?->name ?? 'Sekolah') }}', 
                            '{{ $item->status }}', 
                            '{{ $item->fixed_date ? $item->fixed_date->format('Y-m-d') : ($item->visit?->target_date ? $item->visit->target_date->format('Y-m-d') : '') }}', 
                            '{{ addslashes($item->fixed_time ?: ($item->visit?->target_time ?? '')) }}', 
                            '{{ addslashes($picName) }}', 
                            '{{ addslashes($picPhone) }}', 
                            '{{ addslashes($item->socialization_location ?: ($item->visit?->socialization_method === 'Lainnya' ? $item->visit->other_activity_name : $item->visit?->socialization_method ?? '')) }}', 
                            '{{ addslashes($item->notes ?: ($item->description ?? '')) }}', 
                            {{ $item->is_schedule_fixed ? 1 : 0 }}
                        )"
                        class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-brand-600 text-white font-bold text-[11px] shadow-xs flex items-center gap-1 transition cursor-pointer">
                    <i data-lucide="calendar-check" class="w-3 h-3 text-amber-400"></i>
                    <span>{{ $item->is_schedule_fixed ? 'Ubah Jadwal' : 'Kunci Jadwal FIX' }}</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Hanya untuk Sosialisasi: Konfirmasi Sosialisasi Selesai -->
    @if($cat === 'penjadwalan_sosialisasi')
        @if($item->socialization_completed_at)
            <a href="{{ route('socializations.completed.index') }}"
                class="flex items-center justify-center gap-1.5 rounded-xl bg-emerald-50 border border-emerald-200 px-3 py-2 text-[11px] font-bold text-emerald-800 hover:bg-emerald-100 transition">
                <i data-lucide="circle-check" class="w-3.5 h-3.5"></i>
                <span>Sosialisasi terlaksana {{ $item->socialization_completed_at->format('d M Y') }}</span>
            </a>
        @elseif($item->is_schedule_fixed || $item->status === 'completed')
            <div x-data="{ showCompletionForm: false }" class="pt-1">
                <button type="button" @click="showCompletionForm = !showCompletionForm"
                    class="w-full flex items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-[11px] font-bold text-white hover:bg-emerald-700 transition">
                    <i data-lucide="presentation" class="w-3.5 h-3.5"></i>
                    <span>Konfirmasi Sosialisasi Selesai</span>
                </button>
                <form x-show="showCompletionForm" x-cloak method="POST"
                    action="{{ route('socializations.completed.confirm', $item) }}"
                    class="mt-2 space-y-2 rounded-xl border border-emerald-200 bg-emerald-50 p-2.5">
                    @csrf
                    @method('PUT')
                    <label class="block text-[10px] font-bold text-emerald-900">
                        Tanggal kegiatan terlaksana
                        <input type="date" name="socialization_completed_at" max="{{ today()->toDateString() }}"
                            value="{{ today()->toDateString() }}" required
                            class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs text-slate-800">
                    </label>
                    <label class="block text-[10px] font-bold text-emerald-900">
                        Catatan presentasi/kegiatan (opsional)
                        <textarea name="socialization_completion_notes" rows="2" maxlength="5000"
                            class="mt-1 w-full rounded-lg border border-emerald-200 px-2 py-1.5 text-xs text-slate-800"
                            placeholder="Contoh: presentasi di kelas XII, 120 peserta"></textarea>
                    </label>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showCompletionForm = false"
                            class="rounded-lg px-2.5 py-1.5 text-[10px] font-bold text-slate-600 hover:bg-white">Batal</button>
                        <button type="submit"
                            class="rounded-lg bg-emerald-700 px-2.5 py-1.5 text-[10px] font-bold text-white hover:bg-emerald-800">Simpan Konfirmasi</button>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>
