<x-app-layout title="Rencana Kunjungan Marketing">
    <!-- Header Title & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Rencana Kunjungan Marketing</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penjadwalan agenda kunjungan tim marketing ke sekolah SLTA</p>
        </div>
        
        <a href="{{ route('visit-plans.create') }}"
            class="px-4 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
            <i data-lucide="calendar-plus" class="w-4 h-4"></i>
            <span>Buat Agenda Kunjungan Baru</span>
        </a>
    </div>

    <!-- Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Total Agenda Terjadwal</p>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalPlanned) }} Agenda</h3>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Kunjungan Hari Ini</p>
            <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($todayPlanned) }} Agenda</h3>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Selesai Dikunjungi</p>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalCompleted) }} Selesai</h3>
        </div>
    </div>

    <!-- Filter & Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
        <!-- Table Header & Filters -->
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <form method="GET" action="{{ route('visit-plans.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <select name="status" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300 font-medium">
                    <option value="">Semua Status Agenda</option>
                    <option value="planned" {{ request('status') == 'planned' ? 'selected' : '' }}>Planned (Terjadwal)</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled (Batal)</option>
                </select>

                <select name="user_id" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300 font-medium">
                    <option value="">Semua Marketing</option>
                    @foreach($marketingUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>

                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300 font-medium">
                
                @if(request()->anyFilled(['status', 'user_id', 'date']))
                    <a href="{{ route('visit-plans.index') }}" class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition">
                        Reset Filter
                    </a>
                @endif
            </form>

            <span class="text-xs font-bold text-slate-500">Total: {{ $plans->total() }} Rencana</span>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4">Tanggal Rencana</th>
                        <th class="py-3.5 px-4">Sekolah Tujuan</th>
                        <th class="py-3.5 px-4">Kecamatan</th>
                        <th class="py-3.5 px-4">Marketing PIC</th>
                        <th class="py-3.5 px-4">Tujuan Kunjungan</th>
                        <th class="py-3.5 px-4 text-center">Prioritas</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($plans as $plan)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($plan->planned_date)->format('d M Y') }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                <a href="{{ route('schools.show', $plan->school_id) }}" class="hover:text-brand-600 block">
                                    {{ $plan->school ? $plan->school->name : '-' }}
                                </a>
                                <span class="text-[10px] text-slate-400 font-normal">{{ $plan->school && $plan->school->schoolType ? $plan->school->schoolType->name : '' }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">
                                {{ $plan->school && $plan->school->district ? $plan->school->district->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-brand-600 whitespace-nowrap">
                                {{ $plan->user ? $plan->user->name : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-700 max-w-xs truncate">
                                {{ $plan->purpose }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold whitespace-nowrap">
                                <span class="px-2 py-0.5 text-[10px] rounded bg-slate-100 text-slate-700">Prio {{ $plan->priority }}</span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                @php
                                    $sColor = match($plan->status) {
                                        'completed' => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                                        'cancelled' => 'bg-red-100 text-red-800 border border-red-200',
                                        default => 'bg-amber-100 text-amber-800 border border-amber-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-full uppercase {{ $sColor }}">
                                    {{ $plan->status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="{{ route('visit-plans.edit', $plan->id) }}" title="Edit Agenda" class="p-1.5 bg-brand-50 hover:bg-brand-100 text-brand-600 rounded-lg transition">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form method="POST" action="{{ route('visit-plans.destroy', $plan->id) }}" onsubmit="return confirm('Batalkan agenda kunjungan ini?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Agenda" class="p-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-400">Belum ada agenda kunjungan terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-100">
            {{ $plans->links() }}
        </div>
    </div>
</x-app-layout>
