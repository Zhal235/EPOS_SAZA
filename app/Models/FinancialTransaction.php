<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class FinancialTransaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'transaction_id',
        'type',
        'category',
        'santri_id',
        'santri_name',
        'rfid_tag',
        'amount',
        'previous_balance',
        'new_balance',
        'payment_method',
        'reference_number',
        'synced_to_simpels',
        'synced_at',
        'sync_response',
        'withdrawn_from_simpels',
        'withdrawn_at',
        'withdrawal_reference',
        'withdrawn_by',
        'description',
        'notes',
        'status',
        'user_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'previous_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'synced_to_simpels' => 'boolean',
        'withdrawn_from_simpels' => 'boolean',
        'synced_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    // Constants
    const TYPE_RFID_PAYMENT = 'rfid_payment';
    const TYPE_REFUND = 'refund';
    const TYPE_WITHDRAWAL_SIMPELS = 'withdrawal_simpels';
    const TYPE_CASH_IN = 'cash_in';
    const TYPE_CASH_OUT = 'cash_out';
    const TYPE_TENANT_PAYOUT = 'tenant_payout'; // Pembayaran/pencairan saldo ke tenant foodcourt

    const CATEGORY_INCOME = 'income';
    const CATEGORY_EXPENSE = 'expense';
    const CATEGORY_TRANSFER = 'transfer';

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_number)) {
                $transaction->transaction_number = static::generateTransactionNumber();
            }
        });
    }

    // Relationships
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawnBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'withdrawn_by');
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(SimpelsWithdrawal::class, 'withdrawal_id');
    }

    public function withdrawals(): BelongsToMany
    {
        return $this->belongsToMany(
            SimpelsWithdrawal::class,
            'financial_transaction_withdrawal',
            'financial_transaction_id',
            'simpels_withdrawal_id'
        )->withPivot('amount')->withTimestamps();
    }

    // Scopes
    public function scopeRfidPayments($query)
    {
        return $query->where('type', self::TYPE_RFID_PAYMENT);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('type', self::TYPE_WITHDRAWAL_SIMPELS);
    }

    public function scopeIncome($query)
    {
        return $query->where('category', self::CATEGORY_INCOME);
    }

    public function scopeExpense($query)
    {
        return $query->where('category', self::CATEGORY_EXPENSE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeSyncedToSimpels($query)
    {
        return $query->where('synced_to_simpels', true);
    }

    public function scopeNotSynced($query)
    {
        return $query->where('synced_to_simpels', false);
    }

    public function scopeWithdrawn($query)
    {
        return $query->where('withdrawn_from_simpels', true);
    }

    public function scopeNotWithdrawn($query)
    {
        return $query->where('withdrawn_from_simpels', false);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->endOfDay()
        ]);
    }

    // Accessors
    public function getFormattedAmountAttribute()
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getTypeLabelAttribute()
    {
        return match($this->type) {
            self::TYPE_RFID_PAYMENT => 'Pembayaran RFID',
            self::TYPE_REFUND => 'Pengembalian',
            self::TYPE_WITHDRAWAL_SIMPELS => 'Penarikan SIMPels',
            self::TYPE_CASH_IN => 'Kas Masuk',
            self::TYPE_CASH_OUT => 'Kas Keluar',
            self::TYPE_TENANT_PAYOUT => 'Pembayaran ke Tenant',
            default => 'Lainnya'
        };
    }

    public function getCategoryLabelAttribute()
    {
        return match($this->category) {
            self::CATEGORY_INCOME => 'Pemasukan',
            self::CATEGORY_EXPENSE => 'Pengeluaran',
            self::CATEGORY_TRANSFER => 'Transfer',
            default => 'Lainnya'
        };
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_REFUNDED => 'Dikembalikan',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_FAILED => 'red',
            self::STATUS_REFUNDED => 'blue',
            default => 'gray'
        };
    }

    // Methods
    public static function generateTransactionNumber()
    {
        $prefix = 'FIN';
        $date = now()->format('Ymd');
        
        $lastTransaction = static::where('transaction_number', 'like', $prefix . $date . '%')
                                ->orderBy('transaction_number', 'desc')
                                ->first();
        
        if ($lastTransaction) {
            $lastSequence = (int) substr($lastTransaction->transaction_number, -4);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function markAsSynced($response = null)
    {
        $this->update([
            'synced_to_simpels' => true,
            'synced_at' => now(),
            'sync_response' => $response ? json_encode($response) : null
        ]);
    }

    public function markAsWithdrawn($withdrawalReference, $withdrawnBy)
    {
        $this->update([
            'withdrawn_from_simpels' => true,
            'withdrawn_at' => now(),
            'withdrawal_reference' => $withdrawalReference,
            'withdrawn_by' => $withdrawnBy
        ]);
    }

    // Static helper methods
    public static function recordRfidPayment(Transaction $transaction, array $santriData, $userId)
    {
        return static::create([
            'transaction_id' => $transaction->id,
            'type' => self::TYPE_RFID_PAYMENT,
            'category' => self::CATEGORY_INCOME,
            'santri_id' => $santriData['id'] ?? null,
            'santri_name' => $santriData['name'] ?? null,
            'rfid_tag' => $santriData['rfid'] ?? null,
            'amount' => $transaction->total_amount,
            'previous_balance' => $santriData['previous_balance'] ?? null,
            'new_balance' => $santriData['new_balance'] ?? null,
            'payment_method' => 'rfid',
            'description' => "Pembayaran RFID - {$transaction->transaction_number}",
            'status' => self::STATUS_COMPLETED,
            'user_id' => $userId,
        ]);
    }

    public static function recordRefund(Transaction $originalTransaction, $amount, $reason, $userId)
    {
        return static::create([
            'transaction_id' => $originalTransaction->id,
            'reference_number' => $originalTransaction->transaction_number,
            'type' => self::TYPE_REFUND,
            'category' => self::CATEGORY_EXPENSE,
            'santri_id' => $originalTransaction->customer_id ?? null,
            'santri_name' => $originalTransaction->customer_name,
            'rfid_tag' => null, // Will be filled if refund is via RFID
            'amount' => $amount,
            'payment_method' => $originalTransaction->payment_method,
            'description' => "Pengembalian - {$originalTransaction->transaction_number}",
            'notes' => $reason,
            'status' => self::STATUS_COMPLETED,
            'user_id' => $userId,
        ]);
    }
}
