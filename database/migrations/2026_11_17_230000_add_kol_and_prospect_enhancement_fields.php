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
        // 1. Kol Enhancement Fields on student_questionnaires
        Schema::table('student_questionnaires', function (Blueprint $table) {
            if (!Schema::hasColumn('student_questionnaires', 'instagram_followers_verified')) {
                $table->unsignedInteger('instagram_followers_verified')->nullable()->after('instagram_followers');
            }
            if (!Schema::hasColumn('student_questionnaires', 'kol_verification_status')) {
                $table->string('kol_verification_status', 30)->default('unverified')->after('instagram_followers_verified');
            }
            if (!Schema::hasColumn('student_questionnaires', 'kol_verification_notes')) {
                $table->text('kol_verification_notes')->nullable()->after('kol_verification_status');
            }
            if (!Schema::hasColumn('student_questionnaires', 'estimated_rate_card')) {
                $table->json('estimated_rate_card')->nullable()->after('kol_verification_notes');
            }
            if (!Schema::hasColumn('student_questionnaires', 'work_links')) {
                $table->json('work_links')->nullable()->after('kol_notes');
            }
        });

        // 2. Registration Proof on prospects table
        Schema::table('prospects', function (Blueprint $table) {
            if (!Schema::hasColumn('prospects', 'registration_proof_path')) {
                $table->string('registration_proof_path', 255)->nullable()->after('notes');
            }
            if (!Schema::hasColumn('prospects', 'registration_proof_drive_url')) {
                $table->text('registration_proof_drive_url')->nullable()->after('registration_proof_path');
            }
            if (!Schema::hasColumn('prospects', 'registration_proof_drive_id')) {
                $table->string('registration_proof_drive_id', 150)->nullable()->after('registration_proof_drive_url');
            }
            if (!Schema::hasColumn('prospects', 'registration_proof_uploaded_at')) {
                $table->timestamp('registration_proof_uploaded_at')->nullable()->after('registration_proof_drive_id');
            }
            if (!Schema::hasColumn('prospects', 'registration_proof_uploaded_by')) {
                $table->unsignedBigInteger('registration_proof_uploaded_by')->nullable()->after('registration_proof_uploaded_at');
            }
        });

        // 3. User Login by NISN / Username
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username', 50)->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'nisn')) {
                $table->string('nisn', 30)->nullable()->after('username');
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
                'instagram_followers_verified',
                'kol_verification_status',
                'kol_verification_notes',
                'estimated_rate_card',
                'work_links',
            ]);
        });

        Schema::table('prospects', function (Blueprint $table) {
            $table->dropColumn([
                'registration_proof_path',
                'registration_proof_drive_url',
                'registration_proof_drive_id',
                'registration_proof_uploaded_at',
                'registration_proof_uploaded_by',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'nisn',
            ]);
        });
    }
};
