<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'city',
        'postal_code',
        'credit_limit',
        'payment_terms',
        'is_active',
        'is_tenant_supplier', // true = supplier dummy otomatis untuk tenant foodcourt
        'notes'
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'payment_terms' => 'integer',
        'is_active' => 'boolean',
        'is_tenant_supplier' => 'boolean',
    ];

    // Relationships
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Hanya supplier nyata (bukan dummy tenant)
    public function scopeReal($query)
    {
        return $query->where('is_tenant_supplier', false);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->company_name ?? $this->name;
    }

    public function getProductCountAttribute()
    {
        return $this->products()->count();
    }

    public function getActiveProductCountAttribute()
    {
        return $this->products()->where('is_active', true)->count();
    }

    public function getTotalPurchaseValueAttribute()
    {
        return $this->products()->sum('cost_price');
    }
}
