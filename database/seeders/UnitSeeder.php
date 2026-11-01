<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            // Weight Units
            [
                'code' => 'gram',
                'name' => 'Gram',
                'category' => 'weight',
                'conversion_factor' => 1,
                'base_unit' => 'kg',
            ],
            [
                'code' => 'kg',
                'name' => 'Kilogram',
                'category' => 'weight',
                'conversion_factor' => 1000,
                'base_unit' => 'kg',
            ],
            // Volume Units
            [
                'code' => 'ml',
                'name' => 'Milliliter',
                'category' => 'volume',
                'conversion_factor' => 1,
                'base_unit' => 'liter',
            ],
            [
                'code' => 'liter',
                'name' => 'Liter',
                'category' => 'volume',
                'conversion_factor' => 1000,
                'base_unit' => 'liter',
            ],
            // Count Units
            [
                'code' => 'pcs',
                'name' => 'Pieces',
                'category' => 'count',
                'conversion_factor' => 1,
                'base_unit' => 'pcs',
            ],
            [
                'code' => 'dozen',
                'name' => 'Dozen',
                'category' => 'count',
                'conversion_factor' => 12,
                'base_unit' => 'pcs',
            ],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
