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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('customer_type', ['regular', 'santri'])->default('regular')->after('role');
            $table->string('class')->nullable()->after('customer_type');
            $table->string('rfid_number')->unique()->nullable()->after('class');
            $table->decimal('balance', 15, 2)->default(0)->after('rfid_number');
            $table->decimal('spending_limit', 15, 2)->default(0)->after('balance');
            $table->timestamp('last_topup_at')->nullable()->after('spending_limit');
            
            $table->index(['customer_type', 'rfid_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['customer_type', 'rfid_number']);
            $table->dropColumn([
                'customer_type',
                'class', 
                'rfid_number',
                'balance',
                'spending_limit',
                'last_topup_at'
            ]);
        });
    }
};
