<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simpels_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('withdrawal_number')->unique();
            
            // Period info
            $table->date('period_start');
            $table->date('period_end');
            
            // Summary
            $table->integer('total_transactions')->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('withdrawn_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            
            // Status
            $table->string('status')->default('pending'); // pending, processing, completed, cancelled
            
            // Approval
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            
            // Withdrawal details
            $table->timestamp('withdrawn_at')->nullable();
            $table->string('withdrawal_method')->nullable(); // bank_transfer, cash
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            
            // Documentation
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable(); // Path to uploaded receipt
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('status');
            $table->index(['period_start', 'period_end']);
            $table->index('created_at');
        });
        
        // Pivot table untuk link financial transactions dengan withdrawal
        Schema::create('financial_transaction_withdrawal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('simpels_withdrawal_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2); // Amount yang ditarik dari transaksi ini
            $table->timestamps();
            
            $table->unique(['financial_transaction_id', 'simpels_withdrawal_id'], 'ft_sw_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_transaction_withdrawal');
        Schema::dropIfExists('simpels_withdrawals');
    }
};
