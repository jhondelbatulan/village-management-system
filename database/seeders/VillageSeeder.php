<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;

class VillageSeeder extends Seeder
{
    public function run(): void
    {
        $villages = [
            [
                'village_name' => 'Comagascas',
                'village_code' => 'COM',
                'description' => 'Comagascas Resettlement Village under the City Planning and Development Office.',
                'address' => 'Comagascas, Cabadbaran City, Agusan del Norte',
                'status' => 'active',
            ],
            [
                'village_name' => 'La Union (Angel Village)',
                'village_code' => 'LAU',
                'description' => 'La Union Resettlement Village, also known as Angel Village.',
                'address' => 'La Union, Cabadbaran City, Agusan del Norte',
                'status' => 'active',
            ],
            [
                'village_name' => 'Calibunan',
                'village_code' => 'CAL',
                'description' => 'Calibunan Resettlement Village under the City Planning and Development Office.',
                'address' => 'Calibunan, Cabadbaran City, Agusan del Norte',
                'status' => 'active',
            ],
        ];

        foreach ($villages as $village) {
            Village::updateOrCreate(
                ['village_code' => $village['village_code']],
                $village
            );
        }
    }
}
