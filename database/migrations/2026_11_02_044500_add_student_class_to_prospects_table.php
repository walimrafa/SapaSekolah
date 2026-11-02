<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (!Schema::hasColumn('prospects', 'student_class')) {
                $table->string('student_class', 50)->nullable()->after('nim');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (Schema::hasColumn('prospects', 'student_class')) {
                $table->dropColumn('student_class');
            }
        });
    }
};
