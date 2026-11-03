<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'PT Indofood Makmur Mandiri',
                'phone' => '021-5551234',
                'address' => 'Jl. Sudirman Kav. 21, Jakarta Selatan',
            ],
            [
                'name' => 'CV Sumber Rezeki Sembako',
                'phone' => '022-7778901',
                'address' => 'Kawasan Industri Rancaekek No. 45, Bandung',
            ],
            [
                'name' => 'PT Unilever Retail Distribusi',
                'phone' => '021-8989123',
                'address' => 'BSD City Sektor 12, Tangerang',
            ],
        ];

        foreach ($suppliers as $sup) {
            Supplier::updateOrCreate(['name' => $sup['name']], $sup);
        }
    }
}
