<?php

namespace Tests\Feature;

use App\Models\Billing;
use App\Models\Event;
use App\Models\EventResponse;
use App\Models\Household;
use App\Models\Notification;
use App\Models\PaymentConfiguration;
use App\Models\PaymentType;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActivityNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Village $otherVillage;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    private User $residentA;

    private User $residentB;

    private Household $householdA;

    private Household $householdB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Comagascas', 'village_code' => 'CMG']);
        $this->otherVillage = Village::forceCreate(['village_name' => 'Other Village', 'village_code' => 'OTH']);

        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);

        $this->householdA = $this->makeHousehold($this->village, '1', '1');
        $this->householdB = $this->makeHousehold($this->otherVillage, '1', '1');

        $this->residentA = $this->makeUser('resident', $this->village->id, $this->householdA);
        $this->residentB = $this->makeUser('resident', $this->otherVillage->id, $this->householdB);
    }

    private function makeUser(string $roleSlug, ?int $villageId = null, ?Household $household = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $villageId,
            'household_id' => $household?->id,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
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
            'submitted_by' => $this->secretary->id,
        ]);
    }

    public function test_publishing_an_announcement_notifies_village_staff_except_the_creator(): void
    {
        $this->actingAs($this->admin)->post(route('announcements.store'), [
            'village_id' => $this->village->id,
            'title' => 'Water Interruption',
            'category' => 'general',
            'description' => 'No water supply on Saturday.',
            'status' => 'published',
        ])->assertRedirect(route('announcements.index'));

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->secretary->id,
            'type' => 'announcement',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->treasurer->id,
            'type' => 'announcement',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->admin->id,
            'type' => 'announcement',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->residentB->id,
            'type' => 'announcement',
        ]);
    }

    public function test_deleting_an_event_notifies_staff_and_responding_households(): void
    {
        $event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Barangay Assembly',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        EventResponse::create([
            'event_id' => $event->id,
            'household_id' => $this->householdA->id,
            'response' => 'going',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('events.destroy', $event))
            ->assertRedirect(route('events.index'));

        $staffNotice = Notification::where('user_id', $this->secretary->id)
            ->where('type', 'event')
            ->where('title', 'Event Deleted')
            ->firstOrFail();
        $this->assertStringContainsString('Barangay Assembly', $staffNotice->message);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->treasurer->id,
            'type' => 'event',
            'title' => 'Event Deleted',
        ]);
        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->admin->id,
            'title' => 'Event Deleted',
        ]);

        $residentNotice = Notification::where('user_id', $this->residentA->id)
            ->where('type', 'event')
            ->where('title', 'Event Cancelled')
            ->firstOrFail();
        $this->assertStringContainsString('Barangay Assembly', $residentNotice->message);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->residentB->id,
            'title' => 'Event Cancelled',
        ]);
    }

    public function test_recording_a_payment_notifies_the_households_resident_accounts(): void
    {
        PaymentConfiguration::create([
            'village_id' => $this->village->id,
            'payment_enabled' => true,
            'payment_manager_role' => 'treasurer',
        ]);

        $paymentType = PaymentType::create([
            'village_id' => $this->village->id,
            'payment_name' => 'Monthly Dues',
            'frequency' => 'monthly',
            'default_amount' => 500,
            'is_active' => true,
        ]);

        $billing = Billing::create([
            'billing_number' => 'BL-' . Str::upper(Str::random(6)),
            'household_id' => $this->householdA->id,
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

        $this->actingAs($this->treasurer)->withSession(['auth.password_confirmed_at' => time()])->post(route('payments.store'), [
            'billing_id' => $billing->id,
            'amount' => 500,
            'payment_method' => 'cash',
        ])->assertRedirect(route('billings.show', $billing));

        $notice = Notification::where('user_id', $this->residentA->id)
            ->where('type', 'payment')
            ->firstOrFail();

        $this->assertStringContainsString('Payment Recorded', $notice->title);
        $this->assertStringContainsString('500.00', $notice->message);
        $this->assertSame(route('household-payments.show', $this->householdA->id), $notice->action_url);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->residentB->id,
            'type' => 'payment',
        ]);
    }

    public function test_archiving_a_household_notifies_its_users_and_village_admins(): void
    {
        $this->actingAs($this->secretary)
            ->delete(route('households.destroy', $this->householdA))
            ->assertRedirect(route('households.index'));

        $residentNotice = Notification::where('user_id', $this->residentA->id)
            ->where('type', 'warning')
            ->where('title', 'Household Archived')
            ->firstOrFail();
        $this->assertStringContainsString($this->householdA->household_number, $residentNotice->message);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $this->admin->id,
            'title' => 'Household Archived',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->residentB->id,
            'title' => 'Household Archived',
        ]);
    }

    public function test_archiving_a_resident_notifies_the_households_users(): void
    {
        $resident = Resident::create([
            'village_id' => $this->village->id,
            'household_id' => $this->householdA->id,
            'resident_code' => 'RES-' . Str::upper(Str::random(6)),
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);

        $this->actingAs($this->secretary)
            ->delete(route('residents.destroy', $resident))
            ->assertRedirect(route('residents.index'));

        $notice = Notification::where('user_id', $this->residentA->id)
            ->where('type', 'warning')
            ->where('title', 'Resident Archived')
            ->firstOrFail();
        $this->assertStringContainsString('Juan Dela Cruz', $notice->message);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $this->residentB->id,
            'title' => 'Resident Archived',
        ]);
    }
}
