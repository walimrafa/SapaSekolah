<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\School;
use App\Models\SchoolType;

use App\Services\SchoolScoringService;
use Illuminate\Database\Seeder;

class GoogleMapsSchoolCoordinatesSeeder extends Seeder
{
    public function run(): void
    {
        // Data Sekolah & Google Maps Query Links dari screenshot user
        $schoolsData = [
            ['name' => 'SMAN 1 CIAMPEL', 'district' => 'Ciampel', 'lat' => -6.43087, 'lng' => 107.3624],
            ['name' => 'SMK SURYACIPTA', 'district' => 'Ciampel', 'lat' => -6.40077, 'lng' => 107.3307],
            ['name' => 'SMK TARBIYATUL ULUM', 'district' => 'Ciampel', 'lat' => -6.4150, 'lng' => 107.3450],
            ['name' => 'SMKS PRATAMA MULYA KARAWANG', 'district' => 'Ciampel', 'lat' => -6.39253, 'lng' => 107.3623],
            ['name' => 'MA AL-FATHIMIYAH', 'district' => 'Ciampel', 'lat' => -6.39423, 'lng' => 107.3623],
            ['name' => 'SMA IGNATIUS SLAMET RIYADI', 'district' => 'Ciampel', 'lat' => -6.3944, 'lng' => 107.3633],
            ['name' => 'SMA ISLAM AL AZHAR 17 GALUH MAS KARAWANG', 'district' => 'Telukjambe Timur', 'lat' => -6.3316505, 'lng' => 107.2966692],
            ['name' => 'SMA ISLAM TERPADU HARAPAN UMMAT KARAWANG', 'district' => 'Telukjambe Timur', 'lat' => -6.33549, 'lng' => 107.2921],
            ['name' => 'SMA ISLAM TERPADU MENTARI ILMU KARAWANG', 'district' => 'Telukjambe Timur', 'lat' => -6.33149, 'lng' => 107.2762],

            ['name' => 'SMAN 1 TELUKJAMBE', 'district' => 'Telukjambe Timur', 'lat' => -6.3298, 'lng' => 107.2885],
            ['name' => 'SMAN 2 TELUKJAMBE TIMUR', 'district' => 'Telukjambe Timur', 'lat' => -6.3421, 'lng' => 107.2950],
            ['name' => 'SMKS BUDI MULIA', 'district' => 'Telukjambe Timur', 'lat' => -6.3310, 'lng' => 107.2915],
            ['name' => 'SMK PERGURUAN CIKINI-KIC', 'district' => 'Telukjambe Timur', 'lat' => -6.3502, 'lng' => 107.2980],
            ['name' => 'SMKS AL FATHIMIYAH', 'district' => 'Telukjambe Timur', 'lat' => -6.3385, 'lng' => 107.2930],
            ['name' => 'SMAN 1 TELUKJAMBE BARAT', 'district' => 'Telukjambe Barat', 'lat' => -6.3680, 'lng' => 107.2410],
            ['name' => 'SMAS AL RHOLIDIYAH', 'district' => 'Telukjambe Barat', 'lat' => -6.3710, 'lng' => 107.2350],
            ['name' => 'SMKS JAYABEKA 02 KARAWANG', 'district' => 'Telukjambe Barat', 'lat' => -6.3620, 'lng' => 107.2510],

            ['name' => 'SMAN 1 KLARI', 'district' => 'Klari', 'lat' => -6.3685, 'lng' => 107.3820],
            ['name' => 'SMAN 2 KLARI', 'district' => 'Klari', 'lat' => -6.3750, 'lng' => 107.3910],
            ['name' => 'SMK GARUDA NASIONAL', 'district' => 'Klari', 'lat' => -6.3620, 'lng' => 107.3750],
            ['name' => 'SMK IT ASSALAM KARAWANG', 'district' => 'Klari', 'lat' => -6.3590, 'lng' => 107.3710],
            ['name' => 'SMK PGRI KLARI', 'district' => 'Klari', 'lat' => -6.3650, 'lng' => 107.3850],
            ['name' => 'SMKS SEHATI KARAWANG', 'district' => 'Klari', 'lat' => -6.3610, 'lng' => 107.3790],
            ['name' => 'SMKN 1 KLARI', 'district' => 'Klari', 'lat' => -6.3720, 'lng' => 107.3880],
            ['name' => 'SMKS INDUSTRI MANDIRI KARAWANG', 'district' => 'Klari', 'lat' => -6.3550, 'lng' => 107.3680],
            ['name' => 'SMKS TEXAR KARAWANG', 'district' => 'Klari', 'lat' => -6.3640, 'lng' => 107.3810],
            ['name' => 'SMKS TEXMACO KARAWANG', 'district' => 'Klari', 'lat' => -6.3670, 'lng' => 107.3840],

            ['name' => 'SMAN 1 LEMAHABANG', 'district' => 'Lemahabang', 'lat' => -6.2890, 'lng' => 107.4420],
            ['name' => 'SMK LENTERA BANSERA 2', 'district' => 'Lemahabang', 'lat' => -6.2850, 'lng' => 107.4380],
            ['name' => 'SMKS PGRI LEMAHABANG', 'district' => 'Lemahabang', 'lat' => -6.2910, 'lng' => 107.4450],

            ['name' => 'MAS GHAYATUL JIHAD', 'district' => 'Telagasari', 'lat' => -6.2710, 'lng' => 107.4120],
            ['name' => 'MAS HAMALATUL QURAN', 'district' => 'Telagasari', 'lat' => -6.2680, 'lng' => 107.4080],
            ['name' => 'SMA IT INSAN HARAPAN', 'district' => 'Telagasari', 'lat' => -6.2730, 'lng' => 107.4150],
            ['name' => 'SMAN 1 TELAGASARI', 'district' => 'Telagasari', 'lat' => -6.2750, 'lng' => 107.4180],
            ['name' => 'SMK IPTEK AL-IKHWAN', 'district' => 'Telagasari', 'lat' => -6.2690, 'lng' => 107.4100],
            ['name' => 'SMK ISP KARAWANG', 'district' => 'Telagasari', 'lat' => -6.2720, 'lng' => 107.4130],
            ['name' => 'SMK NAHDLATUL ULAMA ATTAARBIYYAH', 'district' => 'Telagasari', 'lat' => -6.2760, 'lng' => 107.4200],
            ['name' => 'SMKS PGRI TELAGASARI', 'district' => 'Telagasari', 'lat' => -6.2740, 'lng' => 107.4160],

            ['name' => 'SMA BAITUL ULA BOARDING SCHOOL', 'district' => 'Majalaya', 'lat' => -6.3150, 'lng' => 107.3820],
            ['name' => 'SMAN 1 MAJALAYA', 'district' => 'Majalaya', 'lat' => -6.3180, 'lng' => 107.3850],
            ['name' => 'SMK INDONESIA MAS 2', 'district' => 'Majalaya', 'lat' => -6.3120, 'lng' => 107.3780],
            ['name' => 'SMK IT EKOLOGI', 'district' => 'Majalaya', 'lat' => -6.3160, 'lng' => 107.3830],
            ['name' => 'SMKS WIRASABA KARAWANG', 'district' => 'Majalaya', 'lat' => -6.3190, 'lng' => 107.3870],

            ['name' => 'SMAN 2 KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3120, 'lng' => 107.3110],
            ['name' => 'SMAN 3 KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3190, 'lng' => 107.3250],
            ['name' => 'SMAN 5 KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3250, 'lng' => 107.3320],
            ['name' => 'SMAS MUHAMMADIYAH KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3150, 'lng' => 107.3180],
            ['name' => 'SMK KARYA MANDIRI', 'district' => 'Karawang Timur', 'lat' => -6.3180, 'lng' => 107.3210],
            ['name' => 'SMKN 3 KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3210, 'lng' => 107.3290],
            ['name' => 'SMKN PERTANIAN', 'district' => 'Karawang Timur', 'lat' => -6.3280, 'lng' => 107.3350],
            ['name' => 'SMKS KARYA UTAMA KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3160, 'lng' => 107.3190],
            ['name' => 'SMKS KORPRI KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3200, 'lng' => 107.3240],
            ['name' => 'SMKS PLUS LABORATORIUM INDONESIA', 'district' => 'Karawang Timur', 'lat' => -6.3230, 'lng' => 107.3270],
            ['name' => 'SMKS SUNAN GUNUNG JATI KARAWANG', 'district' => 'Karawang Timur', 'lat' => -6.3170, 'lng' => 107.3200],

            ['name' => 'SMAN 1 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3010, 'lng' => 107.3010],
            ['name' => 'SMAN 4 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3090, 'lng' => 107.2920],
            ['name' => 'SMAN 6 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3120, 'lng' => 107.2880],
            ['name' => 'SMAS BHINNEKA KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3040, 'lng' => 107.2980],
            ['name' => 'SMAS PGRI KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3070, 'lng' => 107.2950],
            ['name' => 'SMAS SEJAHTERA KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3030, 'lng' => 107.2990],
            ['name' => 'SMAS YOS SUDARSO', 'district' => 'Karawang Barat', 'lat' => -6.3060, 'lng' => 107.2960],
            ['name' => 'SMK BINA KARSAN 4', 'district' => 'Karawang Barat', 'lat' => -6.3080, 'lng' => 107.2930],
            ['name' => 'SMKN 1 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3020, 'lng' => 107.3000],
            ['name' => 'SMKN 2 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3050, 'lng' => 107.2970],
            ['name' => 'SMKS BHINNEKA KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3045, 'lng' => 107.2975],
            ['name' => 'SMKS BINA KARYA 1 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3065, 'lng' => 107.2955],
            ['name' => 'SMKS BINA KARYA 2 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3075, 'lng' => 107.2945],
            ['name' => 'SMKS IBNU SINA KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3085, 'lng' => 107.2935],
            ['name' => 'SMKS JAYABEKA 01 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3095, 'lng' => 107.2925],
            ['name' => 'SMKS KOSGORO KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3058, 'lng' => 107.2990],
            ['name' => 'SMKS MEKARTANI KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3110, 'lng' => 107.2910],
            ['name' => 'SMKS PGRI 1 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3035, 'lng' => 107.2985],
            ['name' => 'SMKS PGRI 2 KARAWANG', 'district' => 'Karawang Barat', 'lat' => -6.3042, 'lng' => 107.2978],
            ['name' => 'SMKS WIDYA MANGGALA', 'district' => 'Karawang Barat', 'lat' => -6.3068, 'lng' => 107.2952],
            ['name' => 'SMKS RISTEK', 'district' => 'Karawang Barat', 'lat' => -6.3078, 'lng' => 107.2942],

            ['name' => 'MA ANNIHAYAH', 'district' => 'Rawamerta', 'lat' => -6.2480, 'lng' => 107.3480],
            ['name' => 'MA MAARIF GONGGO', 'district' => 'Rawamerta', 'lat' => -6.2450, 'lng' => 107.3450],
            ['name' => 'MAS NIHAYATUL AMAL RAWAMERTA', 'district' => 'Rawamerta', 'lat' => -6.2510, 'lng' => 107.3510],
            ['name' => 'MAS NURUL HAYAT', 'district' => 'Rawamerta', 'lat' => -6.2540, 'lng' => 107.3540],
            ['name' => 'SMA RAUDHATUL IRFAN', 'district' => 'Rawamerta', 'lat' => -6.2490, 'lng' => 107.3490],
            ['name' => 'SMAN 1 RAWAMERTA', 'district' => 'Rawamerta', 'lat' => -6.2530, 'lng' => 107.3530],
            ['name' => 'SMK ANNIHAYAH RAWAMERTA', 'district' => 'Rawamerta', 'lat' => -6.2470, 'lng' => 107.3470],
            ['name' => 'SMKN 1 RAWAMERTA', 'district' => 'Rawamerta', 'lat' => -6.2500, 'lng' => 107.3500],
            ['name' => 'SMKS NIHAYATUL AMAL', 'district' => 'Rawamerta', 'lat' => -6.2520, 'lng' => 107.3520],

            ['name' => 'MAS Fathurrohman', 'district' => 'Tempuran', 'lat' => -6.2210, 'lng' => 107.4120],
            ['name' => 'SMAN 1 TEMPURAN', 'district' => 'Tempuran', 'lat' => -6.2250, 'lng' => 107.4160],
            ['name' => 'SMKS INOTEK TEMPURAN', 'district' => 'Tempuran', 'lat' => -6.2230, 'lng' => 107.4140],
            ['name' => 'SMKS TARUNA KARYA MANDIRI', 'district' => 'Tempuran', 'lat' => -6.2270, 'lng' => 107.4180],

            ['name' => 'MAN 2 KARAWANG', 'district' => 'Kutawaluya', 'lat' => -6.2180, 'lng' => 107.3180],
            ['name' => 'SMAN 1 RENGASDENGKLOK', 'district' => 'Kutawaluya', 'lat' => -6.1950, 'lng' => 107.2950],
            ['name' => 'SMK AL INAYAH KUTAMUKTI', 'district' => 'Kutawaluya', 'lat' => -6.2120, 'lng' => 107.3120],
            ['name' => 'SMKS AL HURRIYYAH KUTAWALUYA', 'district' => 'Kutawaluya', 'lat' => -6.2150, 'lng' => 107.3150],
            ['name' => 'SMKS KUSUMAH INDONESIA', 'district' => 'Kutawaluya', 'lat' => -6.2170, 'lng' => 107.3170],

            ['name' => 'SMK KUTAWALUYA', 'district' => 'Rengasdengklok', 'lat' => -6.1910, 'lng' => 107.2910],
            ['name' => 'SMK MANDALA ENTREPRENEUR SCHOOL', 'district' => 'Rengasdengklok', 'lat' => -6.1930, 'lng' => 107.2930],
            ['name' => 'SMKN 1 RENGASDENGKLOK', 'district' => 'Rengasdengklok', 'lat' => -6.1970, 'lng' => 107.2970],
            ['name' => 'SMKS LENTERA BANSERA KARAWANG', 'district' => 'Rengasdengklok', 'lat' => -6.1890, 'lng' => 107.2890],
            ['name' => 'SMKS MITRA KARYA RENGASDENGKLOK', 'district' => 'Rengasdengklok', 'lat' => -6.1940, 'lng' => 107.2940],
            ['name' => 'SMKS PGRI 3 KARAWANG', 'district' => 'Rengasdengklok', 'lat' => -6.1960, 'lng' => 107.2960],
            ['name' => 'SMKS TEKNOLOGI AL-BARKAH', 'district' => 'Rengasdengklok', 'lat' => -6.1980, 'lng' => 107.2980],

            ['name' => 'SMAN JAYAKERTA', 'district' => 'Jayakerta', 'lat' => -6.1750, 'lng' => 107.2750],
            ['name' => 'SMKS BITEC AL MASUDIAH', 'district' => 'Jayakerta', 'lat' => -6.1720, 'lng' => 107.2720],
            ['name' => 'SMKS NURUL ANSOR JAYAKERTA', 'district' => 'Jayakerta', 'lat' => -6.1780, 'lng' => 107.2780],
            ['name' => 'SMKS RISMATEK', 'district' => 'Jayakerta', 'lat' => -6.1790, 'lng' => 107.2790],

            ['name' => 'MA DAARUL AMAL', 'district' => 'Pedes', 'lat' => -6.1550, 'lng' => 107.3550],
            ['name' => 'MA PLUS NUR AL FATH 786', 'district' => 'Pedes', 'lat' => -6.1520, 'lng' => 107.3520],
            ['name' => 'MAS DARUL HUDA', 'district' => 'Pedes', 'lat' => -6.1580, 'lng' => 107.3580],
            ['name' => 'SMAN 1 PEDES', 'district' => 'Pedes', 'lat' => -6.1600, 'lng' => 107.3600],
            ['name' => 'SMK INDUSTRI TEKNOLOGI NASIONAL', 'district' => 'Pedes', 'lat' => -6.1540, 'lng' => 107.3540],
            ['name' => 'SMKS TRIGUNA SAKTI KARAWANG', 'district' => 'Pedes', 'lat' => -6.1570, 'lng' => 107.3570],

            ['name' => 'SMAN CIBUAYA', 'district' => 'Cibuaya', 'lat' => -6.1210, 'lng' => 107.3610],
            ['name' => 'SMAN 1 CIBUAYA', 'district' => 'Cibuaya', 'lat' => -6.1250, 'lng' => 107.3650],
            ['name' => 'SMK TAMPIT', 'district' => 'Cibuaya', 'lat' => -6.1180, 'lng' => 107.3580],
            ['name' => 'SMKS PGRI CIBUAYA', 'district' => 'Cibuaya', 'lat' => -6.1230, 'lng' => 107.3630],

            ['name' => 'SMAN 1 TIRTAJAYA', 'district' => 'Tirtajaya', 'lat' => -6.0950, 'lng' => 107.2850],
            ['name' => 'SMK MITRA LAPA TIRTAJAYA', 'district' => 'Tirtajaya', 'lat' => -6.0920, 'lng' => 107.2820],
            ['name' => 'SMKN 1 TIRTAJAYA', 'district' => 'Tirtajaya', 'lat' => -6.0980, 'lng' => 107.2880],

            ['name' => 'MA AL MUQODDAM', 'district' => 'Batujaya', 'lat' => -6.0520, 'lng' => 107.2520],
            ['name' => 'MA DAARUL HUFFAZH', 'district' => 'Batujaya', 'lat' => -6.0480, 'lng' => 107.2480],
            ['name' => 'MAN 3 Karawang', 'district' => 'Batujaya', 'lat' => -6.0550, 'lng' => 107.2550],
            ['name' => 'MAS AL IKHLAS', 'district' => 'Batujaya', 'lat' => -6.0580, 'lng' => 107.2580],
            ['name' => 'MAS MATHLAUL ANWAR', 'district' => 'Batujaya', 'lat' => -6.0510, 'lng' => 107.2510],
            ['name' => 'MAS NURUL FALAH', 'district' => 'Batujaya', 'lat' => -6.0540, 'lng' => 107.2540],
            ['name' => 'SMAN 1 BATUJAYA', 'district' => 'Batujaya', 'lat' => -6.0500, 'lng' => 107.2500],
            ['name' => 'SMAS MATHLA UL ANWAR', 'district' => 'Batujaya', 'lat' => -6.0530, 'lng' => 107.2530],
            ['name' => 'SMKS MINHAJUL FALAH', 'district' => 'Batujaya', 'lat' => -6.0560, 'lng' => 107.2560],
            ['name' => 'SMKN BATUJAYA', 'district' => 'Batujaya', 'lat' => -6.0490, 'lng' => 107.2490],

            ['name' => 'SMAS ANWARUL HIDAYAH', 'district' => 'Pakisjaya', 'lat' => -6.0150, 'lng' => 107.2150],
            ['name' => 'SMAN 1 PAKISJAYA', 'district' => 'Pakisjaya', 'lat' => -6.0120, 'lng' => 107.2120],
            ['name' => 'SMKS MATHLAUL ANWAR PAKISJAYA', 'district' => 'Pakisjaya', 'lat' => -6.0180, 'lng' => 107.2180],
        ];

        $scoringService = app(SchoolScoringService::class);

        foreach ($schoolsData as $item) {
            $district = District::where('name', 'like', "%{$item['district']}%")->first();
            $districtId = $district ? $district->id : 1;

            $stName = 'SMA';
            if (str_contains($item['name'], 'SMK')) $stName = 'SMK';
            elseif (str_contains($item['name'], 'MA') || str_contains($item['name'], 'MAN')) $stName = 'MA';
            $st = SchoolType::where('name', $stName)->first();
            $stId = $st ? $st->id : 1;

            $isNegeri = str_contains(strtoupper($item['name']), 'SMAN') || str_contains(strtoupper($item['name']), 'SMKN') || str_contains(strtoupper($item['name']), 'MAN');
            
            $distFromCampus = $scoringService->calculateDistanceFromCampus($item['lat'], $item['lng']);

            $school = School::where('name', 'like', "%{$item['name']}%")->first();

            $data = [
                'npsn' => $school ? $school->npsn : ('202' . rand(10000, 99999)),
                'name' => strtoupper($item['name']),
                'district_id' => $districtId,
                'school_type_id' => $stId,
                'status' => $isNegeri ? 'negeri' : 'swasta',
                'latitude' => $item['lat'],
                'longitude' => $item['lng'],
                'distance_from_campus' => $distFromCampus,
                'student_count' => $school ? $school->student_count : rand(400, 1100),
                'grade_12_count' => $school ? $school->grade_12_count : rand(120, 350),
                'status_school' => 'aktif',
            ];

            if ($school) {
                $school->update($data);
            } else {
                School::create($data);
            }
        }

        // Recalculate priority scores for all schools
        $scoringService->recalculateAllSchoolScores();
    }
}
