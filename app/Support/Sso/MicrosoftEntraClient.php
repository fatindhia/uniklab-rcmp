<?php

namespace App\Support\Sso;

use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * The OAuth 2.0 authorization-code flow (with PKCE) against Microsoft Entra
 * ID, ending in the signed-in account's Microsoft Graph profile.
 *
 * Tokens never leave this class: the access token is used for the one Graph
 * call and dropped, the refresh token is never kept, and neither is logged,
 * stored in the session, or sent to the browser.
 */
class MicrosoftEntraClient
{
    /** A sign-in left open longer than this has to be started again. */
    private const PENDING_TTL_SECONDS = 600;

    private const HTTP_TIMEOUT_SECONDS = 15;

    private const SESSION_KEY = 'sso.pending';

    /**
     * Starts a sign-in and returns the Microsoft URL to send the browser to.
     *
     * The state (CSRF on the callback), nonce (id_token replay) and PKCE
     * verifier (stolen code) stay server-side in the session; only the state,
     * nonce and the verifier's hash go into the URL.
     *
     * @throws SsoException
     */
    public function authorizationUrl(Session $session): string
    {
        $this->assertConfigured();

        $pending = [
            'state' => Str::random(40),
            'nonce' => Str::random(40),
            'verifier' => Str::random(64),
            'started_at' => time(),
        ];
        $session->put(self::SESSION_KEY, $pending);

        return $this->endpoint('authorize').'?'.http_build_query([
            'client_id' => config('sso.client_id'),
            'response_type' => 'code',
            // query, not form_post: the callback stays a top-level GET, which
            // the SameSite=lax session cookie (and so the state) survives.
            'response_mode' => 'query',
            'redirect_uri' => $this->redirectUri(),
            'scope' => config('sso.scopes'),
            'state' => $pending['state'],
            'nonce' => $pending['nonce'],
            'code_challenge' => $this->codeChallenge($pending['verifier']),
            'code_challenge_method' => 'S256',
            // Shared lab PCs: always ask which account, rather than silently
            // reusing whoever last signed in to Microsoft in this browser.
            'prompt' => 'select_account',
        ], '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Finishes a sign-in from Microsoft's redirect back to the callback and
     * returns the account's Graph profile (id, displayName, mail,
     * userPrincipalName, ...).
     *
     * @throws SsoException
     */
    public function handleCallback(Request $request): array
    {
        // Pulled, not read: whatever happens next, this callback can't be replayed.
        $pending = $request->session()->pull(self::SESSION_KEY);

        if ($this->queryString($request, 'error') !== '') {
            throw $this->authorizeError($request);
        }

        $state = $this->queryString($request, 'state');
        if (! is_array($pending) || $state === '' || ! hash_equals($pending['state'], $state)) {
            throw new SsoException('Callback state is missing or does not match this session', SsoException::EXPIRED);
        }

        if (time() - $pending['started_at'] > self::PENDING_TTL_SECONDS) {
            throw new SsoException('Callback arrived after the sign-in window closed', SsoException::EXPIRED);
        }

        $code = $this->queryString($request, 'code');
        if ($code === '') {
            throw new SsoException('Callback carried no authorization code');
        }

        $this->assertConfigured();

        $tokens = $this->redeemCode($code, $pending['verifier']);
        $claims = $this->idTokenClaims($tokens['id_token']);

        if (! hash_equals($pending['nonce'], (string) ($claims['nonce'] ?? ''))) {
            throw new SsoException('id_token nonce does not match this sign-in');
        }

        // The authority URL already names the UniKL tenant; make sure the token agrees.
        if (strtolower((string) ($claims['tid'] ?? '')) !== strtolower(config('sso.tenant_id'))) {
            throw SsoException::notAuthorised('id_token was issued by a different tenant', ['tid' => $claims['tid'] ?? null]);
        }

        $profile = $this->fetchProfile($tokens['access_token']);

        if (strtolower((string) ($claims['oid'] ?? '')) !== strtolower($profile['id'])) {
            throw new SsoException('Graph profile does not belong to the account in the id_token');
        }

        return $profile;
    }

    /** Microsoft sent the browser back with an error instead of a code. */
    private function authorizeError(Request $request): SsoException
    {
        $description = Str::limit($this->queryString($request, 'error_description'), 300);
        $context = [
            'error' => $this->queryString($request, 'error'),
            'error_subcode' => $this->queryString($request, 'error_subcode'),
            'error_description' => $description,
        ];

        return match (true) {
            $context['error_subcode'] === 'cancel' => new SsoException('User cancelled Microsoft sign-in', SsoException::CANCELLED, $context),
            // The Entra app requires user assignment and this account isn't assigned.
            str_contains($description, 'AADSTS50105') => SsoException::notAuthorised('Account is not assigned to the Entra application', $context),
            default => new SsoException('Microsoft returned an authorization error', SsoException::FAILED, $context),
        };
    }

    /**
     * @return array{access_token: string, id_token: ?string}
     *
     * @throws SsoException
     */
    private function redeemCode(string $code, string $verifier): array
    {
        try {
            $response = Http::asForm()->acceptJson()->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->post($this->endpoint('token'), [
                    'client_id' => config('sso.client_id'),
                    'client_secret' => config('sso.client_secret'),
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'redirect_uri' => $this->redirectUri(),
                    'code_verifier' => $verifier,
                    'scope' => config('sso.scopes'),
                ]);
        } catch (ConnectionException $e) {
            throw new SsoException('Could not reach the Microsoft token endpoint: '.$e->getMessage());
        }

        if ($response->failed() || ! is_string($response->json('access_token'))) {
            // Failure bodies carry error codes and a description, never a
            // token, so these fields are safe to log. A wrong or expired
            // client secret (AADSTS7000215 / 7000222), an unknown tenant
            // (AADSTS90002) and an expired or reused code (AADSTS70008 /
            // 54005) all end up here.
            throw new SsoException('Microsoft token request failed', SsoException::FAILED, [
                'status' => $response->status(),
                'error' => $response->json('error'),
                'error_codes' => $response->json('error_codes'),
                'trace_id' => $response->json('trace_id'),
            ]);
        }

        return [
            'access_token' => $response->json('access_token'),
            'id_token' => $response->json('id_token'),
        ];
    }

    /**
     * The id_token comes straight from the token endpoint over TLS, which
     * OpenID Connect Core §3.1.3.7 accepts in place of checking its
     * signature — so decoding the payload is enough to read nonce, tid, oid.
     *
     * @throws SsoException
     */
    private function idTokenClaims(?string $idToken): array
    {
        $parts = explode('.', (string) $idToken);
        $claims = count($parts) === 3
            ? json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true)
            : null;

        if (! is_array($claims)) {
            throw new SsoException('Token response had no usable id_token — is "openid" in AZURE_OAUTH_SCOPES?');
        }

        return $claims;
    }

