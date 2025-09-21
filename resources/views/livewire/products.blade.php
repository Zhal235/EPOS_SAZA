<div>
    <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <input type="text" placeholder="Search products..." 
                               class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <select class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option>All Categories</option>
                        <option>Beverages</option>
                        <option>Snacks</option>
                        <option>Instant Food</option>
                        <option>Personal Care</option>
                    </select>
                </div>
                <div class="flex space-x-3">
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-upload mr-2"></i>Import
                    </button>
                    <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </button>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <!-- Product Card 1 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="h-48 bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-wine-bottle text-red-500 text-4xl"></i>
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">Coca Cola 330ml</h3>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Stock</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">SKU: CC001 | Beverages</p>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Cost Price:</span>
                                <span class="font-medium">Rp 3,500</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sell Price:</span>
                                <span class="font-bold text-indigo-600">Rp 5,000</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Stock:</span>
                                <span class="font-medium">45 units</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button class="flex-1 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Card 2 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="h-48 bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-cookie-bite text-yellow-600 text-4xl"></i>
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">Oreo Original</h3>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Stock</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">SKU: OR001 | Snacks</p>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Cost Price:</span>
                                <span class="font-medium">Rp 6,000</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sell Price:</span>
                                <span class="font-bold text-indigo-600">Rp 8,500</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Stock:</span>
                                <span class="font-medium">23 units</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button class="flex-1 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Card 3 - Low Stock -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="h-48 bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-utensils text-orange-500 text-4xl"></i>
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">Indomie Goreng</h3>
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Low Stock</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">SKU: IG001 | Instant Food</p>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Cost Price:</span>
                                <span class="font-medium">Rp 2,500</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sell Price:</span>
                                <span class="font-bold text-indigo-600">Rp 3,500</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Stock:</span>
                                <span class="font-medium text-red-600">15 units</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button class="flex-1 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700">
                                <i class="fas fa-plus mr-1"></i>Restock
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Product Card 4 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="h-48 bg-gray-100 flex items-center justify-center">
                        <i class="fas fa-pump-soap text-blue-500 text-4xl"></i>
                    </div>
                    <div class="p-4">
                        <div class="flex items-start justify-between mb-2">
                            <h3 class="font-semibold text-gray-900">Lifebuoy Soap</h3>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Stock</span>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">SKU: LB001 | Personal Care</p>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Cost Price:</span>
                                <span class="font-medium">Rp 3,000</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sell Price:</span>
                                <span class="font-bold text-indigo-600">Rp 4,200</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Stock:</span>
                                <span class="font-medium">67 units</span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <button class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button class="flex-1 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between bg-white px-6 py-3 rounded-lg border border-gray-200">
                <div class="flex items-center">
                    <p class="text-sm text-gray-700">
                        Showing <span class="font-medium">1</span> to <span class="font-medium">12</span> of <span class="font-medium">48</span> products
                    </p>
                </div>
                <div class="flex space-x-2">
                    <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Previous</button>
                    <button class="px-3 py-2 text-sm bg-indigo-600 text-white rounded-lg">1</button>
                    <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">2</button>
                    <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">3</button>
                    <button class="px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>
