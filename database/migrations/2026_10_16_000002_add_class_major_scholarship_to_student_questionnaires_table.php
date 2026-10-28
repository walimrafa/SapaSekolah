<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_questionnaires', function (Blueprint $table) {
            $table->string('student_class', 50)->after('student_name');
            $table->string('student_major', 100)->after('student_class');
            $table->string('scholarship_interest', 10)->after('guardian_social_media');
        });
    }

    public function down(): void
    {
        Schema::table('student_questionnaires', function (Blueprint $table) {
            $table->dropColumn(['student_class', 'student_major', 'scholarship_interest']);
        });
    }
};
