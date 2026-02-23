@props(['user' => auth()->user()])

<!-- Navigation Menu -->
<nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
    <!-- Dashboard - Available for all roles -->
    <a href="{{ route('dashboard') }}" 
       class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="fas fa-tachometer-alt w-5 text-center"></i>
        <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Dashboard</span>
    </a>

    <!-- RETAIL / TOKO Section -->
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">RETAIL / TOKO</h3>
        </div>
        
        @if($user->canAccessStore())
        <a href="{{ route('pos', ['mode' => 'store']) }}" 
           class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('pos') && (!request('mode') || request('mode') == 'store') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Kasir Toko</span>
        </a>
        @endif

        @if($user->canAccessAdmin())
        <a href="{{ route('products') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('products') ? 'active' : '' }}">
            <i class="fas fa-box w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Produk &amp; Stok</span>
            @if(isset($lowStockCount) && $lowStockCount > 0)
                <span x-show="sidebarOpen" class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $lowStockCount }}</span>
            @endif
        </a>
        
        <a href="{{ route('categories') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('categories') ? 'active' : '' }}">
            <i class="fas fa-tags w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Kategori</span>
        </a>

        <a href="{{ route('revenue.report', ['type' => 'store']) }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('revenue.report') && request('type') == 'store' ? 'active' : '' }}">
            <i class="fas fa-chart-line w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Laporan Pendapatan</span>
        </a>
        @endif
    </div>

    <!-- FOODCOURT Section -->
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">FOODCOURT</h3>
        </div>
        
        @if($user->canAccessFoodcourt())
        <a href="{{ route('pos', ['mode' => 'foodcourt']) }}" 
           class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('pos') && request('mode') == 'foodcourt' ? 'active' : '' }}">
            <i class="fas fa-utensils w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Kasir Foodcourt</span>
        </a>
        @endif

        @if($user->canAccessAdmin())
        <a href="{{ route('tenants') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('tenants') ? 'active' : '' }}">
            <i class="fas fa-store w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Manajemen Tenant</span>
        </a>
        
        <a href="{{ route('foodcourt.finance') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('foodcourt.finance') ? 'active' : '' }}">
            <i class="fas fa-wallet w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Keuangan &amp; Saldo</span>
        </a>

        <a href="{{ route('revenue.report', ['type' => 'foodcourt']) }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('revenue.report') && request('type') == 'foodcourt' ? 'active' : '' }}">
            <i class="fas fa-file-invoice-dollar w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Laporan Pendapatan</span>
        </a>
        @endif
    </div>

    <!-- SYSTEM / GENERAL Section -->
    <div class="pt-4">
        <div x-show="sidebarOpen" class="px-4 mb-2">
            <h3 class="text-xs font-semibold text-indigo-200 uppercase tracking-wider">SYSTEM</h3>
        </div>

        <a href="{{ route('rfid.withdrawal') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('rfid.withdrawal') ? 'active' : '' }}">
            <i class="fas fa-hand-holding-usd w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Penarikan Tunai (Santri)</span>
        </a>

        @if($user->canAccessAdmin())
        <a href="{{ route('financial') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('financial') ? 'active' : '' }}">
            <i class="fas fa-university w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Manajemen Keuangan</span>
        </a>
        @endif

        <a href="{{ route('transactions') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('transactions') ? 'active' : '' }}">
            <i class="fas fa-receipt w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Semua Transaksi</span>
        </a>

        @if($user->isAdmin())
        <a href="{{ route('staff') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('staff') ? 'active' : '' }}">
            <i class="fas fa-users w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Manajemen Staf</span>
        </a>
        @endif

        @if($user->canAccessAdmin())
        <a href="{{ route('cashier.report') }}" class="menu-item flex items-center px-4 py-3 text-white rounded-lg {{ request()->routeIs('cashier.report') ? 'active' : '' }}">
            <i class="fas fa-user-clock w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Laporan Per Kasir</span>
        </a>
        @endif
        
        @if($user->canAccessAdmin())
        <a href="#" class="menu-item flex items-center px-4 py-3 text-white rounded-lg">
            <i class="fas fa-cog w-5 text-center"></i>
            <span x-show="sidebarOpen" x-transition class="ml-3 font-medium">Pengaturan</span>
        </a>
        @endif
    </div>
</nav>