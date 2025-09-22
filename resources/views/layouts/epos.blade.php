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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        
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
    <body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: true, darkMode: false }" :class="{ 'dark': darkMode }">
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
                                    <a href="{{ route('dashboard') }}" class="hover:text-gray-700">Home</a>
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
                            <!-- Search -->
                            <div class="relative">
                                <input type="text" placeholder="Search products, customers..." 
                                       class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                            </div>

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
                                    <i class="fas fa-plus mr-2"></i>New Sale
                                </a>
                            </div>

                            <!-- Theme Toggle -->
                            <button @click="darkMode = !darkMode" 
                                    class="p-2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-moon" x-show="!darkMode"></i>
                                <i class="fas fa-sun" x-show="darkMode"></i>
                            </button>

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
                                        <i class="fas fa-user mr-3"></i>Profile
                                    </a>
                                    <a href="#" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-cog mr-3"></i>Settings
                                    </a>
                                    <hr class="my-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2 text-gray-700 hover:bg-gray-100 text-left">
                                            <i class="fas fa-sign-out-alt mr-3"></i>Logout
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
                        <span>System Online</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-database mr-2"></i>
                        <span>Database Connected</span>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <span>Last Sync: {{ now()->format('H:i:s') }}</span>
                    <span>Store: SAZA Main Branch</span>
                </div>
            </div>
        </footer>

        <!-- Global Scripts -->
        @livewireScripts
        <script>
            // Auto-update time
            setInterval(() => {
                document.querySelectorAll('[data-time]').forEach(el => {
                    el.textContent = new Date().toLocaleTimeString();
                });
            }, 1000);
            
            // Keyboard shortcuts
            document.addEventListener('keydown', (e) => {
                if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                    e.preventDefault();
                    // Open POS Terminal
                    window.location.href = '#';
                }
            });
            
            // Debug Livewire
            console.log('Livewire loaded:', typeof Livewire !== 'undefined');
            if (typeof Livewire !== 'undefined') {
                console.log('Livewire version:', Livewire.version || 'unknown');
            }
        </script>
    </body>
</html>