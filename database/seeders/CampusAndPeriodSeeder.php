<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Campus;
use App\Models\School;
use App\Models\SchoolVisit;
use App\Models\VisitPlan;
use App\Models\Prospect;
use App\Models\StudentQuestionnaire;
use App\Models\FollowUp;
use App\Models\MarketingRoute;
use App\Models\SchoolEventProposal;
use Illuminate\Database\Seeder;

class CampusAndPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Kampus
        $campuses = [
            [
                'code' => 'KRW',
                'name' => 'UBSI Kampus Karawang',
                'city' => 'Kabupaten Karawang',
                'address' => 'Jalan Banten No.1, Kelurahan Karangpawitan, Kecamatan Karawang Barat, Kabupaten Karawang, Jawa Barat 41351',
                'latitude' => -6.30540000,
                'longitude' => 107.29950000,
                'phone' => '081220022851',
                'email' => 'karawang@bsi.ac.id',
                'website' => 'https://bsi.ac.id',
                'is_active' => true,
                'is_default' => true,
                'notes' => 'Kampus Utama Karawang Barat',
            ],
            [
                'code' => 'CKP',
                'name' => 'UBSI Kampus Cikampek',
                'city' => 'Kabupaten Karawang',
                'address' => 'Jl. Ir. H. Juanda No.17, Sarimulya, Kec. Kotabaru, Cikampek, Karawang, Jawa Barat 41374',
                'latitude' => -6.40870000,
                'longitude' => 107.45890000,
                'phone' => '085695662714',
                'email' => 'cikampek@bsi.ac.id',
                'website' => 'https://bsi.ac.id',
                'is_active' => true,
                'is_default' => false,
                'notes' => 'Kampus Wilayah Cikampek & Purwakarta',
            ],
            [
                'code' => 'CKR',
                'name' => 'UBSI Kampus Cikarang',
                'city' => 'Kabupaten Bekasi',
                'address' => 'Jl. Cikarang Baru Raya Kav. 29-31 Kota Jababeka, Cikarang Utara, Bekasi, Jawa Barat 17530',
                'latitude' => -6.31420000,
                'longitude' => 107.16250000,
                'phone' => '(021) 89108000',
                'email' => 'cikarang@bsi.ac.id',
                'website' => 'https://bsi.ac.id',
                'is_active' => true,
                'is_default' => false,
                'notes' => 'Kampus Wilayah Kawasan Industri Bekasi & Cikarang',
            ],
        ];

        $defaultCampus = null;
        foreach ($campuses as $campusData) {
            $campus = Campus::updateOrCreate(['code' => $campusData['code']], $campusData);
            if ($campus->is_default) {
                $defaultCampus = $campus;
            }
        }

        // 2. Seed Periode Akademik
        $periods = [
            [
                'name' => '2025/2026',
                'start_date' => '2025-09-01',
                'end_date' => '2026-08-31',
                'is_active' => true,
                'is_current' => false,
                'notes' => 'Tahun Ajaran / Periode PMB 2025/2026',
            ],
            [
                'name' => '2026/2027',
                'start_date' => '2026-09-01',
                'end_date' => '2027-08-31',
                'is_active' => true,
                'is_current' => true,
                'notes' => 'Tahun Ajaran / Periode PMB Aktif Saat Ini 2026/2027',
            ],
            [
                'name' => '2027/2028',
                'start_date' => '2027-09-01',
                'end_date' => '2028-08-31',
                'is_active' => false,
                'is_current' => false,
                'notes' => 'Tahun Ajaran PMB Mendatang',
            ],
        ];

        $currentPeriod = null;
        foreach ($periods as $periodData) {
            $p = AcademicPeriod::updateOrCreate(['name' => $periodData['name']], $periodData);
            if ($p->is_current) {
                $currentPeriod = $p;
            }
        }

        // 3. Kaitkan data existing yang masih null ke default campus & current period
        if ($defaultCampus && $currentPeriod) {
            School::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id]);
            SchoolVisit::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id, 'academic_period_id' => $currentPeriod->id]);
            VisitPlan::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id, 'academic_period_id' => $currentPeriod->id]);
            MarketingRoute::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id, 'academic_period_id' => $currentPeriod->id]);
            FollowUp::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id, 'academic_period_id' => $currentPeriod->id]);
            Prospect::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id, 'academic_period_id' => $currentPeriod->id]);
            StudentQuestionnaire::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id, 'academic_period_id' => $currentPeriod->id]);
            SchoolEventProposal::whereNull('campus_id')->update(['campus_id' => $defaultCampus->id, 'academic_period_id' => $currentPeriod->id]);
        }
    }
}

