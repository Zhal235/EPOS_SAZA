<?php

namespace App\Livewire;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class TransactionHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $paymentMethod = '';
    public $status = '';
    public $selectedTransaction = null;
    public $showDetailModal = false;

    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'paymentMethod' => ['except' => ''],
        'status' => ['except' => '']
    ];

    public function mount()
    {
        // Set default date range to today
        $this->dateFrom = now()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingPaymentMethod()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function viewTransaction($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['items.product', 'user'])
                                               ->findOrFail($transactionId);
        $this->showDetailModal = true;
    }

    public function closeDetailModal()
    {
        $this->showDetailModal = false;
        $this->selectedTransaction = null;
    }

    public function getTodayTransactionsProperty()
    {
        return Transaction::whereDate('created_at', today())->completed()->count();
    }

    public function getTodayRevenueProperty()
    {
        return Transaction::whereDate('created_at', today())->completed()->sum('total_amount');
    }

    public function getThisMonthRevenueProperty()
    {
        return Transaction::thisMonth()->completed()->sum('total_amount');
    }

    public function getTransactions()
    {
        return Transaction::with(['user'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('transaction_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_phone', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->when($this->paymentMethod, function ($query) {
                $query->where('payment_method', $this->paymentMethod);
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.transaction-history', [
            'transactions' => $this->getTransactions()
        ])->layout('layouts.epos', [
            'header' => 'Transaction History'
        ]);
    }
}
