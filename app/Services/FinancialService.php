<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Transaction;
use App\Models\SimpelsWithdrawal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialService
{
    /**
     * Record RFID payment transaction
     */
    public function recordRfidPayment(Transaction $transaction, array $santriData): FinancialTransaction
    {
        try {
            $financialTransaction = FinancialTransaction::create([
                'transaction_id' => $transaction->id,
                'type' => FinancialTransaction::TYPE_RFID_PAYMENT,
                'category' => FinancialTransaction::CATEGORY_INCOME,
                'santri_id' => $santriData['id'] ?? null,
                'santri_name' => $santriData['name'] ?? $transaction->customer_name,
                'rfid_tag' => $santriData['rfid'] ?? null,
                'amount' => $transaction->total_amount,
                'previous_balance' => $santriData['previous_balance'] ?? null,
                'new_balance' => $santriData['new_balance'] ?? null,
                'payment_method' => 'rfid',
                'description' => "Pembayaran RFID - {$transaction->transaction_number}",
                'status' => FinancialTransaction::STATUS_COMPLETED,
                'user_id' => $transaction->user_id,
                'synced_to_simpels' => true,
                'synced_at' => now(),
            ]);

            Log::info('Financial transaction recorded for RFID payment', [
                'financial_transaction_id' => $financialTransaction->id,
                'transaction_number' => $financialTransaction->transaction_number,
                'amount' => $financialTransaction->amount
            ]);

            return $financialTransaction;

        } catch (\Exception $e) {
            Log::error('Failed to record financial transaction', [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Record refund transaction
     */
    public function recordRefund(Transaction $originalTransaction, float $amount, string $reason, ?array $santriData = null): FinancialTransaction
    {
        try {
            DB::beginTransaction();

            // Find original financial transaction
            $originalFinancial = FinancialTransaction::where('transaction_id', $originalTransaction->id)->first();

            $refundTransaction = FinancialTransaction::create([
                'transaction_id' => $originalTransaction->id,
                'reference_number' => $originalTransaction->transaction_number,
                'type' => FinancialTransaction::TYPE_REFUND,
                'category' => FinancialTransaction::CATEGORY_EXPENSE,
                'santri_id' => $santriData['id'] ?? $originalFinancial?->santri_id,
                'santri_name' => $santriData['name'] ?? $originalTransaction->customer_name,
                'rfid_tag' => $santriData['rfid'] ?? $originalFinancial?->rfid_tag,
                'amount' => $amount,
                'payment_method' => $originalTransaction->payment_method,
                'description' => "Pengembalian - {$originalTransaction->transaction_number}",
                'notes' => $reason,
                'status' => FinancialTransaction::STATUS_COMPLETED,
                'user_id' => auth()->id(),
            ]);

            // Update original transaction status if full refund
            if ($amount >= $originalTransaction->total_amount) {
                $originalTransaction->update(['status' => 'refunded']);
                if ($originalFinancial) {
                    $originalFinancial->update(['status' => FinancialTransaction::STATUS_REFUNDED]);
                }
            }

            DB::commit();

            Log::info('Financial refund transaction recorded', [
                'refund_transaction_id' => $refundTransaction->id,
                'original_transaction' => $originalTransaction->transaction_number,
                'amount' => $amount
            ]);

            return $refundTransaction;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record refund transaction', [
                'transaction_id' => $originalTransaction->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get financial summary for a period
     */
    public function getSummary(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfDay();

        $query = FinancialTransaction::whereBetween('created_at', [$startDate, $endDate]);

        $totalIncome = (clone $query)->income()->completed()->sum('amount');
        $totalExpense = (clone $query)->expense()->completed()->sum('amount');
        $totalRfidPayments = (clone $query)->rfidPayments()->completed()->sum('amount');
        $totalRefunds = (clone $query)->refunds()->completed()->sum('amount');
        $countTransactions = (clone $query)->completed()->count();

        $pendingSync = FinancialTransaction::notSynced()
            ->where('type', FinancialTransaction::TYPE_RFID_PAYMENT)
            ->count();

        $pendingWithdrawal = FinancialTransaction::rfidPayments()
            ->completed()
            ->notWithdrawn()
            ->sum('amount');

        $withdrawnAmount = FinancialTransaction::rfidPayments()
            ->completed()
            ->withdrawn()
            ->sum('amount');

        return [
            'total_income' => (float) $totalIncome,
            'total_expense' => (float) $totalExpense,
            'net_income' => (float) ($totalIncome - $totalExpense),
            'total_rfid_payments' => (float) $totalRfidPayments,
            'total_refunds' => (float) $totalRefunds,
            'count_transactions' => $countTransactions,
            'pending_sync_count' => $pendingSync,
            'pending_withdrawal_amount' => (float) $pendingWithdrawal,
            'withdrawn_amount' => (float) $withdrawnAmount,
            'available_for_withdrawal' => (float) $pendingWithdrawal,
        ];
    }

    /**
     * Get transactions that haven't been withdrawn from SIMPels
     */
    public function getAvailableForWithdrawal(?Carbon $startDate = null, ?Carbon $endDate = null)
    {
        $query = FinancialTransaction::rfidPayments()
            ->completed()
            ->whereNull('withdrawal_id'); // Transaksi yang belum masuk withdrawal manapun

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
        }
        // Jika startDate & endDate null, ambil semua transaksi yang available

        return $query->orderBy('created_at')->get();
    }

    /**
     * Create withdrawal request
     */
    public function createWithdrawalRequest(array $data): SimpelsWithdrawal
    {
        try {
            DB::beginTransaction();

            // If period not specified, use all available transactions
            $startDate = $data['period_start'] ? Carbon::parse($data['period_start']) : null;
            $endDate = $data['period_end'] ? Carbon::parse($data['period_end']) : null;

            // Get transactions in the period (or all if period null) that haven't been withdrawn
            $allTransactions = $this->getAvailableForWithdrawal($startDate, $endDate);

            if ($allTransactions->isEmpty()) {
                throw new \Exception('Tidak ada transaksi yang tersedia untuk ditarik. Saldo tersedia: Rp 0');
            }

            // If specific amount is requested, validate and take transactions
            $requestedAmount = isset($data['withdrawal_amount']) && $data['withdrawal_amount'] > 0 
                ? (float) $data['withdrawal_amount'] 
                : null;

            if ($requestedAmount) {
                // Ambil transaksi sebanyak mungkin sampai mendekati atau melebihi requested amount
                $transactions = collect();
                $currentTotal = 0;
                
                // Sort transactions by amount ascending untuk ambil yang terkecil dulu
                $sortedTransactions = $allTransactions->sortBy('amount');
                
                foreach ($sortedTransactions as $transaction) {
                    // Tambahkan transaksi sampai total >= requested atau tidak ada lagi
                    $transactions->push($transaction);
                    $currentTotal += $transaction->amount;
                    
                    // Jika sudah mencapai atau melebihi target, stop
                    if ($currentTotal >= $requestedAmount) {
                        break;
                    }
                }
                
                $totalAmount = $transactions->sum('amount');
                
                // Validasi: pastikan ada transaksi
                if ($transactions->isEmpty()) {
                    throw new \Exception("Tidak ada transaksi yang tersedia untuk ditarik.");
                }
                
                // Info: jika total lebih besar dari requested, beri info
                if ($totalAmount > $requestedAmount) {
                    Log::info("Withdrawal amount adjusted", [
                        'requested' => $requestedAmount,
                        'actual' => $totalAmount,
                        'reason' => 'Rounded up to cover full transactions'
                    ]);
                }
            } else {
                // Take all available transactions
                $transactions = $allTransactions;
                $totalAmount = $transactions->sum('amount');
            }

            // Determine period from transactions if not specified
            if (!$startDate) {
                $startDate = $transactions->min('created_at');
            }
            if (!$endDate) {
                $endDate = $transactions->max('created_at');
            }

            // Create withdrawal record in EPOS
            $withdrawal = SimpelsWithdrawal::create([
                'period_start' => $startDate,
                'period_end' => $endDate,
                'total_transactions' => $transactions->count(),
                'total_amount' => $totalAmount,
                'withdrawn_amount' => 0,
                'remaining_amount' => $totalAmount,
                'status' => SimpelsWithdrawal::STATUS_PENDING,
                'requested_by' => auth()->id(),
                'withdrawal_method' => $data['withdrawal_method'] ?? SimpelsWithdrawal::METHOD_CASH,
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'account_name' => $data['account_name'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Link transactions to withdrawal and mark them
            $transactionIds = $transactions->pluck('id')->toArray();
            
            foreach ($transactions as $transaction) {
                $withdrawal->transactions()->attach($transaction->id, [
                    'amount' => $transaction->amount
                ]);
            }
            
            // Mark all transactions with withdrawal_id using direct DB update
            DB::table('financial_transactions')
                ->whereIn('id', $transactionIds)
                ->update(['withdrawal_id' => $withdrawal->id]);
                
            Log::info('Transactions linked to withdrawal', [
                'withdrawal_id' => $withdrawal->id,
                'transaction_ids' => $transactionIds,
                'count' => count($transactionIds)
            ]);

            // Send request to SIMPels API
            try {
                $simpelsApi = app(\App\Services\SimpelsApiService::class);
                
                $transactionsData = $transactions->map(function($t) {
                    return [
                        'transaction_number' => $t->transaction_number,
                        'amount' => $t->amount,
                        'santri_id' => $t->santri_id,
                        'santri_name' => $t->santri_name,
                        'transaction_date' => $t->created_at->format('Y-m-d'),
                    ];
                })->toArray();

                $simpelsResponse = $simpelsApi->createWithdrawalRequest([
                    'withdrawal_number' => $withdrawal->withdrawal_number,
                    'amount' => $totalAmount, // SIMPELS expects 'amount' not 'total_amount'
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                    'total_transactions' => $transactions->count(),
                    'withdrawal_method' => $withdrawal->withdrawal_method,
                    'bank_name' => $withdrawal->bank_name,
                    'account_number' => $withdrawal->account_number,
                    'account_name' => $withdrawal->account_name,
                    'requested_by' => auth()->user()->name ?? 'Admin EPOS',
                    'notes' => $withdrawal->notes,
                    'transactions' => $transactionsData,
                ]);

                if (!$simpelsResponse || !$simpelsResponse['success']) {
                    throw new \Exception('Gagal mengirim request ke SIMPels: ' . ($simpelsResponse['message'] ?? 'Unknown error'));
                }

                Log::info('Withdrawal request sent to SIMPels', [
                    'withdrawal_number' => $withdrawal->withdrawal_number,
                    'simpels_response' => $simpelsResponse
                ]);

            } catch (\Exception $apiError) {
                Log::error('Failed to send withdrawal to SIMPels API', [
                    'error' => $apiError->getMessage(),
                    'withdrawal_number' => $withdrawal->withdrawal_number
                ]);
                // Continue despite API error - local record already created
            }

            DB::commit();

            Log::info('Withdrawal request created', [
                'withdrawal_id' => $withdrawal->id,
                'withdrawal_number' => $withdrawal->withdrawal_number,
                'total_amount' => $totalAmount,
                'transaction_count' => $transactions->count()
            ]);

            return $withdrawal;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create withdrawal request', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Approve withdrawal request
     */
    public function approveWithdrawal(SimpelsWithdrawal $withdrawal, int $approvedBy): void
    {
        if ($withdrawal->status !== SimpelsWithdrawal::STATUS_PENDING) {
            throw new \Exception('Penarikan ini tidak dalam status pending');
        }

        $withdrawal->approve($approvedBy);

        Log::info('Withdrawal approved', [
            'withdrawal_id' => $withdrawal->id,
            'withdrawal_number' => $withdrawal->withdrawal_number,
            'approved_by' => $approvedBy
        ]);
    }

    /**
     * Complete withdrawal and mark transactions as withdrawn
     */
    public function completeWithdrawal(SimpelsWithdrawal $withdrawal, array $details = []): void
    {
        try {
            DB::beginTransaction();

            if ($withdrawal->status !== SimpelsWithdrawal::STATUS_PROCESSING) {
                throw new \Exception('Penarikan harus disetujui terlebih dahulu');
            }

            // Mark all related transactions as withdrawn
            $withdrawnBy = auth()->id();
            foreach ($withdrawal->transactions as $transaction) {
                $transaction->markAsWithdrawn($withdrawal->withdrawal_number, $withdrawnBy);
            }

            // Update withdrawal amounts
            $withdrawal->update([
                'withdrawn_amount' => $withdrawal->total_amount,
                'remaining_amount' => 0,
            ]);

            // Complete withdrawal
            $withdrawal->complete($details);

            DB::commit();

            Log::info('Withdrawal completed', [
                'withdrawal_id' => $withdrawal->id,
                'withdrawal_number' => $withdrawal->withdrawal_number,
                'amount' => $withdrawal->total_amount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete withdrawal', [
                'withdrawal_id' => $withdrawal->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Cancel withdrawal request
     */
    public function cancelWithdrawal(SimpelsWithdrawal $withdrawal, ?string $reason = null): void
    {
        if ($withdrawal->status === SimpelsWithdrawal::STATUS_COMPLETED) {
            throw new \Exception('Penarikan yang sudah selesai tidak dapat dibatalkan');
        }

        $withdrawal->cancel($reason);

        Log::info('Withdrawal cancelled', [
            'withdrawal_id' => $withdrawal->id,
            'withdrawal_number' => $withdrawal->withdrawal_number,
            'reason' => $reason
        ]);
    }

    /**
     * Get daily transaction chart data
     */
    public function getDailyChartData(Carbon $startDate, Carbon $endDate): array
    {
        $days = $endDate->diffInDays($startDate) + 1;
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            $income = FinancialTransaction::income()
                ->completed()
                ->whereDate('created_at', $date)
                ->sum('amount');
            
            $expense = FinancialTransaction::expense()
                ->completed()
                ->whereDate('created_at', $date)
                ->sum('amount');

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'date_formatted' => $date->format('d M'),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'net' => (float) ($income - $expense)
            ];
        }

        return $data;
    }

    /**
     * Sync pending transactions to SIMPels
     */
    public function syncPendingTransactions(): array
    {
        $pendingTransactions = FinancialTransaction::notSynced()
            ->where('type', FinancialTransaction::TYPE_RFID_PAYMENT)
            ->where('status', FinancialTransaction::STATUS_COMPLETED)
            ->get();

        $synced = 0;
        $failed = 0;

        foreach ($pendingTransactions as $transaction) {
            try {
                // TODO: Implement actual sync to SIMPels API
                // For now, just mark as synced
                $transaction->markAsSynced([
                    'synced_at' => now(),
                    'method' => 'manual_sync'
                ]);
                $synced++;
            } catch (\Exception $e) {
                Log::error('Failed to sync transaction', [
                    'transaction_id' => $transaction->id,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        return [
            'total' => $pendingTransactions->count(),
            'synced' => $synced,
            'failed' => $failed
        ];
    }
}
