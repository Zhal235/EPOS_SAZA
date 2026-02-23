<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','cashier','cashier_store','cashier_foodcourt','customer') DEFAULT 'cashier'");
        }
        // SQLite uses VARCHAR already (from previous migration), no change needed
    }

    public function down(): void
    {
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','manager','cashier','customer') DEFAULT 'cashier'");
        }
    }
};
