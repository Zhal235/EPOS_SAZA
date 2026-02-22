<?php

namespace App\Livewire;

use App\Models\TransactionItem;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueReport extends Component
{
    use WithPagination;

    // Filter properties
    public $startDate;
    public $endDate;
    public $reportType = 'store'; // 'store' or 'foodcourt'

    public function mount()
    {
        // Default to current month
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        // Auto-detect type from URL if passed (optional)
        if (request()->has('type') && in_array(request('type'), ['store', 'foodcourt'])) {
            $this->reportType = request('type');
        }
    }

    public function updatedReportType()
    {
        $this->resetPage();
    }

    // Helper scope for filtering by date
    protected function dateFilter($query)
    {
        return $query->whereDate('created_at', '>=', $this->startDate)
                     ->whereDate('created_at', '<=', $this->endDate);
    }

    // --- STORE METRICS ---
    // (tenant_id IS NULL)

    public function getStoreRevenueProperty()
    {
        return TransactionItem::whereNull('tenant_id')
            ->whereHas('transaction', function($q) {
                $q->where('status', 'completed');
            })
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->sum('total_price');
    }

    // --- FOODCOURT METRICS --- 
    // (tenant_id IS NOT NULL)
    // Revenue = Only Commission (what we keep)
    // Gross Sales = Total transaction value (what customer paid)

    public function getFoodcourtRevenueProperty()
    {
        return TransactionItem::whereNotNull('tenant_id')
            ->whereHas('transaction', function($q) {
                $q->where('status', 'completed');
            })
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->sum('commission_amount');
    }
    
    public function getFoodcourtGrossSalesProperty()
    {
        return TransactionItem::whereNotNull('tenant_id')
            ->whereHas('transaction', function($q) {
                $q->where('status', 'completed');
            })
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->sum('total_price');
    }

    // --- DETAILED DATA for Table ---

    public function getDetailedDataProperty()
    {
        if ($this->reportType === 'store') {
            // Data Penjualan Toko Per Tanggal
            return TransactionItem::query()
                ->whereNull('tenant_id')
                ->whereHas('transaction', function($q) {
                    $q->where('status', 'completed');
                })
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->select([
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(quantity) as total_items'),
                    DB::raw('SUM(total_price) as total_revenue'),
                    DB::raw('COUNT(DISTINCT transaction_id) as transaction_count')
                ])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'desc')
                ->paginate(10);
        } else {
            // Data Pendapatan Foodcourt Per Tanggal
            return TransactionItem::query()
                ->whereNotNull('tenant_id')
                ->whereHas('transaction', function($q) {
                    $q->where('status', 'completed');
                })
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->select([
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as gross_sales'),
                    DB::raw('SUM(commission_amount) as net_revenue'), 
                    DB::raw('SUM(tenant_amount) as tenant_share'),
                    DB::raw('COUNT(DISTINCT transaction_id) as transaction_count')
                ])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('date', 'desc')
                ->paginate(10);
        }
    }

    public function render()
    {
        return view('livewire.revenue-report', [
            'storeRevenue' => $this->storeRevenue,
            'foodcourtRevenue' => $this->foodcourtRevenue,
            'foodcourtGrossSales' => $this->foodcourtGrossSales,
            // 'reportData' comes from property access in blade via detailedData
        ])->layout('layouts.epos', [
            'header' => $this->reportType === 'store' ? 'Laporan Pendapatan Toko' : 'Laporan Pendapatan Foodcourt'
        ]);
    }
}
