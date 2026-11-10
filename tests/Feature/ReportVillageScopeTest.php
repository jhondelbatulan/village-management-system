<?php

namespace Tests\Feature;

use App\Models\GeneratedReport;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportVillageScopeTest extends TestCase
{
    use RefreshDatabase;

    private Village $villageA;

    private Village $villageB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->villageA = Village::create([
            'village_name' => 'Comagascas',
            'village_code' => 'CGS-001',
            'status' => 'active',
        ]);

        $this->villageB = Village::create([
            'village_name' => 'Bagong Silang',
            'village_code' => 'BGS-002',
            'status' => 'active',
        ]);

        $this->makeHousehold($this->villageA, 'HH-ALPHA-01');
        $this->makeHousehold($this->villageB, 'HH-BRAVO-02');
    }

    private function makeHousehold(Village $village, string $number): Household
    {
        return Household::create([
            'village_id' => $village->id,
            'household_number' => $number,
            'block_number' => '1',
            'lot_number' => '1',
            'household_head_name' => 'Head of ' . $number,
            'status' => 'active',
            'approval_status' => 'approved',
        ]);
    }

    private function makeUser(string $roleSlug, Village $village): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $village->id,
            'username' => $roleSlug . '_' . Str::lower($village->village_code),
            'email' => $roleSlug . '_' . Str::lower($village->village_code) . '@gmail.com',
            'password' => 'SecretPassword1',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function makeSuperAdmin(): User
    {
        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'username' => 'superadmin_reports',
            'email' => 'superadmin_reports@gmail.com',
            'password' => 'SecretPassword1',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);
    }

    public function test_super_admin_sees_every_village_and_the_all_villages_option(): void
    {
        $this->actingAs($this->makeSuperAdmin())
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('All Villages')
            ->assertSee($this->villageA->village_name)
            ->assertSee($this->villageB->village_name);
    }

    public function test_village_admin_only_sees_their_own_village(): void
    {
        $this->actingAs($this->makeUser('admin', $this->villageA))
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee($this->villageA->village_name)
            ->assertDontSee($this->villageB->village_name)
            ->assertDontSee('All Villages');
    }

    public function test_village_secretary_only_sees_their_own_village(): void
    {
        $this->actingAs($this->makeUser('secretary', $this->villageB))
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee($this->villageB->village_name)
            ->assertDontSee($this->villageA->village_name)
            ->assertDontSee('All Villages');
    }

    public function test_village_admin_cannot_generate_a_report_for_another_village(): void
    {
        $admin = $this->makeUser('admin', $this->villageA);

        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()])->post(route('reports.generate'), [
            'report_type' => 'household_master_list',
            'format' => 'csv',
            'village_id' => $this->villageB->id,
        ])->assertOk();

        $this->assertDatabaseHas('generated_reports', [
            'user_id' => $admin->id,
            'report_type' => 'household_master_list',
            'village_id' => $this->villageA->id,
        ]);

        $this->assertDatabaseMissing('generated_reports', [
            'user_id' => $admin->id,
            'village_id' => $this->villageB->id,
        ]);
    }

    public function test_village_admin_print_preview_is_scoped_to_their_own_village_data(): void
    {
        $this->actingAs($this->makeUser('admin', $this->villageA))
            ->get(route('reports.print', [
                'report_type' => 'household_master_list',
                'village_id' => $this->villageB->id,
            ]))
            ->assertOk()
            ->assertSee('HH-ALPHA-01')
            ->assertDontSee('HH-BRAVO-02');
    }

    public function test_village_admin_cannot_omit_the_village_to_get_all_villages(): void
    {
        $admin = $this->makeUser('admin', $this->villageA);

        $this->actingAs($admin)->withSession(['auth.password_confirmed_at' => time()])->post(route('reports.generate'), [
            'report_type' => 'household_master_list',
            'format' => 'csv',
        ])->assertOk();

        $this->assertDatabaseHas('generated_reports', [
            'user_id' => $admin->id,
            'village_id' => $this->villageA->id,
        ]);
    }

    public function test_village_treasurer_report_is_scoped_to_their_village(): void
    {
        $treasurer = $this->makeUser('treasurer', $this->villageB);

        $this->actingAs($treasurer)->post(route('reports.generate'), [
            'report_type' => 'household_master_list',
            'format' => 'csv',
            'village_id' => $this->villageA->id,
        ])->assertOk();

        $this->assertDatabaseHas('generated_reports', [
            'user_id' => $treasurer->id,
            'village_id' => $this->villageB->id,
        ]);
    }

    public function test_super_admin_may_generate_across_all_villages(): void
    {
        $superAdmin = $this->makeSuperAdmin();

        $this->actingAs($superAdmin)->withSession(['auth.password_confirmed_at' => time()])->post(route('reports.generate'), [
            'report_type' => 'household_master_list',
            'format' => 'csv',
        ])->assertOk();

        $this->assertDatabaseHas('generated_reports', [
            'user_id' => $superAdmin->id,
            'report_type' => 'household_master_list',
            'village_id' => null,
        ]);

        $this->actingAs($superAdmin)->withSession(['auth.password_confirmed_at' => time()])->post(route('reports.generate'), [
            'report_type' => 'household_master_list',
            'format' => 'csv',
            'village_id' => $this->villageB->id,
        ])->assertOk();

        $this->assertSame(
            1,
            GeneratedReport::where('user_id', $superAdmin->id)
                ->where('village_id', $this->villageB->id)
                ->count()
        );
    }

    public function test_schedule_village_list_is_scoped_to_their_own_village(): void
    {
        $this->actingAs($this->makeUser('admin', $this->villageA))
            ->get(route('reports.schedule'))
            ->assertOk()
            ->assertSee($this->villageA->village_name)
            ->assertDontSee($this->villageB->village_name)
            ->assertDontSee('All Villages');
    }

    public function test_storing_a_schedule_forces_the_admins_own_village(): void
    {
        $admin = $this->makeUser('admin', $this->villageA);

        $this->actingAs($admin)->post(route('reports.schedule.store'), [
            'report_type' => 'collection',
            'village_id' => $this->villageB->id,
            'frequency' => 'monthly',
        ])->assertRedirect(route('reports.schedule'));

        $this->assertDatabaseHas('generated_reports', [
            'user_id' => $admin->id,
            'report_type' => 'collection',
            'village_id' => $this->villageA->id,
            'is_scheduled' => true,
        ]);
    }
}
