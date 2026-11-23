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
        Schema::table('school_visits', function (Blueprint $table) {
            if (!Schema::hasColumn('school_visits', 'agenda_category')) {
                $table->string('agenda_category', 50)->default('penjadwalan_sosialisasi')->after('visit_plan_id');
            }
            if (!Schema::hasColumn('school_visits', 'invitation_attendance_status')) {
                $table->string('invitation_attendance_status', 50)->nullable()->after('agenda_category');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_visits', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('school_visits', 'agenda_category')) {
                $columnsToDrop[] = 'agenda_category';
            }
            if (Schema::hasColumn('school_visits', 'invitation_attendance_status')) {
                $columnsToDrop[] = 'invitation_attendance_status';
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
