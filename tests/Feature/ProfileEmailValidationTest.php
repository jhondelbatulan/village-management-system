<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEmailValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        $this->user = User::create([
            'role_id' => $role->id,
            'username' => 'profileuser',
            'email' => 'profileuser@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Profile',
            'last_name' => 'User',
            'status' => 'active',
        ]);
    }

    public function test_edit_profile_page_renders(): void
    {
        $this->actingAs($this->user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('profileuser@gmail.com');
    }

    public function test_saving_profile_changes_with_an_email_succeeds(): void
    {
        // Guards the save path: the email rule must not reach for DNS, which
        // is unavailable on the deployed (WASM) PHP runtime.
        $this->actingAs($this->user)->put(route('profile.update'), [
            'username' => 'profileuser',
            'first_name' => 'Updated',
            'middle_name' => '',
            'last_name' => 'User',
            'suffix' => '',
            'email' => 'profileuser@gmail.com',
            'contact_number' => '09171234567',
        ])->assertRedirect(route('profile.index'));

        $this->user->refresh();
        $this->assertSame('Updated', $this->user->first_name);
        $this->assertSame('profileuser@gmail.com', $this->user->email);
    }

    public function test_saving_profile_with_an_invalid_email_is_rejected(): void
    {
        $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'username' => 'profileuser',
                'first_name' => 'Profile',
                'last_name' => 'User',
                'email' => 'not-an-email',
            ])
            ->assertSessionHasErrors(['email']);

        $this->assertSame('profileuser@gmail.com', $this->user->fresh()->email);
    }

    public function test_saving_profile_with_a_duplicate_email_is_rejected(): void
    {
        $otherRole = Role::where('role_slug', 'admin')->firstOrFail();

        User::create([
            'role_id' => $otherRole->id,
            'username' => 'someoneelse',
            'email' => 'someoneelse@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Some',
            'last_name' => 'Else',
            'status' => 'active',
        ]);

        $this->actingAs($this->user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'username' => 'profileuser',
                'first_name' => 'Profile',
                'last_name' => 'User',
                'email' => 'someoneelse@gmail.com',
            ])
            ->assertSessionHasErrors(['email']);
    }
}
