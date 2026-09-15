<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\EnsureUserIsStaffMember;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LocalAdminLoginTest extends TestCase
{
    use CreatesStaffAccounts, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        // A local or Docker checkout has no Entra app at all.
        config([
            'sso.enabled' => false,
            'sso.client_id' => null,
            'sso.client_secret' => null,
            'sso.tenant_id' => null,
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

    public function test_login_page_offers_the_local_form_only(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Local Administrator Login')
            ->assertSee('name="password"', false)
            ->assertDontSee('Sign in with Microsoft');
    }

    /** Test 6 — SSO off + panel account credentials = signed in. */
    #[DataProvider('panelRoles')]
    public function test_panel_account_signs_in_with_its_password(string $role): void
    {
        $user = $this->staff($role, 'someone@unikl.edu.my');

        $this->post(route('login.attempt'), ['staff_id' => $user->staff_id, 'password' => self::PASSWORD])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    /** Test 7 — SSO off + account without a panel role = denied. */
    public function test_account_without_a_panel_role_is_refused(): void
    {
        $user = $this->staff('viewer', 'someone@unikl.edu.my');

        $this->from(route('login'))
            ->post(route('login.attempt'), ['staff_id' => $user->staff_id, 'password' => self::PASSWORD])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['staff_id' => EnsureUserIsStaffMember::NOT_AUTHORISED_MESSAGE]);

        $this->assertGuest();
        $this->assertNull($user->fresh()->last_login_at);
    }

    public function test_wrong_password_is_refused_without_saying_why(): void
    {
        $user = $this->staff('viewer', 'someone@unikl.edu.my');

        $this->from(route('login'))
            ->post(route('login.attempt'), ['staff_id' => $user->staff_id, 'password' => 'wrong-password'])
            ->assertSessionHasErrors(['staff_id' => 'Those credentials do not match our records.']);

        $this->assertGuest();
    }

    public function test_deactivated_account_fails_like_a_wrong_password(): void
    {
        $user = $this->staff('admin', 'someone@unikl.edu.my', ['is_active' => false]);

        $this->from(route('login'))
            ->post(route('login.attempt'), ['staff_id' => $user->staff_id, 'password' => self::PASSWORD])
            ->assertSessionHasErrors(['staff_id' => 'Those credentials do not match our records.']);

        $this->assertGuest();
    }

    public function test_microsoft_sign_in_is_switched_off(): void
    {
        $this->post(route('login.microsoft'))->assertNotFound();
        $this->get(route('login.microsoft.callback', ['code' => 'x', 'state' => 'y']))->assertNotFound();
    }

    public function test_deactivated_account_is_signed_out_of_an_open_session(): void
    {
        $user = $this->staff('admin', 'someone@unikl.edu.my', ['is_active' => false]);

        $this->actingAs($user)
            ->get(route('admin.history'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['staff_id' => EnsureUserIsStaffMember::NOT_AUTHORISED_MESSAGE]);

        $this->assertGuest();
    }

    public function test_account_without_a_panel_role_is_signed_out_of_an_open_session(): void
    {
        $user = $this->staff('viewer', 'someone@unikl.edu.my');

        $this->actingAs($user)
            ->get(route('admin.history'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['staff_id' => EnsureUserIsStaffMember::NOT_AUTHORISED_MESSAGE]);

        $this->assertGuest();
    }
}
