<?php

namespace App\Services;

use App\Models\Accreditation;
use App\Models\ActivityLog;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\School;
use App\Models\SchoolPotential;
use App\Models\SchoolType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolIntegrationService
{
    public function __construct(
        protected SchoolScoringService $scoringService
    ) {}

    /**
     * Curated list of Regencies (Kabupaten/Kota) with Kemendagri codes.
     */
    public function getAvailableRegencies(): array
    {
        return [
            // Jawa Barat
            ['code' => '3215', 'name' => 'Kabupaten Karawang', 'province' => 'Jawa Barat', 'kec_count' => 30],
            ['code' => '3216', 'name' => 'Kabupaten Bekasi', 'province' => 'Jawa Barat', 'kec_count' => 23],
            ['code' => '3275', 'name' => 'Kota Bekasi', 'province' => 'Jawa Barat', 'kec_count' => 12],
            ['code' => '3213', 'name' => 'Kabupaten Subang', 'province' => 'Jawa Barat', 'kec_count' => 30],
            ['code' => '3214', 'name' => 'Kabupaten Purwakarta', 'province' => 'Jawa Barat', 'kec_count' => 17],
            ['code' => '3201', 'name' => 'Kabupaten Bogor', 'province' => 'Jawa Barat', 'kec_count' => 40],
            ['code' => '3271', 'name' => 'Kota Bogor', 'province' => 'Jawa Barat', 'kec_count' => 6],
            ['code' => '3276', 'name' => 'Kota Depok', 'province' => 'Jawa Barat', 'kec_count' => 11],
            ['code' => '3204', 'name' => 'Kabupaten Bandung', 'province' => 'Jawa Barat', 'kec_count' => 31],
            ['code' => '3273', 'name' => 'Kota Bandung', 'province' => 'Jawa Barat', 'kec_count' => 30],
            ['code' => '3217', 'name' => 'Kabupaten Bandung Barat', 'province' => 'Jawa Barat', 'kec_count' => 16],
            ['code' => '3277', 'name' => 'Kota Cimahi', 'province' => 'Jawa Barat', 'kec_count' => 3],
            ['code' => '3203', 'name' => 'Kabupaten Cianjur', 'province' => 'Jawa Barat', 'kec_count' => 32],
            ['code' => '3202', 'name' => 'Kabupaten Sukabumi', 'province' => 'Jawa Barat', 'kec_count' => 47],
            ['code' => '3272', 'name' => 'Kota Sukabumi', 'province' => 'Jawa Barat', 'kec_count' => 7],
            ['code' => '3212', 'name' => 'Kabupaten Indramayu', 'province' => 'Jawa Barat', 'kec_count' => 31],
            ['code' => '3209', 'name' => 'Kabupaten Cirebon', 'province' => 'Jawa Barat', 'kec_count' => 40],
            ['code' => '3274', 'name' => 'Kota Cirebon', 'province' => 'Jawa Barat', 'kec_count' => 5],
            ['code' => '3210', 'name' => 'Kabupaten Majalengka', 'province' => 'Jawa Barat', 'kec_count' => 26],
            ['code' => '3208', 'name' => 'Kabupaten Kuningan', 'province' => 'Jawa Barat', 'kec_count' => 32],
            ['code' => '3211', 'name' => 'Kabupaten Sumedang', 'province' => 'Jawa Barat', 'kec_count' => 26],
            ['code' => '3205', 'name' => 'Kabupaten Garut', 'province' => 'Jawa Barat', 'kec_count' => 42],
            ['code' => '3206', 'name' => 'Kabupaten Tasikmalaya', 'province' => 'Jawa Barat', 'kec_count' => 39],
            ['code' => '3278', 'name' => 'Kota Tasikmalaya', 'province' => 'Jawa Barat', 'kec_count' => 10],
            ['code' => '3207', 'name' => 'Kabupaten Ciamis', 'province' => 'Jawa Barat', 'kec_count' => 27],
            ['code' => '3279', 'name' => 'Kota Banjar', 'province' => 'Jawa Barat', 'kec_count' => 4],
            ['code' => '3218', 'name' => 'Kabupaten Pangandaran', 'province' => 'Jawa Barat', 'kec_count' => 10],

            // DKI Jakarta
            ['code' => '3171', 'name' => 'Kota Jakarta Pusat', 'province' => 'DKI Jakarta', 'kec_count' => 8],
            ['code' => '3174', 'name' => 'Kota Jakarta Selatan', 'province' => 'DKI Jakarta', 'kec_count' => 10],
            ['code' => '3175', 'name' => 'Kota Jakarta Timur', 'province' => 'DKI Jakarta', 'kec_count' => 10],
            ['code' => '3173', 'name' => 'Kota Jakarta Barat', 'province' => 'DKI Jakarta', 'kec_count' => 8],
            ['code' => '3172', 'name' => 'Kota Jakarta Utara', 'province' => 'DKI Jakarta', 'kec_count' => 6],
            ['code' => '3101', 'name' => 'Kabupaten Kepulauan Seribu', 'province' => 'DKI Jakarta', 'kec_count' => 2],

            // Banten
            ['code' => '3603', 'name' => 'Kabupaten Tangerang', 'province' => 'Banten', 'kec_count' => 29],
            ['code' => '3671', 'name' => 'Kota Tangerang', 'province' => 'Banten', 'kec_count' => 13],
            ['code' => '3674', 'name' => 'Kota Tangerang Selatan', 'province' => 'Banten', 'kec_count' => 7],
            ['code' => '3604', 'name' => 'Kabupaten Serang', 'province' => 'Banten', 'kec_count' => 29],
            ['code' => '3673', 'name' => 'Kota Serang', 'province' => 'Banten', 'kec_count' => 6],
            ['code' => '3672', 'name' => 'Kota Cilegon', 'province' => 'Banten', 'kec_count' => 8],
            ['code' => '3602', 'name' => 'Kabupaten Lebak', 'province' => 'Banten', 'kec_count' => 28],
            ['code' => '3601', 'name' => 'Kabupaten Pandeglang', 'province' => 'Banten', 'kec_count' => 35],

            // Jawa Tengah
            ['code' => '3302', 'name' => 'Purwokerto (Kabupaten Banyumas)', 'province' => 'Jawa Tengah', 'kec_count' => 27],
            ['code' => '3374', 'name' => 'Kota Semarang', 'province' => 'Jawa Tengah', 'kec_count' => 16],
            ['code' => '3372', 'name' => 'Kota Surakarta (Solo)', 'province' => 'Jawa Tengah', 'kec_count' => 5],
            ['code' => '3301', 'name' => 'Kabupaten Cilacap', 'province' => 'Jawa Tengah', 'kec_count' => 24],
            ['code' => '3329', 'name' => 'Kabupaten Brebes', 'province' => 'Jawa Tengah', 'kec_count' => 17],
            ['code' => '3328', 'name' => 'Kabupaten Tegal', 'province' => 'Jawa Tengah', 'kec_count' => 18],
            ['code' => '3376', 'name' => 'Kota Tegal', 'province' => 'Jawa Tengah', 'kec_count' => 4],
            ['code' => '3319', 'name' => 'Kabupaten Kudus', 'province' => 'Jawa Tengah', 'kec_count' => 9],
            ['code' => '3320', 'name' => 'Kabupaten Jepara', 'province' => 'Jawa Tengah', 'kec_count' => 16],
            ['code' => '3318', 'name' => 'Kabupaten Pati', 'province' => 'Jawa Tengah', 'kec_count' => 21],
            ['code' => '3326', 'name' => 'Kabupaten Pekalongan', 'province' => 'Jawa Tengah', 'kec_count' => 19],
            ['code' => '3375', 'name' => 'Kota Pekalongan', 'province' => 'Jawa Tengah', 'kec_count' => 4],
            ['code' => '3308', 'name' => 'Kabupaten Magelang', 'province' => 'Jawa Tengah', 'kec_count' => 21],
            ['code' => '3371', 'name' => 'Kota Magelang', 'province' => 'Jawa Tengah', 'kec_count' => 3],
            ['code' => '3310', 'name' => 'Kabupaten Klaten', 'province' => 'Jawa Tengah', 'kec_count' => 26],
            ['code' => '3311', 'name' => 'Kabupaten Sukoharjo', 'province' => 'Jawa Tengah', 'kec_count' => 12],
            ['code' => '3313', 'name' => 'Kabupaten Karanganyar', 'province' => 'Jawa Tengah', 'kec_count' => 17],
            ['code' => '3312', 'name' => 'Kabupaten Wonogiri', 'province' => 'Jawa Tengah', 'kec_count' => 25],
            ['code' => '3309', 'name' => 'Kabupaten Boyolali', 'province' => 'Jawa Tengah', 'kec_count' => 22],
            ['code' => '3314', 'name' => 'Kabupaten Sragen', 'province' => 'Jawa Tengah', 'kec_count' => 20],
            ['code' => '3305', 'name' => 'Kabupaten Kebumen', 'province' => 'Jawa Tengah', 'kec_count' => 26],
            ['code' => '3306', 'name' => 'Kabupaten Purworejo', 'province' => 'Jawa Tengah', 'kec_count' => 16],
            ['code' => '3307', 'name' => 'Kabupaten Wonosobo', 'province' => 'Jawa Tengah', 'kec_count' => 15],
            ['code' => '3323', 'name' => 'Kabupaten Temanggung', 'province' => 'Jawa Tengah', 'kec_count' => 20],
            ['code' => '3324', 'name' => 'Kabupaten Kendal', 'province' => 'Jawa Tengah', 'kec_count' => 20],
            ['code' => '3325', 'name' => 'Kabupaten Batang', 'province' => 'Jawa Tengah', 'kec_count' => 15],
            ['code' => '3327', 'name' => 'Kabupaten Pemalang', 'province' => 'Jawa Tengah', 'kec_count' => 14],
            ['code' => '3321', 'name' => 'Kabupaten Demak', 'province' => 'Jawa Tengah', 'kec_count' => 14],
            ['code' => '3322', 'name' => 'Kabupaten Semarang', 'province' => 'Jawa Tengah', 'kec_count' => 19],
            ['code' => '3373', 'name' => 'Kota Salatiga', 'province' => 'Jawa Tengah', 'kec_count' => 4],
            ['code' => '3315', 'name' => 'Kabupaten Grobogan', 'province' => 'Jawa Tengah', 'kec_count' => 19],
            ['code' => '3316', 'name' => 'Kabupaten Blora', 'province' => 'Jawa Tengah', 'kec_count' => 16],
            ['code' => '3317', 'name' => 'Kabupaten Rembang', 'province' => 'Jawa Tengah', 'kec_count' => 14],
            ['code' => '3304', 'name' => 'Kabupaten Banjarnegara', 'province' => 'Jawa Tengah', 'kec_count' => 20],
            ['code' => '3303', 'name' => 'Kabupaten Purbalingga', 'province' => 'Jawa Tengah', 'kec_count' => 18],

            // Kalimantan Barat
            ['code' => '6171', 'name' => 'Kota Pontianak', 'province' => 'Kalimantan Barat', 'kec_count' => 6],
            ['code' => '6112', 'name' => 'Kabupaten Kubu Raya', 'province' => 'Kalimantan Barat', 'kec_count' => 9],
            ['code' => '6102', 'name' => 'Kabupaten Mempawah', 'province' => 'Kalimantan Barat', 'kec_count' => 9],
            ['code' => '6172', 'name' => 'Kota Singkawang', 'province' => 'Kalimantan Barat', 'kec_count' => 5],
            ['code' => '6101', 'name' => 'Kabupaten Sambas', 'province' => 'Kalimantan Barat', 'kec_count' => 19],
            ['code' => '6104', 'name' => 'Kabupaten Ketapang', 'province' => 'Kalimantan Barat', 'kec_count' => 20],
            ['code' => '6103', 'name' => 'Kabupaten Sanggau', 'province' => 'Kalimantan Barat', 'kec_count' => 15],
            ['code' => '6105', 'name' => 'Kabupaten Sintang', 'province' => 'Kalimantan Barat', 'kec_count' => 14],

            // DI Yogyakarta
            ['code' => '3471', 'name' => 'Kota Yogyakarta', 'province' => 'DI Yogyakarta', 'kec_count' => 14],
            ['code' => '3404', 'name' => 'Kabupaten Sleman', 'province' => 'DI Yogyakarta', 'kec_count' => 17],
            ['code' => '3402', 'name' => 'Kabupaten Bantul', 'province' => 'DI Yogyakarta', 'kec_count' => 17],
            ['code' => '3403', 'name' => 'Kabupaten Gunungkidul', 'province' => 'DI Yogyakarta', 'kec_count' => 18],
            ['code' => '3401', 'name' => 'Kabupaten Kulon Progo', 'province' => 'DI Yogyakarta', 'kec_count' => 12],

            // Jawa Timur
            ['code' => '3578', 'name' => 'Kota Surabaya', 'province' => 'Jawa Timur', 'kec_count' => 31],
            ['code' => '3573', 'name' => 'Kota Malang', 'province' => 'Jawa Timur', 'kec_count' => 5],
            ['code' => '3507', 'name' => 'Kabupaten Malang', 'province' => 'Jawa Timur', 'kec_count' => 33],
            ['code' => '3515', 'name' => 'Kabupaten Sidoarjo', 'province' => 'Jawa Timur', 'kec_count' => 18],
            ['code' => '3525', 'name' => 'Kabupaten Gresik', 'province' => 'Jawa Timur', 'kec_count' => 18],
            ['code' => '3516', 'name' => 'Kabupaten Mojokerto', 'province' => 'Jawa Timur', 'kec_count' => 18],
            ['code' => '3576', 'name' => 'Kota Mojokerto', 'province' => 'Jawa Timur', 'kec_count' => 3],
            ['code' => '3514', 'name' => 'Kabupaten Pasuruan', 'province' => 'Jawa Timur', 'kec_count' => 24],
            ['code' => '3575', 'name' => 'Kota Pasuruan', 'province' => 'Jawa Timur', 'kec_count' => 4],
            ['code' => '3517', 'name' => 'Kabupaten Jombang', 'province' => 'Jawa Timur', 'kec_count' => 21],
            ['code' => '3506', 'name' => 'Kabupaten Kediri', 'province' => 'Jawa Timur', 'kec_count' => 26],
            ['code' => '3571', 'name' => 'Kota Kediri', 'province' => 'Jawa Timur', 'kec_count' => 3],
            ['code' => '3510', 'name' => 'Kabupaten Banyuwangi', 'province' => 'Jawa Timur', 'kec_count' => 25],
            ['code' => '3509', 'name' => 'Kabupaten Jember', 'province' => 'Jawa Timur', 'kec_count' => 31],
            ['code' => '3519', 'name' => 'Kabupaten Madiun', 'province' => 'Jawa Timur', 'kec_count' => 15],
            ['code' => '3577', 'name' => 'Kota Madiun', 'province' => 'Jawa Timur', 'kec_count' => 3],
            ['code' => '3579', 'name' => 'Kota Batu', 'province' => 'Jawa Timur', 'kec_count' => 3],
        ];
    }

    /**
     * Get list of unique available provinces.
     */
    public function getAvailableProvinces(): array
    {
        return array_values(array_unique(array_column($this->getAvailableRegencies(), 'province')));
    }

    /**
     * Get list of districts (kecamatan) for a specific regency.
     */
    public function getDistrictsByRegency(string $regencyCode): array
    {
        $filePath = database_path('data/districts_by_regency.json');
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            if (isset($data[$regencyCode]) && is_array($data[$regencyCode])) {
                return $data[$regencyCode];
            }
        }

        $storagePath = storage_path('app/districts_by_regency.json');
        if (file_exists($storagePath)) {
            $data = json_decode(file_get_contents($storagePath), true);
            if (isset($data[$regencyCode]) && is_array($data[$regencyCode])) {
                return $data[$regencyCode];
            }
        }

        // Fallback: fetch from open API
        try {
            $resp = @file_get_contents("https://ibnux.github.io/data-indonesia/kecamatan/{$regencyCode}.json");
            if ($resp) {
                $json = json_decode($resp, true);
                if (is_array($json)) {
                    $districts = array_map(fn($item) => [
                        'code' => (string) $item['id'],
                        'name' => ucwords(strtolower(trim($item['nama']))),
                    ], $json);

                    // Auto-cache in database/data/districts_by_regency.json
                    if (!empty($districts) && file_exists($filePath)) {
                        $existing = json_decode(file_get_contents($filePath), true) ?: [];
                        $existing[$regencyCode] = $districts;
                        @file_put_contents($filePath, json_encode($existing, JSON_PRETTY_PRINT));
                    }

                    return $districts;
                }
            }
        } catch (\Throwable) {}

        return [];
    }

    /**
     * Run the automated integration for the chosen regency, school criteria, selected district codes, and integration year.
     */
    public function integrate(string $regencyCode, string $criteria = 'ALL', array|string|null $districtInput = null, ?int $integrationYear = null): array
    {
        $integrationYear = $integrationYear ? (int) $integrationYear : (int) date('Y');

        // 1. Find Regency Config
        $regencies = collect($this->getAvailableRegencies());
        $regencyInfo = $regencies->firstWhere('code', $regencyCode);

        if (!$regencyInfo) {
            return [
                'status' => false,
                'message' => 'Kode Kabupaten/Kota tidak dikenali.',
            ];
        }

        // Resolve District info
        $districts = $this->getDistrictsByRegency($regencyCode);
        $districtsByCode = collect($districts)->keyBy('code');

        // Normalize selected district codes
        $selectedKecCodes = [];
        if (is_array($districtInput)) {
            $selectedKecCodes = array_values(array_filter($districtInput, fn($c) => !empty($c) && $c !== 'ALL'));
        } elseif (is_string($districtInput) && !empty($districtInput) && $districtInput !== 'ALL') {
            $selectedKecCodes = [$districtInput];
        }

        $allKecCodesInRegency = !empty($districts)
            ? array_column($districts, 'code')
            : [];

        // If user didn't check any or selected all
        $isAllDistricts = empty($selectedKecCodes) || count($selectedKecCodes) === count($allKecCodesInRegency);
        if (empty($selectedKecCodes)) {
            $selectedKecCodes = $allKecCodesInRegency;
        }

        $isSpecificDistrict = count($selectedKecCodes) === 1;
        $districtName = $isSpecificDistrict
            ? ($districtsByCode[$selectedKecCodes[0]]['name'] ?? null)
            : null;

        if ($isAllDistricts) {
            $wilayahLabel = "seluruh {$regencyInfo['name']} (" . count($selectedKecCodes) . " Kecamatan)";
        } elseif ($isSpecificDistrict) {
            $wilayahLabel = "Kecamatan {$districtName}, {$regencyInfo['name']}";
        } else {
            $wilayahLabel = count($selectedKecCodes) . " Kecamatan di {$regencyInfo['name']}";
        }

        // 2. Resolve Province and Regency in Database
        $province = Province::firstOrCreate(
            ['name' => $regencyInfo['province']],
            ['name' => $regencyInfo['province']]
        );

        $regency = Regency::firstOrCreate(
            ['name' => $regencyInfo['name']],
            [
                'province_id' => $province->id,
                'name' => $regencyInfo['name'],
            ]
        );

        // Pre-create district in database if specific district selected
        $targetDistrictModel = null;
        if ($isSpecificDistrict && $districtName) {
            $targetDistrictModel = District::firstOrCreate(
                [
                    'regency_id' => $regency->id,
                    'name' => $districtName,
                ],
                [
                    'regency_id' => $regency->id,
                    'name' => $districtName,
                ]
            );
        }

        $targetDistrictIds = [];
        if (!$isAllDistricts) {
            foreach ($selectedKecCodes as $kCode) {
                if (isset($districtsByCode[$kCode])) {
                    $dModel = District::firstOrCreate(
                        ['regency_id' => $regency->id, 'name' => $districtsByCode[$kCode]['name']],
                        ['regency_id' => $regency->id, 'name' => $districtsByCode[$kCode]['name']]
                    );
                    $targetDistrictIds[] = $dModel->id;
                }
            }
        }

        // 3. Determine Education Levels to Fetch
        $criteria = strtoupper(trim($criteria));
        $levels = match ($criteria) {
            'SMA' => ['SMA'],
            'SMK' => ['SMK'],
            'MA' => ['MA'],
            default => ['SMK', 'SMA', 'MA'], // ALL SLTA sederajat
        };

        // Preload Reference Maps
        $schoolTypes = SchoolType::all()->keyBy(fn($st) => strtoupper($st->name));
        $accreditations = Accreditation::all()->keyBy(fn($acc) => strtoupper($acc->code));
        $defaultAccreditation = $accreditations['BT'] ?? Accreditation::first();

        // 4. Automatically restore any previously soft-deleted schools in this regency matching the selected criteria
        $trashedQuery = School::onlyTrashed()->where(function ($q) use ($regency, $targetDistrictIds, $isAllDistricts) {
            if (!$isAllDistricts && !empty($targetDistrictIds)) {
                $q->whereIn('district_id', $targetDistrictIds);
            } else {
                $q->where('regency_id', $regency->id)
                  ->orWhereHas('district', fn($dq) => $dq->where('regency_id', $regency->id))
                  ->orWhereNull('regency_id');
            }
        });

        if ($criteria !== 'ALL') {
            $selectedTypeId = $schoolTypes[$criteria]->id ?? null;
            if ($selectedTypeId) {
                $trashedQuery->where('school_type_id', $selectedTypeId);
            }
        } else {
            $sltaTypeIds = SchoolType::whereIn('name', ['SMA', 'SMK', 'MA', 'SLB'])->pluck('id')->toArray();
            $trashedQuery->where(function ($tq) use ($sltaTypeIds) {
                $tq->whereIn('school_type_id', $sltaTypeIds)
                   ->orWhereNull('school_type_id');
            });
        }

        $restoredFromTrashCount = 0;
        foreach ($trashedQuery->get() as $trashedSchool) {
            $trashedSchool->restore();
            $levelName = $trashedSchool->schoolType->name ?? 'SMA';
            $accCode = $trashedSchool->accreditation->code ?? 'B';
            $est = $this->calculateEstimatedGrade12($trashedSchool->npsn, $levelName, $trashedSchool->status, $accCode, $integrationYear);

            $trashedSchool->update([
                'regency_id' => $regency->id,
                'status_school' => 'aktif',
                'grade_12_count' => $trashedSchool->grade_12_count ?: $est['grade_12_count'],
                'grade_11_count' => $trashedSchool->grade_11_count ?: $est['grade_11_count'],
                'grade_10_count' => $trashedSchool->grade_10_count ?: $est['grade_10_count'],
                'student_count' => $trashedSchool->student_count ?: $est['student_count'],
            ]);
            $restoredFromTrashCount++;
        }

        // 5. Fetch Schools from Official Indonesian Data Referensi Service in Parallel
        $kecCodes = $selectedKecCodes;
        if (empty($kecCodes)) {
            $maxKec = (int) ($regencyInfo['kec_count'] ?? 30);
            for ($i = 1; $i <= $maxKec; $i++) {
                $kecCodes[] = $regencyCode . str_pad($i, 2, '0', STR_PAD_LEFT);
            }
        }

        $allRawSchools = [];
        foreach ($levels as $lvl) {
            $rawItems = $this->fetchSchoolsInParallel($kecCodes, $lvl);
            foreach ($rawItems as $npsn => $item) {
                if (!isset($allRawSchools[$npsn])) {
                    $allRawSchools[$npsn] = $item;
                }
            }
        }

        // Also fetch general kabupaten/district level as a fallback
        if (empty($allRawSchools)) {
            $fallbackCode = $isSpecificDistrict ? ($selectedKecCodes[0] ?? $regencyCode) : $regencyCode;
            foreach ($levels as $lvl) {
                $rawItems = $this->fetchByWilayahSingle($fallbackCode, $lvl);
                foreach ($rawItems as $npsn => $item) {
                    if (!isset($allRawSchools[$npsn])) {
                        $allRawSchools[$npsn] = $item;
                    }
                }
            }
        }

        if (empty($allRawSchools)) {
            if ($restoredFromTrashCount > 0) {
                return [
                    'status' => true,
                    'message' => "Pemulihan data berhasil! Sebanyak {$restoredFromTrashCount} sekolah yang sebelumnya terhapus telah berhasil dipulihkan otomatis ke status aktif di {$wilayahLabel}.",
                    'regency' => $regencyInfo['name'],
                    'district' => $districtName,
                    'criteria' => $criteria,
                    'created_count' => 0,
                    'updated_count' => $restoredFromTrashCount,
                    'restored_count' => $restoredFromTrashCount,
                    'total_count' => $restoredFromTrashCount,
                    'type_breakdown' => $typeBreakdown ?? ['SMA' => 0, 'SMK' => 0, 'MA' => 0],
                    'source_breakdown' => $sourceBreakdown ?? ['Dapodik (Kemendikbud)' => 0, 'EMIS (Kemenag)' => 0],
                ];
            }

            return [
                'status' => false,
                'message' => "Tidak ada data sekolah SLTA aktif yang ditemukan di {$wilayahLabel} untuk kriteria {$criteria} atau server referensi sedang sibuk.",
            ];
        }

        // 6. Insert / Update Records in Database
        $createdCount = 0;
        $updatedCount = 0;
        $totalGrade12Count = 0;
        $typeBreakdown = ['SMA' => 0, 'SMK' => 0, 'MA' => 0];
        $sourceBreakdown = ['Dapodik (Kemendikbud)' => 0, 'EMIS (Kemenag)' => 0];

        foreach ($allRawSchools as $npsn => $item) {
            $rawBentuk = strtoupper(trim($item['bentukPendidikan'] ?? ''));
            $levelKey = match ($rawBentuk) {
                'SMA' => 'SMA',
                'SMK' => 'SMK',
                'MA', 'MAS', 'MAN' => 'MA',
                default => 'SMA',
            };

            $schoolTypeId = $schoolTypes[$levelKey]->id ?? ($schoolTypes['SMA']->id ?? 1);

            // Determine Accreditation
            $rawAcc = strtoupper(trim($item['akreditasi'] ?? ''));
            $accId = $accreditations[$rawAcc]->id ?? ($defaultAccreditation?->id ?? 4);

            // Determine Status (Negeri / Swasta)
            $statusRaw = strtoupper(trim($item['statusSatuanPendidikan'] ?? ''));
            $status = str_contains($statusRaw, 'NEGERI') ? 'negeri' : 'swasta';

            // Determine District
            $kecName = trim($item['alamat']['nama_kecamatan'] ?? '');
            if ($kecName === '' && $districtName) {
                $kecName = $districtName;
            }
            $cleanKec = $kecName !== '' ? ucwords(strtolower($kecName)) : ($districtName ?: '');
            // Strip any 'Kecamatan ' or 'Kec. ' prefix so name matches database records
            $cleanKec = trim(preg_replace('/^(kecamatan|kec\.?)\s+/i', '', $cleanKec));

            $districtId = null;
            if ($isSpecificDistrict && $targetDistrictModel) {
                // If user selected a specific district in the sync form, guarantee association
                $districtId = $targetDistrictModel->id;
            } elseif ($cleanKec !== '') {
                $district = District::where('regency_id', $regency->id)
                    ->whereRaw('LOWER(name) = ?', [strtolower($cleanKec)])
                    ->first();

                if (!$district) {
                    $district = District::create([
                        'regency_id' => $regency->id,
                        'name' => $cleanKec,
                    ]);
                }
                $districtId = $district->id;
            } elseif ($targetDistrictModel) {
                $districtId = $targetDistrictModel->id;
            }

            // Coordinates & Distance - validate realistic Indonesia geographic coordinates (-11.5 to 6.5 lat, 94.0 to 142.0 lng)
            $rawLat = isset($item['lokasi']['lintang']) ? (float) $item['lokasi']['lintang'] : null;
            $rawLng = isset($item['lokasi']['bujur']) ? (float) $item['lokasi']['bujur'] : null;

            $lat = null;
            $lng = null;
            $distance = null;

            if ($rawLat !== null && $rawLng !== null && $rawLat != 0.0 && $rawLng != 0.0) {
                if ($rawLat >= -11.5 && $rawLat <= 6.5 && $rawLng >= 94.0 && $rawLng <= 142.0) {
                    $lat = $rawLat;
                    $lng = $rawLng;
                    $calcDist = $this->scoringService->calculateDistanceFromCampus($lat, $lng);
                    $distance = round(min(9999.99, max(0.0, $calcDist)), 2);
                }
            }

            // Address assembly
            $street = trim($item['alamat']['jalan'] ?? '');
            $village = trim($item['alamat']['nama_desa'] ?? '');
            $fullAddress = trim("{$street}, {$village}", ', ');
            if (empty($fullAddress)) {
                $fullAddress = "{$cleanKec}, {$regencyInfo['name']}";
            }

            // Ministry source determination
            $kementerian = strtoupper(trim($item['kementerianPembina'] ?? ''));
            $isKemenag = str_contains($kementerian, 'AGAMA') || $levelKey === 'MA';
            if ($isKemenag) {
                $sourceBreakdown['EMIS (Kemenag)']++;
            } else {
                $sourceBreakdown['Dapodik (Kemendikbud)']++;
            }

            // Calculate automated Grade 12, Grade 11, Grade 10 and Total Students based on integration year
            $est = $this->calculateEstimatedGrade12($npsn, $levelKey, $status, $rawAcc, $integrationYear);
            $totalGrade12Count += $est['grade_12_count'];

            $schoolData = [
                'npsn' => $npsn,
                'name' => strtoupper(trim($item['nama'])),
                'school_type_id' => $schoolTypeId,
                'status' => $status,
                'province_id' => $province->id,
                'regency_id' => $regency->id,
                'district_id' => $districtId,
                'address' => $fullAddress,
                'latitude' => $lat,
                'longitude' => $lng,
                'distance_from_campus' => $distance,
                'phone' => trim($item['kontak']['nomor_telepon'] ?? '') ?: null,
                'email' => trim($item['kontak']['email'] ?? '') ?: null,
                'website' => trim($item['kontak']['website'] ?? '') ?: null,
                'accreditation_id' => $accId,
                'student_count' => $est['student_count'],
                'grade_10_count' => $est['grade_10_count'],
                'grade_11_count' => $est['grade_11_count'],
                'grade_12_count' => $est['grade_12_count'],
                'status_school' => 'aktif',
                'notes' => 'Terintegrasi otomatis dari ' . ($isKemenag ? 'Kemenag (EMIS)' : 'Dapodik (Kemdikbud)') . " (Tahun Ajaran {$integrationYear}/" . ($integrationYear + 1) . ') pada ' . now()->format('d/m/Y H:i'),
            ];

            $existing = School::withTrashed()->where('npsn', $npsn)->first();

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                    $restoredFromTrashCount++;
                    $createdCount++;
                } else {
                    $updatedCount++;
                }

                // Update fields and ensure status is active
                $schoolData['deleted_at'] = null;
                $existing->update(array_filter($schoolData, fn($v) => !is_null($v)));
                $savedSchool = $existing;
            } else {
                $savedSchool = School::create($schoolData);
                $createdCount++;
            }

            // Sync with SchoolPotential
            try {
                SchoolPotential::updateOrCreate(
                    ['school_id' => $savedSchool->id],
                    [
                        'total_students' => $est['student_count'],
                        'grade_12_students' => $est['grade_12_count'],
                        'potential_score' => $savedSchool->potential_score ?? 0,
                        'notes' => "Data potensi otomatis integrasi Dapodik/Kemenag Tahun {$integrationYear}",
                    ]
                );
            } catch (\Throwable) {}

            $typeBreakdown[$levelKey] = ($typeBreakdown[$levelKey] ?? 0) + 1;
        }

        // 7. Recalculate School Priority Scores
        try {
            $this->scoringService->recalculateAllSchoolScores();
        } catch (\Throwable $e) {
            Log::warning('Recalculate scores after integration: ' . $e->getMessage());
        }

        // 8. Log to Activity Log if exists
        try {
            if (class_exists(ActivityLog::class)) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => 'INTEGRATE_DAPODIK_KEMENAG',
                    'description' => "Integrasi {$wilayahLabel} ({$criteria}): {$createdCount} sekolah baru, {$updatedCount} diperbarui, {$restoredFromTrashCount} dipulihkan dari sampah.",
                    'created_at' => now(),
                ]);
            }
        } catch (\Throwable) {}

        $restoreMsg = $restoredFromTrashCount > 0 ? ", serta {$restoredFromTrashCount} data terhapus otomatis dipulihkan kembali" : "";
        $totalSynced = $createdCount + $updatedCount;

        return [
            'status' => true,
            'message' => "Integrasi berhasil! Total {$totalSynced} sekolah SLTA aktif di {$wilayahLabel} berhasil disinkronkan ({$createdCount} data baru, {$updatedCount} diperbarui{$restoreMsg}) dengan estimasi data Kelas 12 Tahun Ajaran {$integrationYear}/" . ($integrationYear + 1) . ".",
            'regency' => $regencyInfo['name'],
            'district' => $districtName ?: (!$isAllDistricts ? count($selectedKecCodes) . ' Kecamatan Terpilih' : null),
            'criteria' => $criteria,
            'integration_year' => $integrationYear,
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
            'restored_count' => $restoredFromTrashCount,
            'total_count' => $totalSynced,
            'type_breakdown' => $typeBreakdown,
            'source_breakdown' => $sourceBreakdown,
            'total_grade_12' => $totalGrade12Count,
        ];
    }

    /**
     * Fetch schools across multiple kecamatan codes in parallel using curl_multi with chunking.
     */
    protected function fetchSchoolsInParallel(array $kecCodes, string $bentukPendidikan): array
    {
        $chunks = array_chunk($kecCodes, 12);
        $results = [];

        foreach ($chunks as $chunkIndex => $chunkCodes) {
            if ($chunkIndex > 0) {
                usleep(150000); // 150ms pause between batches to prevent 429 rate limit
            }

            $mh = curl_multi_init();
            $curlHandles = [];

            foreach ($chunkCodes as $code) {
                $url = "https://sekolah.devapi.id/sekolah?kode_wilayah={$code}&bentuk_pendidikan={$bentukPendidikan}&limit=100";
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 12,
                    CURLOPT_CONNECTTIMEOUT => 6,
                    CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) SAPA-Sekolah-UBSI/2.0',
                ]);
                curl_multi_add_handle($mh, $ch);
                $curlHandles[$code] = $ch;
            }

            $active = null;
            do {
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);

            while ($active && $mrc == CURLM_OK) {
                if (curl_multi_select($mh, 0.2) === -1) {
                    usleep(50000);
                }
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }

            foreach ($curlHandles as $code => $ch) {
                $content = curl_multi_getcontent($ch);
                curl_multi_remove_handle($mh, $ch);
                curl_close($ch);

                if ($content) {
                    $json = json_decode($content, true);
                    if (!empty($json['data']) && is_array($json['data'])) {
                        foreach ($json['data'] as $item) {
                            $npsn = trim((string) ($item['npsn'] ?? ''));
                            $nama = trim((string) ($item['nama'] ?? ''));
                            if ($npsn !== '' && $nama !== '') {
                                $results[$npsn] = $item;
                            }
                        }
                    }
                }
            }

            curl_multi_close($mh);
        }

        return $results;
    }

    /**
     * Fetch schools by single wilayah code (fallback).
     */
    protected function fetchByWilayahSingle(string $kodeWilayah, string $bentukPendidikan): array
    {
        $url = "https://sekolah.devapi.id/sekolah?kode_wilayah={$kodeWilayah}&bentuk_pendidikan={$bentukPendidikan}&limit=100";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_USERAGENT => 'SAPA-Sekolah-UBSI/2.0 Integration Client',
        ]);
        $resp = curl_exec($ch);
        curl_close($ch);

        $results = [];
        if ($resp) {
            $json = json_decode($resp, true);
            if (!empty($json['data']) && is_array($json['data'])) {
                foreach ($json['data'] as $item) {
                    $npsn = trim((string) ($item['npsn'] ?? ''));
                    $nama = trim((string) ($item['nama'] ?? ''));
                    if ($npsn !== '' && $nama !== '') {
                        $results[$npsn] = $item;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Calculate realistic estimated Grade 12 students (and grades 10, 11, total)
     * based on school educational level, status (negeri/swasta), accreditation,
     * and integration academic year according to Indonesian standards (Permendikbud No. 22/2016).
     */
    public function calculateEstimatedGrade12(string $npsn, string $level, string $status, string $accreditation, int $year): array
    {
        $level = strtoupper(trim($level));
        $status = strtolower(trim($status));
        $acc = strtoupper(trim($accreditation));

        // Base rombel ranges [min, max] per level, status, and accreditation
        if ($level === 'SMK') {
            if ($status === 'negeri') {
                $rombelRange = [10, 14]; // SMKN biasanya memiliki 10-14 rombel per angkatan
            } elseif ($acc === 'A') {
                $rombelRange = [6, 10];
            } elseif ($acc === 'B') {
                $rombelRange = [4, 7];
            } else {
                $rombelRange = [2, 4];
            }
        } elseif ($level === 'SMA') {
            if ($status === 'negeri') {
                $rombelRange = [8, 12]; // SMAN biasanya memiliki 8-12 rombel (MIPA & IPS)
            } elseif ($acc === 'A') {
                $rombelRange = [5, 8];
            } elseif ($acc === 'B') {
                $rombelRange = [3, 5];
            } else {
                $rombelRange = [2, 4];
            }
        } else { // MA (Madrasah Aliyah)
            if ($status === 'negeri') {
                $rombelRange = [5, 8];
            } elseif ($acc === 'A') {
                $rombelRange = [3, 6];
            } else {
                $rombelRange = [2, 4];
            }
        }

        // Deterministic pseudo-random seed based on NPSN and integration year
        // Guarantees consistent numbers for the same school in a given year,
        // while realistically varying across different academic years.
        $hash = abs(crc32("{$npsn}_{$year}"));
        $rombelCount = $rombelRange[0] + ($hash % ($rombelRange[1] - $rombelRange[0] + 1));

        // Average students per rombel: Permendikbud standard is 32 to 36 for secondary education
        $avgPerClass = 32 + (($hash >> 4) % 5); // 32, 33, 34, 35, 36

        // Grade 12 students count
        $grade12 = $rombelCount * $avgPerClass;

        // Natural cohort variations for grade 11 and grade 10 (±3%)
        $grade11 = (int) round($grade12 * (1.0 + ((($hash >> 8) % 7) - 3) * 0.01));
        $grade10 = (int) round($grade11 * (1.0 + ((($hash >> 12) % 7) - 2) * 0.01));
        $total = $grade10 + $grade11 + $grade12;

        return [
            'year' => $year,
            'academic_year' => "{$year}/" . ($year + 1),
            'rombel_count' => $rombelCount,
            'avg_per_class' => $avgPerClass,
            'grade_12_count' => $grade12,
            'grade_11_count' => $grade11,
            'grade_10_count' => $grade10,
            'student_count' => $total,
        ];
    }
}
