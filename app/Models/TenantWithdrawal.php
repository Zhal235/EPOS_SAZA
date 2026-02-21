<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantWithdrawal extends Model
{
    protected $fillable = [
        'tenant_id',
        'reference_number',
        'amount',
        'status',
        'processed_by',
        'processed_at',
        'proof_image',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($withdrawal) {
            if (empty($withdrawal->reference_number)) {
                $withdrawal->reference_number = 'WD-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}