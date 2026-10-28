<?php

namespace Database\Seeders;

use App\Models\Accreditation;
use Illuminate\Database\Seeder;

class AccreditationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accreditations = [
            ['name' => 'Akreditasi A', 'code' => 'A', 'description' => 'Akreditasi A - Unggul'],
            ['name' => 'Akreditasi B', 'code' => 'B', 'description' => 'Akreditasi B - Baik'],
            ['name' => 'Akreditasi C', 'code' => 'C', 'description' => 'Akreditasi C - Cukup'],
            ['name' => 'Belum Terakreditasi', 'code' => 'BT', 'description' => 'Belum Terakreditasi'],
        ];

        foreach ($accreditations as $accreditation) {
            Accreditation::updateOrCreate(['code' => $accreditation['code']], $accreditation);
        }
    }
}
