<div x-data="{ showDeleteModal: @entangle('showDeleteModal') }">
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
                    <button 
                        type="button"
                        x-data
                        x-on:click="$wire.set('showImportModal', true)"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <i class="fas fa-upload mr-2"></i>Import
                    </button>
                    <button 
                        type="button"
                        x-data
                        x-on:click="$wire.set('showAddModal', true)"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                    >
                        <i class="fas fa-plus mr-2"></i>Add Product
                    </button>
                </div>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($products as $product)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-3">
                            <div class="max-w-[70%]">
                                <h3 class="font-bold text-gray-900 text-lg line-clamp-2 leading-tight">{{ $product->name }}</h3>
                                @if($product->brand)
                                    <p class="text-sm text-gray-500 mt-1">{{ $product->brand }}</p>
                                @endif
                            </div>
                            <div class="flex-shrink-0">
                                @if($product->stock_quantity <= 0)
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-1 rounded-full border border-red-200">Out of Stock</span>
                                @elseif($product->is_low_stock)
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-1 rounded-full border border-yellow-200">Low Stock</span>
                                @else
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full border border-green-200">In Stock</span>
                                @endif
                            </div>
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
                             <button wire:click.prevent="openRestockModal({{ $product->id }})" type="button" class="px-3 py-2 text-sm bg-orange-50 text-orange-700 border border-orange-200 rounded-lg hover:bg-orange-100 transition-colors" title="Restock / Tambah Stok">
                                <i class="fas fa-cubes"></i>
                            </button>
                            <button wire:click.prevent="openEditModal({{ $product->id }})" type="button" class="flex-1 py-2 text-sm border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-edit mr-1"></i>Edit
                            </button>
                            <button wire:click.prevent="openViewModal({{ $product->id }})" type="button" class="flex-1 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                            <button wire:click.prevent="confirmDelete({{ $product->id }})" type="button" class="px-3 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors" title="Delete Product">
                                <i class="fas fa-trash"></i>
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

    <!-- Debug Info -->
    @if(config('app.debug'))
    <div class="fixed bottom-4 left-4 bg-black text-white p-2 rounded text-xs opacity-75 z-50">
        Debug: Add={{ $showAddModal ? 'true' : 'false' }} | Import={{ $showImportModal ? 'true' : 'false' }} | Delete={{ $showDeleteModal ? 'true' : 'false' }}
    </div>
    @endif

    <!-- Success Message -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
             class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    <!-- Error Message -->
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('error') }}
        </div>
    @endif

    <!-- Warning Message -->
    @if (session()->has('warning'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" 
             class="fixed top-4 right-4 bg-yellow-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Add Product Modal - Simple Version -->
    @if($showAddModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" 
         x-data 
         x-init="document.body.classList.add('overflow-y-hidden')"
         x-on:keydown.escape.window="$wire.closeAddModal()"
         x-on:click.self="$wire.closeAddModal()"
    >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" x-on:click="$wire.closeAddModal()">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                 x-on:click.stop>
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-plus-circle mr-2 text-green-600"></i>Add New Product
                    </h2>
                    
                    <form wire:submit="saveProduct" class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Product Name -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                                <input wire:model="productForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Enter product name" required>
                                @error('productForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                                <select wire:model="productForm.category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
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
                                <select wire:model="productForm.supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
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
                                @error('productForm.brand') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Cost Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price *</label>
                                <input wire:model="productForm.cost_price" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0.00" required>
                                @error('productForm.cost_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Selling Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price *</label>
                                <input wire:model="productForm.selling_price" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0.00" required>
                                @error('productForm.selling_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Stock Quantity -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stock Quantity *</label>
                                <input wire:model="productForm.stock_quantity" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0" required>
                                @error('productForm.stock_quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Unit -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
                                <select wire:model="productForm.unit" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                    <option value="pcs">Pieces (pcs)</option>
                                    <option value="kg">Kilogram (kg)</option>
                                    <option value="gram">Gram (g)</option>
                                    <option value="liter">Liter (L)</option>
                                    <option value="ml">Milliliter (ml)</option>
                                    <option value="dus">Dus</option>
                                    <option value="lusin">Lusin</option>
                                </select>
                                @error('productForm.unit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
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
            </div>
        </div>
    </div>
    @endif

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

    <!-- Edit Product Modal -->
    @if($selectedProduct && $showEditModal)
    <x-modal name="edit-product" :show="$showEditModal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                <i class="fas fa-edit mr-2 text-indigo-600"></i>Edit Produk
            </h2>
            
            <form wire:submit="updateProduct" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Product Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk *</label>
                        <input wire:model="productForm.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Masukkan nama produk">
                        @error('productForm.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- SKU -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                        <input wire:model="productForm.sku" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="SKU">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori *</label>
                        <select wire:model="productForm.category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('productForm.category_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Supplier -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pemasok *</label>
                        <select wire:model="productForm.supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">Pilih Pemasok</option>
                            @foreach(\App\Models\Supplier::active()->get() as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('productForm.supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Brand -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Merek</label>
                        <input wire:model="productForm.brand" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Masukkan nama merek">
                    </div>

                    <!-- Unit -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli *</label>
                        <input wire:model="productForm.cost_price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0.00">
                        @error('productForm.cost_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Selling Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual *</label>
                        <input wire:model="productForm.selling_price" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0.00">
                        @error('productForm.selling_price') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stock Quantity -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Stok *</label>
                        <input wire:model="productForm.stock_quantity" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="0">
                        @error('productForm.stock_quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Min Stock -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stok Minimum *</label>
                        <input wire:model="productForm.min_stock" type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="5">
                        @error('productForm.min_stock') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <!-- Barcode -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                        <div class="flex space-x-2">
                            <input wire:model="productForm.barcode" type="text" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Masukkan atau buat barcode">
                            <button type="button" wire:click="generateBarcode" class="px-3 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                <i class="fas fa-barcode"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Size -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ukuran</label>
                        <input wire:model="productForm.size" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Masukkan ukuran">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea wire:model="productForm.description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Masukkan deskripsi produk"></textarea>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input wire:model="productForm.is_active" type="checkbox" id="is_active" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">Produk Aktif</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" wire:click="closeEditModal" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
    @endif

    <!-- Import Modal - Simple Version -->
    @if($showImportModal)
    <div class="fixed inset-0 z-50 overflow-y-auto"
         x-data 
         x-init="document.body.classList.add('overflow-y-hidden')"
         x-on:keydown.escape.window="$wire.closeImportModal()"
         x-on:click.self="$wire.closeImportModal()"
    >
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 transition-opacity" aria-hidden="true" x-on:click="$wire.closeImportModal()">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                 x-on:click.stop>
                <div class="p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">
                        <i class="fas fa-upload mr-2 text-blue-600"></i>Import Products
                    </h2>
                    
                    <form wire:submit="importProducts" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Upload File</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                                <p class="text-gray-600 mb-2">Choose your Excel file</p>
                                <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                @if($importFile)
                                    <p class="mt-2 text-sm text-green-600">
                                        <i class="fas fa-check mr-1"></i>{{ $importFile->getClientOriginalName() }}
                                    </p>
                                @endif
                                @error('importFile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <!-- Download Template Section -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <h4 class="font-medium text-green-900 mb-2">
                                <i class="fas fa-download mr-2"></i>Download Template
                            </h4>
                            <p class="text-sm text-green-800 mb-3">Download template file untuk format yang benar:</p>
                            <a href="{{ route('products.download-template') }}" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700" target="_blank">
                                <i class="fas fa-file-excel mr-2"></i>Download CSV Template
                            </a>
                        </div>

                        <!-- Import Requirements -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-900 mb-2">
                                <i class="fas fa-info-circle mr-2"></i>Import Requirements:
                            </h4>
                            <div class="text-sm text-blue-800 space-y-1">
                                <p><strong>Excel Format Required:</strong></p>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>Download template Excel file first</li>
                                    <li>Fill in your product data following the format</li>
                                    <li>Save as .xlsx or .xls file</li>
                                    <li>Upload the completed file</li>
                                </ul>
                                <p class="mt-2"><strong>Required Fields:</strong></p>
                                <ul class="list-disc list-inside space-y-1 ml-4">
                                    <li>Product Name</li>
                                    <li>Category Name (must exist in system)</li>
                                    <li>Supplier Name (must exist in system)</li>
                                    <li>Cost Price & Selling Price</li>
                                    <li>Stock Quantity</li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="button" wire:click="closeImportModal" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700" {{ !$importFile ? 'disabled' : '' }}>
                                <i class="fas fa-upload mr-2"></i>Import Products
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Restock Modal -->
    @if($restockForm['product_id'] && $showRestockModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true"
         x-data
         x-init="document.body.classList.add('overflow-y-hidden')"
         x-on:keydown.escape.window="$wire.closeRestockModal()"
         x-on:click.self="$wire.closeRestockModal()"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 aria-hidden="true" 
                 x-on:click="$wire.closeRestockModal()"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                 x-on:click.stop>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-cubes text-orange-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Restock Product
                            </h3>
                            <div class="mt-4">
                                <form wire:submit.prevent="saveRestock">
                                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                                        <h3 class="font-medium text-gray-900">{{ $selectedProduct->name }}</h3>
                                        <p class="text-sm text-gray-500">Current Stock: {{ $selectedProduct->stock_quantity }} {{ $selectedProduct->unit }}</p>
                                    </div>
                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- Quantity to Add -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Add *</label>
                                            <input wire:model.live="restockForm.quantity" type="number" min="1" class="w-full px-3 py-2 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-blue-50" required>
                                            @error('restockForm.quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                    
                                        <!-- Unit Cost (Buying Price) -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Unit Cost (Buying Price) *</label>
                                            <input wire:model.live="restockForm.unit_cost" type="number" step="0.01" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                            @error('restockForm.unit_cost') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                    
                                        <!-- Total Cost (Calculated) -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Cost (Auto)</label>
                                            <input wire:model="restockForm.total_cost" type="number" step="0.01" class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
                                        </div>
                    
                                        <!-- Supplier -->
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
                                            <select wire:model="restockForm.supplier_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                                                <option value="">Select Supplier</option>
                                                @foreach(\App\Models\Supplier::active()->get() as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('restockForm.supplier_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                    
                                    <!-- Notes -->
                                    <div class="mt-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                                        <textarea wire:model="restockForm.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="e.g. Invoice #12345"></textarea>
                                    </div>
                    
                                    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm text-blue-800 mb-4">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        This will increase stock and automatically record an expense transaction.
                                    </div>
                    
                                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200 mt-4">
                                        <button type="button" wire:click="closeRestockModal" class="px-4 py-2 bg-gray-200 text-gray-800 border border-gray-300 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                            Cancel
                                        </button>
                                        <button type="button" wire:click="saveRestock" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 shadow-md">
                                            <i class="fas fa-save mr-2"></i>Confirm Restock
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" 
         x-data
         x-init="document.body.classList.add('overflow-y-hidden')"
         x-on:keydown.escape.window="$wire.cancelDelete()"
         x-on:click.self="$wire.cancelDelete()"
    >
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
             x-on:click="$wire.cancelDelete()"></div>
        
        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen px-4 py-6"
             x-on:click.stop>
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-auto">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-gray-900">Delete Product</h2>
                            <p class="text-sm text-gray-500">This action cannot be undone</p>
                        </div>
                    </div>
                    
                    @if($productToDelete)
                    <div class="mb-6">
                        <p class="text-gray-700">Are you sure you want to delete the product:</p>
                        <p class="font-semibold text-gray-900 mt-1">{{ $productToDelete->name }}</p>
                        <p class="text-sm text-gray-500">SKU: {{ $productToDelete->sku }}</p>
                    </div>
                    @else
                    <div class="mb-6">
                        <p class="text-gray-700">Loading product information...</p>
                    </div>
                    @endif
                    
                    <div class="flex justify-end space-x-3">
                        <button wire:click="cancelDelete" 
                                type="button" 
                                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Cancel
                        </button>
                        <button wire:click="deleteProduct" 
                                type="button" 
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>Delete Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        [x-cloak] { display: none !important; }
    </style>

    @if(config('app.debug'))
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Products page loaded');
        
        // Debug Livewire
        if (typeof Livewire !== 'undefined') {
            console.log('Livewire is loaded');
            
            // Listen to Livewire events
            Livewire.on('productDeleted', () => {
                console.log('Product deleted successfully');
            });
        } else {
            console.error('Livewire is not loaded!');
        }
        
        // Debug modal state
        document.addEventListener('livewire:update', () => {
            console.log('Livewire updated');
        });
    });
    </script>
    @endif
</div>
