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
        Schema::create('database_backups', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->index();
            $table->string('disk')->default('local');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0); // in bytes
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->string('type', 30)->default('manual'); // 'manual', 'scheduled'
            $table->string('status', 30)->default('pending'); // 'pending', 'success', 'failed'
            $table->string('gdrive_status', 30)->default('pending'); // 'pending', 'uploaded', 'failed', 'not_configured'
            $table->string('gdrive_folder_id')->nullable();
            $table->string('gdrive_file_id')->nullable();
            $table->text('gdrive_file_url')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_backups');
    }
};
