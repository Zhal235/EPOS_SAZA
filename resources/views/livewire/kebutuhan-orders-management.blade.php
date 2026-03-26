<div>
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-green-700">{{ $counts->confirmed ?? 0 }}</p>
            <p class="text-sm text-green-600 mt-1"><i class="fas fa-check-circle mr-1"></i>Siap Diserahkan</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-yellow-700">{{ $counts->pending ?? 0 }}</p>
            <p class="text-sm text-yellow-600 mt-1"><i class="fas fa-clock mr-1"></i>Menunggu Konfirmasi</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-blue-700">{{ $counts->completed ?? 0 }}</p>
            <p class="text-sm text-blue-600 mt-1"><i class="fas fa-box-open mr-1"></i>Sudah Diserahkan</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
            <p class="text-2xl font-bold text-gray-700">{{ $counts->total ?? 0 }}</p>
            <p class="text-sm text-gray-600 mt-1"><i class="fas fa-list mr-1"></i>Total Pesanan</p>
        </div>
    </div>

    {{-- Filters & Refresh --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-48">
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari</label>
                <input wire:model.live="search" type="text" placeholder="No. pesanan atau nama santri..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select wire:model.live="filter"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="all">Semua</option>
                    <option value="confirmed">✓ Siap Diserahkan</option>
                    <option value="pending_confirmation">⏳ Menunggu</option>
                    <option value="completed">✓ Sudah Diserahkan</option>
                    <option value="rejected">✗ Ditolak</option>
                    <option value="expired">Kedaluwarsa</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Urutkan</label>
                <select wire:model.live="sortBy"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="created_at">Terbaru</option>
                    <option value="total_amount">Total</option>
                    <option value="santri_name">Nama</option>
                </select>
            </div>
            <button wire:click="refreshFromSimpels" wire:loading.attr="disabled"
                class="flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition">
                <i wire:loading.remove wire:target="refreshFromSimpels" class="fas fa-sync-alt"></i>
                <i wire:loading wire:target="refreshFromSimpels" class="fas fa-spinner fa-spin"></i>
                Refresh Status
            </button>
        </div>
    </div>

    {{-- Order Cards --}}
    @if($orders->count() > 0)
        <div class="space-y-3">
            @foreach($orders as $order)
                <div class="bg-white rounded-xl shadow-sm border overflow-hidden
                    {{ $order->status === 'confirmed' ? 'border-green-300' : ($order->status === 'completed' ? 'border-blue-200' : 'border-gray-200') }}">

                    {{-- Highlighted banner untuk yang siap diserahkan --}}
                    @if($order->status === 'confirmed')
                        <div class="bg-green-600 px-4 py-1.5 flex items-center justify-between">
                            <span class="text-white text-xs font-semibold">
                                <i class="fas fa-bell mr-1"></i>SIAP DISERAHKAN — Sudah dikonfirmasi orang tua
                            </span>
                            <span class="text-green-200 text-xs">{{ $order->confirmed_at ? \Carbon\Carbon::parse($order->confirmed_at)->diffForHumans() : '' }}</span>
                        </div>
                    @endif

                    <div class="p-4 flex items-start gap-4">
                        {{-- Icon status --}}
                        <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                            {{ $order->status === 'confirmed' ? 'bg-green-100' : ($order->status === 'completed' ? 'bg-blue-100' : ($order->status === 'rejected' ? 'bg-red-100' : 'bg-yellow-100')) }}">
                            @if($order->status === 'confirmed')
                                <i class="fas fa-check text-green-600"></i>
                            @elseif($order->status === 'completed')
                                <i class="fas fa-box-open text-blue-600"></i>
                            @elseif($order->status === 'rejected')
                                <i class="fas fa-times text-red-600"></i>
                            @else
                                <i class="fas fa-clock text-yellow-600"></i>
                            @endif
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-bold text-gray-900 text-sm">{{ $order->santri_name }}</span>
                                <code class="text-xs bg-gray-100 px-2 py-0.5 rounded font-mono text-gray-600">{{ $order->order_number }}</code>

                                @if($order->status === 'pending_confirmation')
                                    <span class="text-xs bg-yellow-100 text-yellow-700 border border-yellow-300 px-2 py-0.5 rounded-full font-medium">⏳ Menunggu Konfirmasi</span>
                                @elseif($order->status === 'confirmed')
                                    <span class="text-xs bg-green-100 text-green-700 border border-green-300 px-2 py-0.5 rounded-full font-medium">✓ Dikonfirmasi</span>
                                @elseif($order->status === 'completed')
                                    <span class="text-xs bg-blue-100 text-blue-700 border border-blue-300 px-2 py-0.5 rounded-full font-medium">✓ Sudah Diserahkan</span>
                                @elseif($order->status === 'rejected')
                                    <span class="text-xs bg-red-100 text-red-700 border border-red-300 px-2 py-0.5 rounded-full font-medium">✗ Ditolak</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-600 border border-gray-300 px-2 py-0.5 rounded-full font-medium">Kedaluwarsa</span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
                                <span><i class="fas fa-box text-gray-400 mr-1"></i>{{ count($order->items) }} item</span>
                                <span class="font-semibold text-gray-900">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                                <span class="text-gray-400 text-xs">{{ $order->created_at->format('d M Y H:i') }}</span>
                            </div>

                            {{-- Item list (ringkas) --}}
                            <div class="mt-1 text-xs text-gray-500">
                                {{ collect($order->items)->pluck('name')->take(3)->implode(', ') }}
                                @if(count($order->items) > 3)
                                    <span class="text-indigo-500">+{{ count($order->items) - 3 }} lainnya</span>
                                @endif
                            </div>

                            @if($order->status === 'confirmed' && $order->confirmed_by)
                                <p class="text-xs text-green-700 mt-1">
                                    <i class="fas fa-user-check mr-1"></i>Dikonfirmasi oleh {{ $order->confirmed_by }}
                                </p>
                            @elseif($order->status === 'rejected' && $order->rejection_reason)
                                <p class="text-xs text-red-600 mt-1">
                                    <i class="fas fa-comment mr-1"></i>Alasan: {{ $order->rejection_reason }}
                                </p>
                            @endif
                        </div>

                        {{-- Action --}}
                        <div class="flex-shrink-0">
                            @if($order->status === 'confirmed')
                                <button wire:click="completeOrder({{ $order->id }})"
                                    class="flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold transition shadow-sm">
                                    <i class="fas fa-check"></i>
                                    Serahkan Barang
                                </button>
                            @elseif($order->status === 'pending_confirmation')
                                <span class="text-xs text-gray-400 italic">Menunggu<br>konfirmasi</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 py-16 text-center">
            <i class="fas fa-inbox text-5xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-500 text-lg font-medium">Tidak ada pesanan ditemukan</p>
            <p class="text-gray-400 text-sm mt-1">Klik "Refresh Status" untuk memperbarui dari SIMPELS</p>
        </div>
    @endif
</div>
