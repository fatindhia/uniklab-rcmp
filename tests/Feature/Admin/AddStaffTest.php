<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\StaffController;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Auth\CreatesStaffAccounts;
use Tests\TestCase;

class AddStaffTest extends TestCase
{
    use CreatesStaffAccounts, RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
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

    public static function ssoModes(): array
    {
        return ['sso on' => [true], 'sso off' => [false]];
    }

    #[DataProvider('ssoModes')]
    public function test_staff_are_added_by_email_role_and_lab_types(bool $sso): void
    {
        config(['sso.enabled' => $sso]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), [
                'email' => ' Aisyah@UniKL.edu.my ',
                'role_id' => $this->roleId('lab_staff'),
                'lab_types' => ['pharma', 'csl'],
                'password' => StaffController::DEFAULT_PASSWORD,
            ])
            ->assertSessionHasNoErrors();

        $user = User::where('email', 'aisyah@unikl.edu.my')->sole();
        $this->assertTrue($user->hasPendingStaffId());
        $this->assertSame('', $user->full_name);
        $this->assertSame('aisyah@unikl.edu.my', $user->displayName());
        $this->assertSame(['csl', 'pharma'], $user->lab_types);
        $this->assertNull($user->oid);
        $this->assertTrue(Hash::check(StaffController::DEFAULT_PASSWORD, $user->password_hash));
    }

    public function test_a_new_account_signs_in_locally_with_its_email_and_the_default_password(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@unikl.edu.my', 'role_id' => $this->roleId('lab_staff')]);
        auth()->logout();

        $this->post(route('login.attempt'), ['email' => 'aisyah@unikl.edu.my', 'password' => StaffController::DEFAULT_PASSWORD])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs(User::where('email', 'aisyah@unikl.edu.my')->sole());
    }

    public function test_the_admin_can_choose_another_password(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@unikl.edu.my', 'role_id' => $this->roleId('admin'), 'password' => 'Another@5678'])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('Another@5678', User::where('email', 'aisyah@unikl.edu.my')->value('password_hash')));
    }

    public function test_an_email_already_on_file_is_refused(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'ADMIN@unikl.edu.my', 'role_id' => $this->roleId('lab_staff')])
            ->assertSessionHasErrors('email');

        $this->assertSame(1, User::count());
    }

    public function test_with_sso_on_only_unikl_addresses_are_accepted(): void
    {
        config(['sso.enabled' => true]);

        $this->actingAs($this->admin)
            ->post(route('admin.staff.store'), ['email' => 'aisyah@s.unikl.edu.my', 'role_id' => $this->roleId('lab_staff')])
            ->assertSessionHasErrors('email');
    }

    public function test_a_pending_account_can_be_edited_before_its_name_arrives(): void
    {
        $user = $this->staff('lab_staff', 'aisyah@unikl.edu.my', ['staff_id' => User::newPendingStaffId(), 'full_name' => '']);

        $this->actingAs($this->admin)
            ->patch(route('admin.staff.update', $user), ['role_id' => $this->roleId('admin'), 'is_active' => 1])
            ->assertSessionHasNoErrors();

        $this->assertSame('admin', $user->fresh()->role->name);
    }

    private function roleId(string $name): int
    {
        return Role::where('name', $name)->value('id');
    }
}
