<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('transaction_items', 'cost_price')) {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->decimal('cost_price', 15, 2)->default(0)->after('unit_price')
                      ->comment('Snapshot harga beli produk saat transaksi');
            });
        }

        // Isi data lama dari tabel products (best-effort untuk histori yang sudah ada)
        DB::statement('
            UPDATE transaction_items
            SET cost_price = (
                SELECT cost_price FROM products WHERE products.id = transaction_items.product_id
            )
            WHERE cost_price = 0
        ');
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
