<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('booth_number')->nullable()->after('slug');
            $table->string('owner_name')->nullable()->after('booth_number');
            $table->string('phone')->nullable()->after('owner_name');
            $table->text('description')->nullable()->after('phone');
            // Default commission applied to all products of this tenant (can be overridden per product)
            $table->enum('commission_type', ['fixed', 'percentage'])->default('fixed')->after('description');
            $table->decimal('commission_value', 12, 2)->default(0)->after('commission_type');
            $table->boolean('is_active')->default(true)->after('commission_value');
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'booth_number', 'owner_name', 'phone', 'description',
                'commission_type', 'commission_value', 'is_active', 'sort_order',
            ]);
        });
    }
};
