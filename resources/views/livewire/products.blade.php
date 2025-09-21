<div>
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Products</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalProducts) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Low Stock</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $lowStockCount }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Out of Stock</p>
                    <p class="text-2xl font-bold text-red-600">{{ $outOfStockCount }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times-circle text-red-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Value</p>
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($totalValue, 0, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Filters and Search -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap items-center space-x-4">
                    <!-- Search -->
                    <div class="relative">
                        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search products, SKU, barcode..." 
                               class="w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>

                    <!-- Category Filter -->
                    <select wire:model.live="categoryFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>

                    <!-- Stock Filter -->
                    <select wire:model.live="stockFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="">All Stock</option>
                        <option value="in_stock">In Stock</option>
                        <option value="low_stock">Low Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
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
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Product Image -->
                    <div class="h-48 bg-gray-100 flex items-center justify-center relative">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        @else
                            <i class="{{ $product->category->icon ?? 'fas fa-box' }} text-4xl" style="color: {{ $product->category->color ?? '#6366F1' }}"></i>
                        @endif
                        
                        <!-- Stock Status Badge -->
                        <div class="absolute top-2 right-2">
                            @if($product->stock_quantity <= 0)
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full">Out of Stock</span>
                            @elseif($product->is_low_stock)
                                <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">Low Stock</span>
                            @else
                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">In Stock</span>
                            @endif
                        </div>
                    </div>

                    <div class="p-4">
                        <!-- Product Name & Brand -->
                        <div class="mb-2">
                            <h3 class="font-semibold text-gray-900 line-clamp-2">{{ $product->name }}</h3>
                            @if($product->brand)
                                <p class="text-sm text-gray-500">{{ $product->brand }}</p>
                            @endif
                        </div>

                        <!-- SKU & Category -->
                        <p class="text-sm text-gray-500 mb-3">
                            SKU: {{ $product->sku }} | {{ $product->category->name }}
                            @if($product->size)
                                | {{ $product->size }}
                            @endif
                        </p>

                        <!-- Pricing Info -->
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Cost Price:</span>
                                <span class="font-medium">{{ $product->formatted_cost_price }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Sell Price:</span>
                                <span class="font-bold text-indigo-600">{{ $product->formatted_selling_price }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Stock:</span>
                                <span class="font-medium {{ $product->is_low_stock ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Profit:</span>
                                <span class="font-medium text-green-600">{{ $product->profit_margin }}%</span>
                            </div>
                        </div>

                        <!-- Barcode -->
                        @if($product->barcode)
                            <div class="mb-4">
                                <p class="text-xs text-gray-400">Barcode: {{ $product->barcode }}</p>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex space-x-2">
                            <button class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button class="flex-1 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full">
                    <div class="text-center py-12">
                        <i class="fas fa-box-open text-gray-400 text-6xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Products Found</h3>
                        <p class="text-gray-500 mb-4">
                            @if($search || $categoryFilter || $stockFilter)
                                No products match your current filters.
                            @else
                                Get started by adding your first product.
                            @endif
                        </p>
                        @if($search || $categoryFilter || $stockFilter)
                            <button wire:click="$set('search', ''); $set('categoryFilter', ''); $set('stockFilter', '')" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Clear Filters
                            </button>
                        @else
                            <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                <i class="fas fa-plus mr-2"></i>Add First Product
                            </button>
                        @endif
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($products->hasPages())
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                {{ $products->links() }}
            </div>
        @endif
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
