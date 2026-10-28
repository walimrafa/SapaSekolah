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
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_notification_logs', 'calendar_url')) {
                $table->text('calendar_url')->nullable()->after('message');
            }
            if (!Schema::hasColumn('whatsapp_notification_logs', 'email_recipients')) {
                $table->string('email_recipients', 255)->nullable()->after('calendar_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('whatsapp_notification_logs', function (Blueprint $table) {
            $table->dropColumn(['calendar_url', 'email_recipients']);
        });
    }
};
