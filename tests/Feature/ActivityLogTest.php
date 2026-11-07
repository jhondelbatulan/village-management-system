<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        $this->superAdmin = User::create([
            'role_id' => $role->id,
            'username' => 'logadmin',
            'email' => 'logadmin@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Log',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);
    }

    public function test_activity_logs_page_loads_for_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('system-admin.activity-logs.index'));

        $response->assertStatus(200);
    }

    public function test_subject_column_shows_readable_label_not_json(): void
    {
        $village = Village::create(['village_name' => 'Log Village', 'village_code' => 'LV-001', 'status' => 'active']);
        $household = Household::create([
            'village_id' => $village->id,
            'household_number' => 'HH-SUBJECT-1',
            'block_number' => '9',
            'lot_number' => '9',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        ActivityLog::create([
            'user_id' => $this->superAdmin->id,
            'role' => 'super_admin',
            'action' => 'Created',
            'module' => 'Households',
            'subject_type' => Household::class,
            'subject_id' => $household->id,
            'description' => 'Household created: HH-SUBJECT-1',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('system-admin.activity-logs.index'));

        $response->assertStatus(200);
        // The subject label (household number) must be shown...
        $response->assertSee('HH-SUBJECT-1');
        // ...and the raw JSON model dump must NOT appear.
        $response->assertDontSee('{"id"', false);
    }

    public function test_subject_label_accessor_resolves_household(): void
    {
        $village = Village::create(['village_name' => 'Log Village 2', 'village_code' => 'LV-002', 'status' => 'active']);
        $household = Household::create([
            'village_id' => $village->id,
            'household_number' => 'HH-LABEL-2',
            'block_number' => '8',
            'lot_number' => '8',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $log = ActivityLog::create([
            'user_id' => $this->superAdmin->id,
            'role' => 'super_admin',
            'action' => 'Updated',
            'module' => 'Households',
            'subject_type' => Household::class,
            'subject_id' => $household->id,
            'description' => 'Household updated: HH-LABEL-2',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ]);

        $this->assertSame('HH-LABEL-2', $log->fresh()->subject_label);
    }
}
