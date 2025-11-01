<!-- Withdrawals List -->
<div class="space-y-6">
    @forelse($withdrawals as $withdrawal)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        <!-- Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">{{ $withdrawal->withdrawal_number }}</h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Periode: {{ $withdrawal->period_start->format('d M Y') }} - {{ $withdrawal->period_end->format('d M Y') }}
                    </p>
                </div>
                <div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        bg-{{ $withdrawal->status_color }}-100 text-{{ $withdrawal->status_color }}-800">
                        {{ $withdrawal->status_label }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="px-6 py-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                <!-- Amount Info -->
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Transaksi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $withdrawal->total_transactions }}</p>
                    <p class="text-xs text-gray-500 mt-1">transaksi dalam periode ini</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Amount</p>
                    <p class="text-2xl font-bold text-indigo-600">{{ $withdrawal->formatted_total_amount }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Status Penarikan</p>
                    @if($withdrawal->status === 'completed')
                        <p class="text-2xl font-bold text-green-600">{{ $withdrawal->formatted_withdrawn_amount }}</p>
                        <p class="text-xs text-gray-500 mt-1">Sudah ditarik</p>
                    @else
                        <p class="text-2xl font-bold text-yellow-600">{{ $withdrawal->formatted_remaining_amount }}</p>
                        <p class="text-xs text-gray-500 mt-1">Menunggu penarikan</p>
                    @endif
                </div>
            </div>

            <!-- Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200">
                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Informasi Penarikan</p>
                    <dl class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Metode:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->withdrawal_method_label }}</dd>
                        </div>
                        @if($withdrawal->withdrawal_method === 'bank_transfer')
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Bank:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->bank_name }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">No. Rekening:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->account_number }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Atas Nama:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->account_name }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <div>
                    <p class="text-sm font-medium text-gray-700 mb-2">Status & Timeline</p>
                    <dl class="space-y-1">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Diminta oleh:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->requestedBy->name }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Tanggal Request:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->created_at->format('d M Y, H:i') }}</dd>
                        </div>
                        @if($withdrawal->approved_by)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Disetujui oleh:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->approvedBy->name }}</dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Tanggal Approval:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->approved_at->format('d M Y, H:i') }}</dd>
                        </div>
                        @endif
                        @if($withdrawal->withdrawn_at)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Tanggal Penarikan:</dt>
                            <dd class="font-medium text-gray-900">{{ $withdrawal->withdrawn_at->format('d M Y, H:i') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            @if($withdrawal->notes)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm font-medium text-gray-700 mb-1">Catatan</p>
                <p class="text-sm text-gray-600">{{ $withdrawal->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Actions - Read Only (Approval dilakukan di SIMPels) -->
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 text-sm text-gray-600">
                    <i class="fas fa-info-circle"></i>
                    <span>Approval dilakukan di sistem SIMPels</span>
                </div>
                @if($withdrawal->status === 'pending')
                    <span class="inline-flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-sm font-medium">
                        <i class="fas fa-clock mr-2"></i>
                        Menunggu Approval SIMPels
                    </span>
                @elseif($withdrawal->status === 'approved')
                    <span class="inline-flex items-center px-4 py-2 bg-blue-100 text-blue-800 rounded-lg text-sm font-medium">
                        <i class="fas fa-check mr-2"></i>
                        Disetujui - Menunggu Pembayaran
                    </span>
                @elseif($withdrawal->status === 'completed')
                    <span class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-medium">
                        <i class="fas fa-check-circle mr-2"></i>
                        Selesai
                    </span>
                @elseif($withdrawal->status === 'rejected')
                    <span class="inline-flex items-center px-4 py-2 bg-red-100 text-red-800 rounded-lg text-sm font-medium">
                        <i class="fas fa-times-circle mr-2"></i>
                        Ditolak
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded-lg text-sm font-medium">
                        <i class="fas fa-ban mr-2"></i>
                        {{ $withdrawal->status_label }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
        <i class="fas fa-folder-open text-gray-400 text-5xl mb-4"></i>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Penarikan</h3>
        <p class="text-gray-600 mb-6">Belum ada permintaan penarikan dana dari SIMPels</p>
        <button wire:click="openWithdrawalModal" 
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition inline-flex items-center gap-2">
            <i class="fas fa-plus"></i>
            <span>Buat Permintaan Penarikan</span>
        </button>
    </div>
    @endforelse
</div>

<!-- Pagination -->
@if($withdrawals->hasPages())
<div class="mt-6">
    {{ $withdrawals->links() }}
</div>
@endif
