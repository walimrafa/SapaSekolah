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
        Schema::create('school_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('criterion_id')->constrained('scoring_criteria')->cascadeOnDelete();
            $table->decimal('score', 5, 2)->default(0);
            $table->decimal('weighted_score', 5, 2)->default(0);
            $table->string('period', 10)->nullable()->comment('Periode penilaian, misal: 2026/2027');

            $table->index('school_id');
            $table->index('criterion_id');
            $table->index('period');
            $table->unique(['school_id', 'criterion_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_scores');
    }
};
