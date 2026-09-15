<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait CreatesStaffAccounts
{
    protected const PASSWORD = 'correct-horse-battery';

    private int $nextStaffId = 900001;

    /** A role name that isn't seeded (e.g. "viewer") is created with no panel access. */
    protected function staff(string $role, string $email, array $attributes = []): User
    {
        $roleId = Role::where('name', $role)->value('id')
            ?? Role::create(['name' => $role, 'label' => ucfirst($role)])->id;

        return User::create($attributes + [
            'staff_id' => (string) $this->nextStaffId++,
            'role_id' => $roleId,
            'full_name' => 'Test Staff',
            'email' => $email,
            'phone_number' => '',
            'password_hash' => Hash::make(self::PASSWORD),
            'is_active' => true,
        ]);
    }

    public static function panelRoles(): array
    {
        return [
            'lab staff' => ['lab_staff'],
            'admin' => ['admin'],
            'super admin' => ['super_admin'],
        ];
    }
}
