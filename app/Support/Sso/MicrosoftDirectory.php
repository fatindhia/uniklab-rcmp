<?php

namespace App\Support\Sso;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Looks a UniKL staff member up in Microsoft Graph by email, so Manage Staff
 * can create an account from the directory instead of retyping the staff ID,
 * name and phone number.
 *
 * This runs as the application itself (client credentials), not as the admin
 * doing the lookup, so the Entra app registration needs the User.Read.All
 * *application* permission with admin consent — the delegated User.Read used
 * for sign-in only covers the signed-in account's own profile.
 */
class MicrosoftDirectory
{
    public const NOT_FOUND = 'No UniKL Microsoft account uses that email address.';

    public const DISABLED = 'That Microsoft account is disabled.';

    public const UNAVAILABLE = 'The UniKL directory could not be reached. Please try again, or contact the system administrator if the problem continues.';

    private const HTTP_TIMEOUT_SECONDS = 15;

    private const TOKEN_CACHE_KEY = 'sso.directory_token';

    public function __construct(private readonly MicrosoftEntraClient $entra) {}

    /**
     * staff_id is '' when the directory holds none for this person.
     *
     * @return array{oid: string, staff_id: string, full_name: string, email: string, phone_number: string}
     *
     * @throws SsoException
     */
    public function findStaff(string $email): array
    {
        $email = strtolower(trim($email));
        $staffIdAttribute = config('sso.staff_id_attribute');
        $quoted = str_replace("'", "''", $email);

        try {
            $response = Http::withToken($this->appToken())->acceptJson()->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->get(rtrim(config('sso.graph_api_url'), '/').'/users', [
                    // mail and the sign-in name usually match at UniKL, but
                    // not always — accept either.
                    '$filter' => "mail eq '$quoted' or userPrincipalName eq '$quoted'",
                    '$select' => implode(',', array_unique([
                        'id', 'displayName', 'mail', 'userPrincipalName', 'mobilePhone', 'businessPhones', 'accountEnabled',
                        // A dotted path (onPremisesExtensionAttributes.extensionAttribute1)
                        // is selected by its top-level property.
                        strtok($staffIdAttribute, '.'),
                    ])),
                    '$top' => 2,
                ]);
        } catch (ConnectionException $e) {
            throw new SsoException('Could not reach Microsoft Graph: '.$e->getMessage(), self::UNAVAILABLE);
        }

        if ($response->failed() || ! is_array($response->json('value'))) {
            // 403 here almost always means User.Read.All (Application) hasn't
            // been granted admin consent on the app registration.
            throw new SsoException('Microsoft Graph user lookup failed', self::UNAVAILABLE, [
                'status' => $response->status(),
                'error' => $response->json('error.code'),
            ]);
        }

        $matches = $response->json('value');

        if (count($matches) !== 1) {
            throw new SsoException(count($matches) ? 'Email matches more than one Microsoft account' : 'No Microsoft account uses this email', self::NOT_FOUND, ['email' => $email]);
        }

        $user = $matches[0];

        if (($user['accountEnabled'] ?? true) === false) {
            throw new SsoException('Microsoft account is disabled', self::DISABLED, ['email' => $email]);
        }

        $phone = ($user['mobilePhone'] ?? null) ?: ($user['businessPhones'][0] ?? '');

        return [
            'oid' => strtolower((string) $user['id']),
            'staff_id' => trim((string) data_get($user, $staffIdAttribute, '')),
            'full_name' => trim((string) ($user['displayName'] ?? '')),
            'email' => strtolower(trim((string) (($user['mail'] ?? null) ?: ($user['userPrincipalName'] ?? $email)))),
            'phone_number' => trim((string) $phone),
        ];
    }

    /**
     * An app-only Graph token, reused until shortly before it expires.
     *
     * @throws SsoException
     */
    private function appToken(): string
    {
        if ($token = Cache::get(self::TOKEN_CACHE_KEY)) {
            return $token;
        }

        $this->entra->assertConfigured();

        try {
            $response = Http::asForm()->acceptJson()->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->post($this->entra->endpoint('token'), [
                    'client_id' => config('sso.client_id'),
                    'client_secret' => config('sso.client_secret'),
                    'grant_type' => 'client_credentials',
                    'scope' => 'https://graph.microsoft.com/.default',
                ]);
        } catch (ConnectionException $e) {
            throw new SsoException('Could not reach the Microsoft token endpoint: '.$e->getMessage(), self::UNAVAILABLE);
        }

        $token = $response->json('access_token');

        if ($response->failed() || ! is_string($token)) {
            throw new SsoException('Microsoft app-only token request failed', self::UNAVAILABLE, [
                'status' => $response->status(),
                'error' => $response->json('error'),
                'error_codes' => $response->json('error_codes'),
                'trace_id' => $response->json('trace_id'),
            ]);
        }

        Cache::put(self::TOKEN_CACHE_KEY, $token, max(60, (int) $response->json('expires_in', 3600) - 300));

        return $token;
    }
}
