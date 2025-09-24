<div>
    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
             class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
             class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    <!-- API Integration Scripts -->
    <script src="/js/config/api.js"></script>
    <script src="/js/utils/api.js"></script>
    <script src="/js/modules/customer-scanner.js"></script>
    <script src="/js/modules/transaction-processor.js"></script>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
            <!-- Products Grid -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
                    <!-- Search & Categories -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="relative flex-1 max-w-md">
                                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search products..." 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                            </div>
                            <div class="relative">
                                <input wire:model="barcodeInput" wire:keydown.enter="scanBarcode" type="text" placeholder="Scan barcode..." 
                                       class="w-48 pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <i class="fas fa-barcode absolute left-3 top-4 text-gray-400"></i>
                            </div>
                            <button wire:click="scanBarcode" class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-barcode mr-2"></i>Scan
                            </button>
                        </div>
                    </div>

                    <!-- Category Tabs -->
                    <div class="flex space-x-2 mb-6 overflow-x-auto">
                        <button wire:click="selectCategory('')" 
                                class="px-4 py-2 {{ $selectedCategory == '' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg text-sm whitespace-nowrap">
                            All
                        </button>
                        @foreach($categories as $category)
                            <button wire:click="selectCategory('{{ $category->id }}')" 
                                    class="px-4 py-2 {{ $selectedCategory == $category->id ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg text-sm whitespace-nowrap">
                                {{ $category->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        @forelse($products as $product)
                            <div wire:click="addToCart({{ $product->id }})" class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                                <div class="w-full h-24 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
                                    @if($product->image_url)
                                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <i class="{{ $product->category->icon ?? 'fas fa-box' }} text-2xl" style="color: {{ $product->category->color ?? '#6366F1' }}"></i>
                                    @endif
                                </div>
                                <h4 class="font-medium text-gray-900 text-sm mb-1">{{ $product->name }}</h4>
                                <p class="text-xs text-gray-500 mb-2">SKU: {{ $product->sku }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-bold text-indigo-600">{{ $product->formatted_selling_price }}</span>
                                    <span class="text-xs {{ $product->is_low_stock ? 'text-red-500' : 'text-gray-500' }}">
                                        Stock: {{ $product->stock_quantity }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-12">
                                <i class="fas fa-box-open text-gray-400 text-6xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">No Products Found</h3>
                                <p class="text-gray-500">
                                    @if($search || $selectedCategory)
                                        No products match your current search or category filter.
                                    @else
                                        No products available at the moment.
                                    @endif
                                </p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if($products->hasPages())
                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cart & Checkout -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full flex flex-col">
                    <!-- Customer Selection -->
                    @if($paymentMethod !== 'rfid')
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select wire:model="customer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="walk-in">Walk-in Customer</option>
                            @foreach(\App\Models\User::regularCustomers()->get() as $regularCustomer)
                                <option value="{{ $regularCustomer->id }}">{{ $regularCustomer->name }} - {{ $regularCustomer->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="mb-6">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-id-card text-blue-600 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-blue-900">🔴 RFID Payment AKTIF</h4>
                                    <p class="text-sm text-blue-700">Sistem pembayaran RFID terintegrasi dengan SIMPels siap digunakan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Cart Items -->
                    <div class="flex-1 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cart Items</h3>
                        
                        @if(count($cart) > 0)
                            <div class="space-y-3">
                                @foreach($cart as $item)
                                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900 text-sm">{{ $item['name'] }}</h4>
                                            <p class="text-xs text-gray-500">{{ 'Rp ' . number_format($item['price'], 0, ',', '.') }} each</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            <span class="w-8 text-center">{{ $item['quantity'] }}</span>
                                            <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                        </div>
                                        <div class="ml-3 text-right">
                                            <p class="font-medium text-gray-900">{{ 'Rp ' . number_format($item['total'], 0, ',', '.') }}</p>
                                            <button wire:click="removeFromCart({{ $item['id'] }})" class="text-red-500 text-xs hover:text-red-700">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-shopping-cart text-gray-400 text-4xl mb-2"></i>
                                <p class="text-gray-500 text-sm">Your cart is empty</p>
                                <p class="text-gray-400 text-xs">Add products to get started</p>
                            </div>
                        @endif
                    </div>

                    <!-- Order Summary -->
                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal ({{ $this->totalItems }} items)</span>
                                <span class="text-gray-900">{{ 'Rp ' . number_format($this->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount</span>
                                <span class="text-green-600">-{{ 'Rp ' . number_format($discount, 0, ',', '.') }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-indigo-600">{{ 'Rp ' . number_format($this->total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button wire:click="selectPaymentMethod('cash')" class="flex flex-col items-center p-3 border-2 {{ $paymentMethod == 'cash' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-money-bill-wave {{ $paymentMethod == 'cash' ? 'text-indigo-600' : 'text-gray-600' }} mb-1"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'cash' ? 'text-indigo-600' : 'text-gray-600' }}">Cash</span>
                            </button>
                            <button wire:click="selectPaymentMethod('qris')" class="flex flex-col items-center p-3 border-2 {{ $paymentMethod == 'qris' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-qrcode {{ $paymentMethod == 'qris' ? 'text-indigo-600' : 'text-gray-600' }} mb-1"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'qris' ? 'text-indigo-600' : 'text-gray-600' }}">QRIS</span>
                            </button>
                            <button wire:click="selectPaymentMethod('rfid')" class="flex flex-col items-center p-3 border-2 {{ $paymentMethod == 'rfid' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-wifi {{ $paymentMethod == 'rfid' ? 'text-indigo-600' : 'text-gray-600' }} mb-1"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'rfid' ? 'text-indigo-600' : 'text-gray-600' }}">RFID</span>
                            </button>
                            <button wire:click="selectPaymentMethod('card')" class="flex flex-col items-center p-3 border-2 {{ $paymentMethod == 'card' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-credit-card {{ $paymentMethod == 'card' ? 'text-indigo-600' : 'text-gray-600' }} mb-1"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'card' ? 'text-indigo-600' : 'text-gray-600' }}">Card</span>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button wire:click="processPayment" 
                                @if(count($cart) == 0) disabled @endif
                                class="w-full py-3 {{ count($cart) > 0 ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'bg-gray-300 cursor-not-allowed' }} text-white rounded-lg font-medium transition-all">
                            <i class="fas fa-credit-card mr-2"></i>Process Payment
                        </button>
                        <div class="grid grid-cols-2 gap-3">
                            <button wire:click="holdTransaction" 
                                    @if(count($cart) == 0) disabled @endif
                                    class="py-2 {{ count($cart) > 0 ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-gray-200 text-gray-400 cursor-not-allowed' }} border rounded-lg text-sm">
                                <i class="fas fa-save mr-1"></i>Hold
                            </button>
                            <button wire:click="clearCart" 
                                    @if(count($cart) == 0) disabled @endif
                                    class="py-2 {{ count($cart) > 0 ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-gray-200 text-gray-400 cursor-not-allowed' }} border rounded-lg text-sm">
                                <i class="fas fa-trash mr-1"></i>Clear
                            </button>
                        </div>
                        
                        <!-- Held Transactions -->
                        @if(count($holdTransactions) > 0)
                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Held Transactions</h4>
                                <div class="space-y-2">
                                    @foreach($holdTransactions as $holdId => $held)
                                        <button wire:click="loadHeldTransaction('{{ $holdId }}')" 
                                                class="w-full text-left p-2 text-xs border border-gray-200 rounded hover:bg-gray-50">
                                            <div class="flex justify-between">
                                                <span>{{ $holdId }}</span>
                                                <span>{{ 'Rp ' . number_format($held['total'], 0, ',', '.') }}</span>
                                            </div>
                                            <div class="text-gray-500">{{ $held['created_at'] }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Quick Access -->
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Quick Access</h4>
                            <a href="{{ route('transactions') }}" class="w-full flex items-center justify-center py-2 text-sm text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                                <i class="fas fa-history mr-2"></i>View Transaction History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RFID Modal -->
        @if($showRfidModal)
            <div class="fixed inset-0 overflow-y-auto h-full w-full z-50 flex items-center justify-center" wire:click="closeRfidModal">
                <div class="relative mx-auto p-6 border w-80 max-w-sm shadow-xl rounded-lg bg-white" wire:click.stop>
                    <div class="mt-3">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-medium text-gray-900">
                                <i class="fas fa-id-card text-blue-600 mr-2"></i>
                                RFID Payment
                            </h3>
                            <button wire:click="closeRfidModal" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>

                        <!-- Order Summary in Modal -->
                        <div class="bg-gray-50 rounded-lg p-3 mb-4">
                            <h4 class="font-medium text-gray-900 mb-2 text-sm">Order Summary</h4>
                            <div class="space-y-1 text-xs">
                                <div class="flex justify-between">
                                    <span>Items ({{ $this->totalItems }})</span>
                                    <span>{{ 'Rp ' . number_format($this->subtotal, 0, ',', '.') }}</span>
                                </div>
                                @if($discount > 0)
                                    <div class="flex justify-between text-green-600">
                                        <span>Discount</span>
                                        <span>-{{ 'Rp ' . number_format($discount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <hr class="my-1">
                                <div class="flex justify-between font-bold text-sm">
                                    <span>Total</span>
                                    <span class="text-indigo-600">{{ 'Rp ' . number_format($this->total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        @if($rfidScanning)
                            <!-- Scanning State -->
                            <div class="text-center py-6">
                                <div class="animate-pulse">
                                    <i class="fas fa-id-card text-4xl text-blue-500 mb-3"></i>
                                </div>
                                <h4 class="text-base font-medium text-gray-900 mb-2">Tap RFID Card</h4>
                                <p class="text-gray-600 mb-4 text-sm">Silakan tempelkan kartu RFID santri ke reader</p>
                                
                                <!-- Test Button for Development -->
                                <div class="border-t pt-3 mt-3">
                                    <p class="text-xs text-gray-500 mb-2">Testing Mode:</p>
                                    <button wire:click="simulateRfidScan" class="px-3 py-2 bg-yellow-500 text-white rounded-lg text-xs hover:bg-yellow-600">
                                        Simulate RFID Scan
                                    </button>
                                </div>
                            </div>
                        @elseif($selectedSantri)
                            <!-- Santri Found -->
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="fas fa-user-check text-3xl text-green-500 mb-2"></i>
                                    <h4 class="text-base font-medium text-gray-900">Santri Ditemukan</h4>
                                </div>
                                
                                <!-- Santri Info -->
                                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-3">
                                    <div class="space-y-1 text-xs">
                                        <div class="flex justify-between">
                                            <span class="font-medium">Nama:</span>
                                            <span>{{ $selectedSantri->name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Kelas:</span>
                                            <span>{{ $selectedSantri->class ?: '-' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="font-medium">Saldo:</span>
                                            <span class="font-bold text-green-600">{{ $selectedSantri->formatted_balance }}</span>
                                        </div>
                                        @if($selectedSantri->spending_limit > 0)
                                            <div class="flex justify-between">
                                                <span class="font-medium">Limit Belanja:</span>
                                                <span>{{ $selectedSantri->formatted_spending_limit }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-2">
                                    <button wire:click="closeRfidModal" class="flex-1 px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm">
                                        Cancel
                                    </button>
                                    <button wire:click="confirmRfidPayment" class="flex-1 px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                                        <i class="fas fa-check mr-1"></i>Confirm
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
