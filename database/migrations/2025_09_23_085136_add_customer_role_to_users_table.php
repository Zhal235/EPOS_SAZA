<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Untuk SQLite, karena tidak support ALTER ENUM, kita akan menggunakan string
        // dan validasi di model level
        
        if (config('database.default') === 'sqlite') {
            // SQLite: Update existing role values to allow 'customer'
            // First, change column type to string to allow any value
            \DB::statement('PRAGMA foreign_keys=OFF');
            \DB::statement('BEGIN TRANSACTION');
            
            // Create new table with string role
            \DB::statement('CREATE TABLE users_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name VARCHAR NOT NULL,
                email VARCHAR NOT NULL UNIQUE,
                email_verified_at DATETIME,
                role VARCHAR DEFAULT "cashier",
                is_active BOOLEAN DEFAULT 1,
                last_login_at DATETIME,
                password VARCHAR NOT NULL,
                customer_type VARCHAR DEFAULT "regular",
                class VARCHAR,
                nis VARCHAR,
                nip VARCHAR,
                subject VARCHAR,
                experience INTEGER,
                rfid_number VARCHAR UNIQUE,
                balance DECIMAL(15,2) DEFAULT 0,
                spending_limit DECIMAL(15,2) DEFAULT 0,
                last_topup_at DATETIME,
                phone VARCHAR,
                remember_token VARCHAR,
                created_at DATETIME,
                updated_at DATETIME
            )');
            
            // Copy data
            \DB::statement('INSERT INTO users_new SELECT * FROM users');
            
            // Drop old table
            \DB::statement('DROP TABLE users');
            
            // Rename new table
            \DB::statement('ALTER TABLE users_new RENAME TO users');
            
            // Recreate indexes
            \DB::statement('CREATE INDEX users_customer_type_rfid_number_index ON users (customer_type, rfid_number)');
            
            \DB::statement('COMMIT');
            \DB::statement('PRAGMA foreign_keys=ON');
            
        } else {
            // MySQL approach
            \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'cashier', 'customer') DEFAULT 'cashier'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'cashier') DEFAULT 'cashier'");
        }
        // For SQLite, no need to revert since we're using string type now
    }
};
