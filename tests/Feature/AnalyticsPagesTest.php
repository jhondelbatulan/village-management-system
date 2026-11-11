<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalyticsPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        $this->superAdmin = User::create([
            'role_id' => $role->id,
            'username' => 'analyticsadmin',
            'email' => 'analyticsadmin@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Analytics',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);

        $this->village = Village::create([
            'village_name' => 'Test Village',
            'village_code' => 'TV-001',
            'status' => 'active',
        ]);
    }

    public function test_household_analytics_index_loads_for_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('analytics.household.index'));

        $response->assertStatus(200);
    }

    public function test_employment_analytics_index_loads_for_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('analytics.employment.index'));

        $response->assertStatus(200);
    }

    public function test_household_analytics_village_page_loads(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('analytics.household.village', $this->village->id));

        $response->assertStatus(200);
    }

    public function test_employment_analytics_village_page_loads(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('analytics.employment.village', $this->village->id));

        $response->assertStatus(200);
    }

    public function test_other_core_analytics_pages_load(): void
    {
        $routes = [
            'analytics.population.index',
            'analytics.demographic.index',
            'analytics.education.index',
            'analytics.occupation.index',
            'analytics.skills.index',
            'analytics.executive-intelligence.index',
        ];

        foreach ($routes as $name) {
            $response = $this->actingAs($this->superAdmin)->get(route($name));
            $response->assertStatus(200, "Route [$name] failed with status {$response->getStatusCode()}.");
            $response->assertSee('/assets/js/chart.umd.js');
            $response->assertDontSee('cdn.jsdelivr.net/npm/chart.js');
        }
    }

    public function test_local_chartjs_asset_is_available_for_analytics_pages(): void
    {
        $this->assertFileExists(public_path('assets/js/chart.umd.js'));
        $this->assertStringContainsString('Chart.js v4.4.0', file_get_contents(public_path('assets/js/chart.umd.js')));
    }

    public function test_executive_intelligence_csv_export_runs(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('analytics.executive-intelligence.export', 'csv'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
    }
}
