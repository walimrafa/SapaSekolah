<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            if (!Schema::hasColumn('follow_ups', 'agenda_category')) {
                $table->string('agenda_category', 50)
                    ->default('penjadwalan_sosialisasi')
                    ->after('visit_id')
                    ->index();
            }
            if (!Schema::hasColumn('follow_ups', 'invitation_attendance_status')) {
                $table->string('invitation_attendance_status', 50)
                    ->nullable()
                    ->after('agenda_category')
                    ->index();
            }
        });

        // Backfill data dari school_visits yang sudah ada
        try {
            DB::statement("
                UPDATE follow_ups f
                JOIN school_visits v ON f.visit_id = v.id
                SET f.agenda_category = v.agenda_category,
                    f.invitation_attendance_status = v.invitation_attendance_status
                WHERE v.agenda_category IS NOT NULL
            ");
        } catch (\Throwable $e) {
            // Ignore if tables empty or not matched
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            if (Schema::hasColumn('follow_ups', 'invitation_attendance_status')) {
                $table->dropColumn('invitation_attendance_status');
            }
            if (Schema::hasColumn('follow_ups', 'agenda_category')) {
                $table->dropColumn('agenda_category');
            }
        });
    }
};
