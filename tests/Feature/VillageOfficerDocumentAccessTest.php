<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Models\VillageDocument;
use App\Models\VillageOfficer;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

class VillageOfficerDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    private Village $village;

    private Village $otherVillage;

    private User $superAdmin;

    private User $admin;

    private User $secretary;

    private User $treasurer;

    private User $resident;

    private User $foreignAdmin;

    private User $foreignSecretary;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->village = Village::forceCreate(['village_name' => 'Officers Village', 'village_code' => 'OPV']);
        $this->otherVillage = Village::forceCreate(['village_name' => 'Other Village', 'village_code' => 'OTV']);

        $this->superAdmin = $this->makeUser('super_admin');
        $this->admin = $this->makeUser('admin', $this->village->id);
        $this->secretary = $this->makeUser('secretary', $this->village->id);
        $this->treasurer = $this->makeUser('treasurer', $this->village->id);
        $this->resident = $this->makeUser('resident', $this->village->id);
        $this->foreignAdmin = $this->makeUser('admin', $this->otherVillage->id);
        $this->foreignSecretary = $this->makeUser('secretary', $this->otherVillage->id);
    }

    private function makeUser(string $roleSlug, ?int $villageId = null): User
    {
        $role = Role::where('role_slug', $roleSlug)->firstOrFail();

        return User::create([
            'role_id' => $role->id,
            'village_id' => $villageId,
            'username' => $roleSlug . '_' . Str::random(6),
            'email' => Str::random(10) . '@gmail.com',
            'password' => 'secret-password',
            'first_name' => 'Test',
            'last_name' => ucfirst($roleSlug),
            'status' => 'active',
        ]);
    }

    private function officerPayload(array $overrides = []): array
    {
        return array_merge([
            'position' => 'Barangay Captain',
            'full_name' => 'Juan Dela Cruz',
            'status' => 'active',
        ], $overrides);
    }

    public function test_super_admin_can_view_officers_but_cannot_add_update_or_remove(): void
    {
        $officer = VillageOfficer::create([
            'village_id' => $this->village->id,
            'position' => 'Kagawad',
            'full_name' => 'Existing Officer',
            'status' => 'active',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('villages.officers.index', $this->village))
            ->assertOk()
            ->assertDontSee('Add Officer')
            ->assertSee('Existing Officer');

        $this->actingAs($this->superAdmin)
            ->post(route('villages.officers.store', $this->village), $this->officerPayload())
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->put(route('villages.officers.update', [$this->village, $officer]), $this->officerPayload([
                'full_name' => 'Renamed Officer',
            ]))
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->delete(route('villages.officers.destroy', [$this->village, $officer]))
            ->assertForbidden();

        $this->assertSame(1, VillageOfficer::count());
        $this->assertSame('Existing Officer', $officer->fresh()->full_name);
    }

    public function test_own_village_admin_and_secretary_can_add_an_officer(): void
    {
        foreach ([$this->admin, $this->secretary] as $user) {
            $this->actingAs($user)
                ->get(route('villages.officers.index', $this->village))
                ->assertOk()
                ->assertSee('Add Officer');

            $this->actingAs($user)
                ->post(route('villages.officers.store', $this->village), $this->officerPayload([
                    'full_name' => 'Officer Of ' . $user->id,
                ]))
                ->assertRedirect(route('villages.officers.index', $this->village));
        }

        $this->assertSame(2, VillageOfficer::count());
    }

    public function test_cross_village_admin_and_secretary_cannot_view_or_manage_officers(): void
    {
        foreach ([$this->foreignAdmin, $this->foreignSecretary] as $user) {
            $this->actingAs($user)
                ->get(route('villages.officers.index', $this->village))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('villages.officers.store', $this->village), $this->officerPayload())
                ->assertForbidden();
        }

        $this->assertSame(0, VillageOfficer::count());
    }

    public function test_treasurer_and_resident_cannot_access_the_officers_page(): void
    {
        foreach ([$this->treasurer, $this->resident] as $user) {
            $this->actingAs($user)
                ->get(route('villages.officers.index', $this->village))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('villages.officers.store', $this->village), $this->officerPayload())
                ->assertForbidden();
        }
    }

    public function test_secretary_can_update_and_remove_an_officer(): void
    {
        $officer = VillageOfficer::create([
            'village_id' => $this->village->id,
            'position' => 'Treasurer',
            'full_name' => 'Old Name',
            'status' => 'active',
        ]);

        $this->actingAs($this->secretary)
            ->put(route('villages.officers.update', [$this->village, $officer]), $this->officerPayload([
                'position' => 'Secretary',
                'full_name' => 'New Name',
            ]))
            ->assertRedirect(route('villages.officers.index', $this->village));

        $this->assertSame('New Name', $officer->fresh()->full_name);

        $this->actingAs($this->secretary)
            ->delete(route('villages.officers.destroy', [$this->village, $officer]))
            ->assertRedirect(route('villages.officers.index', $this->village));

        $this->assertDatabaseMissing('village_officers', ['id' => $officer->id]);
    }

    public function test_super_admin_can_view_documents_but_cannot_upload_or_delete(): void
    {
        $document = VillageDocument::create([
            'village_id' => $this->village->id,
            'title' => 'Existing Document',
            'file_path' => 'village_documents/existing.pdf',
            'file_type' => 'pdf',
            'uploaded_by' => $this->admin->id,
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('villages.documents.index', $this->village))
            ->assertOk()
            ->assertDontSee('Upload Document')
            ->assertSee('Existing Document');

        $this->actingAs($this->superAdmin)
            ->post(route('villages.documents.store', $this->village), [
                'title' => 'Super Upload',
                'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            ])
            ->assertForbidden();

        $this->actingAs($this->superAdmin)
            ->delete(route('villages.documents.destroy', [$this->village, $document]))
            ->assertForbidden();

        $this->assertSame(1, VillageDocument::count());
    }

    public function test_own_village_admin_and_secretary_can_upload_documents(): void
    {
        foreach ([$this->admin, $this->secretary] as $user) {
            $this->actingAs($user)
                ->get(route('villages.documents.index', $this->village))
                ->assertOk()
                ->assertSee('Upload Document');

            $this->actingAs($user)
                ->post(route('villages.documents.store', $this->village), [
                    'title' => 'Document Of ' . $user->id,
                    'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                ])
                ->assertRedirect(route('villages.documents.index', $this->village));
        }

        $this->assertSame(2, VillageDocument::count());
    }

    public function test_cross_village_admin_and_secretary_cannot_view_or_upload_documents(): void
    {
        foreach ([$this->foreignAdmin, $this->foreignSecretary] as $user) {
            $this->actingAs($user)
                ->get(route('villages.documents.index', $this->village))
                ->assertForbidden();

            $this->actingAs($user)
                ->post(route('villages.documents.store', $this->village), [
                    'title' => 'Foreign Upload',
                    'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
                ])
                ->assertForbidden();
        }

        $this->assertSame(0, VillageDocument::count());
    }

    public function test_treasurer_and_resident_cannot_access_the_documents_page(): void
    {
        foreach ([$this->treasurer, $this->resident] as $user) {
            $this->actingAs($user)
                ->get(route('villages.documents.index', $this->village))
                ->assertForbidden();
        }
    }
}
