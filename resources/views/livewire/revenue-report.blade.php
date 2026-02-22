
<div class="h-full flex flex-col">
    <!-- Header & Filters -->
    <div class="bg-white p-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            @if($reportType === 'store')
                <h2 class="text-xl font-bold text-gray-800">Laporan Pendapatan Toko</h2>
                <p class="text-sm text-gray-500">Omzet penjualan produk retail</p>
            @else
                <h2 class="text-xl font-bold text-gray-800">Laporan Pendapatan Foodcourt</h2>
                <p class="text-sm text-gray-500">Omzet tenant & pendapatan komisi</p>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <!-- Type Switcher -->
            <div class="bg-gray-100 p-1 rounded-lg flex mr-2">
                <button wire:click="$set('reportType', 'store')" class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $reportType === 'store' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Toko
                </button>
                <button wire:click="$set('reportType', 'foodcourt')" class="px-4 py-2 text-sm font-medium rounded-md transition-colors {{ $reportType === 'foodcourt' ? 'bg-white shadow-sm text-indigo-600' : 'text-gray-500 hover:text-gray-700' }}">
                    Foodcourt
                </button>
            </div>

            <!-- Date Filters -->
            <div class="flex items-center bg-gray-50 border border-gray-300 rounded-lg overflow-hidden">
                <input type="date" wire:model.live="startDate" class="border-0 bg-transparent text-sm focus:ring-0 py-2 px-3">
                <span class="text-gray-400 px-1">-</span>
                <input type="date" wire:model.live="endDate" class="border-0 bg-transparent text-sm focus:ring-0 py-2 px-3">
            </div>

            <button wire:click="$refresh" class="p-2 text-gray-500 hover:text-gray-700 bg-gray-100 rounded-lg" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4">
        @if($reportType === 'store')
            <!-- Store Stats -->
            <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-6 text-white shadow-sm">
                <p class="text-indigo-100 text-sm font-medium mb-1">Total Pendapatan Toko</p>
                <h3 class="text-3xl font-bold">Rp {{ number_format($this->storeRevenue, 0, ',', '.') }}</h3>
                <p class="text-xs text-indigo-200 mt-2">Periode {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            </div>
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                 <p class="text-gray-500 text-sm font-medium mb-1">Total Transaksi</p>
                 <h3 class="text-2xl font-bold text-gray-800">
                     {{ $this->detailedData->sum('transaction_count') }}
                 </h3>
                 <p class="text-xs text-gray-400 mt-2">Transaksi retail</p>
             </div>
        @else
            <!-- Foodcourt Stats -->
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm font-medium mb-1">Total Omzet Tenant (Gross)</p>
                <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($this->foodcourtGrossSales, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-400 mt-2">Total transaksi pelanggan foodcourt</p>
            </div>
            
            <div class="bg-white rounded-xl p-6 border border-gray-200 shadow-sm">
                <p class="text-gray-500 text-sm font-medium mb-1">Hak Tenant (Payout)</p>
                <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($this->foodcourtGrossSales - $this->foodcourtRevenue, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-400 mt-2">Akan ditarik oleh tenant</p>
            </div>

            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-sm">
                <p class="text-green-100 text-sm font-medium mb-1">Pendapatan Komisi (Net)</p>
                <h3 class="text-3xl font-bold">Rp {{ number_format($this->foodcourtRevenue, 0, ',', '.') }}</h3>
                <p class="text-xs text-green-200 mt-2">Keuntungan bersih sekolah</p>
            </div>
        @endif
    </div>

    <!-- Data Table -->
    <div class="flex-1 overflow-auto bg-gray-50 p-4 pt-0">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">
                    Rincian Harian ({{ $reportType === 'store' ? 'Toko' : 'Foodcourt' }})
                </h3>
            </div>
            
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        @if($reportType === 'store')
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Item Terjual</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pendapatan (IDR)</th>
                        @else
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Transaksi</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Omzet (Gross)</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Hak Tenant</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider font-bold text-green-600">Komisi (Net)</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->detailedData as $row)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                {{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}
                            </td>
                            
                            @if($reportType === 'store')
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    {{ $row->transaction_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    {{ $row->total_items }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-indigo-600">
                                    Rp {{ number_format($row->total_revenue, 0, ',', '.') }}
                                </td>
                            @else
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                    {{ $row->transaction_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                    Rp {{ number_format($row->gross_sales, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                    Rp {{ number_format($row->tenant_share, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-green-600 bg-green-50">
                                    + Rp {{ number_format($row->net_revenue, 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $reportType === 'store' ? 4 : 5 }}" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-3xl mb-3 text-gray-300"></i>
                                    <p>Tidak ada data untuk periode ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            
            <div class="px-6 py-4 border-t border-gray-200">
               {{ $this->detailedData->links() }}
            </div>
        </div>
    </div>
</div>

