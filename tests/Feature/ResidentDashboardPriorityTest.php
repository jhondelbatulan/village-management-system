<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResidentDashboardPriorityTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $admin;

    private User $secretary;

    private User $residentUser;

    private Household $household;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Comagascas', 'village_code' => 'CMG']);
        $this->admin = $this->makeUser('admin');
        $this->secretary = $this->makeUser('secretary');

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Head One',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);

        $this->residentUser = $this->makeUser('resident', $this->household);
    }

    private function makeUser(string $roleSlug, ?Household $household = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'household_id' => $household?->id,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function makeAnnouncement(string $title, bool $pinned = false): Announcement
    {
        return Announcement::create([
            'village_id' => $this->village->id,
            'title' => $title,
            'category' => 'general',
            'description' => 'Description for ' . $title,
            'status' => 'published',
            'is_pinned' => $pinned,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_creating_an_announcement_auto_publishes_it(): void
    {
        $this->actingAs($this->admin)->post(route('announcements.store'), [
            'village_id' => $this->village->id,
            'title' => 'Auto Published Notice',
            'category' => 'general',
            'description' => 'No status field submitted.',
        ])->assertRedirect(route('announcements.index'));

        $this->assertSame('published', Announcement::where('title', 'Auto Published Notice')->firstOrFail()->status);
    }

    public function test_announcements_and_events_show_below_the_balance_cards_on_the_resident_dashboard(): void
    {
        $this->makeAnnouncement('Top Announcement');

        $this->actingAs($this->residentUser)
            ->get(route('dashboard.resident'))
            ->assertOk()
            ->assertSeeInOrder(['Outstanding Balance', 'Total Paid', 'Recent Announcements', 'Upcoming Events', 'My Household']);
    }

    public function test_announcement_and_event_cards_link_to_their_show_pages(): void
    {
        $announcement = $this->makeAnnouncement('Linked Announcement');

        $event = \App\Models\Event::create([
            'village_id' => $this->village->id,
            'title' => 'Linked Event',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->residentUser)
            ->get(route('dashboard.resident'))
            ->assertOk()
            ->assertSee(route('announcements.show', $announcement), false)
            ->assertSee(route('events.show', $event), false);
    }

    public function test_pinned_announcements_are_listed_first_on_the_resident_dashboard(): void
    {
        $this->makeAnnouncement('Newer Unpinned');
        $this->makeAnnouncement('Older Pinned', pinned: true);

        $this->actingAs($this->residentUser)
            ->get(route('dashboard.resident'))
            ->assertOk()
            ->assertSeeInOrder(['Older Pinned', 'Newer Unpinned']);
    }

    public function test_the_billings_menu_item_is_hidden_from_the_secretary(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('dashboard.secretary'))
            ->assertOk()
            ->assertDontSee(route('billings.index'), false);
    }
}
