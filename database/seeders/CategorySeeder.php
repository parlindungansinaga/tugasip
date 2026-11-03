<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Makanan & Minuman', 'description' => 'Produk makanan ringan, instan, dan minuman kemasan'],
            ['name' => 'Sembako', 'description' => 'Kebutuhan pokok seperti beras, minyak, dan gula'],
            ['name' => 'Perawatan Tubuh', 'description' => 'Sabun, sampo, pasta gigi, dan sabun cuci'],
            ['name' => 'Alat Tulis Kantor', 'description' => 'Buku, pulpen, kertas, dan perlengkapan kerja'],
            ['name' => 'Kebutuhan Rumah Tangga', 'description' => 'Detergen, pewangi, baterai, dan tisu'],
        ];

        foreach ($categories as $cat) {
            Category::updateOrCreate(['name' => $cat['name']], $cat);
        }
    }
}
