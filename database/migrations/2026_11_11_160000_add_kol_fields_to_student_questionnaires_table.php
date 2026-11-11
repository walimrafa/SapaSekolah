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
        Schema::table('student_questionnaires', function (Blueprint $table) {
            if (!Schema::hasColumn('student_questionnaires', 'instagram_handle')) {
                $table->string('instagram_handle', 150)->nullable()->after('student_social_media');
            }
            if (!Schema::hasColumn('student_questionnaires', 'instagram_followers')) {
                $table->unsignedInteger('instagram_followers')->default(0)->index()->after('instagram_handle');
            }
            if (!Schema::hasColumn('student_questionnaires', 'tiktok_handle')) {
                $table->string('tiktok_handle', 150)->nullable()->after('instagram_followers');
            }
            if (!Schema::hasColumn('student_questionnaires', 'tiktok_followers')) {
                $table->unsignedInteger('tiktok_followers')->default(0)->after('tiktok_handle');
            }
            if (!Schema::hasColumn('student_questionnaires', 'is_kol')) {
                $table->boolean('is_kol')->default(false)->index()->after('tiktok_followers');
            }
            if (!Schema::hasColumn('student_questionnaires', 'kol_status')) {
                $table->string('kol_status', 50)->default('kandidat')->after('is_kol');
            }
            if (!Schema::hasColumn('student_questionnaires', 'kol_notes')) {
                $table->text('kol_notes')->nullable()->after('kol_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_questionnaires', function (Blueprint $table) {
            $table->dropColumn([
                'instagram_handle',
                'instagram_followers',
                'tiktok_handle',
                'tiktok_followers',
                'is_kol',
                'kol_status',
                'kol_notes',
            ]);
        });
    }
};

