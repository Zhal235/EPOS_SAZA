<!-- Filters -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
            <input type="text" wire:model.live.debounce.300ms="searchQuery" 
                   placeholder="No transaksi, nama santri, RFID..."
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
        </div>

        <!-- Type Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Transaksi</label>
            <select wire:model.live="filterType" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="all">Semua Tipe</option>
                <option value="rfid_payment">Pembayaran RFID</option>
                <option value="refund">Pengembalian</option>
                <option value="withdrawal_simpels">Penarikan SIMPels</option>
                <option value="cash_in">Kas Masuk</option>
                <option value="cash_out">Kas Keluar</option>
            </select>
        </div>

        <!-- Status Filter -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select wire:model.live="filterStatus" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                <option value="all">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="completed">Selesai</option>
                <option value="failed">Gagal</option>
                <option value="refunded">Dikembalikan</option>
            </select>
        </div>
    </div>
</div>

<!-- Transactions Table -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Transaksi
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tipe
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Santri/Customer
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Jumlah
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Sync/Tarik
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transactions as $transaction)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $transaction->transaction_number }}</div>
                        <div class="text-xs text-gray-500">{{ $transaction->description }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $transaction->category === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $transaction->type_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $transaction->santri_name ?? '-' }}</div>
                        @if($transaction->rfid_tag)
                        <div class="text-xs text-gray-500">
                            <i class="fas fa-credit-card mr-1"></i>{{ $transaction->rfid_tag }}
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold {{ $transaction->category === 'income' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->category === 'income' ? '+' : '-' }} {{ $transaction->formatted_amount }}
                        </div>
                        @if($transaction->previous_balance && $transaction->new_balance)
                        <div class="text-xs text-gray-500">
                            {{ number_format($transaction->previous_balance, 0, ',', '.') }} → {{ number_format($transaction->new_balance, 0, ',', '.') }}
                        </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            bg-{{ $transaction->status_color }}-100 text-{{ $transaction->status_color }}-800">
                            {{ $transaction->status_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($transaction->type === 'rfid_payment')
                            <div class="flex flex-col gap-1">
                                @if($transaction->synced_to_simpels)
                                    <span class="inline-flex items-center text-xs text-green-600">
                                        <i class="fas fa-check-circle mr-1"></i> Sync
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs text-yellow-600">
                                        <i class="fas fa-clock mr-1"></i> Belum Sync
                                    </span>
                                @endif
                                
                                @if($transaction->withdrawn_from_simpels)
                                    <span class="inline-flex items-center text-xs text-blue-600">
                                        <i class="fas fa-money-bill-wave mr-1"></i> Ditarik
                                    </span>
                                @else
                                    <span class="inline-flex items-center text-xs text-gray-500">
                                        <i class="fas fa-hourglass-half mr-1"></i> Belum
                                    </span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $transaction->created_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $transaction->created_at->format('H:i:s') }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="fas fa-inbox text-gray-400 text-4xl mb-3"></i>
                            <p class="text-gray-500">Tidak ada transaksi ditemukan</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($transactions->hasPages())
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

<!-- Export Button -->
<div class="mt-6 flex justify-end">
    <button wire:click="exportTransactions" 
            class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:shadow-lg transition flex items-center gap-2 font-semibold">
        <i class="fas fa-file-excel"></i>
        <span>Export ke Excel</span>
    </button>
</div>
