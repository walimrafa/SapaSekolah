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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('writing_style', 100)->default('Judul Paling Menarik');
            $table->string('opinion_author', 255)->nullable();
            $table->string('category', 100)->default('NEWS');
            $table->string('campus_context', 100)->nullable(); // UBSI Karawang, UBSI Cikampek, Signaltodays, or null (Umum)
            
            // Liputan Kegiatan Fields
            $table->boolean('is_event')->default(false);
            $table->string('event_category', 50)->nullable(); // Pra Event, Hari H Event, Pasca Event
            $table->string('event_organizer', 255)->nullable(); // Penyelenggara
            $table->string('event_host', 255)->nullable(); // Pembawa Acara
            $table->string('event_speaker', 255)->nullable(); // Narasumber
            $table->string('event_executor', 255)->nullable(); // Pelaksana
            $table->string('event_participants', 255)->nullable(); // Peserta
            $table->string('event_location_time', 255)->nullable(); // Tempat dan Waktu
            
            // Media & Content
            $table->string('featured_image', 500)->nullable();
            $table->string('image_source', 100)->nullable(); // uploaded, pexels, fallback
            $table->longText('content')->nullable();
            $table->integer('word_count')->default(0);
            
            // Status & Lifecycle
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->string('docx_path', 500)->nullable();
            $table->timestamp('scheduled_at')->nullable();
            
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
