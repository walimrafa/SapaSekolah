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
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('name', 100);
            $table->enum('gender', ['L', 'P'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->integer('graduation_year')->nullable();
            $table->string('interest_program', 100)->nullable();
            $table->string('source', 50)->nullable()->comment('kunjungan, pameran, website, referral, dll');
            $table->enum('status', ['lead', 'prospek', 'daftar', 'registrasi', 'bayar', 'mahasiswa', 'tidak_lanjut'])->default('lead');
            $table->foreignId('marketing_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('school_id');
            $table->index('marketing_user_id');
            $table->index('status');
            $table->index('graduation_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
