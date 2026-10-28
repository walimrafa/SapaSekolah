<x-app-layout title="Persetujuan Tutup Periode">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <i data-lucide="shield-alert" class="w-6 h-6 text-red-600"></i>
                <span>Persetujuan Tutup Periode &amp; Reset Data</span>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">Otorisasi multi-pihak: Memerlukan persetujuan mutlak dari Pimpinan, Koordinator Marketing, dan Customer Service</p>
        </div>

        @if(auth()->user()->isAdmin() && !$activeRequest)
            <a href="{{ route('settings.index') }}#danger-zone" class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-xl shadow-lg shadow-red-600/30 transition flex items-center space-x-2">
                <i data-lucide="power-off" class="w-4 h-4"></i>
                <span>Ajukan Tutup Periode Baru</span>
            </a>
        @endif
    </div>

    <!-- Active Request Card (If any) -->
    @if($activeRequest)
        <div class="bg-white rounded-2xl border-2 border-red-200 shadow-xl overflow-hidden mb-8">
            <!-- Header Banner -->
            <div class="bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 px-6 py-4 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center space-x-3">
                    <span class="p-2 rounded-xl bg-white/20 backdrop-blur-sm">
                        <i data-lucide="clock" class="w-5 h-5 text-white"></i>
                    </span>
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider bg-white/25 px-2.5 py-0.5 rounded-full">Status Pengajuan</span>
                        <h2 class="text-sm font-extrabold mt-0.5">Menunggu Persetujuan 3 Pihak ({{ $activeRequest->approvedCount() }}/3 Disetujui)</h2>
                    </div>
                </div>

                <div class="text-right text-xs opacity-90">
                    <span>Tahun Akademik: <strong class="font-bold text-white">{{ $activeRequest->academic_year ?? '-' }}</strong></span>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <!-- Info Pengajuan -->
                <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4 text-xs">
                    <div class="space-y-1">
                        <p class="text-slate-500">Diajukan oleh:</p>
                        <p class="font-extrabold text-slate-800 text-sm flex items-center gap-1.5">
                            <i data-lucide="user-check" class="w-4 h-4 text-brand-600"></i>
                            <span>{{ $activeRequest->requester->name ?? 'Administrator' }} (Admin)</span>
                        </p>
                        <p class="text-[11px] text-slate-400">Waktu Pengajuan: {{ $activeRequest->created_at->translatedFormat('l, d F Y - H:i') }} WIB</p>
                    </div>

                    <div class="md:max-w-md bg-white p-3 rounded-lg border border-slate-200">
                        <p class="text-[11px] font-bold text-slate-500 mb-1">Alasan / Catatan Penutupan Periode:</p>
                        <p class="font-medium text-slate-800 italic">"{{ $activeRequest->reason }}"</p>
                    </div>
                </div>

                <!-- 3 Approvers Live Status Grid -->
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-slate-500 mb-3 flex items-center gap-2">
                        <i data-lucide="check-check" class="w-4 h-4 text-slate-400"></i>
                        <span>Status Persetujuan 3 Pihak Penentu</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- 1. Pimpinan Kampus -->
                        <div class="p-4 rounded-xl border {{ $activeRequest->pimpinan_status === 'approved' ? 'bg-emerald-50/60 border-emerald-200' : ($activeRequest->pimpinan_status === 'rejected' ? 'bg-red-50/60 border-red-200' : 'bg-amber-50/60 border-amber-200') }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="award" class="w-4 h-4 text-purple-600"></i>
                                    <span>Pimpinan Kampus</span>
                                </span>
                                @if($activeRequest->pimpinan_status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> Disetujui
                                    </span>
                                @elseif($activeRequest->pimpinan_status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 flex items-center gap-1">
                                        <i data-lucide="x" class="w-3 h-3"></i> Ditolak
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        Menunggu
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs font-medium text-slate-600">
                                @if($activeRequest->pimpinan)
                                    Oleh: <strong class="text-slate-800">{{ $activeRequest->pimpinan->name }}</strong>
                                    <span class="block text-[10px] text-slate-400">{{ $activeRequest->pimpinan_action_at?->translatedFormat('d M Y, H:i') }}</span>
                                    @if($activeRequest->pimpinan_notes)
                                        <span class="block text-[11px] text-slate-600 italic mt-1 bg-white/70 p-1.5 rounded border border-slate-100">"{{ $activeRequest->pimpinan_notes }}"</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Belum memberikan keputusan</span>
                                @endif
                            </p>
                        </div>

                        <!-- 2. Koordinator Marketing -->
                        <div class="p-4 rounded-xl border {{ $activeRequest->koordinator_status === 'approved' ? 'bg-emerald-50/60 border-emerald-200' : ($activeRequest->koordinator_status === 'rejected' ? 'bg-red-50/60 border-red-200' : 'bg-amber-50/60 border-amber-200') }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="briefcase" class="w-4 h-4 text-blue-600"></i>
                                    <span>Koordinator Marketing</span>
                                </span>
                                @if($activeRequest->koordinator_status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> Disetujui
                                    </span>
                                @elseif($activeRequest->koordinator_status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 flex items-center gap-1">
                                        <i data-lucide="x" class="w-3 h-3"></i> Ditolak
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        Menunggu
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs font-medium text-slate-600">
                                @if($activeRequest->koordinator)
                                    Oleh: <strong class="text-slate-800">{{ $activeRequest->koordinator->name }}</strong>
                                    <span class="block text-[10px] text-slate-400">{{ $activeRequest->koordinator_action_at?->translatedFormat('d M Y, H:i') }}</span>
                                    @if($activeRequest->koordinator_notes)
                                        <span class="block text-[11px] text-slate-600 italic mt-1 bg-white/70 p-1.5 rounded border border-slate-100">"{{ $activeRequest->koordinator_notes }}"</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Belum memberikan keputusan</span>
                                @endif
                            </p>
                        </div>

                        <!-- 3. Customer Service -->
                        <div class="p-4 rounded-xl border {{ $activeRequest->cs_status === 'approved' ? 'bg-emerald-50/60 border-emerald-200' : ($activeRequest->cs_status === 'rejected' ? 'bg-red-50/60 border-red-200' : 'bg-amber-50/60 border-amber-200') }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                                    <i data-lucide="headset" class="w-4 h-4 text-emerald-600"></i>
                                    <span>Customer Service</span>
                                </span>
                                @if($activeRequest->cs_status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3 h-3"></i> Disetujui
                                    </span>
                                @elseif($activeRequest->cs_status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 flex items-center gap-1">
                                        <i data-lucide="x" class="w-3 h-3"></i> Ditolak
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse">
                                        Menunggu
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs font-medium text-slate-600">
                                @if($activeRequest->customerService)
                                    Oleh: <strong class="text-slate-800">{{ $activeRequest->customerService->name }}</strong>
                                    <span class="block text-[10px] text-slate-400">{{ $activeRequest->cs_action_at?->translatedFormat('d M Y, H:i') }}</span>
                                    @if($activeRequest->cs_notes)
                                        <span class="block text-[11px] text-slate-600 italic mt-1 bg-white/70 p-1.5 rounded border border-slate-100">"{{ $activeRequest->cs_notes }}"</span>
                                    @endif
                                @else
                                    <span class="text-slate-400 italic">Belum memberikan keputusan</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form Keputusan (Hanya muncul jika akun ini berhak menyetujui & belum memberikan suara) -->
                @if($canApprove)
                    <div class="p-6 bg-slate-900 border border-slate-700/80 rounded-2xl text-white shadow-xl">
                        <div class="flex items-center space-x-3 mb-4 pb-3 border-b border-slate-700/80">
                            <span class="w-9 h-9 rounded-xl bg-amber-500 text-slate-900 flex items-center justify-center font-black shadow-md shadow-amber-500/20">
                                <i data-lucide="pen-tool" class="w-5 h-5"></i>
                            </span>
                            <div>
                                <h4 class="text-sm font-bold text-white">Form Keputusan Otorisasi: {{ auth()->user()->role->name }}</h4>
                                <p class="text-xs text-slate-300">Berikan keputusan Anda untuk menyetujui atau menolak permohonan tutup periode ini.</p>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('period-closing.respond', $activeRequest) }}" class="space-y-4 text-xs">
                            @csrf

                            <div>
                                <label class="block font-bold text-slate-100 mb-1.5">
                                    Catatan / Alasan (Wajib jika menolak, opsional jika menyetujui):
                                </label>
                                <textarea name="notes" rows="2" placeholder="Tuliskan catatan pertimbangan Anda..."
                                    style="background-color: #ffffff !important; color: #0f172a !important;"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 placeholder:text-slate-400 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-100 mb-1.5">
                                    Masukkan Password Akun Anda <span class="text-amber-400">*</span>
                                </label>
                                <input type="password" name="password" required autocomplete="current-password"
                                    placeholder="Password akun Anda..."
                                    style="background-color: #ffffff !important; color: #0f172a !important;"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-900 placeholder:text-slate-400 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                                <p class="text-xs text-amber-200/90 mt-1.5 flex items-center gap-1.5 font-medium">
                                    <i data-lucide="info" class="w-3.5 h-3.5 text-amber-400 shrink-0"></i>
                                    <span>Dibutuhkan untuk memverifikasi keabsahan identitas Anda sebagai pengambil keputusan.</span>
                                </p>
                            </div>

                            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-3 border-t border-slate-800">
                                <button type="submit" name="decision" value="reject" onclick="return confirm('Apakah Anda yakin MENOLAK permohonan ini? Jika ditolak, tutup periode akan langsung BATAL dan data operasional tidak akan diubah.')"
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold transition flex items-center justify-center space-x-1.5 shadow-md shadow-red-600/30">
                                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                                    <span>Tolak Pengajuan (Batalkan Reset)</span>
                                </button>
                                <button type="submit" name="decision" value="approve" onclick="return confirm('Apakah Anda yakin MENYETUJUI penutupan periode ini? Jika ketiga pihak menyetujui, seluruh data operasional akan diriset ke 0.')"
                                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 active:bg-emerald-800 text-white font-extrabold transition flex items-center justify-center space-x-1.5 shadow-lg shadow-emerald-600/30">
                                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                                    <span>Setujui Tutup Periode</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Admin Cancel Action -->
                @if(auth()->user()->isAdmin())
                    <div class="flex justify-end pt-2 border-t border-slate-100">
                        <form method="POST" action="{{ route('period-closing.cancel', $activeRequest) }}" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan tutup periode yang sedang berjalan ini?')">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-slate-500 hover:text-red-600 font-semibold text-xs transition flex items-center space-x-1">
                                <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                <span>Batalkan Permohonan Ini</span>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- No Active Request Banner -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200/80 shadow-soft mb-8 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center space-x-3.5">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-sm font-extrabold text-slate-900">Tidak Ada Permohonan Tutup Periode Aktif</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Siklus operasional pemasaran &amp; PMB saat ini sedang berjalan normal.</p>
                </div>
            </div>

            <div class="text-xs text-right">
                <span class="text-slate-400">Periode Terakhir Ditutup:</span>
                <p class="font-bold text-slate-800">{{ \App\Services\AppSettingService::get('last_period_closed_at') ?: 'Belum pernah di-reset' }}</p>
            </div>
        </div>
    @endif

    <!-- History Log Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="history" class="w-4 h-4 text-slate-400"></i>
                <span>Riwayat Pengajuan Tutup Periode</span>
            </h3>
        </div>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead class="bg-slate-50 text-slate-600 font-bold border-b border-slate-100">
                    <tr>
                        <th class="py-3 px-4">Tgl &amp; Pemohon</th>
                        <th class="py-3 px-4">Alasan &amp; TA</th>
                        <th class="py-3 px-4 text-center">Pimpinan</th>
                        <th class="py-3 px-4 text-center">Koordinator</th>
                        <th class="py-3 px-4 text-center">Customer Service</th>
                        <th class="py-3 px-4 text-center">Status Akhir</th>
                        <th class="py-3 px-4">Waktu Selesai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($historyRequests as $item)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <strong class="font-extrabold text-slate-900 block">{{ $item->created_at->translatedFormat('d M Y, H:i') }}</strong>
                                <span class="text-[11px] text-slate-400">{{ $item->requester->name ?? 'Admin' }}</span>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <span class="font-semibold text-slate-800 block truncate" title="{{ $item->reason }}">{{ $item->reason }}</span>
                                <span class="text-[10px] text-slate-400">TA: {{ $item->academic_year ?? '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->pimpinan_status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Setuju</span>
                                @elseif($item->pimpinan_status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Tolak</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->koordinator_status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Setuju</span>
                                @elseif($item->koordinator_status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Tolak</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->cs_status === 'approved')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Setuju</span>
                                @elseif($item->cs_status === 'rejected')
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800">Tolak</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menunggu</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($item->status === 'completed')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">SELESAI (RESET)</span>
                                @elseif($item->status === 'rejected')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-red-100 text-red-800" title="{{ $item->rejection_reason }}">DITOLAK</span>
                                @elseif($item->status === 'cancelled')
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-slate-100 text-slate-600">DIBATALKAN</span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-800">PROSES</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-[11px] text-slate-500">
                                {{ $item->executed_at ? $item->executed_at->translatedFormat('d M Y, H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">Belum ada riwayat permohonan tutup periode.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $historyRequests->links() }}
        </div>
    </div>
</x-app-layout>
