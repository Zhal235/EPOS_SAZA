<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // outlet_type: 'store' = produk toko biasa, 'foodcourt' = produk milik tenant foodcourt
            $table->enum('outlet_type', ['store', 'foodcourt'])->default('store')->after('category_id');

            // tenant_id: null = produk toko, not-null = produk foodcourt
            $table->foreignId('tenant_id')->nullable()->after('outlet_type')
                  ->constrained('tenants')->onDelete('set null');

            // Commission override per product (null = use tenant default)
            $table->enum('commission_type', ['fixed', 'percentage'])->nullable()->after('tenant_id');
            $table->decimal('commission_value', 12, 2)->nullable()->after('commission_type');

            $table->index(['outlet_type', 'is_active']);
            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['outlet_type', 'is_active']);
            $table->dropIndex(['tenant_id', 'is_active']);
            $table->dropColumn(['outlet_type', 'tenant_id', 'commission_type', 'commission_value']);
        });
    }
};
