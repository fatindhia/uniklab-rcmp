<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\StaffController;
use App\Models\Role;
use App\Models\User;
use App\Support\Sso\MicrosoftDirectory;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Auth\CreatesStaffAccounts;
use Tests\TestCase;

class AddStaffFromDirectoryTest extends TestCase
{
    use CreatesStaffAccounts, RefreshDatabase;

    private const OID = '11111111-2222-4333-8444-555555555555';

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        config([
            'sso.enabled' => true,
            'sso.client_id' => 'test-client-id',
            'sso.client_secret' => 'test-client-secret',
            'sso.tenant_id' => '0a1b2c3d-0000-4000-8000-00000000abcd',
        ]);

        $this->admin = $this->staff('admin', 'admin@unikl.edu.my');
    }

    /** See MicrosoftSsoLoginTest: lab 9's data migration needs FKs off on an empty DB. */
    protected function beforeRefreshingDatabase(): void
    {
        Schema::disableForeignKeyConstraints();
    }

    protected function afterRefreshingDatabase(): void
    {
        Schema::enableForeignKeyConstraints();
    }

    public function test_new_staff_are_created_from_the_directory_with_the_default_password(): void
    {
        $this->fakeDirectory([$this->directoryUser()]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), [
                'email' => 'Aisyah@UniKL.edu.my',
                'role_id' => $this->roleId('lab_staff'),
                'lab_types' => ['pharma', 'csl'],
                // Left blank, so they come from the directory.
                'staff_id' => '',
                'full_name' => '',
                'phone_number' => '',
            ])
            ->assertSessionHasNoErrors();

        $user = User::find('S12345');
        $this->assertNotNull($user);
        $this->assertSame('Nur Aisyah Binti Ahmad', $user->full_name);
        $this->assertSame('aisyah@unikl.edu.my', $user->email);
        $this->assertSame('+60 12-345 6789', $user->phone_number);
        $this->assertSame(self::OID, $user->oid);
        $this->assertSame($this->roleId('lab_staff'), $user->role_id);
        $this->assertTrue(Hash::check(StaffController::DEFAULT_PASSWORD, $user->password_hash));

        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'oauth2/v2.0/token') && $r['grant_type'] === 'client_credentials');
        Http::assertSent(fn (HttpRequest $r) => str_contains(urldecode($r->url()), "mail eq 'aisyah@unikl.edu.my'"));
    }

    public function test_business_phone_is_used_when_there_is_no_mobile(): void
    {
        $this->fakeDirectory([$this->directoryUser(['mobilePhone' => null, 'businessPhones' => ['03-1234 5678']])]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.staff.lookup'), ['email' => 'aisyah@unikl.edu.my'])
            ->assertOk()
            ->assertExactJson([
                'oid' => self::OID,
                'staff_id' => 'S12345',
                'full_name' => 'Nur Aisyah Binti Ahmad',
                'email' => 'aisyah@unikl.edu.my',
                'phone_number' => '03-1234 5678',
            ]);
    }

    public function test_unknown_email_is_refused(): void
    {
        $this->fakeDirectory([]);

        $this->actingAs($this->admin)
            ->postJson(route('admin.staff.lookup'), ['email' => 'nobody@unikl.edu.my'])
            ->assertStatus(422)
            ->assertJson(['message' => MicrosoftDirectory::NOT_FOUND]);
    }

    public function test_non_unikl_email_never_reaches_the_directory(): void
    {
        Http::fake();

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@s.unikl.edu.my', 'role_id' => $this->roleId('lab_staff')])
            ->assertSessionHasErrors('email');

        Http::assertNothingSent();
    }

    public function test_someone_who_already_has_an_account_is_refused(): void
    {
        $this->staff('lab_staff', 'old-address@unikl.edu.my', ['staff_id' => 'S12345']);
        $this->fakeDirectory([$this->directoryUser()]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@unikl.edu.my', 'role_id' => $this->roleId('lab_staff')])
            ->assertSessionHasErrors('email');

        $this->assertSame(2, User::count());
    }

    public function test_staff_id_is_typed_in_when_the_directory_has_none(): void
    {
        $this->fakeDirectory([$this->directoryUser(['employeeId' => null])]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@unikl.edu.my', 'role_id' => $this->roleId('lab_staff')])
            ->assertSessionHasErrors('staff_id');

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@unikl.edu.my', 'role_id' => $this->roleId('lab_staff'), 'staff_id' => 'S99999'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Nur Aisyah Binti Ahmad', User::find('S99999')?->full_name);
    }

    public function test_typed_values_win_and_only_blanks_come_from_the_directory(): void
    {
        $this->fakeDirectory([$this->directoryUser()]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), [
                'email' => 'aisyah@unikl.edu.my',
                'role_id' => $this->roleId('lab_staff'),
                'staff_id' => 'S99999',
                'full_name' => 'Aisyah Ahmad',
            ])
            ->assertSessionHasNoErrors();

        $user = User::find('S99999');
        $this->assertNull(User::find('S12345'));
        $this->assertSame('Aisyah Ahmad', $user->full_name);
        $this->assertSame('+60 12-345 6789', $user->phone_number);
        $this->assertSame(self::OID, $user->oid);
    }

    public function test_name_is_typed_in_when_the_directory_has_none(): void
    {
        $this->fakeDirectory([$this->directoryUser(['displayName' => null])]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@unikl.edu.my', 'role_id' => $this->roleId('lab_staff')])
            ->assertSessionHasErrors('full_name');

        $this->assertSame(1, User::count());
    }

    public function test_directory_failure_shows_a_generic_error(): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'app-token', 'expires_in' => 3600]),
            'graph.microsoft.com/*' => Http::response(['error' => ['code' => 'Authorization_RequestDenied']], 403),
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@unikl.edu.my', 'role_id' => $this->roleId('lab_staff')])
            ->assertSessionHasErrors(['email' => MicrosoftDirectory::UNAVAILABLE]);
    }

    public function test_without_sso_staff_are_typed_in_by_hand_with_the_default_password(): void
    {
        config(['sso.enabled' => false]);
        Http::fake();

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), [
                'staff_id' => 'L0001',
                'full_name' => 'Local Staff',
                'email' => 'local@unikl.edu.my',
                'role_id' => $this->roleId('lab_staff'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check(StaffController::DEFAULT_PASSWORD, User::find('L0001')->password_hash));
        Http::assertNothingSent();

        $this->actingAs($this->admin)->postJson(route('admin.staff.lookup'), ['email' => 'local@unikl.edu.my'])->assertNotFound();
    }

    public function test_lab_staff_cannot_look_people_up(): void
    {
        Http::fake();

        $this->actingAs($this->staff('lab_staff', 'lab@unikl.edu.my'))
            ->postJson(route('admin.staff.lookup'), ['email' => 'aisyah@unikl.edu.my'])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    private function fakeDirectory(array $users): void
    {
        Http::fake([
            'login.microsoftonline.com/*' => Http::response(['access_token' => 'app-token', 'expires_in' => 3600]),
            'graph.microsoft.com/*' => Http::response(['value' => $users]),
        ]);
    }

    private function directoryUser(array $overrides = []): array
    {
        return $overrides + [
            'id' => strtoupper(self::OID),
            'displayName' => 'Nur Aisyah Binti Ahmad',
            'mail' => 'Aisyah@unikl.edu.my',
            'userPrincipalName' => 'aisyah@unikl.edu.my',
            'employeeId' => 'S12345',
            'mobilePhone' => '+60 12-345 6789',
            'businessPhones' => [],
            'accountEnabled' => true,
        ];
    }

    private function roleId(string $name): int
    {
        return Role::where('name', $name)->value('id');
    }
}
