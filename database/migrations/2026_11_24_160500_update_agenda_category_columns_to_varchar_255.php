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
        if (Schema::hasColumn('school_visits', 'agenda_category')) {
            Schema::table('school_visits', function (Blueprint $table) {
                $table->string('agenda_category', 255)->default('penjadwalan_sosialisasi')->change();
            });
        }

        if (Schema::hasColumn('follow_ups', 'agenda_category')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->string('agenda_category', 255)->default('penjadwalan_sosialisasi')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('school_visits', 'agenda_category')) {
            Schema::table('school_visits', function (Blueprint $table) {
                $table->string('agenda_category', 50)->default('penjadwalan_sosialisasi')->change();
            });
        }

        if (Schema::hasColumn('follow_ups', 'agenda_category')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->string('agenda_category', 50)->default('penjadwalan_sosialisasi')->change();
            });
        }
    }
};
