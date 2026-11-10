<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Notification;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Workflow Village', 'village_code' => 'WFV']);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
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

    private function makeHousehold(string $approvalStatus, array $overrides = []): Household
    {
        return Household::create(array_merge([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '2',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => $approvalStatus,
            'submitted_by' => $this->secretary->id,
        ], $overrides));
    }

    private function makeResident(string $approvalStatus, ?Household $household = null): Resident
    {
        return Resident::create([
            'village_id' => $this->village->id,
            'household_id' => $household?->id,
            'resident_code' => 'RES-' . Str::upper(Str::random(6)),
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'gender' => 'Female',
            'status' => 'active',
            'approval_status' => $approvalStatus,
            'submitted_by' => $this->secretary->id,
        ]);
    }

    public function test_pending_submission_is_hidden_from_super_admin_but_visible_to_admin(): void
    {
        $household = $this->makeHousehold('pending');

        $this->actingAs($this->superAdmin)
            ->get(route('approvals.households'))
            ->assertOk()
            ->assertViewHas('households', fn ($view) => $view->pluck('id')->doesntContain($household->id));

        $this->actingAs($this->admin)
            ->get(route('approvals.households'))
            ->assertOk()
            ->assertViewHas('households', fn ($view) => $view->pluck('id')->contains($household->id));

        $resident = $this->makeResident('pending', $household);

        $this->actingAs($this->superAdmin)
            ->get(route('approvals.residents'))
            ->assertOk()
            ->assertViewHas('residents', fn ($view) => $view->pluck('id')->doesntContain($resident->id));

        $this->actingAs($this->admin)
            ->get(route('approvals.residents'))
            ->assertOk()
            ->assertViewHas('residents', fn ($view) => $view->pluck('id')->contains($resident->id));
    }

    public function test_super_admin_cannot_approve_a_pending_record_directly(): void
    {
        $household = $this->makeHousehold('pending');
        $resident = $this->makeResident('pending');

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-household', $household))
            ->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertRedirect();

        $this->assertSame('pending', $household->fresh()->approval_status);
        $this->assertSame('pending', $resident->fresh()->approval_status);
    }

    public function test_secretary_cannot_approve_records(): void
    {
        $resident = $this->makeResident('pending');

        $this->actingAs($this->secretary)
            ->post(route('approvals.approve-resident', $resident))
            ->assertForbidden();

        $this->assertSame('pending', $resident->fresh()->approval_status);
    }

    public function test_second_approval_attempt_is_blocked_as_already_processed(): void
    {
        $household = $this->makeHousehold('pending');
        $resident = $this->makeResident('pending', $household);

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-household', $household))
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-household', $household))
            ->assertSessionHas('error', 'This household request has already been processed.');

        $this->assertSame('admin_approved', $household->fresh()->approval_status);

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertSessionHas('success');

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertSessionHas('error', 'This resident request has already been processed.');

        $this->assertSame('admin_approved', $resident->fresh()->approval_status);

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-household', $household))
            ->assertSessionHas('success');

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-household', $household))
            ->assertSessionHas('error', 'This household request has already been processed.');

        $this->assertSame('approved', $household->fresh()->approval_status);

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertSessionHas('success');

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertSessionHas('error', 'This resident request has already been processed.');

        $this->assertSame('approved', $resident->fresh()->approval_status);
    }

    public function test_residents_approval_page_shows_block_lot_instead_of_household_column(): void
    {
        $household = $this->makeHousehold('pending');
        $this->makeResident('pending', $household);

        $this->actingAs($this->admin)
            ->get(route('approvals.residents'))
            ->assertOk()
            ->assertSee('Block / Lot', false)
            ->assertSee('Block 1 - Lot 2', false);
    }

    public function test_household_flows_through_admin_then_super_admin_approval(): void
    {
        $household = $this->makeHousehold('pending');

        // Stage 1 — Admin approves.
        $this->actingAs($this->admin)
            ->post(route('approvals.approve-household', $household))
            ->assertRedirect()
            ->assertSessionHas('success');

        $household->refresh();

        $this->assertSame('admin_approved', $household->approval_status);
        $this->assertSame($this->admin->id, $household->admin_approved_by);
        $this->assertNull($household->approved_by);

        // Stage 2 — Super Admin finalizes.
        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-household', $household))
            ->assertRedirect()
            ->assertSessionHas('success');

        $household->refresh();

        $this->assertSame('approved', $household->approval_status);
        $this->assertSame($this->superAdmin->id, $household->approved_by);
        $this->assertNotNull($household->approved_at);
    }

    public function test_super_admin_final_approval_auto_activates_the_household(): void
    {
        $household = $this->makeHousehold('pending', ['status' => 'inactive']);

        // Stage 1 — Admin approves but the household stays inactive.
        $this->actingAs($this->admin)
            ->post(route('approvals.approve-household', $household))
            ->assertRedirect()
            ->assertSessionHas('success');

        $household->refresh();

        $this->assertSame('admin_approved', $household->approval_status);
        $this->assertSame('inactive', $household->status);

        // Stage 2 — Super Admin finalizes and the household is activated.
        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-household', $household))
            ->assertRedirect()
            ->assertSessionHas('success');

        $household->refresh();

        $this->assertSame('approved', $household->approval_status);
        $this->assertSame('active', $household->status);
    }

    public function test_super_admin_final_approval_from_the_household_page_auto_activates_the_household(): void
    {
        $household = $this->makeHousehold('admin_approved', ['status' => 'inactive']);

        $this->actingAs($this->superAdmin)
            ->post(route('households.approve', $household), ['approval_status' => 'approved'])
            ->assertRedirect(route('households.show', $household))
            ->assertSessionHas('success');

        $household->refresh();

        $this->assertSame('approved', $household->approval_status);
        $this->assertSame('active', $household->status);
    }

    public function test_resident_flows_through_admin_then_super_admin_approval(): void
    {
        $resident = $this->makeResident('pending');

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertRedirect()
            ->assertSessionHas('success');

        $resident->refresh();

        $this->assertSame('admin_approved', $resident->approval_status);
        $this->assertSame($this->admin->id, $resident->admin_approved_by);

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertRedirect()
            ->assertSessionHas('success');

        $resident->refresh();

        $this->assertSame('approved', $resident->approval_status);
        $this->assertSame($this->superAdmin->id, $resident->approved_by);
    }

    public function test_admin_can_reject_pending_and_super_admin_can_reject_admin_approved(): void
    {
        $household = $this->makeHousehold('pending');
        $member = $this->makeResident('pending', $household);

        $this->actingAs($this->admin)
            ->post(route('approvals.reject-household', [$household, 'remarks' => 'Incomplete documents']))
            ->assertRedirect()
            ->assertSessionHas('success');

        // Rejected requests are removed completely, not kept as "rejected" rows.
        $this->assertDatabaseMissing('households', ['id' => $household->id]);
        $this->assertDatabaseMissing('residents', ['id' => $member->id]);

        $resident = $this->makeResident('pending');

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.reject-resident', [$resident, 'remarks' => 'Duplicate record']))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
    }

    public function test_pending_resident_is_hidden_from_household_until_final_approval(): void
    {
        $household = $this->makeHousehold('approved');
        $approvedResident = $this->makeResident('approved', $household);
        $pendingResident = $this->makeResident('pending', $household);

        $pendingResident->update(['first_name' => 'Pending', 'last_name' => 'Person', 'approval_status' => 'pending']);

        $household->load('residents');

        $this->assertTrue($household->residents->pluck('id')->contains($approvedResident->id));
        $this->assertFalse($household->residents->pluck('id')->contains($pendingResident->id));
        $this->assertCount(1, $household->residents);

        // Resident-facing "My Household" page must not list the pending member
        // as an active household member, but must show it under pending requests
        // so the resident knows the submission went through.
        $this->secretary->update(['household_id' => $household->id]);

        $this->actingAs($this->secretary)
            ->get(route('households.my-household'))
            ->assertOk()
            ->assertSee('Maria Santos', false)
            ->assertSee('Pending Approval Requests', false)
            ->assertSee('Pending Person', false);

        // Now complete the two-stage approval and confirm the member appears.
        $this->actingAs($this->admin)
            ->post(route('approvals.approve-resident', $pendingResident))
            ->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-resident', $pendingResident))
            ->assertRedirect();

        $household->refresh()->load('residents');

        $this->assertSame('approved', $pendingResident->fresh()->approval_status);
        $this->assertTrue($household->residents->pluck('id')->contains($pendingResident->id));
        $this->assertCount(2, $household->residents);

        // Once approved, the pending requests section disappears from the page.
        $this->actingAs($this->secretary)
            ->get(route('households.my-household'))
            ->assertOk()
            ->assertSee('Pending Person', false)
            ->assertDontSee('Pending Approval Requests', false);
    }

    public function test_residents_index_defaults_to_approved_records(): void
    {
        $household = $this->makeHousehold('approved');
        $approvedResident = $this->makeResident('approved', $household);
        $pendingResident = $this->makeResident('pending', $household);

        $this->actingAs($this->admin)
            ->get(route('residents.index'))
            ->assertOk()
            ->assertViewHas('residents', function ($view) use ($approvedResident, $pendingResident) {
                return $view->pluck('id')->contains($approvedResident->id)
                    && ! $view->pluck('id')->contains($pendingResident->id);
            });

        // Request with explicit status filter still shows the requested records.
        $this->actingAs($this->admin)
            ->get(route('residents.index', ['approval_status' => 'pending']))
            ->assertOk()
            ->assertViewHas('residents', fn ($view) => $view->pluck('id')->contains($pendingResident->id));
    }

    public function test_resident_store_rejects_invalid_names(): void
    {
        $household = $this->makeHousehold('approved');

        $base = [
            'household_id' => $household->id,
            'gender' => 'Male',
            'birthdate' => '2000-01-01',
            'civil_status' => 'Single',
            'status' => 'active',
            'suffix' => '',
            'relationship_to_head' => '',
            'educational_attainment' => '',
            'religion' => '',
            'nationality' => '',
            'occupation' => '',
            'contact_number' => '',
            'email' => '',
        ];

        foreach (['JOHN123', 'Ju@n', 'Juan-Cruz', "Juan's", 'Juan2 Santos'] as $badName) {
            $this->actingAs($this->secretary)
                ->post(route('residents.store'), array_merge($base, [
                    'first_name' => $badName,
                    'last_name' => 'Dela Cruz',
                ]))
                ->assertSessionHasErrors('first_name');
        }

        $this->actingAs($this->secretary)
            ->post(route('residents.store'), array_merge($base, [
                'first_name' => 'juan',
                'last_name' => 'dela cruz',
            ]))
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $resident = Resident::latest('id')->firstOrFail();
        $this->assertSame('Juan', $resident->first_name);
        $this->assertSame('Dela Cruz', $resident->last_name);
    }

    public function test_submission_notifications_show_household_context_instead_of_codes(): void
    {
        $household = $this->makeHousehold('draft');

        $this->actingAs($this->secretary)
            ->post(route('households.submit', $household))
            ->assertRedirect()
            ->assertSessionHas('success');

        $householdNotice = Notification::where('user_id', $this->admin->id)->latest('id')->firstOrFail();

        $this->assertStringContainsString('Blk 1', $householdNotice->message);
        $this->assertStringContainsString('Lot 2', $householdNotice->message);
        $this->assertStringContainsString('Juan Dela Cruz', $householdNotice->message);
        $this->assertStringNotContainsString($household->household_number, $householdNotice->message);

        $resident = $this->makeResident('draft', $household);

        $this->actingAs($this->secretary)
            ->post(route('residents.submit', $resident))
            ->assertRedirect()
            ->assertSessionHas('success');

        $residentNotice = Notification::where('user_id', $this->admin->id)
            ->where('message', 'like', '%is added on the household%')
            ->latest('id')
            ->firstOrFail();

        $this->assertStringContainsString('Blk 1', $residentNotice->message);
        $this->assertStringContainsString('Lot 2', $residentNotice->message);
        $this->assertStringContainsString('Juan Dela Cruz', $residentNotice->message);
        $this->assertStringNotContainsString($household->household_number, $residentNotice->message);
        $this->assertStringNotContainsString($resident->resident_code, $residentNotice->message);
    }

    public function test_super_admin_approval_notification_shows_household_context_instead_of_code(): void
    {
        $household = $this->makeHousehold('pending');

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-household', $household))
            ->assertRedirect();

        $superAdminNotice = Notification::where('user_id', $this->superAdmin->id)->latest('id')->firstOrFail();

        $this->assertStringContainsString('Blk 1', $superAdminNotice->message);
        $this->assertStringContainsString('Lot 2', $superAdminNotice->message);
        $this->assertStringContainsString('Juan Dela Cruz', $superAdminNotice->message);
        $this->assertStringNotContainsString($household->household_number, $superAdminNotice->message);

        $pendingNotice = Notification::where('user_id', $this->secretary->id)->latest('id')->firstOrFail();

        $this->assertStringContainsString('Awaiting Super Admin final approval', $pendingNotice->message);
        $this->assertStringNotContainsString($household->household_number, $pendingNotice->message);

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-household', $household))
            ->assertRedirect();

        $finalNotice = Notification::where('user_id', $this->secretary->id)->latest('id')->firstOrFail();

        $this->assertStringContainsString('approved by Super Admin', $finalNotice->message);
        $this->assertStringContainsString('Blk 1', $finalNotice->message);
        $this->assertStringNotContainsString($household->household_number, $finalNotice->message);
    }

    public function test_no_household_approval_notification_contains_the_household_code(): void
    {
        $approved = $this->makeHousehold('pending');
        $rejected = $this->makeHousehold('admin_approved', ['block_number' => '9', 'lot_number' => '9']);

        $this->actingAs($this->admin)
            ->post(route('approvals.approve-household', $approved))
            ->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-household', $approved))
            ->assertRedirect();

        $this->actingAs($this->superAdmin)
            ->post(route('approvals.reject-household', [$rejected, 'remarks' => 'Incomplete address']))
            ->assertRedirect();

        foreach (Notification::whereNotNull('message')->pluck('message') as $message) {
            $this->assertStringNotContainsString($approved->household_number, $message);
            $this->assertStringNotContainsString($rejected->household_number, $message);
        }
    }

    public function test_approval_notifications_reach_only_relevant_roles(): void
    {
        $treasurerRole = Role::where('role_slug', 'treasurer')->firstOrFail();
        $treasurer = User::create([
            'role_id' => $treasurerRole->id,
            'village_id' => $this->village->id,
            'username' => 'treasurer_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'Treasurer',
            'status' => 'active',
        ]);

        $resident = $this->makeResident('pending');

        // Stage 1: admin approves -> super admins + submitter get notified, treasurer does not.
        $this->actingAs($this->admin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertRedirect();

        $this->assertSame(
            1,
            Notification::where('user_id', $this->superAdmin->id)->count(),
            'Super admin must be notified of the new approval pending final decision.'
        );
        $this->assertSame(
            1,
            Notification::where('user_id', $this->secretary->id)->where('message', 'like', '%Awaiting Super Admin final approval%')->count(),
            'Submitter must be informed the record is awaiting final approval.'
        );
        $this->assertSame(0, Notification::where('user_id', $treasurer->id)->count(), 'Treasurer must not receive approval notifications.');

        // Stage 2: super admin finalizes -> submitter gets approved notice.
        $this->actingAs($this->superAdmin)
            ->post(route('approvals.approve-resident', $resident))
            ->assertRedirect();

        $this->assertSame(
            1,
            Notification::where('user_id', $this->secretary->id)->where('message', 'like', '%approved by Super Admin%')->count(),
            'Submitter must be notified once the resident is finally approved.'
        );

        // Rejection by admin -> submitter informed, super admin not notified again.
        $other = $this->makeResident('pending');

        $this->actingAs($this->admin)
            ->post(route('approvals.reject-resident', [$other, 'remarks' => 'Incomplete address']))
            ->assertRedirect();

        $this->assertSame(
            1,
            Notification::where('user_id', $this->secretary->id)->where('message', 'like', '%rejected%')->count(),
            'Submitter must be notified when the record is rejected.'
        );
    }

    public function test_resident_approval_records_leave_the_queue_for_the_history_section(): void
    {
        $household = $this->makeHousehold('approved');
        $approved = $this->makeResident('approved', $household);
        $adminApproved = $this->makeResident('admin_approved', $household);
        $rejected = $this->makeResident('rejected', $household);

        $main = $this->actingAs($this->superAdmin)->get(route('approvals.residents'));
        $main->assertOk()->assertSee('All Approval Records');
        $mainIds = $main->viewData('residents')->pluck('id')->all();

        $this->assertNotContains($approved->id, $mainIds);
        $this->assertContains($adminApproved->id, $mainIds);
        $this->assertContains($rejected->id, $mainIds);

        $history = $this->actingAs($this->superAdmin)
            ->get(route('approvals.residents', ['view' => 'history']))
            ->assertOk()
            ->assertSee('History of Approved Residents');

        $this->assertSame([$approved->id], $history->viewData('residents')->pluck('id')->all());

        // The old ?status=approved link must land on the history section too.
        $viaStatus = $this->actingAs($this->superAdmin)
            ->get(route('approvals.residents', ['status' => 'approved']))
            ->assertOk()
            ->assertSee('History of Approved Residents');

        $this->assertSame([$approved->id], $viaStatus->viewData('residents')->pluck('id')->all());
    }
}
