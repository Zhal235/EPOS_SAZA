<div x-data="{ activeTab: 'sales' }">
    <!-- Quick Stats Banner -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 rounded-xl shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">
                        @if(auth()->user()->isCashier())
                            Personal Sales & Analytics
                        @else
                            Sales & Analytics Dashboard
                        @endif
                    </h2>
                    <p class="text-indigo-100 mt-1">Comprehensive sales insights and performance metrics</p>
                </div>
                <div class="text-right">
                    <div class="text-indigo-100 text-sm">Period</div>
                    <div class="text-xl font-semibold">
                        @if($reportType === 'daily')
                            Today
                        @elseif($reportType === 'weekly')
                            This Week
                        @elseif($reportType === 'monthly')
                            This Month
                        @else
                            Custom Range
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-2">
            <div class="flex gap-2">
                <button @click="activeTab = 'sales'" 
                        :class="activeTab === 'sales' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-chart-line"></i>
                    <span>Sales Overview</span>
                </button>
                <button @click="activeTab = 'analytics'" 
                        :class="activeTab === 'analytics' ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'"
                        class="flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2">
                    <i class="fas fa-chart-bar"></i>
                    <span>Analytics & Insights</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center mb-4">
            <i class="fas fa-filter text-indigo-600 text-lg mr-2"></i>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Report Filters</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-white dark:text-gray-300 mb-2">
                    <i class="fas fa-chart-bar mr-1"></i>Report Type
                </label>
                <select wire:model.live="reportType" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-indigo-500 focus:ring-indigo-500" style="color: #1f2937 !important;">
                    <option value="daily" class="text-gray-900 bg-white">Today</option>
                    <option value="weekly" class="text-gray-900 bg-white">This Week</option>
                    <option value="monthly" class="text-gray-900 bg-white">This Month</option>
                    <option value="custom" class="text-gray-900 bg-white">Custom Range</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-calendar-alt mr-1"></i>From Date
                </label>
                <input type="date" wire:model="dateFrom" 
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 transition-all"
                       x-bind:disabled="'{{ $reportType }}' !== 'custom'"
                       x-bind:class="'{{ $reportType }}' !== 'custom' ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : ''">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <i class="fas fa-calendar-check mr-1"></i>To Date
                </label>
                <input type="date" wire:model="dateTo" 
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 transition-all"
                       x-bind:disabled="'{{ $reportType }}' !== 'custom'"
                       x-bind:class="'{{ $reportType }}' !== 'custom' ? 'bg-gray-100 dark:bg-gray-600 cursor-not-allowed' : ''">
            </div>

                        <!-- Cashier Filter (Admin only) -->
            @if(auth()->user()->canAccessAdmin())
            <div>
                <label class="block text-sm font-medium text-white dark:text-gray-300 mb-2">
                    <i class="fas fa-user mr-1"></i>Cashier
                </label>
                <select wire:model="selectedCashier" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-indigo-500 focus:ring-indigo-500" style="color: #1f2937 !important;">
                    <option value="" class="text-gray-900 bg-white">All Cashiers</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}" class="text-gray-900 bg-white">{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <!-- Payment Method Filter -->
            <div>
                <label class="block text-sm font-medium text-white dark:text-gray-300 mb-2">
                    <i class="fas fa-credit-card mr-1"></i>Payment Method
                </label>
                <select wire:model="selectedPaymentMethod" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 focus:border-indigo-500 focus:ring-indigo-500" style="color: #1f2937 !important;">
                    <option value="" class="text-gray-900 bg-white">All Methods</option>
                    <option value="cash" class="text-gray-900 bg-white">Cash</option>
                    <option value="qris" class="text-gray-900 bg-white">QRIS</option>
                    <option value="rfid" class="text-gray-900 bg-white">RFID</option>
                    <option value="card" class="text-gray-900 bg-white">Card</option>
                    <option value="transfer" class="text-gray-900 bg-white">Transfer</option>
                </select>
            </div>
        </div>        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button wire:click="generateReport" 
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-chart-line mr-2"></i>Generate Report
            </button>
            <button wire:click="exportReport" 
                    class="px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-file-export mr-2"></i>Export (PDF/Excel)
            </button>
            <button wire:click="generateReport" 
                    class="px-6 py-3 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-2 border-gray-300 dark:border-gray-600 hover:border-indigo-500 dark:hover:border-indigo-500 rounded-lg font-medium transition-all">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
        </div>
    </div>

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6 shadow-sm" role="alert">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    <!-- Sales Overview Tab -->
    <div x-show="activeTab === 'sales'" x-transition>
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Sales -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-blue-600 p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-600 dark:text-blue-400 text-sm font-semibold uppercase tracking-wide">Total Sales</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                    @if($totalSales > 0)
                        <p class="text-gray-600 dark:text-gray-400 text-xs mt-2">
                            <i class="fas fa-arrow-up text-green-500 mr-1"></i>Active period
                        </p>
                    @endif
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 rounded-full p-4">
                    <i class="fas fa-money-bill-wave text-blue-600 dark:text-blue-400 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Transactions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-green-600 p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-600 dark:text-green-400 text-sm font-semibold uppercase tracking-wide">Transactions</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($totalTransactions) }}</h3>
                    @if($totalTransactions > 0)
                        <p class="text-gray-600 dark:text-gray-400 text-xs mt-2">
                            <i class="fas fa-check-circle text-green-500 mr-1"></i>Completed orders
                        </p>
                    @endif
                </div>
                <div class="bg-green-100 dark:bg-green-900 rounded-full p-4">
                    <i class="fas fa-receipt text-green-600 dark:text-green-400 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Average Transaction -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-purple-600 p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-600 dark:text-purple-400 text-sm font-semibold uppercase tracking-wide">Average Sale</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">Rp {{ number_format($averageTransaction, 0, ',', '.') }}</h3>
                    @if($averageTransaction > 0)
                        <p class="text-gray-600 dark:text-gray-400 text-xs mt-2">
                            <i class="fas fa-balance-scale text-purple-500 mr-1"></i>Per transaction
                        </p>
                    @endif
                </div>
                <div class="bg-purple-100 dark:bg-purple-900 rounded-full p-4">
                    <i class="fas fa-calculator text-purple-600 dark:text-purple-400 text-3xl"></i>
                </div>
            </div>
        </div>

        <!-- Total Profit -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-l-4 border-orange-600 p-6 transform hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-600 dark:text-orange-400 text-sm font-semibold uppercase tracking-wide">Profit</p>
                    <h3 class="text-3xl font-bold text-gray-900 dark:text-white mt-2">Rp {{ number_format($totalProfit, 0, ',', '.') }}</h3>
                    @if($totalProfit > 0)
                        <p class="text-gray-600 dark:text-gray-400 text-xs mt-2">
                            <i class="fas fa-trending-up text-green-500 mr-1"></i>Net earnings
                        </p>
                    @endif
                </div>
                <div class="bg-orange-100 dark:bg-orange-900 rounded-full p-4">
                    <i class="fas fa-chart-line text-orange-600 dark:text-orange-400 text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Top Products -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-trophy text-yellow-500 mr-2"></i>Top Selling Products
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-3 py-1 rounded-full">
                    Top 10
                </span>
            </div>
            @if(count($topProducts) > 0)
                <div class="space-y-2">
                    @foreach($topProducts as $index => $item)
                        <div class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-lg hover:shadow-md transition-shadow">
                            <div class="flex items-center gap-3">
                                <span class="flex items-center justify-center w-10 h-10 rounded-full font-bold text-lg
                                    {{ $index === 0 ? 'bg-yellow-100 text-yellow-600 ring-2 ring-yellow-400' : '' }}
                                    {{ $index === 1 ? 'bg-gray-200 text-gray-600 ring-2 ring-gray-400' : '' }}
                                    {{ $index === 2 ? 'bg-orange-100 text-orange-600 ring-2 ring-orange-400' : '' }}
                                    {{ $index > 2 ? 'bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300' : '' }}">
                                    @if($index === 0)
                                        <i class="fas fa-crown"></i>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                                <div>
                                    <p class="font-semibold text-gray-800 dark:text-white">{{ $item->product->name ?? 'Unknown' }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        <i class="fas fa-box mr-1"></i>{{ $item->total_quantity }} items sold
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-green-600 dark:text-green-400 font-bold text-lg">
                                    Rp {{ number_format($item->total_sales, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                        <i class="fas fa-inbox text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No sales data available</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try adjusting your date range or filters</p>
                </div>
            @endif
        </div>

        <!-- Sales by Payment Method -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-credit-card text-blue-500 mr-2"></i>Payment Methods
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">
                    Analysis
                </span>
            </div>
            @if(count($salesByPaymentMethod) > 0)
                <div class="space-y-2">
                    @foreach($salesByPaymentMethod as $payment)
                        @php
                            $percentage = $totalSales > 0 ? ($payment->total / $totalSales) * 100 : 0;
                        @endphp
                        <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-700 dark:to-gray-600 rounded-lg hover:shadow-md transition-shadow">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center
                                        {{ $payment->payment_method === 'cash' ? 'bg-green-100 dark:bg-green-900' : '' }}
                                        {{ $payment->payment_method === 'qris' ? 'bg-blue-100 dark:bg-blue-900' : '' }}
                                        {{ $payment->payment_method === 'rfid' ? 'bg-purple-100 dark:bg-purple-900' : '' }}
                                        {{ !in_array($payment->payment_method, ['cash', 'qris', 'rfid']) ? 'bg-orange-100 dark:bg-orange-900' : '' }}">
                                        @if($payment->payment_method === 'cash')
                                            <i class="fas fa-money-bill-wave text-green-600 dark:text-green-300 text-lg"></i>
                                        @elseif($payment->payment_method === 'qris')
                                            <i class="fas fa-qrcode text-blue-600 dark:text-blue-300 text-lg"></i>
                                        @elseif($payment->payment_method === 'rfid')
                                            <i class="fas fa-credit-card text-purple-600 dark:text-purple-300 text-lg"></i>
                                        @else
                                            <i class="fas fa-wallet text-orange-600 dark:text-orange-300 text-lg"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800 dark:text-white capitalize">{{ $payment->payment_method }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            <i class="fas fa-receipt mr-1"></i>{{ $payment->count }} transactions
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-green-600 dark:text-green-400 font-bold text-lg">
                                        Rp {{ number_format($payment->total, 0, ',', '.') }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ number_format($percentage, 1) }}% of total
                                    </p>
                                </div>
                            </div>
                            <!-- Progress bar -->
                            <div class="w-full bg-gray-200 dark:bg-gray-800 rounded-full h-2 mt-2">
                                <div class="h-2 rounded-full transition-all duration-500
                                    {{ $payment->payment_method === 'cash' ? 'bg-green-500' : '' }}
                                    {{ $payment->payment_method === 'qris' ? 'bg-blue-500' : '' }}
                                    {{ $payment->payment_method === 'rfid' ? 'bg-purple-500' : '' }}
                                    {{ !in_array($payment->payment_method, ['cash', 'qris', 'rfid']) ? 'bg-orange-500' : '' }}"
                                    style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                        <i class="fas fa-credit-card text-3xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 dark:text-gray-400 font-medium">No payment data available</p>
                    <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Transactions will appear here once available</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Sales by Hour (Daily Report Only) -->
    @if($reportType === 'daily' && count($salesByHour) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-clock text-indigo-500 mr-2"></i>Sales by Hour
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full">
                    Hourly Breakdown
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
                @foreach($salesByHour as $hourData)
                    @php
                        $maxHourSales = $salesByHour->max('total');
                        $heightPercentage = $maxHourSales > 0 ? ($hourData->total / $maxHourSales) * 100 : 0;
                    @endphp
                    <div class="relative group">
                        <div class="p-4 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-700 dark:to-gray-600 rounded-lg text-center hover:shadow-lg transition-all transform hover:-translate-y-1">
                            <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-300">{{ str_pad($hourData->hour, 2, '0', STR_PAD_LEFT) }}:00</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1 font-medium">
                                <i class="fas fa-shopping-bag mr-1"></i>{{ $hourData->count }} sales
                            </p>
                            <p class="text-xs text-green-600 dark:text-green-400 font-bold mt-2">
                                Rp {{ number_format($hourData->total / 1000, 0) }}K
                            </p>
                            <!-- Visual bar indicator -->
                            <div class="mt-3 w-full bg-gray-200 dark:bg-gray-800 rounded-full h-1.5">
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-1.5 rounded-full transition-all duration-500" 
                                     style="width: {{ $heightPercentage }}%"></div>
                            </div>
                        </div>
                        <!-- Tooltip on hover -->
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                            {{ $hourData->count }} transactions<br>
                            Rp {{ number_format($hourData->total, 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

        <!-- Empty State for No Data -->
        @if($totalTransactions === 0 && ($dateFrom || $dateTo))
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-indigo-100 to-purple-100 dark:from-indigo-900 dark:to-purple-900 rounded-full mb-4">
                    <i class="fas fa-chart-line text-4xl text-indigo-600 dark:text-indigo-300"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">No Sales Data Found</h3>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    There are no transactions for the selected period.<br>
                    Try adjusting your date range or filters to see results.
                </p>
                <div class="flex items-center justify-center gap-4">
                    <button wire:click="$set('reportType', 'monthly')" 
                            class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-calendar-alt mr-2"></i>View This Month
                    </button>
                    <button wire:click="$set('dateFrom', ''); $set('dateTo', '')" 
                            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-lg font-medium transition-colors">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </button>
                </div>
            </div>
        @endif
    </div>
    
    <!-- Analytics & Insights Tab -->
    <div x-show="activeTab === 'analytics'" x-transition>
        <!-- Growth Comparison Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Sales Growth Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        <i class="fas fa-chart-line text-green-500 mr-2"></i>Sales Growth
                    </h3>
                    <select wire:model.live="comparisonPeriod" class="text-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700" style="color: #1f2937 !important;">
                        <option value="previous" class="text-gray-900 bg-white">vs Previous Period</option>
                        <option value="last_month" class="text-gray-900 bg-white">vs Last Month</option>
                        <option value="last_year" class="text-gray-900 bg-white">vs Last Year</option>
                    </select>
                </div>
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Current Period</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($growthData['current_sales'] ?? 0, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                @if(isset($growthData['sales_growth']))
                                    @if($growthData['sales_growth'] >= 0)
                                        <div class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                            <i class="fas fa-arrow-up"></i>
                                            <span class="text-xl font-bold">{{ number_format(abs($growthData['sales_growth']), 1) }}%</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1 text-red-600 dark:text-red-400">
                                            <i class="fas fa-arrow-down"></i>
                                            <span class="text-xl font-bold">{{ number_format(abs($growthData['sales_growth']), 1) }}%</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-green-200 dark:border-green-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $growthData['compare_period_label'] ?? 'Previous' }}: Rp {{ number_format($growthData['compare_sales'] ?? 0, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Total Transactions</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($growthData['current_transactions'] ?? 0) }}</p>
                            </div>
                            <div class="text-right">
                                @if(isset($growthData['transactions_growth']))
                                    @if($growthData['transactions_growth'] >= 0)
                                        <div class="flex items-center gap-1 text-green-600 dark:text-green-400">
                                            <i class="fas fa-arrow-up"></i>
                                            <span class="text-xl font-bold">{{ number_format(abs($growthData['transactions_growth']), 1) }}%</span>
                                        </div>
                                    @else
                                        <div class="flex items-center gap-1 text-red-600 dark:text-red-400">
                                            <i class="fas fa-arrow-down"></i>
                                            <span class="text-xl font-bold">{{ number_format(abs($growthData['transactions_growth']), 1) }}%</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-t border-blue-200 dark:border-blue-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $growthData['compare_period_label'] ?? 'Previous' }}: {{ number_format($growthData['compare_transactions'] ?? 0) }} transactions</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Peak Hours Card -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                        <i class="fas fa-clock text-orange-500 mr-2"></i>Peak Hours
                    </h3>
                    <span class="text-sm text-gray-500 dark:text-gray-400 bg-orange-50 dark:bg-orange-900/30 px-3 py-1 rounded-full">
                        Top 5
                    </span>
                </div>
                @if(count($peakHours) > 0)
                    <div class="space-y-3">
                        @foreach($peakHours as $index => $peak)
                            <div class="flex items-center justify-between p-3 bg-gradient-to-r from-orange-50 to-amber-50 dark:from-orange-900/20 dark:to-amber-900/20 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900 flex items-center justify-center">
                                        <span class="font-bold text-orange-600 dark:text-orange-300">{{ $index + 1 }}</span>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white">{{ $peak['hour'] }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $peak['count'] }} transactions</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-green-600 dark:text-green-400">Rp {{ number_format($peak['sales'] / 1000, 0) }}K</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-clock text-4xl text-gray-300 dark:text-gray-600 mb-2"></i>
                        <p class="text-gray-500 dark:text-gray-400">No peak hours data</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Sales Trend Chart -->
        @if(count($salesTrend) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-chart-area text-indigo-500 mr-2"></i>Sales Trend
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-1 rounded-full">
                    {{ count($salesTrend) }} days
                </span>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                @php
                    $maxSales = collect($salesTrend)->max('sales');
                @endphp
                @foreach($salesTrend as $day)
                    @php
                        $heightPercentage = $maxSales > 0 ? ($day['sales'] / $maxSales) * 100 : 0;
                    @endphp
                    <div class="relative group">
                        <div class="bg-gradient-to-t from-indigo-100 to-indigo-50 dark:from-indigo-900 dark:to-indigo-800 rounded-lg p-3 text-center hover:shadow-lg transition-all">
                            <div class="flex flex-col items-center justify-end" style="height: 120px;">
                                <div class="w-full bg-gradient-to-t from-indigo-600 to-purple-600 rounded-t transition-all duration-500" 
                                     style="height: {{ $heightPercentage }}%"></div>
                            </div>
                            <p class="text-xs font-bold text-gray-900 dark:text-white mt-2">{{ $day['label'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $day['count'] }} sales</p>
                            <p class="text-xs text-green-600 dark:text-green-400 font-bold">{{ number_format($day['sales'] / 1000, 0) }}K</p>
                        </div>
                        <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap pointer-events-none z-10">
                            Rp {{ number_format($day['sales'], 0, ',', '.') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Category Performance -->
        @if(count($categoryPerformance) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-layer-group text-purple-500 mr-2"></i>Category Performance
                </h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($categoryPerformance as $category)
                    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-lg p-4 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $category['category'] }}</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $category['transaction_count'] }} transactions</p>
                            </div>
                            <div class="w-12 h-12 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                                <i class="fas fa-tags text-purple-600 dark:text-purple-300"></i>
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total Sales</span>
                                <span class="font-bold text-green-600 dark:text-green-400">Rp {{ number_format($category['sales'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Items Sold</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ $category['quantity'] }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Avg Value</span>
                                <span class="font-semibold text-indigo-600 dark:text-indigo-400">Rp {{ number_format($category['avg_transaction_value'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Product Performance Table -->
        @if(count($productPerformance) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-800 dark:text-white">
                    <i class="fas fa-box-open text-blue-500 mr-2"></i>Product Performance Analysis
                </h3>
                <span class="text-sm text-gray-500 dark:text-gray-400 bg-blue-50 dark:bg-blue-900/30 px-3 py-1 rounded-full">
                    {{ count($productPerformance) }} products
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase">Product</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase">Category</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase">Qty Sold</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase">Sales</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase">Profit</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase">Margin %</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-800 dark:text-gray-200 uppercase">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($productPerformance as $product)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $product['product_name'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $product['transaction_count'] }} transactions</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-medium bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 rounded-full">
                                        {{ $product['category'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ $product['quantity'] }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-green-600 dark:text-green-400">
                                    Rp {{ number_format($product['sales'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    <span class="{{ $product['profit'] >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                        Rp {{ number_format($product['profit'], 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="px-2 py-1 text-xs font-bold rounded
                                        {{ $product['profit_margin'] >= 30 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $product['profit_margin'] >= 15 && $product['profit_margin'] < 30 ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                                        {{ $product['profit_margin'] >= 0 && $product['profit_margin'] < 15 ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' : '' }}
                                        {{ $product['profit_margin'] < 0 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}">
                                        {{ number_format($product['profit_margin'], 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if($product['current_stock'] == 0)
                                        <span class="px-2 py-1 text-xs font-bold bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 rounded">
                                            Out of Stock
                                        </span>
                                    @elseif($product['current_stock'] < 10)
                                        <span class="px-2 py-1 text-xs font-bold bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 rounded">
                                            {{ $product['current_stock'] }} (Low)
                                        </span>
                                    @else
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $product['current_stock'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
