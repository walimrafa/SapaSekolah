<?php

namespace Database\Seeders;

use App\Models\ContactPosition;
use Illuminate\Database\Seeder;

class ContactPositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['name' => 'Kepala Sekolah', 'description' => 'Kepala Sekolah / Pimpinan Sekolah'],
            ['name' => 'Wakasek', 'description' => 'Wakil Kepala Sekolah'],
            ['name' => 'Wakasek Humas', 'description' => 'Wakil Kepala Sekolah Bidang Humas'],
            ['name' => 'Guru BK', 'description' => 'Guru Bimbingan Konseling'],
            ['name' => 'BKK', 'description' => 'Bursa Kerja Khusus'],
            ['name' => 'TU', 'description' => 'Tata Usaha'],
            ['name' => 'Operator Sekolah', 'description' => 'Operator Data Sekolah'],
            ['name' => 'Pembina OSIS', 'description' => 'Pembina Organisasi Siswa Intra Sekolah'],
            ['name' => 'PIC Kerjasama', 'description' => 'Person In Charge untuk Kerjasama'],
        ];

        foreach ($positions as $position) {
            ContactPosition::updateOrCreate(['name' => $position['name']], $position);
        }
    }
}
