<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolStudentIntake;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SchoolStudentIntakeImportService
{
    public function __construct(
        protected SchoolScoringService $scoringService
    ) {}

    public function import(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2 || empty(array_filter($rows[0] ?? [], fn ($value) => trim((string) $value) !== ''))) {
            return ['status' => false, 'message' => 'File Excel kosong atau tidak memiliki baris data.'];
        }

        $headerMap = $this->mapHeaders($rows[0]);
        if (!isset($headerMap['period']) ||
            (!isset($headerMap['npsn']) && !isset($headerMap['school_name'])) ||
            !isset($headerMap['count'])) {
            return [
                'status' => false,
                'message' => 'Header wajib: NPSN atau Nama Sekolah, Periode PMB, dan Jumlah Mahasiswa Baru.',
                'errors' => [],
            ];
        }

        $groupedCounts = [];
        $errors = [];
        $skipped = 0;
        $seenKeys = [];
        $duplicateKeys = [];

        foreach (array_slice($rows, 1, null, true) as $index => $row) {
            $values = [];
            foreach ($headerMap as $field => $column) {
                $values[$field] = trim((string) ($row[$column] ?? ''));
            }

            if (($values['period'] ?? '') === '' && ($values['count'] ?? '') === '') {
                continue;
            }

            $rowNumber = $index + 1;
            $school = $this->findSchool($values['npsn'] ?? '', $values['school_name'] ?? '');
            if (!$school) {
                $schoolLabel = ($values['npsn'] ?? '') ?: ($values['school_name'] ?? '');
                $this->addError($errors, $skipped, $rowNumber, "Sekolah tidak ditemukan ({$schoolLabel}).");
                continue;
            }

            $period = $values['period'];
            if ($period === '' || mb_strlen($period) > 30) {
                $this->addError($errors, $skipped, $rowNumber, 'Periode PMB wajib diisi dan maksimal 30 karakter.');
                continue;
            }

            $countValue = $values['count'] ?? '';
            $count = $this->parseCount($countValue);
            if ($count === null) {
                $this->addError($errors, $skipped, $rowNumber, 'Jumlah mahasiswa baru harus berupa bilangan bulat nol atau lebih.');
                continue;
            }

            $key = $school->id . '|' . mb_strtolower($period);
            if (isset($duplicateKeys[$key])) {
                continue;
            }
            if (isset($seenKeys[$key])) {
                unset($groupedCounts[$key]);
                $duplicateKeys[$key] = true;
                $this->addError($errors, $skipped, $rowNumber, 'Sekolah dan periode PMB tercantum lebih dari sekali; gunakan satu baris rekap per sekolah/periode.');
                continue;
            }
            $seenKeys[$key] = true;
            $groupedCounts[$key] = [
                'school_id' => $school->id,
                'intake_period' => $period,
                'student_count' => $count,
            ];
        }

        if ($groupedCounts === []) {
            return [
                'status' => false,
                'message' => 'Tidak ada baris data valid untuk diimpor.',
                'errors' => $errors,
                'skipped' => $skipped,
            ];
        }

        DB::transaction(function () use ($groupedCounts) {
            foreach ($groupedCounts as $data) {
                SchoolStudentIntake::updateOrCreate(
                    [
                        'school_id' => $data['school_id'],
                        'intake_period' => $data['intake_period'],
                    ],
                    ['student_count' => $data['student_count']]
                );
            }
        });

        $this->scoringService->recalculateAllSchoolScores();

        return [
            'status' => true,
            'imported' => count($groupedCounts),
            'skipped' => $skipped,
            'errors' => $errors,
            'message' => 'Data mahasiswa baru berhasil diimpor untuk ' . count($groupedCounts) .
                ' sekolah/periode. ' . $skipped . ' baris dilewati. Skor prioritas sudah diperbarui.',
        ];
    }

    private function mapHeaders(array $headers): array
    {
        $aliases = [
            'npsn' => ['npsn', 'npsnasalsekolah', 'npsnsekolah'],
            'school_name' => ['namasekolah', 'namasekolahasal', 'sekolahasal', 'asalsekolah', 'asalsma', 'asalsmk', 'schoolname'],
            'period' => ['periode', 'periodepmb', 'periodepenerimaan', 'angkatan', 'tahunajaran', 'tahunmasuk', 'tahunpmb', 'intakeperiod'],
            'count' => ['jumlah', 'jumlahmahasiswa', 'jumlahmahasiswabaru', 'jumlahmaba', 'mahasiswabaru', 'totalmahasiswa', 'totalmaba', 'studentcount'],
        ];
        $headerMap = [];

        foreach ($headers as $index => $header) {
            $normalized = strtolower((string) preg_replace('/[^a-zA-Z0-9]/', '', trim((string) $header)));
            foreach ($aliases as $field => $fieldAliases) {
                if (in_array($normalized, $fieldAliases, true)) {
                    $headerMap[$field] = $index;
                    break;
                }
            }
        }

        return $headerMap;
    }

    private function findSchool(string $npsn, string $name): ?School
    {
        if ($npsn !== '') {
            $school = School::where('npsn', $npsn)->first();
            if ($school) {
                return $school;
            }
        }

        if ($name !== '') {
            return School::whereRaw('LOWER(TRIM(name)) = ?', [mb_strtolower($name)])->first();
        }

        return null;
    }

    private function parseCount(string $value): ?int
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        if (preg_match('/^\d+$/', $value)) {
            return strlen($value) <= 9 ? (int) $value : null;
        }

        if (!preg_match('/^\d{1,3}(?:[.,\s]\d{3})+$/', $value)) {
            return null;
        }

        $digits = preg_replace('/[.,\s]/', '', $value);
        if ($digits === '' || strlen($digits) > 9) {
            return null;
        }

        return (int) $digits;
    }

    private function addError(array &$errors, int &$skipped, int $rowNumber, string $message): void
    {
        $skipped++;
        if (count($errors) < 10) {
            $errors[] = "Baris {$rowNumber}: {$message}";
        }
    }
}
