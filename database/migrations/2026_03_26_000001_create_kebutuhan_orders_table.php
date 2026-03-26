<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kebutuhan_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // KBT-20260326-XXXX
            $table->string('santri_id');              // ID santri dari SIMPELS
            $table->string('santri_name');
            $table->string('rfid_uid')->nullable();
            $table->json('items');                    // [{product_id, name, qty, price, subtotal}]
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', [
                'pending_confirmation',
                'confirmed',
                'rejected',
                'expired',
                'completed',
            ])->default('pending_confirmation');
            $table->string('simpels_order_id')->nullable(); // ID dari SIMPELS backend
            $table->unsignedBigInteger('cashier_id');
            $table->timestamp('expired_at');               // created_at + 1 hari
            $table->timestamp('confirmed_at')->nullable();
            $table->string('confirmed_by')->nullable();    // 'wali' | 'admin'
            $table->string('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('cashier_id')->references('id')->on('users')->onDelete('restrict');
            $table->index(['santri_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kebutuhan_orders');
    }
};
