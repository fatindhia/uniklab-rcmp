<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 2, 'name' => 'lab_staff', 'label' => 'Lab Staff'],
            ['id' => 3, 'name' => 'super_admin', 'label' => 'Super Admin'],
            ['id' => 4, 'name' => 'admin', 'label' => 'Admin'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(['id' => $role['id']], $role);
        }
    }
}
