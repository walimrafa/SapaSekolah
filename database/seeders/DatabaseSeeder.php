<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SchoolTypeSeeder::class,
            AccreditationSeeder::class,
            ContactPositionSeeder::class,
            ScoringCriterionSeeder::class,
            StudyProgramSeeder::class,
            AdminUserSeeder::class,
        ]);
    }
}
