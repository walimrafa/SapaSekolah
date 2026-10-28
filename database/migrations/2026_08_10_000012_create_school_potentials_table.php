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
        Schema::create('school_potentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->integer('total_students')->default(0);
            $table->integer('grade_12_students')->default(0);
            $table->decimal('estimated_college_interest', 5, 2)->default(0)->comment('Persentase minat kuliah');
            $table->decimal('estimated_it_interest', 5, 2)->default(0)->comment('Persentase minat IT');
            $table->decimal('estimated_business_interest', 5, 2)->default(0)->comment('Persentase minat bisnis');
            $table->integer('previous_students')->default(0)->comment('Mahasiswa dari sekolah ini sebelumnya');
            $table->integer('competitor_count')->default(0)->comment('Jumlah kompetitor yang masuk');
            $table->enum('cooperation_status', ['aktif', 'tidak_aktif', 'belum'])->default('belum');
            $table->decimal('potential_score', 5, 2)->default(0);
            $table->text('notes')->nullable();

            $table->index('school_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_potentials');
    }
};
