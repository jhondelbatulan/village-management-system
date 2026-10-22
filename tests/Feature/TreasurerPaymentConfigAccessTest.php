<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Services\MenuService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TreasurerPaymentConfigAccessTest extends TestCase
{
    use RefreshDatabase;

    private Village $ownVillage;

    private Village $otherVillage;

    private User $treasurer;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->ownVillage = Village::forceCreate(['village_name' => 'Own Village', 'village_code' => 'OWV']);
        $this->otherVillage = Village::forceCreate(['village_name' => 'Other Village', 'village_code' => 'OTV']);

        $this->treasurer = $this->makeUser('treasurer', $this->ownVillage->id);
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

    private function configPayload(array $overrides = []): array
    {
        return array_merge([
            'payment_enabled' => '1',
            'payment_manager_role' => 'treasurer',
            'grace_period_days' => 3,
            'billing_prefix' => 'BILL',
            'receipt_prefix' => 'RCPT',
            'permit_prefix' => 'PERMIT',
        ], $overrides);
    }

    public function test_treasurer_can_open_the_payment_config_page_for_their_own_village(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('payment-config.edit', $this->ownVillage))
            ->assertOk();

        $this->actingAs($this->treasurer)
            ->get(route('payment-config.index'))
            ->assertOk()
            ->assertSee('Own Village', false)
            ->assertDontSee('Other Village', false);
    }

    public function test_treasurer_cannot_access_payment_config_for_another_village(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('payment-config.edit', $this->otherVillage))
            ->assertForbidden();

        $this->actingAs($this->treasurer)
            ->get(route('payment-config.show', $this->otherVillage))
            ->assertForbidden();

        $this->actingAs($this->treasurer)
            ->put(route('payment-config.update', $this->otherVillage), $this->configPayload())
            ->assertForbidden();
    }

    public function test_treasurer_can_update_payment_config_for_their_own_village(): void
    {
        $this->actingAs($this->treasurer)
            ->put(route('payment-config.update', $this->ownVillage), $this->configPayload())
            ->assertRedirect(route('payment-config.show', $this->ownVillage))
            ->assertSessionHas('success');
    }

    public function test_treasurer_can_add_a_custom_payment_type_for_their_own_village_only(): void
    {
        $this->actingAs($this->treasurer)
            ->post(route('payment-config.store-type'), [
                'village_id' => $this->ownVillage->id,
                'payment_name' => 'Water Dues',
                'frequency' => 'monthly',
                'default_amount' => 150,
            ])
            ->assertRedirect(route('payment-config.show', $this->ownVillage))
            ->assertSessionHas('success');

        $this->actingAs($this->treasurer)
            ->post(route('payment-config.store-type'), [
                'village_id' => $this->otherVillage->id,
                'payment_name' => 'Water Dues',
                'frequency' => 'monthly',
                'default_amount' => 150,
            ])
            ->assertForbidden();
    }

    public function test_secretary_cannot_access_payment_config_routes(): void
    {
        $role = Role::where('role_slug', 'secretary')->firstOrFail();

        $secretary = User::create([
            'role_id' => $role->id,
            'village_id' => $this->ownVillage->id,
            'username' => 'sec_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'Secretary',
            'status' => 'active',
        ]);

        $this->actingAs($secretary)
            ->get(route('payment-config.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_view_but_not_manage_payment_config(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('payment-config.index'))
            ->assertOk()
            ->assertSee('Other Village', false);

        $this->actingAs($this->superAdmin)
            ->get(route('payment-config.show', $this->otherVillage))
            ->assertOk();

        $this->actingAs($this->superAdmin)
            ->get(route('payment-config.edit', $this->otherVillage))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->put(route('payment-config.update', $this->otherVillage), $this->configPayload())
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->post(route('payment-config.store-type'), [
                'village_id' => $this->otherVillage->id,
                'payment_name' => 'Water Dues',
                'frequency' => 'monthly',
                'default_amount' => 150,
            ])
            ->assertForbidden();
    }

    public function test_treasurer_menu_contains_payment_config_announcements_and_events(): void
    {
        $items = app(MenuService::class)->getMenuForRole('treasurer', $this->treasurer);
        $routes = array_column($items, 'route');

        $this->assertContains('announcements.index', $routes);
        $this->assertContains('events.index', $routes);

        $configItem = collect($items)->firstWhere('route', 'payment-config.edit');
        $this->assertNotNull($configItem);
        $this->assertSame('Payment Config', $configItem['label']);
        $this->assertSame(['village' => $this->ownVillage->id], $configItem['route_params']);
    }

    public function test_treasurer_dashboard_renders_with_the_new_menu_entries(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('dashboard.treasurer'))
            ->assertOk()
            ->assertSee('Payment Config', false)
            ->assertSee('Announcements', false)
            ->assertSee('Events', false);
    }
}
