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
        Schema::create('marketing_activities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200);
            $table->string('activity_type', 50)->comment('sosialisasi, presentasi, pameran, workshop, dll');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_activities');
    }
};
