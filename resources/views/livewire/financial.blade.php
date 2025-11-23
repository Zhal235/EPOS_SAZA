<div>
    <!-- Header dengan Ringkasan Keuangan -->
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total Pemasukan -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Pemasukan</p>
                        <p class="text-2xl font-bold text-green-600">{{ 'Rp ' . number_format($summary['total_income'], 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-arrow-up"></i> {{ $summary['total_transactions'] }} transaksi
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-plus text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Pengeluaran -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Total Pengeluaran</p>
                        <p class="text-2xl font-bold text-red-600">{{ 'Rp ' . number_format($summary['total_expense'], 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-arrow-down"></i> Termasuk refund
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-minus text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Saldo RFID Tersedia -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Saldo RFID Tersedia</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $summary['pending_withdrawal_formatted'] }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-wallet"></i> Siap ditarik
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-credit-card text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <!-- Total Sudah Ditarik -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600 mb-1">Sudah Ditarik</p>
                        <p class="text-2xl font-bold text-purple-600">{{ 'Rp ' . number_format($summary['withdrawn_amount'], 0, ',', '.') }}</p>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="fas fa-check-circle"></i> Dari SIMPels
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-download text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter dan Tab Navigation -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <!-- Tab Navigation -->
        <div class="flex flex-wrap items-center justify-between mb-6">
            <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
                <button wire:click="setTab('overview')" 
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'overview' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-chart-pie mr-2"></i>Ringkasan
                </button>
                <button wire:click="setTab('transactions')" 
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'transactions' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-list mr-2"></i>Transaksi
                </button>
                <button wire:click="setTab('withdrawals')" 
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'withdrawals' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-hand-holding-usd mr-2"></i>Penarikan RFID
                </button>
                <button wire:click="setTab('expenses')" 
                        class="px-4 py-2 rounded-md text-sm font-medium transition-colors {{ $activeTab === 'expenses' ? 'bg-white text-indigo-600 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    <i class="fas fa-shopping-cart mr-2"></i>Pengeluaran
                </button>
            </div>

            @if($activeTab === 'withdrawals')
            @if($this->hasPendingWithdrawals)
                <div class="flex items-center gap-3">
                    <button disabled
                            class="px-4 py-2 bg-gray-300 text-gray-500 rounded-lg cursor-not-allowed opacity-60">
                        <i class="fas fa-lock mr-2"></i>Tarik Saldo RFID
                    </button>
                    <div class="flex items-center text-sm text-orange-600 bg-orange-50 px-3 py-2 rounded-lg border border-orange-200">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <span>Ada {{ $this->pendingWithdrawalsCount }} penarikan menunggu approval SIMPELS</span>
                    </div>
                </div>
            @else
                <button wire:click="openWithdrawalModal" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Tarik Saldo RFID
                </button>
            @endif
            @endif
        </div>

        <!-- Filter Controls -->
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" wire:model="dateFrom" 
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model="dateTo" 
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            @if($activeTab === 'transactions')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                <select wire:model="filterType" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="all">Semua Jenis</option>
                    <option value="rfid_payment">Pembayaran RFID</option>
                    <option value="refund">Pengembalian</option>
                    <option value="cash_in">Kas Masuk</option>
                    <option value="cash_out">Kas Keluar</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select wire:model="filterStatus" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500">
                    <option value="all">Semua Status</option>
                    <option value="completed">Selesai</option>
                    <option value="pending">Pending</option>
                    <option value="failed">Gagal</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
                <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Cari transaksi..." 
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            @endif
            <div class="flex gap-2">
                <button wire:click="applyDateFilter" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filter
                </button>
                <button wire:click="resetFilters" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-undo mr-2"></i>Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Tab Content -->
    @if($activeTab === 'overview')
        <!-- Grafik dan Ringkasan -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grafik Keuangan -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Grafik Keuangan</h3>
                    <button wire:click="exportTransactions" 
                            class="px-3 py-2 text-sm text-indigo-600 border border-indigo-600 rounded-lg hover:bg-indigo-50">
                        <i class="fas fa-download mr-2"></i>Export
                    </button>
                </div>
                
                <!-- Chart Placeholder -->
                <div class="h-64 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-chart-area text-4xl text-indigo-400 mb-4"></i>
                        <p class="text-gray-600">Grafik Pemasukan vs Pengeluaran</p>
                        <p class="text-sm text-gray-500">Akan ditampilkan dengan Chart.js</p>
                    </div>
                </div>
            </div>

            <!-- Status RFID -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status Saldo RFID</h3>
                
                <div class="space-y-4">
                    <div class="p-4 bg-blue-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-blue-900">Total Pembayaran RFID</span>
                        </div>
                        <p class="text-2xl font-bold text-blue-600">{{ 'Rp ' . number_format($summary['total_rfid_payments'], 0, ',', '.') }}</p>
                    </div>
                    
                    <div class="p-4 bg-green-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-green-900">Sisa Belum Ditarik</span>
                        </div>
                        <p class="text-2xl font-bold text-green-600">{{ $summary['pending_withdrawal_formatted'] }}</p>
                        
                        @if($summary['pending_withdrawal'] > 0)
                        <button wire:click="openWithdrawalModal" 
                                class="mt-3 w-full px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                            <i class="fas fa-download mr-2"></i>Tarik Sekarang
                        </button>
                        @endif
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-900">Sudah Ditarik</span>
                        </div>
                        <p class="text-xl font-bold text-gray-600">{{ 'Rp ' . number_format($summary['withdrawn_amount'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'transactions')
        <!-- Daftar Transaksi -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Transaksi Keuangan</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Santri/Keterangan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">RFID Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $transaction->transaction_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    {{ $transaction->category === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $transaction->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                @if($transaction->santri_name)
                                    <div>{{ $transaction->santri_name }}</div>
                                    @if($transaction->rfid_tag)
                                    <div class="text-xs text-gray-500">RFID: {{ $transaction->rfid_tag }}</div>
                                    @endif
                                @else
                                    {{ $transaction->description }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium 
                                {{ $transaction->category === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->category === 'income' ? '+' : '-' }}{{ $transaction->formatted_amount }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $transaction->status_color }}-100 text-{{ $transaction->status_color }}-800">
                                    {{ $transaction->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                @if($transaction->type === 'rfid_payment')
                                    @if($transaction->withdrawn_from_simpels)
                                        <span class="text-green-600"><i class="fas fa-check-circle"></i> Ditarik</span>
                                    @else
                                        <span class="text-yellow-600"><i class="fas fa-clock"></i> Belum</span>
                                    @endif
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-receipt text-4xl mb-4"></i>
                                <p>Tidak ada transaksi ditemukan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $transactions->links() }}
            </div>
        </div>

    @elseif($activeTab === 'withdrawals')
        <!-- Daftar Penarikan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Riwayat Penarikan Saldo RFID</h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Penarikan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Metode</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status EPOS</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status SIMPels</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($withdrawals as $withdrawal)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $withdrawal->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $withdrawal->withdrawal_number }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">
                                Rp {{ number_format($withdrawal->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-900">
                                @if($withdrawal->withdrawal_method === 'bank_transfer')
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-university mr-1"></i>
                                        Bank Transfer
                                    </span>
                                @else
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-money-bill-wave mr-1"></i>
                                        Cash
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($withdrawal->simpels_status === 'approved' || $withdrawal->simpels_status === 'completed')
                                    {{-- Jika sudah approved/completed di SIMPELS, tampilkan Sukses --}}
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Sukses
                                    </span>
                                @elseif($withdrawal->status === 'cancelled')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Dibatalkan
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Menunggu
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($withdrawal->simpels_status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        {{ $withdrawal->simpels_status_label }}
                                    </span>
                                @elseif($withdrawal->simpels_status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $withdrawal->simpels_status_label }}
                                    </span>
                                @elseif($withdrawal->simpels_status === 'completed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $withdrawal->simpels_status_label }}
                                    </span>
                                @elseif($withdrawal->simpels_status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        {{ $withdrawal->simpels_status_label }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $withdrawal->simpels_status_label }}
                                    </span>
                                @endif
                                @if($withdrawal->simpels_updated_at)
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $withdrawal->simpels_updated_at->format('d/m H:i') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex justify-center space-x-2">
                                    @if(in_array($withdrawal->simpels_status, ['approved', 'completed']))
                                        {{-- Jika sudah approved/completed di SIMPELS, tidak bisa dibatalkan --}}
                                        <span class="text-xs text-gray-400">-</span>
                                    @elseif($withdrawal->status === 'cancelled')
                                        {{-- Jika sudah dibatalkan --}}
                                        <span class="text-xs text-gray-400">-</span>
                                    @else
                                        {{-- Tampilkan tombol batalkan hanya jika masih pending --}}
                                        <button wire:click="cancelWithdrawal({{ $withdrawal->id }})" 
                                                wire:confirm="Yakin ingin membatalkan penarikan ini? Penarikan akan dibatalkan di SIMPELS juga."
                                                class="px-3 py-1 text-xs bg-red-50 text-red-600 hover:bg-red-100 rounded border border-red-200 transition"
                                                title="Batalkan Penarikan">
                                            <i class="fas fa-times mr-1"></i> Batalkan
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <i class="fas fa-hand-holding-usd text-4xl mb-4"></i>
                                <p>Belum ada riwayat penarikan</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $withdrawals->links() }}
            </div>
        </div>

    @elseif($activeTab === 'expenses')
        <!-- Tab Pengeluaran untuk Stok -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Pengeluaran & Pembelian Stok</h3>
                <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Catat Pengeluaran
                </button>
            </div>
            
            <div class="text-center py-12 text-gray-500">
                <i class="fas fa-shopping-cart text-4xl mb-4"></i>
                <p class="text-lg mb-2">Fitur Pengeluaran</p>
                <p class="text-sm">Akan segera tersedia untuk mencatat pembelian stok dan pengeluaran lainnya</p>
            </div>
        </div>
    @endif

    <!-- Modal Penarikan Saldo RFID -->
    @if($showWithdrawalModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="closeWithdrawalModal">
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Tarik Saldo RFID</h3>
                <button type="button" wire:click="closeWithdrawalModal" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div>
                <!-- Alert untuk validation errors -->
                @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Ada kesalahan pada form:</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <div class="space-y-4">
                    <!-- Info Jumlah yang Akan Ditarik -->
                    <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-600 mb-1">Jumlah yang Akan Ditarik</p>
                            <p class="text-3xl font-bold text-blue-600">{{ $summary['pending_withdrawal_formatted'] ?? 'Rp 0' }}</p>
                            <p class="text-xs text-blue-600 mt-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Semua saldo tersedia akan ditarik secara otomatis
                            </p>
                        </div>
                    </div>

                    <!-- Metode Penarikan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Metode Penarikan <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="withdrawalMethod" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="cash">Tunai</option>
                            <option value="bank_transfer">Transfer Bank</option>
                        </select>
                        @error('withdrawalMethod') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Detail Bank (jika transfer) -->
                    @if($withdrawalMethod === 'bank_transfer')
                    <div class="space-y-3 p-4 bg-gray-50 rounded-lg">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bank</label>
                            <input type="text" wire:model="bankName" placeholder="Contoh: BCA, Mandiri, BRI"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            @error('bankName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening</label>
                            <input type="text" wire:model="accountNumber" placeholder="1234567890"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            @error('accountNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemegang Rekening</label>
                            <input type="text" wire:model="accountName" placeholder="Nama sesuai rekening"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            @error('accountName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    @endif

                    <!-- Catatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                        <textarea wire:model="withdrawalNotes" rows="3" 
                                  placeholder="Catatan opsional..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="button" wire:click="closeWithdrawalModal" 
                            class="flex-1 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="button"
                            wire:click="createWithdrawal"
                            wire:loading.attr="disabled"
                            wire:target="createWithdrawal"
                            class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <span wire:loading.remove wire:target="createWithdrawal">
                            <i class="fas fa-hand-holding-usd mr-2"></i>Buat Penarikan
                        </span>
                        <span wire:loading wire:target="createWithdrawal">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>