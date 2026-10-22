<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NavigationRoleLabelTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Label Village', 'village_code' => 'LBV']);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
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

    public function test_secretary_sidebar_shows_the_village_name_and_village_secretary_labels(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Label Village Secretary')
            ->assertSee('Village Secretary')
            ->assertDontSee('Barangay Secretary');
    }

    public function test_treasurer_sidebar_shows_the_village_name_and_village_treasurer_labels(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Label Village Treasurer')
            ->assertSee('Village Treasurer')
            ->assertDontSee('Barangay Treasurer');
    }

    public function test_other_roles_keep_their_plain_role_name_label(): void
    {
        $this->actingAs($this->admin)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Administrator')
            ->assertDontSee('Village Secretary')
            ->assertDontSee('Village Treasurer');

        $this->actingAs($this->superAdmin)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('Super Administrator')
            ->assertDontSee('Village Secretary')
            ->assertDontSee('Village Treasurer');
    }
}
