<div>
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
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
        <!-- Financial Report Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col md:flex-row items-center justify-between mb-6 gap-4">
                    <h3 class="text-lg font-semibold text-gray-900">Laporan Keuangan</h3>
                    <div class="flex flex-col sm:flex-row gap-3 items-end sm:items-center">
                        @if($timeFrame === 'custom')
                        <div class="flex items-center space-x-2 animate-fade-in-down">
                            <input type="date" wire:model.live="customStartDate" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                            <span class="text-gray-400">-</span>
                            <input type="date" wire:model.live="customEndDate" class="text-sm border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
                        </div>
                        @endif
                        
                        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
                            <button wire:click="setTimeFrame('daily')" class="px-3 py-1.5 text-xs font-medium {{ $timeFrame === 'daily' ? 'text-indigo-600 bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }} rounded-md transition-all">Harian</button>
                            <button wire:click="setTimeFrame('weekly')" class="px-3 py-1.5 text-xs font-medium {{ $timeFrame === 'weekly' ? 'text-indigo-600 bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }} rounded-md transition-all">Mingguan</button>
                            <button wire:click="setTimeFrame('monthly')" class="px-3 py-1.5 text-xs font-medium {{ $timeFrame === 'monthly' ? 'text-indigo-600 bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }} rounded-md transition-all">Bulanan</button>
                            <button wire:click="setTimeFrame('custom')" class="px-3 py-1.5 text-xs font-medium {{ $timeFrame === 'custom' ? 'text-indigo-600 bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }} rounded-md transition-all">Custom</button>
                        </div>
                    </div>
                </div>

                <!-- Financial Summary Cards -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="p-4 bg-green-50 rounded-lg border border-green-100">
                        <p class="text-xs text-green-600 font-medium mb-1">Total Pendapatan</p>
                        <p class="text-lg font-bold text-green-700">Rp {{ number_format($financialReport['summary']['total_income'], 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-red-50 rounded-lg border border-red-100">
                        <p class="text-xs text-red-600 font-medium mb-1">Total Pengeluaran</p>
                        <p class="text-lg font-bold text-red-700">Rp {{ number_format($financialReport['summary']['total_expense'], 0, ',', '.') }}</p>
                    </div>
                    <div class="p-4 bg-indigo-50 rounded-lg border border-indigo-100">
                        <p class="text-xs text-indigo-600 font-medium mb-1">Profit Bersih</p>
                        <p class="text-lg font-bold text-indigo-700">Rp {{ number_format($financialReport['summary']['total_profit'], 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan</th>
                                <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pengeluaran</th>
                                <th class="px-4 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                                <th class="px-4 py-3 bg-gray-50 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jml Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($financialReport['details'] as $row)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-medium">{{ $row['label'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 text-right">Rp {{ number_format($row['income'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600 text-right">Rp {{ number_format($row['expense'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-indigo-600 text-right font-medium">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 text-center">{{ $row['count'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">Tidak ada data untuk periode ini</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-gray-50 font-medium">
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">Total</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-green-700 text-right">Rp {{ number_format($financialReport['summary']['total_income'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-red-700 text-right">Rp {{ number_format($financialReport['summary']['total_expense'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-indigo-700 text-right">Rp {{ number_format($financialReport['summary']['total_profit'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700 text-center">{{ collect($financialReport['details'])->sum('count') }}</td>
                            </tr>
                        </tfoot>
                    </table>
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
