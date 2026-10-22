<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardRecentActivityTest extends TestCase
{
    use RefreshDatabase;

    private Village $villageA;

    private Village $villageB;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    private User $otherSecretary;

    private User $resident;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->villageA = Village::forceCreate(['village_name' => 'Alpha Village', 'village_code' => 'ALPA']);
        $this->villageB = Village::forceCreate(['village_name' => 'Beta Village', 'village_code' => 'BETB']);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->villageA->id);
        $this->secretary = $this->makeUser('secretary', $this->villageA->id);
        $this->treasurer = $this->makeUser('treasurer', $this->villageA->id);
        $this->otherSecretary = $this->makeUser('secretary', $this->villageB->id);
        $this->resident = $this->makeUser('resident', $this->villageA->id);

        $household = Household::create([
            'village_id' => $this->villageA->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '9',
            'household_head_name' => 'Resident Head',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);
        $this->resident->forceFill(['household_id' => $household->id])->save();

        $this->log($this->secretary, 'SECRETARY_A_ACTION', $this->villageA->id);
        $this->log($this->treasurer, 'TREASURER_A_ACTION', $this->villageA->id);
        $this->log($this->resident, 'RESIDENT_A_ACTION', $this->villageA->id);
        $this->log($this->otherSecretary, 'SECRETARY_B_ACTION', $this->villageB->id);
        $this->log($this->superAdmin, 'SUPER_ADMIN_ACTION', null);
        $this->log($this->superAdmin, 'SUPER_ADMIN_ON_VILLAGE_A', $this->villageA->id);
    }

    private function makeUser(string $roleSlug, ?int $villageId = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $villageId,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function log(User $user, string $description, ?int $villageId): void
    {
        AuditLog::create([
            'user_id' => $user->id,
            'role' => $user->role?->role_slug,
            'village_id' => $villageId,
            'action' => 'Viewed Dashboard',
            'module' => 'Dashboard',
            'description' => $description,
        ]);
    }

    public function test_super_admin_sees_recent_activity_from_every_village(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard.super_admin'));

        $response->assertOk()
            ->assertSee('SECRETARY_A_ACTION')
            ->assertSee('TREASURER_A_ACTION')
            ->assertSee('RESIDENT_A_ACTION')
            ->assertSee('SECRETARY_B_ACTION')
            ->assertSee('SUPER_ADMIN_ACTION')
            ->assertSee('SUPER_ADMIN_ON_VILLAGE_A');
    }

    public function test_admin_sees_only_activity_belonging_to_their_own_village(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.admin'));

        $response->assertOk()
            ->assertSee('SECRETARY_A_ACTION')
            ->assertSee('TREASURER_A_ACTION')
            ->assertSee('RESIDENT_A_ACTION')
            ->assertSee('SUPER_ADMIN_ON_VILLAGE_A')
            ->assertDontSee('SECRETARY_B_ACTION')
            ->assertDontSee('SUPER_ADMIN_ACTION');
    }

    public function test_secretary_sees_only_their_own_activity(): void
    {
        $response = $this->actingAs($this->secretary)->get(route('dashboard.secretary'));

        $response->assertOk()
            ->assertSee('SECRETARY_A_ACTION')
            ->assertDontSee('TREASURER_A_ACTION')
            ->assertDontSee('RESIDENT_A_ACTION')
            ->assertDontSee('SECRETARY_B_ACTION')
            ->assertDontSee('SUPER_ADMIN_ON_VILLAGE_A');
    }

    public function test_treasurer_sees_only_their_own_activity(): void
    {
        $response = $this->actingAs($this->treasurer)->get(route('dashboard.treasurer'));

        $response->assertOk()
            ->assertSee('TREASURER_A_ACTION')
            ->assertDontSee('SECRETARY_A_ACTION')
            ->assertDontSee('RESIDENT_A_ACTION')
            ->assertDontSee('SECRETARY_B_ACTION');
    }

    public function test_resident_sees_only_their_own_activity(): void
    {
        $response = $this->actingAs($this->resident)->get(route('dashboard.resident'));

        $response->assertOk()
            ->assertSee('My Recent Activity')
            ->assertSee('RESIDENT_A_ACTION')
            ->assertDontSee('SECRETARY_A_ACTION')
            ->assertDontSee('TREASURER_A_ACTION')
            ->assertDontSee('SECRETARY_B_ACTION');
    }
}
