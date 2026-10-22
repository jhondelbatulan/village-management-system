<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminMonthlyCollectionTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $superAdmin;

    private User $treasurer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Alpha Village', 'village_code' => 'ALP']);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);

        $household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Head One',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->treasurer->id,
        ]);

        $paymentType = PaymentType::create([
            'village_id' => $this->village->id,
            'payment_name' => 'Monthly Dues',
            'frequency' => 'monthly',
            'default_amount' => 500,
            'is_active' => true,
        ]);

        // This month: 500 paid + 999 cancelled (must not count anywhere).
        $this->pay($household, $paymentType, 500, now()->toDateString(), 'paid');
        $this->pay($household, $paymentType, 999, now()->toDateString(), 'cancelled');

        // Last month: 300 paid. Two months ago: 200 paid.
        $this->pay($household, $paymentType, 300, now()->subMonth()->toDateString(), 'paid');
        $this->pay($household, $paymentType, 200, now()->subMonths(2)->toDateString(), 'paid');
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

    private function pay(Household $household, PaymentType $paymentType, float $amount, string $date, string $status): HouseholdPayment
    {
        $billing = Billing::create([
            'billing_number' => 'BILL-' . Str::upper(Str::random(6)),
            'household_id' => $household->id,
            'village_id' => $this->village->id,
            'payment_type_id' => $paymentType->id,
            'billing_period' => Carbon::parse($date)->format('Y-m'),
            'due_date' => $date,
            'amount' => $amount,
            'penalty' => 0,
            'discount' => 0,
            'total_amount' => $amount,
            'status' => $status === 'cancelled' ? 'pending' : 'paid',
            'created_by' => $this->treasurer->id,
        ]);

        return HouseholdPayment::create([
            'receipt_number' => 'RCPT-' . Str::upper(Str::random(6)),
            'billing_id' => $billing->id,
            'household_id' => $household->id,
            'village_id' => $this->village->id,
            'payment_type_id' => $paymentType->id,
            'amount' => $amount,
            'penalty' => 0,
            'discount' => 0,
            'total_paid' => $amount,
            'payment_date' => $date,
            'due_date' => $date,
            'payment_method' => 'cash',
            'payment_status' => $status,
            'received_by' => $this->treasurer->id,
        ]);
    }

    public function test_super_admin_dashboard_card_shows_this_month_collections_only(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard.super_admin'));

        $response->assertOk()
            ->assertViewHas('paymentStats', function (array $stats): bool {
                return abs($stats['this_month_collected'] - 500.0) < 0.01
                    && abs($stats['previous_month_collected'] - 300.0) < 0.01
                    && abs($stats['total_collected'] - 1000.0) < 0.01;
            })
            ->assertSee(route('dashboard.super_admin.monthly-collection'), false);
    }

    public function test_monthly_collection_page_breaks_down_collections_per_village(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('dashboard.super_admin.monthly-collection'))
            ->assertOk()
            ->assertSee('Alpha Village', false)
            ->assertSee('₱500.00', false)
            ->assertSee('₱300.00', false)
            ->assertSee('₱1,000.00', false)
            ->assertDontSee('₱999.00', false);
    }

    public function test_treasurer_cannot_access_monthly_collection_page(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('dashboard.super_admin.monthly-collection'))
            ->assertForbidden();
    }
}
