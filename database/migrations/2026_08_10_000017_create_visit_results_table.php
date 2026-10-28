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
        Schema::create('visit_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visit_id')->constrained('school_visits')->cascadeOnDelete();
            $table->string('result_type', 50);
            $table->text('description')->nullable();

            $table->index('visit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visit_results');
    }
};
