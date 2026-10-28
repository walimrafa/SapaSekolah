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
        Schema::create('school_event_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 50)->unique();
            
            // Asal Sekolah & Kontak PIC
            $table->foreignId('school_id')->nullable()->constrained('schools')->nullOnDelete();
            $table->string('school_name', 200);
            $table->string('pic_name', 150);
            $table->string('pic_phone', 50);
            $table->string('pic_position', 100)->nullable(); // e.g. Pembina OSIS, Ketua Panitia, Guru BK

            // Detail Event / Kegiatan
            $table->string('event_name', 200);
            $table->date('event_date');
            $table->date('event_end_date')->nullable();
            $table->string('event_time', 100); // e.g. 08:00 - 15:00 WIB
            $table->string('event_location', 255);
            $table->string('target_participants', 150); // e.g. 500 Siswa & Guru
            $table->string('support_type_requested', 255)->nullable(); // e.g. Sponsorship Dana, Doorprize, Pemateri, Booth
            $table->decimal('estimated_budget', 14, 2)->nullable();

            // Berkas Proposal
            $table->string('proposal_file_path', 500);
            $table->string('proposal_file_name', 255);
            $table->unsignedBigInteger('proposal_file_size')->default(0);

            // Status Pengajuan & Persetujuan
            // 'menunggu_review', 'diajukan_ke_pusat', 'menunggu_acc', 'disetujui', 'ditolak'
            $table->string('status', 50)->default('menunggu_review');
            $table->text('status_notes')->nullable();
            $table->decimal('approved_amount', 14, 2)->nullable();
            $table->text('approved_support_items')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();

            // Laporan Pelaksanaan & Dokumentasi (Terintegrasi Otomatis)
            // 'menunggu_pelaksanaan', 'sedang_berjalan', 'selesai'
            $table->string('execution_status', 50)->default('menunggu_pelaksanaan');
            $table->json('documentation_photos')->nullable();
            $table->text('report_description')->nullable();
            $table->integer('realized_participants')->nullable();
            $table->integer('prospects_acquired')->nullable();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reported_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_event_proposals');
    }
};
