<?php

namespace Tests\Feature;

use App\Models\Household;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResidentStoreValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $secretary;

    private Village $village;

    private Household $household;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Resident Validation Village', 'village_code' => 'RVV']);

        $role = Role::where('role_slug', 'secretary')->firstOrFail();

        $this->secretary = User::create([
            'role_id' => $role->id,
            'village_id' => $this->village->id,
            'username' => 'sec_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'Secretary',
            'status' => 'active',
        ]);

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '1',
            'lot_number' => '3',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => $this->secretary->id,
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'household_id' => $this->household->id,
            'first_name' => 'juan',
            'middle_name' => 'd',
            'last_name' => 'dela cruz',
            'suffix' => '',
            'gender' => 'Male',
            'birthdate' => '1990-05-15',
            'civil_status' => 'Single',
            'nationality' => 'Filipino',
            'status' => 'active',
        ], $overrides);
    }

    private function postResident(array $overrides = [])
    {
        return $this->actingAs($this->secretary)->post(route('residents.store'), $this->payload($overrides));
    }

    public function test_names_are_auto_capitalized_and_suffix_is_normalized(): void
    {
        $this->postResident(['suffix' => 'jr'])->assertRedirect(route('residents.index'));

        $resident = Resident::latest('id')->firstOrFail();

        $this->assertSame('Juan', $resident->first_name);
        $this->assertSame('D', $resident->middle_name);
        $this->assertSame('Dela Cruz', $resident->last_name);
        $this->assertSame('Jr.', $resident->suffix);
    }

    public function test_first_name_must_only_contain_letters(): void
    {
        $this->postResident(['first_name' => 'Juan2'])->assertSessionHasErrors('first_name');
        $this->postResident(['first_name' => 'Juan-Cruz'])->assertSessionHasErrors('first_name');
    }

    public function test_last_name_must_only_contain_letters(): void
    {
        $this->postResident(['last_name' => 'Dela Cruz2'])->assertSessionHasErrors('last_name');
        $this->postResident(['last_name' => "O'Brien"])->assertSessionHasErrors('last_name');
    }

    public function test_middle_name_must_be_a_single_capital_letter(): void
    {
        $this->postResident(['middle_name' => 'Maria'])->assertSessionHasErrors('middle_name');
        $this->postResident(['middle_name' => 'M1'])->assertSessionHasErrors('middle_name');
    }

    public function test_suffix_must_be_one_of_the_allowed_values(): void
    {
        $this->postResident(['suffix' => 'Esq.'])->assertSessionHasErrors('suffix');
        $this->postResident(['suffix' => 'Junior'])->assertSessionHasErrors('suffix');
        $this->postResident(['suffix' => 'III'])->assertSessionHasNoErrors();
    }

    public function test_age_is_calculated_from_the_birthdate(): void
    {
        $this->postResident(['age' => '5'])->assertSessionHasNoErrors();

        $resident = Resident::latest('id')->firstOrFail();

        $this->assertSame(Carbon::parse('1990-05-15')->age, $resident->age);
        $this->assertNotSame(5, $resident->age);
    }

    public function test_create_form_renders_the_age_field_and_suffix_options(): void
    {
        $this->actingAs($this->secretary)
            ->get(route('residents.create'))
            ->assertOk()
            ->assertSee('Auto-calculated from birthdate.')
            ->assertSee('id="age"', false)
            ->assertSee('value="VIII"', false);
    }
}
