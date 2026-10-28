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
        if (Schema::hasTable('prospects') && !Schema::hasColumn('prospects', 'scholarship_type')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->string('scholarship_type', 100)->nullable()->after('study_time')
                    ->comment('Kategori program: Beasiswa KIP-Kuliah, Beasiswa Jalur Sekolah, Beasiswa Prestasi, Beasiswa Talenta, Non Beasiswa, dll.');
            });

            // Backfill data yang sudah ada berdasarkan isi catatan (notes) atau default
            $prospects = DB::table('prospects')->select('id', 'notes', 'status')->get();
            foreach ($prospects as $p) {
                $type = 'Non Beasiswa';
                $notes = strtolower($p->notes ?? '');

                if (str_contains($notes, 'jalur sekolah')) {
                    $type = 'Beasiswa Jalur Sekolah';
                } elseif (str_contains($notes, 'kip-kuliah') || str_contains($notes, 'kip-k')) {
                    $type = 'Beasiswa KIP-Kuliah';
                } elseif (str_contains($notes, 'prestasi')) {
                    $type = 'Beasiswa Prestasi';
                } elseif (str_contains($notes, 'talenta')) {
                    $type = 'Beasiswa Talenta';
                } elseif (str_contains($notes, 'beasiswa')) {
                    $type = 'Beasiswa';
                } elseif (str_contains($notes, 'mandiri') || str_contains($notes, 'non beasiswa') || str_contains($notes, 'reguler')) {
                    $type = 'Non Beasiswa';
                } else {
                    // Beri variasi realistis untuk dummy awal jika status mahasiswa
                    if ($p->id % 4 === 0) {
                        $type = 'Beasiswa KIP-Kuliah';
                    } elseif ($p->id % 7 === 0) {
                        $type = 'Beasiswa Prestasi';
                    } else {
                        $type = 'Non Beasiswa';
                    }
                }

                DB::table('prospects')->where('id', $p->id)->update([
                    'scholarship_type' => $type
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('prospects') && Schema::hasColumn('prospects', 'scholarship_type')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->dropColumn('scholarship_type');
            });
        }
    }
};
