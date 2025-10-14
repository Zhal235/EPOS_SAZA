<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'last_login_at',
        'customer_type',
        'class',
        'nis',
        'nip',
        'subject',
        'experience',
        'rfid_number',
        'balance',
        'spending_limit',
        'last_topup_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'balance' => 'decimal:2',
            'spending_limit' => 'decimal:2',
            'last_topup_at' => 'datetime',
        ];
    }

    /**
     * Valid roles for users
     */
    public static function getValidRoles(): array
    {
        return ['admin', 'manager', 'cashier', 'customer'];
    }

    /**
     * Valid customer types
     */
    public static function getValidCustomerTypes(): array
    {
        return ['regular', 'santri', 'guru', 'umum'];
    }

    /**
     * Check if user can login (customers cannot login)
     */
    public function canLogin(): bool
    {
        return $this->role !== 'customer' && $this->is_active;
    }

    /**
     * Check if user has admin role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has manager role
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user has cashier role
     */
    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    /**
     * Check if user can access admin features
     */
    public function canAccessAdmin(): bool
    {
        return in_array($this->role, ['admin', 'manager']);
    }

    /**
     * Check if user is guru
     */
    public function isGuru(): bool
    {
        return $this->customer_type === 'guru';
    }

    /**
     * Check if user is santri
     */
    public function isSantri(): bool
    {
        return $this->customer_type === 'santri';
    }

    /**
     * Check if user is customer (santri/guru)
     */
    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Get formatted balance
     */
    public function getFormattedBalanceAttribute(): string
    {
        return 'Rp ' . number_format($this->balance, 0, ',', '.');
    }

    /**
     * Get formatted spending limit
     */
    public function getFormattedSpendingLimitAttribute(): string
    {
        return 'Rp ' . number_format($this->spending_limit, 0, ',', '.');
    }

    /**
     * Check if santri can afford the amount
     */
    public function canAfford(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Check if amount is within spending limit
     */
    public function isWithinSpendingLimit(float $amount): bool
    {
        if ($this->spending_limit <= 0) {
            return true; // No limit set
        }
        return $amount <= $this->spending_limit;
    }

    /**
     * Deduct balance for santri
     */
    public function deductBalance(float $amount): bool
    {
        if (!$this->canAfford($amount)) {
            return false;
        }

        $this->balance -= $amount;
        return $this->save();
    }

    /**
     * Add balance for santri (top up)
     */
    public function addBalance(float $amount): bool
    {
        $this->balance += $amount;
        $this->last_topup_at = now();
        return $this->save();
    }

    /**
     * Scope for guru only
     */
    public function scopeGuru($query)
    {
        return $query->where('customer_type', 'guru');
    }

    /**
     * Scope for santri only
     */
    public function scopeSantri($query)
    {
        return $query->where('customer_type', 'santri');
    }

    /**
     * Scope for regular customers only (exclude staff/admin)
     */
    public function scopeRegularCustomers($query)
    {
        return $query->where('customer_type', 'regular')
                    ->where('role', 'customer');
    }

    /**
     * Find santri by RFID number
     */
    public static function findByRfid(string $rfidNumber)
    {
        return static::where('rfid_number', $rfidNumber)
                    ->where('customer_type', 'santri')
                    ->first();
    }

    /**
     * Get user's transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }
}
