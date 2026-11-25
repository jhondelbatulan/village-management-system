<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Guards the offline-capable GIS contract:
 *  - essential map/UI assets are served from the application, never a CDN
 *  - satellite tiles for the three villages are shipped with the deployment
 *  - village geography is stored as GeoJSON built from real coordinates
 *  - the map reacts to the browser online/offline state
 */
class OfflineGisTest extends TestCase
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
            'username' => 'superadmin_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);
    }

    public function test_no_view_depends_on_a_cdn_for_essential_assets(): void
    {
        $viewPath = resource_path('views');
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($viewPath, \FilesystemIterator::SKIP_DOTS)
        );

        $offenders = [];
        foreach ($iterator as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            $contents = file_get_contents($file->getPathname());
            if (preg_match('#https://(?:cdn\.jsdelivr\.net|unpkg\.com)[^"\']*#', $contents, $matches)) {
                $offenders[] = str_replace($viewPath, 'views', $file->getPathname()) . ' -> ' . $matches[0];
            }
        }

        $this->assertSame([], $offenders, 'Essential assets must be loaded locally, got: ' . implode(', ', $offenders));
    }

    public function test_layouts_serve_bootstrap_and_icons_locally(): void
    {
        $login = $this->get('/login');

        $login->assertOk()
            ->assertSee('assets/vendor/bootstrap/css/bootstrap.min.css', false)
            ->assertSee('assets/vendor/bootstrap/js/bootstrap.bundle.min.js', false)
            ->assertSee('assets/vendor/bootstrap-icons/bootstrap-icons.min.css', false)
            ->assertDontSee('cdn.jsdelivr.net', false);

        $gis = $this->actingAs($this->superAdmin)->get(route('gis.index'));

        $gis->assertOk()
            ->assertSee('assets/vendor/leaflet/leaflet.css', false)
            ->assertSee('assets/vendor/leaflet/leaflet.js', false)
            ->assertDontSee('unpkg.com', false);
    }

    public function test_gis_map_loads_saved_tiles_and_tracks_connection_state(): void
    {
        $page = $this->actingAs($this->superAdmin)->get(route('gis.index'));

        $page->assertOk()
            // Local offline tile pyramid (z10–z16 satellite imagery).
            ->assertSee('assets/tiles/satellite/{z}/{x}/{y}.jpg', false)
            // The offline layer is offered in the layer switcher.
            ->assertSee('Offline Satellite (Saved)', false)
            // The page swaps to saved tiles when the browser goes offline.
            ->assertSee('mvms:connection', false)
            ->assertSee('applyConnectionState', false)
            // Village geography is loaded from the locally stored GeoJSON.
            ->assertSee('assets/geo/villages.geojson', false)
            ->assertSee('mapped_household_extent', false);
    }

    public function test_navbar_exposes_a_connection_status_indicator(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('dashboard.super_admin'))
            ->assertOk()
            ->assertSee('id="connectionStatus"', false)
            ->assertSee('conn-status', false);
    }

    public function test_offline_tile_pyramid_is_shipped_with_the_application(): void
    {
        $tileRoot = public_path('assets/tiles/satellite');

        $this->assertDirectoryExists($tileRoot);

        $zooms = array_values(array_filter(scandir($tileRoot), fn ($z) => ctype_digit((string) $z)));
        sort($zooms, SORT_NUMERIC);

        // The pyramid must cover the whole navigation range used by the map.
        $this->assertSame(['10', '11', '12', '13', '14', '15', '16'], $zooms);

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($tileRoot, \FilesystemIterator::SKIP_DOTS)
        );
        $count = 0;
        $bytes = 0;
        foreach ($files as $file) {
            if ($file->isFile()) {
                $count++;
                $bytes += $file->getSize();
            }
        }

        // Enough tiles to cover the padded extent of the three villages, and
        // large enough to be real imagery rather than empty placeholders.
        $this->assertGreaterThan(250, $count);
        $this->assertGreaterThan(1024 * 1024, $bytes);
    }

    public function test_village_geojson_is_valid_and_derived_from_recorded_coordinates(): void
    {
        $path = public_path('assets/geo/villages.geojson');

        $this->assertFileExists($path);

        $geojson = json_decode(file_get_contents($path), true);

        $this->assertIsArray($geojson);
        $this->assertSame('FeatureCollection', $geojson['type'] ?? null);
        $this->assertNotEmpty($geojson['features'] ?? []);

        $centers = 0;
        $extents = 0;

        foreach ($geojson['features'] as $feature) {
            $this->assertSame('Feature', $feature['type']);
            $this->assertArrayHasKey('properties', $feature);
            $this->assertArrayHasKey('geometry', $feature);

            $type = $feature['properties']['feature_type'] ?? null;
            $this->assertContains($type, ['village_center', 'mapped_household_extent']);

            if ($type === 'village_center') {
                $centers++;
                $this->assertSame('Point', $feature['geometry']['type']);
                [$lng, $lat] = $feature['geometry']['coordinates'];
                // Real coordinates for Agusan del Norte, Philippines.
                $this->assertGreaterThan(8.0, $lat);
                $this->assertLessThan(10.0, $lat);
                $this->assertGreaterThan(124.0, $lng);
                $this->assertLessThan(127.0, $lng);
            } else {
                $extents++;
                $this->assertSame('Polygon', $feature['geometry']['type']);
                $ring = $feature['geometry']['coordinates'][0];
                $this->assertGreaterThanOrEqual(4, count($ring));
                // Rings must be closed polygons.
                $this->assertSame($ring[0], $ring[count($ring) - 1]);
            }
        }

        // The three covered villages are all present.
        $this->assertSame(3, $centers);
        $this->assertSame(3, $extents);
    }
}
