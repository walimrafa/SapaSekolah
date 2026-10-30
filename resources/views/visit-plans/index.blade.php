<x-app-layout title="Rencana Kunjungan Marketing">
    <!-- Header Title & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Rencana Kunjungan Marketing</h1>
            <p class="text-xs text-slate-500 mt-0.5">Penjadwalan agenda kunjungan tim marketing ke sekolah SLTA</p>
        </div>
        
        <div class="flex items-center gap-2">
            <a href="{{ route('visits.create') }}"
                class="px-3.5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-1.5 transition">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
                <span>Catat Kunjungan Lapangan</span>
            </a>
            <a href="{{ route('visit-plans.create') }}"
                class="px-4 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-md flex items-center space-x-2 transition">
                <i data-lucide="calendar-plus" class="w-4 h-4 text-amber-400"></i>
                <span>Buat Agenda Baru</span>
            </a>
        </div>
    </div>

    <!-- Alert Toast -->
    @if(session('toast'))
        <div class="mb-6 p-4 rounded-2xl {{ (session('toast')['type'] ?? '') === 'error' ? 'bg-rose-50 border border-rose-200 text-rose-800' : 'bg-emerald-50 border border-emerald-200 text-emerald-800' }} text-xs font-bold flex items-center gap-2 shadow-sm">
            <i data-lucide="{{ (session('toast')['type'] ?? '') === 'error' ? 'alert-circle' : 'check-circle' }}" class="w-4 h-4 shrink-0"></i>
            <span>{{ session('toast')['message'] }}</span>
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="flex flex-wrap items-center gap-2 mb-4">
        <a href="{{ route('visit-plans.index', array_merge(request()->except(['status', 'page']), ['status' => 'planned'])) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($currentStatus ?? 'planned') === 'planned' ? 'bg-brand-navy text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i data-lucide="clock" class="w-3.5 h-3.5 {{ ($currentStatus ?? 'planned') === 'planned' ? 'text-amber-400' : 'text-slate-400' }}"></i>
            <span>Agenda Aktif / Terjadwal</span>
            <span class="px-2 py-0.5 text-[10px] rounded-full {{ ($currentStatus ?? 'planned') === 'planned' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700 font-extrabold' }}">
                {{ number_format($totalPlanned) }}
            </span>
        </a>

        <a href="{{ route('visit-plans.index', array_merge(request()->except(['status', 'page']), ['status' => 'completed'])) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($currentStatus ?? '') === 'completed' ? 'bg-emerald-700 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i data-lucide="check-circle-2" class="w-3.5 h-3.5 {{ ($currentStatus ?? '') === 'completed' ? 'text-emerald-200' : 'text-slate-400' }}"></i>
            <span>Sudah Dikunjungi (Pindah ke Data Kunjungan)</span>
            <span class="px-2 py-0.5 text-[10px] rounded-full {{ ($currentStatus ?? '') === 'completed' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800 font-extrabold' }}">
                {{ number_format($totalCompleted) }}
            </span>
        </a>

        <a href="{{ route('visit-plans.index', array_merge(request()->except(['status', 'page']), ['status' => 'all'])) }}"
            class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 {{ ($currentStatus ?? '') === 'all' ? 'bg-slate-800 text-white shadow-md' : 'bg-white text-slate-600 hover:bg-slate-50 border border-slate-200' }}">
            <i data-lucide="layers" class="w-3.5 h-3.5 text-slate-400"></i>
            <span>Semua Agenda</span>
            <span class="px-2 py-0.5 text-[10px] rounded-full {{ ($currentStatus ?? '') === 'all' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700 font-extrabold' }}">
                {{ number_format($totalAll) }}
            </span>
        </a>
    </div>

    <!-- Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Total Agenda Terjadwal</p>
            <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($totalPlanned) }} Agenda</h3>
            <p class="text-[11px] text-slate-400 mt-0.5">Menunggu realisasi kunjungan</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Kunjungan Hari Ini</p>
            <h3 class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($todayPlanned) }} Agenda</h3>
            <p class="text-[11px] text-amber-700 mt-0.5">Agenda untuk hari ini</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-[11px] font-bold text-slate-400 uppercase">Selesai Dikunjungi</p>
            <h3 class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($totalCompleted) }} Selesai</h3>
            <p class="text-[11px] text-emerald-700 mt-0.5">Tercatat di Data Kunjungan Sekolah</p>
        </div>
    </div>

    <!-- Filter & Table Card with Alpine.js Checkbox Management -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden"
        x-data="{
            selectedIds: [],
            allIds: {{ json_encode($plans->pluck('id')->values()->all()) }},
            get selectAll() {
                if (this.allIds.length === 0) return false;
                return this.allIds.every(id => this.selectedIds.includes(id));
            },
            toggleAll() {
                if (this.selectAll) {
                    this.selectedIds = [];
                } else {
                    this.selectedIds = [...this.allIds];
                }
            },
            confirmBulkDelete(e) {
                if (this.selectedIds.length === 0) {
                    e.preventDefault();
                    alert('Pilih minimal satu agenda yang ingin dihapus.');
                    return false;
                }
                if (!confirm('Apakah Anda yakin ingin menghapus ' + this.selectedIds.length + ' agenda rencana kunjungan yang dipilih?')) {
                    e.preventDefault();
                    return false;
                }
                return true;
            }
        }">
        
        <!-- Table Header & Filters -->
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50/50">
            <form method="GET" action="{{ route('visit-plans.index') }}" class="flex flex-wrap items-center gap-2 text-xs">
                <input type="hidden" name="status" value="{{ $currentStatus }}">

                <select name="user_id" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300 font-medium">
                    <option value="">Semua Marketing PIC</option>
                    @foreach($marketingUsers as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>

                <select name="priority" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300 font-medium">
                    <option value="">Semua Prioritas</option>
                    <option value="A" {{ request('priority') == 'A' ? 'selected' : '' }}>Prioritas A (Tinggi)</option>
                    <option value="B" {{ request('priority') == 'B' ? 'selected' : '' }}>Prioritas B (Sedang)</option>
                    <option value="C" {{ request('priority') == 'C' ? 'selected' : '' }}>Prioritas C (Standar)</option>
                </select>

                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="py-1.5 px-3 rounded-xl border border-slate-300 font-medium">
                
                @if(request()->anyFilled(['user_id', 'priority', 'date']))
                    <a href="{{ route('visit-plans.index', ['status' => $currentStatus]) }}" class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold rounded-xl text-xs transition">
                        Reset Filter
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-3">
                <!-- Bulk Delete Action Button (Appears when >= 1 checkbox checked) -->
                <form method="POST" action="{{ route('visit-plans.bulk-destroy') }}" @submit="confirmBulkDelete($event)" x-show="selectedIds.length > 0" x-cloak class="inline-flex items-center gap-2">
                    @csrf
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <button type="submit"
                        class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center gap-1.5 animate-pulse">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Hapus Terpilih (<span x-text="selectedIds.length"></span>)</span>
                    </button>
                </form>

                <span class="text-xs font-bold text-slate-500">Total: {{ $plans->total() }} Rencana</span>
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-900 text-white font-bold uppercase text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-10">
                            <input type="checkbox"
                                :checked="selectAll"
                                @change="toggleAll()"
                                title="Pilih Semua di Halaman Ini"
                                class="w-4 h-4 rounded text-brand-600 border-slate-400 focus:ring-brand-500 cursor-pointer">
                        </th>
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
                        <tr class="hover:bg-slate-50 transition" :class="selectedIds.includes({{ $plan->id }}) ? 'bg-amber-50/60' : ''">
                            <td class="py-3.5 px-4 text-center">
                                <input type="checkbox"
                                    :value="{{ $plan->id }}"
                                    x-model.number="selectedIds"
                                    class="w-4 h-4 rounded text-brand-600 border-slate-300 focus:ring-brand-500 cursor-pointer">
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($plan->planned_date)->translatedFormat('d M Y') }}
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
                            <td class="py-3.5 px-4 text-slate-700 max-w-xs truncate" title="{{ $plan->purpose }}">
                                {{ $plan->purpose }}
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold whitespace-nowrap">
                                @php
                                    $prioBg = match($plan->priority) {
                                        'A' => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                                        'B' => 'bg-blue-100 text-blue-800 border-blue-300',
                                        default => 'bg-slate-100 text-slate-700 border-slate-300',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 text-[10px] rounded-lg border font-extrabold {{ $prioBg }}">
                                    Prio {{ $plan->priority }}
                                </span>
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
                                    {{ $plan->status === 'completed' ? 'Selesai Dikunjungi' : ($plan->status === 'cancelled' ? 'Dibatalkan' : 'Terjadwal') }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center space-x-1.5">
                                    @if($plan->status === 'planned')
                                        <a href="{{ route('visits.create') }}?plan_id={{ $plan->id }}&school_id={{ $plan->school_id }}" title="Eksekusi & Catat Kunjungan Lapangan" class="p-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg transition font-bold text-[10px] flex items-center gap-1">
                                            <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                                            <span>Kunjungi</span>
                                        </a>
                                    @else
                                        <a href="{{ route('visits.index', ['search' => $plan->school?->name]) }}" title="Lihat di Data Kunjungan" class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition text-[10px] font-semibold flex items-center gap-1">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            <span>Kunjungan</span>
                                        </a>
                                    @endif

                                    <a href="{{ route('visit-plans.edit', $plan->id) }}" title="Edit Agenda" class="p-1.5 bg-brand-50 hover:bg-brand-100 text-brand-600 rounded-lg transition">
                                        <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                    </a>
                                    <form method="POST" action="{{ route('visit-plans.destroy', $plan->id) }}" onsubmit="return confirm('Hapus agenda kunjungan ini?')" class="inline">
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
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i data-lucide="calendar" class="w-8 h-8 mx-auto mb-2 text-slate-300"></i>
                                <p class="font-bold text-slate-600">Tidak ada agenda rencana kunjungan pada status ini.</p>
                                <p class="text-xs text-slate-400 mt-1">Agenda yang sudah selesai dikunjungi otomatis berpindah ke Data Kunjungan Sekolah.</p>
                            </td>
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
