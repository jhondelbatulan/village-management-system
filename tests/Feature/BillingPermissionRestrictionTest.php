<?php

namespace Tests\Feature;

use App\Models\PaymentConfiguration;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BillingPermissionRestrictionTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $treasurer;

    private User $superAdmin;

    private User $secretary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Billing Village', 'village_code' => 'BLV']);

        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
        $this->superAdmin = $this->makeUser('super_admin');
        $this->secretary = $this->makeUser('secretary', $this->village->id);

        PaymentConfiguration::create([
            'village_id' => $this->village->id,
            'payment_enabled' => true,
            'payment_manager_role' => 'treasurer',
            'grace_period_days' => 3,
            'billing_prefix' => 'BILL',
            'receipt_prefix' => 'RCPT',
            'permit_prefix' => 'PERMIT',
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

    public function test_treasurer_can_open_payment_and_billing_generation_forms(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('payments.create'))
            ->assertOk();

        $this->actingAs($this->treasurer)
            ->get(route('billings.generate'))
            ->assertOk();
    }

    public function test_super_admin_cannot_record_payments_or_generate_billings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('payments.create'))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->post(route('payments.store'), [
                'billing_id' => 1,
                'amount' => 100,
                'payment_method' => 'cash',
            ])
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->get(route('billings.generate'))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->post(route('billings.generate.store'), [])
            ->assertForbidden();
    }

    public function test_super_admin_can_still_view_payments_and_billings(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('payments.index'))
            ->assertOk()
            ->assertDontSee('Record Payment', false);

        $this->actingAs($this->superAdmin)
            ->get(route('billings.index'))
            ->assertOk()
            ->assertDontSee('Generate Billings', false);
    }

    public function test_secretary_cannot_record_payments_or_generate_billings(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('payments.create'))
            ->assertForbidden();

        $this->actingAs($this->secretary)
            ->get(route('billings.generate'))
            ->assertForbidden();
    }

    public function test_treasurer_from_another_village_cannot_record_payments(): void
    {
        $otherVillage = Village::forceCreate(['village_name' => 'Other Billing Village', 'village_code' => 'OBV']);
        $foreignTreasurer = $this->makeUser('treasurer', $otherVillage->id);

        PaymentConfiguration::create([
            'village_id' => $otherVillage->id,
            'payment_enabled' => true,
            'payment_manager_role' => 'treasurer',
            'grace_period_days' => 3,
        ]);

        $this->actingAs($foreignTreasurer)
            ->get(route('payments.create'))
            ->assertOk();

        $foreignManager = $foreignTreasurer->fresh();
        $this->assertFalse(
            app(\App\Services\PaymentPermissionService::class)
                ->canRecordPayment($foreignManager, $this->village->id)
        );
    }

    public function test_super_admin_cannot_manage_household_amounts(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('household-amounts.index', $this->village))
            ->assertForbidden();

        $this->actingAs($this->treasurer)
            ->get(route('household-amounts.index', $this->village))
            ->assertOk();
    }
}
