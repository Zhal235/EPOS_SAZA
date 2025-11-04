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
        // For SQLite, we can't modify enum constraints, so we'll work with existing values
        // Just add validation in the model instead
        
        // Only run for MySQL
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'cashier', 'customer') DEFAULT 'cashier'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run for MySQL
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'manager', 'cashier') DEFAULT 'cashier'");
        }
    }
};
