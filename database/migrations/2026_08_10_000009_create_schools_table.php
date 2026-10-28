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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('npsn', 20)->unique();
            $table->string('name', 200);
            $table->foreignId('school_type_id')->nullable()->constrained('school_types')->nullOnDelete();
            $table->enum('status', ['negeri', 'swasta'])->default('negeri');
            $table->foreignId('province_id')->nullable()->constrained('provinces')->nullOnDelete();
            $table->foreignId('regency_id')->nullable()->constrained('regencies')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('village_id')->nullable()->constrained('villages')->nullOnDelete();
            $table->text('address')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('website', 200)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->foreignId('accreditation_id')->nullable()->constrained('accreditations')->nullOnDelete();
            $table->integer('student_count')->default(0);
            $table->integer('grade_10_count')->default(0);
            $table->integer('grade_11_count')->default(0);
            $table->integer('grade_12_count')->default(0);
            $table->decimal('potential_score', 5, 2)->default(0);
            $table->enum('priority_level', ['A', 'B', 'C'])->nullable();
            $table->enum('status_school', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for frequent queries
            $table->index('school_type_id');
            $table->index('province_id');
            $table->index('regency_id');
            $table->index('district_id');
            $table->index('village_id');
            $table->index('accreditation_id');
            $table->index('priority_level');
            $table->index('status');
            $table->index('status_school');
            $table->index('potential_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
