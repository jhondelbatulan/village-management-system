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

class AllVillageScopeAndParticipationPermissionTest extends TestCase
{
    use RefreshDatabase;

    private Village $villageA;

    private Village $villageB;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    private User $resident;

    private Household $householdA;

    private Household $householdB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->villageA = Village::forceCreate(['village_name' => 'Village Alpha', 'village_code' => 'VLA']);
        $this->villageB = Village::forceCreate(['village_name' => 'Village Beta', 'village_code' => 'VLB']);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->villageA->id);
        $this->secretary = $this->makeUser('secretary', $this->villageA->id);
        $this->treasurer = $this->makeUser('treasurer', $this->villageA->id);
        $this->resident = $this->makeUser('resident', $this->villageA->id);

        $this->householdA = Household::create([
            'village_id' => $this->villageA->id,
            'household_number' => 'HH-AAA-001',
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Aling Santos',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $this->householdB = Household::create([
            'village_id' => $this->villageB->id,
            'household_number' => 'HH-BBB-001',
            'block_number' => '2',
            'lot_number' => '2',
            'household_head_name' => 'Mang Pedro',
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
            'village_id' => 'all',
            'title' => 'System Wide Notice',
            'category' => 'General',
            'description' => 'Notice body',
            'status' => 'published',
        ], $overrides);
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'village_id' => 'all',
            'title' => 'Grand Fun Run',
            'category' => 'sports',
            'event_date' => now()->addWeek()->toDateString(),
            'status' => 'upcoming',
        ], $overrides);
    }

    private function allVillageEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'village_id' => null,
            'title' => 'All Village Assembly',
            'category' => 'gathering',
            'event_date' => now()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'created_by' => $this->superAdmin->id,
        ], $overrides));
    }

    // ===== Items 5 & 6: All Villages targeting =====

    public function test_super_admin_can_create_an_all_villages_event(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('events.store'), $this->eventPayload())
            ->assertRedirect(route('events.index'));

        $event = Event::where('title', 'Grand Fun Run')->firstOrFail();

        $this->assertNull($event->village_id);
    }

    public function test_super_admin_can_create_an_all_villages_announcement(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('announcements.store'), $this->announcementPayload())
            ->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'System Wide Notice')->firstOrFail();

        $this->assertNull($announcement->village_id);
    }

    public function test_village_admin_cannot_target_all_villages(): void
    {
        $this->actingAs($this->admin)
            ->post(route('events.store'), $this->eventPayload())
            ->assertSessionHasErrors('village_id');

        $this->actingAs($this->admin)
            ->post(route('announcements.store'), $this->announcementPayload())
            ->assertSessionHasErrors('village_id');

        $this->assertSame(0, Event::count());
        $this->assertSame(0, Announcement::count());
    }

    public function test_create_forms_offer_the_all_villages_option_to_the_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('events.create'))
            ->assertOk()
            ->assertSee('All Villages (post to every village)');

        $this->actingAs($this->superAdmin)
            ->get(route('announcements.create'))
            ->assertOk()
            ->assertSee('All Villages (post to every village)');
    }

    public function test_all_villages_events_are_visible_to_every_village_role(): void
    {
        $event = $this->allVillageEvent();

        foreach ([$this->admin, $this->secretary, $this->treasurer, $this->resident] as $user) {
            $this->actingAs($user)
                ->get(route('events.index'))
                ->assertOk()
                ->assertSee('All Village Assembly');
        }

        $this->actingAs($this->resident)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('All Villages');
    }

    // ===== Item 4: creator separation =====

    public function test_events_can_be_filtered_by_creator(): void
    {
        Event::create($this->eventPayload([
            'title' => 'Super Created Event',
            'village_id' => null,
            'created_by' => $this->superAdmin->id,
        ]));

        Event::create($this->eventPayload([
            'title' => 'Admin Created Event',
            'village_id' => $this->villageA->id,
            'created_by' => $this->admin->id,
        ]));

        $this->actingAs($this->superAdmin)
            ->get(route('events.index', ['creator' => 'super_admin']))
            ->assertOk()
            ->assertSee('Super Created Event')
            ->assertDontSee('Admin Created Event');

        $this->actingAs($this->superAdmin)
            ->get(route('events.index', ['creator' => 'admin']))
            ->assertOk()
            ->assertSee('Admin Created Event')
            ->assertDontSee('Super Created Event');
    }

    public function test_announcements_can_be_filtered_by_creator(): void
    {
        Announcement::create($this->announcementPayload([
            'title' => 'Super Created Notice',
            'village_id' => null,
            'created_by' => $this->superAdmin->id,
        ]));

        Announcement::create($this->announcementPayload([
            'title' => 'Admin Created Notice',
            'village_id' => $this->villageA->id,
            'created_by' => $this->admin->id,
        ]));

        $this->actingAs($this->superAdmin)
            ->get(route('announcements.index', ['creator' => 'super_admin']))
            ->assertOk()
            ->assertSee('Super Created Notice')
            ->assertDontSee('Admin Created Notice');

        $this->actingAs($this->superAdmin)
            ->get(route('announcements.index', ['creator' => 'admin']))
            ->assertOk()
            ->assertSee('Admin Created Notice')
            ->assertDontSee('Super Created Notice');
    }

    // ===== Item 6: participation permissions =====

    public function test_only_secretaries_can_record_remove_and_auto_generate_participations(): void
    {
        $event = $this->allVillageEvent();

        foreach ([$this->superAdmin, $this->admin] as $user) {
            $this->actingAs($user)
                ->post(route('events.participations.store', $event), [
                    'household_id' => $this->householdA->id,
                    'status' => 'present',
                ])
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('events.auto-generate', $event))
                ->assertForbidden();
        }

        $participation = Participation::create([
            'event_id' => $event->id,
            'household_id' => $this->householdA->id,
            'resident_id' => null,
            'status' => 'present',
        ]);

        foreach ([$this->superAdmin, $this->admin] as $user) {
            $this->actingAs($user)
                ->delete(route('events.participations.destroy', [$event, $participation]))
                ->assertForbidden();
        }

        $this->assertSame(1, Participation::count());

        $this->actingAs($this->secretary)
            ->post(route('events.participations.store', $event), [
                'household_id' => $this->householdA->id,
                'status' => 'present',
            ])
            ->assertSessionHasErrors('household_id');

        $this->actingAs($this->secretary)
            ->delete(route('events.participations.destroy', [$event, $participation]))
            ->assertRedirect(route('events.show', $event));

        $this->assertSame(0, Participation::count());
    }

    public function test_only_secretaries_can_edit_a_participation_record(): void
    {
        $event = $this->allVillageEvent();

        $participation = Participation::create([
            'event_id' => $event->id,
            'household_id' => $this->householdA->id,
            'resident_id' => null,
            'status' => 'present',
            'remarks' => 'First pass',
        ]);

        foreach ([$this->superAdmin, $this->admin] as $user) {
            $this->actingAs($user)
                ->patch(route('events.participations.update', [$event, $participation]), [
                    'status' => 'absent',
                    'remarks' => 'Should not save',
                ])
                ->assertForbidden();
        }

        $this->assertSame('present', $participation->fresh()->status);

        $this->actingAs($this->secretary)
            ->patch(route('events.participations.update', [$event, $participation]), [
                'status' => 'absent',
                'remarks' => 'Verified absent',
            ])
            ->assertRedirect(route('events.show', $event));

        $participation->refresh();
        $this->assertSame('absent', $participation->status);
        $this->assertSame('Verified absent', $participation->remarks);
    }

    public function test_secretary_sees_the_edit_and_delete_controls_on_the_event_page(): void
    {
        $event = $this->allVillageEvent();

        Participation::create([
            'event_id' => $event->id,
            'household_id' => $this->householdA->id,
            'resident_id' => null,
            'status' => 'present',
        ]);

        $this->actingAs($this->secretary)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('Auto-Generate from Responses')
            ->assertSee('editParticipationModal');

        foreach ([$this->superAdmin, $this->admin] as $user) {
            $this->actingAs($user)
                ->get(route('events.show', $event))
                ->assertOk()
                ->assertDontSee('Auto-Generate from Responses')
                ->assertDontSee('editParticipationModal');
        }
    }

    // ===== Item 6: per-village participation filtering =====

    public function test_all_village_event_participations_are_split_per_village(): void
    {
        $event = $this->allVillageEvent();

        Participation::create([
            'event_id' => $event->id,
            'household_id' => $this->householdA->id,
            'resident_id' => null,
            'status' => 'present',
        ]);

        Participation::create([
            'event_id' => $event->id,
            'household_id' => $this->householdB->id,
            'resident_id' => null,
            'status' => 'present',
        ]);

        // Super admin without a filter sees everyone.
        $this->actingAs($this->superAdmin)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('HH-AAA-001')
            ->assertSee('HH-BBB-001');

        // Super admin filtering on one village only sees that village.
        $this->actingAs($this->superAdmin)
            ->get(route('events.show', ['event' => $event->id, 'village' => $this->villageA->id]))
            ->assertOk()
            ->assertSee('HH-AAA-001')
            ->assertDontSee('HH-BBB-001');

        // The secretary of Village Alpha only ever sees Village Alpha.
        $this->actingAs($this->secretary)
            ->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('HH-AAA-001')
            ->assertDontSee('HH-BBB-001');
    }

    // ===== Item 8: attendance PDF =====

    public function test_attendance_pdf_is_downloadable_by_privileged_roles_only(): void
    {
        $event = $this->allVillageEvent();

        Participation::create([
            'event_id' => $event->id,
            'household_id' => $this->householdA->id,
            'resident_id' => null,
            'status' => 'present',
        ]);

        $response = $this->actingAs($this->superAdmin)->get(route('events.attendance-pdf', $event));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));

        $this->actingAs($this->admin)
            ->get(route('events.attendance-pdf', $event))
            ->assertOk();

        $this->actingAs($this->secretary)
            ->get(route('events.attendance-pdf', $event))
            ->assertOk();

        $this->actingAs($this->treasurer)
            ->get(route('events.attendance-pdf', $event))
            ->assertForbidden();

        $this->actingAs($this->resident)
            ->get(route('events.attendance-pdf', $event))
            ->assertForbidden();
    }
}
