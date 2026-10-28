<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\StudentQuestionnaire;
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
    private const QUESTION_SECTIONS = [
        'A. Motivasi' => [
            'motivation_job' => 'Saya ingin kuliah untuk meningkatkan peluang memperoleh pekerjaan yang baik.',
            'motivation_interest' => 'Saya ingin memilih program studi yang sesuai dengan minat dan bakat saya.',
            'motivation_parent' => 'Harapan orang tua menjadi alasan penting bagi saya untuk kuliah.',
        ],
        'B. Pencarian informasi' => [
            'information_social_media' => 'Saya mencari informasi kampus melalui media sosial sebelum menentukan pilihan.',
            'information_testimonials' => 'Testimoni mahasiswa atau alumni memengaruhi ketertarikan saya terhadap kampus.',
            'information_comparison' => 'Saya membandingkan informasi dari beberapa kampus sebelum mengambil keputusan.',
        ],
        'C. Pengaruh sosial' => [
            'social_parent' => 'Pendapat orang tua sangat memengaruhi pilihan kampus saya.',
            'social_friends' => 'Pilihan teman memengaruhi pertimbangan saya dalam memilih kampus.',
            'social_teacher' => 'Rekomendasi guru atau alumni meningkatkan kepercayaan saya terhadap kampus.',
        ],
        'D. Preferensi kampus' => [
            'campus_cost' => 'Biaya kuliah yang terjangkau merupakan faktor utama bagi saya.',
            'campus_reputation' => 'Akreditasi dan reputasi kampus sangat penting dalam keputusan saya.',
            'campus_career' => 'Peluang kerja, magang, dan keterampilan praktis menjadi pertimbangan utama saya.',
            'campus_location' => 'Lokasi kampus dan kemudahan transportasi memengaruhi pilihan saya.',
        ],
        'E. Niat dan hambatan' => [
            'intention_ubsi' => 'Saya mempertimbangkan UBSI sebagai salah satu pilihan kuliah.',
            'intention_information' => 'Saya berniat mencari informasi PMB UBSI lebih lanjut.',
            'barrier_cost' => 'Kendala biaya dapat membuat saya menunda atau membatalkan rencana kuliah.',
        ],
    ];

    public function create(Request $request): View
    {
        $schools = School::with('district')->orderBy('name')->get();
        $selectedEventId = $request->query('event_id') ?? $request->query('school_event_proposal_id');
        $selectedEvent = $selectedEventId ? \App\Models\SchoolEventProposal::find($selectedEventId) : null;
        $selectedSchoolId = $request->query('school_id') ?? ($selectedEvent ? $selectedEvent->school_id : null);

        return view('student-questionnaires.create', [
            'questionSections' => self::QUESTION_SECTIONS,
            'schools' => $schools,
            'selectedSchoolId' => $selectedSchoolId,
            'selectedEventId' => $selectedEventId,
            'selectedEvent' => $selectedEvent,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $answerRules = [];
        foreach (array_keys(self::allQuestions()) as $key) {
            $answerRules["answers.{$key}"] = ['required', 'integer', 'between:1,5'];
        }

        $validated = $request->validate(array_merge([
            'school_id' => ['nullable'],
            'school_name' => ['nullable', 'string', 'max:150'],
            'school_event_proposal_id' => ['nullable', 'exists:school_event_proposals,id'],
            'student_name' => ['required', 'string', 'max:150'],
            'student_class' => ['required', 'string', 'max:50'],
            'student_major' => ['required', 'string', 'max:100'],
            'student_phone' => ['required', 'string', 'max:30'],
            'student_email' => ['required', 'email', 'max:150'],
            'student_social_media' => ['nullable', 'string', 'max:255'],
            'guardian_name' => ['required', 'string', 'max:150'],
            'guardian_phone' => ['required', 'string', 'max:30'],
            'guardian_social_media' => ['nullable', 'string', 'max:255'],
            'scholarship_interest' => ['required', 'in:ya,tidak'],
        ], $answerRules));

        $schoolId = null;
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

        StudentQuestionnaire::create([
            'school_id' => $schoolId,
            'school_event_proposal_id' => $eventProposalId,
            'source' => $eventProposalId ? 'support_event' : 'kuisioner',
            'school_name' => $schoolName,
            'student_name' => $validated['student_name'],
            'student_class' => $validated['student_class'],
            'student_major' => $validated['student_major'],
            'student_phone' => $validated['student_phone'],
            'student_email' => $validated['student_email'],
            'student_social_media' => $validated['student_social_media'] ?? null,
            'guardian_name' => $validated['guardian_name'],
            'guardian_phone' => $validated['guardian_phone'],
            'guardian_social_media' => $validated['guardian_social_media'] ?? null,
            'scholarship_interest' => $validated['scholarship_interest'],
            'answers' => $validated['answers'],
        ]);

        return redirect()->route('student-questionnaire.create')
            ->with('success_message', 'Terima kasih! Jawaban kuisioner Anda berhasil dikirim.');
    }

    public function index(Request $request): View
    {
        $query = StudentQuestionnaire::with('school.district');

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

        $schools = School::orderBy('name')->get(['id', 'name']);
        $statistics = $this->buildStatistics();

        return view('student-questionnaires.index', [
            'questionnaires' => $query->latest()->paginate(15)->withQueryString(),
            'questionSections' => self::QUESTION_SECTIONS,
            'scaleLabels' => [
                1 => 'Sangat tidak setuju',
                2 => 'Tidak setuju',
                3 => 'Netral',
                4 => 'Setuju',
                5 => 'Sangat setuju',
            ],
            'statistics' => $statistics,
            'schools' => $schools,
        ]);
    }

    public function downloadExcel(): StreamedResponse
    {
        $questions = self::allQuestions();
        $responses = StudentQuestionnaire::with('school')->latest()->get();

        return response()->streamDownload(function () use ($questions, $responses) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Responden');

            $headers = [
                'Waktu Kirim',
                'Nama Lengkap',
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
        $responses ??= StudentQuestionnaire::query()
            ->get(['student_class', 'student_major', 'scholarship_interest', 'answers']);

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
        foreach (self::QUESTION_SECTIONS as $title => $sectionQuestions) {
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

    private static function allQuestions(): array
    {
        $questions = [];
        foreach (self::QUESTION_SECTIONS as $section) {
            $questions = array_merge($questions, $section);
        }

        return $questions;
    }
}
