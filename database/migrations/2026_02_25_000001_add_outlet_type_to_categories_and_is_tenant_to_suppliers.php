<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // 'store' = kategori produk toko, 'foodcourt' = kategori khusus foodcourt
            $table->string('outlet_type')->default('store')->after('sort_order');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            // true = supplier dummy yang dibuat otomatis untuk tenant foodcourt
            $table->boolean('is_tenant_supplier')->default(false)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('outlet_type');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('is_tenant_supplier');
        });
    }
};
