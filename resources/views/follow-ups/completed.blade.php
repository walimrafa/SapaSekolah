<x-app-layout title="Sekolah Sudah Sosialisasi">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="rounded-xl bg-emerald-100 p-2 text-emerald-700">
                    <i data-lucide="presentation" class="h-5 w-5"></i>
                </span>
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">Sekolah Sudah Sosialisasi</h1>
                    <p class="mt-0.5 text-xs text-slate-500">Daftar sekolah yang kegiatan presentasi atau sosialisasinya sudah terlaksana.</p>
                </div>
            </div>
        </div>
        <a href="{{ route('follow-ups.index') }}"
            class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2.5 text-xs font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
            <i data-lucide="arrow-left" class="h-4 w-4"></i>
            Kembali ke Follow Up
        </a>
    </div>

    @if(session('toast'))
        <div class="mb-6 flex items-center gap-3 rounded-2xl border {{ (session('toast')['type'] ?? '') === 'error' ? 'border-rose-200 bg-rose-50 text-rose-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} p-4 text-sm font-semibold">
            <i data-lucide="{{ (session('toast')['type'] ?? '') === 'error' ? 'alert-circle' : 'circle-check' }}" class="h-5 w-5 shrink-0"></i>
            <span>{{ session('toast')['message'] }}</span>
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-3 sm:grid-cols-2">
        <div class="rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">Total Kegiatan Terlaksana</p>
            <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalCount) }}</p>
            <p class="text-xs text-slate-500">Catatan sosialisasi yang sudah dikonfirmasi</p>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-white p-4 shadow-sm">
            <p class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Estimasi Peserta</p>
            <p class="mt-1 text-2xl font-black text-slate-900">{{ number_format($totalAudience) }}</p>
            <p class="text-xs text-slate-500">Jumlah target peserta dari jadwal</p>
        </div>
    </div>

    <form method="GET" action="{{ route('socializations.completed.index') }}" class="mb-5 flex flex-col gap-2 sm:flex-row">
        <label for="search" class="sr-only">Cari sekolah atau PIC</label>
        <input id="search" type="search" name="search" value="{{ request('search') }}"
            placeholder="Cari nama sekolah, kecamatan, atau PIC..."
            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm shadow-sm focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20">
        <button type="submit" class="rounded-xl bg-brand-navy px-5 py-2.5 text-xs font-bold text-white transition hover:bg-slate-800">
            Cari
        </button>
        @if(request()->filled('search'))
            <a href="{{ route('socializations.completed.index') }}"
                class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-center text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                Reset
            </a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-left text-xs">
                <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Sekolah & Kecamatan</th>
                        <th class="px-4 py-3">Tanggal Terlaksana</th>
                        <th class="px-4 py-3">Metode / Lokasi</th>
                        <th class="px-4 py-3">PIC & Marketing</th>
                        <th class="px-4 py-3">Catatan Kegiatan</th>
                        <th class="px-4 py-3">Tindakan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($completedSocializations as $item)
                        <tr class="align-top hover:bg-slate-50/70">
                            <td class="px-4 py-4">
                                <p class="font-extrabold text-slate-900">{{ $item->school?->name ?? 'Sekolah dihapus' }}</p>
                                <p class="mt-1 text-[11px] text-slate-500">{{ $item->school?->district?->name ?? 'Kecamatan tidak tersedia' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-4 font-bold text-emerald-800">
                                {{ $item->socialization_completed_at ? \Carbon\Carbon::parse($item->socialization_completed_at)->translatedFormat('d F Y') : '-' }}
                                @if($item->fixed_date)
                                    <p class="mt-1 text-[10px] font-medium text-slate-400">Jadwal: {{ \Carbon\Carbon::parse($item->fixed_date)->translatedFormat('d M Y') }} {{ $item->fixed_time }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-slate-700">
                                {{ $item->socialization_location ?: ($item->visit?->socialization_method ?: 'Sosialisasi') }}
                                @if($item->target_audience_count)
                                    <p class="mt-1 text-[10px] text-slate-500">{{ number_format($item->target_audience_count) }} estimasi peserta</p>
                                @endif
                            </td>
                            <td class="px-4 py-4">
                                <p class="font-semibold text-slate-800">{{ $item->pic_name ?: 'PIC sekolah' }}</p>
                                <p class="mt-1 text-[10px] text-slate-500">{{ $item->user?->name ?? 'Marketing tidak tersedia' }}</p>
                            </td>
                            <td class="max-w-xs whitespace-normal px-4 py-4 text-slate-600">
                                {{ $item->socialization_completion_notes ?: 'Tidak ada catatan kegiatan.' }}
                            </td>
                            <td class="px-4 py-4">
                                <form method="POST" action="{{ route('socializations.completed.reopen', $item) }}"
                                    onsubmit="return confirm('Batalkan konfirmasi kegiatan ini? Data akan kembali ke daftar Jadwal FIX.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="whitespace-nowrap rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-[10px] font-bold text-amber-800 transition hover:bg-amber-100">
                                        Batalkan Konfirmasi
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <i data-lucide="presentation" class="mx-auto mb-2 h-8 w-8 text-slate-300"></i>
                                <p class="font-bold text-slate-600">Belum ada sosialisasi yang dikonfirmasi</p>
                                <p class="mt-1 text-xs text-slate-400">Konfirmasi kegiatan dari kartu Jadwal FIX di menu Follow Up.</p>
                                <a href="{{ route('follow-ups.index') }}" class="mt-3 inline-flex text-xs font-bold text-brand-600 hover:text-brand-800">Buka Follow Up Sekolah</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($completedSocializations->hasPages())
            <div class="border-t border-slate-100 px-4 py-3">
                {{ $completedSocializations->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
