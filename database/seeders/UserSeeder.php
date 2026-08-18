<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['staff_id' => '620798'],
            [
                'role_id' => 2,
                // Lab staff only receive booking tickets for the lab types they
                // are assigned to, so seed one rather than leaving this account
                // with no notifications on a fresh install.
                'lab_types' => ['research'],
                'full_name' => 'System Administrator',
                'email' => 'fatindhiya07@gmail.com',
                'phone_number' => '',
                'password_hash' => Hash::make('Rcmp@1234'),
                'is_active' => true,
            ]
        );

        // Staff 121212 holds the Admin tier (role 4) — everything except
        // Settings. Promote in place if the account already exists, without
        // touching the name/email whoever created it entered.
        $admin = User::firstOrCreate(
            ['staff_id' => '121212'],
            [
                'role_id' => 4,
                'full_name' => 'Rudhiah',
                'email' => 'rudhiah@unikl.edu.my',
                'phone_number' => '',
                'password_hash' => Hash::make('Rcmp@1234'),
                'is_active' => true,
            ]
        );

        if ((int) $admin->role_id !== 4) {
            $admin->forceFill(['role_id' => 4])->save();
        }
    }
}
