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

        @if($item->visit_id)
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200 shrink-0" title="Data Otomatis dari Hasil Kunjungan Lapangan">
                <i data-lucide="navigation-2" class="w-3 h-3"></i>
                <span>Kunjungan</span>
            </span>
        @endif
    </div>

    <!-- Target / Fixed Schedule Box -->
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

    <!-- PIC & WhatsApp Action -->
    <div class="pt-1 border-t border-slate-100 flex items-center justify-between">
        <div class="min-w-0 flex-1 pr-2">
            <p class="font-bold text-slate-800 truncate text-[11px]" title="{{ $item->pic_name ?: ($item->visit?->recipient_name ?: 'PIC Sekolah') }}">
                {{ $item->pic_name ?: ($item->visit?->recipient_name ?: 'PIC Sekolah') }}
            </p>
            <p class="text-[10px] text-slate-400 truncate">
                {{ $item->pic_position ?: ($item->visit?->recipient_position ?: 'Kontak Sekolah') }}
            </p>
        </div>

        <div class="flex items-center gap-1 shrink-0">
            @if($item->wa_link)
                <a href="{{ $item->wa_link }}" target="_blank" rel="noopener" 
                   class="p-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 transition" 
                   title="Kirim WhatsApp Konfirmasi Jadwal FIX">
                    <i data-lucide="message-square" class="w-3.5 h-3.5"></i>
                </a>
            @endif
            @if($item->pic_phone)
                <a href="tel:{{ $item->pic_phone }}" 
                   class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 transition" 
                   title="Telepon PIC">
                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                </a>
            @endif
        </div>
    </div>

    <!-- Notes snippet if any -->
    @if($item->notes || $item->description)
        <p class="text-[11px] text-slate-600 italic bg-slate-50/70 p-1.5 rounded-lg border border-slate-100 line-clamp-2">
            "{{ Str::limit($item->notes ?: $item->description, 80) }}"
        </p>
    @endif

    <!-- Card Action Buttons -->
    <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5">
        <div class="text-[10px] text-slate-400 truncate">
            {{ $item->followup_date ? $item->followup_date->format('d/m/Y') : '' }} &bull; {{ $item->user?->name ?: 'Marketing' }}
        </div>

        <div class="flex items-center gap-1 shrink-0">
            <!-- Quick Move Category Dropdown -->
            <div x-data="{ openMove: false }" class="relative inline-block text-left">
                <button @click="openMove = !openMove" type="button" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 transition cursor-pointer" title="Pindah Kategori Langsung">
                    <i data-lucide="arrow-right-left" class="w-3.5 h-3.5"></i>
                </button>
                <div x-show="openMove" @click.away="openMove = false" x-cloak class="absolute right-0 bottom-full mb-1 w-48 rounded-2xl bg-white shadow-xl ring-1 ring-black/5 border border-slate-100 z-50 py-1.5 text-xs">
                    <p class="px-3 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pindah Kategori:</p>
                    <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="pending">
                        <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-amber-50 text-slate-700 hover:text-amber-800 font-semibold flex items-center gap-2 {{ $item->status === 'pending' && !$item->is_schedule_fixed ? 'bg-amber-50 text-amber-800 font-bold' : '' }}">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>Menunggu Konfirmasi</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="progress">
                        <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-blue-50 text-slate-700 hover:text-blue-800 font-semibold flex items-center gap-2 {{ $item->status === 'progress' && !$item->is_schedule_fixed ? 'bg-blue-50 text-blue-800 font-bold' : '' }}">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <span>Koordinasi Jadwal</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-emerald-50 text-slate-700 hover:text-emerald-800 font-semibold flex items-center gap-2 {{ $item->status === 'completed' || $item->is_schedule_fixed ? 'bg-emerald-50 text-emerald-800 font-bold' : '' }}">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Jadwal FIX Terkunci</span>
                        </button>
                    </form>
                    <form method="POST" action="{{ route('follow-ups.update-status', $item) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="status" value="failed">
                        <button type="submit" class="w-full text-left px-3 py-1.5 hover:bg-rose-50 text-slate-700 hover:text-rose-800 font-semibold flex items-center gap-2 {{ $item->status === 'failed' ? 'bg-rose-50 text-rose-800 font-bold' : '' }}">
                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                            <span>Belum Diizinkan</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Delete Button -->
            <button type="button" 
                    onclick="openDeleteModal({{ $item->id }}, '{{ addslashes($item->school?->name ?? 'Sekolah') }}')"
                    class="p-1.5 rounded-xl bg-slate-100 hover:bg-rose-50 hover:text-rose-600 text-slate-400 transition cursor-pointer" 
                    title="Hapus riwayat follow up sekolah ini">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
            </button>

            <!-- Kunci / Ubah Jadwal FIX -->
            <button type="button" 
                    onclick="openScheduleModal(
                        {{ $item->id }}, 
                        '{{ addslashes($item->school?->name ?? 'Sekolah') }}', 
                        '{{ $item->status }}', 
                        '{{ $item->fixed_date ? $item->fixed_date->format('Y-m-d') : ($item->visit?->target_date ? $item->visit->target_date->format('Y-m-d') : '') }}', 
                        '{{ addslashes($item->fixed_time ?: ($item->visit?->target_time ?? '')) }}', 
                        '{{ addslashes($item->pic_name ?: ($item->visit?->recipient_name ?? '')) }}', 
                        '{{ addslashes($item->pic_phone ?? '') }}', 
                        '{{ addslashes($item->socialization_location ?: ($item->visit?->socialization_method === 'Lainnya' ? $item->visit->other_activity_name : $item->visit?->socialization_method ?? '')) }}', 
                        '{{ addslashes($item->notes ?: ($item->description ?? '')) }}', 
                        {{ $item->is_schedule_fixed ? 1 : 0 }}
                    )"
                    class="px-2.5 py-1 rounded-xl bg-slate-900 hover:bg-brand-600 text-white font-bold text-[11px] shadow-xs flex items-center gap-1 transition cursor-pointer">
                <i data-lucide="calendar-check" class="w-3 h-3 text-amber-400"></i>
                <span>{{ $item->is_schedule_fixed ? 'Ubah Jadwal' : 'Kunci Jadwal FIX' }}</span>
            </button>
        </div>
    </div>

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
</div>
