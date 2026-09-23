<?php

namespace App\Support\Sso;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Copies identity details from a Microsoft Graph profile onto the account it
 * signed in to — each only while the account has none of its own.
 *
 * Manage Staff adds people by email alone, so the first Microsoft sign-in is
 * where an account gets its Entra object ID, full name and real staff ID.
 * After that nothing is overwritten: an admin's later edits stick.
 */
class ProfileLinker
{
    public function link(User $user, array $profile): User
    {
        $user->forceFill([
            'oid' => $user->oid ?? strtolower($profile['id']),
            'last_login_at' => now(),
        ]);

        $name = trim((string) ($profile['displayName'] ?? ''));
        if ($user->full_name === '' && $name !== '') {
            $user->full_name = mb_substr($name, 0, 150);
        }

        if (! $user->hasPendingStaffId()) {
            $user->save();

            return $user;
        }

        $staffId = trim((string) data_get($profile, config('sso.staff_id_attribute'), ''));
        $pendingId = $user->staff_id;

        if ($staffId === '' || mb_strlen($staffId) > 20 || User::whereKey($staffId)->exists()) {
            // Not worth refusing the sign-in over: the account works under its
            // placeholder ID, and the next sign-in tries again.
            Log::warning('Microsoft sign-in could not set the real staff ID', [
                'staff_id' => $pendingId,
                'directory_staff_id' => $staffId,
                'reason' => $staffId === '' ? 'missing' : (mb_strlen($staffId) > 20 ? 'too long' : 'already in use'),
            ]);
            $user->save();

            return $user;
        }

        DB::transaction(function () use ($user, $staffId, $pendingId) {
            // Saving with a changed key updates the row found by the old one;
            // the foreign keys pointing at users.staff_id cascade the change.
            $user->staff_id = $staffId;
            $user->save();

            // subject_id is a plain column, not a foreign key.
            ActivityLog::where('area', 'staff')->where('subject_id', $pendingId)->update(['subject_id' => $staffId]);
        });

        Log::info('Account took its staff ID from Microsoft', ['from' => $pendingId, 'staff_id' => $staffId]);

        return $user;
    }
}
