<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\EnsureUserIsStaffMember;
use App\Models\User;
use App\Support\Sso\AdminAccountResolver;
use App\Support\Sso\SsoException;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MicrosoftSsoLoginTest extends TestCase
{
    use CreatesStaffAccounts, RefreshDatabase;

    private const TENANT = '0a1b2c3d-0000-4000-8000-00000000abcd';

    private const OID = '11111111-2222-4333-8444-555555555555';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        config([
            'sso.enabled' => true,
            'sso.client_id' => 'test-client-id',
            'sso.client_secret' => 'test-client-secret',
            'sso.tenant_id' => self::TENANT,
            'sso.redirect_uri' => 'https://uniklab.test/api/auth/microsoft/callback',
        ]);
    }

    /**
     * The 2026_08_05_000002 data migration adds equipment to lab 9, which only
     * exists in real data, so foreign keys are off while the schema is built
     * on the empty test database and back on for the test itself.
     */
    protected function beforeRefreshingDatabase(): void
    {
        Schema::disableForeignKeyConstraints();
    }

    protected function afterRefreshingDatabase(): void
    {
        Schema::enableForeignKeyConstraints();
    }

    public function test_login_page_offers_microsoft_sign_in_only(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in with Microsoft')
            ->assertDontSee('Local Administrator Login')
            ->assertDontSee('name="password"', false);
    }

    public function test_password_login_is_switched_off(): void
    {
        $admin = $this->staff('admin', 'admin@unikl.edu.my');

        $this->post(route('login.attempt'), ['staff_id' => $admin->staff_id, 'password' => self::PASSWORD])
            ->assertNotFound();

        $this->assertGuest();
    }

    public function test_redirect_targets_the_unikl_tenant_with_state_nonce_and_pkce(): void
    {
        $query = $this->startSignIn();

        $this->assertSame('test-client-id', $query['client_id']);
        $this->assertSame('https://uniklab.test/api/auth/microsoft/callback', $query['redirect_uri']);
        $this->assertSame('code', $query['response_type']);
        $this->assertSame('S256', $query['code_challenge_method']);
        $this->assertNotEmpty($query['code_challenge']);
        $this->assertNotEmpty($query['state']);
        $this->assertNotEmpty($query['nonce']);
        $this->assertArrayNotHasKey('client_secret', $query);
    }

    /** Test 1 — valid UniKL account + existing panel role = signed in. */
    #[DataProvider('panelRoles')]
    public function test_unikl_account_with_a_panel_role_signs_in(string $role): void
    {
        $user = $this->staff($role, 'rudhiah@unikl.edu.my', ['staff_id' => '121212']);

        $this->signInWithMicrosoft(['mail' => 'Rudhiah@unikl.edu.my', 'userPrincipalName' => 'rudhiah@unikl.edu.my'])
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertSame('121212', $user->staff_id);
        $this->assertSame($role, $user->role->name);
        $this->assertSame(self::OID, $user->oid);
        $this->assertNotNull($user->last_login_at);
        $this->assertSame(1, User::count());

        Http::assertSent(fn (HttpRequest $request) => str_ends_with($request->url(), '/'.self::TENANT.'/oauth2/v2.0/token')
            && $request['code'] === 'test-code'
            && $request['client_secret'] === 'test-client-secret'
            && strlen($request['code_verifier']) >= 43);
        Http::assertSent(fn (HttpRequest $request) => str_starts_with($request->url(), 'https://graph.microsoft.com/v1.0/me')
            && $request->hasHeader('Authorization', 'Bearer test-access-token'));
    }

    public function test_user_principal_name_is_used_when_mail_is_empty(): void
    {
        $user = $this->staff('admin', 'rudhiah@unikl.edu.my');

        $this->signInWithMicrosoft(['mail' => null, 'userPrincipalName' => 'rudhiah@unikl.edu.my'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_linked_account_is_still_found_after_its_email_changes(): void
    {
        $user = $this->staff('admin', 'old.name@unikl.edu.my', ['oid' => self::OID]);

        $this->signInWithMicrosoft(['mail' => 'new.name@unikl.edu.my'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    /** Test 2 — valid UniKL account without a panel role = denied. */
    public function test_unikl_account_without_a_panel_role_is_refused(): void
    {
        $user = $this->staff('viewer', 'someone@unikl.edu.my');

        $this->assertRefused($this->signInWithMicrosoft(['mail' => 'someone@unikl.edu.my']));

        $user->refresh();
        $this->assertSame('viewer', $user->role->name);
        $this->assertNull($user->oid);

        $this->get(route('login'))->assertSee('You are not authorised to access the administrator system.');
    }

    public function test_deactivated_account_is_refused(): void
    {
        $this->staff('admin', 'someone@unikl.edu.my', ['is_active' => false]);

        $this->assertRefused($this->signInWithMicrosoft(['mail' => 'someone@unikl.edu.my']));
    }

    /** Test 3 — non-UniKL account = denied, even with a matching row. */
    public function test_non_unikl_account_is_refused_even_when_its_address_is_on_file(): void
    {
        $this->staff('admin', 'someone@gmail.com');

        $this->assertRefused($this->signInWithMicrosoft([
            'mail' => 'someone@gmail.com',
            'userPrincipalName' => 'someone_gmail.com#EXT#@unikl.onmicrosoft.com',
        ]));
    }

    public function test_token_issued_by_another_tenant_is_refused(): void
    {
        $this->staff('admin', 'rudhiah@unikl.edu.my');

        $this->assertRefused($this->signInWithMicrosoft(
            ['mail' => 'rudhiah@unikl.edu.my'],
            ['tid' => '99999999-0000-4000-8000-000000000000'],
        ));
    }

    /** Test 4 — student account = denied, even with a matching admin row. */
    public function test_student_account_is_refused_even_when_its_address_is_on_file(): void
    {
        $this->staff('admin', 'student@s.unikl.edu.my');

        $this->assertRefused($this->signInWithMicrosoft([
            'mail' => 'student@s.unikl.edu.my',
            'userPrincipalName' => 'student@s.unikl.edu.my',
        ]));
    }

    /** Test 5 — unknown UniKL account = denied, and nothing is created. */
    public function test_unknown_unikl_account_is_refused_and_no_account_is_created(): void
    {
        $this->staff('admin', 'rudhiah@unikl.edu.my');

        $this->assertRefused($this->signInWithMicrosoft(['mail' => 'stranger@unikl.edu.my']));

        $this->assertSame(1, User::count());
    }

    public function test_account_linked_to_a_different_microsoft_identity_is_refused(): void
    {
        $this->staff('admin', 'rudhiah@unikl.edu.my', ['oid' => '99999999-8888-4777-8666-555555555555']);

        $this->assertRefused($this->signInWithMicrosoft(['mail' => 'rudhiah@unikl.edu.my']));
    }

    public function test_microsoft_account_without_an_email_is_refused(): void
    {
        $this->staff('admin', 'rudhiah@unikl.edu.my');

        $this->assertRefused($this->signInWithMicrosoft(['mail' => null, 'userPrincipalName' => null]));
    }

    public function test_cancelling_at_microsoft_returns_to_the_login_page(): void
    {
        Http::fake();
        $this->startSignIn();

        $this->get(route('login.microsoft.callback', ['error' => 'access_denied', 'error_subcode' => 'cancel']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['sso' => SsoException::CANCELLED]);

        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_forged_state_is_refused_before_the_code_is_redeemed(): void
    {
        $this->staff('admin', 'rudhiah@unikl.edu.my');
        Http::fake();
        $this->startSignIn();

        $this->get(route('login.microsoft.callback', ['code' => 'test-code', 'state' => 'forged']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['sso' => SsoException::EXPIRED]);

        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_callback_without_a_started_sign_in_is_refused(): void
    {
        Http::fake();

        $this->get(route('login.microsoft.callback', ['code' => 'test-code', 'state' => 'anything']))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['sso' => SsoException::EXPIRED]);

        Http::assertNothingSent();
    }

    public function test_rejected_token_request_shows_only_a_generic_error(): void
    {
        $this->staff('admin', 'rudhiah@unikl.edu.my');
        $query = $this->startSignIn();

        Http::fake(['login.microsoftonline.com/*' => Http::response([
            'error' => 'invalid_client',
            'error_description' => 'AADSTS7000215: Invalid client secret provided.',
            'error_codes' => [7000215],
        ], 401)]);

        $this->get(route('login.microsoft.callback', ['code' => 'test-code', 'state' => $query['state']]))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['sso' => SsoException::FAILED]);

        $this->assertStringNotContainsString('AADSTS', session('errors')->first('sso'));
        $this->assertGuest();
    }

    public function test_microsoft_graph_failure_shows_only_a_generic_error(): void
    {
        $this->staff('admin', 'rudhiah@unikl.edu.my');

        $this->assertRefused(
            $this->signInWithMicrosoft(['mail' => 'rudhiah@unikl.edu.my'], graphStatus: 503),
            SsoException::FAILED,
        );
    }

    public function test_unreachable_microsoft_shows_only_a_generic_error(): void
    {
        $query = $this->startSignIn();

        Http::fake(['login.microsoftonline.com/*' => Http::failedConnection()]);

        $this->get(route('login.microsoft.callback', ['code' => 'test-code', 'state' => $query['state']]))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['sso' => SsoException::FAILED]);

        $this->assertGuest();
    }

    public function test_missing_azure_configuration_never_leaves_the_site(): void
    {
        config(['sso.client_secret' => null]);

        $this->post(route('login.microsoft'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['sso' => SsoException::FAILED]);
    }

    public function test_multi_tenant_authority_is_refused(): void
    {
        config(['sso.tenant_id' => 'common']);

        $this->post(route('login.microsoft'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['sso' => SsoException::FAILED]);
    }

    #[DataProvider('emailDomains')]
    public function test_email_domain_must_match_exactly(string $email, bool $allowed): void
    {
        $this->assertSame($allowed, AdminAccountResolver::isAllowedEmail($email));
    }

    public static function emailDomains(): array
    {
        return [
            'staff' => ['rudhiah@unikl.edu.my', true],
            'mixed case' => ['Rudhiah@UniKL.edu.my', true],
            'student subdomain' => ['student@s.unikl.edu.my', false],
            'any other subdomain' => ['someone@mail.unikl.edu.my', false],
            'lookalike prefix' => ['someone@evilunikl.edu.my', false],
            'lookalike suffix' => ['someone@unikl.edu.my.example.com', false],
            'other domain' => ['someone@gmail.com', false],
            'quoted local part' => ['"someone@unikl.edu.my"@gmail.com', false],
            'guest user principal name' => ['someone_gmail.com#EXT#@unikl.onmicrosoft.com', false],
            'no local part' => ['@unikl.edu.my', false],
            'bare domain' => ['unikl.edu.my', false],
        ];
    }

    /** Posts the "Sign in with Microsoft" form and returns the authorize URL's query. */
    private function startSignIn(): array
    {
        $location = (string) $this->post(route('login.microsoft'))->assertRedirect()->headers->get('Location');

        $this->assertStringStartsWith('https://login.microsoftonline.com/'.self::TENANT.'/oauth2/v2.0/authorize?', $location);
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);

        return $query;
    }

    /** The whole round trip: our redirect, Microsoft (faked), then the callback. */
    private function signInWithMicrosoft(array $profile, array $claims = [], int $graphStatus = 200): TestResponse
    {
        $query = $this->startSignIn();

        $profile += [
            'id' => self::OID,
            'displayName' => 'Test Staff',
            'mail' => null,
            'userPrincipalName' => null,
            'jobTitle' => null,
            'officeLocation' => null,
        ];

        Http::fake([
            'login.microsoftonline.com/*' => Http::response([
                'token_type' => 'Bearer',
                'access_token' => 'test-access-token',
                'refresh_token' => 'test-refresh-token',
                'id_token' => $this->idToken($claims + ['tid' => self::TENANT, 'oid' => self::OID, 'nonce' => $query['nonce']]),
            ]),
            'graph.microsoft.com/*' => $graphStatus === 200
                ? Http::response($profile)
                : Http::response(['error' => ['code' => 'serviceNotAvailable']], $graphStatus),
        ]);

        return $this->get(route('login.microsoft.callback', ['code' => 'test-code', 'state' => $query['state']]));
    }

    private function idToken(array $claims): string
    {
        $encode = fn (array $part) => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');

        return $encode(['alg' => 'RS256', 'typ' => 'JWT']).'.'.$encode($claims).'.signature';
    }

    private function assertRefused(TestResponse $response, string $message = EnsureUserIsStaffMember::NOT_AUTHORISED_MESSAGE): void
    {
        $response->assertRedirect(route('login'))->assertSessionHasErrors(['sso' => $message]);

        $this->assertGuest();
    }
}
