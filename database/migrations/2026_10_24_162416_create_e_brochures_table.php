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
        Schema::create('e_brochures', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('category', 100)->default('Brosur Utama');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 20)->default('pdf'); // 'pdf' or 'image'
            $table->string('file_extension', 10)->default('pdf');
            $table->unsignedBigInteger('file_size')->default(0); // in bytes
            $table->string('academic_year', 20)->default('2026/2027');
            $table->text('description')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('e_brochures');
    }
};
