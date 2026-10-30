<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\School;
use App\Models\SchoolType;
use App\Services\SchoolStudentIntakeImportService;
use App\Services\SchoolScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PriorityController extends Controller
{
    public function __construct(
        protected SchoolScoringService $scoringService
    ) {}

    /**
     * Display School Priority Ranking Leaderboard & Smart Recommendation.
     */
    public function index(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();

        // Automatically ensure scores are calculated using active campus criteria
        $this->scoringService->recalculateAllSchoolScores($activeCampus?->id);

        $query = School::with(['schoolType', 'accreditation', 'district', 'village', 'primaryContact', 'latestVisit'])
            ->withCount(['visits', 'prospects as individual_ubsi_students_count' => function ($q) {
                $q->where('status', 'mahasiswa');
            }])
            ->withSum('studentIntakes as reported_ubsi_students_count', 'student_count');

        // Isolasi Kampus: Hanya tampilkan peringkat prioritas sekolah kampus aktif
        if ($activeCampus) {
            $query->where('campus_id', $activeCampus->id);
        }

        // Filters
        if ($request->filled('district_id')) {
            $query->where('district_id', $request->district_id);
        }

        if ($request->filled('priority_level')) {
            $query->where('priority_level', strtoupper($request->priority_level));
        }

        if ($request->filled('school_type_id')) {
            $query->where('school_type_id', $request->school_type_id);
        }

        if ($request->filled('status')) {
            $query->where('status', strtolower($request->status));
        }

        // Leaderboard order by potential score desc
        $schools = $query->orderBy('potential_score', 'desc')
            ->orderBy('grade_12_count', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Top Recommendation Cards: Terisolasi ketat sesuai kampus aktif
        $todayRecommendedQuery = School::with(['district', 'schoolType', 'primaryContact'])
            ->withCount(['prospects as individual_ubsi_students_count' => function ($q) {
                $q->where('status', 'mahasiswa');
            }])
            ->withSum('studentIntakes as reported_ubsi_students_count', 'student_count')
            ->where('priority_level', 'A');

        if ($activeCampus) {
            $todayRecommendedQuery->where('campus_id', $activeCampus->id);
        }

        $todayRecommended = $todayRecommendedQuery
            ->orderBy('potential_score', 'desc')
            ->limit(3)
            ->get();

        // Hanya tampilkan kecamatan yang memiliki sekolah di kampus aktif
        $districtsQuery = District::orderBy('name');
        if ($activeCampus) {
            $districtsQuery->whereHas('schools', fn($q) => $q->where('campus_id', $activeCampus->id));
        }
        $districts = $districtsQuery->get();

        $schoolTypes = SchoolType::all();

        $ubsiCampusAddress = $activeCampus ? ($activeCampus->address ?? SchoolScoringService::UBSI_ADDRESS) : SchoolScoringService::UBSI_ADDRESS;

        return view('priorities.index', compact('schools', 'todayRecommended', 'districts', 'schoolTypes', 'ubsiCampusAddress', 'activeCampus'));
    }

    public function importStudentIntakes(Request $request, SchoolStudentIntakeImportService $importService): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'intake_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'intake_file.required' => 'Pilih file Excel data mahasiswa baru.',
            'intake_file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'intake_file.max' => 'Ukuran file maksimal 10 MB.',
        ])->validate();

        try {
            $result = $importService->import($validated['intake_file']);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
            return redirect()->route('priorities.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'File Excel tidak dapat dibaca. Periksa format dan isi file, lalu coba lagi.',
                ]);
        }

        return redirect()->route('priorities.index')
            ->with('toast', [
                'type' => $result['status'] ? 'success' : 'error',
                'message' => $result['message'],
            ])
            ->with('intake_import_errors', $result['errors'] ?? []);
    }

    public function downloadStudentIntakeTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([[
                'NPSN Sekolah',
                'Nama Sekolah Asal',
                'Periode PMB',
                'Jumlah Mahasiswa Baru',
            ]]);
            $schools = School::orderBy('name')->get(['npsn', 'name']);
            $rows = $schools->map(fn (School $school) => [
                (string) $school->npsn,
                $school->name,
                '',
                '',
            ])->all();

            if ($rows !== []) {
                $sheet->fromArray($rows, null, 'A2');
                $sheet->getStyle('A2:A' . ($schools->count() + 1))
                    ->getNumberFormat()
                    ->setFormatCode('@');
                $sheet->setAutoFilter('A1:D' . ($schools->count() + 1));
                $sheet->getStyle('D2:D' . ($schools->count() + 1))
                    ->getNumberFormat()
                    ->setFormatCode('0');
            }

            $sheet->freezePane('A2');
            $sheet->getStyle('A1:D1')->getFont()->setBold(true);
            $sheet->getStyle('A1:D1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
            $sheet->getColumnDimension('A')->setWidth(18);
            $sheet->getColumnDimension('B')->setWidth(38);
            $sheet->getColumnDimension('C')->setWidth(24);
            $sheet->getColumnDimension('D')->setWidth(30);

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-mahasiswa-baru-per-sekolah.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Trigger manual recalculation of school priority scores.
     */
    public function recalculate(): RedirectResponse
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $this->scoringService->recalculateAllSchoolScores($activeCampus?->id);

        $campusName = $activeCampus ? "Kampus {$activeCampus->name}" : 'Semua Kampus Operasional';

        return redirect()->route('priorities.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Skor & Prioritas sekolah berhasil dihitung ulang berdasarkan lokasi {$campusName}.",
            ]);
    }
}
