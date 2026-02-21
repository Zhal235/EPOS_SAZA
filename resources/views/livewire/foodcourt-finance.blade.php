<div class="h-full flex flex-col">
    <!-- Header & Search -->
    <div class="bg-white p-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Keuangan Foodcourt</h2>
            <p class="text-sm text-gray-500">Kelola saldo tenant dan riwayat penarikan</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative w-full md:w-64">
                <input wire:model.live.debounce.300ms="search" type="text" 
                       placeholder="Cari tenant, pemilik, booth..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
            </div>
            <button wire:click="$refresh" class="p-2 text-gray-500 hover:text-gray-700 bg-gray-100 rounded-lg" title="Refresh">
                <i class="fas fa-sync-alt"></i>
            </button>
        </div>
    </div>

    <!-- Tenants List -->
    <div class="flex-1 overflow-auto bg-gray-50 p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @forelse($tenants as $tenant)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-bold text-lg text-gray-900">{{ $tenant->name }}</h3>
                                <p class="text-xs text-gray-500 font-medium">
                                    <i class="fas fa-store mr-1"></i> Booth {{ $tenant->booth_number ?: '-' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $tenant->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $tenant->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        
                        <div class="mb-4">
                            <div class="text-sm text-gray-500 mb-1">Saldo Tersedia</div>
                            <div class="text-2xl font-bold text-indigo-600">
                                Rp {{ number_format($tenant->balance, 0, ',', '.') }}
                            </div>
                        </div>
                        
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <div class="flex items-center">
                                <i class="fas fa-user w-5 text-gray-400"></i>
                                <span>{{ $tenant->owner_name ?: '-' }}</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-phone w-5 text-gray-400"></i>
                                <span>{{ $tenant->phone ?: '-' }}</span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <button wire:click="openWithdrawModal({{ $tenant->id }})" 
                                    class="w-full py-2 px-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-money-bill-wave mr-1"></i> Tarik Saldo
                            </button>
                            <button wire:click="selectTenant({{ $tenant->id }})" 
                                    class="w-full py-2 px-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-colors">
                                <i class="fas fa-history mr-1"></i> Riwayat
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full flex flex-col items-center justify-center p-12 text-gray-500">
                    <i class="fas fa-store-slash text-4xl mb-3 text-gray-300"></i>
                    <p>Tidak ada tenant ditemukan.</p>
                </div>
            @endforelse
        </div>
        
        <div class="mt-4">
            {{ $tenants->links() }}
        </div>
    </div>

    <!-- Withdrawal Modal -->
    @if($selectedTenantId && $showWithdrawModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeWithdrawModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-money-bill-wave text-green-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Penarikan Saldo Tenant
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">
                                    Proses penarikan saldo untuk <strong>{{ $selectedTenant->name }}</strong>.
                                    Saldo saat ini: <span class="font-bold text-green-600">Rp {{ number_format($selectedTenant->balance, 0, ',', '.') }}</span>
                                </p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Penarikan (Rp)</label>
                                        <input type="number" wire:model="withdrawAmount" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2">
                                        @error('withdrawAmount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Keterangan</label>
                                        <textarea wire:model="withdrawNotes" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2" placeholder="Contoh: Transfer ke BCA..."></textarea>
                                        @error('withdrawNotes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>
                                    
                                    <div class="bg-yellow-50 p-3 rounded-md border border-yellow-200 text-xs text-yellow-800">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Pastikan transfer bank sudah dilakukan sebelum mengkonfirmasi penarikan ini. Saldo tenant akan langsung dipotong.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="processWithdrawal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Konfirmasi Penarikan
                    </button>
                    <button type="button" wire:click="closeWithdrawModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- History Modal -->
    @if($selectedTenantId && $showHistoryModal && !$showWithdrawModal)
    <div class="fixed inset-0 z-40 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" wire:click="closeHistoryModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl w-full h-[80vh] flex flex-col">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 border-b border-gray-200 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Riwayat Transaksi: {{ $selectedTenant->name }}
                        </h3>
                        <p class="text-sm text-gray-500">Transaction Ledger & Withdrawals</p>
                    </div>
                    <button wire:click="closeHistoryModal" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6">
                    <h4 class="font-medium text-gray-900 mb-3">Mutasi Saldo Terakhir</h4>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($historyTransactions as $ledger)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $ledger->created_at->format('d/m/Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $ledger->description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($ledger->type == 'sale')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Penjualan
                                                </span>
                                            @elseif($ledger->type == 'withdrawal')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Penarikan
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    {{ ucfirst($ledger->type) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right {{ $ledger->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ ($ledger->amount > 0 ? '+' : '') . number_format($ledger->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                            {{ number_format($ledger->balance_after, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Belum ada riwayat transaksi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">
                    <button type="button" wire:click="closeHistoryModal" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Tutup
                    </button>
                    <button type="button" wire:click="openWithdrawModal({{ $selectedTenant->id }})" class="mr-3 w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Tarik Saldo
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
