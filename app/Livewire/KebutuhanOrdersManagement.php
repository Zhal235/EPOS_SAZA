<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\KebutuhanOrder;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\FinancialTransaction;
use App\Models\Product;
use App\Services\SimpelsApiService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KebutuhanOrdersManagement extends Component
{
    use WithPagination;

    public $filter = 'all';
    public $search = '';
    public $sortBy = 'created_at';
    public $sortDir = 'desc';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function setSortBy($column)
    {
        if ($this->sortBy === $column) {
            $this->sortDir = $this->sortDir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDir = 'asc';
        }
    }

    public function completeOrder(int $orderId): void
    {
        $order = KebutuhanOrder::find($orderId);

        if (!$order || $order->status !== 'confirmed') {
            $this->dispatch('showNotification', [
                'type'    => 'error',
                'title'   => 'Error',
                'message' => 'Pesanan tidak ditemukan atau belum dikonfirmasi.',
                'options' => ['duration' => 3000],
            ]);
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Buat Transaction utama (masuk ke riwayat transaksi EPOS)
            $transaction = Transaction::create([
                'user_id'        => Auth::id(),
                'outlet_mode'    => 'store',
                'customer_name'  => $order->santri_name,
                'subtotal'       => $order->total_amount,
                'tax_amount'     => 0,
                'discount_amount'=> 0,
                'total_amount'   => $order->total_amount,
                'paid_amount'    => $order->total_amount,
                'change_amount'  => 0,
                'payment_method' => 'rfid',
                'status'         => 'completed',
                'notes'          => 'Pesanan Kebutuhan: ' . $order->order_number,
            ]);

            // 2. Buat TransactionItem per item + kurangi stok
            foreach ($order->items as $item) {
                $productId  = $item['product_id'] ?? null;
                $productSku = '';
                $product    = $productId ? Product::find($productId) : null;
                if ($product) {
                    $productSku = $product->sku ?? $product->id . '-KBT';
                }

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id'     => $productId,
                    'product_sku'    => $productSku,
                    'product_name'   => $item['name'],
                    'unit_price'     => $item['price'],
                    'quantity'       => $item['qty'],
                    'total_price'    => $item['subtotal'],
                ]);

                // Kurangi stok jika product ada di DB
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product && $product->track_stock) {
                        $product->updateStock($item['qty'], 'subtract');
                    }
                }
            }

            // 3. Catat ke FinancialTransaction sebagai pemasukan EPOS
            FinancialTransaction::create([
                'transaction_number'  => 'KBT-FIN-' . $order->order_number,
                'transaction_id'      => $transaction->id,
                'type'                => 'kebutuhan_order',
                'category'            => 'income',
                'santri_id'           => $order->santri_id,
                'santri_name'         => $order->santri_name,
                'amount'              => $order->total_amount,
                'payment_method'      => 'rfid',
                'reference_number'    => $order->order_number,
                'synced_to_simpels'   => true, // Saldo sudah dipotong saat wali konfirmasi
                'synced_at'           => now(),
                'description'         => 'Pesanan kebutuhan santri: ' . $order->order_number,
                'status'              => 'completed',
                'user_id'             => Auth::id(),
            ]);

            // 4. Update status order ke completed
            $order->update(['status' => 'completed']);

            DB::commit();

            $this->dispatch('showNotification', [
                'type'    => 'success',
                'title'   => '✅ Barang Diserahkan',
                'message' => "Pesanan {$order->order_number} ({$order->santri_name}) berhasil diserahkan. Transaksi tercatat di riwayat.",
                'options' => ['duration' => 5000],
            ]);

            Log::info('KebutuhanOrder completed', [
                'order_number'   => $order->order_number,
                'transaction_id' => $transaction->id,
                'amount'         => $order->total_amount,
                'cashier_id'     => Auth::id(),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('completeOrder failed', ['error' => $e->getMessage()]);
            $this->dispatch('showNotification', [
                'type'    => 'error',
                'title'   => 'Error',
                'message' => 'Gagal menyelesaikan pesanan: ' . $e->getMessage(),
                'options' => ['duration' => 5000],
            ]);
        }
    }

    /**
     * Refresh status pesanan dari SIMPELS backend untuk update lokal
     */
    public function refreshFromSimpels(): void
    {
        try {
            $simpelsApi = new SimpelsApiService();
            $updated = 0;

            // Ambil semua santri yang punya order pending di lokal
            $santriIds = KebutuhanOrder::where('status', 'pending_confirmation')
                ->pluck('santri_id')
                ->unique()
                ->filter();

            foreach ($santriIds as $santriId) {
                $result = $simpelsApi->checkKebutuhanOrderStatus((string)$santriId);

                if (!($result['success'] ?? false) || empty($result['orders'])) {
                    continue;
                }

                foreach ($result['orders'] as $simpelsOrder) {
                    $eposOrderId = $simpelsOrder['epos_order_id'] ?? null;
                    $newStatus   = $simpelsOrder['status'] ?? null;

                    if (!$eposOrderId || !$newStatus) continue;

                    // Map SIMPELS status ke EPOS status
                    $mappedStatus = match($newStatus) {
                        'confirmed' => 'confirmed',
                        'rejected'  => 'rejected',
                        'expired'   => 'expired',
                        default     => null,
                    };

                    if (!$mappedStatus) continue;

                    $rows = KebutuhanOrder::where('order_number', $eposOrderId)
                        ->where('status', 'pending_confirmation')
                        ->update([
                            'status'           => $mappedStatus,
                            'confirmed_at'     => $simpelsOrder['confirmed_at'] ?? null,
                            'confirmed_by'     => $simpelsOrder['confirmed_by'] ?? null,
                            'rejection_reason' => $simpelsOrder['rejection_reason'] ?? null,
                        ]);

                    $updated += $rows;
                }
            }

            $msg = $updated > 0
                ? "{$updated} pesanan berhasil diperbarui."
                : 'Semua status sudah terkini.';

            $this->dispatch('showNotification', [
                'type'    => 'success',
                'title'   => '✅ Refresh Selesai',
                'message' => $msg,
                'options' => ['duration' => 3000],
            ]);
        } catch (\Exception $e) {
            Log::error('refreshFromSimpels failed', ['error' => $e->getMessage()]);
            $this->dispatch('showNotification', [
                'type'    => 'error',
                'title'   => 'Error',
                'message' => 'Gagal memperbarui: ' . $e->getMessage(),
                'options' => ['duration' => 4000],
            ]);
        }
    }

    public function render()
    {
        $query = KebutuhanOrder::query();

        // Apply filter - show all statuses except completed if filter is 'all'
        if ($this->filter !== 'all') {
            $query->where('status', $this->filter);
        } else {
            // By default, show all orders (pending, confirmed, completed, rejected, expired)
            $query->whereIn('status', ['pending_confirmation', 'confirmed', 'completed', 'rejected', 'expired']);
        }

        // Apply search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('order_number', 'like', "%{$this->search}%")
                  ->orWhere('santri_name', 'like', "%{$this->search}%")
                  ->orWhere('santri_id', 'like', "%{$this->search}%");
            });
        }

        // Sort
        $query->orderBy($this->sortBy, $this->sortDir);

        $orders = $query->paginate(20);

        $counts = KebutuhanOrder::selectRaw("
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'pending_confirmation' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            COUNT(*) as total
        ")->first();

        return view('livewire.kebutuhan-orders-management', [
            'orders' => $orders,
            'counts' => $counts,
        ])->layout('layouts.epos', ['header' => 'Pesanan Kebutuhan Dikonfirmasi']);
    }
}
