<?php

use App\Models\Campus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengembalikan data user management ke data real personil resmi UBSI Karawang & Cikampek.
     */
    public function up(): void
    {
        $roleAdmin = Role::where('name', 'Admin')->first();
        $rolePimpinan = Role::where('name', 'Pimpinan')->first();
        $roleKoordinator = Role::where('name', 'Koordinator Marketing')->first();
        $roleMarketing = Role::where('name', 'Marketing')->first();
        $roleCS = Role::where('name', 'Customer Service')->first();

        $campusKrw = Campus::where('code', 'KRW')->first();
        $campusCkp = Campus::where('code', 'CKP')->first();

        $krwId = $campusKrw ? $campusKrw->id : 1;
        $ckpId = $campusCkp ? $campusCkp->id : 2;

        $realUsers = [
            // 1. Super Administrator (Akses Penuh Seluruh Kampus)
            [
                'match_email' => 'admin@sapasekolah.com',
                'name' => 'Super Administrator',
                'email' => 'walim.wam@bsi.ac.id',
                'role_id' => $roleAdmin?->id ?? 1,
                'campus_id' => null,
                'phone' => '081234567890',
                'password' => 'adminpassword',
                'status' => 'active',
            ],
            // 2. Pimpinan UBSI Kampus Karawang
            [
                'match_email' => 'pimpinan@sapasekolah.com',
                'name' => 'M.Syamsul Azis, M.Kom',
                'email' => 'mohammad.myz@bsi.ac.id',
                'role_id' => $rolePimpinan?->id ?? 2,
                'campus_id' => $krwId,
                'phone' => '081234567891',
                'password' => 'pimpinanpassword',
                'status' => 'active',
            ],
            // 3. Pimpinan UBSI Kampus Cikampek
            [
                'match_email' => 'nurulichsan@bsi.ac.id',
                'name' => 'Nurul Ichsan, M.Kom',
                'email' => 'nurulichsan@bsi.ac.id',
                'role_id' => $rolePimpinan?->id ?? 2,
                'campus_id' => $ckpId,
                'phone' => '085695662714',
                'password' => 'pimpinanpassword',
                'status' => 'active',
            ],
            // 4. Koordinator Marketing (Supervisi Lintas Kampus)
            [
                'match_email' => 'koordinator@sapasekolah.com',
                'name' => 'Ali William',
                'email' => 'walimsaja@gmail.com',
                'role_id' => $roleKoordinator?->id ?? 3,
                'campus_id' => null,
                'phone' => '081234567892',
                'password' => 'kmpassword',
                'status' => 'active',
            ],
            // 5. Marketing Specialist UBSI Kampus Karawang
            [
                'match_email' => 'marketing@sapasekolah.com',
                'name' => 'Markom1 (Marketing Specialist)',
                'email' => 'karawangmarkom@gmail.com',
                'role_id' => $roleMarketing?->id ?? 4,
                'campus_id' => $krwId,
                'phone' => '089876543210',
                'password' => 'markompassword',
                'status' => 'active',
            ],
            // 6. Marketing Specialist UBSI Kampus Cikampek
            [
                'match_email' => 'cikampekmarkom@gmail.com',
                'name' => 'Markom2 (Marketing Cikampek)',
                'email' => 'cikampekmarkom@gmail.com',
                'role_id' => $roleMarketing?->id ?? 4,
                'campus_id' => $ckpId,
                'phone' => '089876543211',
                'password' => 'markompassword',
                'status' => 'active',
            ],
            // 7. Customer Service UBSI Kampus Karawang
            [
                'match_email' => 'cs@sapasekolah.com',
                'name' => 'Ibu Dina (Customer Service)',
                'email' => 'cs@sapasekolah.com',
                'role_id' => $roleCS?->id ?? 6,
                'campus_id' => $krwId,
                'phone' => '081234567893',
                'password' => 'cs1password',
                'status' => 'active',
            ],
            // 8. Customer Service UBSI Kampus Cikampek
            [
                'match_email' => 'cs2@sapasekolah.com',
                'name' => 'Ka Devi',
                'email' => 'deviwinda@gmail.com',
                'role_id' => $roleCS?->id ?? 6,
                'campus_id' => $ckpId,
                'phone' => '081234567894',
                'password' => 'cs2password',
                'status' => 'active',
            ],
        ];

        foreach ($realUsers as $ru) {
            // Cek apakah user ada berdasarkan email baru atau email lama
            $user = User::where('email', $ru['email'])->first();
            if (!$user && !empty($ru['match_email'])) {
                $user = User::where('email', $ru['match_email'])->first();
            }

            if ($user) {
                $user->update([
                    'name' => $ru['name'],
                    'email' => $ru['email'],
                    'role_id' => $ru['role_id'],
                    'campus_id' => $ru['campus_id'],
                    'phone' => $ru['phone'],
                    'status' => $ru['status'],
                    // Pertahankan password jika sudah ada, atau buat baru jika kosong
                    'password' => $user->password ?: Hash::make($ru['password']),
                ]);
            } else {
                User::create([
                    'name' => $ru['name'],
                    'email' => $ru['email'],
                    'role_id' => $ru['role_id'],
                    'campus_id' => $ru['campus_id'],
                    'phone' => $ru['phone'],
                    'status' => $ru['status'],
                    'password' => Hash::make($ru['password']),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }
};
