<?php

namespace App\Services;

use App\Models\School;
use App\Models\StudentQuestionnaire;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentQuestionnaireImportService
{
    /**
     * Import manual student questionnaire data.
     */
    public function import(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2 || empty(array_filter($rows[0] ?? [], fn ($value) => trim((string) $value) !== ''))) {
            return [
                'status' => false,
                'message' => 'File Excel kosong atau tidak memiliki baris header.',
            ];
        }

        $headerMap = $this->mapHeaders($rows[0]);

        if (!isset($headerMap['student_name']) || !isset($headerMap['student_phone'])) {
            return [
                'status' => false,
                'message' => 'Kolom header wajib minimal: "Nama Siswa" dan "No HP Siswa".',
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

                // Skip purely empty lines
                if (count(array_filter($values, fn ($v) => $v !== '')) === 0) {
                    continue;
                }

                $rowNumber = $index + 1;

                if (empty($values['student_name']) || empty($values['student_phone'])) {
                    $this->addRowError($rowErrors, $skippedCount, $rowNumber, 'Nama Siswa dan No HP wajib diisi.');
                    continue;
                }

                $cleanPhone = preg_replace('/[^0-9]/', '', $values['student_phone']);
                if (strlen($cleanPhone) < 7) {
                    $this->addRowError($rowErrors, $skippedCount, $rowNumber, "Nomor HP siswa '{$values['student_phone']}' tidak valid.");
                    continue;
                }

                $scholarshipInterest = $this->normalizeScholarshipInterest($values['scholarship_interest'] ?? '');
                $followupStatus = $this->normalizeFollowupStatus($values['followup_status'] ?? '');

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

                $payload = [
                    'school_id' => $schoolId,
                    'school_name' => $schoolName,
                    'student_name' => $values['student_name'],
                    'student_class' => $values['student_class'] ?? null,
                    'student_major' => $values['student_major'] ?? null,
                    'student_phone' => $values['student_phone'],
                    'student_email' => !empty($values['student_email']) ? $values['student_email'] : null,
                    'student_social_media' => $values['student_social_media'] ?? null,
                    'guardian_name' => $values['guardian_name'] ?? null,
                    'guardian_phone' => $values['guardian_phone'] ?? null,
                    'guardian_social_media' => $values['guardian_social_media'] ?? null,
                    'scholarship_interest' => $scholarshipInterest,
                    'followup_status' => $followupStatus,
                    'college_interest_notes' => $values['college_interest_notes'] ?? null,
                    'scholarship_notes' => $values['scholarship_notes'] ?? null,
                    'followup_notes' => $values['followup_notes'] ?? null,
                    'answers' => [],
                ];

                // Check if existing student by phone
                $existing = StudentQuestionnaire::where('student_phone', $payload['student_phone'])->first();

                if ($existing) {
                    // Update details preserving existing answers if any
                    $updateData = array_filter($payload, fn ($v) => $v !== null && $v !== '');
                    unset($updateData['answers']);
                    $existing->update($updateData);
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
                'message' => 'Tidak ada data yang berhasil diimpor. Periksa daftar error baris.',
                'errors' => $rowErrors,
            ];
        }

        $msg = "Berhasil memproses {$totalProcessed} data siswa ({$createdCount} data baru, {$updatedCount} data diperbarui).";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} baris dilewati karena format tidak sesuai).";
        }

        return [
            'status' => true,
            'message' => $msg,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => $rowErrors,
        ];
    }

    /**
     * Download Excel template for manual student questionnaire import.
     */
    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Template Import Siswa');

            $headers = [
                'Nama Lengkap Siswa *',
                'No. HP Siswa *',
                'Asal Sekolah',
                'Kelas',
                'Jurusan',
                'Minat Beasiswa UBSI (Ya / Tidak)',
                'Minat Program Studi / Kuliah',
                'Catatan Beasiswa yang Diminati',
                'Email Siswa',
                'Nama Orang Tua / Wali',
                'No. HP Wali',
                'Akun Medsos Siswa',
                'Status Awal Follow Up',
                'Catatan Tambahan',
            ];

            foreach ($headers as $index => $header) {
                $coord = Coordinate::stringFromColumnIndex($index + 1) . '1';
                $sheet->setCellValueExplicit($coord, $header, DataType::TYPE_STRING);
            }

            // Header Style
            $highestCol = Coordinate::stringFromColumnIndex(count($headers));
            $sheet->getStyle("A1:{$highestCol}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle("A1:{$highestCol}1")->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF1E293B'); // Slate 800

            // Sample Rows for demonstration
            $sampleRows = [
                [
                    'Muhammad Rizky Pratama',
                    '081234567890',
                    'SMAN 1 Karawang',
                    'XII IPA 2',
                    'IPA',
                    'Ya',
                    'S1 Sistem Informasi',
                    'Minat Beasiswa Prestasi Akademik',
                    'rizky.pratama@gmail.com',
                    'Bambang Susanto',
                    '081298765432',
                    'IG: @rizky_prt',
                    'Belum Dihubungi',
                    'Isi form manual saat kunjungan pameran kampus',
                ],
                [
                    'Siti Nurhaliza',
                    '085712345678',
                    'SMKN 1 Karawang',
                    'XII RPL 1',
                    'Rekayasa Perangkat Lunak',
                    'Ya',
                    'S1 Teknologi Informasi',
                    'Sangat berminat program KIP-Kuliah',
                    'siti.nur@gmail.com',
                    'Hj. Aminah',
                    '085698741235',
                    'TikTok: @sitinur',
                    'Berminat',
                    'Antusias tanya jadwal pendaftaran beasiswa',
                ],
                [
                    'Doni Prasetyo',
                    '087812345678',
                    'SMAN 2 Cikampek',
                    'XII IPS 3',
                    'IPS',
                    'Tidak',
                    'S1 Manajemen Bisnis',
                    '',
                    'doni.pras@gmail.com',
                    'Waryono',
                    '087898745612',
                    '',
                    'Belum Dihubungi',
                    'Fokus jalur reguler',
                ],
            ];

            $rowNumber = 2;
            foreach ($sampleRows as $sampleRow) {
                foreach ($sampleRow as $colIdx => $val) {
                    $coord = Coordinate::stringFromColumnIndex($colIdx + 1) . $rowNumber;
                    $sheet->setCellValueExplicit($coord, (string) $val, DataType::TYPE_STRING);
                }
                $rowNumber++;
            }

            // Auto-size columns
            foreach (range(1, count($headers)) as $col) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
            $sheet->freezePane('A2');

            // Sheet 2: Petunjuk Pengisian
            $instructionSheet = $spreadsheet->createSheet();
            $instructionSheet->setTitle('Petunjuk Pengisian');
            $instructions = [
                ['PANDUAN IMPORT DATA SISWA KUISIONER MANUAL'],
                ['1. Kolom dengan tanda bintang (*) wajib diisi: Nama Lengkap Siswa dan No. HP Siswa.'],
                ['2. Kolom "Minat Beasiswa UBSI" dapat diisi "Ya" atau "Tidak". Jika dikosongkan, otomatis bernilai "Ya".'],
                ['3. Kolom "Status Awal Follow Up" dapat diisi salah satu dari: Belum Dihubungi, No Respon, Respon, Berminat, Daftar Aplikasi. Default: Belum Dihubungi.'],
                ['4. Kolom nomor HP akan disanitasi otomatis untuk kemudahan klik chat WhatsApp langsung.'],
                ['5. Jika nomor HP siswa sudah ada sebelumnya di sistem, data profilnya akan diperbarui.'],
                ['6. Simpan file dengan format .xlsx atau .xls sebelum mengunggah.'],
            ];
            $instructionSheet->fromArray($instructions, null, 'A1');
            $instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
            $instructionSheet->getColumnDimension('A')->setAutoSize(true);

            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'template-import-siswa-kuisioner.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function mapHeaders(array $headerRow): array
    {
        $map = [];

        foreach ($headerRow as $index => $colName) {
            $normalized = strtolower(trim((string) $colName));
            if ($normalized === '') {
                continue;
            }

            $isGuardian = str_contains($normalized, 'wali') || str_contains($normalized, 'orang tua') || str_contains($normalized, 'ortu') || str_contains($normalized, 'guardian');

            if ($isGuardian) {
                if (!isset($map['guardian_phone']) && (str_contains($normalized, 'hp') || str_contains($normalized, 'telp') || str_contains($normalized, 'telepon') || str_contains($normalized, 'phone') || str_contains($normalized, 'wa'))) {
                    $map['guardian_phone'] = $index;
                } elseif (!isset($map['guardian_social_media']) && (str_contains($normalized, 'medsos') || str_contains($normalized, 'sosial media') || str_contains($normalized, 'social'))) {
                    $map['guardian_social_media'] = $index;
                } elseif (!isset($map['guardian_name']) && (str_contains($normalized, 'nama') || str_contains($normalized, 'wali') || str_contains($normalized, 'orang tua'))) {
                    $map['guardian_name'] = $index;
                }
                continue;
            }

            // Student specific fields
            if (!isset($map['student_phone']) && (
                str_contains($normalized, 'no. hp') ||
                str_contains($normalized, 'no hp') ||
                str_contains($normalized, 'nomor hp') ||
                str_contains($normalized, 'no telp') ||
                str_contains($normalized, 'telepon') ||
                str_contains($normalized, 'phone') ||
                str_contains($normalized, 'whatsapp') ||
                str_contains($normalized, 'no wa') ||
                str_contains($normalized, 'nomor wa') ||
                str_contains($normalized, 'student_phone')
            )) {
                $map['student_phone'] = $index;
                continue;
            }

            if (!isset($map['student_name']) && (
                str_contains($normalized, 'nama lengkap') ||
                str_contains($normalized, 'nama siswa') ||
                str_contains($normalized, 'student_name') ||
                str_contains($normalized, 'nama')
            )) {
                $map['student_name'] = $index;
                continue;
            }

            if (!isset($map['school_name']) && (
                str_contains($normalized, 'asal sekolah') ||
                str_contains($normalized, 'sekolah') ||
                str_contains($normalized, 'school')
            )) {
                $map['school_name'] = $index;
                continue;
            }

            if (!isset($map['student_class']) && (
                str_contains($normalized, 'kelas') ||
                str_contains($normalized, 'tingkat') ||
                str_contains($normalized, 'class') ||
                str_contains($normalized, 'student_class')
            )) {
                $map['student_class'] = $index;
                continue;
            }

            if (!isset($map['student_major']) && (
                str_contains($normalized, 'jurusan') ||
                str_contains($normalized, 'peminatan') ||
                str_contains($normalized, 'major') ||
                str_contains($normalized, 'student_major')
            )) {
                $map['student_major'] = $index;
                continue;
            }

            if (!isset($map['scholarship_interest']) && (
                str_contains($normalized, 'minat beasiswa') ||
                str_contains($normalized, 'beasiswa ubsi') ||
                (str_contains($normalized, 'beasiswa') && !str_contains($normalized, 'catatan')) ||
                str_contains($normalized, 'scholarship_interest')
            )) {
                $map['scholarship_interest'] = $index;
                continue;
            }

            if (!isset($map['scholarship_notes']) && (
                str_contains($normalized, 'catatan beasiswa') ||
                str_contains($normalized, 'jenis beasiswa') ||
                str_contains($normalized, 'beasiswa yang diminati') ||
                str_contains($normalized, 'scholarship_notes')
            )) {
                $map['scholarship_notes'] = $index;
                continue;
            }

            if (!isset($map['college_interest_notes']) && (
                str_contains($normalized, 'minat program studi') ||
                str_contains($normalized, 'minat prodi') ||
                str_contains($normalized, 'minat kuliah') ||
                str_contains($normalized, 'program studi') ||
                str_contains($normalized, 'prodi') ||
                str_contains($normalized, 'college_interest')
            )) {
                $map['college_interest_notes'] = $index;
                continue;
            }

            if (!isset($map['student_email']) && (
                str_contains($normalized, 'email')
            )) {
                $map['student_email'] = $index;
                continue;
            }

            if (!isset($map['student_social_media']) && (
                str_contains($normalized, 'medsos') ||
                str_contains($normalized, 'sosial media') ||
                str_contains($normalized, 'instagram') ||
                str_contains($normalized, 'social_media')
            )) {
                $map['student_social_media'] = $index;
                continue;
            }

            if (!isset($map['followup_status']) && (
                str_contains($normalized, 'status awal') ||
                str_contains($normalized, 'status follow') ||
                str_contains($normalized, 'kriteria') ||
                str_contains($normalized, 'followup_status')
            )) {
                $map['followup_status'] = $index;
                continue;
            }

            if (!isset($map['followup_notes']) && (
                str_contains($normalized, 'catatan tambahan') ||
                str_contains($normalized, 'catatan follow up') ||
                str_contains($normalized, 'keterangan') ||
                str_contains($normalized, 'catatan') ||
                str_contains($normalized, 'notes')
            )) {
                $map['followup_notes'] = $index;
                continue;
            }
        }

        return $map;
    }

    private function normalizeScholarshipInterest(string $val): string
    {
        $clean = strtolower(trim($val));
        if ($clean === '' || in_array($clean, ['ya', 'y', '1', 'true', 'mau', 'minat', 'yes', 'bisa', 'tertarik'])) {
            return 'ya';
        }
        if (in_array($clean, ['tidak', 't', '0', 'false', 'no', 'gak', 'enggak', 'tdk'])) {
            return 'tidak';
        }
        return 'ya';
    }

    private function normalizeFollowupStatus(string $val): string
    {
        $clean = strtolower(trim($val));
        if (str_contains($clean, 'no') || str_contains($clean, 'tidak respon') || str_contains($clean, 'missed')) {
            return StudentQuestionnaire::STATUS_NO_RESPON;
        }
        if (str_contains($clean, 'closing') || str_contains($clean, 'daftar') || str_contains($clean, 'deal') || str_contains($clean, 'konfirmasi')) {
            return StudentQuestionnaire::STATUS_CLOSING;
        }
        if (str_contains($clean, 'minat') || str_contains($clean, 'tertarik') || str_contains($clean, 'positif')) {
            return StudentQuestionnaire::STATUS_BERMINAT;
        }
        if (str_contains($clean, 'respon') || str_contains($clean, 'jawab') || str_contains($clean, 'diskusi')) {
            return StudentQuestionnaire::STATUS_RESPON;
        }
        return StudentQuestionnaire::STATUS_BELUM_DIHUBUNGI;
    }

    private function addRowError(array &$rowErrors, int &$skippedCount, int $rowNumber, string $message): void
    {
        $skippedCount++;
        $rowErrors[] = "Baris {$rowNumber}: {$message}";
    }
}
