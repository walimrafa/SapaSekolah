<?php

namespace Database\Seeders;

use App\Models\ScoringCriterion;
use Illuminate\Database\Seeder;

class ScoringCriterionSeeder extends Seeder
{
    /**
     * Bobot scoring sesuai spesifikasi:
     * - Jumlah Siswa Kelas XII: 30%
     * - Minat Kuliah: 20%
     * - Riwayat PMB: 20%
     * - Hubungan dengan Sekolah: 10%
     * - Jarak dari Kampus: 10%
     * - Potensi Kerja Sama: 10%
     */
    public function run(): void
    {
        $criteria = [
            [
                'name' => 'Jumlah Siswa Kelas XII',
                'weight' => 30.00,
                'max_score' => 100,
                'status' => true,
            ],
            [
                'name' => 'Minat Kuliah',
                'weight' => 20.00,
                'max_score' => 100,
                'status' => true,
            ],
            [
                'name' => 'Riwayat PMB',
                'weight' => 20.00,
                'max_score' => 100,
                'status' => true,
            ],
            [
                'name' => 'Hubungan dengan Sekolah',
                'weight' => 10.00,
                'max_score' => 100,
                'status' => true,
            ],
            [
                'name' => 'Jarak dari Kampus',
                'weight' => 10.00,
                'max_score' => 100,
                'status' => true,
            ],
            [
                'name' => 'Potensi Kerja Sama',
                'weight' => 10.00,
                'max_score' => 100,
                'status' => true,
            ],
        ];

        foreach ($criteria as $criterion) {
            ScoringCriterion::updateOrCreate(['name' => $criterion['name']], $criterion);
        }
    }
}
