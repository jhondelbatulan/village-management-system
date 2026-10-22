<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventResponse;
use App\Models\Household;
use App\Models\Participation;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HouseholdEventResponseTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $admin;

    private User $residentUser;

    private Household $household;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Comagascas', 'village_code' => 'CMG']);
        $this->admin = $this->makeUser('admin');

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '2',
            'household_head_name' => 'Kimberly Lacbayo',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->admin->id,
        ]);

        // The account is linked to the household but the household has no
        // resident profiles at all — responding must still work.
        $this->residentUser = $this->makeUser('resident');
        $this->residentUser->update(['household_id' => $this->household->id]);
    }

    private function makeUser(string $roleSlug): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function makeEvent(): Event
    {
        return Event::create([
            'village_id' => $this->village->id,
            'title' => 'Barangay Assembly',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_responding_works_even_when_the_household_has_no_resident_profiles(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $event), ['response' => 'going'])
            ->assertRedirect(route('events.show', $event))
            ->assertSessionHas('success');

        $response = EventResponse::where('event_id', $event->id)->firstOrFail();

        $this->assertSame($this->household->id, $response->household_id);
        $this->assertNull($response->resident_id);

        $this->assertSame(1, Participation::where('event_id', $event->id)
            ->where('household_id', $this->household->id)->count());
    }

    public function test_responding_twice_updates_the_same_household_response(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $event), ['response' => 'going']);

        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $event), ['response' => 'maybe'])
            ->assertRedirect(route('events.show', $event));

        $this->assertSame(1, EventResponse::where('event_id', $event->id)->count());
        $this->assertSame('maybe', EventResponse::where('event_id', $event->id)->firstOrFail()->response);

        // Switching away from "going" drops the auto-generated participation.
        $this->assertSame(0, Participation::where('event_id', $event->id)->count());
    }

    public function test_residents_without_a_household_get_a_clear_error(): void
    {
        $event = $this->makeEvent();
        $this->residentUser->update(['household_id' => null]);

        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $event), ['response' => 'going'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Your account is not linked to a household yet. Please contact the administration.');

        $this->assertSame(0, EventResponse::where('event_id', $event->id)->count());
    }

    public function test_event_show_displays_responding_households_by_block_and_lot(): void
    {
        $event = $this->makeEvent();

        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $event), ['response' => 'going']);

        $this->actingAs($this->residentUser)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('1 responded', false)
            ->assertSee('Block 1, Lot 2', false)
            ->assertSee('(Kimberly Lacbayo)', false)
            ->assertSee('eventResponsesModal', false);
    }

    public function test_announcement_responses_also_work_without_resident_profiles(): void
    {
        $announcement = \App\Models\Announcement::create([
            'village_id' => $this->village->id,
            'title' => 'Water Interruption',
            'category' => 'general',
            'description' => 'No water on Saturday.',
            'status' => 'published',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->residentUser)
            ->post(route('announcements.respond', $announcement), ['response' => 'going'])
            ->assertRedirect(route('announcements.show', $announcement))
            ->assertSessionHas('success');

        $response = \App\Models\AnnouncementResponse::where('announcement_id', $announcement->id)->firstOrFail();

        $this->assertSame($this->household->id, $response->household_id);
        $this->assertNull($response->resident_id);
    }
}
