<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use App\Services\SimpelsApiService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PosTerminal extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
    // 'store' = mode toko biasa, 'foodcourt' = mode foodcourt (hanya produk tenant)
    public string $outletMode = 'store';
    public $cart = [];
    public $customer = 'walk-in';
    public $paymentMethod = 'cash';
    public $discount = 0;
    public $holdTransactions = [];
    public $barcodeInput = '';
    
    // RFID related properties
    public $showRfidModal = false;
    public $selectedSantri = null;
    public $rfidScanning = false;
    public $santriBalance = 0;
    public $dailySpendingLimit = 0;
    public $remainingLimit = 0;
    
    // Cash payment properties
    public $showCashModal = false;
    public $cashReceived = '';
    public $calculateChange = 0;
    public $showReceiptModal = false;
    public $lastTransaction = null;
    
    // Development mode tracking
    public $devModeNotified = false;
    
    // Last RFID scan timestamp for debouncing
    private $lastRfidScan = 0;
    
    protected $simpelsApi;

    /**
     * Constructor injection alternative
     */
    public function boot()
    {
        if (!$this->simpelsApi) {
            $this->initializeSimpelsApi();
        }
    }

    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => '']
    ];

    public function mount()
    {
        $this->cart = [];
        $this->selectedSantri = null;
        
        // Auto-detect mode from URL query parameter 'mode'
        if (request()->has('mode') && in_array(request('mode'), ['store', 'foodcourt'])) {
            $this->outletMode = request('mode');
        }
        
        $this->initializeSimpelsApi();
    }
    
    /**
     * Initialize SimpelsApi service with error handling
     */
    protected function initializeSimpelsApi()
    {
        try {
            $this->simpelsApi = app(SimpelsApiService::class);
            Log::info('SimpelsApiService initialized successfully');
        } catch (\Exception $e) {
            Log::error('Failed to initialize SimpelsApiService: ' . $e->getMessage());
            $this->simpelsApi = null;
        }
    }
    
    /**
     * Get SimpelsApi service with lazy loading
     */
    protected function getSimpelsApi()
    {
        if (!$this->simpelsApi) {
            $this->initializeSimpelsApi();
        }
        
        if (!$this->simpelsApi) {
            // Log warning but don't fail completely - allow fallback mode
            Log::warning('SIMPels API service unavailable, using fallback mode');
            throw new \Exception('SIMPels API service tidak tersedia. Mode development akan digunakan.');
        }
        
        return $this->simpelsApi;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategory()
    {
        $this->resetPage();
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
        $this->resetPage();
    }

    public function switchOutletMode(string $mode): void
    {
        if (!in_array($mode, ['store', 'foodcourt'])) return;

        if (!empty($this->cart)) {
            $this->dispatch('showNotification', [
                'type' => 'warning',
                'title' => '⚠️ Keranjang Tidak Kosong',
                'message' => 'Kosongkan keranjang terlebih dahulu sebelum mengganti mode.',
                'options' => ['duration' => 4000]
            ]);
            return;
        }

        $this->outletMode = $mode;
        $this->selectedCategory = '';
        $this->search = '';
        $this->resetPage();

        $label = $mode === 'foodcourt' ? 'Foodcourt' : 'Toko';
        $this->dispatch('showNotification', [
            'type' => 'info',
            'title' => "Mode {$label}",
            'message' => "Menampilkan produk {$label}.",
            'options' => ['duration' => 2000]
        ]);
    }

    public function addToCart($productId)
    {
        $product = Product::with('tenant')->find($productId);
        
        if (!$product || !$product->canSell(1)) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Stok Habis',
                'message' => 'Produk kehabisan stok!',
                'options' => ['duration' => 3000]
            ]);
            return;
        }

        if (!$product->is_active) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Tidak Tersedia',
                'message' => 'Produk tidak tersedia!',
                'options' => ['duration' => 3000]
            ]);
            return;
        }

        // Guard: jangan campur produk toko dan foodcourt
        if ($product->outlet_type !== $this->outletMode) {
            $label = $product->outlet_type === 'foodcourt' ? 'Foodcourt' : 'Toko';
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Mode Tidak Sesuai',
                'message' => "Produk ini adalah produk {$label}. Ganti mode terlebih dahulu.",
                'options' => ['duration' => 4000]
            ]);
            return;
        }

        $existingItem = collect($this->cart)->where('id', $productId)->first();
        
        if ($existingItem) {
            // Check if we can add more
            if ($product->track_stock && $existingItem['quantity'] >= $product->stock_quantity) {
                $this->dispatch('showNotification', [
                    'type' => 'warning',
                    'title' => '⚠️ Batas Stok',
                    'message' => 'Stok tidak mencukupi!',
                    'options' => ['duration' => 3000]
                ]);
                return;
            }
            
            // Update quantity and recalculate commission
            $this->cart = collect($this->cart)->map(function ($item) use ($productId, $product) {
                if ($item['id'] == $productId) {
                    $item['quantity']++;
                    $item['total']             = $item['quantity'] * $item['price'];
                    $item['commission_amount'] = $product->calculateCommission($item['quantity']);
                    $item['tenant_amount']     = $item['total'] - $item['commission_amount'];
                }
                return $item;
            })->toArray();
        } else {
            // Calculate commission for this product
            $commissionAmount = $product->calculateCommission(1);

            // Add new item
            $this->cart[] = [
                'id'               => $product->id,
                'name'             => $product->name,
                'sku'              => $product->sku,
                'price'            => $product->selling_price,
                'quantity'         => 1,
                'total'            => $product->selling_price,
                'stock'            => $product->stock_quantity,
                // Tenant info (null for store products)
                'outlet_type'      => $product->outlet_type,
                'tenant_id'        => $product->tenant_id,
                'tenant_name'      => $product->tenant?->display_name,
                'commission_type'  => $product->commission_type ?? $product->tenant?->commission_type,
                'commission_value' => $product->commission_value ?? $product->tenant?->commission_value,
                'commission_amount'=> $commissionAmount,
                'tenant_amount'    => $product->selling_price - $commissionAmount,
                'item_notes'       => '',
            ];
        }

        // Show quick success notification
        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => '✅ Ditambah ke Keranjang',
            'message' => "{$product->name} berhasil ditambahkan!",
            'options' => ['duration' => 2000, 'sound' => false]
        ]);
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        $product = Product::find($productId);
        if ($product->track_stock && $quantity > $product->stock_quantity) {
            $this->dispatch('showNotification', [
                'type' => 'warning',
                'title' => '⚠️ Batas Stok',
                'message' => 'Stok tidak mencukupi!',
                'options' => ['duration' => 3000]
            ]);
            return;
        }

        $this->cart = collect($this->cart)->map(function ($item) use ($productId, $quantity, $product) {
            if ($item['id'] == $productId) {
                $item['quantity']          = $quantity;
                $item['total']             = $item['quantity'] * $item['price'];
                $item['commission_amount'] = $product->calculateCommission($item['quantity']);
                $item['tenant_amount']     = $item['total'] - $item['commission_amount'];
            }
            return $item;
        })->toArray();
    }

    public function updateItemNotes($productId, $notes): void
    {
        $this->cart = collect($this->cart)->map(function ($item) use ($productId, $notes) {
            if ($item['id'] == $productId) {
                $item['item_notes'] = substr(strip_tags($notes), 0, 200);
            }
            return $item;
        })->toArray();
    }

    public function removeFromCart($productId)
    {
        $this->cart = collect($this->cart)->where('id', '!=', $productId)->values()->toArray();
    }

    public function clearCart()
    {
        $this->cart = [];
        
        // Clear RFID related states after successful transaction
        $this->resetRfidState();
        
        // Clear cash payment states
        $this->closeCashModal();
        $this->showReceiptModal = false;
        $this->lastTransaction = null;
        
        // Reset payment method to default
        $this->paymentMethod = 'cash';
        $this->customer = 'walk-in';
        
        Log::info('Cart and payment states cleared after successful transaction');
    }

    public function selectPaymentMethod($method)
    {
        $this->paymentMethod = $method;
        
        // Reset customer selection when switching to/from RFID
        if ($method === 'rfid') {
            $this->customer = null;
            $this->selectedSantri = null;
        } else {
            $this->customer = 'walk-in';
        }
    }

    public function openRfidModal()
    {
        $this->showRfidModal = true;
        $this->rfidScanning = true;
    }

    public function closeRfidModal()
    {
        $this->resetRfidState();
        Log::info('RFID modal closed manually');
    }
    
    // Cash payment methods
    public function openCashModal()
    {
        $this->showCashModal = true;
        $this->cashReceived = '';
        $this->calculateChange = 0;
    }

    public function closeCashModal()
    {
        $this->showCashModal = false;
        $this->cashReceived = '';
        $this->calculateChange = 0;
    }

    public function updatedCashReceived()
    {
        $cashAmount = (float) str_replace([',', '.'], ['', ''], $this->cashReceived);
        $this->calculateChange = max(0, $cashAmount - $this->total);
    }

    public function processCashPayment()
    {
        $cashAmount = (float) str_replace([',', '.'], ['', ''], $this->cashReceived);
        
        if ($cashAmount < $this->total) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Uang Tidak Mencukupi',
                'message' => 'Jumlah uang yang diterima kurang dari total pembayaran!',
                'options' => ['duration' => 3000]
            ]);
            return;
        }

        // Process the actual payment
        $this->confirmCashPayment();
    }


    /**
     * Process tenant credits for a completed transaction item
     */
    protected function processTenantCredit(TransactionItem $transactionItem, $cartItem)
    {
        if (!empty($transactionItem->tenant_id)) {
            $tenant = Tenant::find($transactionItem->tenant_id);
            if ($tenant) {
                $creditAmount = $transactionItem->tenant_amount ?? $transactionItem->total_price;
                
                // Increment tenant balance
                $previousBalance = $tenant->balance;
                $tenant->increment('balance', $creditAmount);
                
                // Record in ledger
                \App\Models\TenantLedger::create([
                    'tenant_id' => $tenant->id,
                    'type' => 'sale',
                    'amount' => $creditAmount,
                    'balance_before' => $previousBalance,
                    'balance_after' => $previousBalance + $creditAmount,
                    'transaction_item_id' => $transactionItem->id,
                    'description' => "Penjualan Item: {$transactionItem->product_name}",
                ]);
            }
        }
    }

    public function confirmCashPayment()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong!');
            return;
        }

        $cashAmount = (float) str_replace([',', '.'], ['', ''], $this->cashReceived);
        $changeAmount = $cashAmount - $this->total;

        try {
            DB::beginTransaction();

            // Validate stock availability again before processing
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                if (!$product || !$product->canSell($item['quantity'])) {
                    throw new \Exception("Insufficient stock for {$item['name']}!");
                }
            }

            // Get customer name
            $customerName = 'Walk-in Customer';
            if ($this->customer !== 'walk-in') {
                $customerData = User::find($this->customer);
                $customerName = $customerData ? $customerData->name : 'Walk-in Customer';
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id() ?? 1,
                'customer_name' => $customerName,
                'subtotal' => $this->subtotal,
                'tax_amount' => 0,
                'discount_amount' => $this->discount,
                'total_amount' => $this->total,
                'paid_amount' => $cashAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $this->paymentMethod,
                'status' => 'completed'
            ]);

            // Create transaction items and update stock
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);

                $transactionItem = TransactionItem::create([
                    'transaction_id'   => $transaction->id,
                    'product_id'       => $product->id,
                    'product_sku'      => $product->sku,
                    'product_name'     => $product->name,
                    'unit_price'       => $item['price'],
                    'quantity'         => $item['quantity'],
                    'total_price'      => $item['total'],
                    // Tenant & commission snapshot
                    'tenant_id'        => $item['tenant_id'] ?? null,
                    'tenant_name'      => $item['tenant_name'] ?? null,
                    'commission_type'  => $item['commission_type'] ?? null,
                    'commission_value' => $item['commission_value'] ?? 0,
                    'commission_amount'=> $item['commission_amount'] ?? 0,
                    'tenant_amount'    => $item['tenant_amount'] ?? $item['total'],
                    'item_notes'       => $item['item_notes'] ?? null,
                ]);

                $product->updateStock($item['quantity'], 'subtract');

                // Process Tenant Credit
                $this->processTenantCredit($transactionItem, $item);
            }

            DB::commit();

            // Store transaction for receipt
            $this->lastTransaction = $transaction;

            // Load relations for receipt
            $transaction->load(['items']);

            // Close cash modal and show receipt
            $this->closeCashModal();
            $this->showReceiptModal = true;
            
            // Auto-trigger print for foodcourt
            if ($this->outletMode === 'foodcourt') {
                $this->dispatch('printReceipt', [
                   'transaction' => $transaction->toArray(),
                   'items' => $this->cart, // Ensure items are passed even if relation is slow
                   'cashReceived' => $cashAmount,
                   'change' => $changeAmount,
                   'paymentMethod' => 'cash'
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('showErrorModal', [
                'title' => '❌ Pembayaran Gagal',
                'message' => "Terjadi kesalahan saat memproses pembayaran:\n\n" . $e->getMessage() . "\n\nSilakan coba lagi atau hubungi administrator."
            ]);
        }
    }

    public function closeReceiptModal()
    {
        $this->showReceiptModal = false;
        $this->lastTransaction = null;
        $this->clearCart();
    }

    public function printReceipt()
    {
        if (!$this->lastTransaction) {
            return;
        }

        // Ensure items are loaded
        if (!$this->lastTransaction->relationLoaded('items')) {
            $this->lastTransaction->load('items');
        }

        // Dispatch event to print receipt
        $this->dispatch('printReceipt', [
            'transaction' => $this->lastTransaction->toArray(), // Convert to array to ensure relationships are included
            'items' => $this->lastTransaction->items->toArray(), // Explicitly pass items
            'change' => $this->lastTransaction->change_amount,
            'cashReceived' => $this->lastTransaction->paid_amount,
            'paymentMethod' => $this->lastTransaction->payment_method
        ]);

        // Close receipt modal and clear cart
        $this->closeReceiptModal();
        
        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => '✅ Pembayaran Berhasil',
            'message' => 'Transaksi berhasil diproses. Struk sedang dicetak.',
            'options' => ['duration' => 3000]
        ]);
    }
    
    /**
     * Force close modal (for emergency situations)
     */
    public function forceCloseRfidModal()
    {
        $this->closeRfidModal();
        
        $this->dispatch('showNotification', [
            'type' => 'info',
            'title' => '🔄 Modal Reset',
            'message' => 'Modal RFID telah direset.',
            'options' => ['duration' => 2000]
        ]);
        
        Log::warning('RFID Modal force closed');
    }
    
    /**
     * Handle RFID scan from frontend
     */
    public function handleRfidScan($rfidTag)
    {
        // Sanitize RFID input from scanner
        $rfidTag = $this->sanitizeRfidInput($rfidTag);
        
        if (empty($rfidTag)) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Invalid RFID',
                'message' => 'RFID tag tidak valid atau kosong!',
                'options' => ['duration' => 3000]
            ]);
            return;
        }
        
        // Skip RFID scan if santri is already selected (modal is open for current transaction)
        // This prevents accidental re-scans during an active transaction
        if ($this->selectedSantri && $this->showRfidModal) {
            Log::info('Santri already selected, ignoring duplicate scan', [
                'rfid' => $rfidTag, 
                'selected_santri' => $this->selectedSantri['nama_santri'] ?? 'Unknown'
            ]);
            return;
        }
        
        // Skip RFID scan if not in RFID modal mode
        if (!$this->showRfidModal) {
            Log::info('RFID modal not active, skipping scan', ['rfid' => $rfidTag]);
            return;
        }
        
        // Reset rfidScanning flag if it's been stuck for too long
        if ($this->rfidScanning) {
            Log::warning('RFID scan flag stuck - resetting', ['rfid' => $rfidTag]);
            $this->rfidScanning = false;
        }
        
        Log::info('Processing RFID scan from frontend', ['rfid' => $rfidTag]);
        $this->processRfidScan($rfidTag);
    }
    
    /**
     * Sanitize RFID input from scanner
     * Handles various scanner output formats and removes unwanted characters
     */
    private function sanitizeRfidInput($input)
    {
        if (empty($input)) {
            return '';
        }
        
        $original = $input;
        
        // Remove whitespace, newlines, carriage returns, tabs
        $input = trim($input);
        $input = preg_replace('/[\r\n\t\s]+/', '', $input);
        
        // Remove common RFID scanner prefix/suffix characters
        $input = str_replace(['\r', '\n', '\t', '^', '$', '?', '*'], '', $input);
        
        // Remove non-numeric characters (RFID should be numeric)
        $input = preg_replace('/[^0-9]/', '', $input);
        
        // Log sanitization if changes were made
        if ($original !== $input) {
            Log::info('RFID input sanitized', [
                'original' => $original,
                'sanitized' => $input,
                'original_length' => strlen($original),
                'sanitized_length' => strlen($input),
                'original_hex' => bin2hex($original),
            ]);
        }
        
        return $input;
    }
    
    /**
     * Simple test method for debugging Livewire connection
     */
    public function testLivewireConnection()
    {
        Log::info('Livewire test method called successfully');
        
        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => '✅ Tes Livewire',
            'message' => 'Koneksi Livewire berfungsi! Waktu: ' . now()->format('H:i:s'),
            'options' => ['duration' => 3000]
        ]);
        
        return 'success';
    }

    /**
     * Reset complete RFID state (for debugging)
     */
    public function resetRfidState()
    {
        $this->showRfidModal = false;
        $this->rfidScanning = false;
        $this->selectedSantri = null;
        $this->santriBalance = 0;
        $this->dailySpendingLimit = 0;
        $this->remainingLimit = 0;
        $this->lastRfidScan = 0;
        
        Log::info('RFID state completely reset');
        
        // Removed notification to reduce noise
    }
    
    /**
     * Check if we're in development mode (API not available)
     */
    protected function isApiAvailable()
    {
        try {
            $this->getSimpelsApi()->testConnection();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get API mode status
     */
    public function getApiStatus()
    {
        $isAvailable = $this->isApiAvailable();
        $config = config('services.simpels');
        
        $this->dispatch('showNotification', [
            'type' => $isAvailable ? 'success' : 'info',
            'title' => $isAvailable ? '🟢 Mode Produksi' : '🟡 Mode Pengembangan',
            'message' => $isAvailable ? 
                "API Terhubung: {$config['api_url']}" : 
                "API Tidak Tersedia. Menggunakan data cadangan untuk pengujian.",
            'options' => ['duration' => 5000]
        ]);
        
        Log::info('API Status Check', [
            'available' => $isAvailable,
            'url' => $config['api_url']
        ]);
    }

    /**
     * Test SIMPels API connection (for debugging)
     */
    public function testSimpelsConnection()
    {
        try {
            $api = $this->getSimpelsApi();
            
            // Test actual API connection
            $healthStatus = $api->getHealthStatus();
            
            if ($healthStatus['status'] === 'healthy') {
                Log::info('SIMPels API health check successful', $healthStatus);
                
                $this->dispatch('showNotification', [
                    'type' => 'success',
                    'title' => '✅ Koneksi API Berhasil',
                    'message' => "SIMPels API terhubung! Waktu respon: {$healthStatus['response_time_ms']}ms",
                    'options' => ['duration' => 5000]
                ]);
            } else {
                throw new \Exception($healthStatus['error']);
            }
            
        } catch (\Exception $e) {
            Log::error('SIMPels API connection test failed: ' . $e->getMessage());
            
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Koneksi API Gagal',
                'message' => 'SIMPels API tidak dapat diakses: ' . $e->getMessage(),
                'options' => ['duration' => 8000]
            ]);
        }
    }

    public function getSubtotalProperty()
    {
        return collect($this->cart)->sum('total');
    }

    public function getTotalProperty()
    {
        return $this->subtotal - $this->discount;
    }

    public function getTotalItemsProperty()
    {
        return collect($this->cart)->sum('quantity');
    }

    public function scanBarcode()
    {
        if (empty($this->barcodeInput)) {
            $this->dispatch('showNotification', [
                'type' => 'warning',
                'title' => '⚠️ Barcode Kosong',
                'message' => 'Silakan masukkan barcode!',
                'options' => ['duration' => 3000]
            ]);
            return;
        }

        $product = Product::where('barcode', $this->barcodeInput)
                         ->orWhere('sku', $this->barcodeInput)
                         ->first();

        if (!$product) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Tidak Ditemukan',
                'message' => 'Produk tidak ditemukan dengan barcode: ' . $this->barcodeInput,
                'options' => ['duration' => 4000]
            ]);
            $this->barcodeInput = '';
            return;
        }

        $this->addToCart($product->id);
        $this->barcodeInput = '';
    }

    public function holdTransaction()
    {
        if (empty($this->cart)) {
            $this->dispatch('showNotification', [
                'type' => 'warning',
                'title' => '⚠️ Keranjang Kosong',
                'message' => 'Keranjang kosong! Tambahkan produk terlebih dahulu.',
                'options' => ['duration' => 3000]
            ]);
            return;
        }

        $holdId = 'HOLD_' . time();
        $this->holdTransactions[$holdId] = [
            'id' => $holdId,
            'cart' => $this->cart,
            'customer' => $this->customer,
            'created_at' => now()->format('H:i:s'),
            'total' => $this->total
        ];

        $itemCount = count($this->cart);
        $this->clearCart();
        
        $this->dispatch('showNotification', [
            'type' => 'info',
            'title' => '📋 Transaksi Ditahan',
            'message' => "Transaksi {$holdId} ditahan dengan {$itemCount} item",
            'options' => ['duration' => 4000]
        ]);
    }

    public function loadHeldTransaction($holdId)
    {
        if (isset($this->holdTransactions[$holdId])) {
            $held = $this->holdTransactions[$holdId];
            $itemCount = count($held['cart']);
            $this->cart = $held['cart'];
            $this->customer = $held['customer'];
            unset($this->holdTransactions[$holdId]);
            
            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => '📥 Transaksi Dimuat',
                'message' => "Memuat {$holdId} dengan {$itemCount} item",
                'options' => ['duration' => 3000]
            ]);
        }
    }



    public function simulateRfidScan($rfidNumber = null)
    {
        // This is for testing purposes - simulates RFID scan
        // In real implementation, this will be called by RFID scanner
        $testRfid = $rfidNumber ?: 'RFID001'; // Default test RFID
        $this->processRfidScan($testRfid);
    }

    public function processRfidScan($rfidNumber)
    {
        $currentTime = time();
        
        // Prevent duplicate scans within 2 seconds
        if ($this->rfidScanning || ($currentTime - $this->lastRfidScan) < 2) {
            Log::warning('RFID scan debounced', ['rfid' => $rfidNumber, 'time_diff' => $currentTime - $this->lastRfidScan]);
            return;
        }
        
        $this->lastRfidScan = $currentTime;
        $this->rfidScanning = true;
        
        // Log original RFID for debugging
        Log::info('RFID Scan Input', ['original_rfid' => $rfidNumber, 'length' => strlen($rfidNumber)]);
        
        try {
            // Get santri data from SIMPels API
            Log::info('Requesting santri data from SIMPels API', ['rfid' => $rfidNumber]);
            
            try {
                $apiResponse = $this->getSimpelsApi()->getSantriByRfid($rfidNumber);
                
                Log::info('SIMPels API response received', [
                    'rfid' => $rfidNumber,
                    'success' => $apiResponse['success'] ?? false,
                    'has_data' => isset($apiResponse['data'])
                ]);
            } catch (\Exception $apiError) {
                Log::error('SIMPels API error', [
                    'rfid' => $rfidNumber,
                    'error' => $apiError->getMessage()
                ]);
                
                // Check if RFID not found vs API connection error
                if (str_contains($apiError->getMessage(), '404') || str_contains($apiError->getMessage(), 'tidak ditemukan')) {
                    throw new \Exception("RFID '{$rfidNumber}' tidak terdaftar atau tidak aktif!\n\nGunakan RFID yang valid seperti:\n• 2488698539\n• 2491081819\n• 2664790299");
                } else {
                    // API Connection Error - Show simple notification
                    $this->dispatch('showNotification', [
                        'type' => 'error',
                        'title' => '❌ Koneksi Server SIMPels Gagal',
                        'message' => 'Koneksi ke Server SIMPels Gagal! Pastikan server aktif atau hubungi admin.',
                        'options' => ['duration' => 6000]
                    ]);
                    
                    throw new \Exception('Koneksi ke Server SIMPels Gagal! Pastikan server aktif atau hubungi admin.');
                }
            }
            
            if (!$apiResponse || !isset($apiResponse['success']) || !$apiResponse['success']) {
                throw new \Exception('RFID tidak terdaftar atau bukan santri!');
            }
            
            $santriData = $apiResponse['data'];
            
            // Check if santri can afford the total
            if ($santriData['saldo'] < $this->total) {
                throw new \Exception(
                    "Saldo tidak mencukupi! Saldo: Rp " . number_format($santriData['saldo'], 0, ',', '.') . 
                    ", Total: Rp " . number_format($this->total, 0, ',', '.')
                );
            }

            // Check spending limit if exists
            if (isset($santriData['limit_harian']) && $santriData['limit_harian'] > 0) {
                // Use correct field name from API response
                $remainingLimit = $santriData['sisa_limit_hari_ini'] ?? $santriData['remaining_limit'] ?? $santriData['limit_harian'];
                
                Log::info('Limit checking', [
                    'santri' => $santriData['nama_santri'],
                    'total' => $this->total,
                    'limit_harian' => $santriData['limit_harian'],
                    'sisa_limit_hari_ini' => $santriData['sisa_limit_hari_ini'] ?? 'not_set',
                    'remaining_limit' => $santriData['remaining_limit'] ?? 'not_set',
                    'calculated_remaining' => $remainingLimit
                ]);
                
                if ($this->total > $remainingLimit) {
                    throw new \Exception(
                        "Melebihi batas belanja harian! Sisa limit: Rp " . number_format($remainingLimit, 0, ',', '.') . 
                        ", Total: Rp " . number_format($this->total, 0, ',', '.')
                    );
                }
            }

            // RFID scan successful - set santri data
            $this->selectedSantri = $santriData;
            $this->santriBalance = $santriData['saldo'];
            $this->dailySpendingLimit = $santriData['limit_harian'] ?? 0;
            $this->remainingLimit = $santriData['sisa_limit_hari_ini'] ?? $santriData['remaining_limit'] ?? $santriData['limit_harian'] ?? 0;
            $this->rfidScanning = false;
            
            Log::info('RFID Scan successful', ['santri' => $santriData['nama_santri'], 'rfid' => $rfidNumber]);
            
            // Dispatch success notification
            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => '✅ Santri Ditemukan',
                'message' => "Data santri {$santriData['nama_santri']} berhasil dimuat!",
                'options' => ['duration' => 3000]
            ]);
            
        } catch (\Exception $e) {
            // Reset RFID state completely on error
            $this->resetRfidState();

            // Show single error notification via showNotification only
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ RFID Error',
                'message' => $e->getMessage(),
                'options' => ['duration' => 6000, 'sound' => true]
            ]);

            // Also dispatch SweetAlert modal for critical connection errors
            if (str_contains($e->getMessage(), 'Koneksi ke Server SIMPels Gagal')) {
                $this->dispatch('swal:modal', [
                    'type' => 'error',
                    'title' => '🔌 Koneksi Server Terputus',
                    'text' => 'Server SIMPels tidak dapat diakses. Pastikan server aktif atau gunakan pembayaran TUNAI.',
                    'confirmButtonText' => 'Mengerti',
                    'showCancelButton' => true,
                    'cancelButtonText' => 'Coba Lagi'
                ]);
            }

            Log::error('RFID Scan failed', ['rfid' => $rfidNumber, 'error' => $e->getMessage()]);

            // Dispatch event to close modal in frontend
            $this->dispatch('rfidScanCompleted');
        }
    }

    public function confirmRfidPayment()
    {
        Log::info('=== confirmRfidPayment START ===', [
            'has_selected_santri' => !empty($this->selectedSantri),
            'cart_count' => count($this->cart),
            'total' => $this->total
        ]);
        
        if (!$this->selectedSantri) {
            Log::warning('No santri selected');
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ RFID Error',
                'message' => 'Silakan scan RFID santri terlebih dahulu!',
                'options' => ['duration' => 5000]
            ]);
            return false;
        }

        // Final validation
        if ($this->santriBalance < $this->total) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Saldo Tidak Mencukupi',
                'message' => 'Saldo santri tidak mencukupi untuk transaksi ini!',
                'options' => ['duration' => 5000]
            ]);
            return false;
        }

        // NOTE: global minimum balance is enforced by SIMPels backend (min_balance_jajan)
        // Don't query local DB for wallet settings here to avoid inconsistencies or missing tables.
        // We will proceed and rely on the SIMPels API (called later) to reject transactions that violate minimum balance rules.

        if ($this->dailySpendingLimit > 0 && $this->remainingLimit < $this->total) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Melebihi Limit Harian',
                'message' => 'Transaksi melebihi batas limit belanja harian!',
                'options' => ['duration' => 5000]
            ]);
            return false;
        }

        try {
            DB::beginTransaction();

            // Validate stock availability
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                if (!$product || !$product->canSell($item['quantity'])) {
                    throw new \Exception("Stock tidak mencukupi untuk {$item['name']}! Stock tersedia: " . ($product->stock_quantity ?? 0));
                }
            }

            // Get santri data
            $santriName = is_array($this->selectedSantri) ? 
                ($this->selectedSantri['nama_santri'] ?? $this->selectedSantri['name'] ?? 'Unknown') : 
                ($this->selectedSantri->nama_santri ?? $this->selectedSantri->name ?? 'Unknown');
            
            $santriClass = is_array($this->selectedSantri) ? 
                ($this->selectedSantri['kelas'] ?? $this->selectedSantri['class'] ?? '') : 
                ($this->selectedSantri->kelas ?? $this->selectedSantri->class ?? '');

            $santriRfid = is_array($this->selectedSantri) ? 
                ($this->selectedSantri['rfid_tag'] ?? '') : 
                ($this->selectedSantri->rfid_tag ?? '');

            // Create transaction in local database
            $transaction = Transaction::create([
                'user_id' => Auth::id() ?? 1,
                'customer_name' => $santriName,
                'customer_phone' => $santriClass,
                'subtotal' => $this->subtotal,
                'tax_amount' => 0,
                'discount_amount' => $this->discount,
                'total_amount' => $this->total,
                'paid_amount' => $this->total,
                'change_amount' => 0,
                'payment_method' => 'rfid',
                'status' => 'completed',
                'notes' => "RFID Payment - Santri: {$santriName} - RFID: {$santriRfid}"
            ]);

            // Create transaction items and update stock
            $itemsList = [];
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                
                $transactionItem = TransactionItem::create([
                    'transaction_id'   => $transaction->id,
                    'product_id'       => $product->id,
                    'product_sku'      => $product->sku,
                    'product_name'     => $product->name,
                    'unit_price'       => $item['price'],
                    'quantity'         => $item['quantity'],
                    'total_price'      => $item['total'],
                    'tenant_id'        => $item['tenant_id'] ?? null,
                    'tenant_name'      => $item['tenant_name'] ?? null,
                    'commission_type'  => $item['commission_type'] ?? null,
                    'commission_value' => $item['commission_value'] ?? 0,
                    'commission_amount'=> $item['commission_amount'] ?? 0,
                    'tenant_amount'    => $item['tenant_amount'] ?? $item['total'],
                    'item_notes'       => $item['item_notes'] ?? null,
                ]);

                $product->updateStock($item['quantity'], 'subtract');
                
                // Process Tenant Credit
                $this->processTenantCredit($transactionItem, $item);

                $itemsList[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['total']
                ];
            }

            // Process payment through SIMPels API
            try {
                Log::info("SIMPels Payment Request", [
                    'rfid' => $santriRfid,
                    'santri_name' => $santriName,
                    'amount' => $this->total,
                    'transaction_id' => $transaction->transaction_number,
                    'items' => $itemsList
                ]);

                // Call SIMPels API to process payment
                Log::info('Processing payment through SIMPels API', [
                    'rfid' => $santriRfid,
                    'amount' => $this->total,
                    'transaction_ref' => $transaction->transaction_number
                ]);
                
                // support different shapes returned by SIMPels API or local objects
                $santriId = null;
                if (is_array($this->selectedSantri)) {
                    $santriId = $this->selectedSantri['id'] ?? $this->selectedSantri['santri_id'] ?? null;
                } else {
                    $santriId = $this->selectedSantri->id ?? $this->selectedSantri->santri_id ?? null;
                }
                
                if (!$santriId) {
                    // include full selectedSantri payload for easier debugging
                    Log::error('Selected santri missing id', ['selectedSantri' => $this->selectedSantri]);
                    throw new \Exception('Santri ID tidak ditemukan dalam data RFID');
                }

                Log::info('Calling SIMPels processPayment', [
                    'santri_id' => $santriId,
                    'amount' => $this->total,
                    'transaction_ref' => $transaction->transaction_number
                ]);

                // normalize rfid value (support different payload keys)
                $rfidValue = '';
                if (is_array($this->selectedSantri)) {
                    $rfidValue = $this->selectedSantri['rfid_tag'] ?? $this->selectedSantri['rfid_uid'] ?? '';
                    if (is_array($rfidValue) && isset($rfidValue['uid'])) $rfidValue = $rfidValue['uid'];
                } else {
                    if (is_object($this->selectedSantri->rfid_tag)) {
                        $rfidValue = $this->selectedSantri->rfid_tag->uid ?? '';
                    } else {
                        $rfidValue = $this->selectedSantri->rfid_uid ?? '';
                    }
                }

                $paymentResponse = $this->getSimpelsApi()->processPayment([
                    'santri_id' => $santriId,
                    'rfid_tag' => $rfidValue,
                    'amount' => $this->total,
                    'transaction_ref' => $transaction->transaction_number,
                    'description' => "Transaksi EPOS #{$transaction->transaction_number} - " . count($itemsList) . " items",
                    'items' => $itemsList
                ]);
                
                Log::info('SIMPels processPayment response', [
                    'response' => $paymentResponse
                ]);

                if (!$paymentResponse || !isset($paymentResponse['success']) || !$paymentResponse['success']) {
                    // If SIMPels returned a structured error, include it directly
                    $msg = $paymentResponse['message'] ?? ($paymentResponse['data']['message'] ?? null) ?? 'Unknown error from SIMPels API';
                    throw new \Exception('Payment processing failed: ' . $msg);
                }

                // Get updated balance from API response
                $newBalance = $paymentResponse['data']['new_balance'] ?? ($this->santriBalance - $this->total);
                $newRemainingLimit = $paymentResponse['data']['remaining_limit'] ?? ($this->remainingLimit - $this->total);

                // Update component state AND selectedSantri data so UI reflects new values immediately
                $previousBalance = $this->santriBalance;
                $this->santriBalance = $newBalance;
                $this->remainingLimit = $newRemainingLimit;
                
                // Also update selectedSantri array so the modal displays updated values
                if ($this->selectedSantri) {
                    $this->selectedSantri['saldo'] = $newBalance;
                    $this->selectedSantri['sisa_limit_hari_ini'] = $newRemainingLimit;
                }

                // Record a FinancialTransaction for this RFID payment so the finance dashboard shows values
                try {
                    $financialService = app(\App\Services\FinancialService::class);
                    $financialService->recordRfidPayment($transaction, [
                        'id' => $santriId,
                        'name' => $santriName,
                        'rfid' => $rfidValue,
                        'previous_balance' => $previousBalance,
                        'new_balance' => $newBalance,
                    ]);

                    Log::info('Recorded FinancialTransaction for RFID payment', ['transaction' => $transaction->transaction_number]);
                } catch (\Exception $e) {
                    Log::error('Failed to save FinancialTransaction for RFID payment', ['error' => $e->getMessage(), 'transaction' => $transaction->transaction_number]);
                }

                Log::info("SIMPels Payment Success", [
                    'transaction_id' => $transaction->transaction_number,
                    'new_balance' => $newBalance,
                    'api_response' => $paymentResponse
                ]);

            } catch (\Exception $apiError) {
                Log::error("SIMPels API Payment Error", [
                    'error' => $apiError->getMessage(),
                    'transaction_id' => $transaction->transaction_number,
                    'rfid' => $santriRfid
                ]);
                
                // Check for connection vs payment errors
                if (str_contains($apiError->getMessage(), 'Connection') || 
                    str_contains($apiError->getMessage(), 'timeout') ||
                    str_contains($apiError->getMessage(), 'network') ||
                    str_contains($apiError->getMessage(), 'unavailable')) {
                    
                    // Connection Error - Show simple notification
                    $this->dispatch('showNotification', [
                        'type' => 'error',
                        'title' => '❌ Koneksi Server SIMPels Gagal',
                        'message' => 'Koneksi ke Server SIMPels Gagal! Pastikan server aktif atau hubungi admin.',
                        'options' => ['duration' => 6000]
                    ]);
                    
                    throw new \Exception('Koneksi ke Server SIMPels Gagal! Pastikan server aktif atau hubungi admin.');
                } else {
                    // Payment/Business Logic Error
                    $errorMsg = $apiError->getMessage();
                    $errorMsg = str_replace('Payment processing failed: ', '', $errorMsg);
                    
                    throw new \Exception($errorMsg);
                }
            }

            DB::commit();

            // Store transaction for receipt
            $this->lastTransaction = $transaction;
            $this->showReceiptModal = true;

            $cartItemCount = count($itemsList); // Use actual processed items count
            $transactionNumber = $transaction->transaction_number;

            // Trigger automatic print for Foodcourt
            if ($this->outletMode === 'foodcourt') {
               $this->dispatch('printReceipt', [
                   'transaction' => $transaction->toArray(),
                   'items' => $itemsList, 
                   'cashReceived' => $this->total, // Paid amount
                   'change' => 0,
                   'newBalance' => $newBalance, 
                   'newRemainingLimit' => $newRemainingLimit,
                   'paymentMethod' => 'RFID'
               ]);
            }

            // Clear cart and close modal after successful payment
            $this->clearCart();
            $this->closeRfidModal();
            
            // Show success notification
            $this->dispatch('showRfidSuccess', [
                'customerName' => $santriName,
                'amount' => $transaction->total_amount, // Use final transaction total
                'newBalance' => $newBalance,
                'newRemainingLimit' => $newRemainingLimit,
                'transactionRef' => $transactionNumber,
                'itemCount' => $cartItemCount
            ]);

            Log::info("RFID Payment completed successfully", [
                'transaction_number' => $transactionNumber,
                'santri_name' => $santriName,
                'amount' => $transaction->total_amount,
                'new_balance' => $newBalance
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("RFID Payment failed", [
                'error' => $e->getMessage(),
                'santri_name' => $santriName ?? 'Unknown',
                'amount' => $this->total,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Enhanced error notification with SweetAlert for critical errors
            if (str_contains($e->getMessage(), 'Server SIMPels Tidak Tersedia')) {
                $this->dispatch('swal:modal', [
                    'type' => 'error',
                    'title' => '🔌 Server SIMPels Offline',
                    'text' => 'Koneksi ke server SIMPels terputus. Transaksi dibatalkan untuk keamanan data.',
                    'footer' => 'Gunakan pembayaran TUNAI atau tunggu server kembali aktif.',
                    'confirmButtonText' => 'Gunakan Tunai',
                    'showCancelButton' => true,
                    'cancelButtonText' => 'Tutup'
                ]);
            } else {
                // Show simple error modal
                $this->dispatch('swal:modal', [
                    'type' => 'error',
                    'title' => '❌ Pembayaran Gagal',
                    'text' => $e->getMessage(),
                    'confirmButtonText' => 'OK'
                ]);
            }

            return false;
        }
    }

    public function processPayment()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang kosong!');
            return;
        }

        // If RFID payment, open RFID modal instead of direct payment
        if ($this->paymentMethod === 'rfid') {
            $this->openRfidModal();
            return;
        }

        // If cash payment, open cash modal for amount input
        if ($this->paymentMethod === 'cash') {
            $this->openCashModal();
            return;
        }

        // Regular payment flow for other methods (qris, card)
        try {
            DB::beginTransaction();

            // Validate stock availability again before processing
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                if (!$product || !$product->canSell($item['quantity'])) {
                    throw new \Exception("Insufficient stock for {$item['name']}!");
                }
            }

            // Get customer name
            $customerName = 'Walk-in Customer';
            if ($this->customer !== 'walk-in') {
                $customerData = User::find($this->customer);
                $customerName = $customerData ? $customerData->name : 'Walk-in Customer';
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id() ?? 1,
                'customer_name' => $customerName,
                'subtotal' => $this->subtotal,
                'tax_amount' => 0,
                'discount_amount' => $this->discount,
                'total_amount' => $this->total,
                'paid_amount' => $this->total,
                'change_amount' => 0,
                'payment_method' => $this->paymentMethod,
                'status' => 'completed'
            ]);

            // Create transaction items and update stock
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                
                $transactionItem = TransactionItem::create([
                    'transaction_id'   => $transaction->id,
                    'product_id'       => $product->id,
                    'product_sku'      => $product->sku,
                    'product_name'     => $product->name,
                    'unit_price'       => $item['price'],
                    'quantity'         => $item['quantity'],
                    'total_price'      => $item['total'],
                    'tenant_id'        => $item['tenant_id'] ?? null,
                    'tenant_name'      => $item['tenant_name'] ?? null,
                    'commission_type'  => $item['commission_type'] ?? null,
                    'commission_value' => $item['commission_value'] ?? 0,
                    'commission_amount'=> $item['commission_amount'] ?? 0,
                    'tenant_amount'    => $item['tenant_amount'] ?? $item['total'],
                    'item_notes'       => $item['item_notes'] ?? null,
                ]);

                $product->updateStock($item['quantity'], 'subtract');
                
                // Process Tenant Credit
                $this->processTenantCredit($transactionItem, $item);
            }

            DB::commit();

            $totalAmount = $this->total;
            $itemCount = $this->totalItems;
            $transactionNumber = $transaction->transaction_number;

            // Clear cart
            $this->clearCart();
            
            // Show simple success notification for non-cash payments
            \Log::info('Dispatching success notification for non-cash payment');
            
            $this->dispatch('showSuccessModal', [
                'title' => '✅ Pembayaran Berhasil!',
                'message' => "Transaksi #{$transactionNumber} telah berhasil diproses.\n\nTotal: Rp " . number_format($totalAmount, 0, ',', '.') . "\nMetode: " . strtoupper($this->paymentMethod) . "\nItems: {$itemCount} produk"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->dispatch('showErrorModal', [
                'title' => '❌ Pembayaran Gagal',
                'message' => "Terjadi kesalahan saat memproses pembayaran:\n\n" . $e->getMessage() . "\n\nSilakan coba lagi atau hubungi administrator."
            ]);
        }
    }

    public function getProducts()
    {
        return Product::with(['category', 'tenant'])
            ->where('outlet_type', $this->outletMode)  // hanya tampilkan produk sesuai mode
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('barcode', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('stock_quantity', '>', 0)
                  ->orWhere('track_stock', false);
            })
            ->orderBy('name')
            ->paginate(12);
    }

    public function render()
    {
        $categories = Category::active()->ordered()->get();
        $tenants    = $this->outletMode === 'foodcourt'
            ? Tenant::active()->ordered()->get()
            : collect();

        $headerMode = $this->outletMode === 'foodcourt' ? 'Foodcourt' : 'Toko';
        
        return view('livewire.pos-terminal', [
            'products'   => $this->getProducts(),
            'categories' => $categories,
            'tenants'    => $tenants,
        ])->layout('layouts.epos', [
            'header' => "POS Terminal ({$headerMode})"
        ]);
    }
}
