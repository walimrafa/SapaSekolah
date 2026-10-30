<x-app-layout title="Hasil Kuisioner Siswa">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div class="min-w-0">
            <h1 class="text-xl font-extrabold text-slate-900 tracking-tight">Hasil Kuisioner Siswa</h1>
            <p class="text-xs text-slate-500 mt-0.5">Data responden dan jawaban kuisioner minat kuliah UBSI.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" onclick="openCreateStudentModal()" class="px-4 py-2.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-xl text-xs shadow-md flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Input Kuisioner Siswa</span>
            </button>
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

    <!-- Alert / Flash Messages -->
    @if(session('toast'))
        <div class="p-4 mb-6 rounded-2xl {{ (session('toast')['type'] ?? '') === 'error' ? 'bg-rose-50 border border-rose-200 text-rose-800' : ((session('toast')['type'] ?? '') === 'warning' ? 'bg-amber-50 border border-amber-200 text-amber-800' : 'bg-emerald-50 border border-emerald-200 text-emerald-800') }} text-sm font-semibold flex items-center gap-3 shadow-sm">
            <i data-lucide="{{ (session('toast')['type'] ?? '') === 'error' ? 'alert-triangle' : ((session('toast')['type'] ?? '') === 'warning' ? 'alert-circle' : 'check-circle') }}" class="w-5 h-5 {{ (session('toast')['type'] ?? '') === 'error' ? 'text-rose-600' : ((session('toast')['type'] ?? '') === 'warning' ? 'text-amber-600' : 'text-emerald-600') }} shrink-0"></i>
            <span>{{ session('toast')['message'] ?? 'Data berhasil diproses.' }}</span>
        </div>
    @endif

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
        <select name="school_id" id="filter_school_id" class="select2-school-filter px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold" onchange="this.form.submit()">
            <option value="">-- Semua Sekolah (Ketik / Cari) --</option>
            @foreach($schools as $sch)
                <option value="{{ $sch->id }}" {{ (string)request('school_id') === (string)$sch->id ? 'selected' : '' }}>{{ $sch->name }} {{ $sch->district ? '(' . $sch->district->name . ')' : '' }}</option>
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
                        <th class="py-3 px-4 text-center min-w-36">Tindak Lanjut & Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($questionnaires as $response)
                        <tr class="align-top hover:bg-slate-50" id="student-row-{{ $response->id }}">
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
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('student-follow-ups.index', ['search' => $response->student_phone ?: $response->student_name]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs shadow-xs transition"
                                       title="Follow Up Siswa">
                                        <i data-lucide="phone-call" class="w-3.5 h-3.5"></i>
                                        <span>Follow Up</span>
                                    </a>

                                    <button type="button"
                                            onclick="openEditStudentModal({{ $response->id }}, 'answers')"
                                            class="p-1.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-bold transition"
                                            title="Isi / Perbarui Point Kuisioner (1-5)">
                                        <i data-lucide="clipboard-check" class="w-4 h-4 text-purple-600"></i>
                                    </button>

                                    <button type="button"
                                            onclick="openEditStudentModal({{ $response->id }}, 'profile')"
                                            class="p-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 font-bold transition"
                                            title="Edit Profil & Data Siswa">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </button>

                                    <button type="button"
                                            onclick="confirmDeleteStudent({{ $response->id }}, '{{ addslashes($response->student_name) }}')"
                                            class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold transition"
                                            title="Hapus Data Siswa">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-10 px-4 text-center text-slate-500">
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

            // Edit & Input Student Logic
            function switchStudentModalTab(tab) {
                const tabProfile = document.getElementById('tabContentProfile');
                const tabAnswers = document.getElementById('tabContentAnswers');
                const btnProfile = document.getElementById('modalTabBtnProfile');
                const btnAnswers = document.getElementById('modalTabBtnAnswers');

                if (tab === 'answers') {
                    tabProfile.classList.add('hidden');
                    tabAnswers.classList.remove('hidden');
                    btnProfile.classList.remove('border-brand-600', 'text-brand-600');
                    btnProfile.classList.add('border-transparent', 'text-slate-500');
                    btnAnswers.classList.remove('border-transparent', 'text-slate-500');
                    btnAnswers.classList.add('border-brand-600', 'text-brand-600');
                } else {
                    tabAnswers.classList.add('hidden');
                    tabProfile.classList.remove('hidden');
                    btnAnswers.classList.remove('border-brand-600', 'text-brand-600');
                    btnAnswers.classList.add('border-transparent', 'text-slate-500');
                    btnProfile.classList.remove('border-transparent', 'text-slate-500');
                    btnProfile.classList.add('border-brand-600', 'text-brand-600');
                }
                if (window.lucide) { window.lucide.createIcons(); }
            }

            function setAllQuestionAnswers(score) {
                document.querySelectorAll('input[type="radio"][name^="answers["][value="' + score + '"]').forEach(r => {
                    r.checked = true;
                });
            }

            function openCreateStudentModal() {
                const form = document.getElementById('editStudentForm');
                form.reset();
                document.getElementById('editStudentModalTitle').textContent = 'Input Data & Kuisioner Siswa Baru';
                document.getElementById('editStudentModalDesc').textContent = 'Input profil calon mahasiswa dan isi 16 poin kuisioner secara sistem';
                document.getElementById('editStudentMethod').value = 'POST';
                document.getElementById('editStudentId').value = '';
                form.action = '{{ route('student-questionnaires.store-system') }}';
                
                document.querySelectorAll('input[type="radio"][name^="answers["]').forEach(r => r.checked = false);
                setAllQuestionAnswers(4); // Preset default "Setuju"

                switchStudentModalTab('profile');
                document.getElementById('editStudentModal').classList.remove('hidden');
                if (window.lucide) { window.lucide.createIcons(); }
            }

            async function openEditStudentModal(studentId, targetTab = 'profile') {
                const modal = document.getElementById('editStudentModal');
                modal.classList.remove('hidden');
                if (window.lucide) { window.lucide.createIcons(); }

                document.getElementById('editStudentModalTitle').textContent = 'Edit Data & Poin Kuisioner Siswa';
                document.getElementById('editStudentModalDesc').textContent = 'Perbarui profil, kontak, asal sekolah, dan 16 poin kuisioner siswa';
                document.getElementById('editStudentMethod').value = 'PUT';
                document.getElementById('editStudentId').value = studentId;
                document.getElementById('editStudentForm').action = '/hasil-kuisioner/' + studentId;

                document.querySelectorAll('input[type="radio"][name^="answers["]').forEach(r => r.checked = false);

                try {
                    const res = await fetch('/student-follow-ups/' + studentId + '/edit-data', {
                        headers: { 'Accept': 'application/json' }
                    });
                    const json = await res.json();
                    if (res.ok && json.success) {
                        const s = json.student;
                        document.getElementById('editStudentName').value = s.student_name || '';
                        document.getElementById('editStudentPhone').value = s.student_phone || '';
                        document.getElementById('editStudentEmail').value = s.student_email || '';
                        document.getElementById('editStudentClass').value = s.student_class || '';
                        document.getElementById('editStudentMajor').value = s.student_major || '';
                        document.getElementById('editStudentSocial').value = s.student_social_media || '';
                        document.getElementById('editGuardianName').value = s.guardian_name || '';
                        document.getElementById('editGuardianPhone').value = s.guardian_phone || '';
                        document.getElementById('editGuardianSocial').value = s.guardian_social_media || '';
                        document.getElementById('editScholarshipNotes').value = s.scholarship_notes || '';
                        document.getElementById('editCollegeNotes').value = s.college_interest_notes || '';
                        document.getElementById('editFollowupNotes').value = s.followup_notes || '';

                        // Scholarship radio
                        if (s.scholarship_interest === 'tidak') {
                            document.getElementById('editScholarshipTidak').checked = true;
                        } else {
                            document.getElementById('editScholarshipYa').checked = true;
                        }

                        // Followup status
                        if (document.getElementById('editFollowupStatus')) {
                            document.getElementById('editFollowupStatus').value = s.followup_status || 'belum_dihubungi';
                        }

                        // School selection
                        const schoolSelect = document.getElementById('editStudentSchoolId');
                        const customSchoolWrapper = document.getElementById('editCustomSchoolWrapper');
                        const customSchoolInput = document.getElementById('editStudentCustomSchool');

                        if (s.school_id) {
                            schoolSelect.value = s.school_id;
                            customSchoolWrapper.classList.add('hidden');
                            customSchoolInput.value = '';
                        } else if (s.school_name) {
                            schoolSelect.value = 'other';
                            customSchoolWrapper.classList.remove('hidden');
                            customSchoolInput.value = s.school_name;
                        } else {
                            schoolSelect.value = '';
                            customSchoolWrapper.classList.add('hidden');
                            customSchoolInput.value = '';
                        }

                        // Populate answers
                        if (s.answers && typeof s.answers === 'object') {
                            for (const [key, val] of Object.entries(s.answers)) {
                                const radio = document.querySelector('input[type="radio"][name="answers[' + key + ']"][value="' + val + '"]');
                                if (radio) {
                                    radio.checked = true;
                                }
                            }
                        }
                    }
                } catch (err) {
                    console.error(err);
                    alert('Gagal mengambil data siswa.');
                }

                switchStudentModalTab(targetTab);
            }

            function closeEditStudentModal() {
                document.getElementById('editStudentModal').classList.add('hidden');
            }

            function toggleEditCustomSchool(select) {
                const wrapper = document.getElementById('editCustomSchoolWrapper');
                if (select.value === 'other') {
                    wrapper.classList.remove('hidden');
                    document.getElementById('editStudentCustomSchool').focus();
                } else {
                    wrapper.classList.add('hidden');
                }
            }

            async function handleEditStudentSubmit(e) {
                e.preventDefault();
                const form = document.getElementById('editStudentForm');
                const submitBtn = document.getElementById('btnSubmitEditStudent');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="animate-spin inline-block mr-1">⌛</span> Menyimpan...';

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        closeEditStudentModal();
                        window.location.reload();
                    } else {
                        alert(data.message || 'Gagal menyimpan perubahan siswa.');
                    }
                } catch (err) {
                    console.error(err);
                    form.submit();
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }

            // Delete Student Logic
            function confirmDeleteStudent(studentId, studentName) {
                document.getElementById('deleteStudentNameText').textContent = studentName;
                document.getElementById('deleteStudentForm').action = '/hasil-kuisioner/' + studentId;
                document.getElementById('deleteStudentModal').classList.remove('hidden');
                if (window.lucide) { window.lucide.createIcons(); }
            }

            function closeDeleteStudentModal() {
                document.getElementById('deleteStudentModal').classList.add('hidden');
            }
        </script>
    @endpush

    <!-- MODAL INPUT / EDIT DATA & POIN KUISIONER SISWA -->
    <div id="editStudentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-3xl max-h-[92vh] flex flex-col overflow-hidden transform transition-all animate-in fade-in zoom-in-95">
            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-950 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-amber-500 text-slate-950 font-bold shadow-md">
                        <i data-lucide="user-cog" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 id="editStudentModalTitle" class="text-base font-extrabold tracking-tight">Edit Data Siswa</h3>
                        <p id="editStudentModalDesc" class="text-xs text-amber-400 mt-0.5">Perbarui profil, kontak, asal sekolah, dan 16 poin kuisioner siswa</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditStudentModal()" class="text-slate-400 hover:text-white p-1 rounded-lg transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Tabs: Profil vs Poin Kuisioner -->
            <div class="flex border-b border-slate-200 bg-slate-50 px-6 pt-3 gap-2 shrink-0">
                <button type="button" onclick="switchStudentModalTab('profile')" id="modalTabBtnProfile"
                    class="pb-2.5 px-3 text-xs font-bold border-b-2 border-brand-600 text-brand-600 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="user" class="w-4 h-4"></i>
                    <span>1. Profil &amp; Asal Sekolah</span>
                </button>
                <button type="button" onclick="switchStudentModalTab('answers')" id="modalTabBtnAnswers"
                    class="pb-2.5 px-3 text-xs font-bold border-b-2 border-transparent text-slate-500 hover:text-slate-800 transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="clipboard-check" class="w-4 h-4 text-purple-600"></i>
                    <span>2. Poin Kuisioner (16 Pertanyaan, Skala 1-5)</span>
                </button>
            </div>

            <!-- Modal Body Form -->
            <form id="editStudentForm" method="POST" action="" onsubmit="handleEditStudentSubmit(event)" class="flex-1 overflow-y-auto p-6 space-y-4 text-xs">
                @csrf
                <input type="hidden" name="_method" id="editStudentMethod" value="PUT">
                <input type="hidden" id="editStudentId" value="">

                <!-- TAB 1: PROFIL & KONTAK -->
                <div id="tabContentProfile" class="space-y-4">
                    <!-- Nama Siswa -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Lengkap Siswa <span class="text-rose-500">*</span></label>
                        <input type="text" name="student_name" id="editStudentName" required class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-brand-500 focus:border-brand-500 font-semibold text-slate-900">
                    </div>

                    <!-- Asal Sekolah -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Asal Sekolah</label>
                        <select name="school_id" id="editStudentSchoolId" onchange="toggleEditCustomSchool(this)" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs focus:ring-brand-500 focus:border-brand-500 bg-white">
                            <option value="">-- Pilih Asal Sekolah Terdaftar --</option>
                            @foreach($schools as $sch)
                                <option value="{{ $sch->id }}">{{ $sch->name }} {{ $sch->district ? '(' . $sch->district->name . ')' : '' }}</option>
                            @endforeach
                            <option value="other">Sekolah Lainnya / Tulis Manual...</option>
                        </select>
                        <div id="editCustomSchoolWrapper" class="hidden mt-2">
                            <input type="text" name="school_name" id="editStudentCustomSchool" placeholder="Ketik nama asal sekolah..." class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-slate-50">
                        </div>
                    </div>

                    <!-- Kelas & Jurusan -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Kelas</label>
                            <input type="text" name="student_class" id="editStudentClass" placeholder="Misal: XII, XII IPA 1" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Jurusan</label>
                            <input type="text" name="student_major" id="editStudentMajor" placeholder="Misal: IPA, Rekayasa Perangkat Lunak" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>

                    <!-- Kontak Siswa: WA & Email -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">No. WhatsApp Siswa <span class="text-rose-500">*</span></label>
                            <input type="text" name="student_phone" id="editStudentPhone" required placeholder="08xxxxxxxxxx" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs font-semibold">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Email Siswa</label>
                            <input type="email" name="student_email" id="editStudentEmail" placeholder="nama@email.com" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>

                    <!-- Medsos Siswa -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Media Sosial Siswa (Instagram/TikTok)</label>
                        <input type="text" name="student_social_media" id="editStudentSocial" placeholder="@username" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Data Orang Tua / Wali -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <span class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">Data Orang Tua / Wali Siswa</span>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">Nama Orang Tua / Wali</label>
                                <input type="text" name="guardian_name" id="editGuardianName" placeholder="Nama wali..." class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-white">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-600 mb-1">No. WhatsApp Wali</label>
                                <input type="text" name="guardian_phone" id="editGuardianPhone" placeholder="08xxxxxxxxxx" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-white">
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-600 mb-1">Media Sosial Wali (Opsional)</label>
                            <input type="text" name="guardian_social_media" id="editGuardianSocial" placeholder="@username / akun wali" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-white">
                        </div>
                    </div>

                    <!-- Minat Beasiswa & Catatan Beasiswa -->
                    <div class="p-3.5 rounded-2xl bg-emerald-50/50 border border-emerald-200/80 space-y-3">
                        <div>
                            <label class="block font-bold text-emerald-900 uppercase tracking-wider mb-1.5">Minat Beasiswa UBSI <span class="text-rose-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-emerald-300 bg-white cursor-pointer font-bold text-emerald-800">
                                    <input type="radio" name="scholarship_interest" id="editScholarshipYa" value="ya" class="text-emerald-600 focus:ring-emerald-500">
                                    <span>Ya (Berminat Beasiswa)</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-xl border border-slate-300 bg-white cursor-pointer font-bold text-slate-700">
                                    <input type="radio" name="scholarship_interest" id="editScholarshipTidak" value="tidak" class="text-slate-600 focus:ring-slate-500">
                                    <span>Tidak Berminat</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block font-bold text-emerald-900 mb-1">Keterangan / Jenis Beasiswa</label>
                            <input type="text" name="scholarship_notes" id="editScholarshipNotes" placeholder="Misal: Beasiswa KIP-Kuliah / Non Beasiswa" class="w-full py-2 px-3 rounded-xl border border-emerald-300 text-xs bg-white">
                        </div>
                    </div>

                    <!-- Minat Program Studi -->
                    <div>
                        <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Minat Program Studi</label>
                        <input type="text" name="college_interest_notes" id="editCollegeNotes" placeholder="Pilihan program studi minat siswa..." class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                    </div>

                    <!-- Status Follow Up & Catatan -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Status Kriteria Follow Up</label>
                            <select name="followup_status" id="editFollowupStatus" class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs bg-white font-semibold">
                                <option value="belum_dihubungi">Belum Dihubungi</option>
                                <option value="no_respon">No Respon</option>
                                <option value="respon">Respon</option>
                                <option value="berminat">Berminat</option>
                                <option value="daftar_aplikasi">Daftar Aplikasi</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan Follow Up Terakhir</label>
                            <input type="text" name="followup_notes" id="editFollowupNotes" placeholder="Poin catatan..." class="w-full py-2 px-3 rounded-xl border border-slate-300 text-xs">
                        </div>
                    </div>
                </div>

                <!-- TAB 2: POIN KUISIONER (16 PERTANYAAN, SKALA 1-5) -->
                <div id="tabContentAnswers" class="space-y-4 hidden">
                    <!-- Quick Presets -->
                    <div class="p-3 rounded-2xl bg-gradient-to-r from-purple-50 via-indigo-50 to-blue-50 border border-purple-200 flex flex-wrap items-center justify-between gap-2 shadow-xs">
                        <div class="flex items-center gap-1.5">
                            <i data-lucide="zap" class="w-4 h-4 text-purple-600"></i>
                            <span class="text-xs font-extrabold text-purple-900">Isi Poin Cepat:</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5">
                            <button type="button" onclick="setAllQuestionAnswers(5)" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] shadow-xs cursor-pointer transition">
                                Semua 5 (Sangat Minat)
                            </button>
                            <button type="button" onclick="setAllQuestionAnswers(4)" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-[11px] shadow-xs cursor-pointer transition">
                                Semua 4 (Minat / Setuju)
                            </button>
                            <button type="button" onclick="setAllQuestionAnswers(3)" class="px-2.5 py-1 rounded-lg bg-slate-600 hover:bg-slate-700 text-white font-bold text-[11px] shadow-xs cursor-pointer transition">
                                Semua 3 (Netral)
                            </button>
                        </div>
                    </div>

                    <!-- Scale Description -->
                    <div class="p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-900 flex items-center justify-between">
                        <span>Skala Nilai: <strong>1</strong> = Sangat Tidak Setuju &bull; <strong>2</strong> = Tidak Setuju &bull; <strong>3</strong> = Netral &bull; <strong>4</strong> = Setuju &bull; <strong>5</strong> = Sangat Setuju</span>
                        <span class="font-bold text-amber-800 shrink-0">16 Indikator</span>
                    </div>

                    <!-- Dynamic Sections -->
                    @foreach($questionSections as $sectionTitle => $questions)
                        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                            <h4 class="font-black text-slate-800 text-xs tracking-tight border-b border-slate-200 pb-1.5 flex items-center gap-1.5 text-brand-700">
                                <i data-lucide="check-circle" class="w-3.5 h-3.5 text-brand-600"></i>
                                <span>{{ $sectionTitle }}</span>
                            </h4>
                            <div class="space-y-2.5">
                                @foreach($questions as $key => $question)
                                    <div class="bg-white p-3 rounded-xl border border-slate-200 hover:border-brand-200 transition space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-xs font-semibold text-slate-800 leading-snug">{{ $question }}</p>
                                        </div>
                                        <div class="grid grid-cols-5 gap-1.5 text-center text-xs">
                                            @foreach([1 => '1 (STS)', 2 => '2 (TS)', 3 => '3 (N)', 4 => '4 (S)', 5 => '5 (SS)'] as $val => $lbl)
                                                <label class="flex flex-col items-center gap-0.5 p-1.5 rounded-lg border border-slate-200 hover:border-brand-400 hover:bg-brand-50/50 cursor-pointer transition">
                                                    <input type="radio" name="answers[{{ $key }}]" value="{{ $val }}" class="text-brand-600 focus:ring-brand-500 q-radio-{{ $key }}">
                                                    <span class="font-bold text-[10px] text-slate-700">{{ $lbl }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                    <span class="text-[11px] text-slate-500">Seluruh data &amp; skor akan tersimpan langsung ke sistem.</span>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="closeEditStudentModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                            Batal
                        </button>
                        <button type="submit" id="btnSubmitEditStudent" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-xs shadow-md transition flex items-center gap-1.5">
                            <i data-lucide="check" class="w-4 h-4"></i>
                            <span>Simpan Data &amp; Poin Kuisioner</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS DATA SISWA -->
    <div id="deleteStudentModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs hidden">
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-2xl w-full max-w-md overflow-hidden transform transition-all animate-in fade-in zoom-in-95">
            <div class="p-6 text-center space-y-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold text-slate-900">Hapus Data Siswa</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Apakah Anda yakin ingin menghapus data siswa <strong id="deleteStudentNameText" class="text-rose-600">Nama Siswa</strong>?
                    </p>
                    <p class="text-[11px] text-rose-600/90 mt-1 bg-rose-50 p-2 rounded-xl border border-rose-100">
                        ⚠️ Seluruh riwayat interaksi follow up terkait siswa ini juga akan dihapus permanen.
                    </p>
                </div>
                <form id="deleteStudentForm" method="POST" action="" class="flex items-center justify-center gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" onclick="closeDeleteStudentModal()" class="flex-1 py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition">
                        Batal
                    </button>
                    <button type="submit" id="btnSubmitDeleteStudent" class="flex-1 py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition flex items-center justify-center gap-1.5">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Ya, Hapus Data</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

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
