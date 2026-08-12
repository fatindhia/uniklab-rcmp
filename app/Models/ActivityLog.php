<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One row per configuration change in Manage Labs or Manage Staff — see the
 * activity_logs migration. Written through ActivityLog::record().
 */
class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    const UPDATED_AT = null;

    protected $fillable = [
        'area',
        'action',
        'performed_by',
        'subject_id',
        'subject_label',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by', 'staff_id');
    }

    /**
     * Fields that are noise in a "who changed what" trail, or that must never
     * be written to it.
     */
    private const IGNORED_FIELDS = ['password_hash', 'remember_token', 'updated_at', 'created_at', 'last_login_at'];

    /**
     * Records a change. $changes is the model's own dirty set — pass
     * $model->getChanges() *after* saving, with $before captured from
     * getOriginal() beforehand.
     */
    public static function record(string $area, string $action, ?string $subjectId, string $subjectLabel, array $changes = []): void
    {
        static::create([
            'area' => $area,
            'action' => $action,
            'performed_by' => auth()->id(),
            'subject_id' => $subjectId,
            'subject_label' => $subjectLabel,
            'changes' => $changes ?: null,
        ]);
    }

    /**
     * Builds a readable field => [from, to] diff for an updated model, with
     * sensitive and bookkeeping columns left out. Returns [] when nothing
     * user-visible changed, so a no-op save doesn't create a noisy entry.
     */
    public static function diff(array $before, array $after): array
    {
        $diff = [];

        foreach ($after as $field => $newValue) {
            if (in_array($field, self::IGNORED_FIELDS, true)) {
                continue;
            }

            $oldValue = $before[$field] ?? null;

            if ($oldValue == $newValue) {
                continue;
            }

            $diff[$field] = [self::readable($oldValue), self::readable($newValue)];
        }

        return $diff;
    }

    private static function readable($value): string
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value === null || $value === '') {
            return '—';
        }

        return (string) $value;
    }
}
