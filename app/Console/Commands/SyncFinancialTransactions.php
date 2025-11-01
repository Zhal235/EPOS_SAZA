<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\FinancialTransaction;
use Illuminate\Support\Facades\DB;

class SyncFinancialTransactions extends Command
{
    protected $signature = 'financial:sync';
    protected $description = 'Sync transactions to financial_transactions table';

    public function handle()
    {
        $this->info('Syncing transactions to financial_transactions...');

        try {
            DB::beginTransaction();

            // Sync RFID transactions
            $rfidTransactions = Transaction::where('payment_method', 'rfid')
                ->whereDoesntHave('financialTransaction')
                ->with('items', 'user')
                ->get();

            $synced = 0;
            foreach ($rfidTransactions as $transaction) {
                FinancialTransaction::create([
                    'user_id' => $transaction->user_id ?? auth()->id() ?? 1,
                    'type' => FinancialTransaction::TYPE_RFID_PAYMENT,
                    'category' => FinancialTransaction::CATEGORY_INCOME,
                    'amount' => $transaction->total_amount ?? 0,
                    'santri_id' => $transaction->user->santri_id ?? null,
                    'santri_name' => $transaction->user->name ?? null,
                    'transaction_number' => $transaction->transaction_number,
                    'description' => "RFID Payment - {$transaction->transaction_number}",
                    'status' => FinancialTransaction::STATUS_COMPLETED,
                    'synced_to_simpels' => true,
                    'simpels_sync_at' => now(),
                    'withdrawn_from_simpels' => false,
                    'withdrawal_id' => null,
                    'created_at' => $transaction->created_at,
                    'updated_at' => $transaction->updated_at,
                ]);
                $synced++;
            }

            DB::commit();

            $this->info("✓ Successfully synced {$synced} transactions");
            $this->info("Total RFID transactions: {$rfidTransactions->count()}");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to sync transactions: ' . $e->getMessage());
            return 1;
        }
    }
}
