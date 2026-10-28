<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Move schools pointing to 'Teluk Jambe Timur' (ID 31) to canonical 'Telukjambe Timur' (ID 4)
        DB::table('schools')->where('district_id', 31)->update(['district_id' => 4]);

        // 2. Move schools pointing to 'Teluk Jambe Barat' (ID 32) to canonical 'Telukjambe Barat' (ID 5)
        DB::table('schools')->where('district_id', 32)->update(['district_id' => 5]);

        // 3. Delete redundant district records
        DB::table('districts')->whereIn('id', [31, 32])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op rollback
    }
};
