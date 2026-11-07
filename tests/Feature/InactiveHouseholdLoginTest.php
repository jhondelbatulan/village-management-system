<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class InactiveHouseholdLoginTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::create([
            'village_name' => 'Blocked Village',
            'village_code' => 'BV-001',
            'status' => 'active',
        ]);
    }

    private function makeHousehold(string $status): Household
    {
        return Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Head of Household',
            'status' => $status,
            'approval_status' => 'approved',
        ]);
    }

    private function makeResident(Household $household): User
    {
        $role = Role::where('role_slug', 'resident')->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'household_id' => $household->id,
            'username' => 'blockedresident',
            'email' => 'blockedresident@gmail.com',
            'password' => 'ResidentPass123',
            'first_name' => 'Blocked',
            'last_name' => 'Resident',
            'status' => 'active',
        ]);
    }

    public function test_active_account_cannot_login_while_assigned_household_is_inactive(): void
    {
        $user = $this->makeResident($this->makeHousehold('inactive'));

        $this->post(route('login.submit'), [
            'username' => $user->username,
            'password' => 'ResidentPass123',
        ])->assertRedirect(route('login', ['username' => $user->username]));

        $this->assertGuest();
        $this->assertStringContainsString(
            'household is not yet activated',
            session('errors')->first('username')
        );
    }

    public function test_inactive_household_block_is_logged_as_a_failed_login(): void
    {
        $user = $this->makeResident($this->makeHousehold('inactive'));

        $this->post(route('login.submit'), [
            'username' => $user->username,
            'password' => 'ResidentPass123',
        ]);

        $this->assertDatabaseHas('login_history', [
            'username' => $user->username,
            'status' => 'failed',
            'failure_reason' => 'Household inactive',
        ]);
    }

    public function test_login_succeeds_once_the_assigned_household_is_activated(): void
    {
        $household = $this->makeHousehold('inactive');
        $user = $this->makeResident($household);

        $this->post(route('login.submit'), [
            'username' => $user->username,
            'password' => 'ResidentPass123',
        ]);
        $this->assertGuest();

        $household->update(['status' => 'active']);

        $this->post(route('login.submit'), [
            'username' => $user->username,
            'password' => 'ResidentPass123',
        ])->assertRedirect(route('dashboard.resident'));

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('ResidentPass123', $user->fresh()->password));
    }

    public function test_staff_accounts_without_a_household_are_unaffected(): void
    {
        $role = Role::where('role_slug', 'secretary')->firstOrFail();

        $secretary = User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'username' => 'blocksecretary',
            'email' => 'blocksecretary@gmail.com',
            'password' => 'SecretaryPass123',
            'first_name' => 'Staff',
            'last_name' => 'Member',
            'status' => 'active',
        ]);

        $this->post(route('login.submit'), [
            'username' => $secretary->username,
            'password' => 'SecretaryPass123',
        ])->assertRedirect(route('dashboard.secretary'));

        $this->assertAuthenticatedAs($secretary);
    }
}
