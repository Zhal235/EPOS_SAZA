<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'EPOS SAZA') }} - Point of Sale System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
        
        <!-- SweetAlert2 for Enhanced Modal Alerts -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
        <!-- EPOS Notification System Styles -->
        <link rel="stylesheet" href="/css/notifications.css">
        
        <style>
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
            }
            .sidebar {
                transition: all 0.3s ease;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .sidebar-collapsed {
                width: 80px;
            }
            .sidebar-expanded {
                width: 280px;
            }
            .menu-item {
                transition: all 0.2s ease;
            }
            .menu-item:hover {
                background: rgba(255, 255, 255, 0.1);
                transform: translateX(5px);
            }
            .menu-item.active {
                background: rgba(255, 255, 255, 0.2);
                border-right: 4px solid #fff;
            }
            .notification-badge {
                animation: pulse 2s infinite;
            }
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            .glassmorphism {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border: 1px solid rgba(255, 255, 255, 0.2);
            }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: true }">
        <div class="flex h-screen overflow-hidden">
            <!-- Sidebar -->
            <div class="sidebar fixed inset-y-0 left-0 z-50 flex flex-col" 
                 :class="sidebarOpen ? 'sidebar-expanded' : 'sidebar-collapsed'">
                
                <!-- Logo Area -->
                <div class="flex items-center justify-center h-16 px-4 border-b border-white/20">
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center justify-center w-10 h-10 bg-white rounded-lg">
                            <i class="fas fa-cash-register text-indigo-600 text-xl"></i>
                        </div>
                        <div x-show="sidebarOpen" x-transition class="text-white">
                            <h1 class="text-lg font-bold">EPOS SAZA</h1>
                            <p class="text-xs text-indigo-100">Point of Sale</p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <x-sidebar-navigation />

                <!-- Sidebar Toggle & User Info -->
                <div class="p-4 border-t border-white/20">
                    <div class="flex items-center justify-between">
                        <button @click="sidebarOpen = !sidebarOpen" 
                                class="p-2 text-white hover:bg-white/10 rounded-lg transition-colors">
                            <i class="fas fa-bars"></i>
                        </button>
                        
                        <div x-show="sidebarOpen" x-transition class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600 text-sm"></i>
                            </div>
                            <div class="text-white text-sm">
                                <p class="font-medium">{{ auth()->user()->name }}</p>
                                <p class="text-indigo-200 text-xs">
                                    @if(auth()->user()->isAdmin())
                                        <i class="fas fa-crown mr-1"></i>Administrator
                                    @elseif(auth()->user()->isManager())
                                        <i class="fas fa-user-tie mr-1"></i>Manager
                                    @else
                                        <i class="fas fa-cash-register mr-1"></i>Cashier
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col min-h-screen overflow-hidden" 
                 :class="sidebarOpen ? 'ml-70' : 'ml-20'" style="margin-left: 280px;" 
                 :style="sidebarOpen ? 'margin-left: 280px' : 'margin-left: 80px'">
                
                <!-- Top Header -->
                <header class="glassmorphism shadow-sm border-b border-gray-200 sticky top-0 z-40">
                    <div class="flex items-center justify-between px-6 py-4">
                        <!-- Breadcrumb & Page Title -->
                        <div class="flex items-center space-x-4">
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">
                                    @if(isset($header))
                                        @if(str_contains($header, 'POS'))
                                            <i class="fas fa-shopping-cart mr-2"></i>
                                        @elseif(str_contains($header, 'Products'))
                                            <i class="fas fa-box mr-2"></i>
                                        @elseif(str_contains($header, 'Transaction'))
                                            <i class="fas fa-receipt mr-2"></i>
                                        @elseif(str_contains($header, 'Dashboard'))
                                            <i class="fas fa-tachometer-alt mr-2"></i>
                                        @else
                                            <i class="fas fa-cog mr-2"></i>
                                        @endif
                                        {{ $header }}
                                    @else
                                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                                    @endif
                                </h1>
                                <nav class="flex space-x-2 text-sm text-gray-500">
                                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Beranda</a>
                                    <span>/</span>
                                    <span class="text-gray-900">
                                        @if(isset($header))
                                            {{ $header }}
                                        @else
                                            Dashboard
                                        @endif
                                    </span>
                                </nav>
                            </div>
                        </div>

                        <!-- Header Actions -->
                        <div class="flex items-center space-x-4">
                            <!-- SIMPels API Connection Status - HIDDEN -->
                            <!--
                            <div class="relative group">
                                <div id="api-connection-status" class="flex items-center space-x-2 px-3 py-2 rounded-lg transition-all cursor-pointer">
                                    <div class="flex items-center space-x-2">
                                        <div id="connection-indicator" class="w-3 h-3 rounded-full bg-gray-400 animate-pulse"></div>
                                        <span id="connection-text" class="text-xs font-medium text-gray-600">Menghubungkan...</span>
                                    </div>
                                </div>
                                
                                <div class="absolute right-0 top-full mt-2 w-64 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-50">
                                    <div class="p-3">
                                        <div class="font-semibold mb-1">SIMPels API Status</div>
                                        <div id="connection-details" class="text-gray-300">
                                            Memeriksa koneksi ke server SIMPels...
                                        </div>
                                        <div class="text-gray-400 mt-2 text-xs">
                                            Terakhir diperiksa: <span id="last-check-time">-</span>
                                        </div>
                                    </div>
                                    <div class="absolute top-0 right-4 transform -translate-y-1 w-2 h-2 bg-gray-900 rotate-45"></div>
                                </div>
                            </div>
                            -->

                            <!-- Notifications -->
                            <div class="relative">
                                <button class="p-2 text-gray-400 hover:text-gray-600 relative">
                                    <i class="fas fa-bell text-xl"></i>
                                    <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                                </button>
                            </div>

                            <!-- Quick Actions -->
                            <div class="flex space-x-2">
                                <a href="{{ route('pos') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Penjualan Baru
                                </a>
                            </div>

                            <!-- User Menu -->
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100">
                                    <div class="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                                        <span class="text-white text-sm font-medium">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                    </div>
                                    <div class="text-left">
                                        <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                                        <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                                    </div>
                                    <i class="fas fa-chevron-down text-gray-400"></i>
                                </button>

                                <!-- Dropdown Menu -->
                                <div x-show="open" @click.away="open = false" x-transition
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                                    <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-3"></i>Profil
                                    </a>
                                    <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-3"></i>Pengaturan
                                    </a>
                                    <hr class="my-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 text-left">
                                            <i class="fas fa-sign-out-alt mr-3"></i>Keluar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-auto p-6 pb-20">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Status Bar - Fixed at bottom -->
        <footer class="bg-white border-t border-gray-200 px-6 py-3 fixed bottom-0 left-0 right-0 z-50">
            <div class="flex items-center justify-between text-sm text-gray-500">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        <span>Sistem Online</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-database mr-2"></i>
                        <span>Database Terhubung</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span>Sinkronisasi Terakhir: {{ now()->format('H:i:s') }}</span>
                    <span>Toko: SAZA Cabang Utama</span>
                </div>
            </div>
        </footer>

        <!-- Global Scripts -->
        @livewireScripts
        
        <!-- Environment Config (Load FIRST to disable console in production) -->
        <script src="/js/config/env.js"></script>
        
        <!-- EPOS Notification System -->
        <script src="/js/modules/notification-system.js"></script>
        <script src="/js/modules/error-handler.js"></script>
        
        <!-- SIMPels API Configuration from Laravel -->
        <script>
            // Make Laravel config available to JavaScript
            window.SIMPELS_API_URL = '{{ config('services.simpels.api_url') }}';
            window.SIMPELS_API_KEY = '{{ config('services.simpels.api_key') }}';
            window.SIMPELS_API_TIMEOUT = {{ config('services.simpels.timeout', 30) }};
        </script>
        
        <!-- SIMPels API Integration Scripts -->
        <script src="/js/config/api.js"></script>
        <script src="/js/utils/api.js"></script>
        <!-- Connection monitoring completely disabled for performance -->
        <script src="/js/modules/simpels-connection-alert.js"></script>
        <script src="/js/modules/epos-sweetalert.js"></script>
        <script src="/js/modules/transaction-logger.js"></script>
        
        @php
            $isPosPage = request()->routeIs('pos') || 
                        str_contains(request()->path(), 'pos') || 
                        (isset($header) && str_contains(strtolower($header), 'pos'));
        @endphp
        
        @if($isPosPage)
        <!-- POS-specific scripts (only load on POS pages) -->
        <script>if(window.APP_DEBUG) console.log('🏪 Loading POS-specific scripts for:', '{{ $header ?? 'POS' }}');</script>
        <script src="/js/modules/customer-scanner.js"></script>
        <script src="/js/modules/transaction-processor.js"></script>
        <script src="/js/integration/pos-integration.js"></script>
        @else
        <script>if(window.APP_DEBUG) console.log('📄 Non-POS page detected:', '{{ $header ?? 'Unknown' }}', '- Skipping POS scripts');</script>
        @endif
        
        <!-- Global scripts -->
        <script src="/js/modules/refund-processor.js"></script>
        
        <!-- User metadata for API integration -->
        <meta name="user-name" content="{{ auth()->user()->name }}">
        <meta name="user-id" content="{{ auth()->user()->id }}">
        
        <!-- System initialization -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Log system startup
            transactionLogger.logSystem('ePOS system started', {
                user: '{{ auth()->user()->name }}',
                role: '{{ auth()->user()->role }}',
                timestamp: new Date().toLocaleString('id-ID', { timeZone: 'Asia/Jakarta' })
            });
        });
        
        // Auto-update time (optimized - only update visible elements)
        let timeUpdateInterval;
        function startTimeUpdater() {
            // Only start if there are time elements to update
            if (document.querySelectorAll('[data-time]').length > 0) {
                timeUpdateInterval = setInterval(() => {
                    document.querySelectorAll('[data-time]').forEach(el => {
                        el.textContent = new Date().toLocaleTimeString();
                    });
                }, 1000);
            }
        }
        
        // Start time updater only when needed
        document.addEventListener('DOMContentLoaded', startTimeUpdater);
        
        // Stop interval when page becomes hidden
        document.addEventListener('visibilitychange', () => {
            if (document.hidden && timeUpdateInterval) {
                clearInterval(timeUpdateInterval);
                timeUpdateInterval = null;
            } else if (!document.hidden && !timeUpdateInterval) {
                startTimeUpdater();
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                e.preventDefault();
                // Open POS Terminal
                window.location.href = '#';
            }
        });
        
        // Debug Livewire (only in development)
        if (window.APP_DEBUG) {
            console.log('Livewire loaded:', typeof Livewire !== 'undefined');
            if (typeof Livewire !== 'undefined') {
                console.log('Livewire version:', Livewire.version || 'unknown');
            }
        }
        </script>
    </body>
</html>