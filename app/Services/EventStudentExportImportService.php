<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolEventProposal;
use App\Models\StudentQuestionnaire;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

                $payload = [
                    'school_id' => $schoolId,
                    'school_event_proposal_id' => $eventProposalId,
                    'school_name' => $schoolName,
                    'student_name' => $values['student_name'],
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
            $sheet->mergeCells('A2:O2');
            $sheet->getStyle('A2')->applyFromArray([
                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '475569']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ]);
            $sheet->getRowDimension(2)->setRowHeight(20);

            $headers = [
                'No',
                'Waktu Daftar',
                'Nama Lengkap Siswa',
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
                $sheet->setCellValue('C' . $currentRow, $s->student_name);
                $sheet->setCellValue('D' . $currentRow, $s->school_display_name ?: '-');
                $sheet->setCellValue('E' . $currentRow, $s->student_class ?: '-');
                $sheet->setCellValue('F' . $currentRow, $s->student_major ?: '-');
                $sheet->setCellValueExplicit('G' . $currentRow, (string)$s->student_phone, DataType::TYPE_STRING);
                $sheet->setCellValue('H' . $currentRow, $s->student_email ?: '-');
                $sheet->setCellValue('I' . $currentRow, $s->student_social_media ?: '-');
                $sheet->setCellValue('J' . $currentRow, $s->guardian_name ?: '-');
                $sheet->setCellValueExplicit('K' . $currentRow, (string)$s->guardian_phone, DataType::TYPE_STRING);
                $sheet->setCellValue('L' . $currentRow, $s->guardian_social_media ?: '-');
                $sheet->setCellValue('M' . $currentRow, $scholarshipDisplay);
                $sheet->setCellValue('N' . $currentRow, $eventDisplay);
                $sheet->setCellValue('O' . $currentRow, $statusDisplay);

                // Alternating zebra row background
                $rowFillColor = ($idx % 2 === 0) ? 'FFFFFF' : 'F8FAFC';
                $sheet->getStyle("A{$currentRow}:O{$currentRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowFillColor]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']]],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                ]);

                // Highlight Minat Beasiswa
                if ($s->scholarship_interest === 'ya') {
                    $sheet->getStyle('M' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB('065F46'); // Emerald
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
