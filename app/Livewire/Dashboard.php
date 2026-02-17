<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $timeFrame = 'daily'; // daily, weekly, monthly

    public function setTimeFrame($frame)
    {
        $this->timeFrame = $frame;
    }

    public function getStatsProperty()
    {
        // Default to daily if invalid
        if (!in_array($this->timeFrame, ['daily', 'weekly', 'monthly'])) {
            $this->timeFrame = 'daily';
        }

        $query = Transaction::query()->where('status', 'completed');
        $date = now();
        $label = 'Hari Ini';
        $prevRevenue = 0;

        switch ($this->timeFrame) {
            case 'daily':
                $query->whereDate('created_at', $date);
                $prevQuery = Transaction::query()->where('status', 'completed')->whereDate('created_at', $date->copy()->subDay());
                $label = 'Hari Ini';
                break;
            case 'weekly':
                $startOfWeek = $date->copy()->startOfWeek();
                $endOfWeek = $date->copy()->endOfWeek();
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                
                $prevStart = $startOfWeek->copy()->subWeek();
                $prevEnd = $endOfWeek->copy()->subWeek();
                $prevQuery = Transaction::query()->where('status', 'completed')->whereBetween('created_at', [$prevStart, $prevEnd]);
                $label = 'Minggu Ini';
                break;
            case 'monthly':
                $query->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year);
                $prevQuery = Transaction::query()->where('status', 'completed')->whereMonth('created_at', $date->copy()->subMonth()->month)->whereYear('created_at', $date->copy()->subMonth()->year);
                $label = 'Bulan Ini';
                break;
            default:
                // Fallback same as daily
                $query->whereDate('created_at', $date);
                $prevQuery = Transaction::query()->where('status', 'completed')->whereDate('created_at', $date->copy()->subDay());
        }

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

    public function render()
    {
        return view('livewire.dashboard', [
            'stats' => $this->stats,
            'recentTransactions' => $this->recentTransactions,
            'lowStockProducts' => $this->lowStockProducts,
            'totalProducts' => $this->totalProducts,
            'lowStockCount' => $this->lowStockCount,
            'activeCustomers' => $this->activeCustomers
        ])->layout('layouts.epos', [
            'header' => 'Dashboard EPOS'
        ]);
    }
}
