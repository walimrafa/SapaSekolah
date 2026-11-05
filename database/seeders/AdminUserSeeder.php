<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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

        $users = [
            [
                'role_id' => $roleAdmin?->id ?? 1,
                'name' => 'Super Administrator',
                'email' => 'walim.wam@bsi.ac.id',
                'old_email' => 'admin@sapasekolah.com',
                'campus_id' => null,
                'password' => 'adminpassword',
                'phone' => '081234567890',
            ],
            [
                'role_id' => $rolePimpinan?->id ?? 2,
                'name' => 'M.Syamsul Azis, M.Kom',
                'email' => 'mohammad.myz@bsi.ac.id',
                'old_email' => 'pimpinan@sapasekolah.com',
                'campus_id' => $krwId,
                'password' => 'pimpinanpassword',
                'phone' => '081234567891',
            ],
            [
                'role_id' => $rolePimpinan?->id ?? 2,
                'name' => 'Nurul Ichsan, M.Kom',
                'email' => 'nurulichsan@bsi.ac.id',
                'old_email' => null,
                'campus_id' => $ckpId,
                'password' => 'pimpinanpassword',
                'phone' => '085695662714',
            ],
            [
                'role_id' => $roleKoordinator?->id ?? 3,
                'name' => 'Ali William',
                'email' => 'walimsaja@gmail.com',
                'old_email' => 'koordinator@sapasekolah.com',
                'campus_id' => null,
                'password' => 'kmpassword',
                'phone' => '081234567892',
            ],
            [
                'role_id' => $roleMarketing?->id ?? 4,
                'name' => 'Markom1 (Marketing Specialist)',
                'email' => 'karawangmarkom@gmail.com',
                'old_email' => 'marketing@sapasekolah.com',
                'campus_id' => $krwId,
                'password' => 'markompassword',
                'phone' => '089876543210',
            ],
            [
                'role_id' => $roleMarketing?->id ?? 4,
                'name' => 'Markom2 (Marketing Cikampek)',
                'email' => 'cikampekmarkom@gmail.com',
                'old_email' => null,
                'campus_id' => $ckpId,
                'password' => 'markompassword',
                'phone' => '089876543211',
            ],
            [
                'role_id' => $roleCS?->id ?? 6,
                'name' => 'Ibu Dina (Customer Service)',
                'email' => 'cs@sapasekolah.com',
                'old_email' => 'cs@sapasekolah.com',
                'campus_id' => $krwId,
                'password' => 'cs1password',
                'phone' => '081234567893',
            ],
            [
                'role_id' => $roleCS?->id ?? 6,
                'name' => 'Ka Devi',
                'email' => 'deviwinda@gmail.com',
                'old_email' => 'cs2@sapasekolah.com',
                'campus_id' => $ckpId,
                'password' => 'cs2password',
                'phone' => '081234567894',
            ],
        ];

        foreach ($users as $data) {
            // Find existing user by new email or old email
            $user = User::where('email', $data['email'])->first();
            if (!$user && !empty($data['old_email'])) {
                $user = User::where('email', $data['old_email'])->first();
            }

            if ($user) {
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $user->password ?: Hash::make($data['password']),
                    'role_id' => $data['role_id'],
                    'campus_id' => $data['campus_id'],
                    'phone' => $data['phone'],
                    'status' => 'active',
                ]);
            } else {
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role_id' => $data['role_id'],
                    'campus_id' => $data['campus_id'],
                    'phone' => $data['phone'],
                    'status' => 'active',
                ]);
            }
        }
    }
}
