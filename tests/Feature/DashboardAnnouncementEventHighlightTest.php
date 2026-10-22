<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Event;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DashboardAnnouncementEventHighlightTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    private User $residentUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate([
            'village_name' => 'Highlight Village',
            'village_code' => 'HLV',
        ]);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
        $this->residentUser = $this->makeUser('resident', $this->village->id);

        $household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '7',
            'lot_number' => '8',
            'household_head_name' => 'Highlight Head',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);

        $this->residentUser->forceFill(['household_id' => $household->id])->save();

        Announcement::create([
            'village_id' => $this->village->id,
            'title' => 'Water Works Notice',
            'category' => 'General',
            'description' => 'Public works schedule',
            'status' => 'published',
            'created_by' => $this->secretary->id,
        ]);

        Announcement::create([
            'village_id' => $this->village->id,
            'title' => 'Admin Strategy Briefing',
            'category' => 'General',
            'description' => 'Leadership only',
            'status' => 'published',
            'visible_roles' => ['admin'],
            'created_by' => $this->superAdmin->id,
        ]);

        Event::create([
            'village_id' => $this->village->id,
            'title' => 'Family Fun Day',
            'category' => 'gathering',
            'event_date' => now()->addDays(5)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->secretary->id,
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

    public function test_treasurer_dashboard_highlights_announcements_and_events(): void
    {
        $this->actingAs($this->treasurer)
            ->get(route('dashboard.treasurer'))
            ->assertOk()
            ->assertSee('Recent Announcements')
            ->assertSee('Upcoming Events')
            ->assertSee('Water Works Notice')
            ->assertSee('Family Fun Day')
            ->assertDontSee('Admin Strategy Briefing');
    }

    public function test_super_admin_dashboard_highlights_announcements_and_events(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('dashboard.super_admin'))
            ->assertOk()
            ->assertSee('Recent Announcements')
            ->assertSee('Upcoming Events')
            ->assertSee('Water Works Notice')
            ->assertSee('Admin Strategy Briefing')
            ->assertSee('Family Fun Day');
    }

    public function test_admin_dashboard_highlights_announcements_and_events(): void
    {
        $this->actingAs($this->admin)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('Recent Announcements')
            ->assertSee('Upcoming Events')
            ->assertSee('Water Works Notice')
            ->assertSee('Admin Strategy Briefing')
            ->assertSee('Family Fun Day');
    }

    public function test_secretary_dashboard_highlights_announcements_and_events(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('dashboard.secretary'))
            ->assertOk()
            ->assertSee('Recent Announcements')
            ->assertSee('Upcoming Events')
            ->assertSee('Water Works Notice')
            ->assertSee('Family Fun Day')
            ->assertDontSee('Admin Strategy Briefing');
    }

    public function test_resident_dashboard_highlights_announcements_and_events(): void
    {
        $this->actingAs($this->residentUser)
            ->get(route('dashboard.resident'))
            ->assertOk()
            ->assertSee('Recent Announcements')
            ->assertSee('Upcoming Events')
            ->assertSee('Water Works Notice')
            ->assertSee('Family Fun Day')
            ->assertDontSee('Admin Strategy Briefing');
    }
}
