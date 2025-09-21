<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'sku',
        'barcode',
        'name',
        'description',
        'category_id',
        'supplier_id',
        'brand',
        'unit',
        'weight',
        'size',
        'cost_price',
        'selling_price',
        'wholesale_price',
        'wholesale_min_qty',
        'stock_quantity',
        'min_stock',
        'max_stock',
        'expiry_date',
        'manufacture_date',
        'is_active',
        'is_featured',
        'track_stock',
        'image_url',
        'tax_rate',
        'notes'
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'weight' => 'decimal:3',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock' => 'integer',
        'max_stock' => 'integer',
        'wholesale_min_qty' => 'integer',
        'expiry_date' => 'date',
        'manufacture_date' => 'date',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'track_stock' => 'boolean'
    ];

    // Auto-generate SKU if not provided
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($product) {
            if (empty($product->sku)) {
                $product->sku = static::generateSku();
            }
        });
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'min_stock');
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Accessors & Calculated Fields
    public function getProfitMarginAttribute()
    {
        if ($this->cost_price <= 0) return 0;
        return round((($this->selling_price - $this->cost_price) / $this->cost_price) * 100, 2);
    }

    public function getProfitAmountAttribute()
    {
        return round($this->selling_price - $this->cost_price, 2);
    }

    public function getStockValueAttribute()
    {
        return round($this->stock_quantity * $this->cost_price, 2);
    }

    public function getIsLowStockAttribute()
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    public function getIsOutOfStockAttribute()
    {
        return $this->stock_quantity <= 0;
    }

    public function getStockStatusAttribute()
    {
        if ($this->is_out_of_stock) return 'out_of_stock';
        if ($this->is_low_stock) return 'low_stock';
        return 'in_stock';
    }

    public function getFormattedSellingPriceAttribute()
    {
        return 'Rp ' . number_format($this->selling_price, 0, ',', '.');
    }

    public function getFormattedCostPriceAttribute()
    {
        return 'Rp ' . number_format($this->cost_price, 0, ',', '.');
    }

    // Methods
    public function updateStock($quantity, $operation = 'add')
    {
        if (!$this->track_stock) return true;

        if ($operation === 'add') {
            $this->stock_quantity += $quantity;
        } elseif ($operation === 'subtract') {
            $this->stock_quantity = max(0, $this->stock_quantity - $quantity);
        } elseif ($operation === 'set') {
            $this->stock_quantity = max(0, $quantity);
        }

        return $this->save();
    }

    public function canSell($quantity = 1)
    {
        if (!$this->is_active) return false;
        if (!$this->track_stock) return true;
        return $this->stock_quantity >= $quantity;
    }

    public static function generateSku()
    {
        do {
            $sku = 'PRD' . strtoupper(Str::random(6));
        } while (static::where('sku', $sku)->exists());
        
        return $sku;
    }

    public function generateBarcode()
    {
        if (empty($this->barcode)) {
            do {
                $barcode = '210' . str_pad(rand(0, 9999999999), 10, '0', STR_PAD_LEFT);
            } while (static::where('barcode', $barcode)->exists());
            
            $this->barcode = $barcode;
            $this->save();
        }
        
        return $this->barcode;
    }
}
