<?php

namespace Tests\Feature;

use App\Models\BackupRecord;
use App\Models\Household;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BackupTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $role = Role::where('role_slug', 'super_admin')->firstOrFail();

        $this->superAdmin = User::create([
            'role_id' => $role->id,
            'username' => 'backupadmin',
            'email' => 'backupadmin@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Backup',
            'last_name' => 'Admin',
            'status' => 'active',
        ]);

        $this->village = Village::create([
            'village_name' => 'Backup Village',
            'village_code' => 'BV-001',
            'status' => 'active',
        ]);
    }

    public function test_backups_index_loads_for_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('system-admin.backups.index'));

        $response->assertStatus(200);
    }

    public function test_create_backup_produces_completed_record(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('system-admin.backups.create'));

        $response->assertSessionHas('success');

        $record = BackupRecord::latest('id')->first();
        $this->assertNotNull($record);
        $this->assertSame('completed', $record->status);
        $this->assertGreaterThan(0, $record->size_bytes);
        $this->assertFileExists($record->path);

        // cleanup
        @unlink($record->path);
    }

    public function test_download_backup_returns_file(): void
    {
        $this->actingAs($this->superAdmin)->post(route('system-admin.backups.create'));
        $record = BackupRecord::latest('id')->first();

        $response = $this->actingAs($this->superAdmin)
            ->get(route('system-admin.backups.download', $record->id));

        $response->assertStatus(200);

        @unlink($record->path);
    }

    public function test_delete_backup_removes_record_and_file(): void
    {
        $this->actingAs($this->superAdmin)->post(route('system-admin.backups.create'));
        $record = BackupRecord::latest('id')->first();
        $path = $record->path;

        $response = $this->actingAs($this->superAdmin)
            ->delete(route('system-admin.backups.destroy', $record->id));

        $response->assertSessionHas('success');
        $this->assertNull(BackupRecord::find($record->id));
        $this->assertFileDoesNotExist($path);
    }

    public function test_reset_requires_password(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('system-admin.reset-application-data'), []);

        $response->assertSessionHasErrors('password');
    }

    public function test_reset_rejects_wrong_password(): void
    {
        Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-RESET-1',
            'block_number' => '1',
            'lot_number' => '1',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->from(route('system-admin.backups.index'))
            ->post(route('system-admin.reset-application-data'), [
                'password' => 'WrongPassword999',
            ]);

        $response->assertSessionHasErrors('password');
        $this->assertSame(1, Household::count());
    }

    public function test_reset_with_correct_password_deletes_data(): void
    {
        Household::create([
            'village_id' => $this->village->id,
            'household_number' => 'HH-RESET-2',
            'block_number' => '2',
            'lot_number' => '2',
            'status' => 'active',
            'approval_status' => 'approved',
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->from(route('system-admin.backups.index'))
            ->post(route('system-admin.reset-application-data'), [
                'password' => 'Password123',
            ]);

        $response->assertSessionHas('success');
        $this->assertSame(0, Household::count());
        // The super admin user must survive the reset.
        $this->assertNotNull(User::find($this->superAdmin->id));
    }

    public function test_non_super_admin_cannot_access_backups(): void
    {
        $role = Role::where('role_slug', 'secretary')->first()
            ?? Role::where('role_slug', 'admin')->firstOrFail();

        $user = User::create([
            'role_id' => $role->id,
            'username' => 'notadmin',
            'email' => 'notadmin@gmail.com',
            'password' => 'Password123',
            'first_name' => 'Not',
            'last_name' => 'Admin',
            'status' => 'active',
            'village_id' => null,
        ]);

        $response = $this->actingAs($user)
            ->get(route('system-admin.backups.index'));

        $response->assertStatus(403);
    }
}
