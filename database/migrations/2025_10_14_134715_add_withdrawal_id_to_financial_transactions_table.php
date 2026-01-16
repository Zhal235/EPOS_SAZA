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
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('withdrawal_id')->nullable()->after('user_id');
            $table->foreign('withdrawal_id', 'fk_financial_transactions_withdrawal_id')
                  ->references('id')->on('simpels_withdrawals')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropForeign('fk_financial_transactions_withdrawal_id');
            $table->dropColumn('withdrawal_id');
        });
    }
};
