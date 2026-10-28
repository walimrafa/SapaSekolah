<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update existing account login addresses to the SAPA Sekolah domain.
     */
    public function up(): void
    {
        DB::transaction(function () {
            $users = DB::table('users')
                ->where('email', 'like', '%@simapsek.com')
                ->get(['id', 'email']);

            foreach ($users as $user) {
                $newEmail = substr($user->email, 0, -strlen('@simapsek.com')) . '@sapasekolah.com';

                if (DB::table('users')->where('email', $newEmail)->where('id', '!=', $user->id)->exists()) {
                    throw new \RuntimeException("Cannot rename account {$user->email}: {$newEmail} is already in use.");
                }

                DB::table('users')->where('id', $user->id)->update(['email' => $newEmail]);
            }
        });
    }

    /**
     * Restore the previous login email domain.
     */
    public function down(): void
    {
        DB::transaction(function () {
            $users = DB::table('users')
                ->where('email', 'like', '%@sapasekolah.com')
                ->get(['id', 'email']);

            foreach ($users as $user) {
                $oldEmail = substr($user->email, 0, -strlen('@sapasekolah.com')) . '@simapsek.com';

                if (DB::table('users')->where('email', $oldEmail)->where('id', '!=', $user->id)->exists()) {
                    throw new \RuntimeException("Cannot restore account {$user->email}: {$oldEmail} is already in use.");
                }

                DB::table('users')->where('id', $user->id)->update(['email' => $oldEmail]);
            }
        });
    }
};
