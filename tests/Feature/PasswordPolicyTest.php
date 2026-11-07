<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        $this->superAdmin = User::create([
            'role_id' => $role->id,
            'username' => 'policyadmin',
            'email' => 'policyadmin@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Policy',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);
    }

    private function changePassword(string $current, string $new): mixed
    {
        return $this->actingAs($this->superAdmin)
            ->from(route('profile.change-password'))
            ->put(route('profile.change-password.update'), [
                'current_password' => $current,
                'password' => $new,
                'password_confirmation' => $new,
            ]);
    }

    public function test_profile_change_rejects_password_without_uppercase(): void
    {
        // Default policy requires an uppercase letter.
        $response = $this->changePassword('Password123', 'alllowercase1');

        $response->assertSessionHasErrors('password');
    }

    public function test_profile_change_rejects_too_short_password(): void
    {
        $response = $this->changePassword('Password123', 'Ab1');

        $response->assertSessionHasErrors('password');
    }

    public function test_profile_change_accepts_policy_compliant_password(): void
    {
        $response = $this->changePassword('Password123', 'NewStrongPass1');

        $response->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewStrongPass1', $this->superAdmin->fresh()->password));
    }

    public function test_profile_change_rejects_reused_recent_password(): void
    {
        // Establish history: Password123 (initial), then Alpha123456789, then try Beta.
        $this->changePassword('Password123', 'Alpha123456789');
        $this->changePassword('Alpha123456789', 'Beta1234567890');

        // Reusing Alpha123456789 (a recent password) must be rejected.
        $response = $this->changePassword('Beta1234567890', 'Alpha123456789');

        $response->assertSessionHasErrors('password');
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $response = $this->changePassword('NotMyPassword1', 'NewStrongPass1');

        $response->assertSessionHasErrors('current_password');
    }

    public function test_admin_update_enforces_policy_on_target_user(): void
    {
        $village = Village::create(['village_name' => 'Policy Village', 'village_code' => 'PV-001', 'status' => 'active']);
        $secretaryRole = Role::where('role_slug', 'secretary')->firstOrFail();

        $secretary = User::create([
            'role_id' => $secretaryRole->id,
            'village_id' => $village->id,
            'username' => 'policysecretary',
            'email' => 'policysecretary@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Policy',
            'last_name' => 'Secretary',
            'status' => 'active',
        ]);

        // Weak password (no uppercase) must be rejected.
        $response = $this->actingAs($this->superAdmin)
            ->from(route('users.edit', $secretary->id))
            ->put(route('users.update', $secretary->id), [
                'role_id' => $secretaryRole->id,
                'village_id' => $village->id,
                'username' => $secretary->username,
                'email' => $secretary->email,
                'first_name' => 'Policy',
                'last_name' => 'Secretary',
                'password' => 'alllowercase1',
                'password_confirmation' => 'alllowercase1',
                'status' => 'active',
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('Password123', $secretary->fresh()->password));

        // Compliant password must be accepted.
        $response = $this->actingAs($this->superAdmin)
            ->from(route('users.edit', $secretary->id))
            ->put(route('users.update', $secretary->id), [
                'role_id' => $secretaryRole->id,
                'village_id' => $village->id,
                'username' => $secretary->username,
                'email' => $secretary->email,
                'first_name' => 'Policy',
                'last_name' => 'Secretary',
                'password' => 'NewStrongPass1',
                'password_confirmation' => 'NewStrongPass1',
                'status' => 'active',
            ]);

        $response->assertSessionHas('success');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewStrongPass1', $secretary->fresh()->password));
    }
}
