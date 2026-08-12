<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Splits the day-to-day admin panel into two tiers. Until now every
     * non-super-admin was "lab_staff" and could reach everything, including
     * Manage Staff and the System Report. From here:
     *
     *   super_admin — everything, plus Settings
     *   admin       — everything except Settings
     *   lab_staff   — everything except Manage Staff, System Report, Settings
     *
     * Existing lab_staff accounts keep that role (and so lose Manage Staff and
     * System Report); promote whoever needs them from Manage Staff.
     */
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['id' => 4],
            ['name' => 'admin', 'label' => 'Admin']
        );
    }

    public function down(): void
    {
        // Anyone holding the role falls back to lab staff rather than tripping
        // the users.role_id foreign key.
        DB::table('users')->where('role_id', 4)->update(['role_id' => 2]);

        DB::table('roles')->where('id', 4)->delete();
    }
};
