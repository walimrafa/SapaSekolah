<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\StudentQuestionnaire;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class EventStudentExportImportService
{
    /**
     * Download Excel template for manual event student leads import.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet();

            // Sheet 1: Template Data
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Data Siswa Event');

            $headers = [
                'Nama Lengkap Siswa*',
                'Jenis Kelamin (L/P)*',
                'Asal Sekolah*',
                'Kelas*',
                'Jurusan*',
                'No HP Siswa*',
                'Email Siswa*',
                'Akun Medsos Siswa',
                'Nama Orang Tua / Wali*',
                'No HP Wali*',
                'Akun Medsos Wali',
                'Minat Beasiswa (ya/tidak)*',
                'Nama Event / Ref No (Opsional)',
            ];

            // Header Style
            $headerFill = [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Dark Navy
            ];
            $headerFont = [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ];

            // Write Headers
            foreach ($headers as $colIndex => $headerText) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $cellCoordinate = $colLetter . '1';
                $sheet->setCellValue($cellCoordinate, $headerText);
                $sheet->getStyle($cellCoordinate)->applyFromArray([
                    'fill' => $headerFill,
                    'font' => $headerFont,
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
            }
            $sheet->getRowDimension(1)->setRowHeight(28);

            // Sample Rows
            $sampleData = [
                [
                    'Ahmad Fauzi Rizki',
                    'L',
                    'SMAN 1 Karawang',
                    'XII MIPA 1',
                    'IPA',
                    '081234567890',
                    'ahmad.fauzi@gmail.com',
                    '@ahmad_fauzi (IG)',
                    'Bambang Rizki',
                    '081298765432',
                    '@bambang_r (TikTok)',
                    'ya',
                    'Pentas Seni & Edu Fair 2026',
                ],
                [
                    'Siti Nurhaliza',
                    'P',
                    'SMKN 1 Cikampek',
                    'XII RPL 2',
                    'Rekayasa Perangkat Lunak',
                    '085712345678',
                    'siti.nurhaliza@gmail.com',
                    '@sitinur (IG)',
                    'Hj. Fatimah',
                    '085798765432',
                    '',
                    'tidak',
                    'EVT-2026-0001',
                ],
                [
                    'Budi Santoso',
                    'SMA Islam Al-Azhar',
                    'XII IPS 3',
                    'IPS',
                    '081398765432',
                    'budi.santoso@yahoo.com',
                    '',
                    'Sutrisno',
                    '081312345678',
                    '',
                    'ya',
                    '',
                ],
            ];

            foreach ($sampleData as $rowIndex => $rowValues) {
                $rowNum = $rowIndex + 2;
                foreach ($rowValues as $colIndex => $val) {
                    $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                    $sheet->setCellValueExplicit($colLetter . $rowNum, $val, DataType::TYPE_STRING);
                }
            }

            // Auto-width
            foreach (range(1, count($headers)) as $colIndex) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            // Sheet 2: Panduan & Petunjuk
            $guideSheet = $spreadsheet->createSheet();
            $guideSheet->setTitle('PANDUAN & ATURAN');
            $guideSheet->setCellValue('A1', 'PANDUAN IMPORT DATA HASIL SUPPORT EVENT SISWA');
            $guideSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0F172A');

            $guidelines = [
                [''],
                ['Petunjuk Kolom:', 'Keterangan:'],
                ['Nama Lengkap Siswa*', 'Wajib diisi. Nama lengkap siswa yang mendaftar di booth / event.'],
                ['Asal Sekolah*', 'Wajib diisi. Tulis nama sekolah siswa (misal: SMAN 1 Karawang).'],
                ['Kelas*', 'Wajib diisi. Contoh: XII MIPA 1, XII TKJ, dll.'],
                ['Jurusan*', 'Wajib diisi. Contoh: IPA, IPS, RPL, Akuntansi.'],
                ['No HP Siswa*', 'Wajib diisi. Nomor HP / WhatsApp siswa aktif untuk follow up.'],
                ['Email Siswa*', 'Wajib diisi. Email aktif siswa.'],
                ['Akun Medsos Siswa', 'Opsional. Akun IG / TikTok / Facebook siswa.'],
                ['Nama Orang Tua / Wali*', 'Wajib diisi. Nama orang tua atau wali siswa.'],
                ['No HP Wali*', 'Wajib diisi. Nomor WhatsApp orang tua / wali.'],
                ['Akun Medsos Wali', 'Opsional. Akun media sosial orang tua / wali.'],
                ['Minat Beasiswa*', 'Wajib diisi dengan "ya" atau "tidak". (ya = Minat Beasiswa UBSI, tidak = Non-Beasiswa / Reguler).'],
                ['Nama Event / Ref No', 'Opsional. Masukkan Nama Event atau Nomor Referensi Proposal (misal: EVT-2026-0001).'],
                [''],
                ['Catatan Penting:', 'Semua data yang diimpor akan otomatis masuk ke menu Follow Up Siswa dan tersinkronisasi langsung ke CRM SAPA Sekolah.'],
            ];

            foreach ($guidelines as $idx => $g) {
                $row = $idx + 2;
                $guideSheet->setCellValue('A' . $row, $g[0] ?? '');
                if (isset($g[1])) {
                    $guideSheet->setCellValue('B' . $row, $g[1]);
                }
            }
            $guideSheet->getStyle('A3:B3')->getFont()->setBold(true);
            $guideSheet->getColumnDimension('A')->setWidth(30);
            $guideSheet->getColumnDimension('B')->setWidth(80);

            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'template-import-siswa-hasil-support-event.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Import manual event student leads from uploaded Excel file.
     */
    public function import(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2 || empty(array_filter($rows[0] ?? [], fn ($value) => trim((string) $value) !== ''))) {
            return [
                'status' => false,
                'message' => 'File Excel kosong atau baris header tidak ditemukan.',
            ];
        }

        $headerMap = $this->mapHeaders($rows[0]);

        if (!isset($headerMap['student_name']) || !isset($headerMap['student_phone'])) {
            return [
                'status' => false,
                'message' => 'Kolom header wajib minimal tidak lengkap: "Nama Lengkap Siswa" dan "No HP Siswa" harus ada.',
            ];
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $rowErrors = [];

        DB::transaction(function () use ($rows, $headerMap, &$createdCount, &$updatedCount, &$skippedCount, &$rowErrors) {
            foreach (array_slice($rows, 1, null, true) as $index => $row) {
                $values = [];
                foreach ($headerMap as $field => $colIdx) {
                    $values[$field] = isset($row[$colIdx]) ? trim((string) $row[$colIdx]) : '';
                }

                // Skip purely empty row
                if (count(array_filter($values, fn ($v) => $v !== '')) === 0) {
                    continue;
                }

                $rowNumber = $index + 1;

                if (empty($values['student_name']) || empty($values['student_phone'])) {
                    $skippedCount++;
                    $rowErrors[] = "Baris #{$rowNumber}: Nama Siswa dan Nomor HP wajib diisi.";
                    continue;
                }

                $cleanPhone = preg_replace('/[^0-9]/', '', $values['student_phone']);
                if (strlen($cleanPhone) < 7) {
                    $skippedCount++;
                    $rowErrors[] = "Baris #{$rowNumber}: Nomor HP siswa '{$values['student_phone']}' tidak valid.";
                    continue;
                }

                // Minat Beasiswa normalization
                $scholarshipRaw = strtolower($values['scholarship_interest'] ?? 'ya');
                $scholarshipInterest = str_contains($scholarshipRaw, 'tidak') || str_contains($scholarshipRaw, 'mandiri') || str_contains($scholarshipRaw, 'no') 
                    ? 'tidak' 
                    : 'ya';

                // Resolve school
                $schoolId = null;
                $schoolName = !empty($values['school_name']) ? $values['school_name'] : null;
                if ($schoolName) {
                    $matchedSchool = School::where('name', $schoolName)
                        ->orWhere('name', 'like', "%{$schoolName}%")
                        ->first();
                    if ($matchedSchool) {
                        $schoolId = $matchedSchool->id;
                        $schoolName = $matchedSchool->name;
                    }
                }

                // Resolve Event Proposal
                $eventProposalId = null;
                $eventTitle = null;
                if (!empty($values['event_ref'])) {
                    $eventQuery = trim($values['event_ref']);
                    $matchedEvent = SchoolEventProposal::where('reference_no', $eventQuery)
                        ->orWhere('event_name', 'like', "%{$eventQuery}%")
                        ->first();
                    if ($matchedEvent) {
                        $eventProposalId = $matchedEvent->id;
                        $eventTitle = $matchedEvent->event_name;
                        $matchedEvent->increment('prospects_acquired');
                    } else {
                        $eventTitle = $eventQuery;
                    }
                }

                $collegeNotes = $eventTitle 
                    ? "Hasil Support Event: {$eventTitle}" 
                    : 'Hasil Support Event Sekolah (Import Excel)';

                $scholarshipNotes = ($scholarshipInterest === 'ya') 
                    ? 'Berminat Program Beasiswa UBSI' 
                    : 'Non-Beasiswa / Jalur Mandiri';

                $genderVal = null;
                if (!empty($values['gender'])) {
                    $rawGender = strtoupper(trim((string)$values['gender']));
                    if (str_starts_with($rawGender, 'P') || str_contains($rawGender, 'PEREMPUAN') || str_contains($rawGender, 'WANITA')) {
                        $genderVal = 'P';
                    } elseif (str_starts_with($rawGender, 'L') || str_contains($rawGender, 'LAKI') || str_contains($rawGender, 'PRIA')) {
                        $genderVal = 'L';
                    }
                }

                $payload = [
                    'school_id' => $schoolId,
                    'school_event_proposal_id' => $eventProposalId,
                    'school_name' => $schoolName,
                    'student_name' => $values['student_name'],
                    'gender' => $genderVal,
                    'student_class' => $values['student_class'] ?? 'XII',
                    'student_major' => $values['student_major'] ?? 'Umum',
                    'student_phone' => $values['student_phone'],
                    'student_email' => !empty($values['student_email']) ? $values['student_email'] : 'siswa_' . substr($cleanPhone, -6) . '@ubsi-event.id',
                    'student_social_media' => $values['student_social_media'] ?? null,
                    'guardian_name' => !empty($values['guardian_name']) ? $values['guardian_name'] : 'Wali ' . $values['student_name'],
                    'guardian_phone' => !empty($values['guardian_phone']) ? $values['guardian_phone'] : $values['student_phone'],
                    'guardian_social_media' => $values['guardian_social_media'] ?? null,
                    'scholarship_interest' => $scholarshipInterest,
                    'source' => 'support_event',
                    'answers' => null,
                    'followup_status' => StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI,
                    'college_interest_notes' => $collegeNotes,
                    'scholarship_notes' => $scholarshipNotes,
                ];

                $existing = StudentQuestionnaire::where('student_phone', $payload['student_phone'])->first();

                if ($existing) {
                    $existing->update([
                        'school_id' => $payload['school_id'] ?? $existing->school_id,
                        'school_event_proposal_id' => $payload['school_event_proposal_id'] ?? $existing->school_event_proposal_id,
                        'student_name' => $payload['student_name'],
                        'gender' => $payload['gender'] ?? $existing->gender,
                        'student_class' => $payload['student_class'],
                        'student_major' => $payload['student_major'],
                        'student_email' => $payload['student_email'],
                        'guardian_name' => $payload['guardian_name'],
                        'guardian_phone' => $payload['guardian_phone'],
                        'scholarship_interest' => $payload['scholarship_interest'],
                        'source' => 'support_event',
                        'college_interest_notes' => $payload['college_interest_notes'],
                        'scholarship_notes' => $payload['scholarship_notes'],
                    ]);
                    $updatedCount++;
                } else {
                    StudentQuestionnaire::create($payload);
                    $createdCount++;
                }
            }
        });

        $totalProcessed = $createdCount + $updatedCount;

        if ($totalProcessed === 0 && $skippedCount > 0) {
            return [
                'status' => false,
                'message' => 'Tidak ada data siswa event yang berhasil diimpor.',
                'errors' => $rowErrors,
            ];
        }

        $message = "Import berhasil: {$totalProcessed} siswa event diproses ({$createdCount} data baru, {$updatedCount} diperbarui).";
        if ($skippedCount > 0) {
            $message .= " ({$skippedCount} baris dilewati karena format tidak valid).";
        }

        return [
            'status' => true,
            'message' => $message,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => $rowErrors,
        ];
    }

    /**
     * Export Hasil Support Event data to Excel (.xlsx).
     */
    public function export(Request $request): StreamedResponse
    {
        $query = StudentQuestionnaire::query()
            ->where(function ($q) {
                $q->where('source', 'support_event')
                  ->orWhereNotNull('school_event_proposal_id')
                  ->orWhere('college_interest_notes', 'like', '%Support Event%');
            })
            ->with(['school.district', 'eventProposal', 'lastFollowedUpBy'])
            ->latest();

        if ($request->filled('event_id')) {
            $query->where('school_event_proposal_id', $request->event_id);
        }

        if ($request->filled('scholarship_interest')) {
            $query->where('scholarship_interest', $request->scholarship_interest);
        }

        if ($request->filled('status')) {
            $query->where('followup_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_phone', 'like', "%{$search}%")
                  ->orWhere('student_email', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('student_class', 'like', "%{$search}%")
                  ->orWhere('student_major', 'like', "%{$search}%");
            });
        }

        $students = $query->get();

        return response()->streamDownload(function () use ($students) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Hasil Support Event');

            // Header Banner
            $sheet->setCellValue('A1', 'SAPA SEKOLAH - DATA SISWA HASIL SUPPORT EVENT');
            $sheet->mergeCells('A1:O1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F172A']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(1)->setRowHeight(32);

            $sheet->setCellValue('A2', 'Diekspor pada: ' . date('d F Y H:i') . ' | Total: ' . count($students) . ' Siswa Terdaftar');
            $sheet->mergeCells('A2:P2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '475569']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(20);

            $headers = [
                'No',
                'Waktu Daftar',
                'Nama Lengkap Siswa',
                'Jenis Kelamin',
                'Asal Sekolah',
                'Kelas',
                'Jurusan',
                'No. HP / WA Siswa',
                'Email Siswa',
                'Medsos Siswa',
                'Nama Wali',
                'No. HP Wali',
                'Medsos Wali',
                'Minat Beasiswa',
                'Event Asal',
                'Status Follow Up',
            ];

            $headerRow = 4;
            foreach ($headers as $colIndex => $headerText) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex + 1);
                $cellCoord = $colLetter . $headerRow;
                $sheet->setCellValue($cellCoord, $headerText);
                $sheet->getStyle($cellCoord)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '334155']]],
                ]);
            }
            $sheet->getRowDimension($headerRow)->setRowHeight(26);

            $startDataRow = 5;
            foreach ($students as $idx => $s) {
                $currentRow = $startDataRow + $idx;

                $eventDisplay = $s->eventProposal 
                    ? $s->eventProposal->event_name . ' (' . $s->eventProposal->reference_no . ')' 
                    : ($s->college_interest_notes ?: '-');

                $scholarshipDisplay = ($s->scholarship_interest === 'ya') ? 'Ya (Berminat Beasiswa)' : 'Tidak (Jalur Mandiri)';
                $statusDisplay = $s->followup_status_label;

                $sheet->setCellValue('A' . $currentRow, $idx + 1);
                $sheet->setCellValue('B' . $currentRow, $s->created_at->format('d/m/Y H:i'));
                $sheet->setCellValue('C' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->student_name));
                $sheet->setCellValue('D' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->gender_label ?: '-'));
                $sheet->setCellValue('E' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->school_display_name ?: '-'));
                $sheet->setCellValue('F' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->student_class ?: '-'));
                $sheet->setCellValue('G' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->student_major ?: '-'));
                $sheet->setCellValueExplicit('H' . $currentRow, (string)$s->student_phone, DataType::TYPE_STRING);
                $sheet->setCellValue('I' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->student_email ?: '-'));
                $sheet->setCellValue('J' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->student_social_media ?: '-'));
                $sheet->setCellValue('K' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->guardian_name ?: '-'));
                $sheet->setCellValueExplicit('L' . $currentRow, (string)$s->guardian_phone, DataType::TYPE_STRING);
                $sheet->setCellValue('M' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($s->guardian_social_media ?: '-'));
                $sheet->setCellValue('N' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($scholarshipDisplay));
                $sheet->setCellValue('O' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($eventDisplay));
                $sheet->setCellValue('P' . $currentRow, \App\Support\SecuritySanitizer::sanitizeSpreadsheetCell($statusDisplay));

                // Alternating zebra row background
                $rowFillColor = ($idx % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
                $sheet->getStyle("A{$currentRow}:P{$currentRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowFillColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Highlight Minat Beasiswa
                if ($s->scholarship_interest === 'ya') {
                    $sheet->getStyle('N' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('065F46'); // Emerald
                }

                $sheet->getRowDimension($currentRow)->setRowHeight(22);
            }

            // Auto-width for all columns
            foreach (range(1, count($headers)) as $colIndex) {
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'data-hasil-support-event-' . date('Y-m-d_His') . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Get filtered student leads collection.
     */
    public function getFilteredStudents(Request $request)
    {
        $query = StudentQuestionnaire::query()
            ->where(function ($q) {
                $q->where('source', 'support_event')
                  ->orWhereNotNull('school_event_proposal_id')
                  ->orWhere('college_interest_notes', 'like', '%Support Event%');
            })
            ->with(['school.district', 'eventProposal', 'lastFollowedUpBy'])
            ->latest();

        if ($request->filled('event_id')) {
            $query->where('school_event_proposal_id', $request->event_id);
        }

        if ($request->filled('scholarship_interest')) {
            $query->where('scholarship_interest', $request->scholarship_interest);
        }

        if ($request->filled('status')) {
            $query->where('followup_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                  ->orWhere('student_phone', 'like', "%{$search}%")
                  ->orWhere('student_email', 'like', "%{$search}%")
                  ->orWhere('school_name', 'like', "%{$search}%")
                  ->orWhere('student_class', 'like', "%{$search}%")
                  ->orWhere('student_major', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Export Hasil Support Event data to Printable PDF View.
     */
    public function exportPdf(Request $request): View
    {
        $students = $this->getFilteredStudents($request);
        $activeCampus = AppContextService::getActiveCampus();
        $campusName = $activeCampus ? $activeCampus->name : AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika (UBSI)');
        $exportDate = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';

        $metrics = [
            'total' => $students->count(),
            'scholarship_yes' => $students->where('scholarship_interest', 'ya')->count(),
            'scholarship_no' => $students->where('scholarship_interest', 'tidak')->count(),
            'belum_dihubungi' => $students->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'terfollowup' => $students->where('followup_status', '!=', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
        ];

        return view('event-students.pdf', compact('students', 'campusName', 'exportDate', 'metrics'));
    }

    /**
     * Export Hasil Support Event data to PowerPoint Presentation (.pptx).
     */
    public function exportPptx(Request $request): StreamedResponse
    {
        $students = $this->getFilteredStudents($request);
        $activeCampus = AppContextService::getActiveCampus();
        $campusName = $activeCampus ? $activeCampus->name : AppSettingService::get('campus_name', 'Universitas Bina Sarana Informatika');
        $periodName = AppContextService::getActivePeriod()?->name ?? (date('Y') . '/' . (date('Y') + 1));
        $exportDate = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';
        $fileName = 'Data_Siswa_Hasil_Support_Event_' . date('Ymd_His') . '.pptx';

        $metrics = [
            'total' => $students->count(),
            'scholarship_yes' => $students->where('scholarship_interest', 'ya')->count(),
            'scholarship_no' => $students->where('scholarship_interest', 'tidak')->count(),
            'belum_dihubungi' => $students->where('followup_status', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
            'terfollowup' => $students->where('followup_status', '!=', StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI)->count(),
        ];

        $binary = $this->generateStudentPptxBinary($students, $metrics, $campusName, $periodName, $exportDate);

        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    protected function generateStudentPptxBinary($students, array $metrics, string $campusName, string $periodName, string $exportDate): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'sapa_stud_pptx_');
        $zip = new ZipArchive();
        $zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $slideCount = 5;
        $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml($slideCount));
        $zip->addFromString('_rels/.rels', $this->getRootRelsXml());
        $zip->addFromString('ppt/presentation.xml', $this->getPresentationXml($slideCount));
        $zip->addFromString('ppt/_rels/presentation.xml.rels', $this->getPresentationRelsXml($slideCount));

        $zip->addFromString('ppt/slideMasters/slideMaster1.xml', $this->getSlideMasterXml());
        $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', $this->getSlideMasterRelsXml());
        $zip->addFromString('ppt/slideLayouts/slideLayout1.xml', $this->getSlideLayoutXml());
        $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels', $this->getSlideLayoutRelsXml());

        // Slide 1: Cover
        $zip->addFromString('ppt/slides/slide1.xml', $this->getSlide1CoverXml($campusName, $periodName, $exportDate));
        $zip->addFromString('ppt/slides/_rels/slide1.xml.rels', $this->getSlideRelsXml());

        // Slide 2: KPI Leads
        $zip->addFromString('ppt/slides/slide2.xml', $this->getSlide2KpiXml($metrics, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide2.xml.rels', $this->getSlideRelsXml());

        // Slide 3: Top Sekolah Asal Leads
        $zip->addFromString('ppt/slides/slide3.xml', $this->getSlide3SchoolsXml($students, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide3.xml.rels', $this->getSlideRelsXml());

        // Slide 4: Potensi Beasiswa
        $zip->addFromString('ppt/slides/slide4.xml', $this->getSlide4ScholarshipXml($metrics, $campusName));
        $zip->addFromString('ppt/slides/_rels/slide4.xml.rels', $this->getSlideRelsXml());

        // Slide 5: Rekomendasi Follow Up
        $zip->addFromString('ppt/slides/slide5.xml', $this->getSlide5FollowupXml($campusName, $periodName, $exportDate));
        $zip->addFromString('ppt/slides/_rels/slide5.xml.rels', $this->getSlideRelsXml());

        $zip->close();
        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }

    protected function getContentTypesXml(int $slideCount): string
    {
        $slidesXml = '';
        for ($i = 1; $i <= $slideCount; $i++) {
            $slidesXml .= '<Override PartName="/ppt/slides/slide' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
            . '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
            . '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>'
            . $slidesXml
            . '</Types>';
    }

    protected function getRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'
            . '</Relationships>';
    }

    protected function getPresentationXml(int $slideCount): string
    {
        $sldIdList = '';
        for ($i = 1; $i <= $slideCount; $i++) {
            $sldIdList .= '<p:sldId id="' . (255 + $i) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'
            . '<p:sldIdLst>' . $sldIdList . '</p:sldIdLst>'
            . '<p:sldSz cx="12192000" cy="6858000" type="screen16x9"/>'
            . '<p:notesSz cx="6858000" cy="9144000"/>'
            . '</p:presentation>';
    }

    protected function getPresentationRelsXml(int $slideCount): string
    {
        $rels = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>';
        for ($i = 1; $i <= $slideCount; $i++) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide' . $i . '.xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels
            . '</Relationships>';
    }

    protected function getSlideMasterXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldMaster xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr></p:spTree></p:cSld>'
            . '<p:clrMap bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'
            . '<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst>'
            . '</p:sldMaster>';
    }

    protected function getSlideMasterRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '</Relationships>';
    }

    protected function getSlideLayoutXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldLayout xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main" type="blank">'
            . '<p:cSld><p:spTree><p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr><p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr></p:spTree></p:cSld>'
            . '</p:sldLayout>';
    }

    protected function getSlideLayoutRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '</Relationships>';
    }

    protected function getSlideRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '</Relationships>';
    }

    protected function getSlide1CoverXml(string $campusName, string $periodName, string $exportDate): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, '0B023E')
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 120000, 'DF0B2B')
            . $this->buildBoxShape(4, 'TagPill', 914400, 1400000, 3800000, 420000, '8B5CF6')
            . $this->buildTextShape(5, 'TagText', 914400, 1400000, 3800000, 420000, 'STUDENT LEADS REPORT', 'FFFFFF', 12, true, 'ctr')
            . $this->buildTextShape(6, 'Title', 914400, 2200000, 10363200, 1400000, 'DATA SISWA HASIL SUPPORT EVENT', 'FFFFFF', 34, true, 'l')
            . $this->buildTextShape(7, 'Subtitle', 914400, 3700000, 10363200, 600000, strtoupper($campusName) . ' • TAHUN AKADEMIK ' . strtoupper($periodName), 'F59E0B', 18, true, 'l')
            . $this->buildTextShape(8, 'Desc', 914400, 4400000, 10363200, 600000, 'Rekapitulasi Penjaringan Leads Calon Mahasiswa dari Kegiatan Support Event Sekolah', '94A3B8', 13, false, 'l')
            . $this->buildBoxShape(9, 'InfoBar', 914400, 5600000, 10363200, 700000, '1E1B4B')
            . $this->buildTextShape(10, 'InfoText', 1100000, 5700000, 10000000, 500000, 'Generated by SAPA Sekolah • Tanggal: ' . $exportDate . ' • ' . $campusName, 'E2E8F0', 11, false, 'l')
            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide2KpiXml(array $metrics, string $campusName): string
    {
        $total = number_format($metrics['total'] ?? 0);
        $beasiswa = number_format($metrics['scholarship_yes'] ?? 0);
        $belum = number_format($metrics['belum_dihubungi'] ?? 0);
        $terfollowup = number_format($metrics['terfollowup'] ?? 0);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, '0B023E')
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, 'DF0B2B')
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'CAPAIAN PENJARINGAN LEADS SISWA EVENT', 'FFFFFF', 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Statistik Calon Mahasiswa Terdaftar dari Booth & Stand Event • ' . $campusName, 'F59E0B', 12, false, 'l')

            // Card 1: Total Siswa
            . $this->buildBoxShape(6, 'Card1', 800000, 1800000, 2450000, 2200000, '1E293B')
            . $this->buildBoxShape(7, 'Card1Top', 800000, 1800000, 2450000, 50000, '8B5CF6')
            . $this->buildTextShape(8, 'Card1Lbl', 950000, 1950000, 2150000, 350000, 'TOTAL SISWA TERDAFTAR', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(9, 'Card1Val', 950000, 2300000, 2150000, 750000, $total, 'FFFFFF', 34, true, 'l')
            . $this->buildTextShape(10, 'Card1Sub', 950000, 3200000, 2150000, 500000, 'Leads Masuk ke Database', 'A78BFA', 11, true, 'l')

            // Card 2: Minat Beasiswa
            . $this->buildBoxShape(11, 'Card2', 3450000, 1800000, 2450000, 2200000, '1E293B')
            . $this->buildBoxShape(12, 'Card2Top', 3450000, 1800000, 2450000, 50000, '10B981')
            . $this->buildTextShape(13, 'Card2Lbl', 3600000, 1950000, 2150000, 350000, 'BERMINAT BEASISWA', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(14, 'Card2Val', 3600000, 2300000, 2150000, 750000, $beasiswa, 'FFFFFF', 34, true, 'l')
            . $this->buildTextShape(15, 'Card2Sub', 3600000, 3200000, 2150000, 500000, 'Prioritas Penawaran Admisi', '34D399', 11, true, 'l')

            // Card 3: Belum Dihubungi
            . $this->buildBoxShape(16, 'Card3', 6100000, 1800000, 2450000, 2200000, '1E293B')
            . $this->buildBoxShape(17, 'Card3Top', 6100000, 1800000, 2450000, 50000, 'F59E0B')
            . $this->buildTextShape(18, 'Card3Lbl', 6250000, 1950000, 2150000, 350000, 'BELUM DIHUBUNGI', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(19, 'Card3Val', 6250000, 2300000, 2150000, 750000, $belum, 'FFFFFF', 34, true, 'l')
            . $this->buildTextShape(20, 'Card3Sub', 6250000, 3200000, 2150000, 500000, 'Antrian Follow Up Petugas', 'FBBF24', 11, true, 'l')

            // Card 4: Ter-Follow Up
            . $this->buildBoxShape(21, 'Card4', 8750000, 1800000, 2600000, 2200000, '1E293B')
            . $this->buildBoxShape(22, 'Card4Top', 8750000, 1800000, 2600000, 50000, '3B82F6')
            . $this->buildTextShape(23, 'Card4Lbl', 8900000, 1950000, 2300000, 350000, 'SUDAH DIHUBUNGI', '94A3B8', 11, true, 'l')
            . $this->buildTextShape(24, 'Card4Val', 8900000, 2300000, 2300000, 750000, $terfollowup, 'FFFFFF', 34, true, 'l')
            . $this->buildTextShape(25, 'Card4Sub', 8900000, 3200000, 2300000, 500000, 'Tindak Lanjut Telepon / WA', '60A5FA', 11, true, 'l')

            // Summary Box
            . $this->buildBoxShape(26, 'SummaryBox', 800000, 4300000, 10550000, 1900000, '1E1B4B')
            . $this->buildTextShape(27, 'SumTitle', 1050000, 4450000, 10000000, 350000, 'STATUS INTEGRASI DATA LEADS EVENT:', 'F59E0B', 12, true, 'l')
            . $this->buildTextShape(28, 'SumText', 1050000, 4850000, 10000000, 1200000, "1. Setiap siswa yang mengisi QR barcode atau formulir langsung masuk ke antrian follow up CRM.\n2. Siswa berminat beasiswa memiliki potensi konversi pendaftaran kuliah lebih tinggi.\n3. Petugas marketing dapat memantau respon siswa (berminat, no respon, daftar aplikasi) secara realtime.", 'E2E8F0', 11, false, 'l')

            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide3SchoolsXml($students, string $campusName): string
    {
        $schoolCounts = $students->groupBy('school_display_name')->map->count()->sortDesc()->take(5);
        $rowsXml = '';
        $y = 1800000;

        foreach ($schoolCounts as $schName => $count) {
            $rowsXml .= $this->buildBoxShape(rand(100, 9999), 'RowBg', 800000, $y, 10550000, 680000, '1E293B')
                . $this->buildTextShape(rand(100, 9999), 'RowTitle', 950000, $y + 160000, 7000000, 350000, $schName ?: 'Sekolah Mitra', 'FFFFFF', 12, true, 'l')
                . $this->buildTextShape(rand(100, 9999), 'RowStat', 8000000, $y + 160000, 3100000, 350000, "{$count} Siswa Terjaring", 'F59E0B', 12, true, 'r');
            $y += 780000;
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, '0B023E')
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, 'DF0B2B')
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'SEKOLAH DENGAN PENJARINGAN LEADS TERBANYAK', 'FFFFFF', 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Peringkat sekolah asal siswa yang aktif berpartisipasi pada event • ' . $campusName, 'F59E0B', 12, false, 'l')
            . $rowsXml
            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide4ScholarshipXml(array $metrics, string $campusName): string
    {
        $yes = number_format($metrics['scholarship_yes'] ?? 0);
        $no = number_format($metrics['scholarship_no'] ?? 0);
        $total = $metrics['total'] ?? 0;
        $pctYes = ($total > 0) ? round(($metrics['scholarship_yes'] / $total) * 100, 1) : 0;
        $pctNo = ($total > 0) ? round(($metrics['scholarship_no'] / $total) * 100, 1) : 0;

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, '0B023E')
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, 'DF0B2B')
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'ANALISIS MINAT BEASISWA KULIAH', 'FFFFFF', 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Peminatan program beasiswa vs pendaftaran reguler siswa event • ' . $campusName, 'F59E0B', 12, false, 'l')

            // Box Beasiswa Ya
            . $this->buildBoxShape(6, 'BoxYes', 800000, 1800000, 5100000, 2400000, '1E293B')
            . $this->buildBoxShape(7, 'BoxYesTop', 800000, 1800000, 5100000, 60000, '10B981')
            . $this->buildTextShape(8, 'YesLbl', 1100000, 2050000, 4500000, 350000, 'BERMINAT BEASISWA (KIP / PRESTASI)', '34D399', 12, true, 'l')
            . $this->buildTextShape(9, 'YesVal', 1100000, 2500000, 4500000, 900000, $yes . ' Siswa', 'FFFFFF', 36, true, 'l')
            . $this->buildTextShape(10, 'YesSub', 1100000, 3550000, 4500000, 400000, "Porsi: {$pctYes}% dari total leads", '10B981', 12, true, 'l')

            // Box Reguler
            . $this->buildBoxShape(11, 'BoxNo', 6250000, 1800000, 5100000, 2400000, '1E293B')
            . $this->buildBoxShape(12, 'BoxNoTop', 6250000, 1800000, 5100000, 60000, '64748B')
            . $this->buildTextShape(13, 'NoLbl', 6550000, 2050000, 4500000, 350000, 'JALUR REGULER / MANDIRI', '94A3B8', 12, true, 'l')
            . $this->buildTextShape(14, 'NoVal', 6550000, 2500000, 4500000, 900000, $no . ' Siswa', 'FFFFFF', 36, true, 'l')
            . $this->buildTextShape(15, 'NoSub', 6550000, 3550000, 4500000, 400000, "Porsi: {$pctNo}% dari total leads", '94A3B8', 12, false, 'l')

            // Summary Box
            . $this->buildBoxShape(16, 'BottomInfo', 800000, 4500000, 10550000, 1600000, '1E1B4B')
            . $this->buildTextShape(17, 'BInfoT', 1050000, 4700000, 10000000, 350000, 'STRATEGI PENAWARAN BEASISWA:', 'F59E0B', 12, true, 'l')
            . $this->buildTextShape(18, 'BInfoD', 1050000, 5150000, 10000000, 800000, "Siswa berminat beasiswa harus segera diarahkan untuk melengkapi berkas syarat KIP-K / Beasiswa Prestasi dan membuat akun PMB melalui aplikasi atau portal resmi kampus.", 'E2E8F0', 11, false, 'l')

            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function getSlide5FollowupXml(string $campusName, string $periodName, string $exportDate): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . $this->buildBoxShape(2, 'BgNavy', 0, 0, 12192000, 6858000, '0B023E')
            . $this->buildBoxShape(3, 'AccentStrip', 0, 0, 12192000, 90000, 'DF0B2B')
            . $this->buildTextShape(4, 'Header', 800000, 500000, 10500000, 600000, 'ALUR TINDAK LANJUT FOLLOW UP & ADMISI', 'FFFFFF', 24, true, 'l')
            . $this->buildTextShape(5, 'Subheader', 800000, 1100000, 10500000, 400000, 'Standar Operasional Penanganan Leads Siswa Event • ' . $campusName, 'F59E0B', 12, false, 'l')

            . $this->buildBoxShape(6, 'P1', 800000, 1800000, 10550000, 1100000, '1E293B')
            . $this->buildTextShape(7, 'P1T', 1050000, 1920000, 10000000, 300000, '1. PLOTING PETUGAS MARKETING', 'F59E0B', 11, true, 'l')
            . $this->buildTextShape(8, 'P1D', 1050000, 2250000, 10000000, 500000, 'Gunakan fitur Ploting Petugas untuk membagi data siswa secara otomatis dan merata kepada seluruh staf marketing aktif.', 'E2E8F0', 10, false, 'l')

            . $this->buildBoxShape(9, 'P2', 800000, 3050000, 10550000, 1100000, '1E293B')
            . $this->buildTextShape(10, 'P2T', 1050000, 3170000, 10000000, 300000, '2. KOMUNIKASI WHATSAPP & TELEPON', '38BDF8', 11, true, 'l')
            . $this->buildTextShape(11, 'P2D', 1050000, 3500000, 10000000, 500000, 'Kirimkan pesan personal via template WhatsApp resmi SAPA Sekolah untuk menyapa siswa dan menawarkan e-brosur program studi.', 'E2E8F0', 10, false, 'l')

            . $this->buildBoxShape(12, 'P3', 800000, 4300000, 10550000, 1100000, '1E293B')
            . $this->buildTextShape(13, 'P3T', 1050000, 4420000, 10000000, 300000, '3. KONVERSI KE APLIKASI PMB', '34D399', 11, true, 'l')
            . $this->buildTextShape(14, 'P3D', 1050000, 4750000, 10000000, 500000, 'Bantu siswa hingga tahap pembuatan akun dan pendaftaran aplikasi PMB UBSI untuk mengamankan kuota beasiswa.', 'E2E8F0', 10, false, 'l')

            . $this->buildBoxShape(15, 'FooterBox', 800000, 5700000, 10550000, 600000, '1E1B4B')
            . $this->buildTextShape(16, 'FooterText', 1050000, 5820000, 10000000, 350000, "SAPA Sekolah • {$campusName} • Diekspor pada {$exportDate}", '94A3B8', 10, false, 'l')

            . '</p:spTree></p:cSld></p:sld>';
    }

    protected function buildBoxShape(int $id, string $name, int $x, int $y, int $cx, int $cy, string $colorHex): string
    {
        return '<p:sp>'
            . '<p:nvSpPr><p:cNvPr id="' . $id . '" name="' . $name . '"/><p:cNvSpPr><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
            . '<p:spPr>'
            . '<a:xfrm><a:off x="' . $x . '" y="' . $y . '"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val 8000"/></a:avLst></a:prstGeom>'
            . '<a:solidFill><a:srgbClr val="' . $colorHex . '"/></a:solidFill>'
            . '<a:ln w="0"><a:noFill/></a:ln>'
            . '</p:spPr>'
            . '</p:sp>';
    }

    protected function buildTextShape(int $id, string $name, int $x, int $y, int $cx, int $cy, string $text, string $colorHex, int $fontSize = 12, bool $bold = false, string $align = 'l'): string
    {
        $escaped = htmlspecialchars($text, ENT_XML1, 'UTF-8');
        $paragraphs = explode("\n", $escaped);
        $pXml = '';

        foreach ($paragraphs as $para) {
            $pXml .= '<a:p>'
                . '<a:pPr algn="' . $align . '"/>'
                . '<a:r>'
                . '<a:rPr lang="id-ID" sz="' . ($fontSize * 100) . '" b="' . ($bold ? '1' : '0') . '">'
                . '<a:solidFill><a:srgbClr val="' . $colorHex . '"/></a:solidFill>'
                . '<a:latin typeface="Segoe UI"/>'
                . '</a:rPr>'
                . '<a:t>' . $para . '</a:t>'
                . '</a:r>'
                . '</a:p>';
        }

        return '<p:sp>'
            . '<p:nvSpPr><p:cNvPr id="' . $id . '" name="' . $name . '"/><p:cNvSpPr txBox="1"><a:spLocks noGrp="1"/></p:cNvSpPr><p:nvPr/></p:nvPr>'
            . '<p:spPr>'
            . '<a:xfrm><a:off x="' . $x . '" y="' . $y . '"/><a:ext cx="' . $cx . '" cy="' . $cy . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            . '<a:noFill/>'
            . '<a:ln w="0"><a:noFill/></a:ln>'
            . '</p:spPr>'
            . '<p:txBody>'
            . '<a:bodyPr vert="horz" lIns="0" tIns="0" rIns="0" bIns="0" wrap="square"/>'
            . '<a:lstStyle/>'
            . $pXml
            . '</p:txBody>'
            . '</p:sp>';
    }

    /**
     * Map fuzzy header string to recognized fields.
     */
    protected function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $colName) {
            $normalized = strtolower(trim((string) $colName));
            $normalized = preg_replace('/[^a-z0-9]/', '', $normalized);

            if (str_contains($normalized, 'namasiswa') || str_contains($normalized, 'namalengkap') || $normalized === 'nama') {
                $map['student_name'] = $index;
            } elseif (str_contains($normalized, 'jeniskelamin') || $normalized === 'jk' || $normalized === 'gender') {
                $map['gender'] = $index;
            } elseif (str_contains($normalized, 'sekolah') || str_contains($normalized, 'asalsekolah')) {
                $map['school_name'] = $index;
            } elseif (str_contains($normalized, 'kelas')) {
                $map['student_class'] = $index;
            } elseif (str_contains($normalized, 'jurusan')) {
                $map['student_major'] = $index;
            } elseif (str_contains($normalized, 'hpsiswa') || str_contains($normalized, 'wasiswa') || str_contains($normalized, 'nohpsiswa') || str_contains($normalized, 'teleponsiswa')) {
                $map['student_phone'] = $index;
            } elseif (str_contains($normalized, 'email')) {
                $map['student_email'] = $index;
            } elseif (str_contains($normalized, 'medsossiswa') || str_contains($normalized, 'sosmedsiswa')) {
                $map['student_social_media'] = $index;
            } elseif (str_contains($normalized, 'namawali') || str_contains($normalized, 'orangtua') || str_contains($normalized, 'wali')) {
                $map['guardian_name'] = $index;
            } elseif (str_contains($normalized, 'hpwali') || str_contains($normalized, 'wawali') || str_contains($normalized, 'nohpwali')) {
                $map['guardian_phone'] = $index;
            } elseif (str_contains($normalized, 'medsoswali') || str_contains($normalized, 'sosmedwali')) {
                $map['guardian_social_media'] = $index;
            } elseif (str_contains($normalized, 'beasiswa') || str_contains($normalized, 'minatbeasiswa')) {
                $map['scholarship_interest'] = $index;
            } elseif (str_contains($normalized, 'event') || str_contains($normalized, 'kegiatan') || str_contains($normalized, 'refno')) {
                $map['event_ref'] = $index;
            }
        }

        return $map;
    }
}
