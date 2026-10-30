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
        Schema::table('marketing_activities', function (Blueprint $table) {
            if (!Schema::hasColumn('marketing_activities', 'google_drive_folder_url')) {
                $table->text('google_drive_folder_url')->nullable()->after('document_name');
            }
            if (!Schema::hasColumn('marketing_activities', 'google_drive_file_url')) {
                $table->text('google_drive_file_url')->nullable()->after('google_drive_folder_url');
            }
        });

        Schema::table('school_event_proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('school_event_proposals', 'google_drive_folder_url')) {
                $table->text('google_drive_folder_url')->nullable()->after('documentation_photos');
            }
            if (!Schema::hasColumn('school_event_proposals', 'google_drive_file_url')) {
                $table->text('google_drive_file_url')->nullable()->after('google_drive_folder_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_activities', function (Blueprint $table) {
            if (Schema::hasColumn('marketing_activities', 'google_drive_file_url')) {
                $table->dropColumn('google_drive_file_url');
            }
            if (Schema::hasColumn('marketing_activities', 'google_drive_folder_url')) {
                $table->dropColumn('google_drive_folder_url');
            }
        });

        Schema::table('school_event_proposals', function (Blueprint $table) {
            if (Schema::hasColumn('school_event_proposals', 'google_drive_file_url')) {
                $table->dropColumn('google_drive_file_url');
            }
            if (Schema::hasColumn('school_event_proposals', 'google_drive_folder_url')) {
                $table->dropColumn('google_drive_folder_url');
            }
        });
    }
};

