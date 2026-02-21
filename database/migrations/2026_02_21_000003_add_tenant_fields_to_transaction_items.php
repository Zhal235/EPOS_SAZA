<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            // Snapshot tenant info saat transaksi (null = item dari toko)
            $table->foreignId('tenant_id')->nullable()->after('product_id')
                  ->constrained('tenants')->onDelete('set null');
            $table->string('tenant_name')->nullable()->after('tenant_id'); // snapshot nama tenant

            // Commission snapshot saat transaksi terjadi
            $table->enum('commission_type', ['fixed', 'percentage'])->nullable()->after('tenant_name');
            $table->decimal('commission_value', 12, 2)->default(0)->after('commission_type');
            $table->decimal('commission_amount', 12, 2)->default(0)->after('commission_value'); // komisi total (sudah dihitung)
            $table->decimal('tenant_amount', 12, 2)->default(0)->after('commission_amount');   // yang diterima tenant

            // Catatan item (misal: tidak pedas, extra sambal)
            $table->string('item_notes')->nullable()->after('tenant_amount');

            $table->index(['tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['tenant_id']);
            $table->dropIndex(['tenant_id']);
            $table->dropColumn([
                'tenant_id', 'tenant_name', 'commission_type',
                'commission_value', 'commission_amount', 'tenant_amount', 'item_notes',
            ]);
        });
    }
};
