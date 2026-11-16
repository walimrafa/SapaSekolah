<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('database_backups', 'format')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->string('format', 20)->default('sql')->after('type'); // 'sql', 'excel'
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('database_backups', 'format')) {
            Schema::table('database_backups', function (Blueprint $table) {
                $table->dropColumn('format');
            });
        }
    }
};
