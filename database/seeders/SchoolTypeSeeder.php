<?php

namespace Database\Seeders;

use App\Models\SchoolType;
use Illuminate\Database\Seeder;

class SchoolTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'SMA'],
            ['name' => 'SMK'],
            ['name' => 'MA'],
            ['name' => 'SLB'],
        ];

        foreach ($types as $type) {
            SchoolType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}
