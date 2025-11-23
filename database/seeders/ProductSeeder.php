<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            // Makanan & Snack (Category ID: 1)
            [
                'sku' => 'MKN001',
                'barcode' => '8992761123456',
                'name' => 'Indomie Goreng',
                'description' => 'Mie instan rasa ayam bawang',
                'category_id' => 1,
                'supplier_id' => 1,
                'brand' => 'Indomie',
                'unit' => 'pcs',
                'size' => '85g',
                'cost_price' => 2500,
                'selling_price' => 3500,
                'wholesale_price' => 3200,
                'wholesale_min_qty' => 40,
                'stock_quantity' => 150,
                'min_stock' => 50
            ],
            [
                'sku' => 'MKN002',
                'barcode' => '8992388123789',
                'name' => 'Chitato Rasa Sapi Panggang',
                'description' => 'Keripik kentang rasa sapi panggang',
                'category_id' => 1,
                'supplier_id' => 1,
                'brand' => 'Chitato',
                'unit' => 'pcs',
                'size' => '68g',
                'cost_price' => 8500,
                'selling_price' => 11000,
                'stock_quantity' => 75,
                'min_stock' => 20
            ],
            [
                'sku' => 'MKN003',
                'barcode' => '8991002123456',
                'name' => 'Oreo Original',
                'description' => 'Biskuit sandwich cokelat',
                'category_id' => 1,
                'supplier_id' => 2,
                'brand' => 'Oreo',
                'unit' => 'pcs',
                'size' => '137g',
                'cost_price' => 6500,
                'selling_price' => 8500,
                'stock_quantity' => 60,
                'min_stock' => 15
            ],

            // Minuman (Category ID: 2)
            [
                'sku' => 'MNM001',
                'barcode' => '8886008101234',
                'name' => 'Aqua 600ml',
                'description' => 'Air mineral dalam kemasan',
                'category_id' => 2,
                'supplier_id' => 1,
                'brand' => 'Aqua',
                'unit' => 'pcs',
                'size' => '600ml',
                'cost_price' => 2000,
                'selling_price' => 3000,
                'wholesale_price' => 2800,
                'wholesale_min_qty' => 24,
                'stock_quantity' => 200,
                'min_stock' => 48
            ],
            [
                'sku' => 'MNM002',
                'barcode' => '8851959123456',
                'name' => 'Coca Cola 330ml',
                'description' => 'Minuman berkarbonasi rasa cola',
                'category_id' => 2,
                'supplier_id' => 5,
                'brand' => 'Coca Cola',
                'unit' => 'pcs',
                'size' => '330ml',
                'cost_price' => 4000,
                'selling_price' => 5500,
                'stock_quantity' => 120,
                'min_stock' => 24
            ],
            [
                'sku' => 'MNM003',
                'barcode' => '8992761789123',
                'name' => 'Teh Botol Sosro 450ml',
                'description' => 'Teh dalam kemasan botol',
                'category_id' => 2,
                'supplier_id' => 1,
                'brand' => 'Sosro',
                'unit' => 'pcs',
                'size' => '450ml',
                'cost_price' => 3500,
                'selling_price' => 5000,
                'stock_quantity' => 80,
                'min_stock' => 20
            ],

            // Kebutuhan Sehari-hari (Category ID: 3)
            [
                'sku' => 'KBT001',
                'barcode' => '8901030987654',
                'name' => 'Lifebuoy Sabun Batang',
                'description' => 'Sabun antiseptik untuk keluarga',
                'category_id' => 3,
                'supplier_id' => 2,
                'brand' => 'Lifebuoy',
                'unit' => 'pcs',
                'size' => '110g',
                'cost_price' => 3000,
                'selling_price' => 4200,
                'stock_quantity' => 100,
                'min_stock' => 25
            ],
            [
                'sku' => 'KBT002',
                'barcode' => '8901030456789',
                'name' => 'Pepsodent 75g',
                'description' => 'Pasta gigi untuk kesehatan gigi',
                'category_id' => 3,
                'supplier_id' => 2,
                'brand' => 'Pepsodent',
                'unit' => 'pcs',
                'size' => '75g',
                'cost_price' => 8500,
                'selling_price' => 11000,
                'stock_quantity' => 45,
                'min_stock' => 12
            ],
            [
                'sku' => 'KBT003',
                'barcode' => '8993175123456',
                'name' => 'So Klin Deterjen 800g',
                'description' => 'Deterjen bubuk untuk mencuci',
                'category_id' => 3,
                'supplier_id' => 3,
                'brand' => 'So Klin',
                'unit' => 'pcs',
                'size' => '800g',
                'cost_price' => 12000,
                'selling_price' => 15500,
                'stock_quantity' => 30,
                'min_stock' => 8
            ],

            // Sembako (Category ID: 4)
            [
                'sku' => 'SBK001',
                'barcode' => '8992696123456',
                'name' => 'Beras Premium 5kg',
                'description' => 'Beras putih premium kualitas terbaik',
                'category_id' => 4,
                'supplier_id' => 4,
                'brand' => 'Cap Bulog',
                'unit' => 'kg',
                'size' => '5kg',
                'weight' => 5000,
                'cost_price' => 65000,
                'selling_price' => 75000,
                'stock_quantity' => 25,
                'min_stock' => 5
            ],
            [
                'sku' => 'SBK002',
                'barcode' => '8991001234567',
                'name' => 'Minyak Goreng Tropical 1L',
                'description' => 'Minyak goreng kelapa sawit',
                'category_id' => 4,
                'supplier_id' => 4,
                'brand' => 'Tropical',
                'unit' => 'pcs',
                'size' => '1L',
                'cost_price' => 18000,
                'selling_price' => 22000,
                'stock_quantity' => 40,
                'min_stock' => 10
            ],
            [
                'sku' => 'SBK003',
                'barcode' => '8992696789123',
                'name' => 'Gula Pasir 1kg',
                'description' => 'Gula pasir putih kristal',
                'category_id' => 4,
                'supplier_id' => 4,
                'brand' => 'Gulaku',
                'unit' => 'kg',
                'size' => '1kg',
                'weight' => 1000,
                'cost_price' => 14000,
                'selling_price' => 17000,
                'stock_quantity' => 50,
                'min_stock' => 15
            ],

            // Rokok & Tembakau (Category ID: 5)
            [
                'sku' => 'RKK001',
                'barcode' => '8998889123456',
                'name' => 'Gudang Garam Merah',
                'description' => 'Rokok kretek filter',
                'category_id' => 5,
                'supplier_id' => 4,
                'brand' => 'Gudang Garam',
                'unit' => 'bungkus',
                'size' => '12 batang',
                'cost_price' => 18000,
                'selling_price' => 22000,
                'stock_quantity' => 80,
                'min_stock' => 20
            ],

            // Alat Tulis & Kantor (Category ID: 6)
            [
                'sku' => 'ATK001',
                'barcode' => '8991234567890',
                'name' => 'Pulpen Standard AE7',
                'description' => 'Pulpen tinta biru',
                'category_id' => 6,
                'supplier_id' => 4,
                'brand' => 'Standard',
                'unit' => 'pcs',
                'cost_price' => 1500,
                'selling_price' => 2500,
                'stock_quantity' => 150,
                'min_stock' => 30
            ],
            [
                'sku' => 'ATK002',
                'barcode' => '8991234567891',
                'name' => 'Buku Tulis 38 Lembar',
                'description' => 'Buku tulis bergaris 38 lembar',
                'category_id' => 6,
                'supplier_id' => 4,
                'brand' => 'Sidu',
                'unit' => 'pcs',
                'cost_price' => 3000,
                'selling_price' => 4500,
                'stock_quantity' => 100,
                'min_stock' => 25
            ]
        ];

        foreach ($products as $product) {
            \App\Models\Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product
            );
        }
    }
}
