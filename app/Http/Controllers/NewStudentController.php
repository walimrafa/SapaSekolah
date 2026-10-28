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
        $query = Prospect::with(['school.district.regency', 'marketingUser', 'validatedBy'])
            ->where('status', Prospect::STATUS_MAHASISWA);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
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

        // Summary Stats
        $totalStudents = Prospect::where('status', Prospect::STATUS_MAHASISWA)->count();
        $totalSchools = School::whereHas('prospects', fn($q) => $q->where('status', Prospect::STATUS_MAHASISWA))->count();
        $genderL = Prospect::where('status', Prospect::STATUS_MAHASISWA)->where('gender', 'L')->count();
        $genderP = Prospect::where('status', Prospect::STATUS_MAHASISWA)->where('gender', 'P')->count();
        $totalBeasiswa = Prospect::where('status', Prospect::STATUS_MAHASISWA)->scholarship()->count();
        $totalNonBeasiswa = Prospect::where('status', Prospect::STATUS_MAHASISWA)->nonScholarship()->count();

        // Reference Lists
        $schools = School::orderBy('name')->get();
        $studyPrograms = StudyProgram::where('status', 1)->get();
        $availableStudyTimes = StudyProgram::getStudyTimeOptions();
        $graduationYears = Prospect::where('status', Prospect::STATUS_MAHASISWA)
            ->whereNotNull('graduation_year')
            ->distinct()
            ->orderBy('graduation_year', 'desc')
            ->pluck('graduation_year');

        // Ready for validation count (Tahap 5) for badge notification
        $readyForValidationCount = Prospect::where('status', Prospect::STATUS_BERKAS_LENGKAP)->count();

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
        $query = Prospect::with(['school.district.regency', 'marketingUser', 'validatedBy'])
            ->where('status', Prospect::STATUS_MAHASISWA);

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
            $sheet->getStyle('A1:N1')->getFont()->setBold(true);
            $sheet->getStyle('A1:N1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E293B');
            $sheet->getStyle('A1:N1')->getFont()->getColor()->setARGB('FFFFFFFF');

            $rowIdx = 2;
            foreach ($students as $idx => $s) {
                $sheet->fromArray([[
                    $idx + 1,
                    $s->nim ?: '-',
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
                ]], null, 'A' . $rowIdx);
                $rowIdx++;
            }

            foreach (range('A', 'N') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'data-mahasiswa-baru-ubsi-' . date('Ymd-His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
