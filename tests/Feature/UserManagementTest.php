<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->superAdmin = $this->makeUser('super_admin');
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

    public function test_super_admin_can_deactivate_an_account_without_error(): void
    {
        $village = Village::forceCreate(['village_name' => 'Deactivate Village', 'village_code' => 'DV']);
        $target = $this->makeUser('treasurer', $village->id);

        $originalRememberToken = $target->remember_token;

        $response = $this->actingAs($this->superAdmin)
            ->patch(route('users.toggle-status', $target));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $target->refresh();

        $this->assertSame('inactive', $target->status);
        $this->assertNotSame($originalRememberToken, $target->remember_token);
    }

    public function test_super_admin_can_delete_an_account_without_error(): void
    {
        $village = Village::forceCreate(['village_name' => 'Delete Village', 'village_code' => 'DLT']);
        $target = $this->makeUser('secretary', $village->id);

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('users.destroy', $target));

        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_admin_can_deactivate_users_in_their_village(): void
    {
        $village = Village::forceCreate(['village_name' => 'Admin Village', 'village_code' => 'ADM']);
        $admin = $this->makeUser('admin', $village->id);
        $target = $this->makeUser('resident', $village->id);

        $response = $this->actingAs($admin)
            ->patch(route('users.toggle-status', $target));

        $response->assertRedirect(route('users.index'));

        $this->assertSame('inactive', $target->fresh()->status);
    }

    public function test_user_cannot_deactivate_own_account(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->patch(route('users.toggle-status', $this->superAdmin));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame('active', $this->superAdmin->fresh()->status);
    }

    public function test_super_admin_can_view_the_create_user_form(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('users.create'))
            ->assertOk();
    }

    public function test_create_user_rejects_numbers_and_special_characters_in_names(): void
    {
        $payload = $this->createUserPayload([
            'first_name' => 'Juan1',
            'last_name' => 'Dela-Cruz!',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), $payload);

        $response->assertSessionHasErrors(['first_name', 'last_name']);
        $this->assertDatabaseMissing('users', ['username' => $payload['username']]);
    }

    public function test_create_user_rejects_invalid_email(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(
            route('users.store'),
            $this->createUserPayload(['email' => 'not-an-email'])
        );

        $response->assertSessionHasErrors(['email']);
    }

    public function test_create_user_rejects_invalid_middle_name_and_suffix(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(
            route('users.store'),
            $this->createUserPayload([
                'middle_name' => 'Maria',
                'suffix' => 'Senior',
            ])
        );

        $response->assertSessionHasErrors(['middle_name', 'suffix']);
    }

    public function test_create_user_normalizes_personal_information(): void
    {
        $payload = $this->createUserPayload([
            'first_name' => '  juan   dela ',
            'middle_name' => 'm',
            'last_name' => 'dela cruz',
            'suffix' => 'jr',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $user = User::where('username', $payload['username'])->firstOrFail();

        $this->assertSame('Juan Dela', $user->first_name);
        $this->assertSame('M', $user->middle_name);
        $this->assertSame('Dela Cruz', $user->last_name);
        $this->assertSame('Jr.', $user->suffix);
    }

    public function test_create_user_succeeds_with_contact_number_only(): void
    {
        $payload = $this->createUserPayload([
            'email' => null,
            'contact_number' => '09171234567',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $user = User::where('username', $payload['username'])->firstOrFail();

        $this->assertNull($user->email);
        $this->assertSame('09171234567', $user->contact_number);
    }

    public function test_create_user_succeeds_without_email_and_contact_number(): void
    {
        $payload = $this->createUserPayload([
            'first_name' => null,
            'last_name' => null,
            'email' => '',
            'contact_number' => '',
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));

        $user = User::where('username', $payload['username'])->firstOrFail();

        $this->assertNull($user->email);
        $this->assertNull($user->contact_number);
        $this->assertNull($user->first_name);
        $this->assertNull($user->last_name);
    }

    public function test_second_admin_account_in_the_same_village_is_rejected(): void
    {
        $village = Village::forceCreate(['village_name' => 'Single Admin Village', 'village_code' => 'SAV']);

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), $this->createUserPayload(['village_id' => $village->id]))
            ->assertSessionHasNoErrors();

        $payload = $this->createUserPayload(['village_id' => $village->id]);

        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), $payload);

        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('users', ['username' => $payload['username']]);
    }

    public function test_same_staff_role_can_be_created_in_a_different_village(): void
    {
        $villageA = Village::forceCreate(['village_name' => 'Staff Village A', 'village_code' => 'SVA']);
        $villageB = Village::forceCreate(['village_name' => 'Staff Village B', 'village_code' => 'SVB']);

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), $this->createUserPayload(['village_id' => $villageA->id]))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), $this->createUserPayload(['village_id' => $villageB->id]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));
    }

    public function test_resident_accounts_are_not_limited_per_village(): void
    {
        $village = Village::forceCreate(['village_name' => 'Resident Limit Village', 'village_code' => 'RLV']);
        $residentRole = Role::where('role_slug', 'resident')->firstOrFail();

        $householdA = Household::create([
            'village_id' => $village->id,
            'household_number' => 'HH-A' . Str::upper(Str::random(4)),
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $householdB = Household::create([
            'village_id' => $village->id,
            'household_number' => 'HH-B' . Str::upper(Str::random(4)),
            'block_number' => '1',
            'lot_number' => '2',
            'household_head_name' => 'Pedro Santos',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        foreach ([$householdA, $householdB] as $index => $household) {
            $response = $this->actingAs($this->superAdmin)->post(route('users.store'), [
                'role_id' => $residentRole->id,
                'village_id' => $village->id,
                'household_id' => $household->id,
                'username' => 'resident_' . $index . '_' . Str::random(6),
                'email' => Str::random(8) . '@gmail.com',
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'status' => 'active',
            ]);

            $response->assertSessionHasNoErrors();
        }
    }

    public function test_update_user_role_to_existing_secretary_in_same_village_is_rejected(): void
    {
        $village = Village::forceCreate(['village_name' => 'Role Limit Village', 'village_code' => 'RML']);
        $this->makeUser('secretary', $village->id);

        $target = $this->makeUser('resident', $village->id);
        $secretaryRole = Role::where('role_slug', 'secretary')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)->put(route('users.update', $target), [
            'role_id' => $secretaryRole->id,
            'village_id' => $village->id,
            'username' => $target->username,
            'email' => Str::random(8) . '@gmail.com',
            'first_name' => 'Test',
            'last_name' => 'Resident',
            'status' => 'active',
        ]);

        $response->assertSessionHasErrors('role_id');
    }

    public function test_updating_a_secretary_to_the_same_role_in_the_same_village_is_allowed(): void
    {
        $village = Village::forceCreate(['village_name' => 'Own Slot Village', 'village_code' => 'OSV']);
        $secretary = $this->makeUser('secretary', $village->id);
        $secretaryRole = Role::where('role_slug', 'secretary')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)->put(route('users.update', $secretary), [
            'role_id' => $secretaryRole->id,
            'village_id' => $village->id,
            'username' => $secretary->username,
            'email' => Str::random(8) . '@gmail.com',
            'first_name' => 'Test',
            'last_name' => 'Secretary',
            'status' => 'active',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('users.index'));
    }

    public function test_update_user_can_clear_names_email_and_contact(): void
    {
        $village = Village::forceCreate(['village_name' => 'Clearable Village', 'village_code' => 'CLV']);
        $target = $this->makeUser('admin', $village->id);
        $adminRole = Role::where('role_slug', 'admin')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)->put(route('users.update', $target), [
            'role_id' => $adminRole->id,
            'village_id' => $village->id,
            'username' => $target->username,
            'email' => '',
            'contact_number' => '',
            'first_name' => null,
            'last_name' => null,
            'status' => 'active',
        ]);

        $response->assertSessionHasNoErrors();

        $target->refresh();

        $this->assertNull($target->first_name);
        $this->assertNull($target->last_name);
        $this->assertNull($target->email);
        $this->assertNull($target->contact_number);
        $this->assertSame($target->username, $target->full_name);
    }

    public function test_create_form_marks_names_email_and_phone_as_optional(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.create'));

        $response->assertOk();
        $response->assertDontSee('First Name <span class="text-danger">*</span>', false);
        $response->assertDontSee('Last Name <span class="text-danger">*</span>', false);
        $response->assertDontSee('Provide an email address or a contact number.', false);
    }

    private function createUserPayload(array $overrides = []): array
    {
        $role = Role::where('role_slug', 'admin')->firstOrFail();
        $village = Village::firstOrCreate(
            ['village_code' => 'CVR'],
            ['village_name' => 'Create Village']
        );

        return array_merge([
            'role_id' => $role->id,
            'village_id' => $village->id,
            'status' => 'active',
            'username' => 'admin_' . Str::random(6),
            'email' => Str::random(8) . '@gmail.com',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $overrides);
    }
}
