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
}
