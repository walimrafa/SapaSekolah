<x-app-layout title="Data Proposal Masuk - Support Event Sekolah">
    <div x-data="eventProposalsApp(@js($proposals->keyBy('id')))" class="space-y-6">

        <!-- Header & Action Bar -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-brand-950 to-slate-900 border border-slate-800 p-6 md:p-8 shadow-xl text-white">
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="flex items-center space-x-2.5">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-400/20 text-amber-300 border border-amber-400/30">
                            <i data-lucide="inbox" class="w-3.5 h-3.5 mr-1.5 text-amber-400"></i> Proposal Masuk
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span> Terintegrasi ke Laporan
                        </span>
                    </div>
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-white flex items-center gap-2">
                        Data Proposal Masuk & Workflow Persetujuan
                    </h1>
                    <p class="text-sm md:text-base text-slate-300 max-w-3xl leading-relaxed">
                        Kelola seluruh proposal sponsorship & support kegiatan dari sekolah. Tinjau berkas, disposisikan ke pimpinan pusat, tentukan persetujuan anggaran, dan pantau otomatis di Laporan Suport Kegiatan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('school-events.create') }}" target="_blank"
                        class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl text-xs font-bold bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-slate-950 shadow-md shadow-amber-400/20 transition hover:scale-105">
                        <i data-lucide="external-link" class="w-4 h-4"></i>
                        <span>Form Pengajuan Publik</span>
                    </a>

                    <button type="button" @click="showQrModal = true"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-slate-800 hover:bg-slate-700 text-cyan-300 border border-slate-700 shadow-sm transition hover:scale-105">
                        <i data-lucide="qr-code" class="w-4 h-4 text-cyan-400"></i>
                        <span>QR Code Form</span>
                    </button>

                    <a href="{{ route('event-reports.index') }}"
                        class="inline-flex items-center space-x-1.5 px-3.5 py-2.5 rounded-xl text-xs font-semibold bg-slate-800/80 hover:bg-slate-700 text-slate-200 border border-slate-700 shadow-sm transition">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-emerald-400"></i>
                        <span>Laporan Kegiatan</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <a href="{{ route('event-proposals.index') }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ !request('status') ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Masuk</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total']) }}</p>
                <span class="text-[11px] text-slate-500 font-medium">Semua Usulan</span>
            </a>

            <a href="{{ route('event-proposals.index', ['status' => 'menunggu_review']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('status') === 'menunggu_review' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-amber-600 uppercase tracking-wider flex items-center justify-between">
                    <span>Review Baru</span>
                    @if($stats['menunggu_review'] > 0)
                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                    @endif
                </p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($stats['menunggu_review']) }}</p>
                <span class="text-[11px] text-amber-700 font-medium">Perlu Ditinjau</span>
            </a>

            <a href="{{ route('event-proposals.index', ['status' => 'diajukan_ke_pusat']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('status') === 'diajukan_ke_pusat' ? 'border-indigo-500 ring-2 ring-indigo-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-indigo-600 uppercase tracking-wider">Ke Pusat</p>
                <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ number_format($stats['diajukan_ke_pusat']) }}</p>
                <span class="text-[11px] text-indigo-700 font-medium">Diajukan ke Pusat</span>
            </a>

            <a href="{{ route('event-proposals.index', ['status' => 'menunggu_acc']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('status') === 'menunggu_acc' ? 'border-purple-500 ring-2 ring-purple-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-purple-600 uppercase tracking-wider">Menunggu ACC</p>
                <p class="text-2xl font-extrabold text-purple-600 mt-1">{{ number_format($stats['menunggu_acc']) }}</p>
                <span class="text-[11px] text-purple-700 font-medium">Disposisi ACC</span>
            </a>

            <a href="{{ route('event-proposals.index', ['status' => 'disetujui']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('status') === 'disetujui' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Disetujui</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($stats['disetujui']) }}</p>
                <span class="text-[11px] text-emerald-700 font-medium">Masuk Laporan</span>
            </a>

            <a href="{{ route('event-proposals.index', ['status' => 'ditolak']) }}" 
                class="p-4 bg-white rounded-2xl border transition hover:shadow-md {{ request('status') === 'ditolak' ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-200/90' }}">
                <p class="text-[11px] font-bold text-rose-600 uppercase tracking-wider">Ditolak</p>
                <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($stats['ditolak']) }}</p>
                <span class="text-[11px] text-rose-700 font-medium">Belum Disetujui</span>
            </a>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-4 shadow-sm">
            <form method="GET" action="{{ route('event-proposals.index') }}" class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="search" class="w-4 h-4"></i>
                    </span>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama event, sekolah, no. referensi, lokasi, atau PIC..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500 transition">
                </div>

                <div class="flex items-center gap-2 w-full md:w-auto">
                    <select name="status" class="w-full md:w-44 px-3 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-brand-500">
                        <option value="">Semua Status</option>
                        <option value="menunggu_review" {{ request('status') === 'menunggu_review' ? 'selected' : '' }}>Menunggu Review</option>
                        <option value="diajukan_ke_pusat" {{ request('status') === 'diajukan_ke_pusat' ? 'selected' : '' }}>Diajukan ke Pusat</option>
                        <option value="menunggu_acc" {{ request('status') === 'menunggu_acc' ? 'selected' : '' }}>Menunggu ACC</option>
                        <option value="disetujui" {{ request('status') === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ request('status') === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-sm transition">
                        Filter
                    </button>

                    @if(request()->anyFilled(['q', 'status']))
                        <a href="{{ route('event-proposals.index') }}" class="px-3 py-2.5 rounded-xl text-sm font-medium bg-slate-100 hover:bg-slate-200 text-slate-600 transition" title="Reset Filter">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Proposals List Table -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base">Daftar Pengajuan Proposal Event</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $proposals->total() }} total proposal kegiatan yang masuk</p>
                </div>
            </div>

            @if($proposals->isEmpty())
                <div class="py-16 text-center text-slate-400 space-y-3">
                    <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                        <i data-lucide="inbox" class="w-8 h-8"></i>
                    </div>
                    <p class="text-base font-bold text-slate-700">Belum ada proposal event yang sesuai kriteria.</p>
                    <p class="text-xs text-slate-400 max-w-sm mx-auto">Sekolah dapat mengajukan proposal melalui tautan formulir pengajuan publik tanpa perlu login.</p>
                    <div class="pt-2">
                        <a href="{{ route('school-events.create') }}" target="_blank" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold bg-brand-600 text-white hover:bg-brand-500 transition">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                            <span>Buka Form Pengajuan Publik</span>
                        </a>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 border-b border-slate-200/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Ref & Sekolah</th>
                                <th class="py-3.5 px-4">Nama Event & Jadwal</th>
                                <th class="py-3.5 px-4">Narahubung (PIC)</th>
                                <th class="py-3.5 px-4">Support & Anggaran</th>
                                <th class="py-3.5 px-4">Berkas Proposal</th>
                                <th class="py-3.5 px-4">Status & Approval</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($proposals as $item)
                                @php
                                    $waNumber = preg_replace('/[^0-9]/', '', $item->pic_phone);
                                    if (str_starts_with($waNumber, '0')) {
                                        $waNumber = '62' . substr($waNumber, 1);
                                    }
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition">
                                    <!-- Ref & Sekolah -->
                                    <td class="py-4 px-4 align-top">
                                        <span class="inline-block px-2 py-0.5 rounded text-[11px] font-mono font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                            {{ $item->reference_no }}
                                        </span>
                                        <div class="font-extrabold text-slate-900 mt-1">
                                            {{ $item->school_name }}
                                        </div>
                                        @if($item->school && $item->school->district)
                                            <span class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                                {{ $item->school->district->name }}
                                            </span>
                                        @endif
                                        <div class="text-[10px] text-slate-400 mt-1">
                                            Masuk: {{ $item->created_at->translatedFormat('d M Y, H:i') }}
                                        </div>
                                    </td>

                                    <!-- Event & Jadwal -->
                                    <td class="py-4 px-4 align-top max-w-xs">
                                        <div class="font-bold text-slate-900">
                                            {{ $item->event_name }}
                                        </div>
                                        <div class="text-xs text-brand-600 font-semibold flex items-center gap-1 mt-1">
                                            <i data-lucide="calendar" class="w-3.5 h-3.5 text-brand-500"></i>
                                            {{ $item->event_date ? \Carbon\Carbon::parse($item->event_date)->translatedFormat('d M Y') : '-' }}
                                            @if($item->event_end_date && $item->event_end_date != $item->event_date)
                                                - {{ \Carbon\Carbon::parse($item->event_end_date)->translatedFormat('d M Y') }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                            <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                                            {{ $item->event_time }}
                                        </div>
                                        <div class="text-xs text-slate-600 flex items-center gap-1 mt-0.5">
                                            <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                            <span class="truncate">{{ $item->event_location }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-1">
                                            🎯 Target: <span class="font-semibold text-slate-700">{{ $item->target_participants }}</span>
                                        </div>
                                    </td>

                                    <!-- PIC -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="font-bold text-slate-900">{{ $item->pic_name }}</div>
                                        @if($item->pic_position)
                                            <div class="text-[11px] text-slate-500">{{ $item->pic_position }}</div>
                                        @endif
                                        <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode('Halo Bapak/Ibu ' . $item->pic_name . ', kami dari Tim Universitas BSI mengenai proposal event ' . $item->event_name . ' (' . $item->reference_no . ').') }}"
                                            target="_blank"
                                            class="inline-flex items-center space-x-1.5 mt-2 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 transition">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>{{ $item->pic_phone }}</span>
                                        </a>
                                    </td>

                                    <!-- Support & Anggaran -->
                                    <td class="py-4 px-4 align-top">
                                        <div class="text-xs font-semibold text-slate-700">
                                            {{ $item->support_type_requested ?: 'Sponsorship' }}
                                        </div>
                                        @if($item->estimated_budget)
                                            <div class="text-xs text-slate-500 mt-1">
                                                Estimasi: <span class="font-bold text-slate-900">Rp {{ number_format($item->estimated_budget, 0, ',', '.') }}</span>
                                            </div>
                                        @endif

                                        @if($item->approved_amount)
                                            <div class="mt-1.5 px-2 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs">
                                                <span class="font-bold">ACC Dana:</span> Rp {{ number_format($item->approved_amount, 0, ',', '.') }}
                                            </div>
                                        @endif
                                        @if($item->approved_support_items)
                                            <div class="text-[11px] text-slate-600 mt-1 italic">
                                                "{{ $item->approved_support_items }}"
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Berkas Proposal -->
                                    <td class="py-4 px-4 align-top">
                                        <a href="{{ route('event-proposals.download', $item) }}"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 transition group shadow-sm">
                                            <i data-lucide="download" class="w-3.5 h-3.5 text-brand-600 group-hover:scale-110 transition"></i>
                                            <span>Unduh File</span>
                                        </a>
                                        <div class="text-[10px] text-slate-400 mt-1 max-w-[140px] truncate" title="{{ $item->proposal_file_name }}">
                                            {{ $item->proposal_file_name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ round($item->proposal_file_size / (1024 * 1024), 2) }} MB
                                        </div>
                                    </td>

                                    <!-- Status & Approval -->
                                    <td class="py-4 px-4 align-top">
                                        @if($item->status === 'menunggu_review')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mr-1.5 animate-pulse"></span> Menunggu Review
                                            </span>
                                        @elseif($item->status === 'diajukan_ke_pusat')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-300">
                                                <i data-lucide="send" class="w-3 h-3 mr-1 text-indigo-500"></i> Diajukan ke Pusat
                                            </span>
                                        @elseif($item->status === 'menunggu_acc')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-300">
                                                <i data-lucide="hourglass" class="w-3 h-3 mr-1 text-purple-500"></i> Menunggu ACC
                                            </span>
                                        @elseif($item->status === 'disetujui')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-300">
                                                <i data-lucide="check-circle" class="w-3 h-3 mr-1 text-emerald-500"></i> Disetujui
                                            </span>
                                        @elseif($item->status === 'ditolak')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-300">
                                                <i data-lucide="x-circle" class="w-3 h-3 mr-1 text-rose-500"></i> Ditolak
                                            </span>
                                        @endif

                                        @if($item->reviewer)
                                            <div class="text-[10px] text-slate-500 mt-1">
                                                Oleh: <span class="font-semibold">{{ $item->reviewer->name }}</span>
                                            </div>
                                        @endif

                                        @if($item->status_notes)
                                            <div class="text-[11px] text-slate-600 mt-1 bg-slate-50 p-1.5 rounded border border-slate-100 max-w-[160px]">
                                                "{{ $item->status_notes }}"
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-4 px-4 align-top text-right space-y-1.5">
                                        <button type="button" @click="openStatusModal({{ $item->id }})"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-brand-50 hover:bg-brand-100 text-brand-700 border border-brand-200 transition">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            <span>Ubah Status</span>
                                        </button>

                                        @if(auth()->user()->isAdmin())
                                            <div>
                                                <form action="{{ route('event-proposals.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proposal {{ $item->reference_no }} ini?')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-[11px] text-rose-600 hover:text-rose-800 font-semibold transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-slate-100">
                    {{ $proposals->links() }}
                </div>
            @endif
        </div>

        <!-- Status Workflow Update Modal -->
        <div x-show="showStatusModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showStatusModal = false"
                class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-800 space-y-5 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <span class="text-[11px] font-mono font-bold text-brand-600 bg-brand-50 px-2 py-0.5 rounded" x-text="activeProposal?.reference_no"></span>
                        <h3 class="text-lg font-extrabold text-slate-900 mt-1">Ubah Status & Approval Proposal</h3>
                    </div>
                    <button type="button" @click="showStatusModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <form :action="'/event-proposals/' + activeProposal?.id + '/status'" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Pilih Status Baru <span class="text-rose-500">*</span>
                        </label>
                        <select name="status" x-model="modalStatus" required
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-sm font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500">
                            <option value="menunggu_review">🟡 1. Menunggu Review</option>
                            <option value="diajukan_ke_pusat">🔵 2. Diajukan ke Pusat</option>
                            <option value="menunggu_acc">🟣 3. Menunggu ACC Pusat</option>
                            <option value="disetujui">🟢 4. Disetujui (Masuk Laporan Support)</option>
                            <option value="ditolak">🔴 5. Ditolak</option>
                        </select>
                        <p class="text-[11px] text-slate-500 mt-1" x-show="modalStatus === 'disetujui'">
                            💡 Proposal yang disetujui akan <strong>otomatis tersinkronisasi</strong> dan muncul pada menu <strong>Laporan Suport Kegiatan</strong>.
                        </p>
                    </div>

                    <!-- Fields jika disetujui -->
                    <div x-show="modalStatus === 'disetujui'" class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-1">
                                Nominal Dana Disetujui (Rp)
                            </label>
                            <input type="number" name="approved_amount" x-model="modalApprovedAmount" step="50000" min="0" placeholder="Contoh: 1000000"
                                class="w-full bg-white border border-emerald-300 rounded-xl px-3.5 py-2 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-1">
                                Bentuk / Rincian Barang Support Disetujui
                            </label>
                            <input type="text" name="approved_support_items" x-model="modalApprovedItems" placeholder="Contoh: Stand Booth + Doorprize 5 Goodie Bag & Souvenir"
                                class="w-full bg-white border border-emerald-300 rounded-xl px-3.5 py-2 text-xs font-medium text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Catatan / Keterangan Review
                        </label>
                        <textarea name="status_notes" x-model="modalNotes" rows="3" placeholder="Tuliskan catatan tindak lanjut, nomor disposisi pusat, atau alasan penolakan jika ada..."
                            class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-brand-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" @click="showStatusModal = false"
                            class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-xs font-bold bg-brand-600 hover:bg-brand-500 text-white shadow-md shadow-brand-500/20 transition">
                            Simpan Perubahan Status
                        </button>
                    </div>
                </form>
            </div>
        <!-- Modal QR Code Form Pengajuan Proposal Sekolah -->
        <div x-show="showQrModal" style="display: none;"
            class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div @click.away="showQrModal = false"
                class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-100 text-slate-800 space-y-5 animate-in fade-in zoom-in duration-200">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
                            <i data-lucide="qr-code" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-extrabold text-slate-900">QR Code Pengajuan Proposal</h3>
                    </div>
                    <button type="button" @click="showQrModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                @php
                    $proposalUrl = url(route('school-events.create', [], false));
                    $qrProposalImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=15&data=" . urlencode($proposalUrl);
                @endphp

                <p class="text-xs text-slate-600 leading-relaxed">
                    Bagikan QR Code ini kepada panitia OSIS, Guru, atau Kepala Sekolah agar pihak sekolah dapat mengajukan proposal sponsorship / support event ke UBSI secara online tanpa login.
                </p>

                <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200 text-center shadow-inner">
                    <div class="inline-block p-3 bg-white rounded-2xl shadow-md border border-slate-200">
                        <img src="{{ $qrProposalImageUrl }}" alt="QR Code Pengajuan Proposal Sekolah" class="w-48 h-48 mx-auto rounded-xl">
                    </div>
                    <p class="text-[11px] font-mono font-bold text-blue-700 mt-2.5 truncate px-2">
                        {{ $proposalUrl }}
                    </p>
                </div>

                <div class="space-y-2 pt-1" x-data="{ copiedLink: false }">
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('qr-codes.download', 'school-events') }}"
                            class="py-2.5 px-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="download" class="w-4 h-4"></i>
                            <span>Unduh PNG HD</span>
                        </a>
                        <button type="button" @click="navigator.clipboard.writeText('{{ $proposalUrl }}'); copiedLink = true; setTimeout(() => copiedLink = false, 2000)"
                            class="py-2.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                            <span x-text="copiedLink ? '✓ Disalin!' : 'Salin URL'"></span>
                        </button>
                    </div>

                    <a href="{{ route('school-events.create') }}" target="_blank"
                        class="w-full py-2 bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Halaman Form di Tab Baru</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script>
        function eventProposalsApp(proposalsMap = {}) {
            return {
                proposalsMap: proposalsMap || {},
                showStatusModal: false,
                showQrModal: false,
                activeProposal: null,
                modalStatus: 'menunggu_review',
                modalApprovedAmount: '',
                modalApprovedItems: '',
                modalNotes: '',

                openStatusModal(id) {
                    const proposal = this.proposalsMap[id];
                    if (!proposal) return;
                    this.activeProposal = proposal;
                    this.modalStatus = proposal.status || 'menunggu_review';
                    this.modalApprovedAmount = proposal.approved_amount || '';
                    this.modalApprovedItems = proposal.approved_support_items || '';
                    this.modalNotes = proposal.status_notes || '';
                    this.showStatusModal = true;
                }
            }
        }
    </script>
</x-app-layout>
