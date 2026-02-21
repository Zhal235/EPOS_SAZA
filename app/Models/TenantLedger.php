<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantLedger extends Model
{
    protected $table = 'tenant_ledger';

    protected $fillable = [
        'tenant_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'transaction_item_id',
        'withdrawal_id',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function transactionItem(): BelongsTo
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(TenantWithdrawal::class);
    }
}