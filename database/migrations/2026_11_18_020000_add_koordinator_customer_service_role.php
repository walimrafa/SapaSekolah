<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->updateOrInsert(
                ['name' => 'Koordinator Customer Service'],
                [
                    'description' => 'Koordinator CS yang mengelola ploting data follow-up siswa serta memantau dan mengontrol progres tim Customer Service',
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('roles')) {
            DB::table('roles')->where('name', 'Koordinator Customer Service')->delete();
        }
    }
};
