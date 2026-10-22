<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventResponse;
use App\Models\Household;
use App\Models\Participation;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParticipationMonitoringTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Village $otherVillage;

    private Household $household;

    private Resident $resident;

    private User $residentUser;

    private User $secretary;

    private User $admin;

    private User $superAdmin;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Participation Village', 'village_code' => 'PVV']);
        $this->otherVillage = Village::forceCreate(['village_name' => 'Other Village', 'village_code' => 'OTH']);

        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->superAdmin = $this->makeUser('super_admin');

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '4',
            'lot_number' => '9',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);

        $this->resident = Resident::create([
            'village_id' => $this->village->id,
            'household_id' => $this->household->id,
            'resident_code' => 'RES-' . Str::upper(Str::random(6)),
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);

        $this->residentUser = User::create([
            'role_id' => Role::where('role_slug', 'resident')->firstOrFail()->id,
            'village_id' => $this->village->id,
            'household_id' => $this->household->id,
            'username' => 'res_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'status' => 'active',
        ]);

        $this->event = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Barangay Assembly',
            'category' => 'gathering',
            'event_date' => now()->subDay()->toDateString(),
            'status' => 'completed',
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

    public function test_going_response_records_a_participation_for_the_household(): void
    {
        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'going'])
            ->assertRedirect(route('events.show', $this->event));

        $participation = Participation::where('event_id', $this->event->id)
            ->where('household_id', $this->household->id)
            ->firstOrFail();

        $this->assertSame('present', $participation->status);
        $this->assertNull($participation->resident_id);
        $this->assertSame($this->household->id, $participation->household_id);

        $this->actingAs($this->admin)
            ->get(route('participation.index'))
            ->assertOk()
            ->assertSee('Block 4')
            ->assertSee('Lot 9')
            ->assertDontSee('Juan Dela Cruz')
            ->assertViewHas('householdStats', function ($stats) {
                $stat = collect($stats)->first(fn ($s) => $s['household']->id === $this->household->id);

                return $stat !== null
                    && $stat['participated_count'] === 1
                    && $stat['total_events'] === 1
                    && (float) $stat['participation_pct'] === 100.0;
            });
    }

    public function test_changing_the_response_removes_only_the_auto_generated_participation(): void
    {
        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'going']);

        $this->assertSame(1, Participation::count());

        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'not_going']);

        $this->assertSame(0, Participation::count());

        Participation::create([
            'event_id' => $this->event->id,
            'resident_id' => $this->resident->id,
            'household_id' => $this->household->id,
            'status' => 'absent',
            'remarks' => 'Recorded by secretary',
            'recorded_by' => $this->secretary->id,
        ]);

        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'maybe']);

        $this->assertSame(1, Participation::count());
        $this->assertSame('absent', Participation::first()->status);
    }

    public function test_secretary_can_override_the_auto_generated_participation(): void
    {
        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'going']);

        $this->actingAs($this->secretary)
            ->post(route('events.participations.store', $this->event), [
                'household_id' => $this->household->id,
                'status' => 'absent',
                'remarks' => 'Did not show up',
            ])
            ->assertRedirect(route('events.show', $this->event));

        $participation = Participation::where('event_id', $this->event->id)->firstOrFail();

        $this->assertSame('absent', $participation->status);
        $this->assertSame($this->household->id, $participation->household_id);
        $this->assertSame(1, Participation::count());

        $this->actingAs($this->secretary)
            ->post(route('events.participations.store', $this->event), [
                'household_id' => $this->household->id,
                'status' => 'present',
            ])
            ->assertSessionHasErrors('household_id');
    }

    public function test_manual_participation_is_recorded_per_household(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('events.participations.store', $this->event), [
                'household_id' => $this->household->id,
                'status' => 'present',
                'remarks' => 'Walk-in record',
            ])
            ->assertRedirect(route('events.show', $this->event));

        $participation = Participation::where('event_id', $this->event->id)->firstOrFail();

        $this->assertSame($this->household->id, $participation->household_id);
        $this->assertNull($participation->resident_id);
        $this->assertSame('present', $participation->status);

        $this->actingAs($this->secretary)
            ->post(route('events.participations.store', $this->event), [
                'household_id' => $this->household->id,
                'status' => 'absent',
            ])
            ->assertSessionHasErrors('household_id');

        $this->assertSame(1, Participation::count());
        $this->assertSame('present', Participation::first()->status);
    }

    public function test_auto_generation_creates_one_participation_per_household(): void
    {
        $upcoming = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Health Mission',
            'category' => 'health',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        // Responses are recorded per household, so a household can only
        // respond once per event and maps to exactly one participation.
        EventResponse::create([
            'event_id' => $upcoming->id,
            'household_id' => $this->household->id,
            'response' => 'going',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('events.complete', $upcoming))
            ->assertRedirect(route('events.show', $upcoming));

        $this->assertSame(1, Participation::where('event_id', $upcoming->id)->count());

        $participation = Participation::where('event_id', $upcoming->id)->firstOrFail();

        $this->assertSame($this->household->id, $participation->household_id);
    }

    public function test_household_page_shows_one_participation_row_per_event(): void
    {
        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'going']);

        $secondEvent = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Sports Festival',
            'category' => 'gathering',
            'event_date' => now()->addDays(7)->toDateString(),
            'status' => 'completed',
            'created_by' => $this->secretary->id,
        ]);

        $this->actingAs($this->secretary)
            ->post(route('events.participations.store', $secondEvent), [
                'household_id' => $this->household->id,
                'status' => 'present',
            ])
            ->assertRedirect(route('events.show', $secondEvent));

        $this->assertSame(2, Participation::where('household_id', $this->household->id)->count());

        $this->actingAs($this->superAdmin)
            ->get(route('participation.household', $this->household))
            ->assertOk()
            ->assertViewHas('participations', function ($participations) use ($secondEvent) {
                $eventIds = collect($participations->items())->pluck('event_id');

                return $participations->count() === 2
                    && $eventIds->unique()->count() === 2
                    && $eventIds->contains($this->event->id)
                    && $eventIds->contains($secondEvent->id);
            })
            ->assertSee('Barangay Assembly')
            ->assertSee('Sports Festival');
    }

    public function test_completing_an_event_generates_participations_from_going_responses(): void
    {
        $upcoming = Event::create([
            'village_id' => $this->village->id,
            'title' => 'Youth Cleanup',
            'category' => 'community',
            'event_date' => now()->addDays(3)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->admin->id,
        ]);

        EventResponse::create([
            'event_id' => $upcoming->id,
            'household_id' => $this->household->id,
            'response' => 'going',
        ]);

        $this->assertSame(0, Participation::where('event_id', $upcoming->id)->count());

        $this->actingAs($this->admin)
            ->patch(route('events.complete', $upcoming))
            ->assertRedirect(route('events.show', $upcoming));

        $this->assertSame(1, Participation::where('event_id', $upcoming->id)->count());
    }

    public function test_super_admin_can_drill_down_from_the_comparison_into_a_village(): void
    {
        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'going']);

        $this->actingAs($this->superAdmin)
            ->get(route('participation.index'))
            ->assertOk()
            ->assertSee($this->village->village_name)
            ->assertSee('/participation/village/' . $this->village->id, false);

        $this->actingAs($this->superAdmin)
            ->get(route('participation.village', $this->village))
            ->assertOk()
            ->assertSee('Barangay Assembly')
            ->assertSee('Block 4')
            ->assertSee('Lot 9')
            ->assertDontSee('Juan Dela Cruz')
            ->assertSee('Household Participation')
            ->assertSee(route('participation.household', $this->household), false);

        $this->actingAs($this->superAdmin)
            ->get(route('participation.village', $this->otherVillage))
            ->assertOk()
            ->assertDontSee('Juan Dela Cruz')
            ->assertViewHas('householdStats', fn ($stats) => count($stats) === 0);
    }

    public function test_household_participation_page_shows_block_and_lot_instead_of_names(): void
    {
        $this->actingAs($this->residentUser)
            ->post(route('events.respond', $this->event), ['response' => 'going']);

        $this->actingAs($this->superAdmin)
            ->get(route('participation.household', $this->household))
            ->assertOk()
            ->assertSee('Block 4')
            ->assertSee('Lot 9')
            ->assertDontSee('Juan Dela Cruz')
            ->assertSee('Barangay Assembly');
    }

    public function test_participation_history_shows_block_and_lot_instead_of_household_name(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('participation.resident-history', $this->resident))
            ->assertOk()
            ->assertSee('Block / Lot')
            ->assertSee('Block 4')
            ->assertSee('Lot 9');
    }
}
