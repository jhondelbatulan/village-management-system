<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Services\PaymentService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MonthlyDuesDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Village $otherVillage;

    private User $treasurer;

    private User $admin;

    private Household $householdA;

    private Household $householdB;

    private PaymentType $monthlyDues;

    private PaymentType $annualDues;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Comagascas', 'village_code' => 'CMG']);
        $this->otherVillage = Village::forceCreate(['village_name' => 'Other Village', 'village_code' => 'OTH']);

        $role = Role::where('role_slug', 'treasurer')->firstOrFail();

        $this->treasurer = User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'username' => 'treas_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'Treasurer',
            'status' => 'active',
        ]);

        $adminRole = Role::where('role_slug', 'admin')->firstOrFail();

        $this->admin = User::create([
            'role_id' => $adminRole->id,
            'village_id' => $this->village->id,
            'username' => 'admin_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'President',
            'status' => 'active',
        ]);

        $this->householdA = $this->makeHousehold($this->village, '1', '1');
        $this->householdB = $this->makeHousehold($this->village, '1', '2');
        $otherHousehold = $this->makeHousehold($this->otherVillage, '1', '1');

        $this->monthlyDues = $this->makePaymentType($this->village, 'Monthly Dues', 'monthly');
        $this->annualDues = $this->makePaymentType($this->village, 'Annual Dues', 'annual');
        $otherVillageMonthly = $this->makePaymentType($this->otherVillage, 'Monthly Dues', 'monthly');

        // This month - monthly dues: 800 fully paid (counts), 500 billed / 200 paid partial (does not count)
        $this->pay($this->village, $this->householdA, $this->monthlyDues, 800, 800, now()->toDateString(), now()->format('Y-m'));
        $this->pay($this->village, $this->householdB, $this->monthlyDues, 500, 200, now()->toDateString(), now()->format('Y-m'));

        // This month - annual dues (must not be counted as monthly dues): 1,200
        $this->pay($this->village, $this->householdA, $this->annualDues, 1200, 1200, now()->toDateString(), now()->format('Y-m'));

        // Last month - monthly dues (must not be counted): 700
        $this->pay(
            $this->village,
            $this->householdA,
            $this->monthlyDues,
            700,
            700,
            now()->subMonth()->toDateString(),
            now()->subMonth()->format('Y-m')
        );

        // Another village - monthly dues (must not be counted): 1,000
        $this->pay(
            $this->otherVillage,
            $otherHousehold,
            $otherVillageMonthly,
            1000,
            1000,
            now()->toDateString(),
            now()->format('Y-m')
        );
    }

    private function makeHousehold(Village $village, string $block, string $lot): Household
    {
        return Household::create([
            'village_id' => $village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => $block,
            'lot_number' => $lot,
            'household_head_name' => 'Head ' . $block . $lot,
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->treasurer->id,
        ]);
    }

    private function makePaymentType(Village $village, string $name, string $frequency): PaymentType
    {
        return PaymentType::create([
            'village_id' => $village->id,
            'payment_name' => $name,
            'frequency' => $frequency,
            'default_amount' => 500,
            'is_active' => true,
        ]);
    }

    private function pay(
        Village $village,
        Household $household,
        PaymentType $type,
        float $billingAmount,
        float $paid,
        string $date,
        string $period
    ): HouseholdPayment {
        $billing = Billing::create([
            'billing_number' => 'BILL-' . Str::upper(Str::random(6)),
            'household_id' => $household->id,
            'village_id' => $village->id,
            'payment_type_id' => $type->id,
            'billing_period' => $period,
            'due_date' => $date,
            'amount' => $billingAmount,
            'penalty' => 0,
            'discount' => 0,
            'total_amount' => $billingAmount,
            'status' => $paid >= $billingAmount ? 'paid' : 'partially_paid',
            'created_by' => $this->treasurer->id,
        ]);

        return HouseholdPayment::create([
            'receipt_number' => 'RCPT-' . Str::upper(Str::random(6)),
            'billing_id' => $billing->id,
            'household_id' => $household->id,
            'village_id' => $village->id,
            'payment_type_id' => $type->id,
            'amount' => $billingAmount,
            'penalty' => 0,
            'discount' => 0,
            'total_paid' => $paid,
            'payment_date' => $date,
            'due_date' => $date,
            'payment_method' => 'cash',
            'payment_status' => $paid >= $billingAmount ? 'paid' : 'partially_paid',
            'received_by' => $this->treasurer->id,
        ]);
    }

    public function test_service_totals_only_this_months_fully_paid_monthly_dues_payments(): void
    {
        $stats = (new PaymentService)->getMonthlyDuesCollections($this->village->id);

        $this->assertEqualsWithDelta(800.0, $stats['collected'], 0.01);
        $this->assertEqualsWithDelta(1300.0, $stats['billed'], 0.01);
        $this->assertEqualsWithDelta(500.0, $stats['outstanding'], 0.01);
        $this->assertSame(1, $stats['payment_count']);
        $this->assertSame(1, $stats['households_paid']);
        $this->assertSame(now()->format('F Y'), $stats['label']);
        $this->assertEqualsWithDelta(14.3, $stats['change'], 0.01);
    }

    public function test_treasurer_dashboard_shows_the_monthly_dues_collected_card(): void
    {
        $response = $this->actingAs($this->treasurer)->get(route('dashboard.treasurer'));

        $response->assertOk()
            ->assertSee('Monthly Dues Collected')
            ->assertSee('₱800.00')
            ->assertDontSee('₱1,000.00')
            ->assertDontSee('₱1,700.00');
    }

    public function test_admin_dashboard_shows_the_monthly_dues_collected_card(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard.admin'));

        $response->assertOk()
            ->assertSee('Monthly Dues Collected')
            ->assertSee('₱800.00')
            ->assertDontSee('₱1,000.00')
            ->assertDontSee('₱1,700.00');
    }

    public function test_service_returns_zero_when_the_village_has_no_monthly_payment_types(): void
    {
        $village = Village::forceCreate(['village_name' => 'No Dues Village', 'village_code' => 'NDV']);

        $stats = (new PaymentService)->getMonthlyDuesCollections($village->id);

        $this->assertEqualsWithDelta(0.0, $stats['collected'], 0.01);
        $this->assertSame(0, $stats['payment_count']);
    }
}
