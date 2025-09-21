<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'name' => 'PT Indofood Sukses Makmur',
                'company_name' => 'PT Indofood Sukses Makmur Tbk',
                'contact_person' => 'Budi Santoso',
                'phone' => '021-5650-3000',
                'email' => 'sales@indofood.co.id',
                'address' => 'Jl. Sudirman Kav 76-78, Jakarta',
                'city' => 'Jakarta',
                'postal_code' => '12910',
                'credit_limit' => 50000000,
                'payment_terms' => 30
            ],
            [
                'name' => 'PT Unilever Indonesia',
                'company_name' => 'PT Unilever Indonesia Tbk',
                'contact_person' => 'Sari Dewi',
                'phone' => '021-2856-1000',
                'email' => 'sales@unilever.co.id',
                'address' => 'Jl. BSD Boulevard Barat, Tangerang',
                'city' => 'Tangerang',
                'postal_code' => '15345',
                'credit_limit' => 75000000,
                'payment_terms' => 45
            ],
            [
                'name' => 'PT Wings Surya',
                'company_name' => 'PT Wings Surya',
                'contact_person' => 'Ahmad Rahman',
                'phone' => '031-7995-1234',
                'email' => 'info@wings.co.id',
                'address' => 'Jl. Raya Surabaya-Malang Km 45',
                'city' => 'Surabaya',
                'postal_code' => '61256',
                'credit_limit' => 30000000,
                'payment_terms' => 30
            ],
            [
                'name' => 'CV Toko Grosir Maju',
                'company_name' => 'CV Toko Grosir Maju Bersama',
                'contact_person' => 'Pak Hasan',
                'phone' => '0274-512345',
                'email' => 'majubersama@email.com',
                'address' => 'Jl. Malioboro 125, Yogyakarta',
                'city' => 'Yogyakarta',
                'postal_code' => '55213',
                'credit_limit' => 15000000,
                'payment_terms' => 14
            ],
            [
                'name' => 'PT Coca Cola Amatil',
                'company_name' => 'PT Coca Cola Amatil Indonesia',
                'contact_person' => 'Lisa Purnama',
                'phone' => '021-520-7777',
                'email' => 'sales@coca-cola.co.id',
                'address' => 'Jl. Pulo Ayang Raya, Jakarta',
                'city' => 'Jakarta',
                'postal_code' => '13930',
                'credit_limit' => 40000000,
                'payment_terms' => 30
            ]
        ];

        foreach ($suppliers as $supplier) {
            \App\Models\Supplier::create($supplier);
        }
    }
}
