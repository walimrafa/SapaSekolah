<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_student_intakes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('intake_period', 30);
            $table->unsignedInteger('student_count');
            $table->timestamps();

            $table->unique(['school_id', 'intake_period']);
            $table->index('intake_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_student_intakes');
    }
};
