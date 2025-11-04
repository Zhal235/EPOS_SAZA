<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Services\FinancialService;
use App\Services\SimpelsApiService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundManager extends Component
{
    use WithPagination;

    public $search = '';
    public $dateFrom;
    public $dateTo;
    
    // Refund modal
    public $showRefundModal = false;
    public $selectedTransaction = null;
    public $refundItems = [];
    public $refundReason = '';
    public $refundToRfid = false;
    public $partialRefund = false;

    protected $queryString = ['search', 'dateFrom', 'dateTo'];

    public function mount()
    {
        $this->dateFrom = now()->subDays(30)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openRefundModal($transactionId)
    {
        $this->selectedTransaction = Transaction::with(['items.product', 'user'])
            ->findOrFail($transactionId);
        
        // Initialize refund items with full quantities
        $this->refundItems = [];
        foreach ($this->selectedTransaction->items as $item) {
            $this->refundItems[$item->id] = [
                'selected' => true,
                'quantity' => $item->quantity,
                'max_quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'product_name' => $item->product_name,
            ];
        }
        
        $this->refundToRfid = ($this->selectedTransaction->payment_method === 'rfid');
        $this->showRefundModal = true;
    }

    public function closeRefundModal()
    {
        $this->reset(['showRefundModal', 'selectedTransaction', 'refundItems', 'refundReason', 'partialRefund']);
    }

    public function toggleRefundItem($itemId)
    {
        if (isset($this->refundItems[$itemId])) {
            $this->refundItems[$itemId]['selected'] = !$this->refundItems[$itemId]['selected'];
        }
    }

    public function getRefundTotalProperty()
    {
        $total = 0;
        foreach ($this->refundItems as $item) {
            if ($item['selected']) {
                $total += $item['quantity'] * $item['unit_price'];
            }
        }
        return $total;
    }

    public function processRefund()
    {
        $this->validate([
            'refundReason' => 'required|string|min:10',
        ], [
            'refundReason.required' => 'Alasan pengembalian harus diisi',
            'refundReason.min' => 'Alasan pengembalian minimal 10 karakter',
        ]);

        // Validate at least one item is selected
        $hasSelectedItem = false;
        foreach ($this->refundItems as $item) {
            if ($item['selected']) {
                $hasSelectedItem = true;
                break;
            }
        }

        if (!$hasSelectedItem) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Pilih minimal satu item untuk dikembalikan'
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            $refundAmount = $this->refund_total;
            $isFullRefund = ($refundAmount >= $this->selectedTransaction->total_amount);

            // Process refund to RFID if applicable
            if ($this->refundToRfid && $this->selectedTransaction->payment_method === 'rfid') {
                try {
                    $simpelsApi = app(SimpelsApiService::class);
                    
                    // Extract santri info from transaction notes
                    $notes = $this->selectedTransaction->notes;
                    preg_match('/Santri: (.+?) - RFID: (.+?)$/', $notes, $matches);
                    
                    if (count($matches) >= 3) {
                        $santriName = $matches[1];
                        $rfidTag = $matches[2];
                        
                        // Get santri data
                        $santriData = $simpelsApi->getSantriByRfid($rfidTag);
                        
                        if ($santriData && isset($santriData['data']['id'])) {
                            $santriId = $santriData['data']['id'];
                            
                            // Process refund via SIMPels API
                            $refundResponse = $simpelsApi->processRefund([
                                'santri_id' => $santriId,
                                'amount' => $refundAmount,
                                'original_transaction_ref' => $this->selectedTransaction->transaction_number,
                                'refund_reason' => $this->refundReason
                            ]);

                            if (!$refundResponse || !$refundResponse['success']) {
                                throw new \Exception('Gagal memproses refund ke RFID: ' . ($refundResponse['message'] ?? 'Unknown error'));
                            }

                            Log::info('RFID refund processed successfully', [
                                'transaction' => $this->selectedTransaction->transaction_number,
                                'santri_id' => $santriId,
                                'amount' => $refundAmount
                            ]);
                        }
                    }
                } catch (\Exception $rfidError) {
                    Log::error('Failed to process RFID refund', [
                        'error' => $rfidError->getMessage(),
                        'transaction' => $this->selectedTransaction->transaction_number
                    ]);
                    throw new \Exception('Gagal memproses refund ke RFID: ' . $rfidError->getMessage());
                }
            }

            // Return stock for refunded items
            foreach ($this->refundItems as $itemId => $refundItem) {
                if ($refundItem['selected']) {
                    $transactionItem = TransactionItem::find($itemId);
                    if ($transactionItem && $transactionItem->product) {
                        $transactionItem->product->updateStock($refundItem['quantity'], 'add');
                    }
                }
            }

            // Update transaction status
            if ($isFullRefund) {
                $this->selectedTransaction->update(['status' => 'refunded']);
            }

            // Record financial transaction
            $financialService = app(FinancialService::class);
            $santriData = null;
            
            if ($this->refundToRfid) {
                // Extract santri data from transaction for financial record
                $notes = $this->selectedTransaction->notes;
                preg_match('/Santri: (.+?) - RFID: (.+?)$/', $notes, $matches);
                if (count($matches) >= 3) {
                    $santriData = [
                        'name' => $matches[1],
                        'rfid' => $matches[2]
                    ];
                }
            }

            $financialService->recordRefund(
                $this->selectedTransaction,
                $refundAmount,
                $this->refundReason,
                $santriData
            );

            DB::commit();

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => 'Berhasil',
                'message' => 'Pengembalian berhasil diproses. Total: Rp ' . number_format($refundAmount, 0, ',', '.')
            ]);

            $this->closeRefundModal();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Refund process failed', [
                'error' => $e->getMessage(),
                'transaction' => $this->selectedTransaction->transaction_number
            ]);

            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => 'Gagal',
                'message' => 'Gagal memproses pengembalian: ' . $e->getMessage()
            ]);
        }
    }

    public function getTransactionsProperty()
    {
        return Transaction::with(['items', 'user'])
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('transaction_number', 'like', '%' . $this->search . '%')
                      ->orWhere('customer_name', 'like', '%' . $this->search . '%')
                      ->orWhere('notes', 'like', '%' . $this->search . '%');
                });
            })
            ->whereBetween('created_at', [
                \Carbon\Carbon::parse($this->dateFrom)->startOfDay(),
                \Carbon\Carbon::parse($this->dateTo)->endOfDay()
            ])
            ->whereIn('status', ['completed', 'refunded'])
            ->latest()
            ->paginate(15);
    }

    public function render()
    {
        return view('livewire.refund-manager', [
            'transactions' => $this->transactions,
        ])->layout('layouts.epos', [
            'header' => 'Manajemen Refund'
        ]);
    }
}
