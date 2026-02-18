<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use App\Models\FinancialTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $timeFrame = 'daily'; // daily, weekly, monthly, custom
    public $customStartDate;
    public $customEndDate;

    public function mount()
    {
        $this->customStartDate = now()->subDays(7)->format('Y-m-d');
        $this->customEndDate = now()->format('Y-m-d');
    }

    public function setTimeFrame($frame)
    {
        $this->timeFrame = $frame;
    }

    public function getStatsProperty()
    {
        $query = Transaction::query()->where('status', 'completed');
        $date = now();
        $label = 'Hari Ini';
        $prevRevenue = 0;
        
        // Define timeframe dates
        $startDate = null;
        $endDate = null;
        $prevStartDate = null;
        $prevEndDate = null;

        switch ($this->timeFrame) {
            case 'daily':
                $startDate = $date->copy()->startOfDay();
                $endDate = $date->copy()->endOfDay();
                $prevStartDate = $date->copy()->subDay()->startOfDay();
                $prevEndDate = $date->copy()->subDay()->endOfDay();
                $label = 'Hari Ini (' . $date->locale('id')->isoFormat('D MMM') . ')';
                break;
            case 'weekly':
                $startDate = $date->copy()->startOfWeek();
                $endDate = $date->copy()->endOfWeek();
                $prevStartDate = $startDate->copy()->subWeek();
                $prevEndDate = $endDate->copy()->subWeek();
                $label = 'Minggu Ini';
                break;
            case 'monthly':
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();
                $prevStartDate = $startDate->copy()->subMonth();
                $prevEndDate = $endDate->copy()->subMonth();
                $label = 'Bulan Ini (' . $date->locale('id')->isoFormat('MMMM Y') . ')';
                break;
            case 'custom':
                if ($this->customStartDate && $this->customEndDate) {
                    $startDate = Carbon::parse($this->customStartDate)->startOfDay();
                    $endDate = Carbon::parse($this->customEndDate)->endOfDay();
                    $daysDiff = $startDate->diffInDays($endDate);
                    $prevStartDate = $startDate->copy()->subDays($daysDiff + 1);
                    $prevEndDate = $startDate->copy()->subDay();
                    $label = $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
                } else {
                    // Fallback to daily
                    $startDate = $date->copy()->startOfDay();
                    $endDate = $date->copy()->endOfDay();
                    $prevStartDate = $date->copy()->subDay()->startOfDay();
                    $prevEndDate = $date->copy()->subDay()->endOfDay();
                    $label = 'Hari Ini (Custom Error)';
                }
                break;
        }

        $query->whereBetween('created_at', [$startDate, $endDate]);
        $prevQuery = Transaction::query()->where('status', 'completed')->whereBetween('created_at', [$prevStartDate, $prevEndDate]);

        $revenue = $query->sum('total_amount');
        try {
            $prevRevenue = $prevQuery->sum('total_amount');
        } catch (\Exception $e) {
            $prevRevenue = 0; 
        }
        
        $growth = 0;
        if ($prevRevenue > 0) {
            $growth = (($revenue - $prevRevenue) / $prevRevenue) * 100;
        } elseif ($revenue > 0) {
            $growth = 100; // 100% growth if previous was 0
        }

        return [
            'revenue' => $revenue,
            'growth' => round($growth, 1),
            'label' => $label,
            'total_transactions' => $query->count()
        ];
    }

    public function getTotalProductsProperty()
    {
        return Product::count();
    }
    
    public function getLowStockCountProperty()
    {
        return Product::lowStock()->count();
    }

    public function getActiveCustomersProperty()
    {
        $count = User::whereIn('customer_type', ['regular', 'umum', 'santri', 'guru'])
            ->where('is_active', true)
            ->count();
            
        // Calculate new customers this week
        $newThisWeek = User::whereIn('customer_type', ['regular', 'umum', 'santri', 'guru'])
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();
            
        return [
            'total' => $count,
            'new_this_week' => $newThisWeek
        ];
    }

    public function getRecentTransactionsProperty()
    {
        return Transaction::latest()->take(5)->get();
    }

    public function getLowStockProductsProperty()
    {
        return Product::lowStock()->take(5)->get();
    }

    public function getFinancialReportProperty()
    {
        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        // Determine date range
        if ($this->timeFrame === 'daily') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($this->timeFrame === 'weekly') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif ($this->timeFrame === 'monthly') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($this->timeFrame === 'custom' && $this->customStartDate && $this->customEndDate) {
            $startDate = Carbon::parse($this->customStartDate)->startOfDay();
            $endDate = Carbon::parse($this->customEndDate)->endOfDay();
        }

        $query = FinancialTransaction::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed');
            
        // Get raw data for summarization
        $groupBy = 'date';
        $dateFormat = 'Y-m-d';
        $dbDateFormat = $isSqlite ? "strftime('%Y-%m-%d', created_at)" : 'DATE(created_at)';
        
        if ($this->timeFrame === 'daily') {
            $groupBy = 'hour';
            $dateFormat = 'H:00';
            $dbDateFormat = $isSqlite ? "strftime('%H', created_at)" : 'HOUR(created_at)';
        }

        $records = $query->select(
                DB::raw("$dbDateFormat as label"),
                DB::raw("SUM(CASE WHEN category = 'income' OR type = 'rfid_payment' OR type = 'cash_in' THEN amount ELSE 0 END) as income"),
                DB::raw("SUM(CASE WHEN category = 'expense' OR type = 'refund' OR type = 'cash_out' THEN amount ELSE 0 END) as expense"),
                DB::raw("COUNT(*) as transaction_count")
            )
            ->groupBy('label')
            ->orderBy('label', 'asc')
            ->get();
            
        $data = [];
        $totalIncome = 0;
        $totalExpense = 0;
        
        foreach ($records as $record) {
            $label = $record->label;
            
            // Format label for display
            if ($this->timeFrame === 'daily') {
                $label = sprintf('%02d:00', (int)$record->label);
            } elseif ($this->timeFrame === 'monthly') {
                 try {
                    $dateObj = Carbon::createFromFormat('Y-m-d', $record->label);
                    $label = $dateObj ? $dateObj->locale('id')->isoFormat('D MMM Y') : $record->label;
                 } catch (\Exception $e) {
                    $label = $record->label;
                 }
            } elseif ($this->timeFrame === 'weekly' || $this->timeFrame === 'custom') {
                 try {
                     $dateObj = Carbon::createFromFormat('Y-m-d', $record->label);
                     $label = $dateObj ? $dateObj->locale('id')->isoFormat('dddd, D MMM Y') : $record->label;
                 } catch (\Exception $e) {
                     $label = $record->label;
                 }
            }
            
            $data[] = [
                'label' => $label,
                'date_raw' => $record->label,
                'income' => (float)$record->income,
                'expense' => (float)$record->expense,
                'profit' => (float)$record->income - (float)$record->expense,
                'count' => $record->transaction_count
            ];
            
            $totalIncome += (float)$record->income;
            $totalExpense += (float)$record->expense;
        }
        
        return [
            'details' => $data,
            'summary' => [
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'total_profit' => $totalIncome - $totalExpense
            ]
        ];
    }
    
    public function render()
    {
        return view('livewire.dashboard', [
            'stats' => $this->stats,
            'recentTransactions' => $this->recentTransactions,
            'lowStockProducts' => $this->lowStockProducts,
            'totalProducts' => $this->totalProducts,
            'lowStockCount' => $this->lowStockCount,
            'activeCustomers' => $this->activeCustomers,
            'financialReport' => $this->financialReport
        ])->layout('layouts.epos', [
            'header' => 'Dashboard EPOS'
        ]);
    }
}
