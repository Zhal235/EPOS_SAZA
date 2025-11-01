<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SimpelsWithdrawal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'withdrawal_number',
        'period_start',
        'period_end',
        'total_transactions',
        'total_amount',
        'withdrawn_amount',
        'remaining_amount',
        'status',
        'requested_by',
        'approved_by',
        'approved_at',
        'withdrawn_at',
        'withdrawal_method',
        'bank_name',
        'account_number',
        'account_name',
        'notes',
        'receipt_path',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_amount' => 'decimal:2',
        'withdrawn_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'withdrawn_at' => 'datetime',
    ];

    // Constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_CASH = 'cash';

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($withdrawal) {
            if (empty($withdrawal->withdrawal_number)) {
                $withdrawal->withdrawal_number = static::generateWithdrawalNumber();
            }
        });
    }

    // Relationships
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function transactions(): BelongsToMany
    {
        return $this->belongsToMany(
            FinancialTransaction::class,
            'financial_transaction_withdrawal',
            'simpels_withdrawal_id',
            'financial_transaction_id'
        )->withPivot('amount')->withTimestamps();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    // Accessors
    public function getFormattedTotalAmountAttribute()
    {
        return 'Rp ' . number_format($this->total_amount, 0, ',', '.');
    }

    public function getFormattedWithdrawnAmountAttribute()
    {
        return 'Rp ' . number_format($this->withdrawn_amount, 0, ',', '.');
    }

    public function getFormattedRemainingAmountAttribute()
    {
        return 'Rp ' . number_format($this->remaining_amount, 0, ',', '.');
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Menunggu Approval',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            default => 'Unknown'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_PROCESSING => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }

    public function getWithdrawalMethodLabelAttribute()
    {
        return match($this->withdrawal_method) {
            self::METHOD_BANK_TRANSFER => 'Transfer Bank',
            self::METHOD_CASH => 'Tunai',
            default => '-'
        };
    }

    // Methods
    public static function generateWithdrawalNumber()
    {
        $prefix = 'WD';
        $date = now()->format('Ymd');
        
        $lastWithdrawal = static::where('withdrawal_number', 'like', $prefix . $date . '%')
                                ->orderBy('withdrawal_number', 'desc')
                                ->first();
        
        if ($lastWithdrawal) {
            $lastSequence = (int) substr($lastWithdrawal->withdrawal_number, -4);
            $sequence = $lastSequence + 1;
        } else {
            $sequence = 1;
        }
        
        return $prefix . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function approve($approvedBy)
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'approved_by' => $approvedBy,
            'approved_at' => now(),
        ]);
    }

    public function complete($withdrawalDetails = [])
    {
        $updateData = [
            'status' => self::STATUS_COMPLETED,
            'withdrawn_at' => now(),
        ];

        if (isset($withdrawalDetails['withdrawal_method'])) {
            $updateData['withdrawal_method'] = $withdrawalDetails['withdrawal_method'];
        }
        if (isset($withdrawalDetails['bank_name'])) {
            $updateData['bank_name'] = $withdrawalDetails['bank_name'];
        }
        if (isset($withdrawalDetails['account_number'])) {
            $updateData['account_number'] = $withdrawalDetails['account_number'];
        }
        if (isset($withdrawalDetails['account_name'])) {
            $updateData['account_name'] = $withdrawalDetails['account_name'];
        }
        if (isset($withdrawalDetails['receipt_path'])) {
            $updateData['receipt_path'] = $withdrawalDetails['receipt_path'];
        }

        $this->update($updateData);
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'notes' => $reason ? "Dibatalkan: {$reason}" : 'Dibatalkan'
        ]);
    }
}
