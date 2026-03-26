<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KebutuhanOrder extends Model
{
    protected $fillable = [
        'order_number',
        'santri_id',
        'santri_name',
        'rfid_uid',
        'items',
        'total_amount',
        'status',
        'simpels_order_id',
        'cashier_id',
        'expired_at',
        'confirmed_at',
        'confirmed_by',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'items'        => 'array',
        'total_amount' => 'decimal:2',
        'expired_at'   => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
            if (empty($order->expired_at)) {
                $order->expired_at = now()->addDay();
            }
        });
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'KBT-' . now()->format('Ymd');
        $last = static::where('order_number', 'like', $prefix . '%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending_confirmation');
    }

    public function scopeForSantri($query, string $santriId)
    {
        return $query->where('santri_id', $santriId);
    }

    public function isExpired(): bool
    {
        return $this->expired_at->isPast() && $this->status === 'pending_confirmation';
    }

    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }
}
