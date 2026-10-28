<?php

namespace Database\Seeders;

use App\Models\ContactPosition;
use App\Models\Prospect;
use App\Models\School;
use App\Models\SchoolContact;

use App\Services\SchoolScoringService;
use Illuminate\Database\Seeder;

class OneCampusThirtySeeder extends Seeder
{
    public function run(): void
    {
        $bkPosition = ContactPosition::where('name', 'like', '%BK%')->first();
        $positionId = $bkPosition ? $bkPosition->id : 1;

        // Data 40 Sekolah "One Campus Thirty School" & Jumlah PMB ke UBSI Karawang
        $data = [
            ['name' => 'SMA Darunnadwah Islam', 'teacher' => 'Pak Azis', 'phone' => '081382889592', 'pmb' => 0],
            ['name' => 'SMK Ristek', 'teacher' => 'Pak Ujang', 'phone' => '085882628531', 'pmb' => 0],
            ['name' => 'MA Nihayatul Amal Rawamerta', 'teacher' => 'Pak Endang', 'phone' => '085280497173', 'pmb' => 10],
            ['name' => 'SMAN 1 Batujaya', 'teacher' => 'Bu Feti', 'phone' => '081510664159', 'pmb' => 3],
            ['name' => 'SMAN 1 Ciampel', 'teacher' => 'Bu Ficky', 'phone' => '085776111078', 'pmb' => 3],
            ['name' => 'SMAN 1 Cibuaya', 'teacher' => 'Bu Intan', 'phone' => '085780050283', 'pmb' => 2],
            ['name' => 'SMK INOTEK Tempuran', 'teacher' => 'Bu Nisa', 'phone' => '085782671877', 'pmb' => 1],
            ['name' => 'SMK IT Nurul Ilmi', 'teacher' => 'Bu Novi', 'phone' => '082127080338', 'pmb' => 1],
            ['name' => 'SMKN 1 Karawang', 'teacher' => 'Pak Badru', 'phone' => '083814337801', 'pmb' => 4],
            ['name' => 'SMAN 1 Klari', 'teacher' => 'Bu Ambar', 'phone' => '085697177456', 'pmb' => 7],
            ['name' => 'SMAN 1 Pedes', 'teacher' => 'Ibu Umsiah', 'phone' => '08164835443', 'pmb' => 2],
            ['name' => 'SMK ANNIHAYAH RAWAMERTA', 'teacher' => 'Pak Guntur', 'phone' => '085780470165', 'pmb' => 0],
            ['name' => 'SMK INDUSTRI MANDIRI KARAWANG', 'teacher' => 'Pak Luqman', 'phone' => '081585198307', 'pmb' => 10],
            ['name' => 'SMKN 1 RENGASDENGKLOK', 'teacher' => 'Ibu Yeni', 'phone' => '082218121466', 'pmb' => 1],
            ['name' => 'SMAN 1 RENGASDENGKLOK', 'teacher' => 'Ibu Salsa', 'phone' => '085648064608', 'pmb' => 0],
            ['name' => 'SMK TK 76 NURUL FALAH', 'teacher' => 'Ibu Putri', 'phone' => '089604506514', 'pmb' => 0],
            ['name' => 'SMKN 1 Rawamerta', 'teacher' => 'Ibu Wiwin', 'phone' => '085691830425', 'pmb' => 0],
            ['name' => 'SMAN 1 RAWAMERTA', 'teacher' => 'Ibu Suparti', 'phone' => '089606671708', 'pmb' => 6],
            ['name' => 'SMA MUHAMMADIYAH KARAWANG', 'teacher' => 'Pak Rois', 'phone' => '085714892466', 'pmb' => 0],
            ['name' => 'SMK LABOR TI KARAWANG', 'teacher' => 'Pak Nuryadi', 'phone' => '0895387565062', 'pmb' => 0],
            ['name' => 'SMA BUDI MULYA', 'teacher' => 'Pak Nur', 'phone' => '081281206616', 'pmb' => 0],
            ['name' => 'SMK TEXMACO KARAWANG', 'teacher' => 'Pak Sulaeman', 'phone' => '081286759299', 'pmb' => 0],
            ['name' => 'SMK WIRASABA', 'teacher' => 'Ibu Liana', 'phone' => '08986219516', 'pmb' => 1],
            ['name' => 'SMA TUNAS DHARMA', 'teacher' => 'Pak Dean', 'phone' => '081289694556', 'pmb' => 0],
            ['name' => 'SMA KORPRI', 'teacher' => 'Pak Rifki', 'phone' => '087780522558', 'pmb' => 0],
            ['name' => 'SMAN 1 Majalaya', 'teacher' => 'Ibu Cici Eko Lestari', 'phone' => '085718740297', 'pmb' => 0],
            ['name' => 'SMKN 3 Karawang', 'teacher' => 'Ibu Nadya', 'phone' => '089699398308', 'pmb' => 10],
            ['name' => 'SMKN 2 Karawang', 'teacher' => 'Pak Bimantya', 'phone' => '085716878880', 'pmb' => 3],
            ['name' => 'SMKN 1 Rengasdengklok', 'teacher' => 'Bpk Dede Badru', 'phone' => '083814337801', 'pmb' => 0],
            ['name' => 'SMAN 6 Karawang', 'teacher' => 'Ibu Wawat', 'phone' => '082112651187', 'pmb' => 0],
            ['name' => 'SMAN 5 Karawang', 'teacher' => 'Ibu Indah', 'phone' => '082218116431', 'pmb' => 0],
            ['name' => 'SMAN 4 Karawang', 'teacher' => 'Ibu Yunia', 'phone' => '081381821077', 'pmb' => 3],
            ['name' => 'SMAN 3 Karawang', 'teacher' => 'Ibu Meita', 'phone' => '085290205939', 'pmb' => 0],
            ['name' => 'SMAN 2 Karawang', 'teacher' => 'Ibu Aida', 'phone' => '081584860369', 'pmb' => 6],
            ['name' => 'SMAN 1 Karawang', 'teacher' => 'Ibu Raden Dewi', 'phone' => '085697536323', 'pmb' => 0],
        ];

        foreach ($data as $item) {
            // Find or create school by name
            $school = School::where('name', 'like', "%{$item['name']}%")->first();

            if (!$school) {
                $isNegeri = str_contains(strtoupper($item['name']), 'SMAN') || str_contains(strtoupper($item['name']), 'SMKN');
                $school = School::create([
                    'npsn' => '202' . rand(10000, 99999),
                    'name' => strtoupper($item['name']),
                    'status' => $isNegeri ? 'negeri' : 'swasta',
                    'student_count' => rand(400, 1200),
                    'grade_12_count' => rand(150, 450),
                    'latitude' => -6.3054 + (rand(-100, 100) * 0.003),
                    'longitude' => 107.2995 + (rand(-100, 100) * 0.003),
                ]);
            }

            // Create or update Guru BK Contact
            SchoolContact::updateOrCreate(
                ['school_id' => $school->id, 'name' => $item['teacher']],
                [
                    'position_id' => $positionId,
                    'phone' => $item['phone'],
                    'whatsapp' => $item['phone'],
                    'is_primary' => true,
                ]
            );

            // Create exact PMB converted students (Prospects with status = mahasiswa)
            Prospect::where('school_id', $school->id)->where('status', 'mahasiswa')->delete();

            for ($i = 1; $i <= $item['pmb']; $i++) {
                Prospect::create([
                    'name' => "Mahasiswa UBSI {$school->name} #{$i}",
                    'school_id' => $school->id,
                    'phone' => '089' . rand(10000000, 99999999),
                    'graduation_year' => 2026,
                    'interest_program' => 'Teknik Informatika',
                    'status' => 'mahasiswa',
                ]);
            }
        }

        // Recalculate priority scores based on updated real PMB numbers
        app(SchoolScoringService::class)->recalculateAllSchoolScores();
    }
}
