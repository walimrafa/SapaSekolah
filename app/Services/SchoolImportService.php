<?php

namespace App\Services;

use App\Models\ContactPosition;
use App\Models\District;
use App\Models\School;
use App\Models\SchoolContact;
use App\Models\SchoolType;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class SchoolImportService
{
    public function __construct(
        protected SchoolScoringService $scoringService
    ) {}

    /**
     * Import schools from CSV/Excel file stream.
     */
    public function import(UploadedFile $file): array
    {
        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            if ($reader instanceof Csv) {
                $handle = fopen($file->getRealPath(), 'r');
                if ($handle !== false) {
                    $firstLine = fgets($handle);
                    fclose($handle);
                    if ($firstLine !== false && substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                        $reader->setDelimiter(';');
                    }
                }
            }
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
            $spreadsheet->disconnectWorksheets();
        } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $exception) {
            return ['status' => false, 'message' => 'File Excel/CSV tidak dapat dibaca. Periksa format file.'];
        }

        $rawHeader = $rows[0] ?? null;
        if (!$rawHeader || count(array_filter($rawHeader, fn ($value) => trim((string) $value) !== '')) === 0) {
            return ['status' => false, 'message' => 'Format file kosong atau tidak valid.'];
        }

        // Map headers to standard field keys
        $headerMap = [];
        foreach ($rawHeader as $index => $col) {
            $cleaned = strtolower(trim(preg_replace('/[^a-zA-Z0-9_\s]/', '', $col)));
            
            if (in_array($cleaned, ['npsn'])) $headerMap['npsn'] = $index;
            elseif (in_array($cleaned, ['nama', 'nama sekolah', 'name', 'school_name'])) $headerMap['name'] = $index;
            elseif (in_array($cleaned, ['status', 'status kelembagaan'])) $headerMap['status'] = $index;
            elseif (in_array($cleaned, ['jenjang', 'jenis', 'school_type'])) $headerMap['school_type'] = $index;
            elseif (in_array($cleaned, ['kecamatan', 'district'])) $headerMap['district'] = $index;
            elseif (in_array($cleaned, ['alamat', 'address'])) $headerMap['address'] = $index;
            elseif (in_array($cleaned, ['latitude', 'lat', 'lintang'])) $headerMap['latitude'] = $index;
            elseif (in_array($cleaned, ['longitude', 'lng', 'bujur'])) $headerMap['longitude'] = $index;
            elseif (in_array($cleaned, ['jumlah siswa', 'student_count', 'total_siswa'])) $headerMap['student_count'] = $index;
            elseif (in_array($cleaned, ['siswa kelas 12', 'siswa xii', 'grade_12_count', 'rumbel_xii'])) $headerMap['grade_12_count'] = $index;
            elseif (in_array($cleaned, ['nama guru bk', 'guru bk', 'contact_name', 'nama pic'])) $headerMap['contact_name'] = $index;
            elseif (in_array($cleaned, ['no hp bk', 'no hp', 'telepon bk', 'contact_phone', 'whatsapp'])) $headerMap['contact_phone'] = $index;
        }

        if (!isset($headerMap['name'])) {
            return ['status' => false, 'message' => 'Header file Excel harus memiliki kolom "Nama Sekolah".'];
        }

        if (!isset($headerMap['npsn'])) {
            return ['status' => false, 'message' => 'Header file Excel harus memiliki kolom "NPSN".'];
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $rowErrors = [];
        $bkPosition = ContactPosition::where('name', 'like', '%BK%')->first();
        $positionId = $bkPosition?->id;

        foreach (array_slice($rows, 1, null, true) as $index => $row) {
            if (count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rowNumber = $index + 1;
            $name = trim((string) ($row[$headerMap['name']] ?? ''));
            $npsn = trim((string) ($row[$headerMap['npsn']] ?? ''));
            if ($name === '' || $npsn === '') {
                $this->addRowError($rowErrors, $skippedCount, $rowNumber, 'NPSN dan Nama Sekolah wajib diisi.');
                continue;
            }

            $statusRaw = isset($headerMap['status']) ? strtolower(trim((string) ($row[$headerMap['status']] ?? ''))) : '';
            $status = str_contains($statusRaw, 'negeri') ? 'negeri' : 'swasta';

            // Find District
            $districtId = null;
            if (isset($headerMap['district'])) {
                $dName = trim((string) ($row[$headerMap['district']] ?? ''));
                $district = District::where('name', 'like', "%{$dName}%")->first();
                if ($district) $districtId = $district->id;
            }

            // Find School Type
            $schoolTypeId = null;
            if (isset($headerMap['school_type'])) {
                $stName = strtoupper(trim((string) ($row[$headerMap['school_type']] ?? '')));
                $st = SchoolType::where('name', 'like', "%{$stName}%")->first();
                if ($st) $schoolTypeId = $st->id;
            }

            $latValue = isset($headerMap['latitude']) ? trim((string) ($row[$headerMap['latitude']] ?? '')) : '';
            $lngValue = isset($headerMap['longitude']) ? trim((string) ($row[$headerMap['longitude']] ?? '')) : '';
            if (($latValue === '') !== ($lngValue === '')) {
                $this->addRowError($rowErrors, $skippedCount, $rowNumber, 'Latitude dan longitude harus diisi berpasangan.');
                continue;
            }
            if (($latValue !== '' && !is_numeric(str_replace(',', '.', $latValue))) ||
                ($lngValue !== '' && !is_numeric(str_replace(',', '.', $lngValue)))) {
                $this->addRowError($rowErrors, $skippedCount, $rowNumber, 'Koordinat latitude/longitude harus berupa angka.');
                continue;
            }
            $lat = $latValue !== '' ? (float) str_replace(',', '.', $latValue) : null;
            $lng = $lngValue !== '' ? (float) str_replace(',', '.', $lngValue) : null;
            if (($latValue !== '' && ($lat < -90 || $lat > 90)) ||
                ($lngValue !== '' && ($lng < -180 || $lng > 180))) {
                $this->addRowError($rowErrors, $skippedCount, $rowNumber, 'Koordinat latitude/longitude di luar rentang yang valid.');
                continue;
            }
            
            $distFromCampus = null;
            if ($lat !== null && $lng !== null) {
                $distFromCampus = $this->scoringService->calculateDistanceFromCampus($lat, $lng);
            }

            $address = isset($headerMap['address']) ? trim((string) ($row[$headerMap['address']] ?? '')) : null;
            $studentCountValue = isset($headerMap['student_count']) ? trim((string) ($row[$headerMap['student_count']] ?? '')) : '';
            $grade12CountValue = isset($headerMap['grade_12_count']) ? trim((string) ($row[$headerMap['grade_12_count']] ?? '')) : '';
            if (($studentCountValue !== '' && !ctype_digit($studentCountValue)) ||
                ($grade12CountValue !== '' && !ctype_digit($grade12CountValue))) {
                $this->addRowError($rowErrors, $skippedCount, $rowNumber, 'Jumlah siswa harus berupa bilangan bulat nol atau lebih.');
                continue;
            }
            $studentCount = $studentCountValue === '' ? 0 : (int) $studentCountValue;
            $grade12Count = $grade12CountValue === '' ? 0 : (int) $grade12CountValue;

            // Find or create school
            $school = School::withTrashed()
                ->where('npsn', $npsn)
                ->orWhere('name', 'like', "%{$name}%")
                ->first();

            $data = [
                'npsn' => $npsn,
                'name' => strtoupper($name),
                'status' => $status,
                'school_type_id' => $schoolTypeId ?? 1,
                'district_id' => $districtId ?? 1,
                'regency_id' => 1,
                'address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
                'distance_from_campus' => $distFromCampus,
                'student_count' => $studentCount,
                'grade_12_count' => $grade12Count,
                'status_school' => 'aktif',
            ];

            if ($school) {
                if ($school->trashed()) {
                    $school->restore();
                    $importedCount++;
                } else {
                    $updatedCount++;
                }
                $data['deleted_at'] = null;
                $school->update(array_filter($data, fn($v) => !is_null($v)));
            } else {
                $school = School::create($data);
                $importedCount++;
            }

            // Create Guru BK contact if present
            $contactName = isset($headerMap['contact_name']) ? trim((string) ($row[$headerMap['contact_name']] ?? '')) : '';
            $contactPhone = isset($headerMap['contact_phone']) ? trim((string) ($row[$headerMap['contact_phone']] ?? '')) : '';

            if ((!empty($contactName) || !empty($contactPhone)) && $positionId) {
                SchoolContact::updateOrCreate(
                    ['school_id' => $school->id, 'name' => $contactName ?: 'Guru BK'],
                    [
                        'position_id' => $positionId,
                        'phone' => $contactPhone,
                        'whatsapp' => $contactPhone,
                        'is_primary' => true,
                    ]
                );
            }
        }

        // Recalculate Priority Scores
        if ($importedCount + $updatedCount > 0) {
            $this->scoringService->recalculateAllSchoolScores();
        }

        return [
            'status' => $importedCount + $updatedCount > 0,
            'errors' => $rowErrors,
            'message' => "Import Excel/CSV selesai: {$importedCount} sekolah baru, {$updatedCount} diperbarui, {$skippedCount} baris dilewati.",
        ];
    }

    private function addRowError(array &$errors, int &$skipped, int $rowNumber, string $message): void
    {
        $skipped++;
        if (count($errors) < 10) {
            $errors[] = "Baris {$rowNumber}: {$message}";
        }
    }
}
