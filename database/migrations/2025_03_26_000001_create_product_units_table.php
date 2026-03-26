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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            // Unit details
            $table->string('unit_name'); // pcs, dus, box, karton, lusin, dll
            $table->integer('conversion_rate')->default(1); // Berapa unit terkecil dalam unit ini (1 dus = 24 pcs)
            $table->boolean('is_base_unit')->default(false); // Unit dasar/terkecil (misal: pcs)
            
            // Pricing untuk unit ini
            $table->decimal('selling_price', 12, 2); // Harga jual untuk unit ini
            $table->decimal('cost_price', 12, 2)->nullable(); // Harga beli untuk unit ini
            $table->decimal('wholesale_price', 12, 2)->nullable(); // Harga grosir untuk unit ini
            
            // Barcode khusus untuk unit ini (opsional)
            $table->string('barcode')->nullable()->unique();
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0); // Urutan tampilan
            
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'is_active']);
            $table->index('barcode');
            
            // Ensure one base unit per product
            $table->unique(['product_id', 'unit_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
