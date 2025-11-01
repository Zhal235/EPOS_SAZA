<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('cascade');
            $table->string('type'); // 'rfid_payment', 'refund', 'withdrawal_simpels', 'cash_in', 'cash_out'
            $table->string('category'); // 'income', 'expense', 'transfer'
            
            // Santri/Customer info
            $table->string('santri_id')->nullable(); // ID dari SIMPels
            $table->string('santri_name')->nullable();
            $table->string('rfid_tag')->nullable();
            
            // Transaction details
            $table->decimal('amount', 15, 2);
            $table->decimal('previous_balance', 15, 2)->nullable(); // Balance sebelum transaksi
            $table->decimal('new_balance', 15, 2)->nullable(); // Balance setelah transaksi
            
            // EPOS specific
            $table->string('payment_method')->nullable(); // cash, rfid, qris, card
            $table->string('reference_number')->nullable(); // Referensi ke transaksi lain (misalnya refund)
            
            // SIMPels sync
            $table->boolean('synced_to_simpels')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->text('sync_response')->nullable();
            
            // Withdrawal from SIMPels tracking
            $table->boolean('withdrawn_from_simpels')->default(false);
            $table->timestamp('withdrawn_at')->nullable();
            $table->string('withdrawal_reference')->nullable();
            $table->foreignId('withdrawn_by')->nullable()->constrained('users'); // Staff yang menarik
            
            // General
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('completed'); // pending, completed, failed, refunded
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Kasir/Staff
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('type');
            $table->index('category');
            $table->index('santri_id');
            $table->index('rfid_tag');
            $table->index('status');
            $table->index('synced_to_simpels');
            $table->index('withdrawn_from_simpels');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
