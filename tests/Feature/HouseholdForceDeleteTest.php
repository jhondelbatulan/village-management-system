<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Household;
use App\Models\Participation;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HouseholdForceDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Household $household;

    private User $superAdmin;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Force Delete Village', 'village_code' => 'FDV']);
        $this->superAdmin = $this->makeUser('super_admin');
        $this->secretary = $this->makeUser('secretary', $this->village->id);

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '4',
            'lot_number' => '9',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);
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

    public function test_super_admin_can_permanently_delete_a_household(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('households.force-delete', $this->household));

        $response->assertRedirect(route('households.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('households', ['id' => $this->household->id]);
    }

    public function test_force_delete_works_on_archived_households(): void
    {
        $this->household->delete();

        $response = $this->actingAs($this->superAdmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('households.force-delete', $this->household->id));

        $response->assertRedirect(route('households.index'));

        $this->assertDatabaseMissing('households', ['id' => $this->household->id]);
    }

    public function test_secretary_cannot_force_delete_a_household(): void
    {
        $this->actingAs($this->secretary)
            ->delete(route('households.force-delete', $this->household))
            ->assertForbidden();

        $this->assertDatabaseHas('households', ['id' => $this->household->id]);
    }

    public function test_force_delete_removes_related_participations(): void
    {
        $event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Barangay Assembly',
            'category' => 'gathering',
            'event_date' => now()->toDateString(),
            'status' => 'completed',
            'created_by' => $this->secretary->id,
        ]);

        Participation::create([
            'event_id' => $event->id,
            'household_id' => $this->household->id,
            'status' => 'present',
            'recorded_by' => $this->secretary->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('households.force-delete', $this->household))
            ->assertRedirect(route('households.index'));

        $this->assertDatabaseMissing('households', ['id' => $this->household->id]);
        $this->assertSame(0, Participation::count());
    }

    public function test_force_delete_also_permanently_removes_the_household_residents(): void
    {
        $approvedResident = $this->makeResident($this->household->id, 'approved');
        $pendingResident = $this->makeResident($this->household->id, 'pending');

        $otherHousehold = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-OTHER',
            'block_number' => '5',
            'lot_number' => '1',
            'household_head_name' => 'Pedro Santos',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);
        $otherResident = $this->makeResident($otherHousehold->id, 'approved');

        $this->actingAs($this->superAdmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('households.force-delete', $this->household))
            ->assertRedirect(route('households.index'));

        $this->assertDatabaseMissing('residents', ['id' => $approvedResident->id]);
        $this->assertDatabaseMissing('residents', ['id' => $pendingResident->id]);
        $this->assertDatabaseHas('residents', ['id' => $otherResident->id]);
    }

    public function test_force_delete_removes_residents_that_were_already_archived(): void
    {
        $resident = $this->makeResident($this->household->id, 'approved');
        $resident->delete();

        $this->actingAs($this->superAdmin)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('households.force-delete', $this->household->id))
            ->assertRedirect(route('households.index'));

        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
    }

    private function makeResident(int $householdId, string $approvalStatus): Resident
    {
        return Resident::create([
            'village_id' => $this->village->id,
            'household_id' => $householdId,
            'resident_code' => 'RES-' . Str::upper(Str::random(6)),
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'status' => 'active',
            'approval_status' => $approvalStatus,
            'submitted_by' => $this->secretary->id,
        ]);
    }
}
