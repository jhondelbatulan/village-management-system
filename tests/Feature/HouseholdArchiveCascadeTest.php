<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\PendingChange;
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

    private User $admin;

    private User $superAdmin;

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

        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->superAdmin = $this->makeUser('super_admin');

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

    /**
     * A secretary delete only stages a request (behind the password prompt);
     * the Admin (Stage 1) and the Super Admin (Stage 2) must approve it
     * before the household is actually archived.
     */
    private function requestAndApproveHouseholdDelete(Household $household): PendingChange
    {
        $this->actingAs($this->secretary)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('households.destroy', $household))
            ->assertRedirect(route('households.index'));

        $change = PendingChange::where('action', 'delete')->latest('id')->firstOrFail();

        $this->actingAs($this->admin)->post(route('approvals.approve-delete', $change));
        $this->actingAs($this->superAdmin)->post(route('approvals.approve-delete', $change));

        return $change;
    }

    public function test_archiving_a_household_archives_its_residents(): void
    {
        // Stage 0: the delete is only a request — nothing is archived yet.
        $this->actingAs($this->secretary)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('households.destroy', $this->household))
            ->assertRedirect(route('households.index'));

        $this->assertFalse($this->household->fresh()->trashed());
        $change = PendingChange::where('action', 'delete')->latest('id')->firstOrFail();
        $this->actingAs($this->admin)->get(route('approvals.deletes'))->assertOk();

        // Stage 1: Admin approval alone still does not archive anything.
        $this->actingAs($this->admin)->post(route('approvals.approve-delete', $change));
        $this->assertFalse($this->household->fresh()->trashed());

        // Stage 2: Super Admin final approval performs the archive.
        $this->actingAs($this->superAdmin)->get(route('approvals.deletes'))->assertOk();
        $this->actingAs($this->superAdmin)->post(route('approvals.approve-delete', $change));

        $this->assertTrue(Resident::withTrashed()->findOrFail($this->approvedResident->id)->trashed());
        $this->assertTrue(Resident::withTrashed()->findOrFail($this->pendingResident->id)->trashed());
        $this->assertSame(0, Resident::where('household_id', $this->household->id)->count());
    }

    public function test_household_edits_are_saved_only_after_both_approval_stages(): void
    {
        $this->actingAs($this->secretary);
        $change = app(\App\Services\PendingChangeService::class)->submitUpdate(
            $this->household,
            'household',
            ['address' => 'Proposed address']
        );

        $this->assertSame('pending', $change->approval_status);
        $this->assertNotSame('Proposed address', $this->household->fresh()->address);

        $this->actingAs($this->admin)->get(route('approvals.updates'))->assertOk();
        $this->actingAs($this->admin)->post(route('approvals.approve-update', $change));
        $this->assertSame('admin_approved', $change->fresh()->approval_status);
        $this->assertNotSame('Proposed address', $this->household->fresh()->address);

        $this->actingAs($this->superAdmin)->get(route('approvals.updates'))->assertOk();
        $this->actingAs($this->superAdmin)->post(route('approvals.approve-update', $change));
        $this->assertSame('approved', $change->fresh()->approval_status);
        $this->assertSame('Proposed address', $this->household->fresh()->address);
    }

    public function test_rejected_household_edit_is_never_applied(): void
    {
        $this->actingAs($this->secretary);
        $change = app(\App\Services\PendingChangeService::class)->submitUpdate(
            $this->household,
            'household',
            ['address' => 'Rejected address']
        );

        $this->actingAs($this->admin)->post(route('approvals.reject-update', $change), [
            'remarks' => 'Address needs verification.',
        ]);

        $this->assertSame('rejected', $change->fresh()->approval_status);
        $this->assertNotSame('Rejected address', $this->household->fresh()->address);
    }

    public function test_resubmitted_open_changes_leave_the_original_proposal_immutable(): void
    {
        $this->actingAs($this->secretary);
        $service = app(\App\Services\PendingChangeService::class);
        $first = $service->submitUpdate($this->household, 'household', ['address' => 'First proposal']);
        $second = $service->submitUpdate($this->household, 'household', ['address' => 'Second proposal']);

        $this->assertSame('superseded', $first->fresh()->approval_status);
        $this->assertSame('First proposal', $first->fresh()->payload['address']);
        $this->assertSame('Second proposal', $second->payload['address']);
        $this->assertNotSame($first->id, $second->id);
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

        $this->requestAndApproveHouseholdDelete($this->household);

        $this->assertFalse($otherResident->fresh()->trashed());
    }

    public function test_restoring_a_household_restores_residents_archived_with_it(): void
    {
        $this->requestAndApproveHouseholdDelete($this->household);

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

        $this->requestAndApproveHouseholdDelete($this->household);

        $this->actingAs($this->secretary)
            ->post(route('households.restore', $this->household->id));

        $this->assertFalse($this->approvedResident->fresh()->trashed());
        $this->assertTrue(Resident::withTrashed()->findOrFail($this->pendingResident->id)->trashed());
    }
}
