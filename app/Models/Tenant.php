<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'booth_number',
        'owner_name',
        'phone',
        'description',
        'commission_type',
        'commission_value',
        'is_active',
        'sort_order',
        'balance',
        'account_bank',
        'account_number',
        'account_name',
    ];

    protected $casts = [
        'commission_value' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tenant) {
            if (empty($tenant->slug)) {
                $tenant->slug = Str::slug($tenant->name);
            }
        });
    }

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
    
    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(TenantLedger::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(TenantWithdrawal::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Accessors
    public function getActiveProductCountAttribute(): int
    {
        return $this->products()->where('is_active', true)->count();
    }

    /**
     * Hitung komisi untuk satu item berdasarkan setting tenant.
     * Mengembalikan nominal komisi (bukan persentase).
     */
    public function calculateCommission(float $unitPrice, int $quantity, ?string $overrideType = null, ?float $overrideValue = null): float
    {
        $type  = $overrideType  ?? $this->commission_type;
        $value = $overrideValue ?? (float) $this->commission_value;

        if ($type === 'percentage') {
            $commissionPerUnit = round($unitPrice * $value / 100, 2);
        } else {
            $commissionPerUnit = $value;
        }

        return round($commissionPerUnit * $quantity, 2);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->booth_number
            ? "Booth {$this->booth_number} - {$this->name}"
            : $this->name;
    }

    public function getCommissionLabelAttribute(): string
    {
        if ($this->commission_type === 'percentage') {
            return number_format($this->commission_value, 2) . '%';
        }

        return 'Rp ' . number_format($this->commission_value, 0, ',', '.');
    }
}
