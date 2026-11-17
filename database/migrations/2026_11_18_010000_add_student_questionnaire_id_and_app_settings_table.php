<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\StudentQuestionnaire;
use App\Models\Prospect;
use App\Services\ProspectSyncService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat tabel app_settings untuk persistensi pengaturan dan label sidebar di database MySQL
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->string('key', 191)->primary();
                $table->longText('value')->nullable();
                $table->timestamps();
            });

            // Impor pengaturan awal dari file JSON jika ada
            $jsonFile = storage_path('app/app_settings.json');
            if (file_exists($jsonFile)) {
                $data = json_decode(file_get_contents($jsonFile), true);
                if (is_array($data)) {
                    foreach ($data as $k => $v) {
                        DB::table('app_settings')->updateOrInsert(
                            ['key' => $k],
                            [
                                'value' => is_array($v) ? json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : (string) $v,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            }
        }

        // 2. Tambahkan kolom student_questionnaire_id ke tabel prospects
        if (Schema::hasTable('prospects') && !Schema::hasColumn('prospects', 'student_questionnaire_id')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->unsignedBigInteger('student_questionnaire_id')->nullable()->after('id');
                $table->index('student_questionnaire_id');

                if (Schema::hasTable('student_questionnaires')) {
                    $table->foreign('student_questionnaire_id')
                        ->references('id')
                        ->on('student_questionnaires')
                        ->nullOnDelete();
                }
            });
        }

        // 3. Tautkan prospects yang sudah ada dengan kuisioner siswa berdasarkan nomor telepon / email / nama
        if (Schema::hasTable('prospects') && Schema::hasTable('student_questionnaires')) {
            $unlinkedProspects = Prospect::whereNull('student_questionnaire_id')->get();
            foreach ($unlinkedProspects as $p) {
                $matchedStudent = null;
                if (!empty($p->phone)) {
                    $matchedStudent = StudentQuestionnaire::where('student_phone', $p->phone)->first();
                }
                if (!$matchedStudent && !empty($p->email)) {
                    $matchedStudent = StudentQuestionnaire::where('student_email', $p->email)->first();
                }
                if (!$matchedStudent && !empty($p->name)) {
                    $matchedStudent = StudentQuestionnaire::where('student_name', $p->name)
                        ->when($p->school_id, fn($q) => $q->where('school_id', $p->school_id))
                        ->first();
                }

                if ($matchedStudent) {
                    $p->updateQuietly(['student_questionnaire_id' => $matchedStudent->id]);
                }
            }

            // 4. Sinkronkan seluruh siswa berstatus 'daftar_aplikasi' / 'closing' yang belum masuk ke prospects
            try {
                ProspectSyncService::syncAllDaftarAplikasi();
            } catch (\Throwable $e) {
                // Log and continue safely
                \Illuminate\Support\Facades\Log::warning("Migration syncAllDaftarAplikasi notice: " . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('prospects') && Schema::hasColumn('prospects', 'student_questionnaire_id')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->dropForeign(['student_questionnaire_id']);
                $table->dropColumn('student_questionnaire_id');
            });
        }

        Schema::dropIfExists('app_settings');
    }
};
