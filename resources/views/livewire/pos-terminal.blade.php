<div>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">
            <!-- Products Grid -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full">
                    <!-- Search & Categories -->
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-4 flex-1">
                            <div class="relative flex-1 max-w-md">
                                <input type="text" placeholder="Search products or scan barcode..." 
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
                            </div>
                            <button class="px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-barcode mr-2"></i>Scan
                            </button>
                        </div>
                    </div>

                    <!-- Category Tabs -->
                    <div class="flex space-x-2 mb-6 overflow-x-auto">
                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm whitespace-nowrap">All</button>
                        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm whitespace-nowrap hover:bg-gray-200">Beverages</button>
                        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm whitespace-nowrap hover:bg-gray-200">Snacks</button>
                        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm whitespace-nowrap hover:bg-gray-200">Instant Food</button>
                        <button class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm whitespace-nowrap hover:bg-gray-200">Personal Care</button>
                    </div>

                    <!-- Products Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                        <!-- Product Card 1 -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                            <div class="w-full h-24 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
                                <i class="fas fa-wine-bottle text-red-500 text-2xl"></i>
                            </div>
                            <h4 class="font-medium text-gray-900 text-sm mb-1">Coca Cola 330ml</h4>
                            <p class="text-xs text-gray-500 mb-2">SKU: CC001</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-indigo-600">Rp 5,000</span>
                                <span class="text-xs text-gray-500">Stock: 45</span>
                            </div>
                        </div>

                        <!-- Product Card 2 -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                            <div class="w-full h-24 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
                                <i class="fas fa-cookie-bite text-yellow-600 text-2xl"></i>
                            </div>
                            <h4 class="font-medium text-gray-900 text-sm mb-1">Oreo Original</h4>
                            <p class="text-xs text-gray-500 mb-2">SKU: OR001</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-indigo-600">Rp 8,500</span>
                                <span class="text-xs text-gray-500">Stock: 23</span>
                            </div>
                        </div>

                        <!-- Product Card 3 -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                            <div class="w-full h-24 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
                                <i class="fas fa-utensils text-orange-500 text-2xl"></i>
                            </div>
                            <h4 class="font-medium text-gray-900 text-sm mb-1">Indomie Goreng</h4>
                            <p class="text-xs text-gray-500 mb-2">SKU: IG001</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-indigo-600">Rp 3,500</span>
                                <span class="text-xs text-red-500">Stock: 15</span>
                            </div>
                        </div>

                        <!-- Product Card 4 -->
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer">
                            <div class="w-full h-24 bg-gray-100 rounded-lg mb-3 flex items-center justify-center">
                                <i class="fas fa-pump-soap text-blue-500 text-2xl"></i>
                            </div>
                            <h4 class="font-medium text-gray-900 text-sm mb-1">Lifebuoy Soap</h4>
                            <p class="text-xs text-gray-500 mb-2">SKU: LB001</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-indigo-600">Rp 4,200</span>
                                <span class="text-xs text-gray-500">Stock: 67</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart & Checkout -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 h-full flex flex-col">
                    <!-- Customer Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option>Walk-in Customer</option>
                            <option>John Doe - 081234567890</option>
                            <option>Jane Smith - 081987654321</option>
                        </select>
                    </div>

                    <!-- Cart Items -->
                    <div class="flex-1 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Cart Items</h3>
                        
                        <!-- Cart Item 1 -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 text-sm">Coca Cola 330ml</h4>
                                    <p class="text-xs text-gray-500">Rp 5,000 each</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <span class="w-8 text-center">2</span>
                                    <button class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                                <div class="ml-3 text-right">
                                    <p class="font-medium text-gray-900">Rp 10,000</p>
                                    <button class="text-red-500 text-xs hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Cart Item 2 -->
                            <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div class="flex-1">
                                    <h4 class="font-medium text-gray-900 text-sm">Oreo Original</h4>
                                    <p class="text-xs text-gray-500">Rp 8,500 each</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-minus text-xs"></i>
                                    </button>
                                    <span class="w-8 text-center">1</span>
                                    <button class="w-7 h-7 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-plus text-xs"></i>
                                    </button>
                                </div>
                                <div class="ml-3 text-right">
                                    <p class="font-medium text-gray-900">Rp 8,500</p>
                                    <button class="text-red-500 text-xs hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="border-t border-gray-200 pt-4 mb-6">
                        <div class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal (3 items)</span>
                                <span class="text-gray-900">Rp 18,500</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tax (11%)</span>
                                <span class="text-gray-900">Rp 2,035</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Discount</span>
                                <span class="text-green-600">-Rp 0</span>
                            </div>
                            <hr class="my-2">
                            <div class="flex justify-between text-lg font-bold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-indigo-600">Rp 20,535</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Payment Method</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button class="flex flex-col items-center p-3 border-2 border-indigo-500 bg-indigo-50 rounded-lg">
                                <i class="fas fa-money-bill-wave text-indigo-600 mb-1"></i>
                                <span class="text-xs font-medium text-indigo-600">Cash</span>
                            </button>
                            <button class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-gray-300">
                                <i class="fas fa-qrcode text-gray-600 mb-1"></i>
                                <span class="text-xs font-medium text-gray-600">QRIS</span>
                            </button>
                            <button class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-gray-300">
                                <i class="fas fa-wifi text-gray-600 mb-1"></i>
                                <span class="text-xs font-medium text-gray-600">RFID</span>
                            </button>
                            <button class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-gray-300">
                                <i class="fas fa-credit-card text-gray-600 mb-1"></i>
                                <span class="text-xs font-medium text-gray-600">Card</span>
                            </button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-3">
                        <button class="w-full py-3 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg font-medium hover:from-green-700 hover:to-green-800 transition-all">
                            <i class="fas fa-credit-card mr-2"></i>Process Payment
                        </button>
                        <div class="grid grid-cols-2 gap-3">
                            <button class="py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                                <i class="fas fa-save mr-1"></i>Hold
                            </button>
                            <button class="py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-50">
                                <i class="fas fa-trash mr-1"></i>Clear
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
