<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Household;
use App\Models\PaymentConfiguration;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class BillingStatusAndCarryForwardTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $treasurer;

    private User $resident;

    private Household $household;

    private PaymentType $monthlyDues;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Comagascas', 'village_code' => 'CMG']);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '4',
            'lot_number' => '9',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->treasurer->id,
        ]);

        $this->resident = $this->makeUser('resident', $this->village->id, $this->household);

        PaymentConfiguration::create([
            'village_id' => $this->village->id,
            'payment_enabled' => true,
            'payment_manager_role' => 'treasurer',
            'billing_prefix' => 'BILL',
            'monthly_due_day' => 15,
        ]);

        $this->monthlyDues = PaymentType::create([
            'village_id' => $this->village->id,
            'payment_name' => 'Monthly Dues',
            'frequency' => 'monthly',
            'default_amount' => 500,
            'is_active' => true,
        ]);
    }

    private function makeUser(string $roleSlug, ?int $villageId = null, ?Household $household = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $villageId,
            'household_id' => $household?->id,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function makeBilling(array $overrides = []): Billing
    {
        return Billing::create(array_merge([
            'billing_number' => 'BL-' . Str::upper(Str::random(6)),
            'household_id' => $this->household->id,
            'village_id' => $this->village->id,
            'payment_type_id' => $this->monthlyDues->id,
            'billing_period' => now()->subMonth()->format('Y-m'),
            'due_date' => now()->subWeek()->toDateString(),
            'amount' => 1000,
            'penalty' => 0,
            'discount' => 0,
            'total_amount' => 1000,
            'status' => 'pending',
            'created_by' => $this->treasurer->id,
        ], $overrides));
    }

    private function recordPayment(Billing $billing, float $amount): void
    {
        $this->actingAs($this->treasurer)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('payments.store'), [
                'billing_id' => $billing->id,
                'amount' => $amount,
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('billings.show', $billing));
    }

    public function test_partial_payment_marks_the_billing_as_partially_paid_with_a_remaining_balance(): void
    {
        $billing = $this->makeBilling();

        $this->recordPayment($billing, 400);

        $billing->refresh();
        $this->assertSame('partially_paid', $billing->status);
        $this->assertSame('Partially Paid', $billing->status_label);

        // The billings table shows the remaining balance next to the amount.
        $this->actingAs($this->treasurer)
            ->get(route('billings.index'))
            ->assertOk()
            ->assertSee('Remaining Balance')
            ->assertSee('Partially Paid')
            ->assertSee('600.00');
    }

    public function test_full_payment_marks_the_billing_as_fully_paid(): void
    {
        $billing = $this->makeBilling();

        $this->recordPayment($billing, 1000);

        $billing->refresh();
        $this->assertSame('paid', $billing->status);
        $this->assertSame('Fully Paid', $billing->status_label);

        $this->actingAs($this->treasurer)
            ->get(route('billings.index'))
            ->assertOk()
            ->assertSee('Fully Paid')
            ->assertSee('0.00');
    }

    public function test_unpaid_remainder_is_carried_forward_into_the_next_billing_release(): void
    {
        $billing = $this->makeBilling();

        // ₱400 of the ₱1,000 bill is paid; ₱600 remains after the due date.
        $this->recordPayment($billing, 400);
        $this->assertSame('partially_paid', $billing->refresh()->status);

        $nextPeriod = now()->addMonth()->format('Y-m');

        $this->actingAs($this->treasurer)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('billings.generate.store'), [
                'village_id' => $this->village->id,
                'billing_period' => $nextPeriod,
                'payment_type_ids' => [$this->monthlyDues->id],
            ])
            ->assertRedirect(route('billings.index'));

        // The same billing row rolls forward: no duplicate is created.
        $this->assertSame(1, Billing::count());

        $billing->refresh();

        $this->assertSame($nextPeriod, $billing->billing_period);
        // Back to unpaid, due next month.
        $this->assertSame('pending', $billing->status);
        $this->assertSame('Unpaid', $billing->status_label);

        // Remaining balance (₱600) + new amount (₱500) = ₱1,100.
        $this->assertEqualsWithDelta(1500.0, (float) $billing->total_amount, 0.01);

        $remaining = (float) $billing->total_amount - (float) $billing->payments()->sum('total_paid');
        $this->assertEqualsWithDelta(1100.0, $remaining, 0.01);
    }

    public function test_resident_payment_page_shows_remaining_balance_instead_of_total(): void
    {
        $billing = $this->makeBilling();
        $this->recordPayment($billing, 400);

        $this->actingAs($this->resident)
            ->get(route('household-payments.show', $this->household->id))
            ->assertOk()
            ->assertSee('Remaining Balance')
            ->assertSee('600.00')
            ->assertDontSee('>Total<', false);
    }
}
