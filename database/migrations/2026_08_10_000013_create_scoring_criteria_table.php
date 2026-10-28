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
        Schema::create('scoring_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->decimal('weight', 5, 2)->comment('Bobot dalam persen, total harus 100');
            $table->integer('max_score')->default(100);
            $table->boolean('status')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scoring_criteria');
    }
};
