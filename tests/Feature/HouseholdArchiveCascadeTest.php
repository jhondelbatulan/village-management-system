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

class HouseholdArchiveCascadeTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Household $household;

    private User $secretary;

    private Resident $approvedResident;

    private Resident $pendingResident;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Archive Cascade Village', 'village_code' => 'ACV']);

        $role = Role::where('role_slug', 'secretary')->firstOrFail();

        $this->secretary = User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'username' => 'secretary_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Sara',
            'last_name' => 'Secretary',
            'status' => 'active',
        ]);

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

        $this->approvedResident = $this->makeResident('approved');
        $this->pendingResident = $this->makeResident('pending');
    }

    private function makeResident(string $approvalStatus, ?int $householdId = null): Resident
    {
        return Resident::create([
            'village_id' => $this->village->id,
            'household_id' => $householdId ?? $this->household->id,
            'resident_code' => 'RES-' . Str::upper(Str::random(6)),
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'status' => 'active',
            'approval_status' => $approvalStatus,
            'submitted_by' => $this->secretary->id,
        ]);
    }

    public function test_archiving_a_household_archives_its_residents(): void
    {
        $this->actingAs($this->secretary)
            ->delete(route('households.destroy', $this->household))
            ->assertRedirect(route('households.index'));

        $this->assertTrue(Resident::withTrashed()->findOrFail($this->approvedResident->id)->trashed());
        $this->assertTrue(Resident::withTrashed()->findOrFail($this->pendingResident->id)->trashed());
        $this->assertSame(0, Resident::where('household_id', $this->household->id)->count());
    }

    public function test_archiving_a_household_does_not_touch_residents_of_other_households(): void
    {
        $otherHousehold = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-OTHER',
            'block_number' => '5',
            'lot_number' => '1',
            'household_head_name' => 'Pedro Santos',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);
        $otherResident = $this->makeResident('approved', $otherHousehold->id);

        $this->actingAs($this->secretary)
            ->delete(route('households.destroy', $this->household))
            ->assertRedirect(route('households.index'));

        $this->assertFalse($otherResident->fresh()->trashed());
    }

    public function test_restoring_a_household_restores_residents_archived_with_it(): void
    {
        $this->actingAs($this->secretary)
            ->delete(route('households.destroy', $this->household));

        $this->actingAs($this->secretary)
            ->post(route('households.restore', $this->household->id))
            ->assertRedirect(route('households.index'));

        $this->assertFalse($this->approvedResident->fresh()->trashed());
        $this->assertFalse($this->pendingResident->fresh()->trashed());
        $this->assertSame(2, Resident::where('household_id', $this->household->id)->count());
    }

    public function test_residents_archived_before_the_household_stay_archived_after_restore(): void
    {
        $this->pendingResident->delete();

        // deleted_at is stored at second precision; archive the household at a
        // later time so the resident's own archive timestamp stays distinct.
        $this->travelTo(now()->addMinute());

        $this->actingAs($this->secretary)
            ->delete(route('households.destroy', $this->household));

        $this->actingAs($this->secretary)
            ->post(route('households.restore', $this->household->id));

        $this->assertFalse($this->approvedResident->fresh()->trashed());
        $this->assertTrue(Resident::withTrashed()->findOrFail($this->pendingResident->id)->trashed());
    }
}
