<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create regular customers
        User::create([
            'name' => 'Ahmad Wijaya',
            'email' => 'ahmad.wijaya@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'regular',
            'is_active' => true,
            'phone' => '081234567890',
        ]);

        User::create([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti.nurhaliza@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'regular',
            'is_active' => true,
            'phone' => '081234567891',
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'regular',
            'is_active' => false,
            'phone' => '081234567892',
        ]);

        // Create santri customers
        User::create([
            'name' => 'Muhammad Al-Fatih',
            'email' => 'al.fatih@pesantren.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'santri',
            'class' => 'Kelas 12 IPA',
            'rfid_number' => 'RFID001',
            'balance' => 150000,
            'spending_limit' => 50000,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Fatimah Az-Zahra',
            'email' => 'fatimah@pesantren.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'santri',
            'class' => 'Kelas 11 IPS',
            'rfid_number' => 'RFID002',
            'balance' => 75000,
            'spending_limit' => 30000,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Abdullah Ibn Abbas',
            'email' => 'abdullah@pesantren.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'santri',
            'class' => 'Kelas 10 A',
            'rfid_number' => 'RFID003',
            'balance' => 200000,
            'spending_limit' => 75000,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Khadijah Binti Khuwailid',
            'email' => 'khadijah@pesantren.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'santri',
            'class' => 'Kelas 12 IPS',
            'rfid_number' => 'RFID004',
            'balance' => 25000,
            'spending_limit' => 40000,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Umar Ibn Khattab',
            'email' => 'umar@pesantren.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'santri',
            'class' => 'Kelas 9 B',
            'rfid_number' => 'RFID005',
            'balance' => 100000,
            'spending_limit' => 25000,
            'is_active' => false,
        ]);

        // Create more regular customers for variety
        User::create([
            'name' => 'Dewi Kartika',
            'email' => 'dewi.kartika@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'regular',
            'is_active' => true,
            'phone' => '081234567893',
        ]);

        User::create([
            'name' => 'Rendra Mahendra',
            'email' => 'rendra.mahendra@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'customer_type' => 'regular',
            'is_active' => true,
            'phone' => '081234567894',
        ]);

        $this->command->info('Customer data seeded successfully!');
    }
}
