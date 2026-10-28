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
            $table->string('student_email', 150)->nullable()->change();
            $table->string('guardian_name', 150)->nullable()->change();
            $table->string('guardian_phone', 30)->nullable()->change();
            $table->json('answers')->nullable()->change();
            $table->string('student_class', 50)->nullable()->change();
            $table->string('student_major', 100)->nullable()->change();
            $table->string('scholarship_interest', 10)->default('ya')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_questionnaires', function (Blueprint $table) {
            $table->string('student_email', 150)->nullable(false)->change();
            $table->string('guardian_name', 150)->nullable(false)->change();
            $table->string('guardian_phone', 30)->nullable(false)->change();
            $table->json('answers')->nullable(false)->change();
            $table->string('student_class', 50)->nullable(false)->change();
            $table->string('student_major', 100)->nullable(false)->change();
        });
    }
};
