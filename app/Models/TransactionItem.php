<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_sku',
        'product_name',
        'unit_price',
        'quantity',
        'total_price',
        // Tenant fields
        'tenant_id',
        'tenant_name',
        'commission_type',
        'commission_value',
        'commission_amount',
        'tenant_amount',
        'item_notes',
    ];

    protected $casts = [
        'unit_price'        => 'decimal:2',
        'total_price'       => 'decimal:2',
        'commission_value'  => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'tenant_amount'     => 'decimal:2',
        'quantity'          => 'integer',
    ];

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Accessors
    public function getFormattedUnitPriceAttribute()
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }

    public function getFormattedTotalPriceAttribute()
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }
}
