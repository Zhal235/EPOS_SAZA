<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Makanan & Snack',
                'slug' => 'makanan-snack',
                'description' => 'Berbagai makanan ringan, snack, dan makanan siap saji',
                'icon' => 'fas fa-cookie-bite',
                'color' => '#F59E0B',
                'sort_order' => 1
            ],
            [
                'name' => 'Minuman',
                'slug' => 'minuman',
                'description' => 'Air mineral, soft drink, jus, kopi, teh',
                'icon' => 'fas fa-wine-bottle',
                'color' => '#3B82F6',
                'sort_order' => 2
            ],
            [
                'name' => 'Kebutuhan Sehari-hari',
                'slug' => 'kebutuhan-sehari-hari',
                'description' => 'Sabun, shampo, pasta gigi, deterjen',
                'icon' => 'fas fa-pump-soap',
                'color' => '#10B981',
                'sort_order' => 3
            ],
            [
                'name' => 'Sembako',
                'slug' => 'sembako',
                'description' => 'Beras, minyak, gula, tepung, bumbu dapur',
                'icon' => 'fas fa-shopping-basket',
                'color' => '#EF4444',
                'sort_order' => 4
            ],
            [
                'name' => 'Rokok & Tembakau',
                'slug' => 'rokok-tembakau',
                'description' => 'Rokok berbagai merk dan produk tembakau',
                'icon' => 'fas fa-smoking',
                'color' => '#6B7280',
                'sort_order' => 5
            ],
            [
                'name' => 'Alat Tulis & Kantor',
                'slug' => 'alat-tulis-kantor',
                'description' => 'Pulpen, buku, kertas, dan perlengkapan kantor',
                'icon' => 'fas fa-pen',
                'color' => '#8B5CF6',
                'sort_order' => 6
            ]
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
