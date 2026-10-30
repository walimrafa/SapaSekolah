<x-app-layout title="Log Aktivitas Pengguna & Audit Trail">
    <div class="space-y-6" x-data="{ detailModal: false, selectedLog: null }">

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800 text-slate-300 text-xs font-semibold mb-2 border border-slate-700">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-amber-400"></i>
                    <span>Audit Trail & Keamanan Sistem</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Log Aktivitas Pengguna</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Rekaman jejak operasional seluruh user yang mengakses dan memodifikasi data pada sistem SAPA Sekolah.
                </p>
            </div>

            <!-- Stats Bar -->
            <div class="flex items-center gap-3">
                <div class="px-3.5 py-2 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-right">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Aktivitas Hari Ini</p>
                    <p class="text-lg font-black text-slate-900">{{ number_format($totalToday) }}</p>
                </div>
                <div class="px-3.5 py-2 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-right">
                    <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Login Hari Ini</p>
                    <p class="text-lg font-black text-emerald-600">{{ number_format($totalLoginsToday) }}</p>
                </div>
                <div class="px-3.5 py-2 rounded-2xl bg-white border border-slate-200/80 shadow-soft text-right">
                    <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wider">Mutasi Data</p>
                    <p class="text-lg font-black text-amber-600">{{ number_format($totalChangesToday) }}</p>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-soft">
            <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Cari Keterangan / IP / User</label>
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3 top-3 text-slate-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik kata kunci..." class="w-full pl-9 pr-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                    </div>
                </div>

                <!-- User Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Pengguna (User)</label>
                    <select name="user_id" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        <option value="all">Semua Pengguna</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->role?->name ?? 'User' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Modul Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Modul / Menu</label>
                    <select name="module" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        <option value="all">Semua Modul</option>
                        @foreach($modules as $m)
                            <option value="{{ $m }}" {{ request('module') == $m ? 'selected' : '' }}>{{ strtoupper($m) }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Aksi Filter -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Tipe Aksi</label>
                    <select name="action" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-semibold focus:border-brand-500 focus:outline-none">
                        <option value="all">Semua Aksi</option>
                        @foreach($actions as $a)
                            <option value="{{ $a }}" {{ request('action') == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit & Reset -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-brand-600 hover:bg-brand-500 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Filter</span>
                    </button>
                    @if(request()->hasAny(['search', 'user_id', 'module', 'action', 'date_from', 'date_to']))
                        <a href="{{ route('activity-logs.index') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs transition" title="Reset Filter">
                            <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-900 text-white uppercase text-[10px] tracking-wider font-extrabold">
                        <tr>
                            <th class="py-3.5 px-4">Waktu & Tanggal</th>
                            <th class="py-3.5 px-4">Pengguna</th>
                            <th class="py-3.5 px-3 text-center">Aksi</th>
                            <th class="py-3.5 px-3">Modul</th>
                            <th class="py-3.5 px-4">Keterangan Aktivitas</th>
                            <th class="py-3.5 px-3">IP & Perangkat</th>
                            <th class="py-3.5 px-3 text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/80 transition duration-150">
                                <!-- Waktu -->
                                <td class="py-3 px-4 font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                    <span class="font-bold text-slate-800 block">{{ $log->created_at->format('d/m/Y') }}</span>
                                    <span class="text-[10px] text-slate-400">{{ $log->created_at->format('H:i:s') }} WIB</span>
                                </td>

                                <!-- Pengguna -->
                                <td class="py-3 px-4">
                                    @if($log->user)
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-brand-600 to-amber-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($log->user->name, 0, 2)) }}
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-slate-900 truncate">{{ $log->user->name }}</p>
                                                <p class="text-[10px] text-amber-600 font-semibold">{{ $log->user->role?->name ?? 'User' }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-slate-400 font-medium text-[11px]">
                                            <i data-lucide="globe" class="w-3 h-3"></i>
                                            <span>Publik (Tamu)</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Aksi -->
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @php
                                        $badgeColor = match(strtoupper($log->action)) {
                                            'LOGIN' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'LOGOUT' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'CREATE' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'UPDATE', 'UPDATE_STATUS' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'DELETE' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'VALIDATE', 'VALIDATE_NEW_STUDENT' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'TOGGLE_PUBLIC_PORTAL' => 'bg-orange-50 text-orange-700 border-orange-200',
                                            default => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                    @endphp
                                    <span class="inline-block px-2 py-0.5 rounded-md text-[10px] font-extrabold border {{ $badgeColor }}">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <!-- Modul -->
                                <td class="py-3 px-3">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                        {{ $log->module }}
                                    </span>
                                </td>

                                <!-- Keterangan -->
                                <td class="py-3 px-4 font-medium text-slate-800 max-w-md">
                                    {{ $log->description ?: '-' }}
                                    @if($log->campus)
                                        <span class="inline-block text-[10px] text-brand-600 font-semibold block mt-0.5">
                                            &bull; {{ $log->campus->name }}
                                        </span>
                                    @endif
                                </td>

                                <!-- IP -->
                                <td class="py-3 px-3 text-[11px] font-mono text-slate-500 whitespace-nowrap">
                                    <span>{{ $log->ip_address ?: '-' }}</span>
                                </td>

                                <!-- Detail Action -->
                                <td class="py-3 px-3 text-center">
                                    @if(!empty($log->old_data) || !empty($log->new_data))
                                        <button type="button" @click="selectedLog = {{ json_encode($log) }}; detailModal = true" class="p-1.5 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-slate-100 transition" title="Lihat Payload Data">
                                            <i data-lucide="code" class="w-4 h-4"></i>
                                        </button>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i data-lucide="clipboard-list" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                    <p class="font-bold text-sm">Belum ada rekaman log aktivitas</p>
                                    <p class="text-xs text-slate-400">Semua aksi pengguna akan tercatat otomatis di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($logs->hasPages())
                <div class="p-4 border-t border-slate-100">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

        <!-- Detail Modal (Alpine.js) -->
        <div x-show="detailModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-sm transition-opacity" @click="detailModal = false"></div>
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Rincian Data Aktivitas (Audit Diff)</h3>
                            <p class="text-xs text-slate-500" x-text="selectedLog ? selectedLog.action + ' • Modul: ' + selectedLog.module : ''"></p>
                        </div>
                        <button type="button" @click="detailModal = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div class="space-y-3 text-xs">
                        <template x-if="selectedLog && selectedLog.description">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                                <span class="font-bold text-slate-700 block mb-0.5">Keterangan:</span>
                                <p class="text-slate-600" x-text="selectedLog.description"></p>
                            </div>
                        </template>

                        <template x-if="selectedLog && selectedLog.old_data">
                            <div>
                                <span class="font-bold text-rose-600 block mb-1">Data Lama (Before):</span>
                                <pre class="p-3 bg-slate-900 text-rose-300 rounded-xl overflow-x-auto text-[11px] font-mono" x-text="JSON.stringify(selectedLog.old_data, null, 2)"></pre>
                            </div>
                        </template>

                        <template x-if="selectedLog && selectedLog.new_data">
                            <div>
                                <span class="font-bold text-emerald-600 block mb-1">Data Baru (After):</span>
                                <pre class="p-3 bg-slate-900 text-emerald-300 rounded-xl overflow-x-auto text-[11px] font-mono" x-text="JSON.stringify(selectedLog.new_data, null, 2)"></pre>
                            </div>
                        </template>
                    </div>

                    <div class="pt-3 border-t border-slate-100 text-right">
                        <button type="button" @click="detailModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

