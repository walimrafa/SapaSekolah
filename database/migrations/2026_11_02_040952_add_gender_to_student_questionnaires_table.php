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
        Schema::table('student_questionnaires', function (Blueprint $table) {
            if (!Schema::hasColumn('student_questionnaires', 'gender')) {
                $table->string('gender', 10)->nullable()->after('student_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_questionnaires', function (Blueprint $table) {
            if (Schema::hasColumn('student_questionnaires', 'gender')) {
                $table->dropColumn('gender');
            }
        });
    }
};
