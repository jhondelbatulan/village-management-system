<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HouseholdAssignmentOptionsTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $admin;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Assignment Village', 'village_code' => 'ASV']);

        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
    }

    private function makeUser(string $roleSlug, ?int $villageId = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $villageId,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function makeHousehold(array $overrides = []): Household
    {
        return Household::create(array_merge([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '2',
            'household_head_name' => 'Juan Dela Cruz',
            'address' => 'Purok 1, Main Street',
            'status' => 'active',
            'approval_status' => 'draft',
        ], $overrides));
    }

    public function test_admin_sees_existing_household_when_creating_a_resident_account(): void
    {
        $household = $this->makeHousehold();

        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertSee($household->household_number);
        $response->assertSee('Blk 1, Lot 2', false);
    }

    public function test_secretary_sees_existing_household_when_registering_a_resident(): void
    {
        $household = $this->makeHousehold();

        $response = $this->actingAs($this->secretary)->get(route('residents.create'));

        $response->assertOk();
        $response->assertSee($household->household_number);
        $response->assertSee('Blk 1, Lot 2', false);
    }

    public function test_admin_sees_household_that_is_not_yet_approved(): void
    {
        $household = $this->makeHousehold(['approval_status' => 'pending']);

        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertSee($household->household_number);
    }

    public function test_admin_sees_inactive_household_when_creating_a_resident_account(): void
    {
        $household = $this->makeHousehold(['status' => 'inactive']);

        $response = $this->actingAs($this->admin)->get(route('users.create'));

        $response->assertOk();
        $response->assertSee($household->household_number);
    }

    public function test_resident_form_shows_households_that_are_still_being_approved(): void
    {
        $household = $this->makeHousehold(['approval_status' => 'pending']);

        $response = $this->actingAs($this->secretary)->get(route('residents.create'));

        $response->assertOk();
        $response->assertSee($household->household_number);
        $response->assertSee('Pending Approval', false);
    }

    public function test_secretary_sees_current_household_when_editing_a_resident(): void
    {
        $household = $this->makeHousehold(['approval_status' => 'pending']);

        $resident = Resident::create([
            'village_id' => $this->village->id,
            'household_id' => $household->id,
            'resident_code' => 'RES-' . Str::upper(Str::random(6)),
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'gender' => 'Female',
            'status' => 'active',
            'approval_status' => 'draft',
        ]);

        $response = $this->actingAs($this->secretary)->get(route('residents.edit', $resident));

        $response->assertOk();
        $response->assertSee($household->household_number);
    }
}
