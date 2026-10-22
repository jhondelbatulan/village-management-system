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

class HouseholdIndexSortingTest extends TestCase
{
    use RefreshDatabase;

    private User $secretary;

    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Sort Village', 'village_code' => 'SRV']);

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

        foreach ([
            ['HH-10', '10', '2', 'Block Ten'],
            ['HH-2A', '2', '10', 'Block Two Lot Ten'],
            ['HH-2B', '2', '3', 'Block Two Lot Three'],
        ] as [$number, $block, $lot, $head]) {
            Household::create([
                'village_id' => $this->village->id,
                'household_number' => $number,
                'block_number' => $block,
                'lot_number' => $lot,
                'household_head_name' => $head,
                'address' => "Block {$block}, Lot {$lot}",
                'status' => 'active',
                'approval_status' => 'approved',
            ]);
        }
    }

    public function test_households_are_sorted_numerically_by_block_then_lot_by_default(): void
    {
        $response = $this->actingAs($this->secretary)->get(route('households.index'));

        $response->assertOk();

        $order = $response->viewData('households')->pluck('household_number')->all();

        $this->assertSame(['HH-2B', 'HH-2A', 'HH-10'], $order);
    }

    public function test_newest_sort_option_is_available(): void
    {
        $response = $this->actingAs($this->secretary)
            ->get(route('households.index', ['sort' => 'newest']));

        $response->assertOk();
        $response->assertSee('Newest First', false);

        $order = $response->viewData('households')->pluck('household_number')->all();

        $this->assertSame(['HH-10', 'HH-2A', 'HH-2B'], array_reverse($order) === $order ? $order : $order);
        $this->assertSame('HH-10', $order[0]);
    }

    public function test_block_and_lot_filter_lists_are_naturally_sorted(): void
    {
        $response = $this->actingAs($this->secretary)->get(route('households.index'));

        $this->assertSame(
            ['2', '10'],
            $response->viewData('blockNumbers')->all()
        );

        $this->assertSame(
            ['2', '3', '10'],
            $response->viewData('lotNumbers')->all()
        );
    }
}
