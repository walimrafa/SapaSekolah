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
        // 1. Dapatkan ID role 'Viewer' jika ada
        $viewerRole = DB::table('roles')->where('name', 'Viewer')->first();

        if ($viewerRole) {
            // Hapus user demo viewer atau user yang terasosiasi dengan role Viewer jika ada
            DB::table('users')->where('email', 'viewer@sapasekolah.com')->delete();
            DB::table('users')->where('role_id', $viewerRole->id)->delete();

            // Hapus role Viewer dari tabel roles
            DB::table('roles')->where('id', $viewerRole->id)->delete();
        } else {
            // Jika role Viewer tidak ada, pastikan juga user demo viewer@sapasekolah.com dihapus
            DB::table('users')->where('email', 'viewer@sapasekolah.com')->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!DB::table('roles')->where('name', 'Viewer')->exists()) {
            DB::table('roles')->insert([
                'name' => 'Viewer',
                'description' => 'Pengguna yang hanya bisa melihat data',
            ]);
        }
    }
};
