<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class RejectionDataPurgeTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> Files written to the public disk for a test. */
    private array $createdFiles = [];

    private User $admin;

    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Purge Village', 'village_code' => 'PRG']);

        $role = Role::where('role_slug', 'admin')->firstOrFail();

        $this->admin = User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'username' => 'purgeadmin_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->createdFiles = [];

        parent::tearDown();
    }

    private function fakeFile(string $path): string
    {
        Storage::disk('public')->put($path, 'fake-image-bytes');
        $this->createdFiles[] = $path;

        return $path;
    }

    private function makeHousehold(string $approvalStatus = 'pending'): Household
    {
        return Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '2',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => $approvalStatus,
            'submitted_by' => $this->admin->id,
        ]);
    }

    private function makeResident(string $approvalStatus = 'pending', ?Household $household = null): Resident
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
            'submitted_by' => $this->admin->id,
        ]);
    }

    public function test_rejecting_a_household_removes_the_household_and_every_resident_in_it(): void
    {
        $household = $this->makeHousehold('pending');
        $head = $this->makeResident('pending', $household);
        $member = $this->makeResident('approved', $household);

        $member->update(['profile_picture' => $this->fakeFile('profile_pictures/purge-member.jpg')]);

        $this->actingAs($this->admin)
            ->post(route('approvals.reject-household', [$household, 'remarks' => 'Incomplete documents']))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('households', ['id' => $household->id]);
        $this->assertDatabaseMissing('residents', ['id' => $head->id]);
        $this->assertDatabaseMissing('residents', ['id' => $member->id]);
        $this->assertSame(0, Household::where('approval_status', 'rejected')->count());
        $this->assertSame(0, Resident::where('approval_status', 'rejected')->count());
        Storage::disk('public')->assertMissing('profile_pictures/purge-member.jpg');
    }

    public function test_rejecting_a_resident_removes_the_resident_and_its_files(): void
    {
        $resident = $this->makeResident('pending');
        $resident->update(['profile_picture' => $this->fakeFile('profile_pictures/purge-resident.jpg')]);

        $this->actingAs($this->admin)
            ->post(route('approvals.reject-resident', [$resident, 'remarks' => 'Duplicate record']))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
        $this->assertSame(0, Resident::where('approval_status', 'rejected')->count());
        Storage::disk('public')->assertMissing('profile_pictures/purge-resident.jpg');
    }

    public function test_rejected_records_left_by_older_versions_are_purged_by_the_cleanup_migration(): void
    {
        $rejectedHousehold = $this->makeHousehold('rejected');
        $member = $this->makeResident('approved', $rejectedHousehold);
        $standalone = $this->makeResident('rejected');

        $migration = require base_path('database/migrations/2026_11_09_000001_purge_rejected_households_and_residents.php');
        $migration->up();

        $this->assertDatabaseMissing('households', ['id' => $rejectedHousehold->id]);
        $this->assertDatabaseMissing('residents', ['id' => $member->id]);
        $this->assertDatabaseMissing('residents', ['id' => $standalone->id]);

        // Records that were never rejected survive the cleanup.
        $approvedHousehold = $this->makeHousehold('approved');
        $approvedResident = $this->makeResident('approved', $approvedHousehold);

        $migration->up();

        $this->assertDatabaseHas('households', ['id' => $approvedHousehold->id]);
        $this->assertDatabaseHas('residents', ['id' => $approvedResident->id]);
    }

    public function test_rejected_request_disappears_from_the_approval_queue_and_dashboard_counts(): void
    {
        $household = $this->makeHousehold('pending');
        $resident = $this->makeResident('pending', $household);

        $this->actingAs($this->admin)
            ->post(route('approvals.reject-household', [$household, 'remarks' => 'Invalid lot']))
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('approvals.households'))
            ->assertOk()
            ->assertViewHas('households', fn ($view) => $view->pluck('id')->doesntContain($household->id));

        $this->actingAs($this->admin)
            ->get(route('approvals.residents'))
            ->assertOk()
            ->assertViewHas('residents', fn ($view) => $view->pluck('id')->doesntContain($resident->id));

        $this->assertSame(0, Household::whereIn('approval_status', ['pending', 'rejected'])->count());
        $this->assertSame(0, Resident::whereIn('approval_status', ['pending', 'rejected'])->count());
    }
}
