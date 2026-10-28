<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom study_times pada tabel study_programs
        if (Schema::hasTable('study_programs') && !Schema::hasColumn('study_programs', 'study_times')) {
            Schema::table('study_programs', function (Blueprint $table) {
                $table->json('study_times')->nullable()->after('faculty')
                    ->comment('Pilihan waktu kuliah yang dibuka: reguler_pagi, reguler_malam, karyawan');
            });

            // Set default waktu kuliah yang dibuka untuk prodi yang sudah ada
            // Mayoritas kampus membuka Reguler (Pagi-Siang) dan Karyawan (Jumat-Sabtu)
            $defaultTimes = json_encode(['reguler_pagi', 'reguler_malam', 'karyawan']);
            DB::table('study_programs')->whereNull('study_times')->update([
                'study_times' => $defaultTimes
            ]);
        }

        // 2. Tambah kolom study_time pada tabel prospects
        if (Schema::hasTable('prospects') && !Schema::hasColumn('prospects', 'study_time')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->string('study_time', 50)->nullable()->after('interest_program')
                    ->comment('Waktu kuliah yang dipilih mahasiswa: reguler_pagi, reguler_malam, karyawan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('study_programs') && Schema::hasColumn('study_programs', 'study_times')) {
            Schema::table('study_programs', function (Blueprint $table) {
                $table->dropColumn('study_times');
            });
        }

        if (Schema::hasTable('prospects') && Schema::hasColumn('prospects', 'study_time')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->dropColumn('study_time');
            });
        }
    }
};
