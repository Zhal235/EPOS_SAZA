<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FinancialTransaction;
use App\Models\SimpelsWithdrawal;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Financial extends Component
{
    use WithPagination;

    public $activeTab = 'overview';
    public $dateFrom;
    public $dateTo;
    public $filterType = 'all';
    public $filterStatus = 'all';
    public $searchQuery = '';
    public $outletModeFilter = ''; // '' = all, 'store', 'foodcourt'
    public $posSearch = '';

    // Withdrawal modal
    public $showWithdrawalModal = false;
    public $withdrawalPeriodStart;
    public $withdrawalPeriodEnd;
    public $withdrawalAmount;
    public $withdrawalMethod = 'cash';
    public $bankName = '';
    public $accountNumber = '';
    public $accountName = '';
    public $withdrawalNotes = '';
    
    // Expense modal
    public $showExpenseModal = false;
    public $expenseAmount;
    public $expenseDescription = '';
    public $expenseCategory = 'operational';
    public $expenseNotes = '';

    public $refreshKey = 0;

    protected $queryString = ['activeTab', 'dateFrom', 'dateTo', 'outletModeFilter'];
    
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function mount()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->withdrawalPeriodStart = now()->startOfMonth()->format('Y-m-d');
        $this->withdrawalPeriodEnd = now()->format('Y-m-d');
    }
    
    public function forceRefresh()
    {
        $this->refreshKey++;
        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => 'Diperbarui',
            'message' => 'Dashboard diperbarui'
        ]);
    }

    // Computed property untuk check pending withdrawals (cached per request)
    public function getHasPendingWithdrawalsProperty()
    {
        return $this->getPendingWithdrawalsCountProperty() > 0;
    }

    public function getPendingWithdrawalsCountProperty()
    {
        return SimpelsWithdrawal::where(function($query) {
                $query->whereNull('simpels_status')
                      ->orWhere('simpels_status', 'pending');
            })
            ->where('status', '!=', 'cancelled')
            ->count();
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function updatingSearchQuery()
    {
        $this->resetPage();
    }

    public function updatingPosSearch()
    {
        $this->resetPage();
    }

    public function updatingOutletModeFilter()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function applyDateFilter()
    {
        $this->resetPage();
        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => 'Filter Diterapkan',
            'message' => 'Data diperbarui'
        ]);
    }

    public function resetFilters()
    {
        $this->dateFrom = now()->startOfMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->filterType = 'all';
        $this->filterStatus = 'all';
        $this->searchQuery = '';
        $this->resetPage();
    }

    public function getDashboardSummary()
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to   = Carbon::parse($this->dateTo)->endOfDay();

        // ── POS Transactions (tabel transactions) ────────────────────────
        $posBase = Transaction::whereBetween('created_at', [$from, $to])
            ->where('status', 'completed');

        $posStore      = (clone $posBase)->where('outlet_mode', 'store');
        $posFoodcourt  = (clone $posBase)->where('outlet_mode', 'foodcourt');

        $posStoreSales      = (float) (clone $posStore)->sum('total_amount');
        $posFoodcourtSales  = (float) (clone $posFoodcourt)->sum('total_amount');
        $posTotalSales      = $posStoreSales + $posFoodcourtSales;
        $posTotalCount      = (int) (clone $posBase)->count();
        $posStoreCount      = (int) (clone $posStore)->count();
        $posFoodcourtCount  = (int) (clone $posFoodcourt)->count();

        // ── Profit Toko = Penjualan - HPP (pakai snapshot cost_price di transaction_items) ──
        $storeTransactionIds = (clone $posStore)->pluck('id');
        $storeProfitRaw = DB::table('transaction_items as ti')
            ->whereIn('ti.transaction_id', $storeTransactionIds)
            ->select(
                DB::raw('SUM(ti.total_price) as revenue'),
                DB::raw('SUM(ti.cost_price * ti.quantity) as cogs')
            )
            ->first();
        $posStoreProfit = (float) (($storeProfitRaw->revenue ?? 0) - ($storeProfitRaw->cogs ?? 0));

        // ── Profit Foodcourt = Total Komisi dari transaction_items ──────────────
        $foodcourtTransactionIds = (clone $posFoodcourt)->pluck('id');
        $posFoodcourtProfit = (float) DB::table('transaction_items')
            ->whereIn('transaction_id', $foodcourtTransactionIds)
            ->sum('commission_amount');

        $posTotalProfit = $posStoreProfit + $posFoodcourtProfit;

        // ── FinancialTransaction (RFID / SIMPels) ────────────────────────
        $query = FinancialTransaction::query()->whereBetween('created_at', [$from, $to]);

        $totalRfidPayments = (clone $query)->rfidPayments()->completed()->sum('amount');

        $withdrawnTransactions = DB::table('financial_transactions')
            ->join('simpels_withdrawals', 'financial_transactions.withdrawal_id', '=', 'simpels_withdrawals.id')
            ->where('financial_transactions.type', FinancialTransaction::TYPE_RFID_PAYMENT)
            ->where('financial_transactions.status', FinancialTransaction::STATUS_COMPLETED)
            ->whereIn('simpels_withdrawals.simpels_status', ['approved', 'completed'])
            ->whereBetween('financial_transactions.created_at', [$from, $to])
            ->whereNull('financial_transactions.deleted_at')
            ->sum('financial_transactions.amount');

        $pendingOrRejectedTransactions = DB::table('financial_transactions')
            ->join('simpels_withdrawals', 'financial_transactions.withdrawal_id', '=', 'simpels_withdrawals.id')
            ->where('financial_transactions.type', FinancialTransaction::TYPE_RFID_PAYMENT)
            ->where('financial_transactions.status', FinancialTransaction::STATUS_COMPLETED)
            ->where(function($q) {
                $q->where('simpels_withdrawals.simpels_status', 'pending')
                  ->orWhere('simpels_withdrawals.simpels_status', 'rejected')
                  ->orWhereNull('simpels_withdrawals.simpels_status');
            })
            ->whereBetween('financial_transactions.created_at', [$from, $to])
            ->whereNull('financial_transactions.deleted_at')
            ->sum('financial_transactions.amount');

        $notInWithdrawal = FinancialTransaction::rfidPayments()
            ->completed()
            ->whereNull('withdrawal_id')
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');

        $availableForWithdrawal = $notInWithdrawal + $pendingOrRejectedTransactions;

        return [
            // POS sales breakdown
            'pos_total_sales'       => $posTotalSales,
            'pos_store_sales'       => $posStoreSales,
            'pos_foodcourt_sales'   => $posFoodcourtSales,
            'pos_total_count'       => $posTotalCount,
            'pos_store_count'       => $posStoreCount,
            'pos_foodcourt_count'   => $posFoodcourtCount,
            // Profit bersih
            'pos_store_profit'      => $posStoreProfit,
            'pos_foodcourt_profit'  => $posFoodcourtProfit,
            'pos_total_profit'      => $posTotalProfit,
            // RFID / SIMPels
            'total_income'             => (clone $query)->income()->completed()->sum('amount'),
            'total_expense'            => (clone $query)->expense()->completed()->sum('amount'),
            'total_rfid_payments'      => $totalRfidPayments,
            'total_refunds'            => (clone $query)->refunds()->completed()->sum('amount'),
            'total_transactions'       => (clone $query)->completed()->count(),
            'pending_sync'             => FinancialTransaction::notSynced()->where('type', FinancialTransaction::TYPE_RFID_PAYMENT)->count(),
            'pending_withdrawal'       => $availableForWithdrawal,
            'pending_withdrawal_formatted' => 'Rp ' . number_format($availableForWithdrawal, 0, ',', '.'),
            'withdrawn_amount'         => $withdrawnTransactions,
        ];
    }

    public function getPosTransactionsProperty()
    {
        $query = Transaction::with('user')
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ])
            ->where('status', 'completed');

        if ($this->outletModeFilter !== '') {
            $query->where('outlet_mode', $this->outletModeFilter);
        }

        if ($this->posSearch) {
            $query->where(function ($q) {
                $q->where('transaction_number', 'like', '%' . $this->posSearch . '%')
                  ->orWhere('customer_name', 'like', '%' . $this->posSearch . '%');
            });
        }

        return $query->latest()->paginate(20);
    }

    public function getTransactionsProperty()
    {
        $query = FinancialTransaction::with(['user', 'transaction', 'withdrawnBy'])
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ]);

        if ($this->filterType !== 'all') {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        if ($this->searchQuery) {
            $query->where(function($q) {
                $q->where('transaction_number', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('santri_name', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('rfid_tag', 'like', '%' . $this->searchQuery . '%')
                  ->orWhere('description', 'like', '%' . $this->searchQuery . '%');
            });
        }

        return $query->latest()->paginate(20);
    }

    public function getWithdrawalsProperty()
    {
        return SimpelsWithdrawal::with(['requestedBy', 'approvedBy'])
            ->latest()
            ->paginate(10);
    }

    public function refreshWithdrawalStatus($withdrawalId)
    {
        try {
            $withdrawal = SimpelsWithdrawal::findOrFail($withdrawalId);
            
            // Check status from SIMPELS via API
            $simpelsApi = app(\App\Services\SimpelsApiService::class);
            $response = $simpelsApi->getWithdrawalStatus($withdrawal->withdrawal_number);
            
            if ($response && isset($response['success']) && $response['success']) {
                $simpelsStatus = $response['data']['simpels_status'] ?? $response['data']['status'] ?? null;
                
                if ($simpelsStatus && $simpelsStatus !== $withdrawal->simpels_status) {
                    $withdrawal->update([
                        'simpels_status' => $simpelsStatus,
                        'simpels_updated_at' => now(),
                        'simpels_notes' => $response['data']['notes'] ?? null
                    ]);
                    
                    $this->dispatch('showNotification', [
                        'type' => 'success',
                        'title' => 'Status Diperbarui',
                        'message' => "Status withdrawal {$withdrawal->withdrawal_number} diperbarui: {$simpelsStatus}"
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to refresh withdrawal status', [
                'withdrawal_id' => $withdrawalId,
                'error' => $e->getMessage()
            ]);
            
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal memperbarui status: ' . $e->getMessage()
            ]);
        }
    }

    public function getChartData()
    {
        $startDate = Carbon::parse($this->dateFrom);
        $endDate = Carbon::parse($this->dateTo);
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
                'date' => $date->format('d M'),
                'income' => (float) $income,
                'expense' => (float) $expense,
                'net' => (float) ($income - $expense)
            ];
        }

        return $data;
    }

    public function openWithdrawalModal()
    {
        $this->showWithdrawalModal = true;
    }

    public function closeWithdrawalModal()
    {
        $this->showWithdrawalModal = false;
        $this->reset(['withdrawalAmount', 'withdrawalMethod', 'bankName', 'accountNumber', 'accountName', 'withdrawalNotes']);
    }

    public function createWithdrawal()
    {
        \Log::info('=== createWithdrawal method STARTED ===', [
            'withdrawalAmount' => $this->withdrawalAmount,
            'withdrawalMethod' => $this->withdrawalMethod,
            'bankName' => $this->bankName,
            'accountNumber' => $this->accountNumber,
            'accountName' => $this->accountName,
        ]);

        // Check if there are pending withdrawals (belum diproses di SIMPELS)
        $pendingWithdrawals = SimpelsWithdrawal::where(function($query) {
                $query->whereNull('simpels_status')
                      ->orWhere('simpels_status', 'pending');
            })
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($pendingWithdrawals > 0) {
            $this->dispatch('showNotification', [
                'type' => 'warning',
                'title' => 'Tidak Dapat Membuat Penarikan',
                'message' => 'Masih ada ' . $pendingWithdrawals . ' penarikan yang belum diproses di SIMPELS. Tunggu admin menyetujui penarikan sebelumnya.'
            ]);
            return;
        }

        // Get available balance
        try {
            $availableBalance = $this->getDashboardSummary()['pending_withdrawal'];
        } catch (\Exception $e) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Gagal mendapatkan saldo: ' . $e->getMessage()
            ]);
            return;
        }
        
        $this->validate([
            'withdrawalAmount' => 'required|numeric|min:1|max:' . $availableBalance,
            'withdrawalMethod' => 'required|in:bank_transfer,cash',
            'bankName' => 'required_if:withdrawalMethod,bank_transfer',
            'accountNumber' => 'required_if:withdrawalMethod,bank_transfer',
            'accountName' => 'required_if:withdrawalMethod,bank_transfer',
        ], [
            'withdrawalAmount.numeric' => 'Jumlah harus berupa angka',
            'withdrawalAmount.min' => 'Jumlah minimal Rp 1',
            'withdrawalAmount.max' => 'Jumlah melebihi saldo tersedia (Rp ' . number_format($availableBalance, 0, ',', '.') . ')',
            'withdrawalMethod.required' => 'Metode penarikan harus dipilih',
            'bankName.required_if' => 'Nama bank harus diisi',
            'accountNumber.required_if' => 'Nomor rekening harus diisi',
            'accountName.required_if' => 'Nama pemegang rekening harus diisi',
        ]);

        try {
            \Log::info('Creating withdrawal request', [
                'method' => $this->withdrawalMethod,
                'bank_name' => $this->bankName,
            ]);

            // Use FinancialService to create withdrawal request
            $financialService = app(\App\Services\FinancialService::class);
            
            // Get all available transactions (tidak pakai periode, ambil semua yang belum ditarik)
            $withdrawal = $financialService->createWithdrawalRequest([
                'period_start' => null, // Will use all available transactions
                'period_end' => null,
                'withdrawal_amount' => $this->withdrawalAmount, // Nominal yang diinput user
                'withdrawal_method' => $this->withdrawalMethod,
                'bank_name' => $this->bankName,
                'account_number' => $this->accountNumber,
                'account_name' => $this->accountName,
                'notes' => $this->withdrawalNotes,
            ]);

            \Log::info('Withdrawal request created successfully', [
                'withdrawal_number' => $withdrawal->withdrawal_number,
                'total_amount' => $withdrawal->total_amount
            ]);

            $this->closeWithdrawalModal();
            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => "Permintaan penarikan {$withdrawal->withdrawal_number} berhasil dibuat (Rp " . number_format($withdrawal->total_amount, 0, ',', '.') . ") dan dikirim ke SIMPels"
            ]);
            
            $this->setTab('withdrawals');

        } catch (\Exception $e) {
            \Log::error('Failed to create withdrawal request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal membuat permintaan penarikan: ' . $e->getMessage()
            ]);
        }
    }

    public function approveWithdrawal($id)
    {
        try {
            $withdrawal = SimpelsWithdrawal::findOrFail($id);
            
            if ($withdrawal->status !== SimpelsWithdrawal::STATUS_PENDING) {
                throw new \Exception('Penarikan ini sudah diproses');
            }

            $withdrawal->approve(auth()->id());

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Penarikan berhasil disetujui'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function completeWithdrawal($id)
    {
        try {
            DB::beginTransaction();

            $withdrawal = SimpelsWithdrawal::with('transactions')->findOrFail($id);
            
            if ($withdrawal->status !== SimpelsWithdrawal::STATUS_PROCESSING) {
                throw new \Exception('Penarikan harus disetujui terlebih dahulu');
            }

            // Mark all related transactions as withdrawn
            foreach ($withdrawal->transactions as $transaction) {
                $transaction->markAsWithdrawn($withdrawal->withdrawal_number, auth()->id());
            }

            // Update withdrawal status
            $withdrawal->update([
                'withdrawn_amount' => $withdrawal->total_amount,
                'remaining_amount' => 0,
            ]);
            
            $withdrawal->complete();

            DB::commit();

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Penarikan berhasil diselesaikan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function cancelWithdrawal($id)
    {
        try {
            $withdrawal = SimpelsWithdrawal::findOrFail($id);
            
            if ($withdrawal->status === SimpelsWithdrawal::STATUS_COMPLETED) {
                throw new \Exception('Penarikan yang sudah selesai tidak bisa dibatalkan');
            }

            $withdrawal->cancel('Dibatalkan oleh ' . auth()->user()->name);

            // Send callback to SIMPELS to reject the withdrawal there too
            try {
                $simpelsApiUrl = config('services.simpels.api_url'); // Already includes /api/v1/wallets
                $fullUrl = $simpelsApiUrl . '/epos/withdrawal/' . $withdrawal->withdrawal_number . '/reject';
                
                \Log::info('Attempting to send cancellation to SIMPELS', [
                    'base_url' => $simpelsApiUrl,
                    'withdrawal_number' => $withdrawal->withdrawal_number,
                    'full_url' => $fullUrl
                ]);
                
                if ($simpelsApiUrl && $withdrawal->withdrawal_number) {
                    $response = \Illuminate\Support\Facades\Http::timeout(10)
                        ->withHeaders([
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                        ])
                        ->post($fullUrl, [
                            'reason' => 'Dibatalkan dari ePOS oleh ' . auth()->user()->name,
                        ]);

                    \Log::info('SIMPELS response received', [
                        'status' => $response->status(),
                        'successful' => $response->successful(),
                        'body' => $response->body()
                    ]);

                    if ($response->successful()) {
                        \Log::info('Withdrawal cancellation sent to SIMPELS successfully', [
                            'withdrawal_number' => $withdrawal->withdrawal_number,
                            'response' => $response->json()
                        ]);
                    } else {
                        \Log::warning('Failed to send cancellation to SIMPELS', [
                            'withdrawal_number' => $withdrawal->withdrawal_number,
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                    }
                }
            } catch (\Exception $callbackError) {
                \Log::error('Error sending cancellation callback to SIMPELS', [
                    'withdrawal_number' => $withdrawal->withdrawal_number,
                    'error' => $callbackError->getMessage(),
                    'trace' => $callbackError->getTraceAsString()
                ]);
                // Continue - local cancellation succeeded
            }

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Penarikan berhasil dibatalkan'
            ]);

        } catch (\Exception $e) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function openExpenseModal()
    {
        $this->reset(['expenseAmount', 'expenseDescription', 'expenseCategory', 'expenseNotes']);
        $this->showExpenseModal = true;
    }

    public function closeExpenseModal()
    {
        $this->showExpenseModal = false;
        $this->reset(['expenseAmount', 'expenseDescription', 'expenseCategory', 'expenseNotes']);
    }

    public function saveExpense()
    {
        $this->validate([
            'expenseAmount' => 'required|numeric|min:1',
            'expenseDescription' => 'required|string|max:255',
            'expenseCategory' => 'required|string',
        ]);

        try {
            $service = app(\App\Services\FinancialService::class);
            $service->recordExpense(
                (float) $this->expenseAmount,
                $this->expenseDescription,
                $this->expenseCategory,
                $this->expenseNotes
            );

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Pengeluaran berhasil dicatat'
            ]);

            $this->closeExpenseModal();
            $this->setTab('expenses'); // Switch to expenses tab if not already

        } catch (\Exception $e) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal mencatat pengeluaran: ' . $e->getMessage()
            ]);
        }
    }

    public function exportTransactions()
    {
        $transactions = FinancialTransaction::with(['user', 'transaction'])
            ->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ])
            ->get();

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Transaction Number', 'Type', 'Category', 'Amount', 'Status', 'Description']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->transaction_number,
                    $transaction->type,
                    $transaction->category,
                    $transaction->amount,
                    $transaction->status,
                    $transaction->description
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=financial_transactions_" . date('Y-m-d') . ".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ]);
    }

    public function render()
    {
        return view('livewire.financial', [
            'summary'          => $this->getDashboardSummary(),
            'transactions'     => $this->transactions,
            'posTransactions'  => $this->posTransactions,
            'withdrawals'      => $this->withdrawals,
            'chartData'        => $this->getChartData(),
            'tenantSettlement' => $this->getTenantSettlement(),
        ])->layout('layouts.epos', [
            'header' => 'Manajemen Keuangan'
        ]);
    }

    /**
     * Rekap settlement per tenant untuk periode yang dipilih.
     * Mengembalikan collection: tenant_id, tenant_name, total_sales, total_commission, tenant_payout
     */
    public function getTenantSettlement(): \Illuminate\Support\Collection
    {
        return DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('tenants', 'transaction_items.tenant_id', '=', 'tenants.id')
            ->where('transactions.status', 'completed')
            ->whereNotNull('transaction_items.tenant_id')
            ->whereBetween('transactions.created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay(),
            ])
            ->groupBy('transaction_items.tenant_id', 'tenants.name', 'tenants.booth_number')
            ->select(
                'transaction_items.tenant_id',
                'tenants.name as tenant_name',
                'tenants.booth_number',
                DB::raw('SUM(transaction_items.total_price) as total_sales'),
                DB::raw('SUM(transaction_items.commission_amount) as total_commission'),
                DB::raw('SUM(transaction_items.tenant_amount) as tenant_payout'),
                DB::raw('COUNT(DISTINCT transaction_items.transaction_id) as transaction_count')
            )
            ->orderBy('tenants.booth_number')
            ->get()
            ->map(function ($row) {
                $row->total_sales_formatted    = 'Rp ' . number_format($row->total_sales, 0, ',', '.');
                $row->total_commission_formatted = 'Rp ' . number_format($row->total_commission, 0, ',', '.');
                $row->tenant_payout_formatted  = 'Rp ' . number_format($row->tenant_payout, 0, ',', '.');
                return $row;
            });
    }
}
