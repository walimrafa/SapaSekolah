<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\StudentQuestionnaire;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentQuestionnaireController extends Controller
{
    public static function questionSections(): array
    {
        return \App\Models\QuestionnaireQuestion::getActiveSections();
    }

    public static function allQuestions(): array
    {
        return \App\Models\QuestionnaireQuestion::allQuestions();
    }

    public function create(Request $request): View
    {
        $campuses = \App\Models\Campus::where('is_active', true)->orderBy('is_default', 'desc')->get();
        $requestedCampusId = $request->query('campus_id');
        $activeCampus = $requestedCampusId 
            ? \App\Models\Campus::find($requestedCampusId) 
            : \App\Services\AppContextService::getActiveCampus();

        if (!\App\Services\AppContextService::isPortalActive(\App\Services\AppContextService::PORTAL_STUDENT_QUESTIONNAIRE, $activeCampus?->id)) {
            return view('errors.portal-disabled', [
                'portalName' => 'Portal Kuisioner Siswa & Beasiswa (' . ($activeCampus?->name ?? 'UBSI') . ')',
            ]);
        }

        $schoolsQuery = School::with(['district', 'campus', 'schoolType']);
        if ($requestedCampusId && $activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }
        $schools = $schoolsQuery->orderBy('name')->get();

        $selectedEventId = $request->query('event_id') ?? $request->query('school_event_proposal_id');
        $selectedEvent = $selectedEventId ? \App\Models\SchoolEventProposal::find($selectedEventId) : null;
        $selectedSchoolId = $request->query('school_id') ?? ($selectedEvent ? $selectedEvent->school_id : null);

        return view('student-questionnaires.create', [
            'questionSections' => self::questionSections(),
            'campuses' => $campuses,
            'activeCampus' => $activeCampus,
            'schools' => $schools,
            'selectedSchoolId' => $selectedSchoolId,
            'selectedEventId' => $selectedEventId,
            'selectedEvent' => $selectedEvent,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $campusId = $request->input('campus_id') ?? $request->query('campus_id');
        if (!\App\Services\AppContextService::isPortalActive(\App\Services\AppContextService::PORTAL_STUDENT_QUESTIONNAIRE, $campusId)) {
            return back()->with('error', 'Portal Kuisioner Siswa untuk kampus ini saat ini sedang dinonaktifkan.');
        }

        $answerRules = [];
        foreach (array_keys(self::allQuestions()) as $key) {
            $answerRules["answers.{$key}"] = ['required', 'integer', 'between:1,5'];
        }

        $validated = $request->validate(array_merge([
            'school_id' => ['nullable'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'school_event_proposal_id' => ['nullable', 'exists:school_event_proposals,id'],
            'student_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'string', 'in:L,P,laki-laki,perempuan,Laki-laki,Perempuan'],
            'student_class' => ['required', 'string', 'max:50'],
            'student_major' => ['required', 'string', 'max:100'],
            'student_phone' => ['required', 'string', 'max:30'],
            'student_email' => ['required', 'email', 'max:150'],
            'student_social_media' => ['nullable', 'string', 'max:255'],
            'instagram_handle' => ['nullable', 'string', 'max:150'],
            'instagram_followers' => ['nullable', 'integer', 'min:0'],
            'tiktok_handle' => ['nullable', 'string', 'max:150'],
            'tiktok_followers' => ['nullable', 'integer', 'min:0'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => ['required', 'string', 'max:30'],
            'guardian_social_media' => ['nullable', 'string', 'max:255'],
            'scholarship_interest' => ['required', 'in:ya,tidak'],
        ], $answerRules));

        $schoolId = null;
        $school = null;
        $schoolName = trim((string) ($request->input('school_name') ?? ''));

        if (!empty($validated['school_id']) && $validated['school_id'] !== 'other') {
            $school = School::find($validated['school_id']);
            if ($school) {
                $schoolId = $school->id;
                $schoolName = $school->name;
            }
        }

        if (empty($schoolName) && empty($schoolId)) {
            return back()->withInput()->withErrors([
                'school_id' => 'Asal sekolah wajib dipilih atau diisi.',
            ]);
        }

        $eventProposalId = $validated['school_event_proposal_id'] ?? null;
        if ($eventProposalId) {
            $event = \App\Models\SchoolEventProposal::find($eventProposalId);
            if ($event) {
                $event->increment('prospects_acquired');
            }
        }

        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $targetCampusId = $school?->campus_id ?: ($request->input('campus_id') ?: ($activeCampus?->id ?? 1));

        $gender = !empty($validated['gender'])
            ? (strtoupper(substr($validated['gender'], 0, 1)) === 'P' ? 'P' : 'L')
            : null;

        $questionnaire = StudentQuestionnaire::create([
            'school_id' => $schoolId,
            'campus_id' => $targetCampusId,
            'academic_period_id' => $activePeriod?->id,
            'school_event_proposal_id' => $eventProposalId,
            'source' => $eventProposalId ? 'support_event' : 'kuisioner',
            'school_name' => $schoolName,
            'student_name' => $validated['student_name'],
            'gender' => $gender,
            'student_class' => $validated['student_class'],
            'student_major' => $validated['student_major'],
            'student_phone' => $validated['student_phone'],
            'student_email' => $validated['student_email'],
            'student_social_media' => $validated['student_social_media'] ?? null,
            'instagram_handle' => $validated['instagram_handle'] ?? $request->input('instagram_handle'),
            'instagram_followers' => !empty($validated['instagram_followers']) ? (int) $validated['instagram_followers'] : (int) ($request->input('instagram_followers') ?? 0),
            'tiktok_handle' => $validated['tiktok_handle'] ?? $request->input('tiktok_handle'),
            'tiktok_followers' => !empty($validated['tiktok_followers']) ? (int) $validated['tiktok_followers'] : (int) ($request->input('tiktok_followers') ?? 0),
            'guardian_name' => $validated['guardian_name'],
            'guardian_phone' => $validated['guardian_phone'],
            'guardian_social_media' => $validated['guardian_social_media'] ?? null,
            'scholarship_interest' => $validated['scholarship_interest'],
            'answers' => $validated['answers'],
        ]);

        \App\Services\ActivityLogService::log(
            'CREATE',
            'questionnaire',
            $questionnaire->id,
            "Pengisian Kuisioner Siswa Publik oleh {$validated['student_name']} ({$schoolName})",
            null,
            ['student_name' => $validated['student_name'], 'school' => $schoolName]
        );

        \App\Services\NotificationService::notifyQuestionnaireSubmitted($questionnaire);

        $redirectParams = $request->filled('campus_id') ? ['campus_id' => $request->campus_id] : [];
        return redirect()->route('student-questionnaire.create', $redirectParams)
            ->with('success_message', 'Terima kasih! Jawaban kuisioner Anda berhasil dikirim.');
    }

    public function index(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = StudentQuestionnaire::with('school.district');

        if ($activeCampus) {
            $query->whereHas('school', function ($sq) use ($activeCampus) {
                $sq->where('campus_id', $activeCampus->id);
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('student_email', 'like', "%{$search}%")
                    ->orWhere('student_phone', 'like', "%{$search}%")
                    ->orWhere('guardian_name', 'like', "%{$search}%")
                    ->orWhere('school_name', 'like', "%{$search}%")
                    ->orWhereHas('school', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->input('school_id'));
        }

        $schools = School::when($activeCampus, fn($q) => $q->where('campus_id', $activeCampus->id))
            ->with('district:id,name')
            ->orderBy('name')
            ->get();
        $studyPrograms = \App\Models\StudyProgram::where('status', 1)
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereNull('campus_id')))
            ->orderBy('name')
            ->get();

        $statsBaseQuery = StudentQuestionnaire::query()->whereNotNull('answers');
        if ($activeCampus) {
            $statsBaseQuery->whereHas('school', function ($sq) use ($activeCampus) {
                $sq->where('campus_id', $activeCampus->id);
            });
        }
        if ($activePeriod) {
            $statsBaseQuery->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }
        $statistics = $this->buildStatistics($statsBaseQuery->get(['student_class', 'student_major', 'scholarship_interest', 'answers']));

        return view('student-questionnaires.index', [
            'questionnaires' => $query->latest()->paginate(15)->withQueryString(),
            'questionSections' => self::questionSections(),
            'scaleLabels' => [
                1 => 'Sangat tidak setuju',
                2 => 'Tidak setuju',
                3 => 'Netral',
                4 => 'Setuju',
                5 => 'Sangat setuju',
            ],
            'statistics' => $statistics,
            'schools' => $schools,
            'studyPrograms' => $studyPrograms,
            'statusOptions' => StudentQuestionnaire::followupStatusOptions(),
        ]);
    }

    public function downloadExcel(?Request $request = null): StreamedResponse
    {
        $request = $request ?? request();
        $user = auth()->user();
        if ($user && !\App\Services\AppContextService::canSwitchCampus($user)) {
            $campusId = $user->campus_id ?? \App\Services\AppContextService::getActiveCampus()?->id;
        } else {
            $campusId = $request->filled('campus_id') && $request->campus_id !== 'ALL'
                ? $request->campus_id
                : \App\Services\AppContextService::getActiveCampus()?->id;
        }

        $activeCampus = ($campusId && $campusId !== 'ALL') ? \App\Models\Campus::find($campusId) : null;
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $questions = self::allQuestions();
        $excelQuery = StudentQuestionnaire::with('school')->latest();

        if ($activeCampus) {
            $excelQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
            });
        }

        if ($activePeriod) {
            $excelQuery->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }

        $responses = $excelQuery->get();

        return response()->streamDownload(function () use ($questions, $responses) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Responden');

            $headers = [
                'Waktu Kirim',
                'Nama Lengkap',
                'Jenis Kelamin',
                'Asal Sekolah',
                'Kelas',
                'Jurusan',
                'No. HP Siswa',
                'Email Siswa',
                'Akun Medsos Siswa',
                'Nama Wali',
                'No. HP Wali',
                'Akun Medsos Wali',
            ];
            foreach ($questions as $question) {
                $headers[] = $question;
            }
            $headers[] = 'Berminat Program Beasiswa UBSI';

            foreach ($headers as $index => $header) {
                $coordinate = Coordinate::stringFromColumnIndex($index + 1) . '1';
                $sheet->setCellValueExplicit($coordinate, $header, DataType::TYPE_STRING);
            }
            $headerRange = 'A1:' . $sheet->getHighestColumn() . '1';
            $sheet->getStyle($headerRange)->getFont()->setBold(true);
            $sheet->getStyle($headerRange)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');

            $rowNumber = 2;
            foreach ($responses as $response) {
                $values = [
                    $response->created_at->format('Y-m-d H:i:s'),
                    $response->student_name,
                    $response->gender_label,
                    $response->school_display_name,
                    $response->student_class,
                    $response->student_major,
                    $response->student_phone,
                    $response->student_email,
                    $response->student_social_media ?? '',
                    $response->guardian_name,
                    $response->guardian_phone,
                    $response->guardian_social_media ?? '',
                ];
                foreach (array_keys($questions) as $key) {
                    $values[] = $response->answers[$key] ?? '';
                }
                $values[] = $response->scholarship_interest === 'ya' ? 'Ya' : 'Tidak';
                $values = array_map([\App\Support\SecuritySanitizer::class, 'sanitizeSpreadsheetCell'], $values);

                foreach ($values as $index => $value) {
                    $type = is_int($value) ? DataType::TYPE_NUMERIC : DataType::TYPE_STRING;
                    $coordinate = Coordinate::stringFromColumnIndex($index + 1) . $rowNumber;
                    $sheet->setCellValueExplicit($coordinate, $value, $type);
                }
                $rowNumber++;
            }

            foreach (range(1, Coordinate::columnIndexFromString($sheet->getHighestColumn())) as $column) {
                $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            $statisticsSheet = $spreadsheet->createSheet();
            $statisticsSheet->setTitle('Statistik');
            $statisticsSheet->fromArray([
                ['Statistik Kuisioner Siswa'],
                ['Total responden', $responses->count()],
                ['Berminat beasiswa UBSI', $responses->where('scholarship_interest', 'ya')->count()],
                ['Tidak berminat beasiswa UBSI', $responses->where('scholarship_interest', 'tidak')->count()],
                [],
                ['Pernyataan', 'Rata-rata skor', 'Skor 1', 'Skor 2', 'Skor 3', 'Skor 4', 'Skor 5'],
            ]);

            $statistics = $this->buildStatistics($responses);
            $statisticsRow = 7;
            foreach ($statistics['questions'] as $question) {
                $statisticsSheet->fromArray([[
                    $question['text'],
                    $question['average'],
                    $question['scores'][1],
                    $question['scores'][2],
                    $question['scores'][3],
                    $question['scores'][4],
                    $question['scores'][5],
                ]], null, "A{$statisticsRow}");
                $statisticsRow++;
            }
            $statisticsSheet->getStyle('A1:G1')->getFont()->setBold(true);
            $statisticsSheet->getStyle('A6:G6')->getFont()->setBold(true);
            $statisticsSheet->getStyle('A6:G6')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
            foreach (range('A', 'G') as $column) {
                $statisticsSheet->getColumnDimension($column)->setAutoSize(true);
            }
            $statisticsSheet->freezePane('A7');

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'hasil-kuisioner-siswa.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function buildStatistics(?\Illuminate\Support\Collection $responses = null): array
    {
        if ($responses === null) {
            $responses = StudentQuestionnaire::query()
                ->whereNotNull('answers')
                ->get(['student_class', 'student_major', 'scholarship_interest', 'answers']);
        } else {
            $responses = $responses->filter(fn ($r) => !empty($r->answers));
        }

        $questions = [];
        $scoreTotals = array_fill_keys(range(1, 5), 0);
        foreach (self::allQuestions() as $key => $text) {
            $questions[$key] = [
                'text' => $text,
                'total' => 0,
                'scores' => array_fill_keys(range(1, 5), 0),
                'average' => null,
            ];
        }

        foreach ($responses as $response) {
            foreach ($questions as $key => &$question) {
                $score = $response->answers[$key] ?? null;
                if (is_numeric($score) && (int) $score >= 1 && (int) $score <= 5) {
                    $question['total'] += (int) $score;
                    $question['scores'][(int) $score]++;
                    $scoreTotals[(int) $score]++;
                }
            }
            unset($question);
        }

        foreach ($questions as &$question) {
            $answerCount = array_sum($question['scores']);
            $question['average'] = $answerCount > 0
                ? round($question['total'] / $answerCount, 2)
                : null;
            unset($question['total']);
        }
        unset($question);

        $classCounts = $responses->countBy('student_class')->sortDesc()->all();
        $majorCounts = $responses->countBy('student_major')->sortDesc()->all();

        return [
            'total' => $responses->count(),
            'scholarship' => [
                'ya' => $responses->where('scholarship_interest', 'ya')->count(),
                'tidak' => $responses->where('scholarship_interest', 'tidak')->count(),
            ],
            'classes' => $classCounts,
            'majors' => $majorCounts,
            'scores' => $scoreTotals,
            'sections' => $this->buildSectionStatistics($questions),
            'questions' => $questions,
        ];
    }

    private function buildSectionStatistics(array $questions): array
    {
        $sections = [];
        foreach (self::questionSections() as $title => $sectionQuestions) {
            $scores = array_filter(array_map(
                fn (string $key) => $questions[$key]['average'],
                array_keys($sectionQuestions)
            ), fn (?float $average) => $average !== null);
            $sections[$title] = count($scores) > 0
                ? round(array_sum($scores) / count($scores), 2)
                : null;
        }

        return $sections;
    }

    /**
     * Pastikan pengguna memiliki hak akses terhadap data siswa berdasarkan isolasi kampus.
     * Pengguna kampus cabang (misal UBSI Karawang / UBSI Cikampek) tidak boleh mengakses data kampus lain.
     */
    protected function authorizeCampusAccess(StudentQuestionnaire $sq): void
    {
        $user = auth()->user();
        if ($user && !\App\Services\AppContextService::canSwitchCampus($user)) {
            $userCampusId = $user->campus_id ?? \App\Services\AppContextService::getActiveCampus()?->id;
            $sqCampusId = $sq->campus_id ?: ($sq->school?->campus_id);
            if ($sqCampusId && $userCampusId && (int)$sqCampusId !== (int)$userCampusId) {
                abort(403, 'Akses ditolak. Data kuisioner siswa ini berada di luar wilayah kampus penugasan Anda.');
            }
        }
    }

    /**
     * Update student questionnaire from Hasil Kuisioner index.
     */
    public function update(Request $request, StudentQuestionnaire $studentQuestionnaire): RedirectResponse|JsonResponse
    {
        $this->authorizeCampusAccess($studentQuestionnaire);
        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'string', 'in:L,P,laki-laki,perempuan,Laki-laki,Perempuan'],
            'school_id' => ['nullable'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'student_class' => ['nullable', 'string', 'max:50'],
            'student_major' => ['nullable', 'string', 'max:100'],
            'student_phone' => ['required', 'string', 'max:30'],
            'student_email' => ['nullable', 'email', 'max:150'],
            'student_social_media' => ['nullable', 'string', 'max:255'],
            'instagram_handle' => ['nullable', 'string', 'max:150'],
            'instagram_followers' => ['nullable', 'integer', 'min:0'],
            'tiktok_handle' => ['nullable', 'string', 'max:150'],
            'tiktok_followers' => ['nullable', 'integer', 'min:0'],
            'is_kol' => ['nullable', 'boolean'],
            'kol_status' => ['nullable', 'string', 'max:50'],
            'kol_notes' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_social_media' => ['nullable', 'string', 'max:255'],
            'scholarship_interest' => ['required', 'in:ya,tidak'],
            'followup_status' => ['nullable', 'in:belum_dihubungi,no_respon,respon,berminat,daftar_aplikasi,closing'],
            'scholarship_notes' => ['nullable', 'string', 'max:500'],
            'college_interest_notes' => ['nullable', 'string', 'max:500'],
            'followup_notes' => ['nullable', 'string', 'max:1000'],
            'answers' => ['nullable', 'array'],
        ]);

        $schoolId = null;
        $school = null;
        $schoolName = trim((string) ($request->input('school_name') ?? ''));

        if (!empty($validated['school_id']) && $validated['school_id'] !== 'other') {
            $school = School::find($validated['school_id']);
            if ($school) {
                $schoolId = $school->id;
                $schoolName = $school->name;
            }
        }

        $dataToUpdate = [
            'student_name' => $validated['student_name'],
            'gender' => $request->filled('gender') ? (strtoupper(substr($request->gender, 0, 1)) === 'P' ? 'P' : 'L') : $studentQuestionnaire->gender,
            'school_id' => $schoolId,
            'school_name' => $schoolName ?: $studentQuestionnaire->school_name,
            'student_class' => $validated['student_class'] ?? null,
            'student_major' => $validated['student_major'] ?? null,
            'student_phone' => $validated['student_phone'],
            'student_email' => $validated['student_email'] ?? null,
            'student_social_media' => $validated['student_social_media'] ?? null,
            'instagram_handle' => $request->input('instagram_handle'),
            'instagram_followers' => (int) ($request->input('instagram_followers') ?? 0),
            'tiktok_handle' => $request->input('tiktok_handle'),
            'tiktok_followers' => (int) ($request->input('tiktok_followers') ?? 0),
            'is_kol' => $request->boolean('is_kol'),
            'kol_status' => $request->input('kol_status', StudentQuestionnaire::KOL_STATUS_KANDIDAT),
            'kol_notes' => $request->input('kol_notes'),
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'guardian_social_media' => $validated['guardian_social_media'] ?? null,
            'scholarship_interest' => $validated['scholarship_interest'],
            'scholarship_notes' => $validated['scholarship_notes'] ?? $studentQuestionnaire->scholarship_notes,
            'college_interest_notes' => $validated['college_interest_notes'] ?? $studentQuestionnaire->college_interest_notes,
        ];

        if ($request->has('instagram_handle')) {
            $dataToUpdate['instagram_handle'] = $request->input('instagram_handle');
        }
        if ($request->has('instagram_followers')) {
            $dataToUpdate['instagram_followers'] = (int) $request->input('instagram_followers');
        }
        if ($request->has('tiktok_handle')) {
            $dataToUpdate['tiktok_handle'] = $request->input('tiktok_handle');
        }
        if ($request->has('tiktok_followers')) {
            $dataToUpdate['tiktok_followers'] = (int) $request->input('tiktok_followers');
        }
        if ($request->has('is_kol')) {
            $dataToUpdate['is_kol'] = $request->boolean('is_kol');
        }
        if ($request->has('kol_status')) {
            $dataToUpdate['kol_status'] = $request->input('kol_status');
        }
        if ($request->has('kol_notes')) {
            $dataToUpdate['kol_notes'] = $request->input('kol_notes');
        }

        if ($request->has('answers') && is_array($request->answers)) {
            $existingAnswers = $studentQuestionnaire->answers ?? [];
            foreach ($request->answers as $qKey => $qScore) {
                if (is_numeric($qScore) && $qScore >= 1 && $qScore <= 5) {
                    $existingAnswers[$qKey] = (int) $qScore;
                }
            }
            $dataToUpdate['answers'] = $existingAnswers;
        }

        if (!empty($validated['followup_status'])) {
            $statusVal = ($validated['followup_status'] === 'closing') ? StudentQuestionnaire::STATUS_DAFTAR_APLIKASI : $validated['followup_status'];
            $dataToUpdate['followup_status'] = $statusVal;
        }

        if (array_key_exists('followup_notes', $validated)) {
            $dataToUpdate['followup_notes'] = $validated['followup_notes'];
        }

        $studentQuestionnaire->update($dataToUpdate);

        if (in_array($studentQuestionnaire->followup_status, [StudentQuestionnaire::STATUS_DAFTAR_APLIKASI, 'closing', 'daftar_aplikasi'])) {
            \App\Services\ProspectSyncService::syncStudentQuestionnaire($studentQuestionnaire->fresh());
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa {$studentQuestionnaire->student_name} berhasil diperbarui.",
                'student' => $studentQuestionnaire->fresh(),
            ]);
        }

        return redirect()->route('student-questionnaires.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data siswa {$studentQuestionnaire->student_name} berhasil diperbarui.",
            ]);
    }

    /**
     * Simpan data siswa & poin kuisioner langsung dari sistem (Internal Staff/Admin).
     */
    public function storeFromSystem(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'student_name' => ['required', 'string', 'max:150'],
            'gender' => ['nullable', 'string', 'in:L,P,laki-laki,perempuan,Laki-laki,Perempuan'],
            'school_id' => ['nullable'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'student_class' => ['nullable', 'string', 'max:50'],
            'student_major' => ['nullable', 'string', 'max:100'],
            'student_phone' => ['required', 'string', 'max:30'],
            'student_email' => ['nullable', 'email', 'max:150'],
            'student_social_media' => ['nullable', 'string', 'max:255'],
            'instagram_handle' => ['nullable', 'string', 'max:150'],
            'instagram_followers' => ['nullable', 'integer', 'min:0'],
            'tiktok_handle' => ['nullable', 'string', 'max:150'],
            'tiktok_followers' => ['nullable', 'integer', 'min:0'],
            'is_kol' => ['nullable', 'boolean'],
            'kol_status' => ['nullable', 'string', 'max:50'],
            'kol_notes' => ['nullable', 'string'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_phone' => ['nullable', 'string', 'max:30'],
            'guardian_social_media' => ['nullable', 'string', 'max:255'],
            'scholarship_interest' => ['required', 'in:ya,tidak'],
            'followup_status' => ['nullable', 'in:belum_dihubungi,no_respon,respon,berminat,daftar_aplikasi,closing'],
            'scholarship_notes' => ['nullable', 'string', 'max:500'],
            'college_interest_notes' => ['nullable', 'string', 'max:500'],
            'followup_notes' => ['nullable', 'string', 'max:1000'],
            'answers' => ['nullable', 'array'],
        ]);

        $schoolId = null;
        $school = null;
        $schoolName = trim((string) ($request->input('school_name') ?? ''));

        if (!empty($validated['school_id']) && $validated['school_id'] !== 'other') {
            $school = School::find($validated['school_id']);
            if ($school) {
                $schoolId = $school->id;
                $schoolName = $school->name;
            }
        }

        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $answers = [];
        if ($request->has('answers') && is_array($request->answers)) {
            foreach ($request->answers as $qKey => $qScore) {
                if (is_numeric($qScore) && $qScore >= 1 && $qScore <= 5) {
                    $answers[$qKey] = (int) $qScore;
                }
            }
        }

        $statusVal = $validated['followup_status'] ?? StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI;
        if ($statusVal === 'closing') {
            $statusVal = StudentQuestionnaire::STATUS_DAFTAR_APLIKASI;
        }

        $gender = !empty($validated['gender'])
            ? (strtoupper(substr($validated['gender'], 0, 1)) === 'P' ? 'P' : 'L')
            : null;

        $studentQuestionnaire = StudentQuestionnaire::create([
            'school_id' => $schoolId,
            'campus_id' => $school?->campus_id ?: ($activeCampus?->id ?? 1),
            'academic_period_id' => $activePeriod?->id,
            'school_name' => $schoolName ?: 'Sekolah Lain',
            'student_name' => $validated['student_name'],
            'gender' => $gender,
            'student_class' => $validated['student_class'] ?? null,
            'student_major' => $validated['student_major'] ?? null,
            'student_phone' => $validated['student_phone'],
            'student_email' => $validated['student_email'] ?? null,
            'student_social_media' => $validated['student_social_media'] ?? null,
            'instagram_handle' => $request->input('instagram_handle'),
            'instagram_followers' => (int) ($request->input('instagram_followers') ?? 0),
            'tiktok_handle' => $request->input('tiktok_handle'),
            'tiktok_followers' => (int) ($request->input('tiktok_followers') ?? 0),
            'is_kol' => $request->boolean('is_kol'),
            'kol_status' => $request->input('kol_status', StudentQuestionnaire::KOL_STATUS_KANDIDAT),
            'kol_notes' => $request->input('kol_notes'),
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'guardian_social_media' => $validated['guardian_social_media'] ?? null,
            'scholarship_interest' => $validated['scholarship_interest'],
            'scholarship_notes' => $validated['scholarship_notes'] ?? null,
            'college_interest_notes' => $validated['college_interest_notes'] ?? null,
            'followup_status' => $statusVal,
            'followup_notes' => $validated['followup_notes'] ?? null,
            'source' => 'sistem',
            'answers' => $answers,
        ]);

        \App\Services\ActivityLogService::log(
            'CREATE',
            'student_questionnaire',
            $studentQuestionnaire->id,
            "Input Kuisioner Siswa melalui Sistem: {$studentQuestionnaire->student_name} ({$studentQuestionnaire->school_display_name})",
            null,
            ['student_name' => $studentQuestionnaire->student_name, 'school' => $studentQuestionnaire->school_display_name]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data & kuisioner siswa {$studentQuestionnaire->student_name} berhasil disimpan.",
                'student' => $studentQuestionnaire,
            ]);
        }

        return redirect()->route('student-questionnaires.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data & kuisioner siswa {$studentQuestionnaire->student_name} berhasil diinput ke sistem!",
            ]);
    }

    /**
     * Delete student questionnaire from Hasil Kuisioner index.
     * Hanya diizinkan untuk Admin, Pimpinan, dan Koordinator Marketing.
     */
    public function destroy(Request $request, StudentQuestionnaire $studentQuestionnaire)
    {
        $this->authorizeCampusAccess($studentQuestionnaire);

        $user = auth()->user();
        if (!$user || !$user->canPlotFollowUps()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Akses ditolak. Anda tidak berhak menghapus data kuisioner siswa.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak berhak menghapus data kuisioner siswa.');
        }

        $name = $studentQuestionnaire->student_name;
        $studentQuestionnaire->delete();

        \App\Services\ActivityLogService::log(
            'DELETE',
            'questionnaire',
            $studentQuestionnaire->id,
            "Menghapus data kuisioner siswa: {$name}",
            ['student_name' => $name]
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Data kuisioner siswa {$name} berhasil dihapus.",
            ]);
        }

        return redirect()->route('student-questionnaires.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data kuisioner siswa {$name} berhasil dihapus.",
            ]);
    }
}
