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
        Schema::create('route_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('marketing_routes')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->integer('sequence')->default(1);
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->enum('status', ['pending', 'visited', 'skipped'])->default('pending');
            $table->text('notes')->nullable();

            $table->index('route_id');
            $table->index('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_schools');
    }
};
