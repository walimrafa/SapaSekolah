<?php

namespace Database\Seeders;

use App\Models\StudyProgram;
use Illuminate\Database\Seeder;

class StudyProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            ['name' => 'Teknik Informatika', 'code' => 'TI', 'faculty' => 'Fakultas Teknik', 'status' => true],
            ['name' => 'Sistem Informasi', 'code' => 'SI', 'faculty' => 'Fakultas Teknik', 'status' => true],
            ['name' => 'Manajemen', 'code' => 'MN', 'faculty' => 'Fakultas Ekonomi & Bisnis', 'status' => true],
            ['name' => 'Akuntansi', 'code' => 'AK', 'faculty' => 'Fakultas Ekonomi & Bisnis', 'status' => true],
            ['name' => 'Ilmu Komunikasi', 'code' => 'IK', 'faculty' => 'Fakultas Ilmu Sosial', 'status' => true],
            ['name' => 'Desain Komunikasi Visual', 'code' => 'DKV', 'faculty' => 'Fakultas Desain', 'status' => true],
            ['name' => 'Ilmu Hukum', 'code' => 'HK', 'faculty' => 'Fakultas Hukum', 'status' => true],
            ['name' => 'Pendidikan Guru Sekolah Dasar', 'code' => 'PGSD', 'faculty' => 'Fakultas Keguruan', 'status' => true],
        ];

        foreach ($programs as $program) {
            StudyProgram::updateOrCreate(['code' => $program['code']], $program);
        }
    }
}
