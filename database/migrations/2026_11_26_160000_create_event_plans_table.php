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
        // 1. Buat tabel master event_plans (Master Rencana Event Kampus)
        if (!Schema::hasTable('event_plans')) {
            Schema::create('event_plans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
                $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->nullOnDelete();
                $table->string('title', 255)->comment('Tema / Nama Acara Event Kampus');
                $table->date('event_date')->comment('Tanggal Pelaksanaan Event');
                $table->string('event_time', 100)->comment('Waktu / Jam Pelaksanaan Event, misal 09:00 - 12:00 WIB');
                $table->string('location', 255)->comment('Lokasi / Tempat Pelaksanaan Event');
                $table->string('target_audience', 255)->nullable()->comment('Target Peserta: Siswa Kelas 12, Guru BK, dll');
                $table->integer('quota')->nullable()->comment('Estimasi / Kuota Peserta');
                $table->text('description')->nullable()->comment('Keterangan / Rincian Acara');
                $table->string('status', 50)->default('aktif')->comment('aktif, selesai, dibatalkan');
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->softDeletes();

                $table->index('event_date');
                $table->index('campus_id');
                $table->index('status');
                $table->index('is_active');
            });
        }

        // 2. Tambahkan event_plan_id ke tabel school_visits
        if (Schema::hasTable('school_visits') && !Schema::hasColumn('school_visits', 'event_plan_id')) {
            Schema::table('school_visits', function (Blueprint $table) {
                $table->foreignId('event_plan_id')->nullable()->after('invitation_attendance_status')
                    ->constrained('event_plans')->nullOnDelete();
            });
        }

        // 3. Tambahkan event_plan_id ke tabel follow_ups
        if (Schema::hasTable('follow_ups') && !Schema::hasColumn('follow_ups', 'event_plan_id')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->foreignId('event_plan_id')->nullable()->after('invitation_attendance_status')
                    ->constrained('event_plans')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('follow_ups') && Schema::hasColumn('follow_ups', 'event_plan_id')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->dropForeign(['event_plan_id']);
                $table->dropColumn('event_plan_id');
            });
        }

        if (Schema::hasTable('school_visits') && Schema::hasColumn('school_visits', 'event_plan_id')) {
            Schema::table('school_visits', function (Blueprint $table) {
                $table->dropForeign(['event_plan_id']);
                $table->dropColumn('event_plan_id');
            });
        }

        Schema::dropIfExists('event_plans');
    }
};
