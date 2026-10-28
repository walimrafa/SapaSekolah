@php
    $appSettings = \App\Services\AppSettingService::all();
@endphp
<x-app-layout title="Dashboard Command Center">
    <!-- Top Welcome Banner & Marketing Target Bar -->
    <div class="mb-8 p-6 lg:p-8 rounded-3xl bg-gradient-to-r from-slate-950 via-brand-navy to-slate-900 text-white shadow-xl relative overflow-hidden">
        @if(!empty($appSettings['campus_building_url']))
            <div class="absolute inset-0 bg-cover bg-center opacity-20 mix-blend-luminosity scale-105 pointer-events-none" style="background-image: url('{{ $appSettings['campus_building_url'] }}')"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-slate-950 via-brand-navy/90 to-slate-900/90 pointer-events-none"></div>
        @endif
        <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-bold mb-3 border border-amber-500/30">
                    <i data-lucide="zap" class="w-3.5 h-3.5"></i>
                    <span>SAPA Sekolah Marketing Command Center</span>
                </div>
                <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">
                    Halo, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="text-slate-300 text-sm mt-1 max-w-xl">
                    Pantau pemetaan sekolah SLTA Karawang, optimalkan rencana kunjungan marketing, dan tingkatkan konversi PMB kampus secara realtime.
                </p>
            </div>

            <!-- Quick Action Toolbar -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('map.index') }}"
                    class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold rounded-xl text-xs shadow-lg shadow-amber-500/20 flex items-center space-x-2 transition duration-150">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Buka Peta GIS</span>
                </a>
                <a href="{{ route('schools.index') }}"
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl text-xs border border-white/20 flex items-center space-x-2 transition duration-150">
                    <i data-lucide="building-2" class="w-4 h-4"></i>
                    <span>Data Sekolah</span>
                </a>
                <a href="{{ route('visit-plans.index') }}"
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-xl text-xs border border-white/20 flex items-center space-x-2 transition duration-150">
                    <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                    <span>Buat Rencana</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 10 KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <x-stat-card title="Total Sekolah" :value="$totalSchools" icon="school" color="navy" subtitle="30 Kecamatan Karawang" />
        <x-stat-card title="Prioritas A" :value="$priorityA" icon="star" color="red" subtitle="Skor 80 - 100" />
        <x-stat-card title="Prioritas B" :value="$priorityB" icon="check-circle" color="amber" subtitle="Skor 60 - 79" />
        <x-stat-card title="Prioritas C" :value="$priorityC" icon="shield" color="emerald" subtitle="Skor < 60" />
        <x-stat-card title="Sudah Dikunjungi" :value="$visitedCount" icon="map-pin" color="blue" subtitle="Terkunjungi" />

        <x-stat-card title="Belum Dikunjungi" :value="$unvisitedCount" icon="clock" color="white" subtitle="Target Outreach" />
        <x-stat-card title="Follow Up Hari Ini" :value="$followUpsToday" icon="phone-call" color="amber" subtitle="Jatuh Tempo" />
        <x-stat-card title="Total Prospek" :value="$totalProspects" icon="users" color="purple" subtitle="Data CRM" />
        <x-stat-card title="Registrasi" :value="$registeredProspects" icon="user-check" color="emerald" subtitle="Telah mendaftar" />
        <x-stat-card title="Mahasiswa Fix" :value="$mahasiswaCount" icon="graduation-cap" color="navy" subtitle="Konversi PMB" />
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Schools per Kecamatan Chart (8 Cols) -->
        <div class="lg:col-span-8 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Distribution Sekolah per Kecamatan</h3>
                    <p class="text-xs text-slate-500">Jumlah sekolah SLTA sederajat di Kabupaten Karawang</p>
                </div>
                <span class="px-2.5 py-1 bg-brand-50 text-brand-700 text-xs font-bold rounded-lg border border-brand-100">
                    {{ count($schoolsPerDistrict) }} Kecamatan
                </span>
            </div>
            <div class="h-72">
                <canvas id="districtChart"></canvas>
            </div>
        </div>

        <!-- Schools per Jenjang Chart (4 Cols) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft flex flex-col justify-between">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Kategori Jenjang</h3>
                    <p class="text-xs text-slate-500">SMA, SMK, MA, SLB</p>
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Widgets Row: Leaderboard Top Schools & Next Visit Recommendation -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        <!-- Top 10 Potential Schools Leaderboard (7 Cols) -->
        <div class="lg:col-span-7 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                        <i data-lucide="trophy" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Top 10 Sekolah Paling Potensial</h3>
                        <p class="text-xs text-slate-500">Berdasarkan algoritma weighted scoring SAPA Sekolah</p>
                    </div>
                </div>
                <a href="{{ route('priorities.index') }}" class="text-xs font-bold text-brand-600 hover:text-brand-700">Lihat Semua &rarr;</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                            <th class="pb-3 px-2">Rank</th>
                            <th class="pb-3">Nama Sekolah</th>
                            <th class="pb-3">Kecamatan</th>
                            <th class="pb-3 text-center">Kelas XII</th>
                            <th class="pb-3 text-center">Score</th>
                            <th class="pb-3 text-center">Prioritas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topPotentialSchools as $index => $school)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-2 font-bold text-slate-500">#{{ $index + 1 }}</td>
                                <td class="py-3 font-semibold text-slate-800">
                                    <span class="block truncate max-w-xs">{{ $school->name }}</span>
                                    <span class="text-[10px] text-slate-400 font-normal">{{ $school->schoolType ? $school->schoolType->name : '' }} &bull; {{ strtoupper($school->status) }}</span>
                                </td>
                                <td class="py-3 text-slate-600">{{ $school->district ? $school->district->name : '-' }}</td>
                                <td class="py-3 text-center font-semibold text-slate-700">{{ $school->grade_12_count }}</td>
                                <td class="py-3 text-center font-extrabold text-brand-600">{{ number_format($school->potential_score, 1) }}</td>
                                <td class="py-3 text-center">
                                    @php
                                        $badgeColor = match($school->priority_level) {
                                            'A' => 'bg-red-100 text-red-700 border-red-200',
                                            'B' => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'C' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                            default => 'bg-slate-100 text-slate-600 border-slate-200',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border {{ $badgeColor }}">
                                        {{ $school->priority_level ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-400">Belum ada data sekolah terdaftar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Suggested Next Visits & Upcoming Follow-ups (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- Suggested Visits -->
            <div class="bg-gradient-to-br from-brand-900 to-slate-900 text-white p-6 rounded-3xl shadow-soft">
                <div class="flex items-center space-x-3 mb-4">
                    <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center border border-amber-500/30">
                        <i data-lucide="sparkles" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Rekomendasi Kunjungan Berikutnya</h3>
                        <p class="text-[11px] text-slate-300">Sekolah belum pernah dikunjungi &amp; potensi tinggi</p>
                    </div>
                </div>

                <div class="space-y-3">
                    @forelse($suggestedSchools->take(3) as $s)
                        <div class="p-3.5 rounded-2xl bg-white/10 border border-white/10 hover:bg-white/15 transition flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-white truncate max-w-[200px]">{{ $s->name }}</h4>
                                <p class="text-[10px] text-slate-300 mt-0.5">{{ $s->district ? $s->district->name : '' }} &bull; {{ $s->grade_12_count }} Siswa Klp XII</p>
                            </div>
                            <a href="{{ route('visit-plans.index') }}" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold text-[10px] rounded-lg shadow transition">
                                Jadwalkan
                            </a>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-4">Semua sekolah potensial sudah dikunjungi.</p>
                    @endforelse
                </div>
            </div>

            <!-- Follow Ups Upcoming -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-soft">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                        <i data-lucide="phone-call" class="w-4 h-4 text-amber-500"></i>
                        <span>Follow Up Terdekat</span>
                    </h3>
                    <a href="{{ route('follow-ups.index') }}" class="text-[11px] font-semibold text-brand-600">Semua</a>
                </div>

                <div class="space-y-3">
                    @forelse($upcomingFollowups as $fu)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div>
                                <p class="text-xs font-bold text-slate-800">{{ $fu->school ? $fu->school->name : 'Sekolah' }}</p>
                                <p class="text-[10px] text-slate-500">PIC: {{ $fu->user ? $fu->user->name : '-' }} &bull; {{ $fu->method }}</p>
                            </div>
                            <span class="text-[10px] font-bold px-2 py-1 bg-amber-100 text-amber-800 rounded-lg">
                                {{ \Carbon\Carbon::parse($fu->followup_date)->format('d M') }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-3">Tidak ada jadwal follow-up terdekat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Chart JS Scripts -->
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Districts Bar Chart
            const districtCtx = document.getElementById('districtChart');
            if (districtCtx) {
                new Chart(districtCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($schoolsPerDistrict->pluck('name')) !!},
                        datasets: [{
                            label: 'Jumlah Sekolah',
                            data: {!! json_encode($schoolsPerDistrict->pluck('total')) !!},
                            backgroundColor: '#3b82f6',
                            borderRadius: 8,
                            hoverBackgroundColor: '#1e3a5f'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // School Type Doughnut Chart
            const typeCtx = document.getElementById('typeChart');
            if (typeCtx) {
                new Chart(typeCtx, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($schoolsPerType->pluck('name')) !!},
                        datasets: [{
                            data: {!! json_encode($schoolsPerType->pluck('total')) !!},
                            backgroundColor: ['#1e3a5f', '#3b82f6', '#f59e0b', '#10b981'],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                        },
                        cutout: '70%'
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
