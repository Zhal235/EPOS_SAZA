<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CashierReport extends Component
{
    public string $dateFrom  = '';
    public string $dateTo    = '';
    public string $outletMode = '';  // '' = all, 'store', 'foodcourt'
    public string $selectedCashier = '';
    public string $reportType = 'daily'; // daily, weekly, monthly, custom

    public array $cashiers   = [];
    public array $reportRows = [];

    // Summary totals
    public float $grandTotal        = 0;
    public int   $grandTransactions = 0;

    public function mount(): void
    {
        $this->dateFrom = Carbon::today()->format('Y-m-d');
        $this->dateTo   = Carbon::today()->format('Y-m-d');

        $this->cashiers = User::whereIn('role', User::getStaffRoles())
            ->orderBy('name')
            ->get(['id', 'name', 'role'])
            ->toArray();

        $this->generate();
    }

    public function updatedReportType(): void
    {
        $this->setDateRange();
        $this->generate();
    }

    public function setDateRange(): void
    {
        $now = Carbon::now();
        switch ($this->reportType) {
            case 'daily':
                $this->dateFrom = $now->format('Y-m-d');
                $this->dateTo   = $now->format('Y-m-d');
                break;
            case 'weekly':
                $this->dateFrom = $now->copy()->startOfWeek()->format('Y-m-d');
                $this->dateTo   = $now->copy()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $this->dateFrom = $now->copy()->startOfMonth()->format('Y-m-d');
                $this->dateTo   = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function generate(): void
    {
        $from = Carbon::parse($this->dateFrom)->startOfDay();
        $to   = Carbon::parse($this->dateTo)->endOfDay();

        $query = Transaction::whereBetween('created_at', [$from, $to])
            ->where('status', 'completed');

        if ($this->outletMode !== '') {
            $query->where('outlet_mode', $this->outletMode);
        }

        if ($this->selectedCashier !== '') {
            $query->where('user_id', $this->selectedCashier);
        }

        // Aggregate per cashier
        $rows = $query->select(
                'user_id',
                'outlet_mode',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('SUM(discount_amount) as total_discount'),
                DB::raw('MAX(created_at) as last_transaction_at')
            )
            ->groupBy('user_id', 'outlet_mode')
            ->orderByDesc('total_sales')
            ->with('user:id,name,role')
            ->get();

        $this->reportRows      = $rows->toArray();
        $this->grandTotal       = (float) $rows->sum('total_sales');
        $this->grandTransactions = (int) $rows->sum('transaction_count');
    }

    public function render()
    {
        return view('livewire.cashier-report')
            ->layout('layouts.epos', ['header' => 'Laporan Per Kasir']);
    }
}
