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
        if (Schema::hasTable('school_visits') && !Schema::hasColumn('school_visits', 'event_name')) {
            Schema::table('school_visits', function (Blueprint $table) {
                $table->string('event_name', 255)->nullable()->after('invitation_attendance_status');
            });
        }

        if (Schema::hasTable('follow_ups') && !Schema::hasColumn('follow_ups', 'event_name')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->string('event_name', 255)->nullable()->after('invitation_attendance_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('school_visits') && Schema::hasColumn('school_visits', 'event_name')) {
            Schema::table('school_visits', function (Blueprint $table) {
                $table->dropColumn('event_name');
            });
        }

        if (Schema::hasTable('follow_ups') && Schema::hasColumn('follow_ups', 'event_name')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->dropColumn('event_name');
            });
        }
    }
};
