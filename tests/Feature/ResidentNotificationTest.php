<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Household;
use App\Models\Notification;
use App\Models\PaymentConfiguration;
use App\Models\PaymentType;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResidentNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Village $otherVillage;

    private User $treasurer;

    private User $admin;

    private User $residentA;

    private User $residentA2;

    private User $residentB;

    private Household $householdA;

    private Household $householdA2;

    private PaymentType $monthlyDues;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Comagascas', 'village_code' => 'CMG']);
        $this->otherVillage = Village::forceCreate(['village_name' => 'Other Village', 'village_code' => 'OTH']);

        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
        $this->admin = $this->makeUser('admin', $this->village->id);

        $this->householdA = $this->makeHousehold($this->village, '1', '1');
        $this->householdA2 = $this->makeHousehold($this->village, '1', '2');
        $householdB = $this->makeHousehold($this->otherVillage, '1', '1');

        $this->residentA = $this->makeUser('resident', $this->village->id, $this->householdA);
        $this->residentA2 = $this->makeUser('resident', $this->village->id, $this->householdA2);
        $this->residentB = $this->makeUser('resident', $this->otherVillage->id, $householdB);

        PaymentConfiguration::create([
            'village_id' => $this->village->id,
            'payment_enabled' => true,
            'payment_manager_role' => 'treasurer',
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

    public function test_generating_billings_notifies_the_billed_households_resident_accounts(): void
    {
        $period = now()->format('Y-m');

        $this->actingAs($this->treasurer)->withSession(['auth.password_confirmed_at' => time()])->post(route('billings.generate.store'), [
            'village_id' => $this->village->id,
            'billing_period' => $period,
            'payment_type_ids' => [$this->monthlyDues->id],
        ])->assertRedirect(route('billings.index'));

        $notice = Notification::where('user_id', $this->residentA->id)
            ->where('type', 'billing')
            ->firstOrFail();

        $this->assertStringContainsString("for {$period}", $notice->message);
        $this->assertStringContainsString('New bill billed to', $notice->message);
        $this->assertStringContainsString('Blk 1, Lot 1', $notice->message);
        $this->assertSame(route('billings.index'), $notice->action_url);

        $this->assertDatabaseHas('notifications', ['user_id' => $this->residentA->id, 'type' => 'billing']);

        $householdA2Notice = Notification::where('user_id', $this->residentA2->id)
            ->where('type', 'billing')
            ->firstOrFail();

        $this->assertStringContainsString('Blk 1, Lot 2', $householdA2Notice->message);

        $this->assertDatabaseMissing('notifications', ['user_id' => $this->residentB->id]);
    }

    public function test_creating_a_published_announcement_notifies_the_villages_resident_accounts(): void
    {
        $this->actingAs($this->admin)->post(route('announcements.store'), [
            'village_id' => $this->village->id,
            'title' => 'Water Interruption',
            'category' => 'general',
            'description' => 'No water supply on Saturday.',
            'status' => 'published',
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Water Interruption')->firstOrFail();

        $notice = Notification::where('user_id', $this->residentA->id)
            ->where('type', 'announcement')
            ->firstOrFail();

        $this->assertStringContainsString('Water Interruption', $notice->message);
        $this->assertSame(route('announcements.show', $announcement), $notice->action_url);

        $this->assertDatabaseHas('notifications', ['user_id' => $this->residentA2->id, 'type' => 'announcement']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $this->residentB->id]);
    }

    public function test_draft_announcements_notify_nobody_until_they_are_published(): void
    {
        $this->actingAs($this->admin)->post(route('announcements.store'), [
            'village_id' => $this->village->id,
            'title' => 'Draft Notice',
            'category' => 'general',
            'status' => 'draft',
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Draft Notice')->firstOrFail();

        $this->assertDatabaseMissing('notifications', ['user_id' => $this->residentA->id, 'type' => 'announcement']);

        $this->actingAs($this->admin)
            ->post(route('announcements.publish', $announcement))
            ->assertRedirect();

        $this->assertSame(1, Notification::where('user_id', $this->residentA->id)
            ->where('type', 'announcement')->count());

        $this->actingAs($this->admin)
            ->post(route('announcements.publish', $announcement))
            ->assertRedirect();

        $this->assertSame(1, Notification::where('user_id', $this->residentA->id)
            ->where('type', 'announcement')->count());
    }

    public function test_creating_an_event_notifies_the_villages_resident_accounts(): void
    {
        $this->actingAs($this->admin)->post(route('events.store'), [
            'village_id' => $this->village->id,
            'title' => 'Barangay Assembly',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
        ])->assertRedirect(route('events.index'));

        $notice = Notification::where('user_id', $this->residentA->id)
            ->where('type', 'event')
            ->firstOrFail();

        $this->assertStringContainsString('Barangay Assembly', $notice->message);
        $this->assertStringContainsString(now()->addWeek()->format('M d, Y'), $notice->message);

        $this->assertDatabaseHas('notifications', ['user_id' => $this->residentA2->id, 'type' => 'event']);
        $this->assertDatabaseMissing('notifications', ['user_id' => $this->residentB->id]);
    }

    public function test_publishing_a_draft_through_the_edit_form_notifies_residents(): void
    {
        $this->actingAs($this->admin)->post(route('announcements.store'), [
            'village_id' => $this->village->id,
            'title' => 'Edited Draft',
            'category' => 'general',
            'status' => 'draft',
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Edited Draft')->firstOrFail();

        $this->actingAs($this->admin)->put(route('announcements.update', $announcement), [
            'title' => 'Edited Draft',
            'category' => 'general',
            'status' => 'published',
        ])->assertRedirect(route('announcements.index'));

        $this->assertSame(1, Notification::where('user_id', $this->residentA->id)
            ->where('type', 'announcement')->count());

        $this->assertDatabaseMissing('notifications', ['user_id' => $this->residentB->id, 'type' => 'announcement']);

        $this->actingAs($this->admin)->put(route('announcements.update', $announcement), [
            'title' => 'Edited Draft Renamed',
            'category' => 'general',
            'status' => 'published',
        ])->assertRedirect(route('announcements.index'));

        $this->assertSame(1, Notification::where('user_id', $this->residentA->id)
            ->where('type', 'announcement')->count());
    }

    public function test_creating_a_past_date_event_marks_it_ongoing_and_notifies_residents(): void
    {
        // Past dates no longer auto-complete; the event stays ongoing until
        // its creator marks it completed, so residents are notified right away.
        $this->actingAs($this->admin)->post(route('events.store'), [
            'village_id' => $this->village->id,
            'title' => 'Past Event',
            'category' => 'gathering',
            'event_date' => now()->subWeek()->toDateString(),
        ])->assertRedirect(route('events.index'));

        $event = Event::where('title', 'Past Event')->firstOrFail();
        $this->assertSame('ongoing', $event->status);

        $this->assertSame(1, Notification::where('user_id', $this->residentA->id)
            ->where('type', 'event')->count());

        $this->assertDatabaseMissing('notifications', ['user_id' => $this->residentB->id, 'type' => 'event']);

        // Only the creator can complete it, which stops further announcements.
        $this->actingAs($this->admin)
            ->patch(route('events.complete', $event))
            ->assertRedirect(route('events.show', $event));

        $this->assertSame('completed', $event->fresh()->status);
    }

    public function test_resident_dashboard_layout_plays_a_sound_for_new_notifications(): void
    {
        $this->actingAs($this->residentA)
            ->get(route('dashboard.resident'))
            ->assertOk()
            ->assertSee('playNotificationSound', false);
    }

    public function test_mark_as_read_form_redirects_to_notifications_instead_of_showing_json(): void
    {
        $notification = Notification::create([
            'user_id' => $this->residentA->id,
            'type' => 'announcement',
            'title' => 'A new announcement',
            'message' => 'Please read the announcement.',
        ]);

        $this->actingAs($this->residentA)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Notification marked as read.');

        $this->assertNotNull($notification->fresh()->read_at);
    }
}

