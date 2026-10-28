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
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('visit_id')->nullable()->constrained('school_visits')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('followup_date');
            $table->enum('method', ['wa', 'telepon', 'email', 'visit', 'lainnya'])->default('wa');
            $table->text('description')->nullable();
            $table->text('result')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->enum('status', ['pending', 'selesai', 'tidak_berhasil'])->default('pending');
            $table->timestamps();

            $table->index('school_id');
            $table->index('visit_id');
            $table->index('user_id');
            $table->index('followup_date');
            $table->index('status');
            $table->index('next_followup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
