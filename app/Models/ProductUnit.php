<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUnit extends Model
{
    protected $fillable = [
        'product_id',
        'unit_name',
        'conversion_rate',
        'is_base_unit',
        'selling_price',
        'cost_price',
        'wholesale_price',
        'barcode',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'conversion_rate' => 'integer',
        'is_base_unit' => 'boolean',
        'selling_price' => 'float',
        'cost_price' => 'float',
        'wholesale_price' => 'float',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    /**
     * Relationship to Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope untuk unit yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk base unit
     */
    public function scopeBaseUnit($query)
    {
        return $query->where('is_base_unit', true);
    }

    /**
     * Hitung stock dalam unit ini
     */
    public function getStockInThisUnit(): float
    {
        if ($this->conversion_rate == 0) {
            return 0;
        }
        
        return floor($this->product->stock_quantity / $this->conversion_rate);
    }

    /**
     * Format tampilan unit dengan stock
     */
    public function getDisplayNameWithStock(): string
    {
        $stock = $this->getStockInThisUnit();
        return "{$this->unit_name} (Stock: {$stock})";
    }

    /**
     * Get formatted price
     */
    public function getFormattedPrice(): string
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }
}
