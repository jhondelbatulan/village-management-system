<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Models\VillagePhoto;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VillageLoginBackgroundTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> Files written to the public disk for a test. */
    private array $createdFiles = [];

    private Village $villageA;

    private Village $villageB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->villageA = Village::create([
            'village_name' => 'Comagascas',
            'village_code' => 'CGS-001',
            'cover_photo' => 'village_covers/test-comagascas.jpg',
            'status' => 'active',
        ]);

        $this->villageB = Village::create([
            'village_name' => 'Bagong Silang',
            'village_code' => 'BGS-002',
            'status' => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            Storage::disk('public')->delete($path);
        }

        $this->createdFiles = [];

        parent::tearDown();
    }

    private function fakeCover(string $path): void
    {
        Storage::disk('public')->put($path, 'fake-image-bytes');
        $this->createdFiles[] = $path;
    }

    public function test_login_page_shows_uploaded_village_covers_as_background(): void
    {
        $this->fakeCover($this->villageA->cover_photo);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(asset('storage/village_covers/test-comagascas.jpg'), false);
    }

    public function test_login_page_rotates_between_several_village_covers(): void
    {
        $this->fakeCover($this->villageA->cover_photo);
        $this->fakeCover('village_covers/test-bagongsilang.jpg');
        $this->villageB->update(['cover_photo' => 'village_covers/test-bagongsilang.jpg']);

        $response = $this->get(route('login'))->assertOk();

        $response->assertSee(asset('storage/village_covers/test-comagascas.jpg'), false);
        $response->assertSee(asset('storage/village_covers/test-bagongsilang.jpg'), false);
        $response->assertSee('auth-background-layer');
        $response->assertSee('setInterval');
        $response->assertDontSee('carousel');
    }

    public function test_login_page_shows_photos_added_to_the_village_gallery(): void
    {
        $this->fakeCover('village_photos/gallery-shot.jpg');

        VillagePhoto::create([
            'village_id' => $this->villageA->id,
            'photo_path' => 'village_photos/gallery-shot.jpg',
            'caption' => 'Barangay hall',
            'sort_order' => 0,
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(asset('storage/village_photos/gallery-shot.jpg'), false)
            ->assertSee('auth-background-layer');
    }

    public function test_gallery_photos_of_inactive_villages_are_not_used(): void
    {
        $this->fakeCover('village_photos/inactive-village-shot.jpg');

        VillagePhoto::create([
            'village_id' => $this->villageA->id,
            'photo_path' => 'village_photos/inactive-village-shot.jpg',
            'sort_order' => 0,
        ]);

        $this->villageA->update(['status' => 'inactive']);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(asset('storage/village_photos/inactive-village-shot.jpg'), false);
    }

    public function test_covers_of_inactive_villages_are_not_used(): void
    {
        $this->fakeCover($this->villageA->cover_photo);
        $this->villageA->update(['status' => 'inactive']);

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(asset('storage/village_covers/test-comagascas.jpg'), false);
    }

    public function test_missing_cover_files_are_skipped_instead_of_rendering_broken_images(): void
    {
        // The DB row exists, but the file was never uploaded (or was lost).
        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee(asset('storage/village_covers/test-comagascas.jpg'), false)
            ->assertSee(asset(config('app_settings.assets.login_background')), false);
    }

    public function test_login_page_falls_back_to_the_default_background_when_no_cover_exists(): void
    {
        $this->villageA->update(['cover_photo' => null]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(asset(config('app_settings.assets.login_background')), false);
    }

    public function test_dashboard_village_photos_crossfade_instead_of_sliding(): void
    {
        $role = Role::where('role_slug', 'admin')->firstOrFail();

        $admin = User::create([
            'role_id' => $role->id,
            'village_id' => $this->villageA->id,
            'username' => 'fadeadmin',
            'email' => 'fadeadmin@gmail.com',
            'password' => 'SecretPassword1',
            'first_name' => 'Fade',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);

        VillagePhoto::create([
            'village_id' => $this->villageA->id,
            'photo_path' => 'village_photos/test-fade.jpg',
            'caption' => 'Cover shot',
            'sort_order' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard.admin'))
            ->assertOk()
            ->assertSee('carousel slide carousel-fade')
            ->assertDontSee('data-bs-interval="3000"');
    }
}
