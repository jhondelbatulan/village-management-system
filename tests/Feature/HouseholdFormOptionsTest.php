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

class HouseholdFormOptionsTest extends TestCase
{
    use RefreshDatabase;

    private User $secretary;

    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Form Village', 'village_code' => 'FMV']);

        $role = Role::where('role_slug', 'secretary')->firstOrFail();

        $this->secretary = User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'username' => 'secretary_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Sara',
            'last_name' => 'Secretary',
            'status' => 'active',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'village_id' => $this->village->id,
            'block_number' => '3',
            'lot_number' => '9',
            'household_head_name' => 'juan dela cruz',
            'address' => 'Purok 2, Sample Street',
            'house_status' => 'concrete',
            'household_type' => '3',
            'house_ownership' => 'Owned',
            'registration_date' => '2000-01-01',
            'status' => 'active',
        ], $overrides);
    }

    public function test_family_count_accepts_one_to_five_and_rejects_other_values(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['household_type' => '6']))
            ->assertSessionHasErrors('household_type');

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['household_type' => 'Single-Family']))
            ->assertSessionHasErrors('household_type');

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['household_type' => '1']))
            ->assertSessionHasNoErrors();

        $this->assertSame('1', Household::firstOrFail()->household_type);
    }

    public function test_house_material_rejects_legacy_status_values(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['house_status' => 'owned']))
            ->assertSessionHasErrors('house_status');

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['house_status' => 'wood_timber']))
            ->assertSessionHasNoErrors();

        $this->assertSame('wood_timber', Household::firstOrFail()->house_status);
    }

    public function test_house_ownership_rejects_removed_options(): void
    {
        foreach (['Rented', 'Inherited', 'Other'] as $removed) {
            $this->actingAs($this->secretary)
                ->post(route('households.store'), $this->payload([
                    'house_ownership' => $removed,
                    'block_number' => (string) random_int(10, 99),
                ]))
                ->assertSessionHasErrors('house_ownership');
        }

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['house_ownership' => 'Leased']))
            ->assertSessionHasNoErrors();

        $this->assertSame('Leased', Household::firstOrFail()->house_ownership);
    }

    public function test_create_form_renders_the_new_house_information_options(): void
    {
        $response = $this->actingAs($this->secretary)->get(route('households.create'));

        $response->assertOk();
        $response->assertSee('Family Count', false);
        $response->assertSee('House Material', false);
        $response->assertSee('Wood/Timber', false);
        $response->assertDontSee('Select Status', false);
        $response->assertDontSee('>Rented<', false);
    }
}
