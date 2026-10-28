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
        Schema::create('period_closing_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 500);
            $table->string('academic_year', 50)->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');

            // 1. Persetujuan Pimpinan
            $table->foreignId('pimpinan_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('pimpinan_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('pimpinan_notes')->nullable();
            $table->timestamp('pimpinan_action_at')->nullable();

            // 2. Persetujuan Koordinator Marketing
            $table->foreignId('koordinator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('koordinator_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('koordinator_notes')->nullable();
            $table->timestamp('koordinator_action_at')->nullable();

            // 3. Persetujuan Customer Service
            $table->foreignId('cs_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('cs_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('cs_notes')->nullable();
            $table->timestamp('cs_action_at')->nullable();

            // Eksekusi Akhir
            $table->foreignId('executed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('executed_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('period_closing_requests');
    }
};
