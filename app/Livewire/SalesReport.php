<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalesReport extends Component
{
    public $dateFrom;
    public $dateTo;
    public $selectedCashier = '';
    public $selectedPaymentMethod = '';
    public $reportType = 'daily'; // daily, weekly, monthly, custom
    
    public $totalSales = 0;
    public $totalTransactions = 0;
    public $totalProfit = 0;
    public $averageTransaction = 0;
    
    public $topProducts = [];
    public $salesByPaymentMethod = [];
    public $salesByHour = [];
    public $cashiers = [];

    public function mount()
    {
        // Set default dates
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->dateTo = Carbon::today()->format('Y-m-d');
        
        // Load cashiers for filter
        $this->cashiers = User::whereIn('role', ['admin', 'cashier'])
            ->orderBy('name')
            ->get();
        
        // Generate initial report automatically
        $this->generateReport();
    }

    public function updatedReportType()
    {
        $this->setDateRange();
        $this->generateReport();
    }

    public function setDateRange()
    {
        $now = Carbon::now();
        
        switch ($this->reportType) {
            case 'daily':
                $this->dateFrom = $now->format('Y-m-d');
                $this->dateTo = $now->format('Y-m-d');
                break;
            case 'weekly':
                $this->dateFrom = $now->startOfWeek()->format('Y-m-d');
                $this->dateTo = $now->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $this->dateFrom = $now->startOfMonth()->format('Y-m-d');
                $this->dateTo = $now->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function generateReport()
    {
        $query = Transaction::whereBetween('created_at', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay()
        ]);

        // Apply filters
        if ($this->selectedCashier) {
            $query->where('user_id', $this->selectedCashier);
        }

        if ($this->selectedPaymentMethod) {
            $query->where('payment_method', $this->selectedPaymentMethod);
        }

        $transactions = $query->get();

        // Calculate totals
        $this->totalSales = $transactions->sum('total_amount');
        $this->totalTransactions = $transactions->count();
        $this->totalProfit = $transactions->sum(function ($transaction) {
            return $transaction->items->sum(function ($item) {
                // Calculate profit: (unit_price - cost_price) * quantity
                $costPrice = $item->product->cost_price ?? 0;
                return ($item->unit_price - $costPrice) * $item->quantity;
            });
        });
        $this->averageTransaction = $this->totalTransactions > 0 
            ? $this->totalSales / $this->totalTransactions 
            : 0;

        // Top products
        $this->topProducts = TransactionItem::whereHas('transaction', function ($q) {
            $q->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ]);
            
            if ($this->selectedCashier) {
                $q->where('user_id', $this->selectedCashier);
            }
        })
        ->select('product_id', DB::raw('SUM(quantity) as total_quantity'), DB::raw('SUM(total_price) as total_sales'))
        ->with('product')
        ->groupBy('product_id')
        ->orderByDesc('total_sales')
        ->limit(10)
        ->get();

        // Sales by payment method
        $this->salesByPaymentMethod = Transaction::whereBetween('created_at', [
            Carbon::parse($this->dateFrom)->startOfDay(),
            Carbon::parse($this->dateTo)->endOfDay()
        ])
        ->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
        ->groupBy('payment_method')
        ->get();

        // Sales by hour (for daily reports)
        if ($this->reportType === 'daily') {
            // Check database driver to use correct SQL function
            $driver = config('database.default');
            $connection = config("database.connections.{$driver}.driver");
            
            if ($connection === 'sqlite') {
                // SQLite uses strftime
                $this->salesByHour = Transaction::whereBetween('created_at', [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay()
                ])
                ->select(DB::raw("CAST(strftime('%H', created_at) as INTEGER) as hour"), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
            } else {
                // MySQL/PostgreSQL use HOUR()
                $this->salesByHour = Transaction::whereBetween('created_at', [
                    Carbon::parse($this->dateFrom)->startOfDay(),
                    Carbon::parse($this->dateTo)->endOfDay()
                ])
                ->select(DB::raw('HOUR(created_at) as hour'), DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total'))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
            }
        }

        session()->flash('message', 'Report generated successfully!');
    }

    public function exportReport()
    {
        // TODO: Implement export functionality (PDF/Excel)
        session()->flash('message', 'Export functionality coming soon!');
    }

    public function render()
    {
        // Check if user has permission
        $user = auth()->user();
        if ($user->isCashier() && !$user->canAccessAdmin()) {
            // Cashiers can only see their own sales
            $this->selectedCashier = $user->id;
        }

        return view('livewire.sales-report')
            ->layout('layouts.epos', [
                'header' => auth()->user()->isCashier() ? 'My Sales Report' : 'Sales Report'
            ]);
    }
}
