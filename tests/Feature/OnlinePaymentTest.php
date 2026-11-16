<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\GcashConfig;
use App\Models\Household;
use App\Models\HouseholdPayment;
use App\Models\OnlinePayment;
use App\Models\PaymentConfiguration;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class OnlinePaymentTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Village $otherVillage;

    private User $treasurer;

    private User $foreignTreasurer;

    private User $resident;

    private User $otherResident;

    private Household $household;

    private Billing $billing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'GCash Village', 'village_code' => 'GCV']);
        $this->otherVillage = Village::forceCreate(['village_name' => 'Other Village', 'village_code' => 'OTV']);

        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
        $this->foreignTreasurer = $this->makeUser('treasurer', $this->otherVillage->id);

        PaymentConfiguration::create([
            'village_id' => $this->village->id,
            'payment_enabled' => true,
            'payment_manager_role' => 'treasurer',
        ]);

        GcashConfig::create([
            'village_id' => $this->village->id,
            'gcash_number' => '09171234567',
            'gcash_name' => 'JUAN DELA CRUZ',
        ]);

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->treasurer->id,
        ]);

        $this->resident = $this->makeUser('resident', $this->village->id, $this->household->id);

        $otherHousehold = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '2',
            'lot_number' => '9',
            'household_head_name' => 'Pedro Santos',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->treasurer->id,
        ]);

        $this->otherResident = $this->makeUser('resident', $this->village->id, $otherHousehold->id);

        $paymentType = PaymentType::create([
            'village_id' => $this->village->id,
            'payment_name' => 'Monthly Dues',
            'frequency' => 'monthly',
            'default_amount' => 1000,
            'is_active' => true,
        ]);

        $this->billing = Billing::create([
            'billing_number' => 'BILL-202610-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT),
            'household_id' => $this->household->id,
            'village_id' => $this->village->id,
            'payment_type_id' => $paymentType->id,
            'billing_period' => '2026-10',
            'due_date' => now()->addWeek()->toDateString(),
            'amount' => 1000,
            'penalty' => 0,
            'discount' => 0,
            'total_amount' => 1000,
            'status' => 'pending',
            'created_by' => $this->treasurer->id,
        ]);
    }

    private function makeUser(string $roleSlug, ?int $villageId = null, ?int $householdId = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $villageId,
            'household_id' => $householdId,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function submitOnlinePayment(array $overrides = [])
    {
        $payload = array_merge([
            'amount' => 1000,
            'transaction_number' => 'GCASH123456789',
            'proof' => UploadedFile::fake()->create('proof.png', 500, 'image/png'),
        ], $overrides);

        return $this->actingAs($this->resident)
            ->post(route('billings.pay-online.store', $this->billing), $payload);
    }

    /*
    |--------------------------------------------------------------------------
    | GCash configuration
    |--------------------------------------------------------------------------
    */

    public function test_treasurer_can_save_gcash_details_and_resident_sees_them_as_guide(): void
    {
        $this->actingAs($this->treasurer)
            ->put(route('gcash-config.update'), [
                'gcash_number' => '09171234567',
                'gcash_name' => 'JUAN DELA CRUZ',
            ])
            ->assertRedirect(route('gcash-config.edit'));

        $this->assertDatabaseHas('gcash_configs', [
            'village_id' => $this->village->id,
            'gcash_number' => '09171234567',
            'gcash_name' => 'JUAN DELA CRUZ',
        ]);

        $this->actingAs($this->resident)
            ->get(route('billings.pay-online', $this->billing))
            ->assertOk()
            ->assertSee('09171234567')
            ->assertSee('JUAN DELA CRUZ');
    }

    public function test_gcash_details_validation_requires_number_and_name(): void
    {
        $this->actingAs($this->treasurer)
            ->from(route('gcash-config.edit'))
            ->put(route('gcash-config.update'), ['gcash_number' => '', 'gcash_name' => ''])
            ->assertSessionHasErrors(['gcash_number', 'gcash_name']);
    }

    public function test_pay_online_is_blocked_when_gcash_is_not_configured(): void
    {
        GcashConfig::where('village_id', $this->village->id)->delete();

        $this->actingAs($this->resident)
            ->get(route('billings.pay-online', $this->billing))
            ->assertRedirect(route('billings.show', $this->billing))
            ->assertSessionHas('error');

        $this->actingAs($this->resident)
            ->get(route('billings.show', $this->billing))
            ->assertOk()
            ->assertDontSee('Pay Online (GCash)')
            ->assertDontSee('Submit a New GCash Request');

        $this->actingAs($this->resident)
            ->get(route('household-payments.show', $this->household))
            ->assertOk()
            ->assertDontSee('Pay Online')
            ->assertDontSee('GCash Payment Guide');
    }

    public function test_pay_online_buttons_are_hidden_when_saved_gcash_credentials_are_blank(): void
    {
        GcashConfig::where('village_id', $this->village->id)
            ->update(['gcash_number' => ' ', 'gcash_name' => '']);

        $this->actingAs($this->resident)
            ->get(route('billings.show', $this->billing))
            ->assertOk()
            ->assertDontSee('Pay Online (GCash)');

        $this->actingAs($this->resident)
            ->get(route('household-payments.show', $this->household))
            ->assertOk()
            ->assertDontSee('Pay Online')
            ->assertDontSee('GCash Payment Guide');
    }

    /*
    |--------------------------------------------------------------------------
    | Resident submits an online payment
    |--------------------------------------------------------------------------
    */

    public function test_resident_submits_online_payment_with_screenshot_for_approval(): void
    {
        $this->submitOnlinePayment()
            ->assertRedirect(route('billings.show', $this->billing))
            ->assertSessionHas('success');

        $onlinePayment = OnlinePayment::where('billing_id', $this->billing->id)->firstOrFail();

        $this->assertSame('pending', $onlinePayment->status);
        $this->assertSame($this->household->id, $onlinePayment->household_id);
        $this->assertSame($this->resident->id, $onlinePayment->resident_id);
        $this->assertSame('GCASH123456789', $onlinePayment->transaction_number);
        $this->assertStringStartsWith('payment_proofs/', $onlinePayment->proof_path);
        $this->assertSame(0, HouseholdPayment::where('billing_id', $this->billing->id)->count());
        $this->assertSame(1000.0, (float) $this->billing->fresh()->balance);
        $this->assertSame('pending', $this->billing->fresh()->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->treasurer->id,
            'type' => 'payment',
            'title' => 'Online Payment Request',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->resident->id,
            'type' => 'payment',
            'title' => 'GCash Request Submitted',
        ]);
    }

    public function test_gcash_notifications_only_go_to_treasurers_and_the_requesting_resident(): void
    {
        $admin = $this->makeUser('admin', $this->village->id);
        $secretary = $this->makeUser('secretary', $this->village->id);
        $otherHouseholdResident = $this->makeUser('resident', $this->village->id, $this->household->id);

        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.approve', $onlinePayment));

        foreach ([$admin, $secretary, $otherHouseholdResident] as $excludedUser) {
            $this->assertDatabaseMissing('notifications', [
                'user_id' => $excludedUser->id,
                'type' => 'payment',
            ]);
        }

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->treasurer->id,
            'type' => 'payment',
            'title' => 'Online Payment Request',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->resident->id,
            'type' => 'payment',
            'title' => 'Online Payment Approved',
        ]);
    }

    public function test_resident_cannot_pay_online_for_another_households_billing(): void
    {
        $this->actingAs($this->otherResident)
            ->get(route('billings.pay-online', $this->billing))
            ->assertForbidden();

        $this->actingAs($this->otherResident)
            ->post(route('billings.pay-online.store', $this->billing), [
                'amount' => 100,
                'transaction_number' => 'TXN1',
                'proof' => UploadedFile::fake()->create('proof.png', 100, 'image/png'),
            ])
            ->assertForbidden();

        $this->assertSame(0, OnlinePayment::count());
    }

    public function test_submitting_requires_a_screenshot_and_a_valid_amount(): void
    {
        $this->actingAs($this->resident)
            ->from(route('billings.pay-online', $this->billing))
            ->post(route('billings.pay-online.store', $this->billing), [
                'amount' => 1000,
                'transaction_number' => 'GCASH123456789',
            ])
            ->assertSessionHasErrors(['proof']);

        $this->actingAs($this->resident)
            ->from(route('billings.pay-online', $this->billing))
            ->post(route('billings.pay-online.store', $this->billing), [
                'amount' => 5000,
                'transaction_number' => 'GCASH123456789',
                'proof' => UploadedFile::fake()->create('proof.png', 100, 'image/png'),
            ])
            ->assertSessionHasErrors(['amount']);

        $this->assertSame(0, OnlinePayment::count());
    }

    public function test_duplicate_pending_request_for_the_same_billing_is_blocked(): void
    {
        $this->submitOnlinePayment()->assertRedirect(route('billings.show', $this->billing));

        $this->actingAs($this->resident)
            ->get(route('billings.pay-online', $this->billing))
            ->assertRedirect(route('billings.show', $this->billing))
            ->assertSessionHas('error');

        $this->submitOnlinePayment()->assertSessionHas('error');

        $this->assertSame(1, OnlinePayment::count());
    }

    /*
    |--------------------------------------------------------------------------
    | Treasurer reviews requests
    |--------------------------------------------------------------------------
    */

    public function test_treasurer_menu_contains_online_payments_and_gcash_details(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('dashboard.treasurer'))
            ->assertOk()
            ->assertSee('Online Payments')
            ->assertSee('GCash Details');
    }

    public function test_treasurer_sees_requests_and_details_in_online_payments(): void
    {
        $this->submitOnlinePayment();

        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->get(route('online-payments.index'))
            ->assertOk()
            ->assertSee($this->billing->billing_number)
            ->assertSee('GCASH123456789');

        $this->actingAs($this->treasurer)
            ->get(route('online-payments.show', $onlinePayment))
            ->assertOk()
            ->assertSee('GCASH123456789')
            ->assertSee('₱1,000.00')
            ->assertSee('Current Balance')
            ->assertSee('awaiting your review');

        $this->actingAs($this->resident)
            ->get(route('billings.show', $this->billing))
            ->assertOk()
            ->assertSee('Balance')
            ->assertSee('₱1,000.00');
    }

    public function test_treasurer_of_another_village_cannot_review_online_payments(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->foreignTreasurer)
            ->get(route('online-payments.show', $onlinePayment))
            ->assertForbidden();

        $this->actingAs($this->foreignTreasurer)
            ->post(route('online-payments.approve', $onlinePayment))
            ->assertForbidden();

        $this->assertSame('pending', $onlinePayment->fresh()->status);
    }

    public function test_resident_cannot_access_the_online_payments_pages(): void
    {
        $this->actingAs($this->resident)
            ->get(route('online-payments.index'))
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | Approval records GCash payment
    |--------------------------------------------------------------------------
    */

    public function test_approving_records_payment_and_notifies_the_resident(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.approve', $onlinePayment))
            ->assertRedirect(route('online-payments.show', $onlinePayment));

        $onlinePayment->refresh();
        $this->assertSame('approved', $onlinePayment->status);
        $this->assertSame($this->treasurer->id, $onlinePayment->reviewed_by);
        $this->assertNotNull($onlinePayment->reviewed_at);
        $this->assertNotNull($onlinePayment->payment_id);
        $this->assertSame('paid', $this->billing->fresh()->status);
        $this->assertSame(0.0, (float) $this->billing->fresh()->balance);
        $this->assertSame(1, HouseholdPayment::where('billing_id', $this->billing->id)->count());
        $this->assertSame('GCASH123456789', $onlinePayment->payment?->reference_number);

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.approve', $onlinePayment))
            ->assertSessionHas('error');
        $this->assertSame(1, HouseholdPayment::where('billing_id', $this->billing->id)->count());

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->resident->id,
            'type' => 'payment',
            'title' => 'Online Payment Approved',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->resident->id,
            'type' => 'payment',
            'title' => 'Payment Recorded',
        ]);

        $this->actingAs($this->treasurer)
            ->get(route('online-payments.show', $onlinePayment))
            ->assertOk()
            ->assertSee('Approved and recorded as payment', false)
            ->assertSee('Current Balance')
            ->assertSee('₱0.00');
    }

    public function test_submitting_gcash_request_does_not_change_bill_balance_until_treasurer_approval(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->assertSame(1000.0, (float) $this->billing->fresh()->balance);
        $this->assertSame(0, HouseholdPayment::count());

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.approve', $onlinePayment))
            ->assertRedirect(route('online-payments.show', $onlinePayment));

        $payment = HouseholdPayment::where('billing_id', $this->billing->id)->firstOrFail();
        $this->assertSame('gcash', $payment->payment_method);
        $this->assertSame('GCASH123456789', $payment->reference_number);
        $this->assertSame('paid', $payment->payment_status);

        $this->assertSame($payment->id, $onlinePayment->fresh()->payment_id);
        $this->assertSame('paid', $this->billing->fresh()->status);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->resident->id,
            'type' => 'payment',
            'title' => 'Online Payment Approved',
        ]);
    }

    public function test_pending_or_declined_online_payments_cannot_be_recorded(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->get(route('payments.create', ['online_payment' => $onlinePayment->id]))
            ->assertForbidden();

        $this->actingAs($this->treasurer)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('payments.store'), [
                'billing_id' => $this->billing->id,
                'amount' => 1000,
                'payment_method' => 'gcash',
                'online_payment_id' => $onlinePayment->id,
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, HouseholdPayment::count());
    }

    /*
    |--------------------------------------------------------------------------
    | Decline
    |--------------------------------------------------------------------------
    */

    public function test_treasurer_must_provide_a_reason_to_decline_a_gcash_request(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->from(route('online-payments.show', $onlinePayment))
            ->post(route('online-payments.decline', $onlinePayment), ['declined_reason' => ''])
            ->assertSessionHasErrors(['declined_reason']);

        $this->assertSame('pending', $onlinePayment->fresh()->status);
    }

    public function test_declining_notifies_the_resident_with_the_reason(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.decline', $onlinePayment), [
                'declined_reason' => 'Screenshot is unclear.',
            ])
            ->assertRedirect(route('online-payments.index'));

        $onlinePayment->refresh();
        $this->assertSame('declined', $onlinePayment->status);
        $this->assertSame('Screenshot is unclear.', $onlinePayment->declined_reason);
        $this->assertSame($this->treasurer->id, $onlinePayment->reviewed_by);

        $notice = \App\Models\Notification::where('user_id', $this->resident->id)
            ->where('title', 'Online Payment Declined')
            ->firstOrFail();

        $this->assertStringContainsString('Screenshot is unclear.', $notice->message);
    }

    public function test_resident_can_resubmit_after_a_declined_request(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.decline', $onlinePayment), ['declined_reason' => 'Wrong amount.']);

        $this->actingAs($this->resident)
            ->get(route('billings.show', $this->billing))
            ->assertOk()
            ->assertSee('GCash Payment Requests')
            ->assertSee('payment_proofs/', false)
            ->assertSee('Submit a New GCash Request');

        $this->submitOnlinePayment(['transaction_number' => 'GCASH987654321'])
            ->assertRedirect(route('billings.show', $this->billing))
            ->assertSessionHas('success');

        $this->assertSame(2, OnlinePayment::count());
        $this->assertSame('pending', OnlinePayment::latest('id')->first()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Manual (face-to-face) recording is unaffected
    |--------------------------------------------------------------------------
    */

    public function test_treasurer_can_still_record_a_manual_face_to_face_payment(): void
    {
        $this->actingAs($this->treasurer)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->post(route('payments.store'), [
                'billing_id' => $this->billing->id,
                'amount' => 1000,
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('billings.show', $this->billing));

        $payment = HouseholdPayment::where('billing_id', $this->billing->id)->firstOrFail();
        $this->assertSame('cash', $payment->payment_method);
        $this->assertSame('paid', $this->billing->fresh()->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Bug fixes — billings GCash flow
    |--------------------------------------------------------------------------
    */

    public function test_billings_show_displays_the_error_when_a_request_already_exists(): void
    {
        $this->submitOnlinePayment();

        // Second attempt is redirected back to the billing with a flash error…
        $this->actingAs($this->resident)
            ->get(route('billings.pay-online', $this->billing))
            ->assertRedirect(route('billings.show', $this->billing))
            ->assertSessionHas('error');

        // …and the billing page must actually render that error.
        $this->actingAs($this->resident)
            ->get(route('billings.show', $this->billing))
            ->assertOk()
            ->assertSee('You already have an online payment request awaiting approval', false);
    }

    public function test_staff_do_not_see_resident_pay_online_controls_on_household_payments(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('household-payments.show', $this->household->id))
            ->assertOk()
            ->assertDontSee('Pay Online')
            ->assertDontSee('GCash Payment Guide');

        $this->actingAs($this->resident)
            ->get(route('household-payments.show', $this->household->id))
            ->assertOk()
            ->assertSee('Pay Online')
            ->assertSee('GCash Payment Guide');
    }

    public function test_partial_gcash_payment_reduces_balance_only_after_approval(): void
    {
        $this->submitOnlinePayment(['amount' => 400]);
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->assertSame(1000.0, (float) $this->billing->fresh()->balance);

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.approve', $onlinePayment))
            ->assertRedirect(route('online-payments.show', $onlinePayment));

        $this->assertSame(600.0, (float) $this->billing->fresh()->balance);
        $this->assertSame(400.0, (float) HouseholdPayment::where('billing_id', $this->billing->id)->sum('total_paid'));
    }

    public function test_billings_show_record_payment_link_includes_the_approved_online_payment(): void
    {
        $this->submitOnlinePayment();
        $onlinePayment = OnlinePayment::firstOrFail();

        $this->actingAs($this->treasurer)
            ->post(route('online-payments.approve', $onlinePayment))
            ->assertRedirect(route('online-payments.show', $onlinePayment));

        $this->actingAs($this->treasurer)
            ->get(route('billings.show', $this->billing))
            ->assertOk()
            ->assertSee('Fully Paid', false)
            ->assertSee('₱1,000.00', false);
    }
}
