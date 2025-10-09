<div class="p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">
            @if(auth()->user()->isCashier())
                My Sales Report
            @else
                Sales Report
            @endif
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Comprehensive sales analytics and insights</p>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Report Type</label>
                <select wire:model.live="reportType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="daily">Today</option>
                    <option value="weekly">This Week</option>
                    <option value="monthly">This Month</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
                <input type="date" wire:model="dateFrom" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" :disabled="reportType !== 'custom'">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
                <input type="date" wire:model="dateTo" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" :disabled="reportType !== 'custom'">
            </div>

            <!-- Cashier Filter (Admin only) -->
            @if(auth()->user()->canAccessAdmin())
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cashier</label>
                <select wire:model="selectedCashier" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Cashiers</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Payment Method Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                <select wire:model="selectedPaymentMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Methods</option>
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="rfid">RFID</option>
                    <option value="card">Card</option>
                    <option value="transfer">Transfer</option>
                </select>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-3 mt-4">
            <button wire:click="generateReport" class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-chart-line mr-2"></i>Generate Report
            </button>
            <button wire:click="exportReport" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors">
                <i class="fas fa-file-export mr-2"></i>Export
            </button>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Sales -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Sales</p>
                    <h3 class="text-3xl font-bold mt-2">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Transactions</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($totalTransactions) }}</h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-receipt text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Average Transaction -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Average Sale</p>
                    <h3 class="text-3xl font-bold mt-2">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-calculator text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Profit -->
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Profit</p>
                    <h3 class="text-3xl font-bold mt-2">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h3>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-chart-line text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Products -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-trophy text-yellow-500 mr-2"></i>Top Selling Products
            </h3>
            @if(count($topProducts) > 0)
                <div class="space-y-3">
                    @foreach($topProducts as $index => $item)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white">{{ $item->product->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->total_quantity }} items sold</p>
                                </div>
                            </div>
                            <p class="text-green-600 dark:text-green-400 font-bold">
                                Rp {{ number_format($item->total_sales, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>No sales data available</p>
                </div>
            @endif
        </div>

        <!-- Sales by Payment Method -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-credit-card text-blue-500 mr-2"></i>Payment Methods
            </h3>
            @if(count($salesByPaymentMethod) > 0)
                <div class="space-y-3">
                    @foreach($salesByPaymentMethod as $payment)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                    @if($payment->payment_method === 'cash')
                                        <i class="fas fa-money-bill text-blue-600 dark:text-blue-300"></i>
                                    @elseif($payment->payment_method === 'qris')
                                        <i class="fas fa-qrcode text-blue-600 dark:text-blue-300"></i>
                                    @elseif($payment->payment_method === 'rfid')
                                        <i class="fas fa-credit-card text-blue-600 dark:text-blue-300"></i>
                                    @else
                                        <i class="fas fa-wallet text-blue-600 dark:text-blue-300"></i>
                                    @endif
                                </div>
                                <div>
                                    <p class="font-medium text-gray-800 dark:text-white capitalize">{{ $payment->payment_method }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $payment->count }} transactions</p>
                                </div>
                            </div>
                            <p class="text-green-600 dark:text-green-400 font-bold">
                                Rp {{ number_format($payment->total, 0, ',', '.') }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                    <i class="fas fa-inbox text-4xl mb-3"></i>
                    <p>No payment data available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sales by Hour (Daily Report Only) -->
    @if($reportType === 'daily' && count($salesByHour) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-4">
                <i class="fas fa-clock text-indigo-500 mr-2"></i>Sales by Hour
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($salesByHour as $hourData)
                    <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-center">
                        <p class="text-2xl font-bold text-gray-800 dark:text-white">{{ str_pad($hourData->hour, 2, '0', STR_PAD_LEFT) }}:00</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $hourData->count }} sales</p>
                        <p class="text-xs text-green-600 dark:text-green-400 font-medium mt-1">
                            Rp {{ number_format($hourData->total / 1000, 0) }}K
                        </p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
