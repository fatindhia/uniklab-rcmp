<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Trims the role list down to the two that are actually used: "lab_staff"
     * (the day-to-day admins, previously called "administrator") and
     * "super_admin". The old "staff" role was never assigned to anyone but
     * still showed up in the Manage Lab Staff role dropdown.
     */
    public function up(): void
    {
        DB::table('roles')->where('id', 2)->update([
            'name' => 'lab_staff',
            'label' => 'Lab Staff',
        ]);

        // Nobody holds role 1 today, but move any stragglers onto Lab Staff
        // rather than tripping the users.role_id foreign key on delete.
        DB::table('users')->where('role_id', 1)->update(['role_id' => 2]);

        DB::table('roles')->where('id', 1)->delete();
    }

    public function down(): void
    {
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'staff', 'label' => 'Staff'],
        ]);

        DB::table('roles')->where('id', 2)->update([
            'name' => 'administrator',
            'label' => 'Administrator',
        ]);
    }
};
