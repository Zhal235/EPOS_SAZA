<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosTerminal extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';
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

    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'selectedCategory' => ['except' => '']
    ];

    public function mount()
    {
        $this->cart = [];
        $this->selectedSantri = null;
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

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock_quantity <= 0) {
            session()->flash('error', 'Product is out of stock!');
            return;
        }

        if (!$product->is_active) {
            session()->flash('error', 'Product is not available!');
            return;
        }

        $existingItem = collect($this->cart)->where('id', $productId)->first();
        
        if ($existingItem) {
            // Check if we can add more
            if ($existingItem['quantity'] >= $product->stock_quantity) {
                session()->flash('error', 'Not enough stock available!');
                return;
            }
            
            // Update quantity
            $this->cart = collect($this->cart)->map(function ($item) use ($productId) {
                if ($item['id'] == $productId) {
                    $item['quantity']++;
                    $item['total'] = $item['quantity'] * $item['price'];
                }
                return $item;
            })->toArray();
        } else {
            // Add new item
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->selling_price,
                'quantity' => 1,
                'total' => $product->selling_price,
                'stock' => $product->stock_quantity
            ];
        }

        // Show success message
        session()->flash('message', "{$product->name} added to cart!");
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        $product = Product::find($productId);
        if ($quantity > $product->stock_quantity) {
            session()->flash('error', 'Not enough stock available!');
            return;
        }

        $this->cart = collect($this->cart)->map(function ($item) use ($productId, $quantity) {
            if ($item['id'] == $productId) {
                $item['quantity'] = $quantity;
                $item['total'] = $item['quantity'] * $item['price'];
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
            session()->flash('error', 'Please enter a barcode!');
            return;
        }

        $product = Product::where('barcode', $this->barcodeInput)
                         ->orWhere('sku', $this->barcodeInput)
                         ->first();

        if (!$product) {
            session()->flash('error', 'Product not found!');
            $this->barcodeInput = '';
            return;
        }

        $this->addToCart($product->id);
        $this->barcodeInput = '';
    }

    public function holdTransaction()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty!');
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

        $this->clearCart();
        session()->flash('message', "Transaction held as {$holdId}");
    }

    public function loadHeldTransaction($holdId)
    {
        if (isset($this->holdTransactions[$holdId])) {
            $held = $this->holdTransactions[$holdId];
            $this->cart = $held['cart'];
            $this->customer = $held['customer'];
            unset($this->holdTransactions[$holdId]);
            session()->flash('message', 'Held transaction loaded!');
        }
    }

    // RFID Methods
    public function openRfidModal()
    {
        $this->showRfidModal = true;
        $this->rfidScanning = true;
        $this->selectedSantri = null;
    }

    public function closeRfidModal()
    {
        $this->showRfidModal = false;
        $this->rfidScanning = false;
        $this->selectedSantri = null;
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
        $this->rfidScanning = true;
        
        // Find santri by RFID number
        $santri = User::findByRfid($rfidNumber);
        
        if (!$santri) {
            session()->flash('error', 'RFID tidak terdaftar atau bukan santri!');
            $this->rfidScanning = false;
            return;
        }

        // Check if santri can afford the total
        if (!$santri->canAfford($this->total)) {
            session()->flash('error', "Saldo tidak mencukupi! Saldo: {$santri->formatted_balance}, Total: Rp " . number_format($this->total, 0, ',', '.'));
            $this->rfidScanning = false;
            return;
        }

        // Check spending limit
        if (!$santri->isWithinSpendingLimit($this->total)) {
            session()->flash('error', "Melebihi batas belanja! Limit: {$santri->formatted_spending_limit}, Total: Rp " . number_format($this->total, 0, ',', '.'));
            $this->rfidScanning = false;
            return;
        }

        // RFID scan successful
        $this->selectedSantri = $santri;
        $this->rfidScanning = false;
        session()->flash('message', "Santri ditemukan: {$santri->name} - {$santri->class}");
    }

    public function confirmRfidPayment()
    {
        if (!$this->selectedSantri) {
            session()->flash('error', 'Tidak ada santri yang dipilih!');
            return;
        }

        try {
            DB::beginTransaction();

            // Validate stock availability
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                if (!$product || $product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$item['name']}!");
                }
            }

            // Deduct santri balance
            if (!$this->selectedSantri->deductBalance($this->total)) {
                throw new \Exception('Failed to deduct balance!');
            }

            // Create transaction
            $transaction = Transaction::create([
                'user_id' => Auth::id() ?? 1,
                'customer_name' => $this->selectedSantri->name,
                'customer_phone' => $this->selectedSantri->class, // Store class in phone field for santri
                'subtotal' => $this->subtotal,
                'tax_amount' => 0,
                'discount_amount' => $this->discount,
                'total_amount' => $this->total,
                'paid_amount' => $this->total,
                'change_amount' => 0,
                'payment_method' => 'rfid',
                'status' => 'completed'
            ]);

            // Create transaction items and update stock
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total']
                ]);

                $product->updateStock($item['quantity'], 'subtract');
            }

            DB::commit();

            $transactionNumber = $transaction->transaction_number;
            $remainingBalance = $this->selectedSantri->formatted_balance;

            // Clear cart and close modal
            $this->clearCart();
            $this->closeRfidModal();
            
            session()->flash('message', "Pembayaran RFID berhasil! Transaksi #{$transactionNumber}. Sisa saldo: {$remainingBalance}");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    public function processPayment()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Cart is empty!');
            return;
        }

        // If RFID payment, open RFID modal instead of direct payment
        if ($this->paymentMethod === 'rfid') {
            $this->openRfidModal();
            return;
        }

        // Regular payment flow (cash, qris, card)
        try {
            DB::beginTransaction();

            // Validate stock availability again before processing
            foreach ($this->cart as $item) {
                $product = Product::find($item['id']);
                if (!$product || $product->stock_quantity < $item['quantity']) {
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
                
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total_price' => $item['total']
                ]);

                $product->updateStock($item['quantity'], 'subtract');
            }

            DB::commit();

            $totalAmount = $this->total;
            $itemCount = $this->totalItems;
            $transactionNumber = $transaction->transaction_number;

            // Clear cart and show success
            $this->clearCart();
            
            session()->flash('message', "Payment processed successfully! Transaction #{$transactionNumber} - Rp " . number_format($totalAmount, 0, ',', '.') . " ({$itemCount} items sold)");

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Payment processing failed: ' . $e->getMessage());
        }
    }

    public function getProducts()
    {
        return Product::with(['category'])
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
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->paginate(12);
    }

    public function render()
    {
        return view('livewire.pos-terminal', [
            'products' => $this->getProducts(),
            'categories' => Category::active()->ordered()->get()
        ])->layout('layouts.epos', [
            'header' => 'POS Terminal'
        ]);
    }
}
