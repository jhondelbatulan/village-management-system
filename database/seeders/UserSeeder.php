<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('role_slug', 'super_admin')->first();

        if (!$superAdminRole) {
            $this->command->warn('Super Admin role not found. Make sure RoleSeeder runs first.');
            return;
        }

        User::updateOrCreate(
            ['username' => 'superadmin'],
            [
                'role_id' => $superAdminRole->id,
                'first_name' => 'System',
                'middle_name' => null,
                'last_name' => 'Administrator',
                'suffix' => null,
                'email' => 'superadmin@mvms.local',
                'contact_number' => '09000000000',
                'password' => 'Admin@123',
                'status' => 'active',
            ]
        );
    }
}