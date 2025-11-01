<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Income -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Pemasukan</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</p>
                <p class="text-sm text-green-600 mt-1">
                    <i class="fas fa-sync-alt mr-1"></i>
                    {{ $summary['total_transactions'] }} transaksi
                </p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-arrow-up text-green-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Total Expense -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Total Pengeluaran</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</p>
                <p class="text-sm text-red-600 mt-1">
                    <i class="fas fa-undo mr-1"></i>
                    Refund: Rp {{ number_format($summary['total_refunds'], 0, ',', '.') }}
                </p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-arrow-down text-red-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- RFID Payments -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Pembayaran RFID</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['total_rfid_payments'], 0, ',', '.') }}</p>
                <p class="text-sm mt-1">
                    @if($summary['pending_sync'] > 0)
                        <span class="text-yellow-600">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            {{ $summary['pending_sync'] }} belum sync
                        </span>
                    @else
                        <span class="text-green-600">
                            <i class="fas fa-check-circle mr-1"></i>
                            Semua sync
                        </span>
                    @endif
                </p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-credit-card text-blue-600 text-xl"></i>
            </div>
        </div>
    </div>

    <!-- Pending Withdrawal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 mb-1">Belum Ditarik</p>
                <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($summary['pending_withdrawal'], 0, ',', '.') }}</p>
                <p class="text-sm text-blue-600 mt-1">
                    <i class="fas fa-check mr-1"></i>
                    Ditarik: Rp {{ number_format($summary['withdrawn_amount'], 0, ',', '.') }}
                </p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">Grafik Keuangan</h3>
        <div class="flex space-x-2">
            <span class="inline-flex items-center px-3 py-1 text-sm bg-green-100 text-green-800 rounded-lg">
                <i class="fas fa-circle text-xs mr-2"></i> Pemasukan
            </span>
            <span class="inline-flex items-center px-3 py-1 text-sm bg-red-100 text-red-800 rounded-lg">
                <i class="fas fa-circle text-xs mr-2"></i> Pengeluaran
            </span>
        </div>
    </div>
    <div class="h-80" id="financialChart"></div>
</div>

<!-- Quick Stats -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Activity -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terbaru</h3>
            @if($transactions->count() > 5)
            <button wire:click="setTab('transactions')" class="text-sm text-green-600 hover:text-green-700 font-medium">
                Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
            </button>
            @endif
        </div>
        <div class="space-y-4">
            @forelse($transactions->take(5) as $transaction)
            <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $transaction->category === 'income' ? 'bg-green-100' : 'bg-red-100' }}">
                        <i class="fas {{ $transaction->category === 'income' ? 'fa-arrow-down' : 'fa-arrow-up' }} {{ $transaction->category === 'income' ? 'text-green-600' : 'text-red-600' }}"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $transaction->type_label }}</p>
                        <p class="text-xs text-gray-500">{{ $transaction->santri_name ?? 'N/A' }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold {{ $transaction->category === 'income' ? 'text-green-600' : 'text-red-600' }}">
                        {{ $transaction->category === 'income' ? '+' : '-' }} {{ $transaction->formatted_amount }}
                    </p>
                    <p class="text-xs text-gray-500">{{ $transaction->created_at->format('d M, H:i') }}</p>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                <p class="text-gray-500">Belum ada transaksi</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Keuangan</h3>
        <div class="space-y-4">
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Total Pemasukan</span>
                <span class="text-sm font-semibold text-green-600">+ Rp {{ number_format($summary['total_income'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Total Pengeluaran</span>
                <span class="text-sm font-semibold text-red-600">- Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Pembayaran RFID</span>
                <span class="text-sm font-semibold text-blue-600">Rp {{ number_format($summary['total_rfid_payments'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-3 border-b border-gray-100">
                <span class="text-sm text-gray-600">Refund/Pengembalian</span>
                <span class="text-sm font-semibold text-orange-600">Rp {{ number_format($summary['total_refunds'], 0, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between py-3 pt-4 border-t-2 border-gray-200">
                <span class="text-base font-semibold text-gray-900">Saldo Bersih</span>
                <span class="text-lg font-bold {{ ($summary['total_income'] - $summary['total_expense']) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    Rp {{ number_format($summary['total_income'] - $summary['total_expense'], 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('financialChart');
    if (ctx) {
        const chartData = @json($chartData);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.map(d => d.date),
                datasets: [
                    {
                        label: 'Pemasukan',
                        data: chartData.map(d => d.income),
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Pengeluaran',
                        data: chartData.map(d => d.expense),
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Net',
                        data: chartData.map(d => d.net),
                        borderColor: 'rgb(99, 102, 241)',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        tension: 0.4,
                        fill: false,
                        borderDash: [5, 5]
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush
