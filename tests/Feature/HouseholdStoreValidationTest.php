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

class HouseholdStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $secretary;

    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Validation Village', 'village_code' => 'VLV']);

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
            'block_number' => '7',
            'lot_number' => '12',
            'household_head_name' => 'juan dela cruz',
            'address' => 'Purok 1, Main Street',
            'house_status' => 'concrete',
            'registration_date' => '2000-01-01',
            'status' => 'active',
        ], $overrides);
    }

    public function test_block_and_lot_number_only_accept_numbers(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['block_number' => '12A']))
            ->assertSessionHasErrors('block_number');

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['lot_number' => 'Lot 2']))
            ->assertSessionHasErrors('lot_number');

        $this->assertDatabaseCount('households', 0);
    }

    public function test_household_head_name_only_accepts_letters(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['household_head_name' => 'Juan Dela Cruz 2nd']))
            ->assertSessionHasErrors('household_head_name');

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['household_head_name' => 'Dela-Cruz, Juan']))
            ->assertSessionHasErrors('household_head_name');

        $this->assertDatabaseCount('households', 0);
    }

    public function test_household_head_name_is_auto_capitalized(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['household_head_name' => '  juan   dela   cruz  ']))
            ->assertRedirect(route('households.index'));

        $household = Household::firstOrFail();

        $this->assertSame('Juan Dela Cruz', $household->household_head_name);
        $this->assertSame('7', $household->block_number);
        $this->assertSame('12', $household->lot_number);
    }

    public function test_registration_date_is_not_editable_on_create(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['registration_date' => '2000-01-01']))
            ->assertRedirect(route('households.index'));

        $household = Household::firstOrFail();

        $this->assertSame(now()->toDateString(), $household->registration_date->toDateString());
    }

    public function test_duplicate_block_and_lot_in_same_village_is_rejected(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload())
            ->assertSessionHasErrors('block_number');

        $this->assertDatabaseCount('households', 1);
    }

    public function test_duplicate_block_and_lot_including_archived_households_is_rejected(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload())
            ->assertSessionHasNoErrors();

        Household::firstOrFail()->delete();

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload())
            ->assertSessionHasErrors('block_number');

        $this->assertDatabaseCount('households', 1);
    }

    public function test_leading_zeros_are_normalized_so_zero_seven_duplicates_seven(): void
    {
        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload([
                'block_number' => '07',
                'lot_number' => '012',
            ]))
            ->assertSessionHasErrors('block_number');

        $this->assertDatabaseCount('households', 1);
    }

    public function test_duplicate_block_and_lot_in_a_different_village_is_allowed(): void
    {
        $otherVillage = Village::forceCreate(['village_name' => 'Other Validation Village', 'village_code' => 'VLW']);

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload())
            ->assertSessionHasNoErrors();

        $this->actingAs($this->secretary)
            ->post(route('households.store'), $this->payload(['village_id' => $otherVillage->id]))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('households', 2);
    }

    public function test_create_form_renders_field_constraints(): void
    {
        $response = $this->actingAs($this->secretary)->get(route('households.create'));

        $response->assertOk();
        $response->assertSee('Numbers only.', false);
        $response->assertSee('Letters only. First letter of every word is capitalized automatically.', false);
        $response->assertSee('Cannot be edited.', false);
        $response->assertSee("replace(/[^0-9]/g, '')", false);

        $this->assertMatchesRegularExpression(
            '/<input type="date" name="registration_date"[^>]*readonly/',
            $response->getContent()
        );
    }
}
