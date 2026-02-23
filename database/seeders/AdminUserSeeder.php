<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@epos.com'],
            [
                'name' => 'Admin EPOS',
                'email' => 'admin@epos.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Manager User
        User::updateOrCreate(
            ['email' => 'manager@epos.com'],
            [
                'name' => 'Manager EPOS',
                'email' => 'manager@epos.com',
                'password' => Hash::make('password123'),
                'role' => 'manager',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Cashier User
        User::updateOrCreate(
            ['email' => 'kasir@epos.com'],
            [
                'name' => 'Kasir EPOS',
                'email' => 'kasir@epos.com',
                'password' => Hash::make('password123'),
                'role' => 'cashier',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create additional cashiers
        User::updateOrCreate(
            ['email' => 'kasir2@epos.com'],
            [
                'name' => 'Kasir 2 EPOS',
                'email' => 'kasir2@epos.com',
                'password' => Hash::make('password123'),
                'role' => 'cashier',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Kasir Toko (store-only cashier)
        User::updateOrCreate(
            ['email' => 'kasir.toko@epos.com'],
            [
                'name' => 'Kasir Toko',
                'email' => 'kasir.toko@epos.com',
                'password' => Hash::make('password123'),
                'role' => 'cashier_store',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create Kasir Foodcourt (foodcourt-only cashier)
        User::updateOrCreate(
            ['email' => 'kasir.foodcourt@epos.com'],
            [
                'name' => 'Kasir Foodcourt',
                'email' => 'kasir.foodcourt@epos.com',
                'password' => Hash::make('password123'),
                'role' => 'cashier_foodcourt',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
