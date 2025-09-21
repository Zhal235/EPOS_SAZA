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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique(); // Stock Keeping Unit
            $table->string('barcode')->unique()->nullable(); // Barcode for scanning
            $table->string('name');
            $table->text('description')->nullable();
            
            // Category & Supplier Relations
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            
            // Product Details
            $table->string('brand')->nullable();
            $table->string('unit')->default('pcs'); // pcs, kg, gram, liter, ml, dus, lusin
            $table->decimal('weight', 8, 3)->nullable(); // in grams
            $table->string('size')->nullable(); // e.g., "330ml", "250gr"
            
            // Pricing
            $table->decimal('cost_price', 12, 2); // Harga beli
            $table->decimal('selling_price', 12, 2); // Harga jual
            $table->decimal('wholesale_price', 12, 2)->nullable(); // Harga grosir
            $table->integer('wholesale_min_qty')->nullable(); // Min qty untuk grosir
            
            // Stock Management
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(5); // Minimum stock alert
            $table->integer('max_stock')->nullable(); // Maximum stock capacity
            
            // Dates & Status
            $table->date('expiry_date')->nullable(); // Untuk produk makanan/minuman
            $table->date('manufacture_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('track_stock')->default(true);
            
            // Additional Info
            $table->string('image_url')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0); // Tax percentage
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['category_id', 'is_active']);
            $table->index(['supplier_id']);
            $table->index(['stock_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
