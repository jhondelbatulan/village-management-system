<?php

namespace Tests\Feature;

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

class EventAutoStatusTest extends TestCase
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

    public function test_creating_an_event_with_a_future_date_sets_status_upcoming(): void
    {
        $this->actingAs($this->admin)->post(route('events.store'), [
            'village_id' => $this->village->id,
            'title' => 'Future Event',
            'category' => 'gathering',
            'event_date' => now()->addWeek()->toDateString(),
        ])->assertRedirect(route('events.index'));

        $this->assertSame('upcoming', Event::where('title', 'Future Event')->firstOrFail()->status);
    }

    public function test_creating_an_event_today_within_its_hours_sets_status_ongoing(): void
    {
        $this->travelTo(now()->setTime(10, 0));

        $this->actingAs($this->admin)->post(route('events.store'), [
            'village_id' => $this->village->id,
            'title' => 'Today Event',
            'category' => 'gathering',
            'event_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ])->assertRedirect(route('events.index'));

        $this->assertSame('ongoing', Event::where('title', 'Today Event')->firstOrFail()->status);
    }

    public function test_creating_an_event_today_before_its_start_time_sets_status_upcoming(): void
    {
        $this->travelTo(now()->setTime(10, 0));

        $this->actingAs($this->admin)->post(route('events.store'), [
            'village_id' => $this->village->id,
            'title' => 'Later Today',
            'category' => 'gathering',
            'event_date' => now()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '20:00',
        ])->assertRedirect(route('events.index'));

        $this->assertSame('upcoming', Event::where('title', 'Later Today')->firstOrFail()->status);
    }

    public function test_creating_an_event_with_a_past_date_sets_status_ongoing(): void
    {
        $this->actingAs($this->admin)->post(route('events.store'), [
            'village_id' => $this->village->id,
            'title' => 'Past Event',
            'category' => 'gathering',
            'event_date' => now()->subWeek()->toDateString(),
        ])->assertRedirect(route('events.index'));

        $this->assertSame('ongoing', Event::where('title', 'Past Event')->firstOrFail()->status);
    }

    public function test_creating_an_event_today_after_its_end_time_stays_ongoing(): void
    {
        $this->travelTo(now()->setTime(18, 0));

        $this->actingAs($this->admin)->post(route('events.store'), [
            'village_id' => $this->village->id,
            'title' => 'Finished Today',
            'category' => 'gathering',
            'event_date' => now()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
        ])->assertRedirect(route('events.index'));

        $this->assertSame('ongoing', Event::where('title', 'Finished Today')->firstOrFail()->status);
    }

    public function test_a_non_creator_cannot_complete_or_cancel_someone_elses_event(): void
    {
        $event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Someone Elses Event',
            'category' => 'community',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->secretary)
            ->patch(route('events.complete', $event))
            ->assertForbidden();

        $this->actingAs($this->secretary)
            ->patch(route('events.cancel', $event))
            ->assertForbidden();

        $this->assertSame('upcoming', $event->fresh()->status);
    }

    public function test_super_admin_cannot_complete_or_cancel_another_creators_event(): void
    {
        $superAdmin = $this->makeUser('super_admin');

        $event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Admins Event',
            'category' => 'community',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($superAdmin)
            ->patch(route('events.complete', $event))
            ->assertForbidden();

        $this->assertSame('upcoming', $event->fresh()->status);
    }
    public function test_complete_button_marks_the_event_completed_and_generates_participations(): void
    {
        $event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Cleanup Drive',
            'category' => 'community',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        $event->responses()->create([
            'household_id' => $this->household->id,
            'response' => 'going',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('events.complete', $event))
            ->assertRedirect(route('events.show', $event));

        $this->assertSame('completed', $event->fresh()->status);
        $this->assertSame(1, Participation::where('event_id', $event->id)->count());
    }

    public function test_residents_cannot_use_the_complete_or_cancel_buttons(): void
    {
        $event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Protected Event',
            'category' => 'community',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->residentUser)
            ->patch(route('events.complete', $event))
            ->assertForbidden();

        $this->actingAs($this->residentUser)
            ->patch(route('events.cancel', $event))
            ->assertForbidden();

        $this->assertSame('upcoming', $event->fresh()->status);
    }

    public function test_cancel_button_cancels_the_event_and_cancellation_sticks_on_edit(): void
    {
        $event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Cancelable Event',
            'category' => 'community',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('events.cancel', $event))
            ->assertRedirect(route('events.show', $event));

        $this->assertSame('cancelled', $event->fresh()->status);

        $this->actingAs($this->admin)->put(route('events.update', $event), [
            'title' => 'Cancelable Event',
            'category' => 'community',
            'event_date' => now()->addDays(5)->toDateString(),
        ])->assertRedirect(route('events.index'));

        $this->assertSame('cancelled', $event->fresh()->status);
    }
}
