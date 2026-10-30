<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\User;
use App\Models\VisitPlan;
use App\Services\GoogleDriveService;
use App\Services\WhatsAppNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SchoolVisitController extends Controller
{
    /**
     * Display a listing of field visits.
     */
    public function index(Request $request): View
    {
        $query = SchoolVisit::with(['school.district', 'school.schoolType', 'user', 'visitPlan']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('school', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        \App\Services\AppContextService::applyContextFilters($query);

        $visits = $query->orderBy('visit_date', 'desc')->paginate(15)->withQueryString();

        $activeCampus = \App\Services\AppContextService::getActiveCampus();

        $schoolsQuery = School::select('id', 'name', 'district_id')
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            });
        if ($activeCampus) {
            $schoolsQuery->where('campus_id', $activeCampus->id);
        }
        $schools = $schoolsQuery->orderBy('name')->get();
        
        $marketingUsersQuery = User::active()->select('id', 'name', 'role_id', 'campus_id');
        if ($activeCampus) {
            $marketingUsersQuery->where(function ($q) use ($activeCampus) {
                $q->where('campus_id', $activeCampus->id)->orWhereNull('campus_id');
            });
        }
        $marketingUsers = $marketingUsersQuery->get();

        $visitPlansQuery = VisitPlan::where('status', 'planned')->with('school:id,name');
        if ($activeCampus) {
            $visitPlansQuery->where('campus_id', $activeCampus->id);
        }
        $visitPlans = $visitPlansQuery->get();

        return view('visits.index', compact('visits', 'schools', 'marketingUsers', 'visitPlans'));
    }

    /**
     * Show form page for recording a new field visit.
     */
    public function create(Request $request): View
    {
        $districts = \App\Models\District::has('schools')
            ->withCount('schools')
            ->orderBy('name')
            ->get();

        $schools = School::with(['district', 'schoolType'])
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            })
            ->orderBy('name')
            ->get();
        
        $marketingUsers = User::active()->get();

        // Active planned visit plans
        $visitPlans = VisitPlan::where('status', 'planned')
            ->with(['school.district', 'school.schoolType', 'user'])
            ->orderBy('planned_date', 'asc')
            ->get();

        $initialPlanId = $request->query('plan_id');
        $initialSchoolId = $request->query('school_id');

        return view('visits.create', compact('districts', 'schools', 'marketingUsers', 'visitPlans', 'initialPlanId', 'initialSchoolId'));
    }

    /**
     * Store a newly recorded field visit with socialization details.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_plan_id' => ['nullable', 'exists:visit_plans,id'],
            'visit_date' => ['required', 'date'],
            'headmaster_name' => ['nullable', 'string', 'max:150'], // Nama Kepala Sekolah
            'recipient_name' => ['required', 'string', 'max:150'], // 1. Nama Penerima Beserta Gelarnya
            'recipient_position' => ['required', 'string', 'max:100'], // 2. Jabatan
            'recipient_phone' => ['nullable', 'string', 'max:30'], // No HP / WhatsApp Penerima
            'grade_12_class_count' => ['required', 'integer', 'min:1'], // 3. Jumlah Kelas 12
            'target_month_year' => ['required', 'string', 'max:50'], // 4. Target Bulan dan Tahun Sosialisasinya
            'target_date' => ['nullable', 'date'], // 5. Target Tanggal Sosialisasinya (Opsional)
            'target_time' => ['required', 'string', 'max:20'], // 6. Target Waktu Sosialisasinya
            'students_per_class' => ['required', 'integer', 'min:1'], // 7. Jumlah Siswa Per Kelasnya
            'socialization_method' => ['required', 'string', 'max:100'], // 8. Metode Sosialisasi
            'other_activity_name' => ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'], // Input jika pilih Lainnya
            'purpose' => ['nullable', 'string', 'max:255'],
            'result' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'photo' => [
                'nullable',
                'file',
                'max:51200', // 50MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'rar', 'zip', 'doc', 'docx', '7z'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format berkas harus berupa Gambar (JPG/PNG), Dokumen (PDF/DOCX), atau Arsip (RAR/ZIP).');
                    }
                },
            ],
            'notes' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
            'status' => ['required', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'],
        ]);

        $school = School::findOrFail($validated['school_id']);
        $this->ensureCheckInWithinSchoolRadius(
            $school,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        );

        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        // Auto upload photo and sync to Google Drive
        $photo = $request->file('photo');
        unset($validated['photo']);
        if ($photo) {
            $gdriveResult = GoogleDriveService::uploadDocumentation(
                $photo,
                $school->name,
                $validated['visit_date']
            );
            $validated['photo'] = $gdriveResult['stored_path'];
            $validated['google_drive_folder_url'] = $gdriveResult['drive_folder_url'];
            $validated['google_drive_file_url'] = $gdriveResult['drive_file_url'];
        } else {
            $validated['google_drive_folder_url'] = GoogleDriveService::buildFolderUrlForSchool(
                $school->name,
                $validated['visit_date']
            );
        }

        // Auto match active visit plan if not explicitly supplied
        if (empty($validated['visit_plan_id'])) {
            $matchedPlan = VisitPlan::where('school_id', $validated['school_id'])
                ->where('status', 'planned')
                ->where(function($q) use ($validated) {
                    $q->where('user_id', $validated['user_id'])
                      ->orWhereDate('planned_date', $validated['visit_date']);
                })
                ->first();
            if ($matchedPlan) {
                $validated['visit_plan_id'] = $matchedPlan->id;
            }
        }

        // Set campus & academic period context
        $activeCampus = \App\Services\AppContextService::getActiveCampus();
        $activePeriod = \App\Services\AppContextService::getActivePeriod();
        $validated['campus_id'] = $school->campus_id ?: ($activeCampus?->id);
        $validated['academic_period_id'] = $activePeriod?->id;

        $visit = SchoolVisit::create($validated);

        if (!empty($validated['headmaster_name']) && empty($school->headmaster_name)) {
            $school->update(['headmaster_name' => $validated['headmaster_name']]);
        }

        // If connected to a visit plan, mark plan as completed so it moves out of active plans
        if (!empty($validated['visit_plan_id'])) {
            VisitPlan::where('id', $validated['visit_plan_id'])->update(['status' => 'completed']);
        }

        // Integrasi Notifikasi WA & Google Calendar ke Petugas Bertugas berdasarkan User Management
        try {
            app(WhatsAppNotificationService::class)->notifySchoolVisit($visit);
        } catch (\Throwable $e) {
            Log::warning('Failed auto WA & Calendar notification for visit: ' . $e->getMessage());
        }

        // Otomatis masukkan ke data Follow Up untuk konfirmasi FIX jadwal sosialisasi
        try {
            app(\App\Services\FollowUpSyncService::class)->syncVisit($visit);
        } catch (\Throwable $e) {
            Log::warning('Failed auto sync visit to follow up: ' . $e->getMessage());
        }

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Hasil Kunjungan Lapangan ke {$visit->school->name} berhasil dicatat, notifikasi WA & kalender terkirim, serta otomatis tersinkron ke Follow Up!",
            ]);
    }

    /**
     * Show form page for editing an existing field visit.
     */
    public function edit(SchoolVisit $visit): View
    {
        $visit->load(['school.district', 'user']);

        $districts = \App\Models\District::has('schools')
            ->withCount('schools')
            ->orderBy('name')
            ->get();

        $schools = School::with(['district', 'schoolType'])
            ->whereHas('district', function($q) {
                $q->whereNotIn('name', \App\Models\District::EXCLUDED_DISTRICTS);
            })
            ->orderBy('name')
            ->get();

        $marketingUsers = User::active()->get();

        return view('visits.edit', compact('visit', 'districts', 'schools', 'marketingUsers'));
    }

    /**
     * Update an existing field visit record.
     */
    public function update(Request $request, SchoolVisit $visit): RedirectResponse
    {
        $validated = $request->validate([
            'school_id' => ['required', 'exists:schools,id'],
            'user_id' => ['required', 'exists:users,id'],
            'visit_date' => ['required', 'date'],
            'headmaster_name' => ['nullable', 'string', 'max:150'],
            'recipient_name' => ['required', 'string', 'max:150'],
            'recipient_position' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['nullable', 'string', 'max:30'],
            'grade_12_class_count' => ['required', 'integer', 'min:1'],
            'target_month_year' => ['required', 'string', 'max:50'],
            'target_date' => ['nullable', 'date'],
            'target_time' => ['required', 'string', 'max:20'],
            'students_per_class' => ['required', 'integer', 'min:1'],
            'socialization_method' => ['required', 'string', 'max:100'],
            'other_activity_name' => ['nullable', 'required_if:socialization_method,Lainnya', 'string', 'max:200'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'photo' => [
                'nullable',
                'file',
                'max:51200', // 50MB
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'pdf', 'rar', 'zip', 'doc', 'docx', '7z'];
                    if (!in_array($ext, $allowed)) {
                        $fail('Format berkas harus berupa Gambar (JPG/PNG), Dokumen (PDF/DOCX), atau Arsip (RAR/ZIP).');
                    }
                },
            ],
            'notes' => ['nullable', 'string'],
            'status' => ['required', 'in:berhasil,tidak_berhasil,tidak_bertemu,pending'],
        ]);

        $schoolChanged = (int) $validated['school_id'] !== (int) $visit->school_id;
        if ($schoolChanged && !isset($validated['latitude'], $validated['longitude'])) {
            throw ValidationException::withMessages([
                'latitude' => 'Ambil ulang GPS check-in saat mengubah sekolah kunjungan.',
            ]);
        }

        if (isset($validated['latitude'], $validated['longitude'])) {
            $school = School::findOrFail($validated['school_id']);
            $this->ensureCheckInWithinSchoolRadius(
                $school,
                (float) $validated['latitude'],
                (float) $validated['longitude']
            );
        }

        $validated['contact_person'] = $validated['recipient_name'] . ' (' . $validated['recipient_position'] . ')';

        $photo = $request->file('photo');
        unset($validated['photo']);
        if ($photo) {
            $school = School::find($validated['school_id']);
            $schoolName = $school ? $school->name : ($visit->school?->name ?? 'Sekolah');
            $gdriveResult = GoogleDriveService::uploadDocumentation(
                $photo,
                $schoolName,
                $validated['visit_date']
            );
            if ($visit->photo) {
                Storage::disk('public')->delete($visit->photo);
            }
            $validated['photo'] = $gdriveResult['stored_path'];
            $validated['google_drive_folder_url'] = $gdriveResult['drive_folder_url'];
            $validated['google_drive_file_url'] = $gdriveResult['drive_file_url'];
        }

        $visit->update($validated);

        if (!empty($validated['headmaster_name'])) {
            $school = School::find($validated['school_id']);
            if ($school && empty($school->headmaster_name)) {
                $school->update(['headmaster_name' => $validated['headmaster_name']]);
            }
        }

        // Sync to follow up
        try {
            app(\App\Services\FollowUpSyncService::class)->syncVisit($visit);
        } catch (\Throwable $e) {
            Log::warning('Failed sync visit update to follow up: ' . $e->getMessage());
        }

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data kunjungan ke {$visit->school->name} berhasil diperbarui.",
            ]);
    }

    /**
     * Download Excel rekap data kunjungan sekolah beserta link Google Drive masing-masing sekolah.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = SchoolVisit::with(['school.district', 'school.schoolType', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('school', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('date')) {
            $query->whereDate('visit_date', $request->date);
        }

        $visits = $query->orderBy('visit_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Kunjungan');

        // Header Title
        $sheet->setCellValue('A1', 'REKAPITULASI DATA KUNJUNGAN SEKOLAH & LINK GOOGLE DRIVE');
        $sheet->mergeCells('A1:S1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true)->getColor()->setRGB('0B023E');

        $sheet->setCellValue('A2', 'SAPA Sekolah UBSI • Tanggal Unduh: ' . now()->translatedFormat('d F Y H:i') . ' WIB • Total Data: ' . $visits->count() . ' Kunjungan');
        $sheet->mergeCells('A2:S2');
        $sheet->getStyle('A2')->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('64748B');

        $headers = [
            'No',
            'Tanggal Kunjungan',
            'Nama Sekolah',
            'NPSN',
            'Kecamatan',
            'Bentuk Sekolah',
            'Marketing PIC',
            'No. HP Petugas (User)',
            'Email Petugas (User)',
            'Kepala Sekolah',
            'PIC Penerima Sekolah',
            'Jabatan PIC',
            'No. HP/WA PIC',
            'Target Sosialisasi',
            'Estimasi Siswa Kls 12',
            'Metode Kegiatan',
            'Status Kunjungan',
            'Link Google Drive Dokumentasi',
            'Catatan Kunjungan',
        ];

        $headerRow = 4;
        foreach ($headers as $colIdx => $h) {
            $colLetter = Coordinate::stringFromColumnIndex($colIdx + 1);
            $sheet->setCellValue("{$colLetter}{$headerRow}", $h);
        }

        $lastColLetter = Coordinate::stringFromColumnIndex(count($headers));
        $headerRange = "A4:{$lastColLetter}4";
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0B023E');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $rowNum = 5;
        foreach ($visits as $idx => $v) {
            $school = $v->school;
            $user = $v->user;
            $totalSiswa = ($v->grade_12_class_count && $v->students_per_class) ? ($v->grade_12_class_count * $v->students_per_class) : 0;
            $targetStr = ($v->target_date ? $v->target_date->translatedFormat('d M Y') : $v->target_month_year) . ($v->target_time ? ' (' . $v->target_time . ')' : '');

            $driveFolderUrl = $v->drive_folder_url;

            $sheet->setCellValue("A{$rowNum}", $idx + 1);
            $sheet->setCellValue("B{$rowNum}", $v->visit_date ? $v->visit_date->format('Y-m-d') : '-');
            $sheet->setCellValue("C{$rowNum}", $school?->name ?? '-');
            $sheet->setCellValueExplicit("D{$rowNum}", $school?->npsn ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("E{$rowNum}", $school?->district?->name ?? '-');
            $sheet->setCellValue("F{$rowNum}", $school?->schoolType?->name ?? '-');
            $sheet->setCellValue("G{$rowNum}", $user?->name ?? '-');
            $sheet->setCellValueExplicit("H{$rowNum}", $user?->phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("I{$rowNum}", $user?->email ?? '-');
            $sheet->setCellValue("J{$rowNum}", $school?->headmaster_name ?? ($v->headmaster_name ?? '-'));
            $sheet->setCellValue("K{$rowNum}", $v->recipient_name ?? '-');
            $sheet->setCellValue("L{$rowNum}", $v->recipient_position ?? '-');
            $sheet->setCellValueExplicit("M{$rowNum}", $v->recipient_phone ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue("N{$rowNum}", $targetStr);
            $sheet->setCellValue("O{$rowNum}", $totalSiswa > 0 ? "{$totalSiswa} Siswa ({$v->grade_12_class_count} kls)" : '-');
            $sheet->setCellValue("P{$rowNum}", $v->socialization_method ?? '-');
            $sheet->setCellValue("Q{$rowNum}", strtoupper($v->status));

            // Column R: Google Drive Folder Link (Hyperlink)
            $sheet->setCellValue("R{$rowNum}", $driveFolderUrl);
            $sheet->getCell("R{$rowNum}")->getHyperlink()->setUrl($driveFolderUrl);
            $driveFont = $sheet->getStyle("R{$rowNum}")->getFont();
            $driveFont->getColor()->setRGB('0B023E');
            $driveFont->setUnderline(true);

            $sheet->setCellValue("S{$rowNum}", $v->notes ?? '-');

            $rowNum++;
        }

        // Auto size columns
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($col))->setAutoSize(true);
        }

        $filename = 'Data_Kunjungan_Sekolah_UBSI_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Remove a field visit record.
     */
    public function destroy(SchoolVisit $visit): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || (!$user->isAdmin() && !$user->isPimpinan() && !$user->isKoordinator() && $visit->user_id !== $user->id)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki wewenang untuk menghapus rekaman kunjungan ini.');
        }

        $schoolName = $visit->school ? $visit->school->name : 'Sekolah';
        $visit->delete();

        \App\Services\ActivityLogService::log(
            'DELETE',
            'visit',
            $visit->id,
            "Menghapus data kunjungan sekolah: {$schoolName} (Tanggal: " . ($visit->visit_date?->format('d/m/Y') ?? '-') . ")",
            ['school' => $schoolName, 'visit_date' => $visit->visit_date]
        );

        return redirect()->route('visits.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Data kunjungan ke {$schoolName} berhasil dihapus.",
            ]);
    }

    private function ensureCheckInWithinSchoolRadius(School $school, float $latitude, float $longitude): void
    {
        if ($school->latitude === null || $school->longitude === null) {
            throw ValidationException::withMessages([
                'school_id' => 'Sekolah belum memiliki koordinat. Lengkapi koordinat sekolah sebelum check-in.',
            ]);
        }

        $earthRadiusMeters = 6371000;
        $lat1 = deg2rad((float) $school->latitude);
        $lat2 = deg2rad($latitude);
        $deltaLat = $lat2 - $lat1;
        $deltaLng = deg2rad($longitude - (float) $school->longitude);
        $a = sin($deltaLat / 2) ** 2 +
            cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;
        $distanceMeters = 2 * $earthRadiusMeters * atan2(sqrt($a), sqrt(1 - $a));

        if ($distanceMeters > 500) {
            throw ValidationException::withMessages([
                'latitude' => 'Check-in ditolak. Lokasi harus berada maksimal 500 meter dari sekolah.',
            ]);
        }
    }
}
