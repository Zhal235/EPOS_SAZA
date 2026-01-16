<div>
    <!-- Session Messages Handler - Convert to Notifications -->
    @if (session()->has('message'))
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only show if notification system is ready and we haven't shown this message yet
            const messageId = 'session_message_{{ md5(session('message')) }}';
            if (window.notificationSystem && !sessionStorage.getItem(messageId)) {
                sessionStorage.setItem(messageId, 'shown');
                setTimeout(() => {
                    window.notificationSystem.success(
                        '✅ Berhasil',
                        '{{ addslashes(session('message')) }}',
                        { 
                            duration: 4000, 
                            sessionMessage: true,
                            sound: true
                        }
                    );
                }, 100);
            }
        });
        </script>
        @php session()->forget('message') @endphp
    @endif

    @if (session()->has('error'))
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only show if notification system is ready and we haven't shown this error yet
            const errorId = 'session_error_{{ md5(session('error')) }}';
            if (window.notificationSystem && !sessionStorage.getItem(errorId)) {
                sessionStorage.setItem(errorId, 'shown');
                setTimeout(() => {
                    window.notificationSystem.error(
                        '❌ Kesalahan',
                        '{{ addslashes(session('error')) }}',
                        { 
                            duration: 6000, 
                            sessionMessage: true,
                            sound: true
                        }
                    );
                }, 100);
            }
        });
        </script>
        @php session()->forget('error') @endphp
    @endif

    <!-- API Integration Scripts loaded in layout -->

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
            <!-- Products Grid -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
                    <!-- Search & Categories -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="relative flex-1 max-w-md">
                                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari produk..." 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                            </div>
                            <div class="relative">
                                <input wire:model="barcodeInput" wire:keydown.enter="scanBarcode" type="text" placeholder="Pindai barcode..." 
                                       class="w-48 pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <i class="fas fa-barcode absolute left-3 top-4 text-gray-400"></i>
                            </div>
                            <button wire:click="scanBarcode" class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-barcode mr-2"></i>Pindai
                            </button>
                        </div>
                    </div>

                    <!-- Category Tabs -->
                    <div class="flex space-x-2 mb-6 overflow-x-auto">
                        <button wire:click="selectCategory('')" 
                                class="px-4 py-2 {{ $selectedCategory == '' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }} rounded-lg text-sm whitespace-nowrap">
                            Semua
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6 h-full flex flex-col" style="max-height: calc(100vh - 120px);">
                    <!-- Customer Selection -->
                    @if($paymentMethod !== 'rfid')
                    <div class="mb-4 flex-shrink-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pelanggan</label>
                        <select wire:model="customer" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm">
                            <option value="walk-in">Pelanggan Biasa</option>
                            @foreach(\App\Models\User::regularCustomers()->get() as $regularCustomer)
                                <option value="{{ $regularCustomer->id }}">{{ $regularCustomer->name }} - {{ $regularCustomer->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="mb-4 flex-shrink-0">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-id-card text-blue-600 mr-2 text-sm"></i>
                                    <div>
                                        <h4 class="text-xs font-medium text-blue-900">🔴 RFID Payment AKTIF</h4>
                                        <p class="text-xs text-blue-700">Sistem pembayaran RFID terintegrasi dengan SIMPels</p>
                                    </div>
                                </div>
                                <button 
                                    wire:click="getApiStatus" 
                                    class="text-xs px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                                    title="Cek mode API">
                                    📊 Mode
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Cart Items - Fixed Height with Scroll -->
                    <div class="flex-shrink-0 mb-4">
                        <h3 class="text-base md:text-lg font-semibold text-gray-900 mb-3">Item Keranjang</h3>
                    </div>
                    
                    <div class="flex-1 mb-4 overflow-hidden cart-items-container">
                        @if(count($cart) > 0)
                            <div class="h-full overflow-y-auto scrollbar-thin pr-2">
                                <div class="space-y-2">
                                    @foreach($cart as $item)
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-gray-50 cart-item">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-medium text-gray-900 text-sm truncate cart-item-name">{{ $item['name'] }}</h4>
                                                <p class="text-xs text-gray-500 cart-item-price">{{ 'Rp ' . number_format($item['price'], 0, ',', '.') }} per item</p>
                                            </div>
                                            <div class="flex items-center space-x-2 mx-2">
                                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})" class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 flex-shrink-0">
                                                    <i class="fas fa-minus text-xs"></i>
                                                </button>
                                                <span class="w-6 text-center text-sm font-medium">{{ $item['quantity'] }}</span>
                                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})" class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center hover:bg-gray-200 flex-shrink-0">
                                                    <i class="fas fa-plus text-xs"></i>
                                                </button>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <p class="font-medium text-gray-900 text-sm">{{ 'Rp ' . number_format($item['total'], 0, ',', '.') }}</p>
                                                <button wire:click="removeFromCart({{ $item['id'] }})" class="text-red-500 text-xs hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="h-full flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-shopping-cart text-gray-400 text-3xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">Keranjang Anda kosong</p>
                                    <p class="text-gray-400 text-xs">Tambahkan produk untuk mulai</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Order Summary -->
                    <div class="border-t border-gray-200 pt-3 mb-4 flex-shrink-0">
                        <div class="space-y-1">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal ({{ $this->totalItems }} item)</span>
                                <span class="text-gray-900">{{ 'Rp ' . number_format($this->subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Diskon</span>
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
                    <div class="mb-4 flex-shrink-0">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="selectPaymentMethod('cash')" class="flex flex-col items-center p-2 border-2 {{ $paymentMethod == 'cash' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-money-bill-wave {{ $paymentMethod == 'cash' ? 'text-indigo-600' : 'text-gray-600' }} mb-1 text-sm"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'cash' ? 'text-indigo-600' : 'text-gray-600' }}">Tunai</span>
                            </button>
                            <button wire:click="selectPaymentMethod('qris')" class="flex flex-col items-center p-2 border-2 {{ $paymentMethod == 'qris' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-qrcode {{ $paymentMethod == 'qris' ? 'text-indigo-600' : 'text-gray-600' }} mb-1 text-sm"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'qris' ? 'text-indigo-600' : 'text-gray-600' }}">QRIS</span>
                            </button>
                            <button wire:click="selectPaymentMethod('rfid')" class="flex flex-col items-center p-2 border-2 {{ $paymentMethod == 'rfid' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-wifi {{ $paymentMethod == 'rfid' ? 'text-indigo-600' : 'text-gray-600' }} mb-1 text-sm"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'rfid' ? 'text-indigo-600' : 'text-gray-600' }}">RFID</span>
                            </button>
                            <button wire:click="selectPaymentMethod('card')" class="flex flex-col items-center p-2 border-2 {{ $paymentMethod == 'card' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 hover:border-gray-300' }} rounded-lg">
                                <i class="fas fa-credit-card {{ $paymentMethod == 'card' ? 'text-indigo-600' : 'text-gray-600' }} mb-1 text-sm"></i>
                                <span class="text-xs font-medium {{ $paymentMethod == 'card' ? 'text-indigo-600' : 'text-gray-600' }}">Kartu</span>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2 flex-shrink-0">
                        <button wire:click="processPayment" 
                                @if(count($cart) == 0) disabled @endif
                                class="w-full py-3 {{ count($cart) > 0 ? 'bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800' : 'bg-gray-300 cursor-not-allowed' }} text-white rounded-lg font-medium transition-all text-sm">
                            <i class="fas fa-credit-card mr-2"></i>Proses Pembayaran
                        </button>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="holdTransaction" 
                                    @if(count($cart) == 0) disabled @endif
                                    class="py-2 {{ count($cart) > 0 ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-gray-200 text-gray-400 cursor-not-allowed' }} border rounded-lg text-xs">
                                <i class="fas fa-save mr-1"></i>Tahan
                            </button>
                            <button wire:click="clearCart" 
                                    @if(count($cart) == 0) disabled @endif
                                    class="py-2 {{ count($cart) > 0 ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-gray-200 text-gray-400 cursor-not-allowed' }} border rounded-lg text-xs">
                                <i class="fas fa-trash mr-1"></i>Kosongkan
                            </button>
                        </div>
                        
                        <!-- Held Transactions - Collapsible for space -->
                        @if(count($holdTransactions) > 0)
                            <details class="mt-3 pt-3 border-t border-gray-200">
                                <summary class="text-sm font-medium text-gray-700 cursor-pointer hover:text-gray-900">
                                    Transaksi Tertahan ({{ count($holdTransactions) }})
                                </summary>
                                <div class="mt-2 space-y-1 max-h-20 overflow-y-auto">
                                    @foreach($holdTransactions as $holdId => $held)
                                        <button wire:click="loadHeldTransaction('{{ $holdId }}')" 
                                                class="w-full text-left p-2 text-xs border border-gray-200 rounded hover:bg-gray-50">
                                            <div class="flex justify-between">
                                                <span class="truncate">{{ $holdId }}</span>
                                                <span>{{ 'Rp ' . number_format($held['total'], 0, ',', '.') }}</span>
                                            </div>
                                            <div class="text-gray-500 truncate">{{ $held['created_at'] }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            </details>
                        @endif

                        <!-- Quick Access -->
                        <div class="mt-3 pt-3 border-t border-gray-200">
                            <a href="{{ route('transactions') }}" class="w-full flex items-center justify-center py-2 text-xs text-indigo-600 border border-indigo-200 rounded-lg hover:bg-indigo-50 transition-colors">
                                <i class="fas fa-history mr-1"></i>Transaction History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RFID Modal -->
        @if($showRfidModal)
            <div class="fixed inset-0 overflow-y-auto h-full w-full z-50 flex items-center justify-center" wire:click="closeRfidModal">
                <div class="relative mx-auto p-6 border w-80 max-w-sm shadow-xl rounded-lg bg-white" @click.stop>
                    <div class="mt-3">
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-medium text-gray-900">
                                <i class="fas fa-id-card text-blue-600 mr-2"></i>
                                RFID Payment
                            </h3>
                            <div class="flex items-center space-x-2">
                                <button wire:click="forceCloseRfidModal" 
                                        class="text-orange-400 hover:text-orange-600 p-1 rounded" 
                                        title="Force Close Modal">
                                    <i class="fas fa-power-off text-sm"></i>
                                </button>
                                <button wire:click="closeRfidModal" class="text-gray-400 hover:text-gray-600">
                                    <i class="fas fa-times text-sm"></i>
                                </button>
                            </div>
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
                                
                                <!-- RFID Input Field -->
                                <div class="mt-4">
                                    <input 
                                        type="text" 
                                        id="rfid-input"
                                        placeholder="Scan RFID atau ketik manual"
                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        autocomplete="off"
                                        onkeydown="handleRfidScannerInput(event)"
                                    >
                                    <p class="text-xs text-gray-500 mt-2">
                                        <span class="text-blue-600 font-semibold">Scan kartu RFID</span> atau ketik manual lalu tekan Enter
                                    </p>
                                    <div class="mt-3 space-y-2">
                                        <button 
                                            onclick="triggerManualRfidInput()" 
                                            class="w-full px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                            <i class="fas fa-keyboard mr-2"></i>Input Manual RFID
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @elseif($selectedSantri)
                            <!-- Santri Found -->
                            <div class="py-4">
                                <!-- Header -->
                                <div class="text-center mb-4">
                                    <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                                        <i class="fas fa-user-check text-2xl text-green-600"></i>
                                    </div>
                                    <h4 class="text-lg font-semibold text-gray-900">Data Santri</h4>
                                    <p class="text-sm text-gray-600">Konfirmasi data sebelum pembayaran</p>
                                </div>
                                
                                <!-- Santri Information Card -->
                                <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-4 mb-4">
                                    <div class="space-y-3">
                                        <!-- Nama Santri -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-user text-green-600 mr-2"></i>
                                                <span class="text-sm font-medium text-gray-700">Nama Santri:</span>
                                            </div>
                                            <span class="text-sm font-bold text-gray-900">
                                                {{ is_array($selectedSantri) ? ($selectedSantri['nama_santri'] ?? $selectedSantri['name'] ?? '-') : ($selectedSantri->nama_santri ?? $selectedSantri->name ?? '-') }}
                                            </span>
                                        </div>
                                        
                                        <!-- Kelas -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-graduation-cap text-blue-600 mr-2"></i>
                                                <span class="text-sm font-medium text-gray-700">Kelas:</span>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-800">
                                                {{ is_array($selectedSantri) ? ($selectedSantri['kelas'] ?? $selectedSantri['class'] ?? '-') : ($selectedSantri->kelas ?? $selectedSantri->class ?? '-') }}
                                            </span>
                                        </div>
                                        
                                        <!-- Saldo -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-wallet text-green-600 mr-2"></i>
                                                <span class="text-sm font-medium text-gray-700">Saldo Saat Ini:</span>
                                            </div>
                                            <span class="text-sm font-bold text-green-700">
                                                Rp {{ number_format($santriBalance, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        
                                        <!-- Limit Harian -->
                                        @if($dailySpendingLimit > 0)
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-clock text-orange-600 mr-2"></i>
                                                <span class="text-sm font-medium text-gray-700">Limit Harian:</span>
                                            </div>
                                            <span class="text-sm font-semibold text-gray-800">
                                                Rp {{ number_format($dailySpendingLimit, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        
                                        <!-- Sisa Limit -->
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <i class="fas fa-hourglass-half text-orange-600 mr-2"></i>
                                                <span class="text-sm font-medium text-gray-700">Sisa Limit Hari Ini:</span>
                                            </div>
                                            <span class="text-sm font-bold {{ $remainingLimit >= $this->total ? 'text-green-700' : 'text-red-600' }}">
                                                Rp {{ number_format($remainingLimit, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Payment Summary -->
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                                    <h5 class="text-sm font-medium text-gray-900 mb-2">Ringkasan Pembayaran</h5>
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Total Belanja:</span>
                                            <span class="font-semibold text-gray-900">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Saldo Setelah Bayar:</span>
                                            <span class="font-bold {{ ($santriBalance - $this->total) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                Rp {{ number_format($santriBalance - $this->total, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        @if($dailySpendingLimit > 0)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Sisa Limit Setelah Bayar:</span>
                                            <span class="font-bold {{ ($remainingLimit - $this->total) >= 0 ? 'text-orange-600' : 'text-red-600' }}">
                                                Rp {{ number_format($remainingLimit - $this->total, 0, ',', '.') }}
                                            </span>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Warning Messages -->
                                @if($santriBalance < $this->total)
                                    <div class="bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                            <p class="text-sm text-red-700 font-medium">Saldo tidak mencukupi untuk transaksi ini!</p>
                                        </div>
                                    </div>
                                @elseif($dailySpendingLimit > 0 && $remainingLimit < $this->total)
                                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-4">
                                        <div class="flex items-center">
                                            <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                                            <p class="text-sm text-orange-700 font-medium">Transaksi melebihi batas limit harian!</p>
                                        </div>
                                    </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="flex space-x-3">
                                    <button wire:click="closeRfidModal" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium transition-colors">
                                        <i class="fas fa-times mr-2"></i>Batal
                                    </button>
                                    <button 
                                        onclick="processRfidPayment()" 
                                        class="flex-1 px-4 py-2.5 {{ ($santriBalance >= $this->total && ($dailySpendingLimit <= 0 || $remainingLimit >= $this->total)) ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 cursor-not-allowed' }} text-white rounded-lg text-sm font-medium transition-colors"
                                        {{ ($santriBalance < $this->total || ($dailySpendingLimit > 0 && $remainingLimit < $this->total)) ? 'disabled' : '' }}
                                    >
                                        <i class="fas fa-credit-card mr-2"></i>Bayar Sekarang
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

    <!-- RFID Integration Script -->
    <script>
    // Wait for all scripts to load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('POS Terminal RFID Integration initializing...');
        
        // Initialize RFID integration after a short delay to ensure all modules are loaded
        setTimeout(initializeRfidIntegration, 1000);
    });
    
    function initializeRfidIntegration() {
        console.log('Initializing RFID integration...');
        
        // Auto-focus RFID input when modal opens
        setupRfidInputFocus();
        
        // Setup customerScanner integration
        setupCustomerScannerIntegration();
        
        // Setup global functions
        setupGlobalRfidFunctions();
        
        console.log('RFID integration initialized successfully');
    }
    
    function setupRfidInputFocus() {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    const rfidInput = document.getElementById('rfid-input');
                    if (rfidInput && rfidInput.offsetParent !== null) {
                        setTimeout(() => {
                            rfidInput.focus();
                            rfidInput.select();
                            console.log('RFID input focused and selected');
                            
                            // Add event listener for immediate processing (with debounce)
                            let rfidProcessing = false;
                            rfidInput.addEventListener('input', function(e) {
                                const value = e.target.value.trim();
                                if (value.length >= 8 && !rfidProcessing) { // Prevent multiple processing
                                    rfidProcessing = true;
                                    console.log('Auto-processing RFID:', value);
                                    setTimeout(() => {
                                        @this.call('handleRfidScan', value);
                                        e.target.value = '';
                                        setTimeout(() => {
                                            rfidProcessing = false; // Reset flag after processing
                                        }, 1000);
                                    }, 300); // Increased delay
                                }
                            });
                        }, 100);
                    }
                }
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // Global function for manual RFID input
    window.triggerManualRfidInput = function() {
        const rfidInput = document.getElementById('rfid-input');
        if (rfidInput) {
            const rfidValue = rfidInput.value.trim();
            if (rfidValue) {
                console.log('Manual RFID input:', rfidValue);
                @this.call('handleRfidScan', rfidValue);
                rfidInput.value = '';
            } else {
                alert('Silakan masukkan nomor RFID terlebih dahulu');
                rfidInput.focus();
            }
        }
    };
    
    // Add global keyboard handler for RFID modal
    document.addEventListener('keydown', function(e) {
        // ESC key to force close modal if stuck
        if (e.key === 'Escape' && e.shiftKey) {
            console.log('Force closing RFID modal with Shift+ESC');
            @this.call('forceCloseRfidModal');
        }
        // F12 to reset RFID state (debug)
        else if (e.key === 'F12' && e.ctrlKey) {
            e.preventDefault();
            console.log('Debugging: Reset RFID state');
            @this.call('forceCloseRfidModal');
        }
    });
    
    function setupCustomerScannerIntegration() {
        // Check if customerScanner is available
        if (window.customerScanner) {
            console.log('CustomerScanner found, setting up integration...');
            
            const originalDisplayCustomerInfo = window.customerScanner.displayCustomerInfo;
            
            window.customerScanner.displayCustomerInfo = function(customer) {
                console.log('Customer found:', customer);
                
                // Call original method
                originalDisplayCustomerInfo.call(this, customer);
                
                // If RFID modal is open, process the RFID scan
                try {
                    const showRfidModal = @this.get('showRfidModal');
                    const selectedSantri = @this.get('selectedSantri');
                    
                    // Only process RFID if modal is open AND no santri is already selected
                    if (showRfidModal && customer && customer.rfid_tag && !selectedSantri) {
                        console.log('Processing RFID scan via Livewire...');
                        
                        // Call Livewire method to handle RFID scan
                        @this.call('handleRfidScan', customer.rfid_tag);
                        
                        console.log('RFID scan processed successfully');
                    } else if (selectedSantri) {
                        console.log('Santri already selected, skipping RFID scan');
                    }
                } catch (error) {
                    console.error('Error processing RFID scan:', error);
                }
            };
        } else {
            console.warn('CustomerScanner not found, retrying...');
            // Retry after 2 seconds
            setTimeout(setupCustomerScannerIntegration, 2000);
        }
    }
    
    function setupGlobalRfidFunctions() {
        // RFID Scanner Input Buffer
        let rfidBuffer = '';
        let rfidTimeout = null;
        
        // Handle RFID Scanner Input with buffer for split reads
        window.handleRfidScannerInput = function(event) {
            const input = event.target;
            
            // Clear previous timeout
            if (rfidTimeout) {
                clearTimeout(rfidTimeout);
            }
            
            // If Enter key is pressed
            if (event.key === 'Enter') {
                event.preventDefault();
                
                // Get current value
                const currentValue = input.value.trim();
                
                console.log('RFID Scanner Enter pressed', {
                    currentValue: currentValue,
                    length: currentValue.length,
                    buffer: rfidBuffer
                });
                
                // If we have buffered data, combine it
                if (rfidBuffer && rfidBuffer !== currentValue) {
                    const combinedValue = rfidBuffer + currentValue;
                    console.log('Combining buffered RFID:', {
                        buffer: rfidBuffer,
                        current: currentValue,
                        combined: combinedValue
                    });
                    input.value = combinedValue;
                    rfidBuffer = '';
                }
                
                // Wait 100ms to ensure all scanner data is received
                setTimeout(() => {
                    const finalValue = input.value.trim();
                    
                    console.log('Processing RFID:', {
                        value: finalValue,
                        length: finalValue.length
                    });
                    
                    if (finalValue.length >= 8) { // Minimum 8 digits
                        // Call Livewire method
                        @this.call('handleRfidScan', finalValue);
                        
                        // Clear input after processing
                        setTimeout(() => {
                            input.value = '';
                            rfidBuffer = '';
                        }, 500);
                    } else {
                        console.warn('RFID too short:', finalValue);
                    }
                }, 100);
                
                return false;
            }
            
            // Store current value in buffer
            rfidTimeout = setTimeout(() => {
                rfidBuffer = input.value.trim();
                console.log('RFID buffer updated:', rfidBuffer);
            }, 50);
        };
        
        // Global function for RFID payment processing
        window.processRfidPayment = async function() {
            console.log('Processing RFID payment...');
            
            try {
                const selectedSantri = @this.get('selectedSantri');
                const cart = @this.get('cart');
                const total = @this.get('total');
                
                console.log('Payment data:', { selectedSantri, cart, total });
                
                if (!selectedSantri) {
                    window.notificationSystem.error(
                        '❌ RFID Required',
                        'Silakan scan RFID santri terlebih dahulu!',
                        {
                            actions: [
                                {
                                    text: 'Scan RFID',
                                    class: 'primary',
                                    callback: () => window.customerScanner?.showManualRfidInput()
                                }
                            ]
                        }
                    );
                    return;
                }
                
                if (!cart || cart.length === 0) {
                    window.notificationSystem.warning(
                        '⚠️ Empty Cart',
                        'Keranjang belanja kosong! Tambahkan produk terlebih dahulu.'
                    );
                    return;
                }
                
                // Process payment in Livewire backend directly
                console.log('Calling Livewire confirmRfidPayment...');
                console.log('Livewire component:', @this);
                
                try {
                    const result = await @this.call('confirmRfidPayment');
                    console.log('Payment result:', result);
                    
                    if (result === false) {
                        console.error('Payment returned false');
                    }
                } catch (callError) {
                    console.error('Livewire call error:', callError);
                    throw callError;
                }
                
            } catch (error) {
                console.error('RFID Payment error:', error);
                console.error('Error stack:', error.stack);
                window.notificationSystem.error(
                    '❌ Payment Error',
                    'Terjadi kesalahan saat memproses pembayaran: ' + error.message
                );
            }
        };
        
        // Setup Livewire event listeners with multiple initialization approaches
        function setupLivewireEvents() {
            console.log('Setting up Livewire event listeners...');
            
            // Check if notificationSystem is available
            if (!window.notificationSystem) {
                console.warn('Notification system not ready, retrying...');
                setTimeout(setupLivewireEvents, 1000);
                return;
            }
            
            // Debounce tracking for notifications
            let lastSuccessTime = 0;
            let lastErrorTime = 0;
            const NOTIFICATION_DEBOUNCE = 1000; // 1 second debounce
            
            // Success notification from backend
            Livewire.on('showRfidSuccess', (event) => {
                const now = Date.now();
                if (now - lastSuccessTime < NOTIFICATION_DEBOUNCE) {
                    console.log('Success notification debounced');
                    return;
                }
                lastSuccessTime = now;
                
                console.log('RFID Success event received:', event);
                // Livewire v3 wraps data in array
                const data = Array.isArray(event) ? event[0] : event;
                console.log('Extracted data:', data);
                
                // Clear all existing notifications first to prevent duplicates
                if (window.notificationSystem) {
                    window.notificationSystem.removeAll();
                    
                    window.notificationSystem.rfidSuccess(
                        data.customerName || 'Unknown',
                        data.amount || 0,
                        data.newBalance || 0,
                        data.transactionRef || 'N/A',
                        data.newRemainingLimit ?? null
                    );
                }
            });
            
            // Error notification from backend
            Livewire.on('showRfidError', (event) => {
                const now = Date.now();
                if (now - lastErrorTime < NOTIFICATION_DEBOUNCE) {
                    console.log('Error notification debounced');
                    return;
                }
                lastErrorTime = now;
                
                console.log('RFID Error event received:', event);
                // Livewire v3 wraps data in array
                const data = Array.isArray(event) ? event[0] : event;
                
                window.notificationSystem.rfidError(
                    data.errorMessage || 'Unknown error',
                    data.customerName || 'Unknown',
                    data.amount || 0
                );
            });
            
            // Success modal handler
            Livewire.on('showSuccessModal', (data) => {
                console.log('Success modal event received:', data);
                if (window.notificationSystem) {
                    window.notificationSystem.showSuccessModal(
                        data.title || '✅ Success',
                        data.message || 'Operation completed successfully'
                    );
                }
            });
            
            // Error modal handler
            Livewire.on('showErrorModal', (data) => {
                console.log('Error modal event received:', data);
                if (window.notificationSystem) {
                    window.notificationSystem.showErrorModal(
                        data.title || '❌ Error',
                        data.message || 'An error occurred'
                    );
                }
            });
            
            // General notification handler (fallback)
            Livewire.on('showNotification', (data) => {
                console.log('General notification event received:', data);
                
                // Ensure data is properly structured
                const notifData = Array.isArray(data) ? data[0] : data;
                
                if (window.notificationSystem && typeof window.notificationSystem[notifData.type] === 'function') {
                    window.notificationSystem[notifData.type](
                        notifData.title,
                        notifData.message,
                        notifData.options || {}
                    );
                } else {
                    console.error('Notification system method not found:', notifData.type);
                    console.log('Available notification methods:', Object.keys(window.notificationSystem || {}));
                    
                    // Fallback to success method if available
                    if (window.notificationSystem && typeof window.notificationSystem.success === 'function') {
                        window.notificationSystem.success(
                            notifData.title || 'Notification',
                            notifData.message || 'Message',
                            notifData.options || {}
                        );
                    } else {
                        // Last resort fallback
                        alert((notifData.title || 'Notification') + ': ' + (notifData.message || 'Message'));
                    }
                }
            });
            
            // Auto-close modal on scan completion (for error recovery)
            Livewire.on('rfidScanCompleted', () => {
                console.log('RFID scan completed - auto closing modal');
                setTimeout(() => {
                    const modal = document.querySelector('[wire\\:click="closeRfidModal"]');
                    if (modal) {
                        modal.click();
                    }
                }, 2000);
            });
            
            console.log('Livewire event listeners setup complete');
        }
        
        // Try multiple initialization approaches
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', setupLivewireEvents);
        } else {
            setupLivewireEvents();
        }
        
        // Also try with Livewire hooks
        document.addEventListener('livewire:init', setupLivewireEvents);
        document.addEventListener('livewire:load', setupLivewireEvents);
        
        // Fallback - setup after a delay
        setTimeout(setupLivewireEvents, 2000);
        
        // Manual RFID scan function for testing
        window.testRfidScan = function(rfidTag) {
            if (window.customerScanner && rfidTag) {
                console.log('Testing RFID scan:', rfidTag);
                window.customerScanner.scanRFID(rfidTag);
            } else {
                console.error('CustomerScanner not available or RFID tag not provided');
            }
        };
        
        // Debug functions for RFID troubleshooting
        window.debugRfidState = function() {
            console.log('=== RFID DEBUG STATE ===');
            console.log('Modal visible:', document.querySelector('#rfid-modal') !== null);
            console.log('RFID Input:', document.getElementById('rfid-input'));
            console.log('Livewire available:', typeof @this !== 'undefined');
            console.log('Notification system:', typeof window.notificationSystem);
            console.log('========================');
        };
        
        window.forceResetRfid = function() {
            console.log('Force resetting RFID state...');
            @this.call('resetRfidState');
        };
        
        window.testRfidWithValue = function(rfidValue = '24910818') {
            console.log('Testing RFID with value:', rfidValue);
            @this.call('handleRfidScan', rfidValue);
        };
        
        window.testLivewire = function() {
            console.log('Testing Livewire connection...');
            @this.call('testLivewireConnection');
        };
    }
    </script>

    <!-- Custom CSS untuk Tablet POS Terminal -->
    <style>
        /* Custom scrollbar styling */
        .scrollbar-thin {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }
        
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f7fafc;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        
        /* Tablet optimizations */
        @media screen and (min-width: 768px) and (max-width: 1024px) {
            /* Panel kanan max height di tablet */
            .lg\\:col-span-1 > div {
                max-height: calc(100vh - 140px) !important;
            }
            
            /* Keranjang area di tablet - tinggi tetap dan scrollable */
            .cart-container {
                max-height: 250px !important;
                min-height: 200px !important;
            }
        }
        
        /* Tablet landscape - lebih pendek */
        @media screen and (min-width: 768px) and (max-width: 1024px) and (orientation: landscape) {
            .lg\\:col-span-1 > div {
                max-height: calc(100vh - 120px) !important;
            }
            
            .cart-container {
                max-height: 180px !important;
                min-height: 150px !important;
            }
        }
        
        /* Mobile optimizations */
        @media screen and (max-width: 767px) {
            .lg\\:col-span-1 > div {
                max-height: calc(100vh - 100px) !important;
            }
            
            .cart-container {
                max-height: 200px !important;
                min-height: 150px !important;
            }
        }
        
        /* Smooth scrolling */
        .smooth-scroll {
            scroll-behavior: smooth;
        }
        
        /* Hide scrollbar on mobile but keep functionality */
        @media screen and (max-width: 767px) {
            .scrollbar-thin {
                scrollbar-width: none;
                -ms-overflow-style: none;
            }
            
            .scrollbar-thin::-webkit-scrollbar {
                display: none;
            }
        }
        
        /* Ensure proper spacing on all devices */
        .pos-cart-panel {
            min-height: 0;
            display: flex;
            flex-direction: column;
        }
        
        /* Force fixed height for cart items container */
        .cart-items-container {
            height: 300px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        /* Responsive adjustments for cart height */
        @media screen and (max-width: 1024px) {
            .cart-items-container {
                height: 250px !important;
                max-height: 250px !important;
            }
        }
        
        @media screen and (max-width: 768px) {
            .cart-items-container {
                height: 200px !important;
                max-height: 200px !important;
            }
        }
        
        /* Responsive text sizing */
        @media screen and (max-width: 640px) {
            .cart-item-name {
                font-size: 0.75rem;
                line-height: 1rem;
            }
            
            .cart-item-price {
                font-size: 0.625rem;
            }
        }
    </style>
</div>
