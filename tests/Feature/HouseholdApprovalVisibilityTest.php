<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class HouseholdApprovalVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private Household $approved;

    private Household $draft;

    private Household $pending;

    private Household $adminApproved;

    private Household $rejected;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate([
            'village_name' => 'Visibility Village',
            'village_code' => 'VLB',
            'latitude' => 9.1000000,
            'longitude' => 125.5000000,
        ]);

        $this->approved = $this->makeHousehold('VIV-00001', '1', '1', 'approved', 'Approved Person', 9.101, 125.501);
        $this->draft = $this->makeHousehold('VIV-00002', '1', '2', 'draft', 'Draft Person', 9.102, 125.502);
        $this->pending = $this->makeHousehold('VIV-00003', '1', '3', 'pending', 'Pending Person', 9.103, 125.503);
        $this->adminApproved = $this->makeHousehold('VIV-00004', '1', '4', 'admin_approved', 'Adminapproved Person', 9.104, 125.504);
        $this->rejected = $this->makeHousehold('VIV-00005', '1', '5', 'rejected', 'Rejected Person', 9.105, 125.505);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
    }

    private function makeHousehold(
        string $number,
        string $block,
        string $lot,
        string $approvalStatus,
        string $headName,
        float $lat,
        float $lng
    ): Household {
        return Household::create([
            'village_id' => $this->village->id,
            'household_number' => $number,
            'block_number' => $block,
            'lot_number' => $lot,
            'household_head_name' => $headName,
            'address' => "Block {$block}, Lot {$lot}, Visibility Village",
            'latitude' => $lat,
            'longitude' => $lng,
            'household_type' => 'Single-Family',
            'status' => 'active',
            'approval_status' => $approvalStatus,
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

    private function listedHouseholdIds(User $user, array $query = []): array
    {
        $response = $this->actingAs($user)->get(route('households.index', $query));
        $response->assertStatus(200);

        return $response->viewData('households')->pluck('id')->all();
    }

    public function test_household_list_only_contains_super_admin_approved_households(): void
    {
        foreach ([$this->superAdmin, $this->admin, $this->secretary] as $user) {
            $this->assertSame(
                [$this->approved->id],
                $this->listedHouseholdIds($user),
                "The household list leaked unapproved records for role {$user->role?->role_slug}."
            );
        }

        $this->actingAs($this->superAdmin)
            ->get(route('households.index'))
            ->assertSee('Approved Person')
            ->assertDontSee('Draft Person')
            ->assertDontSee('Pending Person')
            ->assertDontSee('Adminapproved Person')
            ->assertDontSee('Rejected Person');
    }

    public function test_approval_status_query_parameter_cannot_reveal_unapproved_households(): void
    {
        $this->assertNotContains(
            $this->draft->id,
            $this->listedHouseholdIds($this->superAdmin, ['approval_status' => 'draft'])
        );

        $this->assertNotContains(
            $this->pending->id,
            $this->listedHouseholdIds($this->secretary, ['approval_status' => 'pending'])
        );
    }

    public function test_freshly_created_household_is_hidden_from_the_list(): void
    {
        $response = $this->actingAs($this->secretary)->post(route('households.store'), [
            'village_id' => $this->village->id,
            'block_number' => '9',
            'lot_number' => '9',
            'household_head_name' => 'Newly Created Person',
            'address' => 'Purok 9, New Street',
            'house_status' => 'concrete',
        ]);

        $created = Household::where('household_head_name', 'Newly Created Person')->firstOrFail();

        $this->assertSame('draft', $created->approval_status);
        $this->assertSame('active', $created->status);

        // The secretary lands on the record itself so it can still be submitted.
        $response->assertRedirect(route('households.show', $created));

        $this->assertNotContains($created->id, $this->listedHouseholdIds($this->superAdmin));
        $this->assertNotContains($created->id, $this->listedHouseholdIds($this->secretary));
    }

    public function test_household_stays_hidden_until_the_super_admin_final_approval(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.submit', $this->draft))
            ->assertRedirect(route('households.show', $this->draft));

        $this->assertSame('pending', $this->draft->fresh()->approval_status);
        $this->assertNotContains($this->draft->id, $this->listedHouseholdIds($this->superAdmin));

        $this->actingAs($this->admin)->post(route('approvals.approve-household', $this->draft), [
            'approval_status' => 'approved',
        ]);

        $this->assertSame('admin_approved', $this->draft->fresh()->approval_status);
        $this->assertNotContains($this->draft->id, $this->listedHouseholdIds($this->superAdmin));

        $this->actingAs($this->superAdmin)->post(route('approvals.approve-household', $this->draft), [
            'approval_status' => 'approved',
        ]);

        $this->assertSame('approved', $this->draft->fresh()->approval_status);
        $this->assertContains($this->draft->id, $this->listedHouseholdIds($this->superAdmin));

        // Final approval moves the record out of the approval queue and into history.
        $queueIds = $this->actingAs($this->superAdmin)
            ->get(route('approvals.households'))
            ->viewData('households')
            ->pluck('id')
            ->all();

        $this->assertNotContains($this->draft->id, $queueIds);

        $history = $this->actingAs($this->superAdmin)
            ->get(route('approvals.households', ['view' => 'history']))
            ->assertOk()
            ->assertSee('History of Approved Households');

        $this->assertContains($this->draft->id, $history->viewData('households')->pluck('id')->all());
    }

    public function test_secretary_still_manages_unapproved_households_from_the_approvals_queue(): void
    {
        $secretaryQueue = $this->actingAs($this->secretary)
            ->get(route('approvals.households'))
            ->assertStatus(200)
            ->viewData('households')
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $expected = collect([
            $this->draft->id,
            $this->pending->id,
            $this->adminApproved->id,
            $this->rejected->id,
        ])->sort()->values()->all();

        $this->assertSame($expected, $secretaryQueue);

        $adminQueue = $this->actingAs($this->admin)
            ->get(route('approvals.households'))
            ->viewData('households')
            ->pluck('id')
            ->all();

        $this->assertSame([$this->pending->id], $adminQueue);

        $superAdminQueue = $this->actingAs($this->superAdmin)
            ->get(route('approvals.households'))
            ->viewData('households')
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $expectedSuperAdmin = collect([
            $this->adminApproved->id,
            $this->rejected->id,
        ])->sort()->values()->all();

        $this->assertSame($expectedSuperAdmin, $superAdminQueue);
    }

    public function test_household_export_only_contains_approved_households(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('households.export'));
        $response->assertStatus(200);

        $content = $response->streamedContent();

        $this->assertStringContainsString('VIV-00001', $content);
        $this->assertStringNotContainsString('VIV-00002', $content);
        $this->assertStringNotContainsString('VIV-00004', $content);
    }

    public function test_gis_map_only_shows_households_approved_by_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('gis.index'))
            ->assertStatus(200)
            ->assertSee('Approved Person')
            ->assertDontSee('Draft Person')
            ->assertDontSee('Pending Person')
            ->assertDontSee('Adminapproved Person')
            ->assertDontSee('Rejected Person');
    }

    public function test_gis_apis_table_and_export_only_return_approved_households(): void
    {
        $latest = $this->actingAs($this->superAdmin)->get(route('gis.latest', ['limit' => 50]));
        $latest->assertStatus(200);
        $this->assertSame(['VIV-00001'], collect($latest->json('data'))->pluck('household_number')->all());

        $search = $this->actingAs($this->superAdmin)->get(route('gis.search', ['q' => 'VIV']));
        $search->assertStatus(200);
        $this->assertSame(['VIV-00001'], collect($search->json('data'))->pluck('household_number')->all());

        $filter = $this->actingAs($this->superAdmin)->get(route('gis.filter'));
        $filter->assertStatus(200);
        $this->assertSame(['VIV-00001'], collect($filter->json('data'))->pluck('household_number')->all());

        $pendingFilter = $this->actingAs($this->superAdmin)->get(route('gis.filter', ['approval_status' => 'pending']));
        $pendingFilter->assertStatus(200);
        $this->assertSame(0, $pendingFilter->json('count'));

        $table = $this->actingAs($this->superAdmin)->get(route('gis.households', ['village_id' => $this->village->id]));
        $table->assertStatus(200);
        $this->assertSame([$this->approved->id], $table->viewData('households')->pluck('id')->all());

        $export = $this->actingAs($this->superAdmin)->get(route('gis.export'));
        $export->assertStatus(200);
        $export->assertSee('VIV-00001')->assertDontSee('VIV-00002');
    }

    public function test_gis_latest_saved_markers_are_built_from_approved_households_only(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('households.create'))
            ->assertStatus(200)
            ->assertSee('VIV-00001')
            ->assertDontSee('VIV-00002')
            ->assertDontSee('VIV-00004');
    }

    public function test_resident_does_not_get_an_unapproved_household_location_on_gis(): void
    {
        $this->makeHousehold('VIV-00006', '2', '1', 'approved', 'Neighboring Person', 9.106, 125.506);

        $resident = $this->makeUser('resident', $this->village->id, $this->draft);

        $page = $this->actingAs($resident)->get(route('gis.index'));
        $page->assertStatus(200);
        $page->assertSee('awaiting final approval');
        $page->assertDontSee('9.102');
        $page->assertDontSee('125.502');

        $search = $this->actingAs($resident)->get(route('gis.search', ['q' => 'VIV']));
        $search->assertStatus(200);
        $this->assertSame(0, $search->json('count'));

        $filter = $this->actingAs($resident)->get(route('gis.filter'));
        $filter->assertStatus(200);
        $this->assertSame(0, $filter->json('count'));
    }

    public function test_secretary_menu_has_a_draft_households_button(): void
    {
        $secretaryPage = $this->actingAs($this->secretary)->get(route('households.index'));
        $secretaryPage->assertStatus(200);
        $secretaryPage->assertSee('Draft Households');
        $secretaryPage->assertSee('approvals/households?status=draft', false);

        $adminPage = $this->actingAs($this->admin)->get(route('households.index'));
        $adminPage->assertStatus(200);
        $adminPage->assertDontSee('Draft Households');
    }

    public function test_secretary_draft_households_button_lands_on_draft_records_only(): void
    {
        $response = $this->actingAs($this->secretary)
            ->get(route('approvals.households', ['status' => 'draft']));

        $response->assertStatus(200);
        $response->assertSee('Draft Records');
        $response->assertSee('Draft Person');
        $response->assertDontSee('Pending Person');
        $response->assertDontSee('Adminapproved Person');
        $response->assertDontSee('Rejected Person');

        $this->assertSame(
            [$this->draft->id],
            $response->viewData('households')->pluck('id')->all()
        );
    }

    public function test_approved_records_leave_the_approval_records_section_for_the_history_section(): void
    {
        $main = $this->actingAs($this->superAdmin)->get(route('approvals.households'));
        $main->assertOk()->assertSee('All Approval Records');
        $mainIds = $main->viewData('households')->pluck('id')->all();

        $this->assertNotContains($this->approved->id, $mainIds);
        $this->assertContains($this->adminApproved->id, $mainIds);
        $this->assertContains($this->rejected->id, $mainIds);

        $history = $this->actingAs($this->superAdmin)
            ->get(route('approvals.households', ['view' => 'history']))
            ->assertOk()
            ->assertSee('History of Approved Households');

        $this->assertSame([$this->approved->id], $history->viewData('households')->pluck('id')->all());

        // The old ?status=approved link must land on the history section too.
        $viaStatus = $this->actingAs($this->superAdmin)
            ->get(route('approvals.households', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('History of Approved Households');

        $this->assertSame([$this->approved->id], $viaStatus->viewData('households')->pluck('id')->all());
    }

    public function test_approved_history_is_village_scoped_for_admin_and_secretary(): void
    {
        $otherVillage = Village::forceCreate([
            'village_name' => 'Other Village',
            'village_code' => 'OTH',
            'latitude' => 9.2000000,
            'longitude' => 125.6000000,
        ]);

        $otherApproved = Household::create([
            'village_id' => $otherVillage->id,
            'household_number' => 'OTH-00001',
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Other Approved Person',
            'address' => 'Block 1, Lot 1, Other Village',
            'latitude' => 9.201,
            'longitude' => 125.601,
            'household_type' => 'Single-Family',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        foreach ([$this->admin, $this->secretary] as $user) {
            $historyIds = $this->actingAs($user)
                ->get(route('approvals.households', ['view' => 'history']))
                ->assertOk()
                ->viewData('households')
                ->pluck('id')
                ->all();

            $this->assertSame(
                [$this->approved->id],
                $historyIds,
                "The approved history leaked other villages' records for role {$user->role?->role_slug}."
            );
        }

        $superAdminHistory = $this->actingAs($this->superAdmin)
            ->get(route('approvals.households', ['view' => 'history']))
            ->viewData('households')
            ->pluck('id')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(
            collect([$this->approved->id, $otherApproved->id])->sort()->values()->all(),
            $superAdminHistory
        );
    }
}
