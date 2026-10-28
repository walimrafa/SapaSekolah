<?php

namespace App\Services;

use App\Models\Prospect;
use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentImportService
{
    public function __construct(
        protected SchoolScoringService $scoringService
    ) {}

    /**
     * Import admitted UBSI students and associate them with their originating schools.
     */
    public function import(UploadedFile $file): array
    {
        $reader = IOFactory::createReaderForFile($file->getRealPath());
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2 || empty(array_filter($rows[0] ?? [], fn ($value) => trim((string) $value) !== ''))) {
            return ['status' => false, 'message' => 'File Excel kosong atau tidak memiliki baris header.'];
        }

        $headerMap = $this->mapHeaders($rows[0]);
        if (!isset($headerMap['nim'], $headerMap['name']) ||
            (!isset($headerMap['school_npsn']) && !isset($headerMap['school_name']))) {
            return [
                'status' => false,
                'message' => 'Header wajib: NIM, Nama Mahasiswa, dan NPSN atau Sekolah Asal.',
            ];
        }

        $createdCount = 0;
        $updatedCount = 0;
        $rowErrors = [];
        $skippedCount = 0;

        DB::transaction(function () use ($rows, $headerMap, &$createdCount, &$updatedCount, &$rowErrors, &$skippedCount) {
            foreach (array_slice($rows, 1, null, true) as $index => $row) {
                $values = [];
                foreach ($headerMap as $field => $column) {
                    $values[$field] = trim((string) ($row[$column] ?? ''));
                }

                if (count(array_filter($values, fn ($value) => $value !== '')) === 0) {
                    continue;
                }

                $rowNumber = $index + 1;
                if ($values['nim'] === '' || $values['name'] === '') {
                    $this->addRowError($rowErrors, $skippedCount, $rowNumber, 'NIM dan nama mahasiswa wajib diisi.');
                    continue;
                }

                $school = $this->findSchool($values['school_npsn'] ?? '', $values['school_name'] ?? '');
                if (!$school) {
                    $schoolLabel = ($values['school_npsn'] ?? '') ?: ($values['school_name'] ?? '');
                    $this->addRowError($rowErrors, $skippedCount, $rowNumber, "Sekolah asal tidak ditemukan ({$schoolLabel}).");
                    continue;
                }

                $data = [
                    'name' => $values['name'],
                    'school_id' => $school->id,
                    'nim' => $values['nim'],
                    'gender' => $this->normalizeGender($values['gender'] ?? ''),
                    'phone' => ($values['phone'] ?? '') !== '' ? $values['phone'] : null,
                    'email' => ($values['email'] ?? '') !== '' ? $values['email'] : null,
                    'graduation_year' => $this->normalizeGraduationYear($values['graduation_year'] ?? ''),
                    'interest_program' => ($values['interest_program'] ?? '') !== '' ? $values['interest_program'] : null,
                    'study_time' => ($values['study_time'] ?? '') !== '' ? $values['study_time'] : null,
                    'scholarship_type' => ($values['scholarship_type'] ?? '') !== '' ? $values['scholarship_type'] : null,
                    'source' => 'import_excel',
                    'status' => 'mahasiswa',
                ];
                $data = array_filter($data, fn ($value) => $value !== null);

                $validator = Validator::make($data, [
                    'name' => ['required', 'string', 'max:100'],
                    'nim' => ['required', 'string', 'max:30'],
                    'gender' => ['nullable', 'in:L,P'],
                    'phone' => ['nullable', 'string', 'max:20'],
                    'email' => ['nullable', 'email', 'max:100'],
                    'graduation_year' => ['nullable', 'integer', 'between:1900,2100'],
                    'interest_program' => ['nullable', 'string', 'max:100'],
                    'study_time' => ['nullable', 'string', 'max:50'],
                    'scholarship_type' => ['nullable', 'string', 'max:100'],
                ]);

                if ($validator->fails()) {
                    $this->addRowError($rowErrors, $skippedCount, $rowNumber, $validator->errors()->first());
                    continue;
                }

                $prospect = Prospect::updateOrCreate(
                    ['nim' => $data['nim']],
                    $validator->validated()
                );

                $prospect->wasRecentlyCreated ? $createdCount++ : $updatedCount++;
            }
        });

        if ($createdCount + $updatedCount > 0) {
            $this->scoringService->recalculateAllSchoolScores();
        }

        return [
            'status' => $createdCount + $updatedCount > 0,
            'created' => $createdCount,
            'updated' => $updatedCount,
            'skipped' => $skippedCount,
            'errors' => $rowErrors,
            'message' => $createdCount + $updatedCount > 0
                ? "Import selesai: {$createdCount} mahasiswa baru, {$updatedCount} data diperbarui, {$skippedCount} baris dilewati."
                : 'Tidak ada data mahasiswa yang berhasil diimpor.',
        ];
    }

    private function mapHeaders(array $headers): array
    {
        $aliases = [
            'nim' => ['nim', 'nomorindukmahasiswa', 'noindukmahasiswa', 'studentid'],
            'name' => ['nama', 'namamahasiswa', 'namalengkap', 'name', 'studentname'],
            'school_npsn' => ['npsn', 'npsnasalsekolah', 'npsnsekolah'],
            'school_name' => ['sekolahasal', 'namasekolahasal', 'namasekolah', 'asalsekolah', 'schoolname'],
            'gender' => ['jeniskelamin', 'gender'],
            'phone' => ['nohp', 'nomorhp', 'telepon', 'phone', 'whatsapp'],
            'email' => ['email', 'surel'],
            'graduation_year' => ['tahunlulus', 'tahunkelulusan', 'graduationyear'],
            'interest_program' => ['prodi', 'programstudi', 'programstudiubsi', 'interestprogram'],
            'study_time' => ['waktukuliah', 'shift', 'shiftkuliah', 'studytime', 'kelaskuliah', 'kelas'],
            'scholarship_type' => ['beasiswa', 'kategoribeasiswa', 'jenisbeasiswa', 'jalurbeasiswa', 'programbeasiswa', 'scholarshiptype', 'scholarship'],
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

    private function normalizeGender(string $gender): ?string
    {
        return match (strtoupper(trim($gender))) {
            'L', 'LAKI-LAKI', 'LAKI LAKI', 'MALE' => 'L',
            'P', 'PEREMPUAN', 'FEMALE' => 'P',
            '' => null,
            default => strtoupper(trim($gender)),
        };
    }

    private function normalizeGraduationYear(string $year): ?int
    {
        return $year === '' ? null : (int) $year;
    }

    private function addRowError(array &$errors, int &$skipped, int $rowNumber, string $message): void
    {
        $skipped++;
        if (count($errors) < 10) {
            $errors[] = "Baris {$rowNumber}: {$message}";
        }
    }
}
