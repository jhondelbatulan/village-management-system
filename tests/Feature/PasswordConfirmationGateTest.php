<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Sensitive actions (deleting households, editing/deleting user accounts,
 * downloading reports, recording payments, generating billings) must be
 * blocked until the acting user re-enters their own password.
 */
class PasswordConfirmationGateTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Household $household;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Gate Village', 'village_code' => 'GDG']);
        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-GATE-01',
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Gate Head',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);
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
            'first_name' => 'Gate',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function confirmed(): array
    {
        return ['auth.password_confirmed_at' => time()];
    }

    public function test_super_admin_household_force_delete_is_blocked_without_confirmation(): void
    {
        $this->actingAs($this->superAdmin)
            ->delete(route('households.force-delete', $this->household))
            ->assertRedirect(route('password.confirm.show'));

        $this->assertDatabaseHas('households', ['id' => $this->household->id]);
    }

    public function test_super_admin_household_force_delete_runs_after_confirmation(): void
    {
        $this->actingAs($this->superAdmin)
            ->withSession($this->confirmed())
            ->delete(route('households.force-delete', $this->household))
            ->assertRedirect(route('households.index'));

        $this->assertDatabaseMissing('households', ['id' => $this->household->id]);
    }

    public function test_stale_password_confirmation_is_rejected(): void
    {
        // Older than the 300 second window — the action must be gated again.
        $this->actingAs($this->superAdmin)
            ->withSession(['auth.password_confirmed_at' => time() - 301])
            ->delete(route('households.force-delete', $this->household))
            ->assertRedirect(route('password.confirm.show'));

        $this->assertDatabaseHas('households', ['id' => $this->household->id]);
    }

    public function test_role_check_still_runs_before_the_password_gate(): void
    {
        // Even a fresh confirmation does not lift the role restriction itself.
        $this->actingAs($this->secretary)
            ->withSession($this->confirmed())
            ->delete(route('households.force-delete', $this->household))
            ->assertForbidden();

        $this->assertDatabaseHas('households', ['id' => $this->household->id]);
    }

    public function test_user_account_edit_and_delete_require_confirmation_for_super_admin_and_admin(): void
    {
        $target = $this->makeUser('resident', $this->village->id);

        foreach ([$this->superAdmin, $this->admin] as $actor) {
            $this->actingAs($actor)
                ->put(route('users.update', $target), [
                    'role_id' => $target->role_id,
                    'village_id' => $this->village->id,
                    'household_id' => $this->household->id,
                    'username' => $target->username,
                    'first_name' => 'Edited',
                    'last_name' => 'Account',
                    'status' => 'active',
                ])
                ->assertRedirect(route('password.confirm.show'));

            $this->actingAs($actor)
                ->delete(route('users.destroy', $target))
                ->assertRedirect(route('password.confirm.show'));
        }

        $this->assertDatabaseHas('users', ['id' => $target->id, 'first_name' => $target->first_name]);

        // Both actions go through once the password was confirmed.
        $this->actingAs($this->admin)
            ->withSession($this->confirmed())
            ->put(route('users.update', $target), [
                'role_id' => $target->role_id,
                'village_id' => $this->village->id,
                'household_id' => $this->household->id,
                'username' => $target->username,
                'first_name' => 'Edited',
                'last_name' => 'Account',
                'status' => 'active',
            ])
            ->assertRedirect(route('users.index'));

        $this->actingAs($this->superAdmin)
            ->withSession($this->confirmed())
            ->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    public function test_report_generation_requires_confirmation_for_super_admin_and_admin_only(): void
    {
        $payload = ['report_type' => 'household_master_list', 'format' => 'csv'];

        foreach ([$this->superAdmin, $this->admin] as $actor) {
            $this->actingAs($actor)
                ->post(route('reports.generate'), $payload)
                ->assertRedirect(route('password.confirm.show'));
        }

        // Secretary and treasurer download reports without any confirmation step.
        $this->actingAs($this->secretary)
            ->post(route('reports.generate'), $payload)
            ->assertOk();

        $this->actingAs($this->treasurer)
            ->post(route('reports.generate'), $payload)
            ->assertOk();
    }

    public function test_report_download_link_confirms_then_returns_to_the_download_url(): void
    {
        $downloadUrl = route('reports.generate', [
            'report_type' => 'household_master_list',
            'format' => 'csv',
        ]);

        // Hitting the download link without a confirmation stores it as the
        // intended destination and sends the user to the password prompt.
        $this->actingAs($this->superAdmin)
            ->get($downloadUrl)
            ->assertRedirect(route('password.confirm.show'));

        // The stored destination keeps the full download URL (query ordering
        // aside), so confirming returns straight to the download.
        $intended = session('url.intended');
        $this->assertIsString($intended);
        $this->assertStringContainsString('/reports/generate', $intended);
        $this->assertStringContainsString('household_master_list', $intended);

        $this->actingAs($this->superAdmin)
            ->post(route('password.confirm.store'), ['password' => 'secret-password'])
            ->assertRedirect($intended);

        $this->assertNotNull(session('auth.password_confirmed_at'));
    }

    public function test_confirmation_with_the_wrong_password_is_rejected(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('password.confirm.store'), ['password' => 'definitely-wrong'])
            ->assertSessionHasErrors('password');

        $this->assertNull(session('auth.password_confirmed_at'));

        // The gated action is still blocked afterwards.
        $this->actingAs($this->superAdmin)
            ->delete(route('households.force-delete', $this->household))
            ->assertRedirect(route('password.confirm.show'));
    }

    public function test_gated_json_requests_receive_a_419_response(): void
    {
        $this->actingAs($this->superAdmin)
            ->deleteJson(route('households.force-delete', $this->household))
            ->assertStatus(419);

        $this->actingAs($this->treasurer)
            ->postJson(route('payments.store'), [])
            ->assertStatus(419);

        $this->assertDatabaseHas('households', ['id' => $this->household->id]);
    }

    public function test_treasurer_payment_recording_and_billing_generation_require_confirmation(): void
    {
        \App\Models\PaymentConfiguration::create([
            'village_id' => $this->village->id,
            'payment_enabled' => true,
            'payment_manager_role' => 'treasurer',
        ]);

        $paymentType = \App\Models\PaymentType::create([
            'village_id' => $this->village->id,
            'payment_name' => 'Gate Dues',
            'frequency' => 'monthly',
            'default_amount' => 500,
            'is_active' => true,
        ]);

        $billing = \App\Models\Billing::create([
            'billing_number' => 'BL-' . Str::upper(Str::random(6)),
            'household_id' => $this->household->id,
            'village_id' => $this->village->id,
            'payment_type_id' => $paymentType->id,
            'billing_period' => now()->format('Y-m'),
            'due_date' => now()->addWeek()->toDateString(),
            'amount' => 500,
            'penalty' => 0,
            'discount' => 0,
            'total_amount' => 500,
            'status' => 'pending',
            'created_by' => $this->admin->id,
        ]);

        $paymentPayload = [
            'billing_id' => $billing->id,
            'amount' => 500,
            'payment_method' => 'cash',
        ];

        // Both treasurer actions are blocked before the controllers run.
        $this->actingAs($this->treasurer)
            ->post(route('payments.store'), $paymentPayload)
            ->assertRedirect(route('password.confirm.show'));

        $this->actingAs($this->treasurer)
            ->post(route('billings.generate.store'), [])
            ->assertRedirect(route('password.confirm.show'));

        $this->assertSame(0, \App\Models\HouseholdPayment::count());

        // After a fresh confirmation the payment is actually recorded…
        $this->actingAs($this->treasurer)
            ->withSession($this->confirmed())
            ->post(route('payments.store'), $paymentPayload)
            ->assertRedirect(route('billings.show', $billing));

        $this->assertSame(1, \App\Models\HouseholdPayment::count());

        // …and billing generation reaches its own validation instead of the gate.
        $response = $this->actingAs($this->treasurer)
            ->withSession($this->confirmed())
            ->post(route('billings.generate.store'), []);

        $this->assertNotSame(route('password.confirm.show'), $response->headers->get('Location'));
    }
}
