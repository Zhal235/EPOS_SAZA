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
        // For SQLite, we don't need to change anything since it stores enums as strings anyway
        // Just add validation in the model
        
        // Only run for MySQL
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN customer_type ENUM('regular', 'santri', 'guru', 'umum') DEFAULT 'regular'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only run for MySQL
        if (config('database.default') === 'mysql') {
            \DB::statement("ALTER TABLE users MODIFY COLUMN customer_type ENUM('regular', 'santri') DEFAULT 'regular'");
        }
    }
};
