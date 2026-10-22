<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
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
            'username' => 'super_' . Str::random(6),
            'email' => Str::random(8) . '@example.com',
            'password' => 'secret-password',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);
    }

    public function test_guest_can_open_the_login_page(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_authenticated_user_opening_the_login_page_goes_to_the_dashboard(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/login')
            ->assertRedirect(route('dashboard.super_admin'));
    }

    public function test_root_redirects_authenticated_users_to_their_dashboard(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/')
            ->assertRedirect(route('dashboard.super_admin'));
    }

    public function test_root_redirects_guests_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_account_with_an_unrecognized_role_is_logged_out_instead_of_looping(): void
    {
        $this->superAdmin->role->update(['role_slug' => 'retired_role']);

        $response = $this->actingAs($this->superAdmin)->get('/login');

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
