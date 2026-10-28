<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('student_name', 150);
            $table->string('student_phone', 30);
            $table->string('student_email', 150);
            $table->string('student_social_media')->nullable();
            $table->string('guardian_name', 150);
            $table->string('guardian_phone', 30);
            $table->string('guardian_social_media')->nullable();
            $table->json('answers');
            $table->timestamps();

            $table->index('student_name');
            $table->index('student_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_questionnaires');
    }
};
