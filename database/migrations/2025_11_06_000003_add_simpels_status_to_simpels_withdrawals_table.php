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
        Schema::table('simpels_withdrawals', function (Blueprint $table) {
            $table->enum('simpels_status', ['pending', 'approved', 'rejected', 'completed'])->nullable()->after('status');
            $table->timestamp('simpels_updated_at')->nullable()->after('withdrawn_at');
            $table->text('simpels_notes')->nullable()->after('simpels_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('simpels_withdrawals', function (Blueprint $table) {
            $table->dropColumn(['simpels_status', 'simpels_updated_at', 'simpels_notes']);
        });
    }
};