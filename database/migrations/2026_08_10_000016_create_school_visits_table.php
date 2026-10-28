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
        Schema::create('school_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('visit_plan_id')->nullable()->constrained('visit_plans')->nullOnDelete();
            $table->date('visit_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('contact_person', 100)->nullable();
            $table->text('purpose')->nullable();
            $table->text('result')->nullable();
            $table->string('photo', 255)->nullable();
            $table->text('notes')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->enum('status', ['berhasil', 'tidak_berhasil', 'tidak_bertemu', 'pending'])->default('pending');
            $table->timestamps();

            $table->index('school_id');
            $table->index('user_id');
            $table->index('visit_plan_id');
            $table->index('visit_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_visits');
    }
};
