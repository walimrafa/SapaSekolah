<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\School;
use App\Models\SchoolType;
use App\Services\SchoolScoringService;
use Illuminate\Database\Seeder;

class MasterSchoolListWithGmapsSeeder extends Seeder
{
    public function run(): void
    {
        // Koordinat acuan pusat per Kecamatan di Kabupaten Karawang
        $districtCoords = [
            'Karawang Barat'   => [-6.3054, 107.2995],
            'Karawang Timur'   => [-6.3200, 107.3250],
            'Telukjambe Timur' => [-6.3350, 107.2900],
            'Telukjambe Barat' => [-6.3650, 107.2450],
            'Ciampel'          => [-6.3950, 107.3500],
            'Klari'            => [-6.3680, 107.3820],
            'Majalaya'         => [-6.3180, 107.3850],
            'Telagasari'       => [-6.2750, 107.4180],
            'Lemahabang'       => [-6.2890, 107.4420],
            'Rawamerta'        => [-6.2510, 107.3510],
            'Tempuran'         => [-6.2250, 107.4160],
            'Kutawaluya'       => [-6.2150, 107.3150],
            'Rengasdengklok'   => [-6.1950, 107.2950],
            'Jayakerta'        => [-6.1750, 107.2750],
            'Pedes'            => [-6.1600, 107.3600],
            'Cilebar'          => [-6.1800, 107.4100],
            'Cibuaya'          => [-6.1250, 107.3650],
            'Tirtajaya'        => [-6.0950, 107.2850],
            'Batujaya'         => [-6.0500, 107.2500],
            'Pakisjaya'        => [-6.0120, 107.2120],
        ];

        // Tabel Data Sekolah Lengkap dengan Koordinat Google Maps dari Prompt User
        $rawRows = [
            ["Ciampel", "20237453", "SMAN 1 CIAMPEL", "SMA", "NEGERI", -6.3908, 107.3624],
            ["Ciampel", "70008409", "SMK SURYACIPTA", "SMK", "SWASTA", -6.4007, 107.3307],
            ["Ciampel", "69862575", "SMK TARBIYATUL ULUM", "SMK", "SWASTA", -6.3980, 107.3450],
            ["Ciampel", "69774545", "SMKS PRATAMA MULYA KARAWANG", "SMK", "SWASTA", -6.3953, 107.3623],
            ["Telukjambe Timur", "70010477", "MAS AL-FATHIMIYAH", "MA", "SWASTA", -6.3442, 107.3105],
            ["Telukjambe Timur", "20252842", "SMA IGNATIUS SLAMET RIYADI", "SMA", "SWASTA", -6.2940, 107.2693],
            ["Telukjambe Timur", "69989078", "SMA ISLAM AL AZHAR 17 GALUH MAS KARAWANG", "SMA", "SWASTA", -6.3335925, 107.2965692],
            ["Telukjambe Timur", "69947172", "SMA ISLAM TERPADU HARAPAN UMAT KARAWANG", "SMA", "SWASTA", -6.3149, 107.2921],
            ["Telukjambe Timur", "69947206", "SMA ISLAM TERPADU MENTARI ILMU KARAWANG", "SMA", "SWASTA", -6.3236, 107.2752],
            ["Telukjambe Timur", "20217780", "SMAN 1 TELUKJAMBE", "SMA", "NEGERI", -6.3298, 107.2885],
            ["Telukjambe Timur", "69915815", "SMAN 2 TELUKJAMBE TIMUR", "SMA", "NEGERI", -6.3421, 107.2950],
            ["Telukjambe Timur", "20237363", "SMAS BUDI MULIA", "SMA", "SWASTA", -6.3310, 107.2915],
            ["Telukjambe Timur", "70047946", "SMK PERGURUAN CIKINI-KIIC", "SMK", "SWASTA", -6.3502, 107.2980],
            ["Telukjambe Timur", "20258509", "SMKS AL FATHIMIYAH", "SMK", "SWASTA", -6.3385, 107.2930],
            ["Telukjambe Timur", "60728560", "SMKS BUDI MULIA", "SMK", "SWASTA", -6.3315, 107.2920],
            ["Telukjambe Barat", "69734348", "SMAN 1 TELUKJAMBE BARAT", "SMA", "NEGERI", -6.3680, 107.2410],
            ["Telukjambe Barat", "20237361", "SMAS AL KHOLIDIYAH", "SMA", "SWASTA", -6.3710, 107.2350],
            ["Telukjambe Barat", "20244795", "SMKS JAYABEKA 02 KARAWANG", "SMK", "SWASTA", -6.3620, 107.2510],
            ["Klari", "20280381", "MAS DAARUL QUR`AN", "MA", "SWASTA", -6.3650, 107.3780],
            ["Klari", "20217787", "SMAN 1 KLARI", "SMA", "NEGERI", -6.3685, 107.3820],
            ["Klari", "69915809", "SMAN 2 KLARI", "SMA", "NEGERI", -6.3750, 107.3910],
            ["Klari", "70044709", "SMK GARUDA NASIONAL", "SMK", "SWASTA", -6.3620, 107.3750],
            ["Klari", "69949201", "SMK IT ASSALAM KARAWANG", "SMK", "SWASTA", -6.3590, 107.3710],
            ["Klari", "20271592", "SMK PGRI KLARI", "SMK", "SWASTA", -6.3650, 107.3850],
            ["Klari", "69949224", "SMK SEHATI KARAWANG", "SMK", "SWASTA", -6.3610, 107.3790],
            ["Klari", "60728564", "SMKN 1 KLARI", "SMK", "NEGERI", -6.3720, 107.3880],
            ["Klari", "20252393", "SMKS INDUSTRI MANDIRI KARAWANG", "SMK", "SWASTA", -6.3550, 107.3680],
            ["Klari", "20267937", "SMKS TEXAR KARAWANG", "SMK", "SWASTA", -6.3640, 107.3810],
            ["Klari", "20217936", "SMKS TEXMACO KARAWANG", "SMK", "SWASTA", -6.3670, 107.3840],
            ["Lemahabang", "20237376", "SMAN 1 LEMAHABANG", "SMA", "NEGERI", -6.2890, 107.4420],
            ["Lemahabang", "69984091", "SMK LENTERA BANGSA 2", "SMK", "SWASTA", -6.2850, 107.4380],
            ["Lemahabang", "20276403", "SMKS PGRI LEMAHABANG", "SMK", "SWASTA", -6.2910, 107.4450],
            ["Telagasari", "20280383", "MAS GHOYATUL JIHAD", "MA", "SWASTA", -6.2710, 107.4120],
            ["Telagasari", "70007037", "MAS HAMALATUL QURAN", "MA", "SWASTA", -6.2680, 107.4080],
            ["Telagasari", "70036284", "SMA IT INSAN HARAPAN", "SMA", "SWASTA", -6.2730, 107.4150],
            ["Telagasari", "20217781", "SMAN 1 TELAGASARI", "SMA", "NEGERI", -6.2750, 107.4180],
            ["Telagasari", "69857935", "SMK IPTEK AL-IKHWAN", "SMK", "SWASTA", -6.2690, 107.4100],
            ["Telagasari", "69958769", "SMK ISP KARAWANG", "SMK", "SWASTA", -6.2720, 107.4130],
            ["Telagasari", "69972349", "SMK NAHDLATUL ULAMA ATTARBIYYAH", "SMK", "SWASTA", -6.2760, 107.4200],
            ["Telagasari", "20217795", "SMKS PGRI TELAGASARI", "SMK", "SWASTA", -6.2740, 107.4160],
            ["Majalaya", "70047499", "SMA BAITUL ULYA BOARDING SCHOOL", "SMA", "SWASTA", -6.3150, 107.3820],
            ["Majalaya", "69915810", "SMAN 1 MAJALAYA", "SMA", "NEGERI", -6.3180, 107.3850],
            ["Majalaya", "69960705", "SMK INDONESIA MAS 2", "SMK", "SWASTA", -6.3120, 107.3780],
            ["Majalaya", "69947173", "SMK TEKNOLOGI", "SMK", "SWASTA", -6.3160, 107.3830],
            ["Majalaya", "20268195", "SMKS WIRASABA KARAWANG", "SMK", "SWASTA", -6.3190, 107.3870],
            ["Karawang Timur", "20280384", "MAN 2 Karawang", "MA", "NEGERI", -6.3210, 107.3220],
            ["Karawang Timur", "20217763", "SMAN 2 KARAWANG", "SMA", "NEGERI", -6.3121, 107.3115],
            ["Karawang Timur", "20237386", "SMAN 5 KARAWANG", "SMA", "NEGERI", -6.3255, 107.3325],
            ["Karawang Timur", "20237369", "SMAS MUHAMMADIYAH KARAWANG", "SMA", "SWASTA", -6.3150, 107.3180],
            ["Karawang Timur", "69867994", "SMK KARYA MANDIRI", "SMK", "SWASTA", -6.3180, 107.3210],
            ["Karawang Timur", "20237414", "SMKN 3 KARAWANG", "SMK", "NEGERI", -6.3212, 107.3288],
            ["Karawang Timur", "60728565", "SMKN PERTANIAN", "SMK", "NEGERI", -6.3280, 107.3350],
            ["Karawang Timur", "20217805", "SMKS KARYA UTAMA KARAWANG", "SMK", "SWASTA", -6.3160, 107.3190],
            ["Karawang Timur", "20237405", "SMKS KORPRI KARAWANG", "SMK", "SWASTA", -6.3200, 107.3240],
            ["Karawang Timur", "20254289", "SMKS PLUS LABORATORIUM INDONESIA", "SMK", "SWASTA", -6.3230, 107.3270],
            ["Karawang Timur", "20217767", "SMKS SUNAN GUNUNG JATI KARAWANG", "SMK", "SWASTA", -6.3170, 107.3200],
            ["Karawang Barat", "70059724", "SMA IMAN KARAWANG", "SMA", "SWASTA", -6.3020, 107.2960],
            ["Karawang Barat", "70025263", "SMA Islam Teladan Al Irsyad Al Islamiyyah", "SMA", "SWASTA", -6.3060, 107.2970],
            ["Karawang Barat", "69957259", "SMA TUNAS DHARMA", "SMA", "SWASTA", -6.3070, 107.2779],
            ["Karawang Barat", "20217786", "SMAN 1 KARAWANG", "SMA", "NEGERI", -6.3005, 107.3012],
            ["Karawang Barat", "20217764", "SMAN 3 KARAWANG", "SMA", "NEGERI", -6.3045, 107.2925],
            ["Karawang Barat", "20217765", "SMAN 4 KARAWANG", "SMA", "NEGERI", -6.3092, 107.2918],
            ["Karawang Barat", "69904548", "SMAN 6 KARAWANG", "SMA", "NEGERI", -6.3120, 107.2882],
            ["Karawang Barat", "20237362", "SMAS BHINNEKA KARAWANG", "SMA", "SWASTA", -6.3040, 107.2980],
            ["Karawang Barat", "20237366", "SMAS KORPRI KARAWANG", "SMA", "SWASTA", -6.3070, 107.2950],
            ["Karawang Barat", "20265273", "SMAS KOSGORO KARAWANG", "SMA", "SWASTA", -6.3058, 107.2990],
            ["Karawang Barat", "20265284", "SMAS YOS SUDARSO", "SMA", "SWASTA", -6.3060, 107.2960],
            ["Karawang Barat", "69991703", "SMK BINA PENDIDIKAN 4", "SMK", "SWASTA", -6.3080, 107.2930],
            ["Karawang Barat", "20217802", "SMKN 1 KARAWANG", "SMK", "NEGERI", -6.3018, 107.3005],
            ["Karawang Barat", "20217801", "SMKN 2 KARAWANG", "SMK", "NEGERI", -6.3048, 107.2972],
            ["Karawang Barat", "20217792", "SMKS BHINNEKA KARAWANG", "SMK", "SWASTA", -6.3045, 107.2975],
            ["Karawang Barat", "20217809", "SMKS BINA KARYA 1 KARAWANG", "SMK", "SWASTA", -6.3065, 107.2955],
            ["Karawang Barat", "20217808", "SMKS BINA KARYA 2 KARAWANG", "SMK", "SWASTA", -6.3075, 107.2945],
            ["Karawang Barat", "20237401", "SMKS IBNU SINA KARAWANG", "SMK", "SWASTA", -6.3094, 107.2779],
            ["Karawang Barat", "20252214", "SMKS JAYABEKA 01 KARAWANG", "SMK", "SWASTA", -6.2942, 107.3219],
            ["Karawang Barat", "20217804", "SMKS KOSGORO KARAWANG", "SMK", "SWASTA", -6.3062, 107.2859],
            ["Karawang Barat", "20217799", "SMKS PENDEKAR KARAWANG", "SMK", "SWASTA", -6.3422, 107.3139],
            ["Karawang Barat", "20217798", "SMKS PGRI 1 KARAWANG", "SMK", "SWASTA", -6.2990, 107.2627],
            ["Karawang Barat", "20217797", "SMKS PGRI 2 KARAWANG", "SMK", "SWASTA", -6.2798, 107.3315],
            ["Karawang Barat", "20217794", "SMKS PROKLAMASI KARAWANG", "SMK", "SWASTA", -6.3078, 107.2875],
            ["Karawang Barat", "20265292", "SMKS ROSMA", "SMK", "SWASTA", -6.3050, 107.2980],
            ["Karawang Barat", "20217810", "SMKS TARUNA KARYA 1 KARAWANG", "SMK", "SWASTA", -6.2694, 107.3355],
            ["Karawang Barat", "20217849", "SMKS TARUNA KARYA 2 KARAWANG", "SMK", "SWASTA", -6.2806, 107.3395],
            ["Karawang Barat", "20237416", "SMKS TRIJAYA SAKTI KARAWANG", "SMK", "SWASTA", -6.3070, 107.2940],
            ["Karawang Barat", "20217915", "SMKS TUNAS MEKAR KARAWANG", "SMK", "SWASTA", -6.3014, 107.2667],
            ["Rawamerta", "70058917", "MA ANNIHAYAH", "MA", "SWASTA", -6.2480, 107.3480],
            ["Rawamerta", "70048186", "MA NU GARUNGGUNG", "MA", "SWASTA", -6.2450, 107.3450],
            ["Rawamerta", "20277058", "MAS NIHAYATUL AMAL RAWAMERTA", "MA", "SWASTA", -6.2510, 107.3510],
            ["Rawamerta", "70008487", "MAS NURUL HASBY", "MA", "SWASTA", -6.2492, 107.3316],
            ["Rawamerta", "70036745", "SMA RAUDHATUL IRFAN", "SMA", "SWASTA", -6.2540, 107.3364],
            ["Rawamerta", "69734347", "SMAN 1 RAWAMERTA", "SMA", "NEGERI", -6.2530, 107.3530],
            ["Rawamerta", "70000014", "SMK ANNIHAYAH", "SMK", "SWASTA", -6.2444, 107.3412],
            ["Rawamerta", "60728562", "SMKN 1 RAWAMERTA", "SMK", "NEGERI", -6.2500, 107.3500],
            ["Rawamerta", "20265293", "SMKS NIHAYATUL AMAL", "SMK", "SWASTA", -6.2520, 107.3520],
            ["Tempuran", "69941676", "MAS Fathanul Burhan", "MA", "SWASTA", -6.2210, 107.4120],
            ["Tempuran", "20217762", "SMAN 1 TEMPURAN", "SMA", "NEGERI", -6.2250, 107.4160],
            ["Tempuran", "69958770", "SMK INOTEK TEMPURAN", "SMK", "SWASTA", -6.2230, 107.4140],
            ["Tempuran", "69934151", "SMKS TARUNA KARYA MANDIRI", "SMK", "SWASTA", -6.2270, 107.4180],
            ["Kutawaluya", "20277060", "MAN 4 Karawang", "MA", "NEGERI", -6.21855, 107.35124],
            ["Kutawaluya", "20237379", "SMAN 1 RENGASDENGKLOK", "SMA", "NEGERI", -6.1950, 107.2950],
            ["Kutawaluya", "69958814", "SMK AL INAYAH KUTAMUKTI", "SMK", "SWASTA", -6.2120, 107.3120],
            ["Kutawaluya", "20217769", "SMKS AL HURRIYYAH KUTAWALUYA", "SMK", "SWASTA", -6.2486, 107.2814],
            ["Kutawaluya", "20246723", "SMKS PERBANKAN INDONESIA", "SMK", "SWASTA", -6.2438, 107.2830],
            ["Kutawaluya", "20217793", "SMKS RISTEK KARAWANG", "SMK", "SWASTA", -6.2170, 107.3170],
            ["Kutawaluya", "20258501", "SMKS TARUNA KARYA 76 NURUL FALAH KARAWANG", "SMK", "SWASTA", -6.2190, 107.3190],
            ["Rengasdengklok", "69958771", "SMK KALIMASYADA", "SMK", "SWASTA", -6.2220, 107.2764],
            ["Rengasdengklok", "69979166", "SMK MANDALLA ENTREPRENEUR SCHOOL", "SMK", "SWASTA", -6.1930, 107.2930],
            ["Rengasdengklok", "20237465", "SMKN 1 RENGASDENGKLOK", "SMK", "NEGERI", -6.1970, 107.2970],
            ["Rengasdengklok", "20237425", "SMKS LENTERA BANGSA KARAWANG", "SMK", "SWASTA", -6.1890, 107.2890],
            ["Rengasdengklok", "20237461", "SMKS MITRA KARYA RENGASDENGKLOK", "SMK", "SWASTA", -6.1940, 107.2940],
            ["Rengasdengklok", "20265290", "SMKS PGRI 3 KARAWANG", "SMK", "SWASTA", -6.1960, 107.2960],
            ["Rengasdengklok", "20258502", "SMKS TEKNOLOGI AL-BARKAH", "SMK", "SWASTA", -6.1980, 107.2980],
            ["Jayakerta", "69734352", "SMKN JAYAKERTA", "SMK", "NEGERI", -6.1854, 107.3110],
            ["Jayakerta", "20270746", "SMKS BITEC AL MASHURIAH", "SMK", "SWASTA", -6.1422, 107.2950],
            ["Jayakerta", "20237426", "SMKS NURUL ANSOR JAYAKERTA", "SMK", "SWASTA", -6.1582, 107.3078],
            ["Jayakerta", "20276408", "SMKS RISMATEK", "SMK", "SWASTA", -6.1350, 107.2366],
            ["Pedes", "70050942", "MA DARUL AMAL", "MA", "SWASTA", -6.1550, 107.3550],
            ["Pedes", "70058960", "MA PLUS NUR AL FATH 786", "MA", "SWASTA", -6.1520, 107.3520],
            ["Pedes", "20280382", "MAS DARUL HUDA", "MA", "SWASTA", -6.1580, 107.3580],
            ["Pedes", "20217788", "SMAN 1 PEDES", "SMA", "NEGERI", -6.1600, 107.3600],
            ["Pedes", "69827765", "SMK INDUSTRI TEKNOLOGI NASIONAL", "SMK", "SWASTA", -6.1540, 107.3540],
            ["Pedes", "20217916", "SMKS TRIJAYA SAKTI KARAWANG", "SMK", "SWASTA", -6.1570, 107.3570],
            ["Cilebar", "69734350", "SMKN CILEBAR", "SMK", "NEGERI", -6.1672, 107.3948],
            ["Cibuaya", "69904549", "SMAN 1 CIBUAYA", "SMA", "NEGERI", -6.1250, 107.3650],
            ["Cibuaya", "69873937", "SMK YASPIF", "SMK", "SWASTA", -6.1180, 107.3580],
            ["Cibuaya", "20271865", "SMKS YAPINAS", "SMK", "SWASTA", -6.1230, 107.3630],
            ["Tirtajaya", "69964922", "SMAN 1 TIRTAJAYA", "SMA", "NEGERI", -6.0950, 107.2850],
            ["Tirtajaya", "69957014", "SMK MITRA JAYA TIRTAJAYA", "SMK", "SWASTA", -6.0920, 107.2820],
            ["Tirtajaya", "69734356", "SMKN 1 TIRTAJAYA", "SMK", "NEGERI", -6.0980, 107.2880],
            ["Batujaya", "70049865", "MA AL MUQODDAM", "MA", "SWASTA", -6.0520, 107.2520],
            ["Batujaya", "70047049", "MA DAARUL HUFFAZH", "MA", "SWASTA", -6.0480, 107.2480],
            ["Batujaya", "20280379", "MAN 3 Karawang", "MA", "NEGERI", -6.0550, 107.2550],
            ["Batujaya", "20280376", "MAS AL IKHLAS", "MA", "SWASTA", -6.0580, 107.2580],
            ["Batujaya", "20280385", "MAS MATHLAUL ANWAR", "MA", "SWASTA", -6.0510, 107.2510],
            ["Batujaya", "20277059", "MAS NURUL FALAH", "MA", "SWASTA", -6.0540, 107.2540],
            ["Batujaya", "20237370", "SMAN 1 BATUJAYA", "SMA", "NEGERI", -6.0500, 107.2500],
            ["Batujaya", "20265274", "SMAS MATHLA UL ANWAR", "SMA", "SWASTA", -6.0530, 107.2530],
            ["Batujaya", "69807800", "SMK MINHAJUL FALAH", "SMK", "SWASTA", -6.0560, 107.2560],
            ["Batujaya", "69734353", "SMKN BATUJAYA", "SMK", "NEGERI", -6.0490, 107.2490],
            ["Batujaya", "20237457", "SMKS AL IKHLAS BATUJAYA", "SMK", "SWASTA", -6.0525, 107.2525],
            ["Batujaya", "60728561", "SMKS MATHLAUL ANWAR", "SMK", "SWASTA", -6.0535, 107.2535],
            ["Batujaya", "20258507", "SMKS SAINTEK NURUL MUSLIMIN KARAWANG", "SMK", "SWASTA", -6.0515, 107.2515],
            ["Pakisjaya", "20246722", "SMAS ANWARUL HIDAYAH", "SMA", "SWASTA", -6.0150, 107.2150],
            ["Pakisjaya", "69816446", "SMKN 1 PAKISJAYA", "SMK", "NEGERI", -6.0120, 107.2120],
            ["Pakisjaya", "20218066", "SMKS MATHLAUL ANWAR PAKISJAYA", "SMK", "SWASTA", -6.0180, 107.2180],
        ];

        $scoringService = app(SchoolScoringService::class);

        foreach ($rawRows as $r) {
            $districtName = $r[0];
            $npsn = $r[1];
            $name = strtoupper(trim($r[2]));
            $stName = strtoupper(trim($r[3]));
            $status = strtolower(trim($r[4]));
            $lat = (float)$r[5];
            $lng = (float)$r[6];

            $district = District::where('name', 'like', "%{$districtName}%")->first();
            $districtId = $district ? $district->id : 1;

            $st = SchoolType::where('name', $stName)->first();
            $stId = $st ? $st->id : 1;

            $distFromCampus = $scoringService->calculateDistanceFromCampus($lat, $lng);

            $school = School::where('npsn', $npsn)
                ->orWhere('name', 'like', "%{$name}%")
                ->first();

            $data = [
                'npsn' => $npsn,
                'name' => $name,
                'district_id' => $districtId,
                'school_type_id' => $stId,
                'status' => $status,
                'latitude' => $lat,
                'longitude' => $lng,
                'distance_from_campus' => $distFromCampus,
                'status_school' => 'aktif',
            ];

            if ($school) {
                $school->update($data);
            } else {
                $data['student_count'] = rand(400, 1100);
                $data['grade_12_count'] = rand(120, 350);
                School::create($data);
            }
        }

        // Recalculate priority scores for all schools
        $scoringService->recalculateAllSchoolScores();
    }
}
