<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventAnnouncementDeleteTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    private User $resident;

    private Event $event;

    private Announcement $announcement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Delete Village', 'village_code' => 'DLV']);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
        $this->resident = $this->makeUser('resident', $this->village->id);

        $this->event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Deletable Event',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        $this->announcement = Announcement::create([
            'village_id' => $this->village->id,
            'title' => 'Deletable Announcement',
            'category' => 'general',
            'status' => 'published',
            'created_by' => $this->admin->id,
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
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    public function test_events_index_shows_the_delete_button_only_to_the_creator(): void
    {
        $this->actingAs($this->admin)
            ->get(route('events.index'))
            ->assertOk()
            ->assertSee('title="Delete Event"', false);

        foreach ([$this->superAdmin, $this->secretary, $this->treasurer, $this->resident] as $user) {
            $this->actingAs($user)
                ->get(route('events.index'))
                ->assertOk()
                ->assertDontSee('title="Delete Event"', false);
        }
    }

    public function test_announcements_index_shows_the_delete_button_only_to_the_creator(): void
    {
        $this->actingAs($this->admin)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('title="Delete"', false);

        foreach ([$this->superAdmin, $this->secretary, $this->treasurer, $this->resident] as $user) {
            $this->actingAs($user)
                ->get(route('announcements.index'))
                ->assertOk()
                ->assertDontSee('title="Delete"', false);
        }
    }

    public function test_creator_can_delete_their_event_and_announcement(): void
    {
        $this->actingAs($this->admin)
            ->delete(route('events.destroy', $this->event))
            ->assertRedirect(route('events.index'));

        $this->assertSoftDeleted('events', ['id' => $this->event->id]);

        $this->actingAs($this->admin)
            ->delete(route('announcements.destroy', $this->announcement))
            ->assertRedirect(route('announcements.index'));

        $this->assertSoftDeleted('announcements', ['id' => $this->announcement->id]);
    }

    public function test_super_admin_cannot_delete_someone_elses_event_or_announcement(): void
    {
        $this->actingAs($this->superAdmin)
            ->delete(route('events.destroy', $this->event))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->delete(route('announcements.destroy', $this->announcement))
            ->assertForbidden();

        $this->assertDatabaseHas('events', ['id' => $this->event->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('announcements', ['id' => $this->announcement->id, 'deleted_at' => null]);
    }

    public function test_secretary_and_treasurer_cannot_delete_announcements_or_events(): void
    {
        foreach ([$this->secretary, $this->treasurer] as $user) {
            $this->actingAs($user)
                ->delete(route('events.destroy', $this->event))
                ->assertForbidden();

            $this->actingAs($user)
                ->delete(route('announcements.destroy', $this->announcement))
                ->assertForbidden();
        }

        $this->assertDatabaseHas('events', ['id' => $this->event->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('announcements', ['id' => $this->announcement->id, 'deleted_at' => null]);
    }

    public function test_super_admin_can_manage_legacy_records_without_a_creator(): void
    {
        $legacyEvent = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Legacy Event',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
        ]);

        $this->actingAs($this->superAdmin)
            ->delete(route('events.destroy', $legacyEvent))
            ->assertRedirect(route('events.index'));

        $this->assertSoftDeleted('events', ['id' => $legacyEvent->id]);

        $legacyAnnouncement = Announcement::create([
            'village_id' => $this->village->id,
            'title' => 'Legacy Announcement',
            'category' => 'general',
            'status' => 'published',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('announcements.destroy', $legacyAnnouncement))
            ->assertForbidden();

        $this->assertDatabaseHas('announcements', ['id' => $legacyAnnouncement->id, 'deleted_at' => null]);
    }

    public function test_show_pages_expose_manage_actions_only_to_the_creator(): void
    {
        $this->actingAs($this->admin)
            ->get(route('events.show', $this->event))
            ->assertOk()
            ->assertSee('Delete Event');

        $this->actingAs($this->superAdmin)
            ->get(route('events.show', $this->event))
            ->assertOk()
            ->assertDontSee('Delete Event');

        $this->actingAs($this->admin)
            ->get(route('announcements.show', $this->announcement))
            ->assertOk()
            ->assertSee("confirm('Delete this announcement?')", false);

        $this->actingAs($this->superAdmin)
            ->get(route('announcements.show', $this->announcement))
            ->assertOk()
            ->assertDontSee("confirm('Delete this announcement?')", false);
    }

    public function test_non_creator_cannot_edit_or_update_someone_elses_event(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('events.edit', $this->event))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->put(route('events.update', $this->event), [
                'title' => 'Hijacked Title',
                'category' => 'gathering',
                'event_date' => now()->addWeek()->toDateString(),
            ])
            ->assertForbidden();

        $this->assertSame('Deletable Event', $this->event->fresh()->title);

        $this->actingAs($this->admin)
            ->get(route('events.edit', $this->event))
            ->assertOk();
    }

    public function test_non_creator_cannot_edit_update_publish_or_archive_someone_elses_announcement(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('announcements.edit', $this->announcement))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->put(route('announcements.update', $this->announcement), [
                'title' => 'Hijacked Notice',
                'category' => 'general',
                'status' => 'published',
            ])
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->post(route('announcements.archive', $this->announcement))
            ->assertForbidden();

        $this->assertSame('Deletable Announcement', $this->announcement->fresh()->title);
        $this->assertSame('published', $this->announcement->fresh()->status);

        $this->actingAs($this->admin)
            ->get(route('announcements.edit', $this->announcement))
            ->assertOk();
    }

    public function test_non_creator_cannot_complete_or_cancel_someone_elses_event(): void
    {
        $this->actingAs($this->superAdmin)
            ->patch(route('events.complete', $this->event))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->patch(route('events.cancel', $this->event))
            ->assertForbidden();

        $this->assertSame('upcoming', $this->event->fresh()->status);
    }
}
