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
        Schema::create('whatsapp_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notification_type', 50)->index(); // kunjungan_h1, sosialisasi_h5, proposal_masuk, event_h7, test
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('recipient_phone', 30);
            $table->string('recipient_name', 150)->nullable();
            $table->text('message');
            $table->string('status', 20)->default('terkirim'); // terkirim, gagal, simulasi
            $table->text('response_payload')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['notification_type', 'reference_type', 'reference_id'], 'wa_notif_ref_idx');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_notification_logs');
    }
};
