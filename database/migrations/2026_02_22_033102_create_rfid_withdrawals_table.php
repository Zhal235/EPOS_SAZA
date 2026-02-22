<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rfid_withdrawals', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique(); // WD-20240222-001
            $table->decimal('amount', 12, 2);
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id'); // Admin yang request
            $table->timestamp('approved_at')->nullable();
            $table->string('approved_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rfid_withdrawals');
    }
};