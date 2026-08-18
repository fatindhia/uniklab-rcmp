<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'staff_id';

    public $incrementing = false;

    protected $keyType = 'string';

    /** Lab areas an account can be assigned to, in display order. */
    public const LAB_TYPE_LABELS = [
        'research' => 'Research',
        'csl' => 'CSL',
        'pharma' => 'Pharma',
    ];

    protected $fillable = [
        'staff_id',
        'oid',
        'role_id',
        'lab_types',
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
            'lab_types' => 'array',
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

    /**
     * Who receives the new-booking ticket for a booking of $labType.
     *
     * Admins oversee every lab, so they get every ticket regardless of their
     * own lab_types. Lab staff get the areas they're assigned to, which can be
     * more than one — an unassigned account (lab_types NULL) matches nothing,
     * so a forgotten assignment is quiet rather than noisy. Super admins are
     * deliberately absent from both branches: it's a developer-only tier with
     * no day-to-day lab to watch.
     */
    public function scopeBookingTicketRecipients($query, string $labType)
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($labType) {
                $q->whereHas('role', fn ($r) => $r->where('name', 'admin'))
                    ->orWhere(fn ($s) => $s
                        ->whereHas('role', fn ($r) => $r->where('name', 'lab_staff'))
                        ->whereJsonContains('lab_types', $labType));
            });
    }

    /** Human-readable list of the areas this account is assigned to. */
    public function labTypesLabel(): string
    {
        return static::labelForLabTypes($this->lab_types);
    }

    /**
     * Formats a lab type list for display. Accepts the raw JSON string too,
     * since activity-log diffs hand over whatever was stored on the row.
     */
    public static function labelForLabTypes($labTypes): string
    {
        if (is_string($labTypes)) {
            $labTypes = json_decode($labTypes, true);
        }

        return collect($labTypes ?: [])
            ->map(fn ($type) => static::LAB_TYPE_LABELS[$type] ?? $type)
            ->implode(', ');
    }

    /**
     * Keeps a submitted list to known values in a fixed order, dropping blanks
     * and duplicates. Stable ordering matters: Eloquent diffs a JSON column by
     * its encoded string, so ['csl','pharma'] and ['pharma','csl'] would
     * otherwise log as a change when nothing actually changed.
     */
    public static function normalizeLabTypes(?array $labTypes): ?array
    {
        $values = array_values(array_intersect(array_keys(static::LAB_TYPE_LABELS), $labTypes ?? []));

        return $values ?: null;
    }

    /**
     * The addresses to actually mail for a booking of $labType.
     *
     * users.email is free text typed into Manage Lab Staff, and has in practice
     * held things like two addresses in one field. Symfony rejects the first
     * malformed one with an RfcComplianceException, so a single bad row would
     * otherwise abort the recipient loop and silently leave everyone after it
     * unnotified. Drop them here instead, and log loudly enough that the bad
     * row can be found and fixed.
     */
    public static function bookingTicketRecipientEmails(string $labType): Collection
    {
        [$valid, $invalid] = static::bookingTicketRecipients($labType)
            ->pluck('email', 'staff_id')
            ->partition(fn ($email) => filter_var((string) $email, FILTER_VALIDATE_EMAIL) !== false);

        foreach ($invalid as $staffId => $email) {
            Log::warning('Skipped booking notification: staff '.$staffId.' has an invalid email address "'.$email.'".');
        }

        return $valid->values();
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