    /** @throws SsoException */
    private function fetchProfile(string $accessToken): array
    {
        $url = rtrim(config('sso.graph_api_url'), '/').'/'.ltrim(config('sso.graph_me_path'), '/');

        try {
            $response = Http::withToken($accessToken)->acceptJson()->timeout(self::HTTP_TIMEOUT_SECONDS)
                ->get($url, ['$select' => config('sso.graph_me_select')]);
        } catch (ConnectionException $e) {
            throw new SsoException('Could not reach Microsoft Graph: '.$e->getMessage());
        }

        if ($response->failed() || ! is_string($response->json('id'))) {
            throw new SsoException('Microsoft Graph /me request failed', SsoException::FAILED, [
                'status' => $response->status(),
                'error' => $response->json('error.code'),
            ]);
        }

        return $response->json();
    }

    /** @throws SsoException */
    public function assertConfigured(): void
    {
        $missing = array_filter(['client_id', 'client_secret', 'tenant_id'], fn ($key) => blank(config("sso.$key")));

        if ($missing) {
            throw new SsoException('SSO_ENABLED is true but sso.'.implode(', sso.', $missing).' is not set — check the AZURE_* values in .env');
        }

        if (! Str::isUuid(config('sso.tenant_id'))) {
            throw new SsoException('AZURE_TENANT_ID must be the UniKL tenant ID (a GUID), not a domain, "common" or "organizations"');
        }
    }

    public function endpoint(string $action): string
    {
        return rtrim(config('sso.authority'), '/').'/'.config('sso.tenant_id').'/oauth2/v2.0/'.$action;
    }

    private function redirectUri(): string
    {
        return config('sso.redirect_uri') ?: route('login.microsoft.callback');
    }

    private function codeChallenge(string $verifier): string
    {
        return rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');
    }

    /** A query parameter as a string — '' when absent or sent as an array. */
    private function queryString(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }
}
