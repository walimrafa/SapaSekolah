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
        Schema::create('marketing_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->date('date');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('start_location', 255)->nullable();
            $table->string('end_location', 255)->nullable();
            $table->decimal('total_distance', 8, 2)->nullable()->comment('Jarak dalam km');
            $table->integer('estimated_duration')->nullable()->comment('Durasi estimasi dalam menit');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->timestamps();

            $table->index('user_id');
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_routes');
    }
};
