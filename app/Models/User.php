<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'staff_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'staff_id',
        'oid',
        'role_id',
        'full_name',
        'email',
        'phone_number',
        'password_hash',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The three panel roles are nested: super_admin ⊃ admin ⊃ lab_staff. Each
     * check below therefore answers "this tier or above", not "exactly this
     * role", so a Super Admin never has to be listed separately.
     */

    /** Can reach the admin panel at all. */
    public function isStaffMember(): bool
    {
        return in_array($this->role?->name, ['lab_staff', 'admin', 'super_admin'], true);
    }

    /** Admin or above — adds Manage Staff and the System Report. */
    public function isAdmin(): bool
    {
        return in_array($this->role?->name, ['admin', 'super_admin'], true);
    }

    /** Super admin only — adds Settings. */
    public function isSuperAdmin(): bool
    {
        return $this->role?->name === 'super_admin';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }
}
