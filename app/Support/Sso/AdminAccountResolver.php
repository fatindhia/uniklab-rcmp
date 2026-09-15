<?php

namespace App\Support\Sso;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Matches a Microsoft Graph profile to an existing admin panel account, or
 * refuses.
 *
 * A successful Microsoft sign-in only proves who someone is. Whether they may
 * use the panel is decided by the users table, exactly as for the password
 * login: an active account holding a panel role (User::isStaffMember).
 * Nothing here creates an account or changes a role.
 */
class AdminAccountResolver
{
    /** @throws SsoException */
    public function resolve(array $profile): User
    {
        $oid = strtolower((string) ($profile['id'] ?? ''));
        $context = [
            'oid' => $oid,
            'mail' => $profile['mail'] ?? null,
            'upn' => $profile['userPrincipalName'] ?? null,
        ];

        $addresses = $this->addressesFrom($profile);
        if ($addresses === []) {
            throw SsoException::notAuthorised('Microsoft account has no email address', $context);
        }

        $allowed = array_values(array_filter($addresses, fn ($address) => static::isAllowedEmail($address)));
        if ($allowed === []) {
            throw SsoException::notAuthorised('Microsoft account is not on an allowed UniKL email domain', $context);
        }

        $user = User::with('role')->where('oid', $oid)->first() ?? $this->findByEmail($allowed);

        if (! $user) {
            throw SsoException::notAuthorised('No admin panel account matches this Microsoft account', $context);
        }

        $context['staff_id'] = $user->staff_id;

        // Once linked, an account answers to that one Microsoft identity. A
        // different identity carrying the same address (say, a deleted and
        // re-created Entra user) doesn't inherit it — clear users.oid by hand
        // if it really is the same person.
        if ($user->oid !== null && strtolower($user->oid) !== $oid) {
            throw SsoException::notAuthorised('Account is linked to a different Microsoft identity', $context);
        }

        if (! $user->is_active) {
            throw SsoException::notAuthorised('Account is deactivated', $context);
        }

        if (! $user->isStaffMember()) {
            throw SsoException::notAuthorised('Account holds no admin panel role', $context);
        }

        return $user;
    }

    /**
     * Exact match of the part after the last "@" against
     * sso.allowed_email_domains. A suffix check would wave through
     * s.unikl.edu.my (students) and any other domain ending in unikl.edu.my.
     */
    public static function isAllowedEmail(string $email): bool
    {
        $email = strtolower(trim($email));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        $domain = substr($email, strrpos($email, '@') + 1);

        return in_array($domain, config('sso.allowed_email_domains'), true);
    }

    /** Tries mail before userPrincipalName, so the more specific match wins. */
    private function findByEmail(array $addresses): ?User
    {
        foreach ($addresses as $address) {
            // users.email is typed in by hand in Manage Staff, so compare it
            // the way the address would be read, not byte for byte.
            $user = User::with('role')->where(DB::raw('LOWER(TRIM(email))'), $address)->first();

            if ($user) {
                return $user;
            }
        }

        return null;
    }

    /** mail, then userPrincipalName — lower-cased, blanks and duplicates dropped. */
    private function addressesFrom(array $profile): array
    {
        $addresses = array_map(
            fn ($field) => is_string($profile[$field] ?? null) ? strtolower(trim($profile[$field])) : '',
            ['mail', 'userPrincipalName'],
        );

        return array_values(array_unique(array_filter($addresses)));
    }
}
