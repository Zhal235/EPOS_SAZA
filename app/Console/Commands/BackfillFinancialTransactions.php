<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\FinancialTransaction;
use App\Services\FinancialService;

class BackfillFinancialTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epos:backfill-financial-transactions {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create missing FinancialTransaction records for existing RFID transactions';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Searching for RFID transactions without FinancialTransaction records...');

        $transactions = Transaction::where('payment_method', 'rfid')
            ->whereNotIn('id', function ($q) {
                $q->select('transaction_id')->from(with(new FinancialTransaction)->getTable());
            })
            ->orderBy('created_at')
            ->get();

        $count = $transactions->count();
        $this->info("Found {$count} transactions to process.");

        if ($count === 0) {
            return 0;
        }

        $service = app(FinancialService::class);

        foreach ($transactions as $t) {
            $this->line("Processing transaction {$t->transaction_number} (id: {$t->id}) - Rp " . number_format($t->total_amount,0,',','.'));

            $santriData = [
                'id' => $t->customer_id ?? null,
                'name' => $t->customer_name ?? null,
                'rfid' => null,
                'previous_balance' => null,
                'new_balance' => null,
            ];

            // Try to extract RFID from notes if present
            if ($t->notes && preg_match('/RFID:\s*([0-9A-Za-z_-]+)/i', $t->notes, $m)) {
                $santriData['rfid'] = $m[1];
            }

            if ($dryRun) {
                $this->info('[dry-run] would create financial transaction for ' . $t->transaction_number);
                continue;
            }

            try {
                $ft = $service->recordRfidPayment($t, $santriData);
                // ensure created_at aligns with original transaction so summaries by date match
                $ft->update(['created_at' => $t->created_at, 'updated_at' => $t->updated_at]);
                $this->info("Created FinancialTransaction id={$ft->id}");
            } catch (\Exception $e) {
                $this->error("Failed to create for {$t->transaction_number}: " . $e->getMessage());
            }
        }

        $this->info('Backfill complete.');

        return 0;
    }
}
