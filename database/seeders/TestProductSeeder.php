<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class TestProductSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Create test categories first
        $foodCategory = Category::firstOrCreate(['name' => 'Makanan'], [
            'description' => 'Produk makanan untuk testing',
            'is_active' => true,
        ]);

        $drinkCategory = Category::firstOrCreate(['name' => 'Minuman'], [
            'description' => 'Produk minuman untuk testing',
            'is_active' => true,
        ]);

        $snackCategory = Category::firstOrCreate(['name' => 'Snack'], [
            'description' => 'Produk snack untuk testing',
            'is_active' => true,
        ]);

        // Create test products
        $testProducts = [
            // Food items
            [
                'name' => 'Nasi Gudeg',
                'description' => 'Nasi gudeg khas Yogyakarta',
                'price' => 15000,
                'cost' => 10000,
                'stock' => 50,
                'min_stock' => 10,
                'category_id' => $foodCategory->id,
                'barcode' => 'FOOD001',
                'is_active' => true,
            ],
            [
                'name' => 'Nasi Ayam',
                'description' => 'Nasi dengan ayam goreng',
                'price' => 18000,
                'cost' => 12000,
                'stock' => 45,
                'min_stock' => 10,
                'category_id' => $foodCategory->id,
                'barcode' => 'FOOD002',
                'is_active' => true,
            ],
            [
                'name' => 'Nasi Rendang',
                'description' => 'Nasi dengan rendang daging',
                'price' => 25000,
                'cost' => 18000,
                'stock' => 30,
                'min_stock' => 5,
                'category_id' => $foodCategory->id,
                'barcode' => 'FOOD003',
                'is_active' => true,
            ],
            [
                'name' => 'Soto Ayam',
                'description' => 'Soto ayam dengan nasi',
                'price' => 12000,
                'cost' => 8000,
                'stock' => 40,
                'min_stock' => 10,
                'category_id' => $foodCategory->id,
                'barcode' => 'FOOD004',
                'is_active' => true,
            ],

            // Drink items
            [
                'name' => 'Es Teh Manis',
                'description' => 'Es teh manis segar',
                'price' => 5000,
                'cost' => 2000,
                'stock' => 100,
                'min_stock' => 20,
                'category_id' => $drinkCategory->id,
                'barcode' => 'DRINK001',
                'is_active' => true,
            ],
            [
                'name' => 'Es Jeruk',
                'description' => 'Es jeruk segar',
                'price' => 7000,
                'cost' => 3000,
                'stock' => 80,
                'min_stock' => 15,
                'category_id' => $drinkCategory->id,
                'barcode' => 'DRINK002',
                'is_active' => true,
            ],
            [
                'name' => 'Air Mineral',
                'description' => 'Air mineral 600ml',
                'price' => 3000,
                'cost' => 1500,
                'stock' => 150,
                'min_stock' => 30,
                'category_id' => $drinkCategory->id,
                'barcode' => 'DRINK003',
                'is_active' => true,
            ],
            [
                'name' => 'Es Cappuccino',
                'description' => 'Es cappuccino premium',
                'price' => 15000,
                'cost' => 8000,
                'stock' => 25,
                'min_stock' => 5,
                'category_id' => $drinkCategory->id,
                'barcode' => 'DRINK004',
                'is_active' => true,
            ],

            // Snack items
            [
                'name' => 'Keripik Singkong',
                'description' => 'Keripik singkong renyah',
                'price' => 8000,
                'cost' => 4000,
                'stock' => 60,
                'min_stock' => 15,
                'category_id' => $snackCategory->id,
                'barcode' => 'SNACK001',
                'is_active' => true,
            ],
            [
                'name' => 'Biskuit Marie',
                'description' => 'Biskuit marie kemasan',
                'price' => 6000,
                'cost' => 3500,
                'stock' => 75,
                'min_stock' => 20,
                'category_id' => $snackCategory->id,
                'barcode' => 'SNACK002',
                'is_active' => true,
            ],
            [
                'name' => 'Roti Bakar',
                'description' => 'Roti bakar dengan selai',
                'price' => 10000,
                'cost' => 6000,
                'stock' => 35,
                'min_stock' => 10,
                'category_id' => $snackCategory->id,
                'barcode' => 'SNACK003',
                'is_active' => true,
            ],
            [
                'name' => 'Coklat Batang',
                'description' => 'Coklat batang premium',
                'price' => 12000,
                'cost' => 7000,
                'stock' => 40,
                'min_stock' => 10,
                'category_id' => $snackCategory->id,
                'barcode' => 'SNACK004',
                'is_active' => true,
            ],
        ];

        foreach ($testProducts as $productData) {
            Product::firstOrCreate(
                ['barcode' => $productData['barcode']],
                $productData
            );
        }

        $this->command->info('Test products created successfully!');
        $this->command->info('Food items: 4 products');
        $this->command->info('Drink items: 4 products');
        $this->command->info('Snack items: 4 products');
        $this->command->info('Total: 12 test products');
    }
}Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
    }
}
