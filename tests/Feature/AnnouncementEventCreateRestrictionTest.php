<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Household;
use App\Models\Participation;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnnouncementEventCreateRestrictionTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Create Restrict Village', 'village_code' => 'CRV']);
        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
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

    private function announcementPayload(array $overrides = []): array
    {
        return array_merge([
            'village_id' => $this->village->id,
            'title' => 'Community Notice',
            'category' => 'General',
            'description' => 'Notice body',
            'status' => 'published',
        ], $overrides);
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'village_id' => $this->village->id,
            'title' => 'Barangay Assembly',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
        ], $overrides);
    }

    public function test_secretary_cannot_create_an_announcement(): void
    {
        $this->actingAs($this->secretary)->get(route('announcements.create'))->assertForbidden();

        $this->actingAs($this->secretary)
            ->post(route('announcements.store'), $this->announcementPayload())
            ->assertForbidden();

        $this->assertSame(0, Announcement::count());
    }

    public function test_treasurer_cannot_create_an_announcement(): void
    {
        $this->actingAs($this->treasurer)->get(route('announcements.create'))->assertForbidden();

        $this->actingAs($this->treasurer)
            ->post(route('announcements.store'), $this->announcementPayload())
            ->assertForbidden();

        $this->assertSame(0, Announcement::count());
    }

    public function test_secretary_cannot_create_an_event(): void
    {
        $this->actingAs($this->secretary)->get(route('events.create'))->assertForbidden();

        $this->actingAs($this->secretary)
            ->post(route('events.store'), $this->eventPayload())
            ->assertForbidden();

        $this->assertSame(0, Event::count());
    }

    public function test_treasurer_cannot_create_an_event(): void
    {
        $this->actingAs($this->treasurer)->get(route('events.create'))->assertForbidden();

        $this->actingAs($this->treasurer)
            ->post(route('events.store'), $this->eventPayload())
            ->assertForbidden();

        $this->assertSame(0, Event::count());
    }

    public function test_secretary_and_treasurer_cannot_edit_or_delete_announcements(): void
    {
        $announcement = Announcement::create($this->announcementPayload([
            'created_by' => $this->admin->id,
        ]));

        foreach ([$this->secretary, $this->treasurer] as $user) {
            $this->actingAs($user)->get(route('announcements.edit', $announcement))->assertForbidden();
            $this->actingAs($user)->put(route('announcements.update', $announcement), $this->announcementPayload())->assertForbidden();
            $this->actingAs($user)->delete(route('announcements.destroy', $announcement))->assertForbidden();
        }

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'deleted_at' => null]);
    }

    public function test_secretary_and_treasurer_cannot_edit_or_delete_events(): void
    {
        $event = Event::create($this->eventPayload([
            'created_by' => $this->admin->id,
        ]));

        foreach ([$this->secretary, $this->treasurer] as $user) {
            $this->actingAs($user)->get(route('events.edit', $event))->assertForbidden();
            $this->actingAs($user)->put(route('events.update', $event), $this->eventPayload())->assertForbidden();
            $this->actingAs($user)->delete(route('events.destroy', $event))->assertForbidden();
        }

        $this->assertDatabaseHas('events', ['id' => $event->id, 'deleted_at' => null]);
    }

    public function test_admin_can_create_an_announcement_and_an_event(): void
    {
        $this->actingAs($this->admin)
            ->post(route('announcements.store'), $this->announcementPayload(['title' => 'Admin Announcement']))
            ->assertRedirect(route('announcements.index'));

        $this->actingAs($this->admin)
            ->post(route('events.store'), $this->eventPayload(['title' => 'Admin Event']))
            ->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('announcements', ['title' => 'Admin Announcement']);
        $this->assertDatabaseHas('events', ['title' => 'Admin Event']);
    }

    public function test_super_admin_can_create_an_announcement_and_an_event(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('announcements.store'), $this->announcementPayload(['title' => 'Super Announcement']))
            ->assertRedirect(route('announcements.index'));

        $this->actingAs($this->superAdmin)
            ->post(route('events.store'), $this->eventPayload(['title' => 'Super Event']))
            ->assertRedirect(route('events.index'));

        $this->assertDatabaseHas('announcements', ['title' => 'Super Announcement']);
        $this->assertDatabaseHas('events', ['title' => 'Super Event']);
    }

    public function test_secretary_and_treasurer_can_still_view_announcements_and_events(): void
    {
        Announcement::create($this->announcementPayload(['title' => 'Viewable Notice', 'created_by' => $this->admin->id]));
        Event::create($this->eventPayload(['title' => 'Viewable Event', 'created_by' => $this->admin->id]));

        foreach ([$this->secretary, $this->treasurer] as $user) {
            $this->actingAs($user)
                ->get(route('announcements.index'))
                ->assertOk()
                ->assertSee('Viewable Notice');

            $this->actingAs($user)
                ->get(route('events.index'))
                ->assertOk()
                ->assertSee('Viewable Event');
        }
    }

    public function test_secretary_can_still_record_event_participations(): void
    {
        $event = Event::create($this->eventPayload(['created_by' => $this->admin->id]));

        $household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '4',
            'lot_number' => '9',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $this->actingAs($this->secretary)
            ->post(route('events.participations.store', $event), [
                'household_id' => $household->id,
                'status' => 'present',
            ])
            ->assertRedirect(route('events.show', $event));

        $this->assertSame(1, Participation::where('event_id', $event->id)->count());
    }
}
