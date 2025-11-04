<div>
    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Today's Sales -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Penjualan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900">Rp 2,450,000</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-arrow-up"></i> +12.5% dari kemarin
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Products -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Produk</p>
                    <p class="text-2xl font-bold text-gray-900">1,234</p>
                    <p class="text-sm text-red-600 mt-1">
                        <i class="fas fa-exclamation-triangle"></i> 12 stok menipis
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-green-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Active Customers -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pelanggan Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">567</p>
                    <p class="text-sm text-blue-600 mt-1">
                        <i class="fas fa-user-plus"></i> 23 baru minggu ini
                    </p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Monthly Revenue -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Pendapatan Bulanan</p>
                    <p class="text-2xl font-bold text-gray-900">Rp 89,750,000</p>
                    <p class="text-sm text-green-600 mt-1">
                        <i class="fas fa-chart-line"></i> +8.2% vs bulan lalu
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sales Chart -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">Ringkasan Penjualan</h3>
                    <div class="flex space-x-2">
                        <button class="px-3 py-1 text-sm text-indigo-600 bg-indigo-100 rounded-lg">Harian</button>
                        <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Mingguan</button>
                        <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-100 rounded-lg">Bulanan</button>
                    </div>
                </div>
                <!-- Chart Placeholder -->
                <div class="h-64 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg flex items-center justify-center">
                    <div class="text-center">
                        <i class="fas fa-chart-area text-4xl text-indigo-400 mb-4"></i>
                        <p class="text-gray-600">Grafik Penjualan akan ditampilkan di sini</p>
                        <p class="text-sm text-gray-500">Integrasi dengan Chart.js atau sejenisnya</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Transactions & Quick Actions -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="space-y-3">
                    <a href="{{ route('pos') }}" class="w-full flex items-center p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-plus text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Penjualan Baru</p>
                            <p class="text-sm text-gray-500">Mulai transaksi baru</p>
                        </div>
                    </a>
                    
                    <button class="w-full flex items-center p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-box-open text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Tambah Produk</p>
                            <p class="text-sm text-gray-500">Tambah item inventori baru</p>
                        </div>
                    </button>
                    
                    <button class="w-full flex items-center p-3 text-left border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-chart-bar text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Lihat Laporan</p>
                            <p class="text-sm text-gray-500">Periksa analitik penjualan</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
                    <a href="#" class="text-sm text-indigo-600 hover:text-indigo-500">Lihat Semua</a>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-check text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">#TXN-001234</p>
                                <p class="text-sm text-gray-500">2 item - Tunai</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">Rp 125,000</p>
                            <p class="text-xs text-gray-500">2 menit lalu</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-qrcode text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">#TXN-001233</p>
                                <p class="text-sm text-gray-500">5 item - QRIS</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">Rp 350,000</p>
                            <p class="text-xs text-gray-500">5 menit lalu</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 border border-gray-100 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-credit-card text-purple-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">#TXN-001232</p>
                                <p class="text-sm text-gray-500">1 item - Kartu</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-medium text-gray-900">Rp 75,000</p>
                            <p class="text-xs text-gray-500">8 menit lalu</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts & Payment Methods -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Low Stock Alerts -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Peringatan Stok Menipis</h3>
                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">12 item</span>
            </div>
            <div class="space-y-3">
                <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Coca Cola 330ml</p>
                        <p class="text-sm text-gray-500">SKU: CC001</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-red-600">5 tersisa</p>
                        <p class="text-xs text-gray-500">Min: 20</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-between p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div>
                        <p class="font-medium text-gray-900">Indomie Goreng</p>
                        <p class="text-sm text-gray-500">SKU: IG001</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium text-yellow-600">15 tersisa</p>
                        <p class="text-xs text-gray-500">Min: 50</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Methods Status -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Metode Pembayaran</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-money-bill-wave text-green-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Tunai</p>
                            <p class="text-sm text-gray-500">Selalu tersedia</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-qrcode text-blue-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">QRIS</p>
                            <p class="text-sm text-gray-500">Terhubung</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-wifi text-purple-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">RFID/NFC</p>
                            <p class="text-sm text-gray-500">Siap</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                </div>
                
                <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-credit-card text-red-600"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Terminal Kartu</p>
                            <p class="text-sm text-gray-500">Offline</p>
                        </div>
                    </div>
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>
