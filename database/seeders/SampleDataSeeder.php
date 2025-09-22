<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Supplier;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample categories if they don't exist
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'icon' => 'fas fa-laptop',
                'color' => '#3B82F6',
                'is_active' => true
            ],
            [
                'name' => 'Food & Beverage',
                'description' => 'Food and beverage products',
                'icon' => 'fas fa-coffee',
                'color' => '#10B981',
                'is_active' => true
            ],
            [
                'name' => 'Office Supplies',
                'description' => 'Office and stationery supplies',
                'icon' => 'fas fa-paperclip',
                'color' => '#8B5CF6',
                'is_active' => true
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData
            );
        }

        // Create sample suppliers if they don't exist
        $suppliers = [
            [
                'name' => 'PT Tech Supplier',
                'contact_person' => 'John Doe',
                'email' => 'john@techsupplier.com',
                'phone' => '021-12345678',
                'address' => 'Jl. Tech Street No. 123, Jakarta',
                'is_active' => true
            ],
            [
                'name' => 'CV Coffee Supply',
                'contact_person' => 'Jane Smith',
                'email' => 'jane@coffeesupply.com',
                'phone' => '021-87654321',
                'address' => 'Jl. Coffee Ave No. 456, Jakarta',
                'is_active' => true
            ],
            [
                'name' => 'PT Office Pro',
                'contact_person' => 'Bob Wilson',
                'email' => 'bob@officepro.com',
                'phone' => '021-11223344',
                'address' => 'Jl. Office Plaza No. 789, Jakarta',
                'is_active' => true
            ]
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate(
                ['name' => $supplierData['name']],
                $supplierData
            );
        }
    }
}
