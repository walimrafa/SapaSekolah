<?php

use App\Models\Campus;
use App\Models\StudyProgram;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom campus_id pada program studi dan memisahkan data prodi UBSI Karawang & UBSI Cikampek.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('study_programs', 'campus_id')) {
            Schema::table('study_programs', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('faculty')->constrained('campuses')->nullOnDelete();
                $table->index('campus_id');
            });
        }

        $campusKrw = Campus::where('code', 'KRW')->first();
        $campusCkp = Campus::where('code', 'CKP')->first();

        $krwId = $campusKrw?->id ?? 1;
        $ckpId = $campusCkp?->id ?? 2;

        // Specific programs for UBSI Kampus Karawang
        $karawangPrograms = [
            [
                'name' => 'Sistem Informasi (Karawang)',
                'code' => 'SI-KRW',
                'faculty' => 'Fakultas Teknik & Informatika',
                'campus_id' => $krwId,
                'study_times' => ['reguler_pagi', 'reguler_malam', 'karyawan'],
                'status' => true,
            ],
            [
                'name' => 'Sistem Informasi Akuntansi (Karawang)',
                'code' => 'SIA-KRW',
                'faculty' => 'Fakultas Teknik & Informatika',
                'campus_id' => $krwId,
                'study_times' => ['reguler_pagi', 'reguler_malam'],
                'status' => true,
            ],
            [
                'name' => 'Teknologi Komputer (Karawang)',
                'code' => 'TK-KRW',
                'faculty' => 'Fakultas Teknik & Informatika',
                'campus_id' => $krwId,
                'study_times' => ['reguler_pagi', 'reguler_malam', 'karyawan'],
                'status' => true,
            ],
            [
                'name' => 'Manajemen (Karawang)',
                'code' => 'MN-KRW',
                'faculty' => 'Fakultas Ekonomi & Bisnis',
                'campus_id' => $krwId,
                'study_times' => ['reguler_pagi', 'reguler_malam', 'karyawan'],
                'status' => true,
            ],
        ];

        foreach ($karawangPrograms as $prog) {
            StudyProgram::updateOrCreate(['code' => $prog['code']], $prog);
        }

        // Specific programs for UBSI Kampus Cikampek
        $cikampekPrograms = [
            [
                'name' => 'Sistem Informasi (Cikampek)',
                'code' => 'SI-CKP',
                'faculty' => 'Fakultas Teknik & Informatika',
                'campus_id' => $ckpId,
                'study_times' => ['reguler_pagi', 'reguler_malam', 'karyawan'],
                'status' => true,
            ],
            [
                'name' => 'Teknologi Komputer (Cikampek)',
                'code' => 'TK-CKP',
                'faculty' => 'Fakultas Teknik & Informatika',
                'campus_id' => $ckpId,
                'study_times' => ['reguler_pagi', 'reguler_malam', 'karyawan'],
                'status' => true,
            ],
            [
                'name' => 'Sistem Informasi Akuntansi (Cikampek)',
                'code' => 'SIA-CKP',
                'faculty' => 'Fakultas Teknik & Informatika',
                'campus_id' => $ckpId,
                'study_times' => ['reguler_pagi', 'reguler_malam'],
                'status' => true,
            ],
            [
                'name' => 'Manajemen (Cikampek)',
                'code' => 'MN-CKP',
                'faculty' => 'Fakultas Ekonomi & Bisnis',
                'campus_id' => $ckpId,
                'study_times' => ['reguler_pagi', 'reguler_malam', 'karyawan'],
                'status' => true,
            ],
        ];

        foreach ($cikampekPrograms as $prog) {
            StudyProgram::updateOrCreate(['code' => $prog['code']], $prog);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('study_programs', 'campus_id')) {
            Schema::table('study_programs', function (Blueprint $table) {
                $table->dropForeign(['campus_id']);
                $table->dropColumn('campus_id');
            });
        }
    }
};

