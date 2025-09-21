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
                    <button wire:click="openImportModal" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-upload mr-2"></i>Import
                    </button>
                    <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
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
                            <button wire:click="openEditModal({{ $product->id }})" class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button wire:click="openViewModal({{ $product->id }})" class="flex-1 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
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
                            <button wire:click="openAddModal" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
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

    <!-- Success Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
             class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    <!-- Add Product Modal -->
    <x-modal name="add-product" :show="$showAddModal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-plus-circle mr-2 text-green-600"></i>Add New Product
            </h2>
            
            <form wire:submit="saveProduct" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Product Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                        <input wire:model="productForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Enter product name">
                        @error('productForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input wire:model="productForm.sku" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Auto-generated if empty">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                        <select wire:model="productForm.category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('productForm.category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                        <select wire:model="productForm.supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Select Supplier</option>
                            @foreach(\App\Models\Supplier::active()->get() as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('productForm.supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Brand -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input wire:model="productForm.brand" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Enter brand name">
                    </div>

                    <!-- Unit -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unit</label>
                        <select wire:model="productForm.unit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="pcs">Pieces (pcs)</option>
                            <option value="kg">Kilogram (kg)</option>
                            <option value="gram">Gram (g)</option>
                            <option value="liter">Liter (L)</option>
                            <option value="ml">Milliliter (ml)</option>
                            <option value="dus">Dus</option>
                            <option value="lusin">Lusin</option>
                        </select>
                    </div>

                    <!-- Cost Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price *</label>
                        <input wire:model="productForm.cost_price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0.00">
                        @error('productForm.cost_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Selling Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                        <input wire:model="productForm.selling_price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0.00">
                        @error('productForm.selling_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stock Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                        <input wire:model="productForm.stock_quantity" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0">
                        @error('productForm.stock_quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Min Stock -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Stock *</label>
                        <input wire:model="productForm.min_stock" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="5">
                        @error('productForm.min_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea wire:model="productForm.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Enter product description"></textarea>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" wire:click="closeAddModal" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>Save Product
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- View Product Modal -->
    @if($selectedProduct)
    <x-modal name="view-product" :show="$showViewModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-eye mr-2 text-indigo-600"></i>Product Details
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Product Name</label>
                        <p class="text-lg font-semibold">{{ $selectedProduct->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">SKU</label>
                        <p class="font-mono">{{ $selectedProduct->sku }}</p>
                    </div>
                    
                    @if($selectedProduct->barcode)
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Barcode</label>
                        <p class="font-mono">{{ $selectedProduct->barcode }}</p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Category</label>
                        <p>{{ $selectedProduct->category->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Supplier</label>
                        <p>{{ $selectedProduct->supplier->name }}</p>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Cost Price</label>
                        <p class="text-lg">{{ $selectedProduct->formatted_cost_price }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Selling Price</label>
                        <p class="text-lg text-indigo-600 font-semibold">{{ $selectedProduct->formatted_selling_price }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Stock Quantity</label>
                        <p class="text-lg {{ $selectedProduct->is_low_stock ? 'text-red-600' : 'text-green-600' }}">
                            {{ $selectedProduct->stock_quantity }} {{ $selectedProduct->unit }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Profit Margin</label>
                        <p class="text-lg text-green-600 font-semibold">{{ $selectedProduct->profit_margin }}%</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <span class="px-2 py-1 rounded-full text-xs {{ $selectedProduct->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $selectedProduct->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>
            </div>
            
            @if($selectedProduct->description)
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-500 mb-2">Description</label>
                <p class="text-gray-700">{{ $selectedProduct->description }}</p>
            </div>
            @endif
            
            <div class="flex justify-end space-x-3 pt-6">
                <button wire:click="closeViewModal" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Close
                </button>
                <button wire:click="openEditModal({{ $selectedProduct->id }})" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-edit mr-2"></i>Edit Product
                </button>
            </div>
        </div>
    </x-modal>
    @endif

    <!-- Import Modal -->
    <x-modal name="import-products" :show="$showImportModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-upload mr-2 text-blue-600"></i>Import Products
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                        <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-600 mb-2">Drag and drop your Excel or CSV file here</p>
                        <p class="text-sm text-gray-500">Supported formats: .xlsx, .xls, .csv</p>
                        <input type="file" wire:model="importFile" accept=".xlsx,.xls,.csv" class="hidden" id="import-file">
                        <label for="import-file" class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer">
                            Choose File
                        </label>
                    </div>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h4 class="font-medium text-blue-900 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>Import Requirements:
                    </h4>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>• File must include: Name, Category, Supplier, Cost Price, Selling Price</li>
                        <li>• Category and Supplier must exist in the system</li>
                        <li>• Prices must be in numeric format</li>
                        <li>• SKU will be auto-generated if not provided</li>
                    </ul>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 pt-6">
                <button wire:click="closeImportModal" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
                <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-upload mr-2"></i>Import Products
                </button>
            </div>
        </div>
    </x-modal>
</div>
