<x-app-layout title="Tracking Kunjungan Lapangan">
    <!-- Header Title & Add Buttons -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6" x-data="{ showQrModal: false }">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Tracking Kunjungan Lapangan</h1>
            <p class="text-xs text-slate-500 mt-0.5">Pencatatan hasil kunjungan marketing, penerima PIC, target sosialisasi &amp; detail sekolah</p>
        </div>
        
        <div class="flex items-center space-x-2">
            <button @click="showQrModal = true" type="button"
                class="px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition cursor-pointer">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span>Tampilkan QR Code Portal</span>
            </button>

            <a href="{{ route('visits.create') }}"
                class="px-4 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Catat Hasil Kunjungan Lapangan</span>
            </a>
        </div>

        <!-- Modal QR Code Standalone -->
        <div x-show="showQrModal" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" style="display: none;">
            
            <div @click.away="showQrModal = false" class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-6 text-center border border-slate-200 relative">
                <button @click="showQrModal = false" type="button" class="absolute top-4 right-4 text-slate-400 hover:text-slate-900 p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="qr-code" class="w-6 h-6"></i>
                </div>

                <h3 class="text-base font-extrabold text-slate-900">QR Code Portal Input Lapangan</h3>
                <p class="text-xs text-slate-500 mt-1">Scan QR Code ini menggunakan kamera Smartphone petugas di lapangan untuk membuka form tanpaperlu login.</p>

                <div class="my-4 p-3 bg-slate-50 rounded-2xl border border-slate-200 inline-block shadow-inner">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode(route('public-visit.index')) }}" alt="QR Code Portal Input Kunjungan" class="w-48 h-48 mx-auto rounded-xl shadow-xs">
                </div>

                <p class="text-[11px] font-mono font-bold text-brand-700 bg-brand-50 py-1.5 px-3 rounded-xl border border-brand-100 truncate mb-4">
                    {{ route('public-visit.index') }}
                </p>

                <div class="flex items-center justify-center gap-2">
                    <a href="{{ route('public-visit.index') }}" target="_blank" class="w-full py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition flex items-center justify-center space-x-1.5">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>Buka Link ↗</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- QR Code Banner Card -->
    <div class="bg-gradient-to-r from-slate-900 to-brand-navy text-white rounded-3xl p-5 mb-6 shadow-xl border border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-5">
        <div class="flex items-center space-x-4">
            <div class="p-2 bg-white rounded-2xl shadow-md shrink-0">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('public-visit.index')) }}" alt="QR Code" class="w-16 h-16 rounded-xl">
            </div>
            <div>
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold bg-amber-400 text-slate-900 rounded-full uppercase tracking-wider mb-1 inline-block">Akses Tanpa Login</span>
                <h3 class="text-base font-extrabold tracking-tight">QR Code Portal Input Kunjungan Lapangan</h3>
                <p class="text-xs text-slate-300 mt-0.5">Scan QR Code untuk langsung mengisi formulir hasil kunjungan sekolah via HP/Tablet tanpa perlu login pengguna.</p>
            </div>
        </div>

        <div class="flex items-center space-x-2 shrink-0">
            <a href="{{ route('public-visit.index') }}" target="_blank" class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold rounded-xl text-xs transition shadow-md flex items-center space-x-1.5">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Buka Form Portal ↗</span>
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <form method="GET" action="{{ route('visits.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama sekolah..." class="py-1.5 px-3 rounded-xl border border-slate-300">
                
                <select name="status" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300">
                    <option value="">Semua Status Visit</option>
                    <option value="berhasil" {{ request('status') == 'berhasil' ? 'selected' : '' }}>Berhasil (Terlaksana)</option>
                    <option value="tidak_bertemu" {{ request('status') == 'tidak_bertemu' ? 'selected' : '' }}>Tidak Bertemu</option>
                    <option value="tidak_berhasil" {{ request('status') == 'tidak_berhasil' ? 'selected' : '' }}>Tidak Berhasil</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>

                <button type="submit" class="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold rounded-xl text-xs transition">
                    Filter
                </button>
            </form>
            <span class="text-xs font-bold text-slate-500">Total: {{ $visits->total() }} Record Visit</span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">Tanggal Visit</th>
                        <th class="py-3.5 px-4">Sekolah Kunjungan</th>
                        <th class="py-3.5 px-4">Penerima &amp; Jabatan</th>
                        <th class="py-3.5 px-4">Target Sosialisasi</th>
                        <th class="py-3.5 px-4">Metode Kegiatan</th>
                        <th class="py-3.5 px-4 text-center">Estimasi Siswa XII</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($visits as $v)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($v->visit_date)->format('d M Y') }}
                                <span class="block text-[10px] text-brand-600 font-normal">By {{ $v->user ? $v->user->name : '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ $v->school ? $v->school->name : '-' }}
                                <span class="block text-[10px] text-slate-400 font-normal">Kec. {{ $v->school && $v->school->district ? $v->school->district->name : '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800 whitespace-nowrap">
                                {{ $v->recipient_name ?? $v->contact_person ?? '-' }}
                                <span class="block text-[10px] text-slate-500 font-normal">{{ $v->recipient_position ?? 'Jabatan' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 whitespace-nowrap">
                                <span class="font-bold text-emerald-700 block">{{ $v->target_month_year ?? '-' }}</span>
                                <span class="text-[10px] text-slate-500 block">Jam {{ $v->target_time ?? '-' }} &bull; Tgl: {{ $v->target_date ? \Carbon\Carbon::parse($v->target_date)->format('d M Y') : 'Flexibel' }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-purple-700 whitespace-nowrap">
                                {{ $v->socialization_method === 'Lainnya' ? ($v->other_activity_name ?? 'Lainnya') : ($v->socialization_method ?? '-') }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-extrabold text-brand-600 whitespace-nowrap">
                                @if($v->grade_12_class_count && $v->students_per_class)
                                    {{ $v->grade_12_class_count * $v->students_per_class }} Siswa
                                    <span class="block text-[10px] text-slate-400 font-normal">({{ $v->grade_12_class_count }} Kls &times; {{ $v->students_per_class }} Ssw)</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @php
                                    $sColor = match($v->status) {
                                        'berhasil' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                        'tidak_bertemu' => 'bg-amber-100 text-amber-800 border border-amber-200',
                                        'tidak_berhasil' => 'bg-red-100 text-red-800 border border-red-200',
                                        default => 'bg-slate-100 text-slate-700 border border-slate-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase {{ $sColor }}">
                                    {{ str_replace('_', ' ', $v->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    <a href="{{ route('follow-ups.index', ['search' => $v->school?->name]) }}" title="Follow Up Fix Jadwal Sosialisasi" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-lg transition">
                                        <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <a href="{{ route('visits.edit', $v->id) }}" title="Edit Kunjungan" class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg transition">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form method="POST" action="{{ route('visits.destroy', $v->id) }}" onsubmit="return confirm('Hapus data kunjungan ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Kunjungan" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada data kunjungan lapangan dicatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $visits->links() }}
        </div>
    </div>
</x-app-layout>
