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

class RequestMemberValidationTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Household $household;

    private User $resident;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Member Village', 'village_code' => 'MBV']);

        $secretary = Role::where('role_slug', 'secretary')->firstOrFail();

        $this->household = Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-' . Str::upper(Str::random(6)),
            'block_number' => '3',
            'lot_number' => '7',
            'household_head_name' => 'Juan Dela Cruz',
            'status' => 'active',
            'approval_status' => 'approved',
            'submitted_by' => User::create([
                'role_id' => $secretary->id,
                'village_id' => $this->village->id,
                'username' => 'sec_' . Str::random(6),
                'email' => Str::random(10) . '@test.local',
                'password' => 'secret-password',
                'first_name' => 'Test',
                'last_name' => 'Secretary',
                'status' => 'active',
            ])->id,
        ]);

        $residentRole = Role::where('role_slug', 'resident')->firstOrFail();

        $this->resident = User::create([
            'role_id' => $residentRole->id,
            'village_id' => $this->village->id,
            'household_id' => $this->household->id,
            'username' => 'res_' . Str::random(6),
            'email' => Str::random(10) . '@test.local',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => 'Resident',
            'status' => 'active',
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'gender' => 'Male',
            'birthdate' => now()->subYears(25)->toDateString(),
            'civil_status' => 'Single',
        ], $overrides);
    }

    public function test_request_member_form_renders_the_same_validation_fields_as_the_secretary_form(): void
    {
        $this->actingAs($this->resident)
            ->get(route('households.request-member-form'))
            ->assertOk()
            ->assertSee('id="age"', false)
            ->assertSee('Auto-calculated from birthdate.', false)
            ->assertSee('Single capital letter only.', false)
            ->assertSee('member-suffix-options', false)
            ->assertSee('maxlength="1"', false);
    }

    public function test_names_must_only_contain_letters_and_the_suffix_must_be_a_valid_suffix(): void
    {
        $this->actingAs($this->resident)
            ->post(route('households.request-member'), $this->payload([
                'first_name' => 'Juan123',
                'middle_name' => 'Dela Cruz',
                'suffix' => 'Junior',
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors([
                'first_name' => 'First name must only contain letters - no numbers or special characters.',
                'middle_name' => 'Middle name must be a single capital letter (e.g., M).',
                'suffix' => 'Suffix must only be one of: Jr., Sr., II, III, IV, V, VI, VII, VIII, IX, X.',
            ]);

        $this->assertSame(0, Resident::count());
    }

    public function test_special_characters_in_names_are_rejected(): void
    {
        $this->actingAs($this->resident)
            ->post(route('households.request-member'), $this->payload([
                'first_name' => 'Juan-Pablo',
                'last_name' => "O'Brien3",
            ]))
            ->assertRedirect()
            ->assertSessionHasErrors(['first_name', 'last_name']);

        $this->assertSame(0, Resident::count());
    }

    public function test_valid_submission_normalizes_the_name_and_computes_the_age(): void
    {
        $birthDate = now()->subYears(31)->toDateString();

        $this->actingAs($this->resident)
            ->post(route('households.request-member'), $this->payload([
                'first_name' => '  juan   pablo ',
                'middle_name' => 'd',
                'last_name' => 'DELA CRUZ',
                'suffix' => 'jr',
                'birthdate' => $birthDate,
            ]))
            ->assertRedirect(route('households.my-household'));

        $member = Resident::firstOrFail();

        $this->assertSame('Juan Pablo', $member->first_name);
        $this->assertSame('D', $member->middle_name);
        $this->assertSame('Dela Cruz', $member->last_name);
        $this->assertSame('Jr.', $member->suffix);
        $this->assertSame(31, (int) $member->age);
        $this->assertSame('pending', $member->approval_status);
        $this->assertSame($this->household->id, $member->household_id);
        $this->assertSame($this->resident->id, $member->submitted_by);
    }

    public function test_valid_submission_shows_success_flash_and_lists_pending_request_on_my_household(): void
    {
        $this->actingAs($this->resident)
            ->post(route('households.request-member'), $this->payload())
            ->assertRedirect(route('households.my-household'))
            ->assertSessionHas('success');

        $this->actingAs($this->resident)
            ->get(route('households.my-household'))
            ->assertOk()
            ->assertSee('Pending Approval Requests', false)
            ->assertSee('Juan Dela Cruz', false);
    }

    public function test_birthdate_and_gender_remain_required(): void
    {
        $this->actingAs($this->resident)
            ->post(route('households.request-member'), [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'civil_status' => 'Single',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['birthdate', 'gender']);

        $this->assertSame(0, Resident::count());
    }
}
