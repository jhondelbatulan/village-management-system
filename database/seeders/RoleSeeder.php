<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'role_name' => 'Super Administrator',
                'role_slug' => 'super_admin',
                'description' => 'Full system access. Can manage all villages, all users, and system settings.',
            ],
            [
                'role_name' => 'Administrator',
                'role_slug' => 'admin',
                'description' => 'Village-level administrator. Can manage users within assigned village(s).',
            ],
            [
                'role_name' => 'Barangay Secretary',
                'role_slug' => 'secretary',
                'description' => 'Handles record management, resident profiles, certificates, and reports.',
            ],
            [
                'role_name' => 'Barangay Treasurer',
                'role_slug' => 'treasurer',
                'description' => 'Manages payments, collections, financial reports, and budget tracking.',
            ],
            [
                'role_name' => 'Resident',
                'role_slug' => 'resident',
                'description' => 'Village resident. Can view personal records, submit requests, and participate in consultations.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['role_slug' => $role['role_slug']],
                $role
            );
        }
    }
}