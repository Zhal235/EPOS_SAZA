<div x-data="{ showWithdrawalModal: @entangle('showWithdrawalModal') }">
    <!-- Page Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Financial Management</h1>
            <p class="text-sm text-gray-600 mt-1">Monitor keluar masuk uang, pembayaran RFID, dan penarikan dari SIMPels</p>
        </div>
        <div class="flex gap-3">
            <button wire:click="resetFilters" 
                    class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition flex items-center gap-2 shadow-sm">
                <i class="fas fa-redo text-sm"></i>
                <span>Reset Filter</span>
            </button>
            @if($activeTab === 'withdrawals')
            <button wire:click="openWithdrawalModal" 
                    class="px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:shadow-lg transition flex items-center gap-2 font-semibold shadow-sm">
                <i class="fas fa-money-bill-wave"></i>
                <span>Tarik Dana</span>
            </button>
            @endif
        </div>
    </div>

    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-2">
            <div class="flex gap-2">
                <button wire:click="setTab('overview')" 
                        class="flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'overview' ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="fas fa-chart-pie"></i>
                    <span>Overview</span>
                </button>
                <button wire:click="setTab('transactions')" 
                        class="flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'transactions' ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="fas fa-list"></i>
                    <span>Transaksi</span>
                </button>
                <button wire:click="setTab('withdrawals')" 
                        class="flex-1 px-6 py-3 rounded-lg font-semibold transition-all duration-200 flex items-center justify-center gap-2 {{ $activeTab === 'withdrawals' ? 'bg-gradient-to-r from-green-600 to-emerald-600 text-white shadow-md' : 'text-gray-600 hover:bg-gray-100' }}">
                    <i class="fas fa-hand-holding-usd"></i>
                    <span>Penarikan SIMPels</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div>
        
        <!-- Date Filter -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" wire:model="dateFrom" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                    <input type="date" wire:model="dateTo" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="pt-6">
                    <button wire:click="applyDateFilter" 
                            class="px-6 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white rounded-lg hover:shadow-lg transition font-semibold">
                        Terapkan
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab Content -->
        <div>
            @if($activeTab === 'overview')
                @include('livewire.financial.overview', ['summary' => $summary, 'chartData' => $chartData])
            @elseif($activeTab === 'transactions')
                @include('livewire.financial.transactions', ['transactions' => $transactions])
            @elseif($activeTab === 'withdrawals')
                @include('livewire.financial.withdrawals', ['withdrawals' => $withdrawals])
            @endif
        </div>
    </div>

    <!-- Withdrawal Modal -->
    <div x-show="showWithdrawalModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4">
            <!-- Background overlay - solid blur seperti SIMPels -->
            <div x-show="showWithdrawalModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black bg-opacity-40 transition-opacity" 
                 style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);"
                 aria-hidden="true"
                 @click="showWithdrawalModal = false"></div>

            <!-- Modal panel -->
            <div x-show="showWithdrawalModal"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-lg shadow-2xl transform transition-all w-full max-w-md z-50">
                
                <div class="p-5">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-indigo-100">
                            <i class="fas fa-money-bill-wave text-indigo-600"></i>
                        </div>
                        <div class="ml-3 w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">
                                Tarik Dana dari SIMPels
                            </h3>
                            <div class="space-y-3">
                                <!-- Available Balance Info -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="flex items-center">
                                        <i class="fas fa-wallet text-blue-600 text-xl mr-2"></i>
                                        <div>
                                            <p class="text-xs text-blue-700 font-medium">Saldo Tersedia</p>
                                            <p class="text-lg font-bold text-blue-900">{{ $this->getDashboardSummary()['pending_withdrawal_formatted'] ?? 'Rp 0' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nominal to Withdraw -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Penarikan</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                                        <input type="number" wire:model="withdrawalAmount" 
                                               placeholder="0"
                                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    @error('withdrawalAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk tarik semua saldo tersedia</p>
                                </div>

                                <!-- Withdrawal Method -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Penarikan</label>
                                    <select wire:model.live="withdrawalMethod" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        <option value="cash">Tunai</option>
                                        <option value="bank_transfer">Transfer Bank</option>
                                    </select>
                                </div>

                                <!-- Bank Details (if bank_transfer) -->
                                @if($withdrawalMethod === 'bank_transfer')
                                <div class="space-y-2 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <p class="text-xs font-medium text-gray-700 mb-1">Detail Rekening</p>
                                    <div>
                                        <input type="text" wire:model="bankName" 
                                               placeholder="Nama Bank (contoh: BCA)"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        @error('bankName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input type="text" wire:model="accountNumber" 
                                               placeholder="Nomor Rekening"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        @error('accountNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    <div>
                                        <input type="text" wire:model="accountName" 
                                               placeholder="Nama Pemilik Rekening"
                                               class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                        @error('accountName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @endif

                                <!-- Notes -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                                    <textarea wire:model="withdrawalNotes" rows="2"
                                              class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"
                                              placeholder="Tambahkan catatan jika diperlukan"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 flex gap-2">
                    <button type="button" wire:click="createWithdrawal"
                            class="flex-1 inline-flex justify-center items-center rounded-lg px-4 py-2 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <i class="fas fa-check mr-2"></i>
                        Buat Permintaan
                    </button>
                    <button type="button" wire:click="closeWithdrawalModal"
                            class="flex-1 inline-flex justify-center items-center rounded-lg border border-gray-300 px-4 py-2 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush
