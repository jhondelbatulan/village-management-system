<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class GisMapTest extends TestCase
{
    use RefreshDatabase;

    private Village $villageA;

    private Village $villageB;

    private Household $householdA1;

    private Household $householdA2;

    private Household $householdA3;

    private Household $householdB1;

    private User $superAdmin;

    private User $secretary;

    private User $residentA;

    private User $residentNoPin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->villageA = Village::forceCreate([
            'village_name' => 'Alpha Village',
            'village_code' => 'ALPA',
            'latitude' => 9.1000000,
            'longitude' => 125.5000000,
        ]);
        $this->villageB = Village::forceCreate([
            'village_name' => 'Beta Village',
            'village_code' => 'BETB',
            'latitude' => 9.2000000,
            'longitude' => 125.6000000,
        ]);

        $this->householdA1 = $this->makeHousehold($this->villageA, 'VLA-00001', '1', '1', 9.101, 125.501, now()->subDays(3));
        $this->householdA2 = $this->makeHousehold($this->villageA, 'VLA-00002', '1', '2', 9.102, 125.502, now()->subDays(2));
        $this->householdA3 = $this->makeHousehold($this->villageA, 'VLA-00003', '1', '3', null, null, now()->subDay());
        $this->householdB1 = $this->makeHousehold($this->villageB, 'VLB-00001', '1', '1', 9.201, 125.601, now()->subDays(4));

        $this->makeResident($this->householdA1, 'Alphine', 'One', 'approved', 'active');
        $this->makeResident($this->householdA1, 'Alphine', 'Two', 'approved', 'active');
        $this->makeResident($this->householdA1, 'Pendling', 'Three', 'pending', 'active');
        $this->makeResident($this->householdB1, 'Betamine', 'One', 'approved', 'active');

        $this->superAdmin = $this->makeUser('super_admin');
        $this->secretary = $this->makeUser('secretary', $this->villageA->id);
        $this->residentA = $this->makeUser('resident', $this->villageA->id, $this->householdA1);
        $this->residentNoPin = $this->makeUser('resident', $this->villageA->id, $this->householdA3);
    }

    private function makeHousehold(
        Village $village,
        string $number,
        string $block,
        string $lot,
        ?float $lat,
        ?float $lng,
        \DateTimeInterface $createdAt
    ): Household {
        $household = Household::create([
            'village_id' => $village->id,
            'household_number' => $number,
            'block_number' => $block,
            'lot_number' => $lot,
            'household_head_name' => 'Head Of ' . $number,
            'address' => "Block {$block}, Lot {$lot}, {$village->village_name}",
            'latitude' => $lat,
            'longitude' => $lng,
            'household_type' => 'Single-Family',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $household->created_at = $createdAt;
        $household->save();

        return $household;
    }

    private function makeResident(Household $household, string $firstName, string $lastName, string $approval, string $status): Resident
    {
        return Resident::create([
            'village_id' => $household->village_id,
            'household_id' => $household->id,
            'resident_code' => 'RES-' . Str::upper(Str::random(8)),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'gender' => 'Male',
            'status' => $status,
            'approval_status' => $approval,
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

    public function test_super_admin_gis_index_embeds_family_member_counts(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('gis.index'))
            ->assertOk()
            ->assertSee('"member_count":2', false)
            ->assertSee('"member_count":0', false)
            ->assertDontSee('"member_count":3', false);
    }

    public function test_gis_search_returns_member_counts(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('gis.search', ['q' => 'VLA-00001']))
            ->assertOk();

        $this->assertSame(2, $response->json('data.0.member_count'));
    }

    public function test_gis_search_by_resident_name_returns_member_counts(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('gis.search', ['q' => 'Alphine']))
            ->assertOk();

        $this->assertSame(2, $response->json('data.0.member_count'));
    }

    public function test_gis_filter_returns_member_counts(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('gis.filter', ['mapped' => 1]))
            ->assertOk();

        $a1 = collect($response->json('data'))->firstWhere('id', $this->householdA1->id);

        $this->assertNotNull($a1);
        $this->assertSame(2, $a1['member_count']);
    }

    public function test_latest_endpoint_returns_latest_mapped_households_and_forbids_residents(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('gis.latest', ['limit' => 5]))
            ->assertOk()
            ->assertJsonPath('success', true);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertTrue($ids->contains($this->householdA1->id));
        $this->assertTrue($ids->contains($this->householdA2->id));
        $this->assertTrue($ids->contains($this->householdB1->id));
        $this->assertFalse($ids->contains($this->householdA3->id));

        $response = $this->actingAs($this->secretary)
            ->get(route('gis.latest', ['limit' => 5]))
            ->assertOk();

        $secretaryIds = collect($response->json('data'))->pluck('id');
        $this->assertTrue($secretaryIds->contains($this->householdA1->id));
        $this->assertFalse($secretaryIds->contains($this->householdB1->id));

        $this->actingAs($this->residentA)
            ->get(route('gis.latest'))
            ->assertForbidden();
    }

    public function test_resident_gis_map_shows_own_and_neighbor_pinpoints_only(): void
    {
        $this->actingAs($this->residentA)
            ->get(route('gis.index'))
            ->assertOk()
            ->assertSee('VLA-00001', false)
            ->assertSee('VLA-00002', false)
            ->assertSee('Your Household', false)
            ->assertSee('Neighbor', false)
            ->assertDontSee('VLA-00003', false)
            ->assertDontSee('VLB-00001', false)
            ->assertDontSee('Beta Village');
    }

    public function test_resident_without_own_pinpoint_still_sees_neighbors(): void
    {
        $this->actingAs($this->residentNoPin)
            ->get(route('gis.index'))
            ->assertOk()
            ->assertSee('VLA-00001', false)
            ->assertSee('does not have a saved pinpoint yet');
    }

    public function test_household_create_form_embeds_latest_households_and_geocoder(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('households.create'))
            ->assertOk()
            ->assertSee('VLA-00001', false)
            ->assertSee('nominatim', false);
    }

    public function test_super_admin_gis_search_fix_is_applied(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('gis.index'))
            ->assertOk()
            ->assertDontSee('focusSelect', false)
            ->assertSee('nominatim', false)
            ->assertSee('btn-latest', false);
    }

    public function test_village_markers_are_bigger_than_household_markers_on_all_gis_maps(): void
    {
        foreach ([$this->superAdmin, $this->secretary] as $user) {
            $this->actingAs($user)
                ->get(route('gis.index'))
                ->assertOk()
                ->assertSee('width:34px;height:34px;background:#1a73e8', false)
                ->assertSee('width:12px;height:12px;background:', false);
        }

        $this->actingAs($this->residentA)
            ->get(route('gis.index'))
            ->assertOk()
            ->assertSee('width:34px;height:34px;background:#1a73e8', false)
            ->assertSee('width:12px;height:12px;background:', false);
    }

    public function test_secretary_sees_all_village_markers_but_only_own_household_pinpoints(): void
    {
        $page = $this->actingAs($this->secretary)->get(route('gis.index'));

        $page->assertOk()
            ->assertSee('Your Village', false)      // own big marker
            ->assertSee('VLA-00001', false)         // own household pins
            ->assertSee('Beta Village')             // other village's big marker location
            ->assertDontSee('VLB-00001', false)     // other village's household pins
            ->assertDontSee('Head Of VLB-00001', false);
    }

    public function test_gis_maps_default_to_satellite_view_with_a_layer_switcher(): void
    {
        // Esri World Imagery Wayback release WB_2026_R04 — cloud-free over the
        // three villages and available through z18 only.
        $satelliteUrl = 'https://wayback.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/WMTS/1.0.0/GoogleMapsCompatible/MapServer/tile/49059/{z}/{y}/{x}';

        // Super admin sees the system-wide map, the secretary the village map,
        // and the resident their own household map — all three must default to
        // satellite imagery and expose the Satellite/Street layer switcher.
        foreach ([$this->superAdmin, $this->secretary, $this->residentA] as $user) {
            $page = $this->actingAs($user)
                ->get(route('gis.index'))
                ->assertOk()
                ->assertSee($satelliteUrl, false)
                ->assertSee("'Satellite View'", false)
                ->assertSee("'Street Map'", false)
                ->assertSee('L.control.layers', false)
                ->assertSee('satelliteLayer.addTo(map)', false)
                ->assertSee('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', false);

            // Satellite is the base layer actually added to the map first.
            $this->assertMatchesRegularExpression(
                '/satelliteLayer\.addTo\(map\);\s*L\.control\.layers/s',
                $page->getContent()
            );
        }
    }

    public function test_gis_maps_keep_esri_attribution_and_no_api_key_is_required(): void
    {
        $page = $this->actingAs($this->superAdmin)
            ->get(route('gis.index'))
            ->assertOk()
            ->assertSee('Tiles &copy; Esri', false)
            ->assertSee('&copy; OpenStreetMap contributors', false);

        $content = $page->getContent();

        // No invented credentials are baked into the tile URLs.
        $this->assertDoesNotMatchRegularExpression('/Imagery[^\'"]*(api_key|apiKey|token|Token)=/', $content);
    }

    public function test_satellite_layer_stops_at_z18_so_no_missing_tile_message_appears(): void
    {
        // Esri's imagery service returns 404 (rendered as "map data not
        // available") from z19 up, so the satellite layer must cap at z18.
        $page = $this->actingAs($this->superAdmin)
            ->get(route('gis.index'))
            ->assertOk()
            ->assertSee('maxZoom: 18', false);

        $content = $page->getContent();

        $this->assertMatchesRegularExpression(
            '/satelliteLayer\s*=\s*L\.tileLayer\(.*?maxZoom:\s*18/s',
            $content
        );
    }

    public function test_village_apis_never_expose_other_villages_household_locations(): void
    {
        $secretary = $this->actingAs($this->secretary);

        $search = $secretary->get(route('gis.search', ['q' => 'VLB']));
        $search->assertOk();
        $this->assertSame(0, $search->json('count'));

        $filter = $secretary->get(route('gis.filter'));
        $filter->assertOk();
        $this->assertFalse(collect($filter->json('data'))->pluck('id')->contains($this->householdB1->id));

        $latest = $secretary->get(route('gis.latest', ['limit' => 50]));
        $latest->assertOk();
        $this->assertFalse(collect($latest->json('data'))->pluck('id')->contains($this->householdB1->id));

        $villages = $secretary->get(route('gis.villages-api'));
        $villages->assertOk();
        $names = collect($villages->json('villages'))->pluck('village_name');
        $this->assertTrue($names->contains('Alpha Village'));
        $this->assertTrue($names->contains('Beta Village'));
    }

    public function test_secretary_household_map_picker_autolocates_to_their_village(): void
    {
        $page = $this->actingAs($this->secretary)->get(route('households.create'));

        $page->assertOk()
            ->assertSee('value="' . $this->villageA->id . '" selected', false)
            ->assertSee('loadReferenceMarkers(hasExistingPin)', false)
            ->assertSee('"household_number":"VLA-00001"', false)
            ->assertDontSee('"household_number":"VLB-00001"', false);
    }

    public function test_household_create_map_picker_defaults_to_satellite_with_layer_switcher(): void
    {
        $page = $this->actingAs($this->secretary)->get(route('households.create'));

        $page->assertOk()
            ->assertSee('wayback.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/WMTS/1.0.0/GoogleMapsCompatible/MapServer/tile/49059/{z}/{y}/{x}', false)
            ->assertSee("'Satellite View'", false)
            ->assertSee("'Street Map'", false)
            ->assertSee('L.control.layers', false)
            ->assertSee('satelliteLayer.addTo(pickerMap)', false)
            ->assertSee('&copy; OpenStreetMap contributors', false);

        $content = $page->getContent();

        // Satellite must be the base layer actually added to the picker map.
        $this->assertMatchesRegularExpression(
            '/satelliteLayer\.addTo\(pickerMap\);\s*L\.control\.layers/s',
            $content
        );

        // The satellite layer is capped at z18: Wayback has no z19 tiles, and
        // requesting them produced the "map data not available" message.
        $this->assertMatchesRegularExpression(
            '/satelliteLayer\s*=\s*L\.tileLayer\(.*?maxZoom:\s*18/s',
            $content
        );

        // Existing pin-dropping and reference markers are untouched.
        $page->assertSee('var pickerMarker = null;', false);
    }

    public function test_household_edit_map_falls_back_to_village_mapped_coordinates(): void
    {
        $unmapped = $this->makeHousehold($this->villageA, 'VLA-00004', '2', '1', null, null, now());

        $page = $this->actingAs($this->secretary)->get(route('households.edit', $unmapped));

        $page->assertOk()
            ->assertSee('var latVal = ' . $this->villageA->latitude . ';', false)
            ->assertSee('var lngVal = ' . $this->villageA->longitude . ';', false)
            ->assertDontSee('var latVal = 9.2372;', false)
            ->assertDontSee('"household_number":"VLB-00001"', false);
    }

    public function test_village_edit_coordinate_map_defaults_to_satellite_with_layer_switcher(): void
    {
        // Editing a village's coordinates must offer the same satellite view
        // (Esri Wayback 49059, capped at z18) with the live layer switcher as
        // every other map in the system.
        $page = $this->actingAs($this->superAdmin)->get(route('villages.edit', $this->villageA));

        $page->assertOk()
            ->assertSee('wayback.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/WMTS/1.0.0/GoogleMapsCompatible/MapServer/tile/49059/{z}/{y}/{x}', false)
            ->assertSee("'Satellite View'", false)
            ->assertSee("'Street Map'", false)
            ->assertSee('L.control.layers', false)
            ->assertSee('satelliteLayer.addTo(pickerMap)', false)
            ->assertSee('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', false)
            ->assertSee('Tiles &copy; Esri', false);

        $content = $page->getContent();

        // Satellite is the base layer actually added to the picker map first.
        $this->assertMatchesRegularExpression(
            '/satelliteLayer\.addTo\(pickerMap\);\s*L\.control\.layers/s',
            $content
        );

        // The satellite layer stops at z18 so no "map data not available"
        // placeholder ever appears when zooming in on a village coordinate.
        $this->assertMatchesRegularExpression(
            '/satelliteLayer\s*=\s*L\.tileLayer\(.*?maxZoom:\s*18/s',
            $content
        );

        // The pickable coordinate inputs are untouched.
        $page->assertSee('id="latitude"', false)
            ->assertSee('id="longitude"', false);
    }

    public function test_village_create_coordinate_map_defaults_to_satellite_with_layer_switcher(): void
    {
        // Registering a village pins its location through the same satellite
        // picker (Esri Wayback 49059, capped at z18) used when editing one.
        $page = $this->actingAs($this->superAdmin)->get(route('villages.create'));

        $page->assertOk()
            ->assertSee('wayback.maptiles.arcgis.com/arcgis/rest/services/World_Imagery/WMTS/1.0.0/GoogleMapsCompatible/MapServer/tile/49059/{z}/{y}/{x}', false)
            ->assertSee("'Satellite View'", false)
            ->assertSee("'Street Map'", false)
            ->assertSee('L.control.layers', false)
            ->assertSee('satelliteLayer.addTo(pickerMap)', false)
            ->assertSee('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', false)
            ->assertSee('Tiles &copy; Esri', false)
            ->assertSee('id="village-map-picker"', false);

        $content = $page->getContent();

        $this->assertMatchesRegularExpression(
            '/satelliteLayer\.addTo\(pickerMap\);\s*L\.control\.layers/s',
            $content
        );

        $this->assertMatchesRegularExpression(
            '/satelliteLayer\s*=\s*L\.tileLayer\(.*?maxZoom:\s*18/s',
            $content
        );

        // Coordinates come from the map only, so the inputs stay read-only.
        $page->assertSee('id="latitude"', false)
            ->assertSee('id="longitude"', false);

        $this->assertMatchesRegularExpression(
            '/id="latitude"[^>]*readonly/s',
            $content
        );
        $this->assertMatchesRegularExpression(
            '/id="longitude"[^>]*readonly/s',
            $content
        );
    }
}
