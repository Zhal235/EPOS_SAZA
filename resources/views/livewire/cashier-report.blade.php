<div>
    <!-- Header Filters -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                <select wire:model.live="reportType"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white text-gray-900 text-sm">
                    <option value="daily">Hari Ini</option>
                    <option value="weekly">Minggu Ini</option>
                    <option value="monthly">Bulan Ini</option>
                    <option value="custom">Kustom</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" wire:model="dateFrom"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white text-gray-900 text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model="dateTo"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white text-gray-900 text-sm">
            </div>

            <!-- Outlet Mode -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Outlet</label>
                <select wire:model="outletMode"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white text-gray-900 text-sm">
                    <option value="">Semua Outlet</option>
                    <option value="store">Toko</option>
                    <option value="foodcourt">Foodcourt</option>
                </select>
            </div>

            <!-- Cashier Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kasir</label>
                <select wire:model="selectedCashier"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 bg-white text-gray-900 text-sm">
                    <option value="">Semua Kasir</option>
                    @foreach($cashiers as $c)
                        <option value="{{ $c['id'] }}">{{ $c['name'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Generate Button -->
        <div class="mt-4 flex justify-end">
            <button wire:click="generate"
                    class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition-colors shadow-sm">
                <i class="fas fa-sync-alt mr-2"></i>Tampilkan Laporan
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Penjualan</p>
            <h3 class="text-2xl font-bold text-indigo-700 mt-1">
                Rp {{ number_format($grandTotal, 0, ',', '.') }}
            </h3>
        </div>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase font-medium">Total Transaksi</p>
            <h3 class="text-2xl font-bold text-green-700 mt-1">{{ number_format($grandTransactions, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-5">
            <p class="text-xs text-gray-500 uppercase font-medium">Rata-rata / Transaksi</p>
            <h3 class="text-2xl font-bold text-purple-700 mt-1">
                Rp {{ $grandTransactions > 0 ? number_format($grandTotal / $grandTransactions, 0, ',', '.') : '0' }}
            </h3>
        </div>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-purple-50">
            <h2 class="text-base font-semibold text-gray-800">
                <i class="fas fa-user-clock text-indigo-600 mr-2"></i>
                Detail Penjualan Per Kasir
                <span class="text-sm font-normal text-gray-500 ml-2">
                    ({{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} – {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }})
                </span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gradient-to-r from-indigo-600 to-purple-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Kasir</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Outlet</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider">Jml Transaksi</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider">Total Diskon</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-white uppercase tracking-wider">Total Penjualan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">Transaksi Terakhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($reportRows as $row)
                        @php
                            $cashierName = $row['user']['name'] ?? ('User #' . $row['user_id']);
                            $cashierRole = $row['user']['role'] ?? '-';
                            $roleLabel = match($cashierRole) {
                                'admin'              => 'Administrator',
                                'manager'            => 'Manager',
                                'cashier'            => 'Kasir Umum',
                                'cashier_store'      => 'Kasir Toko',
                                'cashier_foodcourt'  => 'Kasir Foodcourt',
                                default              => ucfirst($cashierRole),
                            };
                            $roleColor = match($cashierRole) {
                                'admin'              => 'bg-red-100 text-red-700',
                                'manager'            => 'bg-blue-100 text-blue-700',
                                'cashier'            => 'bg-green-100 text-green-700',
                                'cashier_store'      => 'bg-purple-100 text-purple-700',
                                'cashier_foodcourt'  => 'bg-yellow-100 text-yellow-700',
                                default              => 'bg-gray-100 text-gray-700',
                            };
                            $outletLabel = $row['outlet_mode'] === 'foodcourt' ? 'Foodcourt' : 'Toko';
                            $outletColor = $row['outlet_mode'] === 'foodcourt'
                                ? 'bg-amber-100 text-amber-700'
                                : 'bg-sky-100 text-sky-700';
                        @endphp
                        <tr class="hover:bg-indigo-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $cashierName }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $roleColor }}">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $outletColor }}">
                                    @if($row['outlet_mode'] === 'foodcourt')
                                        <i class="fas fa-utensils mr-1"></i>
                                    @else
                                        <i class="fas fa-shopping-bag mr-1"></i>
                                    @endif
                                    {{ $outletLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800">
                                {{ number_format($row['transaction_count'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right text-red-600">
                                Rp {{ number_format($row['total_discount'] ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-indigo-700">
                                Rp {{ number_format($row['total_sales'], 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-xs">
                                {{ $row['last_transaction_at'] ? \Carbon\Carbon::parse($row['last_transaction_at'])->format('d M Y H:i') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-50 rounded-full mb-3">
                                    <i class="fas fa-user-clock text-2xl text-indigo-400"></i>
                                </div>
                                <p class="font-medium text-gray-700">Tidak ada data transaksi</p>
                                <p class="text-xs mt-1">Coba ubah filter periode atau outlet</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($reportRows) > 0)
                <tfoot class="bg-gray-50 border-t-2 border-gray-300">
                    <tr>
                        <td colspan="3" class="px-6 py-3 font-bold text-gray-800 text-sm">TOTAL</td>
                        <td class="px-6 py-3 text-right font-bold text-gray-800">
                            {{ number_format($grandTransactions, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-right font-bold text-red-600">
                            Rp {{ number_format(collect($reportRows)->sum('total_discount'), 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-right font-bold text-indigo-700">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
