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
        // 1. Modify status column to varchar(50) so it supports custom pipeline statuses
        DB::statement("ALTER TABLE prospects MODIFY status VARCHAR(50) NOT NULL DEFAULT 'daftar_akun'");

        // 2. Add validation audit columns if not exist
        Schema::table('prospects', function (Blueprint $table) {
            if (!Schema::hasColumn('prospects', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('prospects', 'validated_by')) {
                $table->foreignId('validated_by')->nullable()->after('validated_at')->constrained('users')->nullOnDelete();
            }
        });

        // 3. Migrate existing status values
        DB::table('prospects')->whereIn('status', ['lead', 'prospek'])->update(['status' => 'daftar_akun']);
        DB::table('prospects')->where('status', 'daftar')->update(['status' => 'bayar_registrasi']);
        DB::table('prospects')->where('status', 'registrasi')->update(['status' => 'ujian_saringan']);
        DB::table('prospects')->where('status', 'bayar')->update(['status' => 'bayar_daftar_ulang']);
        DB::table('prospects')->where('status', 'mahasiswa')->whereNull('validated_at')->update(['validated_at' => DB::raw('created_at')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (Schema::hasColumn('prospects', 'validated_by')) {
                $table->dropForeign(['validated_by']);
                $table->dropColumn('validated_by');
            }
            if (Schema::hasColumn('prospects', 'validated_at')) {
                $table->dropColumn('validated_at');
            }
        });

        DB::statement("ALTER TABLE prospects MODIFY status ENUM('lead', 'prospek', 'daftar', 'registrasi', 'bayar', 'mahasiswa', 'tidak_lanjut') NOT NULL DEFAULT 'lead'");
    }
};
