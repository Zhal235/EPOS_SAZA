<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create test santri data for API integration testing
        DB::table('users')->insert([
            [
                'name' => 'Ahmad Test Santri',
                'email' => 'test.santri@simpels.local',
                'password' => bcrypt('password123'),
                'phone' => '081234567890',
                'role' => 'cashier', // Use valid role from enum
                'customer_type' => 'santri',
                'nis' => 'TEST001',
                'class' => 'XII IPA 1',
                'rfid_number' => 'TEST123456789',
                'balance' => 100000, // Rp 100.000 saldo
                'spending_limit' => 50000, // Limit Rp 50.000
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Test Santri',
                'email' => 'test.santri2@simpels.local',
                'password' => bcrypt('password123'),
                'phone' => '081234567891',
                'role' => 'cashier', // Use valid role from enum
                'customer_type' => 'santri',
                'nis' => 'TEST002',
                'class' => 'XI IPS 2',
                'rfid_number' => 'TEST123456788',
                'balance' => 5000, // Saldo rendah untuk testing insufficient balance
                'spending_limit' => 25000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Citra Test Santri',
                'email' => 'test.santri3@simpels.local',
                'password' => bcrypt('password123'),
                'phone' => '081234567892',
                'role' => 'cashier', // Use valid role from enum
                'customer_type' => 'santri',
                'nis' => 'TEST003',
                'class' => 'X A',
                'rfid_number' => 'TEST123456787',
                'balance' => 75000,
                'spending_limit' => 30000,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->whereIn('rfid_number', [
            'TEST123456789',
            'TEST123456788', 
            'TEST123456787'
        ])->delete();
    }
};