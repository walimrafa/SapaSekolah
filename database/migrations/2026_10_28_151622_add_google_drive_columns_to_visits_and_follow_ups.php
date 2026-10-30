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
            if (!Schema::hasColumn('school_visits', 'google_drive_folder_url')) {
                $table->text('google_drive_folder_url')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('school_visits', 'google_drive_file_url')) {
                $table->text('google_drive_file_url')->nullable()->after('google_drive_folder_url');
            }
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            if (!Schema::hasColumn('follow_ups', 'documentation_file')) {
                $table->string('documentation_file', 255)->nullable()->after('socialization_completion_notes');
            }
            if (!Schema::hasColumn('follow_ups', 'google_drive_folder_url')) {
                $table->text('google_drive_folder_url')->nullable()->after('documentation_file');
            }
            if (!Schema::hasColumn('follow_ups', 'google_drive_file_url')) {
                $table->text('google_drive_file_url')->nullable()->after('google_drive_folder_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_visits', function (Blueprint $table) {
            $table->dropColumn(['google_drive_folder_url', 'google_drive_file_url']);
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropColumn(['documentation_file', 'google_drive_folder_url', 'google_drive_file_url']);
        });
    }
};
