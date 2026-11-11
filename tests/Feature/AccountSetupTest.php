<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Freshly created accounts must finish a one-time setup (personal
 * information, email, contact number and their own new password) before
 * they are allowed to open their dashboard.
 */
class AccountSetupTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Setup Village', 'village_code' => 'STP']);
        $this->superAdmin = $this->makeUser('super_admin');
    }

    private function makeUser(string $roleSlug, ?int $villageId = null, array $extra = []): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create(array_merge([
            'role_id' => $role->id,
            'village_id' => $villageId,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Fresh',
            'last_name' => 'Account',
            'status' => 'active',
        ], $extra));
    }

    public function test_accounts_created_through_user_management_are_flagged_for_setup(): void
    {
        $adminRole = Role::where('role_slug', 'admin')->firstOrFail();
        $username = 'admin_' . Str::random(6);

        $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'role_id' => $adminRole->id,
            'village_id' => $this->village->id,
            'status' => 'active',
            'username' => $username,
            'email' => Str::random(8) . '@gmail.com',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('users.index'));

        $newUser = User::where('username', $username)->firstOrFail();
        $this->assertTrue($newUser->must_setup);

        // Accounts created directly (e.g. seeders, existing data) are unaffected.
        $this->assertFalse($this->superAdmin->fresh()->must_setup);
    }

    public function test_a_fresh_account_is_redirected_to_setup_instead_of_any_dashboard(): void
    {
        $user = $this->makeUser('admin', $this->village->id, ['must_setup' => true]);

        $this->actingAs($user)->get(route('dashboard.admin'))
            ->assertRedirect(route('account.setup.show'));

        $this->actingAs($user)->get(route('users.index'))
            ->assertRedirect(route('account.setup.show'));

        // The setup screen itself renders.
        $this->actingAs($user)->get(route('account.setup.show'))
            ->assertOk()
            ->assertSee('Set Up Your Account');
    }

    public function test_setup_screen_validates_and_capitalizes_inputs_in_real_time(): void
    {
        $user = $this->makeUser('admin', $this->village->id, ['must_setup' => true]);

        $this->actingAs($user)
            ->get(route('account.setup.show'))
            ->assertOk()
            // Names auto-capitalize first letters while typing...
            ->assertSee('autoCapitalize')
            ->assertSee('titleCase')
            // ...the middle name stays a single capital letter...
            ->assertSee('Middle name must be a single capital letter')
            ->assertSee('inputmode="numeric"', false)
            ->assertSee('digits only', false)
            // ...and every field reports errors before the page round-trip.
            ->assertSee('is-invalid')
            ->assertSee('Password confirmation does not match.');
    }

    public function test_login_sends_a_fresh_account_to_the_setup_screen(): void
    {
        $user = $this->makeUser('admin', $this->village->id, ['must_setup' => true]);

        $this->post(route('login'), [
            'username' => $user->username,
            'password' => 'secret-password',
        ])->assertRedirect(route('account.setup.show'));
    }

    public function test_completing_setup_saves_details_and_password_then_releases_the_dashboard(): void
    {
        $user = $this->makeUser('admin', $this->village->id, ['must_setup' => true]);

        $response = $this->actingAs($user)->post(route('account.setup.store'), [
            'first_name' => 'Juan',
            'middle_name' => 'M',
            'last_name' => 'Dela Cruz',
            'email' => 'juan.setup@example.com',
            'contact_number' => '09171234567',
            'password' => 'BrandNewPass1',
            'password_confirmation' => 'BrandNewPass1',
        ]);

        $response->assertRedirect(route('dashboard.admin'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertFalse($user->must_setup);
        $this->assertSame('Juan', $user->first_name);
        $this->assertSame('M', $user->middle_name);
        $this->assertSame('Dela Cruz', $user->last_name);
        $this->assertSame('juan.setup@example.com', $user->email);
        $this->assertSame('09171234567', $user->contact_number);
        $this->assertTrue(Hash::check('BrandNewPass1', $user->password));
        $this->assertFalse(Hash::check('secret-password', $user->password));

        // The dashboard is reachable now, and the setup screen no longer is.
        $this->actingAs($user)->get(route('dashboard.admin'))->assertOk();
        $this->actingAs($user)->get(route('account.setup.show'))
            ->assertRedirect(route('dashboard.admin'));
    }

    public function test_setup_validation_keeps_the_account_in_setup_mode_until_it_is_done_right(): void
    {
        $user = $this->makeUser('secretary', $this->village->id, ['must_setup' => true]);

        // Missing required personal information and contact details.
        $this->actingAs($user)->post(route('account.setup.store'), [])
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'contact_number', 'password']);

        $this->assertTrue($user->fresh()->must_setup);

        // A password that violates the password policy is rejected too.
        $this->actingAs($user)->post(route('account.setup.store'), [
            'first_name' => 'Weak',
            'last_name' => 'Password',
            'email' => $user->email,
            'contact_number' => '09171112222',
            'password' => 'alllowercase1',
            'password_confirmation' => 'alllowercase1',
        ])->assertSessionHasErrors('password');

        $fresh = $user->fresh();
        $this->assertTrue($fresh->must_setup);
        $this->assertTrue(Hash::check('secret-password', $fresh->password));

        $this->actingAs($fresh)->post(route('account.setup.store'), [
            'first_name' => 'Fresh',
            'last_name' => 'Account',
            'email' => $fresh->email,
            'contact_number' => '0917 111 2222',
            'password' => 'BrandNewPass1',
            'password_confirmation' => 'BrandNewPass1',
        ])->assertSessionHasErrors('contact_number');
    }

    public function test_existing_accounts_are_never_forced_into_setup(): void
    {
        $user = $this->makeUser('treasurer', $this->village->id);

        $this->assertFalse($user->fresh()->must_setup);

        $this->actingAs($user)->get(route('dashboard.treasurer'))->assertOk();
        $this->actingAs($user)->get(route('account.setup.show'))
            ->assertRedirect(route('dashboard.treasurer'));
    }

    public function test_logout_is_allowed_while_setup_is_pending(): void
    {
        $user = $this->makeUser('resident', $this->village->id, [
            'household_id' => null,
            'must_setup' => true,
        ]);

        $this->actingAs($user)->post(route('logout'))
            ->assertRedirect(route('login'));
    }
}
