<div x-data="{ showRefundModal: @entangle('showRefundModal') }">
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pengembalian Barang (Refund)</h1>
            <p class="text-sm text-gray-600 mt-1">Kelola pengembalian barang dan refund pembayaran</p>
        </div>
        <a href="{{ route('pos') }}" 
           class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition flex items-center gap-2 shadow-sm">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke POS</span>
        </a>
    </div>

    <!-- Filters -->
    <div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cari Transaksi</label>
                    <input type="text" wire:model.live.debounce.300ms="search" 
                           placeholder="No transaksi, nama customer..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                    <input type="date" wire:model.live="dateFrom" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                    <input type="date" wire:model.live="dateTo" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <!-- Transactions List -->
        <div class="space-y-4">
            @forelse($transactions as $transaction)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $transaction->transaction_number }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $transaction->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $transaction->status === 'completed' ? 'Selesai' : 'Dikembalikan' }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ $transaction->created_at->format('d M Y, H:i') }} • 
                                Kasir: {{ $transaction->user->name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900">{{ $transaction->getFormattedTotalAttribute() }}</p>
                            <p class="text-sm text-gray-600">
                                <i class="fas {{ $transaction->payment_method === 'rfid' ? 'fa-credit-card' : ($transaction->payment_method === 'cash' ? 'fa-money-bill-wave' : 'fa-qrcode') }} mr-1"></i>
                                {{ strtoupper($transaction->payment_method) }}
                            </p>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <p class="text-sm font-medium text-gray-700">Customer: <span class="font-normal">{{ $transaction->customer_name }}</span></p>
                        @if($transaction->notes)
                        <p class="text-xs text-gray-500 mt-1">{{ $transaction->notes }}</p>
                        @endif
                    </div>

                    <!-- Transaction Items -->
                    <div class="space-y-2 mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Items:</p>
                        @foreach($transaction->items as $item)
                        <div class="flex items-center justify-between text-sm py-2 px-3 bg-gray-50 rounded">
                            <div class="flex-1">
                                <span class="font-medium text-gray-900">{{ $item->product_name }}</span>
                                <span class="text-gray-500 ml-2">x{{ $item->quantity }}</span>
                            </div>
                            <span class="font-semibold text-gray-900">
                                Rp {{ number_format($item->total_price, 0, ',', '.') }}
                            </span>
                        </div>
                        @endforeach
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end">
                        @if($transaction->status === 'completed')
                        <button wire:click="openRefundModal({{ $transaction->id }})"
                                class="px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 text-white rounded-lg hover:shadow-lg transition flex items-center gap-2 font-semibold">
                            <i class="fas fa-undo"></i>
                            <span>Proses Pengembalian</span>
                        </button>
                        @else
                        <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg text-sm font-medium">
                            <i class="fas fa-check-circle mr-2"></i>
                            Sudah Dikembalikan
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <i class="fas fa-search text-gray-400 text-5xl mb-4"></i>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Tidak Ada Transaksi</h3>
                <p class="text-gray-600">Tidak ada transaksi yang ditemukan dengan filter yang dipilih</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    <!-- Refund Modal -->
    <div x-show="showRefundModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="showRefundModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 aria-hidden="true"
                 @click="showRefundModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div x-show="showRefundModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                
                @if($selectedTransaction)
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-undo text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Proses Pengembalian
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Transaksi: {{ $selectedTransaction->transaction_number }}
                            </p>
                            
                            <div class="mt-4 space-y-4">
                                <!-- Items Selection -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Item yang Dikembalikan</label>
                                    <div class="space-y-2 max-h-64 overflow-y-auto">
                                        @foreach($refundItems as $itemId => $item)
                                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg {{ $item['selected'] ? 'bg-red-50 border-red-300' : 'bg-gray-50' }}">
                                            <div class="flex items-center gap-3 flex-1">
                                                <input type="checkbox" 
                                                       wire:click="toggleRefundItem({{ $itemId }})"
                                                       @if($item['selected']) checked @endif
                                                       class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 rounded">
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900">{{ $item['product_name'] }}</p>
                                                    <p class="text-xs text-gray-500">Rp {{ number_format($item['unit_price'], 0, ',', '.') }} x {{ $item['quantity'] }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                @if($item['selected'])
                                                <input type="number" 
                                                       wire:model.live="refundItems.{{ $itemId }}.quantity"
                                                       min="1" 
                                                       max="{{ $item['max_quantity'] }}"
                                                       class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-red-500">
                                                @endif
                                                <p class="text-sm font-semibold text-gray-900 mt-1">
                                                    Rp {{ number_format($item['quantity'] * $item['unit_price'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Refund Total -->
                                <div class="bg-indigo-50 p-4 rounded-lg">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700">Total Pengembalian:</span>
                                        <span class="text-2xl font-bold text-indigo-600">
                                            Rp {{ number_format($this->refund_total, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- RFID Refund Option -->
                                @if($selectedTransaction->payment_method === 'rfid')
                                <div class="flex items-center gap-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                    <input type="checkbox" 
                                           wire:model="refundToRfid"
                                           checked
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <div>
                                        <p class="text-sm font-medium text-blue-900">Kembalikan ke Saldo RFID</p>
                                        <p class="text-xs text-blue-700">Dana akan dikembalikan ke saldo dompet digital santri</p>
                                    </div>
                                </div>
                                @endif

                                <!-- Reason -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan Pengembalian *</label>
                                    <textarea wire:model="refundReason" rows="3"
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"
                                              placeholder="Jelaskan alasan pengembalian barang (minimal 10 karakter)"></textarea>
                                    @error('refundReason') 
                                    <span class="text-red-500 text-xs mt-1">{{ $message }}</span> 
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="processRefund"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-lg px-4 py-2 bg-gradient-to-r from-red-600 to-rose-600 text-base font-semibold text-white hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        <i class="fas fa-check mr-2"></i>
                        Proses Pengembalian
                    </button>
                    <button type="button" wire:click="closeRefundModal"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
