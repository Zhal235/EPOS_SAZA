<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FinancialTransaction;
use App\Models\SimpelsWithdrawal;
use Illuminate\Support\Facades\DB;

class MarkManualWithdrawal extends Command
{
    protected $signature = 'financial:mark-manual-withdrawal {amount}';
    protected $description = 'Mark transactions as manually withdrawn from SIMPels';

    public function handle()
    {
        $amount = (float) $this->argument('amount');
        
        $this->info("Marking Rp " . number_format($amount, 0) . " as manually withdrawn...");

        try {
            DB::beginTransaction();

            // Create a virtual withdrawal record to mark manual withdrawal
            $withdrawal = SimpelsWithdrawal::create([
                'period_start' => now()->startOfMonth(),
                'period_end' => now(),
                'total_transactions' => 0,
                'total_amount' => $amount,
                'withdrawn_amount' => $amount,
                'remaining_amount' => 0,
                'status' => SimpelsWithdrawal::STATUS_COMPLETED,
                'requested_by' => auth()->id() ?? 1,
                'withdrawal_method' => SimpelsWithdrawal::METHOD_CASH,
                'notes' => 'Manual withdrawal from SIMPels (existing data)',
                'approved_by' => auth()->id() ?? 1,
                'approved_at' => now(),
                'withdrawn_at' => now(),
            ]);

            // Get oldest transactions that sum up to the amount
            $transactions = FinancialTransaction::where('type', 'rfid_payment')
                ->whereNull('withdrawal_id')
                ->orderBy('created_at')
                ->get();

            $totalMarked = 0;
            $markedCount = 0;

            foreach ($transactions as $transaction) {
                if ($totalMarked >= $amount) {
                    break;
                }

                $transaction->update([
                    'withdrawal_id' => $withdrawal->id,
                    'withdrawn_from_simpels' => true
                ]);

                $withdrawal->transactions()->attach($transaction->id, [
                    'amount' => $transaction->amount
                ]);

                $totalMarked += $transaction->amount;
                $markedCount++;

                $this->line("✓ Marked: " . $transaction->transaction_number . " - Rp " . number_format($transaction->amount, 0));
            }

            DB::commit();

            $this->info("\n✓ Successfully marked {$markedCount} transactions");
            $this->info("Total marked: Rp " . number_format($totalMarked, 0));
            $this->info("Withdrawal ID: {$withdrawal->id}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed: ' . $e->getMessage());
            return 1;
        }
    }
}
