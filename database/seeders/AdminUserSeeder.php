<?php

namespace Database\Seeders;

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
        $users = [
            [
                'role' => 'Admin',
                'name' => 'Super Administrator',
                'email' => 'walim.wam@bsi.ac.id',
                'old_email' => 'admin@sapasekolah.com',
                'password' => 'adminpassword',
                'phone' => '081234567890',
            ],
            [
                'role' => 'Pimpinan',
                'name' => 'M.Syamsul Azis, M.Kom (Pimpinan Kampus)',
                'email' => 'mohammad.myz@bsi.ac.id',
                'old_email' => 'pimpinan@sapasekolah.com',
                'password' => 'pimpinanpassword',
                'phone' => '081234567891',
            ],
            [
                'role' => 'Koordinator Marketing',
                'name' => 'Ali William (Koordinator Marketing)',
                'email' => 'walimsaja@gmail.com',
                'old_email' => 'koordinator@sapasekolah.com',
                'password' => 'kmpassword',
                'phone' => '081234567892',
            ],
            [
                'role' => 'Marketing',
                'name' => 'Markom1 (Marketing Specialist)',
                'email' => 'karawangmarkom@gmail.com',
                'old_email' => 'marketing@sapasekolah.com',
                'password' => 'markompassword',
                'phone' => '089876543210',
            ],
            [
                'role' => 'Customer Service',
                'name' => 'Ibu Dina (Customer Service)',
                'email' => 'cs@sapasekolah.com',
                'old_email' => 'cs@sapasekolah.com',
                'password' => 'cs1password',
                'phone' => '081234567893',
            ],
            [
                'role' => 'Customer Service',
                'name' => 'Ka Devi',
                'email' => 'deviwinda@gmail.com',
                'old_email' => null,
                'password' => 'cs2password',
                'phone' => '081234567894',
            ],
        ];

        foreach ($users as $data) {
            $role = Role::where('name', $data['role'])->first();

            // Find existing user by new email or old email
            $user = User::where('email', $data['email'])->first();
            if (!$user && !empty($data['old_email'])) {
                $user = User::where('email', $data['old_email'])->first();
            }

            if ($user) {
                $user->update([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role_id' => $role ? $role->id : null,
                    'phone' => $data['phone'],
                    'status' => 'active',
                ]);
            } else {
                User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => Hash::make($data['password']),
                    'role_id' => $role ? $role->id : null,
                    'phone' => $data['phone'],
                    'status' => 'active',
                ]);
            }
        }
    }
}

