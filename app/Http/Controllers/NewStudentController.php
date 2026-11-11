<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\School;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\SchoolScoringService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NewStudentController extends Controller
{
    /**
     * Display directory of official validated new students (Mahasiswa Baru).
     */
    public function index(Request $request): View
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = Prospect::with(['school.district.regency', 'marketingUser', 'validatedBy'])
            ->where('status', Prospect::STATUS_MAHASISWA);

        if ($activeCampus) {
            $query->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhere(function ($sub) use ($activeCampus) {
                      $sub->whereNull('campus_id')
                          ->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                  });
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('student_class', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('interest_program')) {
            $query->where('interest_program', $request->interest_program);
        }

        if ($request->filled('study_time')) {
            $query->where('study_time', $request->study_time);
        }

        if ($request->filled('scholarship')) {
            if ($request->scholarship === 'beasiswa') {
                $query->scholarship();
            } elseif ($request->scholarship === 'non_beasiswa') {
                $query->nonScholarship();
            } else {
                $query->where('scholarship_type', $request->scholarship);
            }
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('graduation_year')) {
            $query->where('graduation_year', $request->graduation_year);
        }

        $students = $query->orderBy('validated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Summary Stats (Filtered by Active Campus & Period)
        $baseStudentQuery = Prospect::where('status', Prospect::STATUS_MAHASISWA);
        if ($activeCampus) {
            $baseStudentQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhere(function ($sub) use ($activeCampus) {
                      $sub->whereNull('campus_id')
                          ->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                  });
            });
        }
        if ($activePeriod) {
            $baseStudentQuery->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }

        $totalStudents = (clone $baseStudentQuery)->count();
        $totalSchools = School::when($activeCampus, fn($q) => $q->where('campus_id', $activeCampus->id))
            ->whereHas('prospects', function ($q) use ($activeCampus, $activePeriod) {
                $q->where('status', Prospect::STATUS_MAHASISWA);
                if ($activeCampus) $q->where('campus_id', $activeCampus->id);
                if ($activePeriod) $q->where('academic_period_id', $activePeriod->id);
            })->count();
        $genderL = (clone $baseStudentQuery)->where('gender', 'L')->count();
        $genderP = (clone $baseStudentQuery)->where('gender', 'P')->count();
        $totalBeasiswa = (clone $baseStudentQuery)->scholarship()->count();
        $totalNonBeasiswa = (clone $baseStudentQuery)->nonScholarship()->count();

        // Reference Lists
        $schools = School::when($activeCampus, fn($q) => $q->where('campus_id', $activeCampus->id))
            ->with('district:id,name')
            ->orderBy('name')
            ->get();
        $studyPrograms = StudyProgram::where('status', 1)
            ->when($activeCampus, fn($q) => $q->where(fn($sub) => $sub->where('campus_id', $activeCampus->id)->orWhereNull('campus_id')))
            ->orderBy('name')
            ->get();
        $availableStudyTimes = StudyProgram::getStudyTimeOptions();
        $graduationYears = (clone $baseStudentQuery)
            ->whereNotNull('graduation_year')
            ->distinct()
            ->orderBy('graduation_year', 'desc')
            ->pluck('graduation_year');

        // Ready for validation count (Tahap 5) for badge notification
        $readyQuery = Prospect::where('status', Prospect::STATUS_BERKAS_LENGKAP);
        if ($activeCampus) {
            $readyQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhere(function ($sub) use ($activeCampus) {
                      $sub->whereNull('campus_id')
                          ->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                  });
            });
        }
        if ($activePeriod) {
            $readyQuery->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }
        $readyForValidationCount = $readyQuery->count();

        return view('new-students.index', compact(
            'students',
            'totalStudents',
            'totalSchools',
            'genderL',
            'genderP',
            'totalBeasiswa',
            'totalNonBeasiswa',
            'schools',
            'studyPrograms',
            'availableStudyTimes',
            'graduationYears',
            'readyForValidationCount'
        ));
    }

    /**
     * Rollback/Cancel student validation (move back to Stage 5 in Prospect CRM).
     */
    public function unvalidate(Prospect $prospect, SchoolScoringService $scoringService): RedirectResponse
    {
        $name = $prospect->name;
        $prospect->update([
            'status' => Prospect::STATUS_BERKAS_LENGKAP,
            'validated_at' => null,
            'validated_by' => null,
        ]);

        try {
            $scoringService->recalculateAllSchoolScores();
        } catch (\Throwable) {}

        try {
            if (class_exists(\App\Models\ActivityLog::class)) {
                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'UNVALIDATE_STUDENT',
                    'description' => "Membatalkan validasi mahasiswa baru: {$name} (dikembalikan ke antrean Tahap 5 Prospek)",
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable) {}

        return redirect()->route('new-students.index')
            ->with('toast', [
                'type' => 'info',
                'message' => "Validasi {$name} telah dibatalkan. Calon mahasiswa kembali ke antrean Tahap 5 di menu Data Prospek.",
            ]);
    }

    /**
     * Export validated new students to Excel spreadsheet.
     */
    public function export(Request $request): StreamedResponse
    {
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();

        $query = Prospect::with(['school.district.regency', 'marketingUser', 'validatedBy'])
            ->where('status', Prospect::STATUS_MAHASISWA);

        if ($activeCampus) {
            $query->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)
                  ->orWhere(function ($sub) use ($activeCampus) {
                      $sub->whereNull('campus_id')
                          ->whereHas('school', fn($sq) => $sq->where('campus_id', $activeCampus->id));
                  });
            });
        }

        if ($activePeriod) {
            $query->where(function ($q) use ($activePeriod) {
                $q->where('academic_period_id', $activePeriod->id)
                  ->orWhereNull('academic_period_id');
            });
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }
        if ($request->filled('interest_program')) {
            $query->where('interest_program', $request->interest_program);
        }
        if ($request->filled('study_time')) {
            $query->where('study_time', $request->study_time);
        }
        if ($request->filled('scholarship')) {
            if ($request->scholarship === 'beasiswa') {
                $query->scholarship();
            } elseif ($request->scholarship === 'non_beasiswa') {
                $query->nonScholarship();
            } else {
                $query->where('scholarship_type', $request->scholarship);
            }
        }
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('student_class', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('name', 'asc')->get();

        return response()->streamDownload(function () use ($students) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Mahasiswa Baru UBSI');

            // Header Row
            $headers = [
                'No',
                'NIM',
                'Kelas',
                'Nama Lengkap',
                'L/P',
                'No WhatsApp',
                'Email',
                'Sekolah Asal',
                'Kecamatan Sekolah',
                'Program Studi',
                'Waktu Kuliah',
                'Kategori Program / Beasiswa',
                'Tahun Lulus',
                'Tanggal Divalidasi',
                'Status',
            ];
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle('A1:O1')->getFont()->setBold(true);
            $sheet->getStyle('A1:O1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E293B');
            $sheet->getStyle('A1:O1')->getFont()->getColor()->setARGB('FFFFFFFF');

            $rowIdx = 2;
            foreach ($students as $idx => $s) {
                $sheet->fromArray([\App\Support\SecuritySanitizer::sanitizeSpreadsheetRow([
                    $idx + 1,
                    $s->nim ?: '-',
                    $s->student_class ?: '-',
                    $s->name,
                    $s->gender ?: '-',
                    $s->phone ?: '-',
                    $s->email ?: '-',
                    $s->school?->name ?: '-',
                    $s->school?->district?->name ?: '-',
                    $s->interest_program ?: '-',
                    $s->study_time_label ?: '-',
                    $s->scholarship_badge['label'] ?? ($s->scholarship_type ?: 'Non Beasiswa'),
                    $s->graduation_year ?: '-',
                    $s->validated_at ? $s->validated_at->format('d/m/Y H:i') : '-',
                    'Mahasiswa Baru Resmi',
                ])], null, 'A' . $rowIdx);
                $rowIdx++;
            }

            foreach (range('A', 'O') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'data-mahasiswa-baru-ubsi-' . date('Ymd-His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
