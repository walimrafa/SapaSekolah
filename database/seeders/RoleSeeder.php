<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'Admin', 'description' => 'Administrator sistem dengan akses penuh'],
            ['name' => 'Pimpinan', 'description' => 'Pimpinan yang dapat melihat semua laporan dan dashboard'],
            ['name' => 'Koordinator Marketing', 'description' => 'Koordinator yang mengelola tim marketing'],
            ['name' => 'Koordinator Customer Service', 'description' => 'Koordinator CS yang mengelola ploting data follow-up siswa serta memantau dan mengontrol progres tim Customer Service'],
            ['name' => 'Marketing', 'description' => 'Staff marketing yang melakukan kunjungan dan follow-up'],
            ['name' => 'Customer Service', 'description' => 'Staff CS / Telemarketing yang mengelola prospek, kuisioner, dan follow-up siswa'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
