<?php

use App\Models\Campus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Memisahkan penyimpanan Google Drive UBSI Kampus Cikampek ke folder khusus:
     * https://drive.google.com/drive/folders/1VpGirvZZ1s2mR3ooMc_s5ZmAquitC0tA?usp=sharing
     */
    public function up(): void
    {
        // 1. Tambah kolom google_drive_folder_id & google_drive_folder_url pada tabel campuses
        Schema::table('campuses', function (Blueprint $table) {
            if (!Schema::hasColumn('campuses', 'google_drive_folder_id')) {
                $table->string('google_drive_folder_id', 100)->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('campuses', 'google_drive_folder_url')) {
                $table->text('google_drive_folder_url')->nullable()->after('google_drive_folder_id');
            }
        });

        // 2. Set Google Drive URL spesifik pada masing-masing kampus
        $cikampekUrl = 'https://drive.google.com/drive/folders/1VpGirvZZ1s2mR3ooMc_s5ZmAquitC0tA?usp=sharing';
        $cikampekId = '1VpGirvZZ1s2mR3ooMc_s5ZmAquitC0tA';

        $karawangUrl = 'https://drive.google.com/drive/folders/1DQkSEomL8cacePKG8Ex72JldQABKndiD?usp=sharing';
        $karawangId = '1DQkSEomL8cacePKG8Ex72JldQABKndiD';

        DB::table('campuses')
            ->where('code', 'CKP')
            ->orWhere('name', 'like', '%Cikampek%')
            ->update([
                'google_drive_folder_id' => $cikampekId,
                'google_drive_folder_url' => $cikampekUrl,
            ]);

        DB::table('campuses')
            ->where('code', 'KRW')
            ->orWhere('name', 'like', '%Karawang%')
            ->update([
                'google_drive_folder_id' => $karawangId,
                'google_drive_folder_url' => $karawangUrl,
            ]);

        $ckpCampus = Campus::where('code', 'CKP')->orWhere('name', 'like', '%Cikampek%')->first();
        $ckpCampusId = $ckpCampus?->id ?? 2;

        // 3. Update data dokumentasi historis Cikampek agar mengarah ke folder Google Drive Cikampek yang baru
        // A. School Visits
        try {
            DB::statement("
                UPDATE school_visits v
                INNER JOIN schools s ON v.school_id = s.id
                SET v.google_drive_folder_url = '{$cikampekUrl}'
                WHERE s.campus_id = {$ckpCampusId} 
                  AND (v.google_drive_folder_url IS NULL OR v.google_drive_folder_url LIKE '%1DQkSEomL8cacePKG8Ex72JldQABKndiD%')
            ");
        } catch (\Throwable) {
            DB::table('school_visits')->where('campus_id', $ckpCampusId)->update(['google_drive_folder_url' => $cikampekUrl]);
        }

        // B. Follow Ups
        try {
            DB::statement("
                UPDATE follow_ups f
                INNER JOIN schools s ON f.school_id = s.id
                SET f.google_drive_folder_url = '{$cikampekUrl}'
                WHERE s.campus_id = {$ckpCampusId}
                  AND (f.google_drive_folder_url IS NULL OR f.google_drive_folder_url LIKE '%1DQkSEomL8cacePKG8Ex72JldQABKndiD%')
            ");
        } catch (\Throwable) {
            DB::table('follow_ups')->where('campus_id', $ckpCampusId)->update(['google_drive_folder_url' => $cikampekUrl]);
        }

        // C. School Event Proposals
        try {
            DB::statement("
                UPDATE school_event_proposals p
                LEFT JOIN schools s ON p.school_id = s.id
                SET p.google_drive_folder_url = '{$cikampekUrl}'
                WHERE (p.campus_id = {$ckpCampusId} OR s.campus_id = {$ckpCampusId})
                  AND (p.google_drive_folder_url IS NULL OR p.google_drive_folder_url LIKE '%1DQkSEomL8cacePKG8Ex72JldQABKndiD%')
            ");
        } catch (\Throwable) {
            DB::table('school_event_proposals')->where('campus_id', $ckpCampusId)->update(['google_drive_folder_url' => $cikampekUrl]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campuses', function (Blueprint $table) {
            if (Schema::hasColumn('campuses', 'google_drive_folder_url')) {
                $table->dropColumn('google_drive_folder_url');
            }
            if (Schema::hasColumn('campuses', 'google_drive_folder_id')) {
                $table->dropColumn('google_drive_folder_id');
            }
        });
    }
};
