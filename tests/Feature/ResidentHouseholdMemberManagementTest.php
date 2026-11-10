<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Residents can edit and remove the members of their OWN household one by
 * one (the household head can never be removed).
 */
class ResidentHouseholdMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Household $household;

    private Household $otherHousehold;

    private Resident $head;

    private Resident $member;

    private Resident $foreignMember;

    private User $residentUser;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Member Village', 'village_code' => 'MBRV']);

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-MEM-01',
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Family Head',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $this->otherHousehold = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-MEM-02',
            'block_number' => '2',
            'lot_number' => '1',
            'household_head_name' => 'Other Head',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $this->head = $this->makeResident($this->household, 'Family', 'Head');
        $this->member = $this->makeResident($this->household, 'Family', 'Member');
        $this->foreignMember = $this->makeResident($this->otherHousehold, 'Other', 'Member');

        $this->head->is_household_head = true;
        $this->head->save();

        $this->household->household_head_id = $this->head->id;
        $this->household->save();

        $this->residentUser = $this->makeUser('resident', $this->household);
        $this->secretary = $this->makeUser('secretary');
    }

    private function makeResident(Household $household, string $firstName, string $lastName): Resident
    {
        return Resident::create([
            'village_id' => $household->village_id,
            'household_id' => $household->id,
            'resident_code' => 'RES-' . Str::upper(Str::random(8)),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'status' => 'active',
            'approval_status' => 'approved',
            'relationship_to_head' => $firstName === 'Family' && $lastName === 'Head' ? null : 'Child',
        ]);
    }

    private function makeUser(string $roleSlug, ?Household $household = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $household?->village_id ?? $this->village->id,
            'household_id' => $household?->id,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Member',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function validMemberPayload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Updated',
            'middle_name' => 'M',
            'last_name' => 'Member',
            'gender' => 'Male',
            'birthdate' => '1995-05-05',
            'civil_status' => 'Married',
            'contact_number' => '09170001111',
            'email' => 'member@example.com',
            'occupation' => 'Teacher',
            'relationship_to_head' => 'Child',
        ], $overrides);
    }

    public function test_my_household_page_offers_edit_and_delete_per_member_but_never_deletes_the_head(): void
    {
        $page = $this->actingAs($this->residentUser)->get(route('households.my-household'));

        $page->assertOk()
            ->assertSee(route('households.members.edit', $this->member), false)
            ->assertSee('action="' . route('households.members.destroy', $this->member) . '"', false)
            ->assertSee(route('households.members.edit', $this->head), false)
            // The head row only carries a disabled delete control, never a form.
            ->assertSee('The household head cannot be removed', false)
            ->assertDontSee('action="' . route('households.members.destroy', $this->head) . '"', false);
    }

    public function test_resident_can_open_the_edit_form_for_an_own_household_member(): void
    {
        $page = $this->actingAs($this->residentUser)->get(route('households.members.edit', $this->member));

        $page->assertOk()
            ->assertSee(route('households.members.update', $this->member), false)
            ->assertSee('Family', false)
            ->assertSee('Member', false);
    }

    public function test_resident_can_update_an_own_household_member(): void
    {
        $response = $this->actingAs($this->residentUser)
            ->put(route('households.members.update', $this->member), $this->validMemberPayload());

        $response->assertRedirect(route('households.my-household'));
        $response->assertSessionHas('success');

        $this->member->refresh();
        $this->assertSame('Updated', $this->member->first_name);
        $this->assertSame('M', $this->member->middle_name);
        $this->assertSame('Teacher', $this->member->occupation);
        $this->assertSame('Married', $this->member->civil_status);
        $this->assertSame('09170001111', $this->member->contact_number);
        $this->assertSame('member@example.com', $this->member->email);
        // The stored age always follows the edited birthdate.
        $this->assertSame(Carbon::parse('1995-05-05')->age, $this->member->age);
        // Household and village never change through this form.
        $this->assertSame($this->household->id, $this->member->household_id);
        $this->assertSame($this->village->id, $this->member->village_id);
    }

    public function test_resident_can_remove_an_own_non_head_household_member(): void
    {
        $response = $this->actingAs($this->residentUser)
            ->delete(route('households.members.destroy', $this->member));

        $response->assertRedirect(route('households.my-household'));
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('residents', ['id' => $this->member->id]);

        // The rest of the household is untouched.
        $this->assertDatabaseHas('residents', [
            'id' => $this->head->id,
            'deleted_at' => null,
        ]);

        // The remaining household accounts are notified about the removal.
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->residentUser->id,
            'title' => 'Household Member Removed',
        ]);
    }

    public function test_resident_cannot_remove_the_household_head(): void
    {
        $response = $this->actingAs($this->residentUser)
            ->delete(route('households.members.destroy', $this->head));

        $response->assertRedirect(route('households.my-household'));
        $response->assertSessionHas('error');

        $kept = Resident::withTrashed()->findOrFail($this->head->id);
        $this->assertFalse($kept->trashed());
    }

    public function test_resident_cannot_edit_or_delete_members_of_another_household(): void
    {
        $this->actingAs($this->residentUser)
            ->get(route('households.members.edit', $this->foreignMember))
            ->assertForbidden();

        $this->actingAs($this->residentUser)
            ->put(route('households.members.update', $this->foreignMember), $this->validMemberPayload())
            ->assertForbidden();

        $this->actingAs($this->residentUser)
            ->delete(route('households.members.destroy', $this->foreignMember))
            ->assertForbidden();

        $this->foreignMember->refresh();
        $this->assertSame('Other', $this->foreignMember->first_name);
    }

    public function test_only_resident_accounts_can_manage_household_members(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('households.members.edit', $this->member))
            ->assertForbidden();

        $this->actingAs($this->secretary)
            ->delete(route('households.members.destroy', $this->member))
            ->assertForbidden();

        $this->assertDatabaseHas('residents', [
            'id' => $this->member->id,
            'deleted_at' => null,
        ]);
    }
}
