@props(['user' => auth()->user()])

<!-- Navigation Menu -->
<nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
    <!-- Dashboard - Available for all roles -->
    <a href="{{ route('dashboard') }}" 
       class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt w-5 text-center"></i>
        <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Dashboard</span>
    </a>

    <!-- POS Section - Available for all roles -->
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">Point of Sale</h3>
        </div>
        
        <a href="{{ route('pos') }}" 
           class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('pos') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Terminal POS</span>
            <span x-show="sidebarOpen" class="ml-auto">
                <div class="notification-badge w-2 h-2 bg-yellow-400 rounded-full"></div>
            </span>
        </a>

        <a href="{{ route('transactions') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('transactions') ? 'active' : '' }}">
            <i class="fas fa-receipt w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Riwayat Transaksi</span>
        </a>
    </div>

    <!-- Inventory Section - Admin & Manager only -->
    @if($user->canAccessAdmin())
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">Inventory</h3>
        </div>
        
        <a href="{{ route('products') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('products') ? 'active' : '' }}">
            <i class="fas fa-box w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Produk & Stok</span>
            @if(isset($lowStockCount) && $lowStockCount > 0)
                <span x-show="sidebarOpen" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $lowStockCount }}</span>
            @endif
        </a>
        
        <a href="{{ route('categories') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('categories') ? 'active' : '' }}">
            <i class="fas fa-tags w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Kategori & Supplier</span>
        </a>
    </div>
    @endif

    <!-- Reports Section - Role-based access -->
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">Reports</h3>
        </div>
        
        <a href="{{ route('sales.report') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('sales.report') ? 'active' : '' }}">
            <i class="fas fa-chart-line w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">
                @if($user->isCashier())
                    Penjualan & Analitik Saya
                @else
                    Penjualan & Analitik
                @endif
            </span>
        </a>

        @if($user->canAccessAdmin())
        <a href="{{ route('financial') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('financial') ? 'active' : '' }}">
            <i class="fas fa-dollar-sign w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Keuangan</span>
        </a>
        @endif
    </div>

    <!-- Management Section - Admin & Manager only -->
    @if($user->canAccessAdmin())
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">Management</h3>
        </div>
        
        @if($user->isAdmin())
        <a href="{{ route('staff') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('staff') ? 'active' : '' }}">
            <i class="fas fa-users w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Manajemen Staf</span>
        </a>
        @endif
        
        <a href="#" class="menu-item flex items-center px-4 py-3 text-white rounded-lg">
            <i class="fas fa-credit-card w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Metode Pembayaran</span>
        </a>
    </div>
    @endif

    <!-- Settings Section - Role-based -->
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">System</h3>
        </div>
        
        @if($user->canAccessAdmin())
        <a href="#" class="menu-item flex items-center px-4 py-3 text-white rounded-lg">
            <i class="fas fa-cog w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Pengaturan</span>
        </a>
        @endif

        <a href="#" class="menu-item flex items-center px-4 py-3 text-white rounded-lg">
            <i class="fas fa-bell w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Notifikasi</span>
            <span x-show="sidebarOpen" class="ml-auto bg-green-500 text-white text-xs px-2 py-1 rounded-full">3</span>
        </a>

        <!-- Cashier-only menu items -->
        @if($user->isCashier())
        <a href="#" class="menu-item flex items-center px-4 py-3 text-white rounded-lg">
            <i class="fas fa-clock w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Shift Saya</span>
        </a>
        @endif
    </div>
</nav>