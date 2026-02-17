<div>
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Penjualan {{ $stats['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['revenue'], 0, ',', '.') }}</p>
                    <p class="text-sm {{ $stats['growth'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        <i class="fas fa-arrow-{{ $stats['growth'] >= 0 ? 'up' : 'down' }}"></i> {{ $stats['growth'] }}% dari periode lalu
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Produk</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalProducts) }}</p>
                    <p class="text-sm {{ $lowStockCount > 0 ? 'text-red-600' : 'text-green-600' }} mt-1">
                        <i class="fas fa-exclamation-triangle"></i> {{ $lowStockCount }} stok menipis
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pelanggan Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activeCustomers['total']) }}</p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-user-plus"></i> {{ $activeCustomers['new_this_week'] }} baru minggu ini
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Transaction Count -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Transaksi {{ $stats['label'] }}</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_transactions']) }}</p>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fas fa-receipt"></i> Total transaksi
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-receipt text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sales Chart -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Ringkasan Penjualan</h3>
                    <div class="flex space-x-2">
                        <button wire:click="setTimeFrame('daily')" class="px-3 py-1 text-sm {{ $timeFrame === 'daily' ? 'text-indigo-600 bg-indigo-100' : 'text-gray-600 hover:bg-gray-100' }} rounded-lg">Harian</button>
                        <button wire:click="setTimeFrame('weekly')" class="px-3 py-1 text-sm {{ $timeFrame === 'weekly' ? 'text-indigo-600 bg-indigo-100' : 'text-gray-600 hover:bg-gray-100' }} rounded-lg">Mingguan</button>
                        <button wire:click="setTimeFrame('monthly')" class="px-3 py-1 text-sm {{ $timeFrame === 'monthly' ? 'text-indigo-600 bg-indigo-100' : 'text-gray-600 hover:bg-gray-100' }} rounded-lg">Bulanan</button>
                    </div>
                </div>
                <!-- Chart Placeholder -->
                <div class="h-64 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-chart-area text-4xl text-indigo-400 mb-4"></i>
                        <p class="text-gray-600">Grafik Penjualan akan ditampilkan di sini</p>
                        <p class="text-sm text-gray-500">Integrasi dengan Chart.js atau sejenisnya</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions & Quick Actions -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    <a href="{{ route('pos') }}" class="w-full flex items-center p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-plus text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Penjualan Baru</p>
                            <p class="text-sm text-gray-500">Mulai transaksi baru</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('products') }}" class="w-full flex items-center p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-box-open text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Tambah Produk</p>
                            <p class="text-sm text-gray-500">Tambah item inventori baru</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('sales.report') }}" class="w-full flex items-center p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chart-bar text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Lihat Laporan</p>
                            <p class="text-sm text-gray-500">Periksa analitik penjualan</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
                    <a href="{{ route('transactions') }}" class="text-sm text-indigo-600 hover:text-indigo-500">Lihat Semua</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentTransactions as $transaction)
                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center mr-3 {{ $transaction->status === 'completed' ? 'bg-green-100' : 'bg-gray-100' }}">
                                <i class="fas {{ $transaction->status === 'completed' ? 'fa-check text-green-600' : 'fa-clock text-gray-600' }} text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $transaction->transaction_number }}</p>
                                <p class="text-sm text-gray-500">{{ $transaction->total_items }} item - {{ ucfirst($transaction->payment_method) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500">{{ $transaction->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        <p>Belum ada transaksi</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts & Payment Methods -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Peringatan Stok Menipis</h3>
                @if($lowStockCount > 0)
                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">{{ $lowStockCount }} item</span>
                @endif
            </div>
            <div class="space-y-3">
                @forelse($lowStockProducts as $product)
                <div class="flex items-center justify-between p-3 {{ $product->stock_quantity <= 0 ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200' }} border rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">{{ $product->name }}</p>
                        <p class="text-sm text-gray-500">SKU: {{ $product->sku }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium {{ $product->stock_quantity <= 0 ? 'text-red-600' : 'text-yellow-600' }}">{{ $product->stock_quantity }} tersisa</p>
                        <p class="text-xs text-gray-500">Min: {{ $product->min_stock }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-4 text-gray-500">
                    <p class="text-green-600"><i class="fas fa-check-circle mr-2"></i>Semua stok aman</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Payment Methods Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Metode Pembayaran</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-money-bill-wave text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Tunai</p>
                            <p class="text-sm text-gray-500">Selalu tersedia</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-qrcode text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">QRIS</p>
                            <p class="text-sm text-gray-500">Terhubung</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-wifi text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">RFID/NFC</p>
                            <p class="text-sm text-gray-500">Siap</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-credit-card text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Terminal Kartu</p>
                            <p class="text-sm text-gray-500">Offline</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>
