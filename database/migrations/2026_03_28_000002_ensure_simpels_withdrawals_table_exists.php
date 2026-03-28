<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Ensure simpels_withdrawals table exists with proper structure.
     * This migration is safe to run multiple times.
     */
    public function up(): void
    {
        // Only create if doesn't exist
        if (!Schema::hasTable('simpels_withdrawals')) {
            Schema::create('simpels_withdrawals', function (Blueprint $table) {
                $table->id();
                $table->string('withdrawal_number')->unique();
                
                // Period info
                $table->date('period_start')->nullable();
                $table->date('period_end')->nullable();
                
                // Summary
                $table->integer('total_transactions')->default(0);
                $table->decimal('total_amount', 15, 2)->default(0);
                $table->decimal('withdrawn_amount', 15, 2)->default(0);
                $table->decimal('remaining_amount', 15, 2)->default(0);
                
                // Status
                $table->string('status')->default('pending');
                
                // Approval - Make nullable to handle deleted users
                $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                
                // Withdrawal details
                $table->timestamp('withdrawn_at')->nullable();
                $table->string('withdrawal_method')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('account_name')->nullable();
                
                // Documentation
                $table->text('notes')->nullable();
                $table->string('receipt_path')->nullable();
                
                // SIMPELS integration fields
                $table->string('simpels_status')->nullable();
                $table->timestamp('simpels_updated_at')->nullable();
                $table->text('simpels_notes')->nullable();
                
                $table->timestamps();
                $table->softDeletes();
                
                // Indexes
                $table->index('status');
                $table->index(['period_start', 'period_end']);
                $table->index('created_at');
                $table->index('simpels_status');
            });
            
            \Log::info('Created simpels_withdrawals table');
        }

        // Ensure pivot table exists
        if (!Schema::hasTable('financial_transaction_withdrawal')) {
            Schema::create('financial_transaction_withdrawal', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('financial_transaction_id');
                $table->unsignedBigInteger('simpels_withdrawal_id');
                $table->decimal('amount', 15, 2);
                $table->timestamps();
                
                $table->foreign('financial_transaction_id', 'fk_ft_withdrawal_ft_id')
                      ->references('id')->on('financial_transactions')->onDelete('cascade');
                $table->foreign('simpels_withdrawal_id', 'fk_ft_withdrawal_sw_id')
                      ->references('id')->on('simpels_withdrawals')->onDelete('cascade');
                
                $table->unique(['financial_transaction_id', 'simpels_withdrawal_id'], 'ft_sw_unique');
            });
            
            \Log::info('Created financial_transaction_withdrawal pivot table');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop tables in down - too dangerous for production
    }
};
