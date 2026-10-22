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

class AnnouncementVisibilityTest extends TestCase
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
            'village_name' => 'Visibility Village',
            'village_code' => 'VSV',
        ]);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
        $this->residentUser = $this->makeUser('resident', $this->village->id);

        $household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '2',
            'household_head_name' => 'Resident Head',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);

        $this->residentUser->forceFill(['household_id' => $household->id])->save();
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

    private function storeAs(User $user, array $overrides = [])
    {
        return $this->actingAs($user)->post(route('announcements.store'), array_merge([
            'village_id' => $this->village->id,
            'title' => 'General Notice',
            'category' => 'General',
            'description' => 'Notice body',
            'status' => 'published',
        ], $overrides));
    }

    public function test_super_admin_can_limit_an_announcement_to_specific_roles(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Staff Briefing',
            'visible_roles' => ['admin', 'secretary'],
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Staff Briefing')->firstOrFail();

        $this->assertSame(['admin', 'secretary'], $announcement->visible_roles);

        $this->actingAs($this->admin)->get(route('announcements.index'))->assertOk()->assertSee('Staff Briefing');
        $this->actingAs($this->secretary)->get(route('announcements.index'))->assertOk()->assertSee('Staff Briefing');
        $this->actingAs($this->treasurer)->get(route('announcements.index'))->assertOk()->assertDontSee('Staff Briefing');
        $this->actingAs($this->residentUser)->get(route('announcements.index'))->assertOk()->assertDontSee('Staff Briefing');
        $this->actingAs($this->superAdmin)->get(route('announcements.index'))->assertOk()->assertSee('Staff Briefing');
    }

    public function test_public_announcement_is_visible_to_every_role(): void
    {
        $this->storeAs($this->superAdmin, ['title' => 'Village Fair Notice'])
            ->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Village Fair Notice')->firstOrFail();
        $this->assertNull($announcement->visible_roles);

        foreach ([$this->superAdmin, $this->admin, $this->secretary, $this->treasurer, $this->residentUser] as $user) {
            $this->actingAs($user)
                ->get(route('announcements.index'))
                ->assertOk()
                ->assertSee('Village Fair Notice');
        }
    }

    public function test_show_page_is_forbidden_for_roles_not_selected(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Admin Strategy Meet',
            'visible_roles' => ['admin'],
        ]);

        $announcement = Announcement::where('title', 'Admin Strategy Meet')->firstOrFail();

        $this->actingAs($this->admin)->get(route('announcements.show', $announcement))->assertOk();
        $this->actingAs($this->treasurer)->get(route('announcements.show', $announcement))->assertForbidden();
        $this->actingAs($this->secretary)->get(route('announcements.show', $announcement))->assertForbidden();
        $this->actingAs($this->residentUser)->get(route('announcements.show', $announcement))->assertForbidden();
    }

    public function test_creator_keeps_access_to_their_own_restricted_announcement(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Super Admin Note For Admins',
            'visible_roles' => ['admin'],
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Super Admin Note For Admins')->firstOrFail();

        $this->actingAs($this->superAdmin)->get(route('announcements.show', $announcement))->assertOk();
        $this->actingAs($this->admin)->get(route('announcements.show', $announcement))->assertOk();
        $this->actingAs($this->secretary)->get(route('announcements.show', $announcement))->assertForbidden();
        $this->actingAs($this->treasurer)->get(route('announcements.show', $announcement))->assertForbidden();
    }

    public function test_invalid_visibility_role_is_rejected(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Bad Visibility',
            'visible_roles' => ['super_admin'],
        ])->assertSessionHasErrors('visible_roles.0');

        $this->assertDatabaseMissing('announcements', ['title' => 'Bad Visibility']);
    }

    public function test_public_checkbox_value_is_stored_as_public(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Explicit Public',
            'visible_roles' => ['public'],
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::where('title', 'Explicit Public')->firstOrFail();
        $this->assertNull($announcement->visible_roles);
    }

    public function test_announcement_hidden_from_residents_does_not_notify_residents(): void
    {
        $this->storeAs($this->admin, [
            'title' => 'Admin Only Notice',
            'visible_roles' => ['admin'],
        ])->assertRedirect(route('announcements.index'));

        $this->assertSame(0, $this->notificationsFor($this->residentUser));

        $this->storeAs($this->admin, ['title' => 'Everyone Notice'])
            ->assertRedirect(route('announcements.index'));

        $this->assertGreaterThan(0, $this->notificationsFor($this->residentUser));
    }

    public function test_edit_form_shows_the_saved_visibility_selection(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Restricted Draft',
            'status' => 'draft',
            'visible_roles' => ['admin', 'treasurer'],
        ]);

        $announcement = Announcement::where('title', 'Restricted Draft')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->get(route('announcements.edit', $announcement))
            ->assertOk()
            ->assertSee('id="visible_admin" checked', false)
            ->assertSee('id="visible_treasurer" checked', false)
            ->assertDontSee('id="visible_public" checked', false)
            ->assertDontSee('id="visible_resident" checked', false);
    }

    public function test_public_edit_form_has_public_checked(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Public Draft',
            'status' => 'draft',
        ]);

        $announcement = Announcement::where('title', 'Public Draft')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->get(route('announcements.edit', $announcement))
            ->assertOk()
            ->assertSee('id="visible_public" checked', false);
    }

    public function test_updating_an_announcement_can_change_visibility(): void
    {
        $this->storeAs($this->superAdmin, [
            'title' => 'Evolving Notice',
            'visible_roles' => ['secretary'],
        ]);

        $announcement = Announcement::where('title', 'Evolving Notice')->firstOrFail();
        $this->assertSame(['secretary'], $announcement->visible_roles);

        $this->actingAs($this->superAdmin)->put(route('announcements.update', $announcement), [
            'title' => 'Evolving Notice',
            'category' => 'General',
            'status' => 'published',
            'visible_roles' => ['public'],
        ])->assertRedirect(route('announcements.index'));

        $this->assertNull($announcement->fresh()->visible_roles);

        $this->actingAs($this->treasurer)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('Evolving Notice');
    }

    public function test_create_form_shows_the_role_visibility_selector(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('announcements.create'))
            ->assertOk()
            ->assertSee('Who Can View')
            ->assertSee('id="visible_public" checked', false)
            ->assertSee('id="visible_admin"', false)
            ->assertSee('id="visible_secretary"', false)
            ->assertSee('id="visible_treasurer"', false)
            ->assertSee('id="visible_resident"', false);
    }

    private function notificationsFor(User $user): int
    {
        return \DB::table('notifications')->where('user_id', $user->id)->count();
    }
}
