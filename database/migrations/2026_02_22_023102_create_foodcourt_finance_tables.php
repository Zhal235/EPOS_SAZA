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
        // 1. Add balance & bank info to tenants
        Schema::table('tenants', function (Blueprint $table) {
            $table->decimal('balance', 15, 2)->default(0)->after('is_active');
            $table->string('account_bank', 50)->nullable()->after('balance');
            $table->string('account_number', 50)->nullable()->after('account_bank');
            $table->string('account_name', 100)->nullable()->after('account_number');
        });

        // 2. Create Tenant Withdrawals table
        Schema::create('tenant_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number')->unique();
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->foreignId('processed_by')->nullable()->constrained('users'); // Admin/Finance or system
            $table->timestamp('processed_at')->nullable();
            $table->string('proof_image')->nullable(); // Upload transfer proof
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Create Tenant Ledger (Transaction History)
        Schema::create('tenant_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['sale', 'withdrawal', 'adjustment', 'refund'])->default('sale');
            $table->decimal('amount', 15, 2); // Positive/Negative based on type? Or always positive and type defines credit/debit?
            // Let's use signed amount: + for Credit (Sale), - for Debit (Withdrawal)
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            
            // Allow linking to the main transaction system or withdrawal
            $table->foreignId('transaction_item_id')->nullable()->constrained()->nullOnDelete(); 
            // Better link to item, not header, because one header has multiple tenants.
            // Or use transaction_id + notes? TransactionItem is safest.
            
            $table->foreignId('withdrawal_id')->nullable()->constrained('tenant_withdrawals')->cascadeOnDelete();
            
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_ledger');
        Schema::dropIfExists('tenant_withdrawals');
        
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['balance', 'account_bank', 'account_number', 'account_name']);
        });
    }
};
