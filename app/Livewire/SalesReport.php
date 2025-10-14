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
    
    // Analytics data
    public $salesTrend = [];
    public $growthData = [];
    public $productPerformance = [];
    public $categoryPerformance = [];
    public $peakHours = [];
    public $comparisonPeriod = 'previous'; // previous, last_month, last_year

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

        // Generate analytics data
        $this->generateAnalytics();
        
        session()->flash('message', 'Report generated successfully!');
    }
    
    public function generateAnalytics()
    {
        // Sales Trend - Last 7 days or custom range
        $startDate = Carbon::parse($this->dateFrom);
        $endDate = Carbon::parse($this->dateTo);
        $daysDiff = $startDate->diffInDays($endDate) + 1;
        
        if ($daysDiff <= 31) {
            // Daily trend
            $this->salesTrend = [];
            for ($i = 0; $i < $daysDiff; $i++) {
                $date = $startDate->copy()->addDays($i);
                $dailySales = Transaction::whereDate('created_at', $date)->sum('total_amount');
                $dailyCount = Transaction::whereDate('created_at', $date)->count();
                
                $this->salesTrend[] = [
                    'date' => $date->format('Y-m-d'),
                    'label' => $date->format('d M'),
                    'sales' => $dailySales,
                    'count' => $dailyCount,
                ];
            }
        }
        
        // Growth comparison
        $this->calculateGrowthData();
        
        // Product Performance (including slow-moving items)
        $this->calculateProductPerformance();
        
        // Category Performance
        $this->calculateCategoryPerformance();
        
        // Peak Hours Analysis
        $this->calculatePeakHours();
    }
    
    public function calculateGrowthData()
    {
        $currentStart = Carbon::parse($this->dateFrom)->startOfDay();
        $currentEnd = Carbon::parse($this->dateTo)->endOfDay();
        $daysDiff = $currentStart->diffInDays($currentEnd) + 1;
        
        // Calculate for comparison period
        if ($this->comparisonPeriod === 'previous') {
            $compareStart = $currentStart->copy()->subDays($daysDiff);
            $compareEnd = $currentEnd->copy()->subDays($daysDiff);
        } elseif ($this->comparisonPeriod === 'last_month') {
            $compareStart = $currentStart->copy()->subMonth();
            $compareEnd = $currentEnd->copy()->subMonth();
        } else { // last_year
            $compareStart = $currentStart->copy()->subYear();
            $compareEnd = $currentEnd->copy()->subYear();
        }
        
        // Current period stats
        $currentSales = Transaction::whereBetween('created_at', [$currentStart, $currentEnd])->sum('total_amount');
        $currentTransactions = Transaction::whereBetween('created_at', [$currentStart, $currentEnd])->count();
        
        // Comparison period stats
        $compareSales = Transaction::whereBetween('created_at', [$compareStart, $compareEnd])->sum('total_amount');
        $compareTransactions = Transaction::whereBetween('created_at', [$compareStart, $compareEnd])->count();
        
        // Calculate growth percentages
        $salesGrowth = $compareSales > 0 ? (($currentSales - $compareSales) / $compareSales) * 100 : 0;
        $transactionsGrowth = $compareTransactions > 0 ? (($currentTransactions - $compareTransactions) / $compareTransactions) * 100 : 0;
        
        $this->growthData = [
            'current_sales' => $currentSales,
            'compare_sales' => $compareSales,
            'sales_growth' => $salesGrowth,
            'current_transactions' => $currentTransactions,
            'compare_transactions' => $compareTransactions,
            'transactions_growth' => $transactionsGrowth,
            'compare_period_label' => $this->getComparisonPeriodLabel(),
        ];
    }
    
    public function calculateProductPerformance()
    {
        $this->productPerformance = TransactionItem::whereHas('transaction', function ($q) {
            $q->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ]);
        })
        ->select('product_id', 
            DB::raw('SUM(quantity) as total_quantity'), 
            DB::raw('SUM(total_price) as total_sales'),
            DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'))
        ->with('product.category')
        ->groupBy('product_id')
        ->orderByDesc('total_sales')
        ->get()
        ->map(function ($item) {
            $product = $item->product;
            $costPrice = $product->cost_price ?? 0;
            $totalCost = $costPrice * $item->total_quantity;
            $profit = $item->total_sales - $totalCost;
            $profitMargin = $item->total_sales > 0 ? ($profit / $item->total_sales) * 100 : 0;
            
            return [
                'product_name' => $product->name ?? 'Unknown',
                'category' => $product->category->name ?? 'Uncategorized',
                'quantity' => $item->total_quantity,
                'sales' => $item->total_sales,
                'profit' => $profit,
                'profit_margin' => $profitMargin,
                'transaction_count' => $item->transaction_count,
                'current_stock' => $product->stock ?? 0,
            ];
        });
    }
    
    public function calculateCategoryPerformance()
    {
        $this->categoryPerformance = TransactionItem::whereHas('transaction', function ($q) {
            $q->whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ]);
        })
        ->with('product.category')
        ->get()
        ->groupBy(function ($item) {
            return $item->product->category->name ?? 'Uncategorized';
        })
        ->map(function ($items, $categoryName) {
            $totalSales = $items->sum('total_price');
            $totalQuantity = $items->sum('quantity');
            $transactionCount = $items->pluck('transaction_id')->unique()->count();
            
            return [
                'category' => $categoryName,
                'sales' => $totalSales,
                'quantity' => $totalQuantity,
                'transaction_count' => $transactionCount,
                'avg_transaction_value' => $transactionCount > 0 ? $totalSales / $transactionCount : 0,
            ];
        })
        ->sortByDesc('sales')
        ->values();
    }
    
    public function calculatePeakHours()
    {
        $driver = config('database.default');
        $connection = config("database.connections.{$driver}.driver");
        
        if ($connection === 'sqlite') {
            $hourlyData = Transaction::whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ])
            ->select(DB::raw("CAST(strftime('%H', created_at) as INTEGER) as hour"), 
                    DB::raw('COUNT(*) as count'), 
                    DB::raw('SUM(total_amount) as total'))
            ->groupBy('hour')
            ->orderByDesc('total')
            ->get();
        } else {
            $hourlyData = Transaction::whereBetween('created_at', [
                Carbon::parse($this->dateFrom)->startOfDay(),
                Carbon::parse($this->dateTo)->endOfDay()
            ])
            ->select(DB::raw('HOUR(created_at) as hour'), 
                    DB::raw('COUNT(*) as count'), 
                    DB::raw('SUM(total_amount) as total'))
            ->groupBy('hour')
            ->orderByDesc('total')
            ->get();
        }
        
        $this->peakHours = $hourlyData->take(5)->map(function ($item) {
            return [
                'hour' => str_pad($item->hour, 2, '0', STR_PAD_LEFT) . ':00',
                'count' => $item->count,
                'sales' => $item->total,
            ];
        });
    }
    
    public function getComparisonPeriodLabel()
    {
        switch ($this->comparisonPeriod) {
            case 'previous':
                return 'Previous Period';
            case 'last_month':
                return 'Last Month';
            case 'last_year':
                return 'Last Year';
            default:
                return 'Previous Period';
        }
    }
    
    public function updatedComparisonPeriod()
    {
        $this->calculateGrowthData();
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
                'header' => auth()->user()->isCashier() ? 'My Sales & Analytics' : 'Sales & Analytics Dashboard'
            ]);
    }
}
