<?php

namespace App\Http\Controllers;

use App\Models\Prospect;
use App\Models\School;
use App\Models\StudyProgram;
use App\Models\User;
use App\Services\StudentImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProspectController extends Controller
{
    /**
     * Display Prospects Lead CRM Pipeline.
     */
    public function index(Request $request): View
    {
        $query = Prospect::with(['school.district', 'marketingUser']);

        if ($request->filled('status')) {
            if ($request->status === 'pipeline') {
                $query->where('status', '!=', Prospect::STATUS_MAHASISWA);
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } else {
            // Default: exclude validated mahasiswa baru so they move to Menu Mahasiswa Baru
            $query->where('status', '!=', Prospect::STATUS_MAHASISWA);
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->school_id);
        }

        if ($request->filled('marketing_user_id')) {
            $query->where('marketing_user_id', $request->marketing_user_id);
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
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nim', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $prospects = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        $schools = School::orderBy('name')->get();
        $marketingUsers = User::active()->get();
        $studyPrograms = StudyProgram::where('status', 1)->get();

        // 5 Tahap Pipeline Status Counts
        $statusCounts = [
            'daftar_akun' => Prospect::where('status', Prospect::STATUS_DAFTAR_AKUN)->count(),
            'bayar_registrasi' => Prospect::where('status', Prospect::STATUS_BAYAR_REGISTRASI)->count(),
            'ujian_saringan' => Prospect::where('status', Prospect::STATUS_UJIAN_SARINGAN)->count(),
            'bayar_daftar_ulang' => Prospect::where('status', Prospect::STATUS_BAYAR_DAFTAR_ULANG)->count(),
            'berkas_lengkap' => Prospect::where('status', Prospect::STATUS_BERKAS_LENGKAP)->count(),
            'mahasiswa' => Prospect::where('status', Prospect::STATUS_MAHASISWA)->count(),
            'tidak_lanjut' => Prospect::where('status', Prospect::STATUS_TIDAK_LANJUT)->count(),
        ];

        return view('prospects.index', compact(
            'prospects',
            'schools',
            'marketingUsers',
            'studyPrograms',
            'statusCounts'
        ));
    }

    /**
     * Store a newly created lead/prospect.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'gender' => ['nullable', 'in:L,P'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'school_id' => ['required', 'exists:schools,id'],
            'graduation_year' => ['required', 'integer'],
            'interest_program' => ['nullable', 'string', 'max:100'],
            'study_time' => ['nullable', 'string', 'max:50'],
            'scholarship_type' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:daftar_akun,bayar_registrasi,ujian_saringan,bayar_daftar_ulang,berkas_lengkap,mahasiswa,tidak_lanjut'],
            'marketing_user_id' => ['nullable', 'exists:users,id'],
            'notes' => ['nullable', 'string'],
        ]);

        Prospect::create($validated);

        return redirect()->route('prospects.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Data prospek calon mahasiswa berhasil ditambahkan.',
            ]);
    }

    public function import(Request $request, StudentImportService $importService): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'student_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ], [
            'student_file.required' => 'Pilih file Excel yang akan diimpor.',
            'student_file.mimes' => 'File harus berformat .xlsx, .xls, atau .csv.',
            'student_file.max' => 'Ukuran file maksimal 10 MB.',
        ])->validate();

        try {
            $result = $importService->import($validated['student_file']);
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
            return redirect()->route('prospects.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'File Excel tidak dapat dibaca. Periksa format dan isi file, lalu coba lagi.',
                ]);
        }

        return redirect()->route('prospects.index')
            ->with('toast', [
                'type' => $result['status'] ? 'success' : 'error',
                'message' => $result['message'],
            ])
            ->with('import_errors', $result['errors'] ?? []);
    }

    public function downloadImportTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->fromArray([[
                'NIM',
                'Nama Mahasiswa',
                'NPSN',
                'Sekolah Asal',
                'Jenis Kelamin',
                'No HP',
                'Email',
                'Tahun Lulus',
                'Program Studi',
                'Waktu Kuliah',
                'Kategori Beasiswa',
            ]]);
            $sheet->getStyle('A1:K1')->getFont()->setBold(true);
            $sheet->getStyle('A1:K1')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE2E8F0');
            foreach (range('A', 'K') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-mahasiswa-ubsi.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Update prospect status in CRM pipeline.
     */
    public function updateStatus(Request $request, Prospect $prospect, \App\Services\SchoolScoringService $scoringService): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:daftar_akun,bayar_registrasi,ujian_saringan,bayar_daftar_ulang,berkas_lengkap,mahasiswa,tidak_lanjut'],
        ]);

        $newStatus = $validated['status'];
        $updateData = ['status' => $newStatus];

        if ($newStatus === Prospect::STATUS_MAHASISWA) {
            $updateData['validated_at'] = now();
            $updateData['validated_by'] = auth()->id();
        }

        $prospect->update($updateData);

        if ($newStatus === Prospect::STATUS_MAHASISWA) {
            try {
                $scoringService->recalculateAllSchoolScores();
            } catch (\Throwable) {}

            return redirect()->route('new-students.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => "Prospek {$prospect->name} telah tervalidasi dan dipindahkan ke Menu Mahasiswa Baru.",
                ]);
        }

        $statusLabel = Prospect::getStatusLabels()[$newStatus] ?? strtoupper($newStatus);

        return redirect()->route('prospects.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Status prospek {$prospect->name} diubah menjadi: {$statusLabel}",
            ]);
    }

    /**
     * Validate candidate to become official new student (Tahap 5 -> Mahasiswa Baru).
     */
    public function validateStudent(Request $request, Prospect $prospect, \App\Services\SchoolScoringService $scoringService): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:30', 'unique:prospects,nim,' . $prospect->id],
            'interest_program' => ['nullable', 'string', 'max:100'],
            'study_time' => ['nullable', 'string', 'max:50'],
            'scholarship_type' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'nim.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi untuk validasi.',
            'nim.unique' => 'NIM ini sudah terdaftar untuk mahasiswa lain.',
        ]);

        $prospect->update([
            'nim' => $validated['nim'],
            'interest_program' => $validated['interest_program'] ?? $prospect->interest_program,
            'study_time' => $validated['study_time'] ?? $prospect->study_time,
            'scholarship_type' => $validated['scholarship_type'] ?? $prospect->scholarship_type,
            'status' => Prospect::STATUS_MAHASISWA,
            'validated_at' => now(),
            'validated_by' => auth()->id(),
            'notes' => $validated['notes'] ? ($prospect->notes ? $prospect->notes . "\n" . $validated['notes'] : $validated['notes']) : $prospect->notes,
        ]);

        try {
            $scoringService->recalculateAllSchoolScores();
        } catch (\Throwable) {}

        try {
            if (class_exists(\App\Models\ActivityLog::class)) {
                \App\Models\ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'VALIDATE_NEW_STUDENT',
                    'description' => "Validasi Mahasiswa Baru: {$prospect->name} (NIM: {$validated['nim']}) dari {$prospect->school?->name}",
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable) {}

        return redirect()->route('new-students.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Selamat! {$prospect->name} telah berhasil divalidasi dan resmi menjadi Mahasiswa Baru. Data telah dipindahkan ke Menu Mahasiswa Baru.",
            ]);
    }

    public function destroy(Prospect $prospect): RedirectResponse
    {
        $name = $prospect->name;
        $prospect->delete();

        return redirect()->route('prospects.index')
            ->with('toast', ['type' => 'success', 'message' => "Prospek {$name} dihapus."]);
    }
}
