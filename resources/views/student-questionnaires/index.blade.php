<x-app-layout title="Hasil Kuisioner Siswa">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Hasil Kuisioner Siswa</h1>
            <p class="text-xs text-slate-500 mt-0.5">Data responden dan jawaban kuisioner minat kuliah UBSI.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openImportModal()" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2">
                <i data-lucide="file-up" class="w-4 h-4"></i>
                Import Data Manual
            </button>
            <a href="{{ route('student-follow-ups.index') }}" class="px-4 py-2.5 bg-gradient-to-r from-brand-600 to-amber-500 hover:from-brand-700 hover:to-amber-600 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                Kelola Follow Up Siswa
            </a>
            <a href="{{ route('student-questionnaires.export') }}" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2">
                <i data-lucide="download" class="w-4 h-4"></i>
                Unduh Hasil Excel
            </a>
            <a href="{{ route('student-questionnaire.create') }}" target="_blank" rel="noopener" class="px-4 py-2.5 bg-brand-navy hover:bg-slate-900 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                Buka Form Publik
            </a>
            <a href="{{ route('qr-codes.index') }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                QR Code untuk Siswa
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-xs font-bold text-slate-400 uppercase">Total Responden</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($statistics['total']) }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-emerald-200 shadow-soft">
            <p class="text-xs font-bold text-emerald-700 uppercase">Berminat Beasiswa UBSI</p>
            <p class="text-2xl font-extrabold text-emerald-700 mt-1">{{ number_format($statistics['scholarship']['ya']) }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $statistics['total'] ? number_format($statistics['scholarship']['ya'] / $statistics['total'] * 100, 1) : 0 }}% dari responden</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-slate-200/80 shadow-soft">
            <p class="text-xs font-bold text-slate-500 uppercase">Tidak Berminat Beasiswa</p>
            <p class="text-2xl font-extrabold text-slate-700 mt-1">{{ number_format($statistics['scholarship']['tidak']) }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $statistics['total'] ? number_format($statistics['scholarship']['tidak'] / $statistics['total'] * 100, 1) : 0 }}% dari responden</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 mb-6">
        <section class="xl:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Rata-rata Skor per Aspek</h2>
                <p class="text-xs text-slate-500 mt-1">Skala 1 (sangat tidak setuju) sampai 5 (sangat setuju).</p>
            </div>
            <div class="p-4 h-72">
                <canvas id="questionnaireSectionChart" role="img" aria-label="Diagram rata-rata skor per aspek kuisioner"></canvas>
            </div>
        </section>

        <div class="space-y-4">
            <section class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
                <div class="p-4 border-b border-slate-100">
                    <h2 class="text-sm font-bold text-slate-900">Minat Beasiswa UBSI</h2>
                </div>
                <div class="p-4 h-52">
                    <canvas id="questionnaireScholarshipChart" role="img" aria-label="Diagram minat program beasiswa UBSI"></canvas>
                </div>
            </section>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Responden per Jurusan</h2>
            </div>
            <div class="p-4 h-72">
                <canvas id="questionnaireMajorChart" role="img" aria-label="Diagram jumlah responden per jurusan"></canvas>
            </div>
        </section>
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-900">Responden per Kelas</h2>
            </div>
            <div class="p-4 h-72">
                <canvas id="questionnaireClassChart" role="img" aria-label="Diagram jumlah responden per kelas"></canvas>
            </div>
        </section>
    </div>

    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900">Sebaran Seluruh Skor Jawaban</h2>
            <p class="text-xs text-slate-500 mt-1">Jumlah jawaban pada skala 1 sampai 5 dari seluruh pernyataan kuisioner.</p>
        </div>
        <div class="p-4 h-64">
            <canvas id="questionnaireScoreChart" role="img" aria-label="Diagram sebaran seluruh skor jawaban kuisioner"></canvas>
        </div>
    </section>

    <section class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden mb-6">
        <div class="p-4 border-b border-slate-100">
            <h2 class="text-sm font-bold text-slate-900">Statistik Jawaban per Pernyataan</h2>
            <p class="text-xs text-slate-500 mt-1">Jumlah jawaban untuk setiap nilai skala dan rata-rata skor responden.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold">
                    <tr>
                        <th class="py-3 px-4 min-w-80">Pernyataan</th>
                        <th class="py-3 px-4 text-center">Rata-rata</th>
                        @foreach(range(1, 5) as $score)
                            <th class="py-3 px-4 text-center">Skor {{ $score }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($questionSections as $sectionTitle => $questions)
                        <tr class="bg-slate-50">
                            <th colspan="7" class="py-2 px-4 text-left font-extrabold text-slate-800">{{ $sectionTitle }}</th>
                        </tr>
                        @foreach($questions as $key => $question)
                            <tr>
                                <td class="py-3 px-4 text-slate-700">{{ $question }}</td>
                                <td class="py-3 px-4 text-center font-bold">{{ $statistics['questions'][$key]['average'] !== null ? number_format($statistics['questions'][$key]['average'], 2) : '-' }}</td>
                                @foreach(range(1, 5) as $score)
                                    <td class="py-3 px-4 text-center">{{ number_format($statistics['questions'][$key]['scores'][$score]) }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <form method="GET" action="{{ route('student-questionnaires.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa, sekolah, email, no HP..." class="w-full max-w-sm px-4 py-2.5 rounded-xl border border-slate-300 text-sm">
        <select name="school_id" class="px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold" onchange="this.form.submit()">
            <option value="">-- Semua Sekolah --</option>
            @foreach($schools as $sch)
                <option value="{{ $sch->id }}" {{ (string)request('school_id') === (string)$sch->id ? 'selected' : '' }}>{{ $sch->name }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs">Cari</button>
        @if(request()->filled('search') || request()->filled('school_id'))
            <a href="{{ route('student-questionnaires.index') }}" class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs flex items-center">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-soft overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-900 text-white font-bold uppercase">
                    <tr>
                        <th class="py-3 px-4">Waktu Kirim</th>
                        <th class="py-3 px-4">Nama Siswa</th>
                        <th class="py-3 px-4">Asal Sekolah</th>
                        <th class="py-3 px-4">Kelas / Jurusan</th>
                        <th class="py-3 px-4">Kontak Siswa</th>
                        <th class="py-3 px-4">Nama Wali</th>
                        <th class="py-3 px-4">Minat Beasiswa</th>
                        <th class="py-3 px-4">Status Follow Up</th>
                        <th class="py-3 px-4">Jawaban</th>
                        <th class="py-3 px-4 text-center">Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($questionnaires as $response)
                        <tr class="align-top hover:bg-slate-50">
                            <td class="py-4 px-4 whitespace-nowrap">{{ $response->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-4 px-4">
                                <p class="font-bold text-slate-900">{{ $response->student_name }}</p>
                                <p class="text-slate-500 mt-1">{{ $response->student_social_media ?: 'Medsos: -' }}</p>
                            </td>
                            <td class="py-4 px-4">
                                <span class="font-bold text-slate-800">{{ $response->school_display_name }}</span>
                                @if($response->school && $response->school->district)
                                    <span class="block text-[11px] text-slate-400">Kec. {{ $response->school->district->name }}</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-semibold">{{ $response->student_class ?: '-' }}</p>
                                <p class="text-slate-500 mt-1">{{ $response->student_major ?: '-' }}</p>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-bold">{{ $response->student_phone }}</p>
                                <p class="text-slate-500 mt-1">{{ $response->student_email }}</p>
                            </td>
                            <td class="py-4 px-4">
                                <p class="font-semibold">{{ $response->guardian_name }}</p>
                                <p class="text-slate-500 mt-1">{{ $response->guardian_phone }}</p>
                                <p class="text-slate-500 mt-1">{{ $response->guardian_social_media ?: 'Medsos: -' }}</p>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full font-bold {{ $response->scholarship_interest === 'ya' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $response->scholarship_interest === 'ya' ? 'Ya (Berminat)' : 'Tidak' }}
                                </span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $response->followup_badge_class }}">
                                    {{ $response->followup_status_label }}
                                </span>
                                @if($response->last_followup_at)
                                    <p class="text-[10px] text-slate-400 mt-1">{{ $response->last_followup_at->format('d/m H:i') }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4 min-w-52">
                                <details>
                                    <summary class="cursor-pointer font-bold text-brand-700">Lihat 16 jawaban skala</summary>
                                    <div class="mt-3 space-y-4">
                                        @foreach($questionSections as $sectionTitle => $questions)
                                            <div>
                                                <h3 class="font-extrabold text-slate-900 mb-2">{{ $sectionTitle }}</h3>
                                                <ol class="space-y-2">
                                                    @foreach($questions as $key => $question)
                                                        <li class="text-slate-600">
                                                            <span>{{ $question }}</span>
                                                            @php($score = $response->answers[$key] ?? null)
                                                            <span class="font-extrabold text-slate-900">— {{ $score ? "{$score} - {$scaleLabels[$score]}" : '-' }}</span>
                                                        </li>
                                                    @endforeach
                                                </ol>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            </td>
                            <td class="py-4 px-4 text-center">
                                <a href="{{ route('student-follow-ups.index', ['search' => $response->student_phone ?: $response->student_name]) }}" 
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition">
                                    <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                    <span>Follow Up</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 px-4 text-center text-slate-500">
                                {{ request()->filled('search') ? 'Tidak ada responden yang cocok dengan pencarian.' : 'Belum ada jawaban kuisioner yang masuk.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">
            {{ $questionnaires->links() }}
        </div>
    </div>
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (!window.Chart) {
                    return;
                }

                const chartColors = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#2563eb'];
                const createBarChart = (id, labels, values, color, horizontal = false, max = undefined, integerTicks = true) => {
                    const canvas = document.getElementById(id);
                    if (!canvas) return;

                    new window.Chart(canvas, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [{
                                data: values,
                                backgroundColor: color,
                                borderRadius: 6,
                                maxBarThickness: 36,
                            }],
                        },
                        options: {
                            indexAxis: horizontal ? 'y' : 'x',
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    max: horizontal ? undefined : max,
                                    ticks: integerTicks ? { precision: 0 } : {},
                                },
                                y: {
                                    beginAtZero: true,
                                    max: horizontal ? max : undefined,
                                    ticks: integerTicks ? { precision: 0 } : {},
                                },
                            },
                        },
                    });
                };

                createBarChart(
                    'questionnaireSectionChart',
                    @json(array_keys($statistics['sections'])),
                    @json(array_values($statistics['sections'])),
                    '#2563eb',
                    false,
                    5,
                    false
                );
                createBarChart(
                    'questionnaireMajorChart',
                    @json(array_keys($statistics['majors'])),
                    @json(array_values($statistics['majors'])),
                    '#8b5cf6',
                    true
                );
                createBarChart(
                    'questionnaireClassChart',
                    @json(array_keys($statistics['classes'])),
                    @json(array_values($statistics['classes'])),
                    '#0d9488',
                    true
                );
                createBarChart(
                    'questionnaireScoreChart',
                    ['1 - Sangat tidak setuju', '2 - Tidak setuju', '3 - Netral', '4 - Setuju', '5 - Sangat setuju'],
                    @json(array_values($statistics['scores'])),
                    chartColors
                );

                const scholarshipCanvas = document.getElementById('questionnaireScholarshipChart');
                if (scholarshipCanvas) {
                    new window.Chart(scholarshipCanvas, {
                        type: 'doughnut',
                        data: {
                            labels: ['Berminat', 'Tidak berminat'],
                            datasets: [{
                                data: [{{ $statistics['scholarship']['ya'] }}, {{ $statistics['scholarship']['tidak'] }}],
                                backgroundColor: ['#10b981', '#cbd5e1'],
                                borderWidth: 0,
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            responsive: true,
                            plugins: {
                                legend: { position: 'bottom' },
                            },
                        },
                    });
                }
            });

            function openImportModal() {
                document.getElementById('importModal').classList.remove('hidden');
                if (window.lucide) { window.lucide.createIcons(); }
            }

            function closeImportModal() {
                document.getElementById('importModal').classList.add('hidden');
            }
        </script>
    @endpush

    <!-- MODAL IMPORT MANUAL SISWA KUISIONER -->
    <div id="importModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-lg overflow-hidden transform transition-all">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-blue-600 text-white">
                        <i data-lucide="file-up" class="w-4 h-4"></i>
                    </span>
                    <div>
                        <h3 class="text-base font-extrabold tracking-tight">Import Data Siswa Manual</h3>
                        <p class="text-xs text-amber-400 mt-0.5">Unggah data siswa kuisioner kertas / form manual</p>
                    </div>
                </div>
                <button type="button" onclick="closeImportModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form action="{{ route('student-follow-ups.import') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                @csrf
                <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-2">
                    <p class="font-bold flex items-center gap-1.5 text-blue-800">
                        <i data-lucide="info" class="w-4 h-4 text-blue-600"></i>
                        Petunjuk Import Siswa Manual:
                    </p>
                    <ul class="list-disc pl-5 space-y-1 text-slate-700">
                        <li>Kolom wajib hanya <strong>Nama Lengkap Siswa</strong> dan <strong>No. HP Siswa</strong>.</li>
                        <li>Kolom Kelas, Jurusan, Minat Beasiswa, Minat Prodi, dan Wali bersifat opsional.</li>
                        <li>Data siswa yang diimpor langsung dapat di-follow up dengan kriteria status laporan.</li>
                    </ul>
                    <div class="pt-2">
                        <a href="{{ route('student-follow-ups.import-template') }}" class="inline-flex items-center gap-1.5 font-bold text-blue-700 hover:text-blue-900 underline">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            Download Template Excel (.xlsx)
                        </a>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Pilih File Excel / CSV (.xlsx, .xls, .csv) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required 
                           class="w-full text-xs text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-300 rounded-2xl p-2 bg-slate-50">
                </div>

                <!-- Submit / Cancel -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" onclick="closeImportModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-600/20 transition flex items-center gap-1.5">
                        <i data-lucide="upload" class="w-4 h-4"></i>
                        <span>Mulai Import Siswa</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
