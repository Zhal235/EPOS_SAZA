<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

/**
 * Laravel Livewire component for managing products in EPOS.
 * 
 * This component provides working modals for:
 * - Add Product Modal ($showAddModal)
 * - Import Product Modal ($showImportModal) 
 * - Delete Product Modal ($showDeleteModal)
 * - Edit Product Modal ($showEditModal)
 * - View Product Modal ($showViewModal)
 * 
 * Key Methods:
 * - openAddModal(): reset validation and open Add Product modal
 * - closeAddModal(): close Add Product modal
 * - openImportModal(): open Import modal
 * - closeImportModal(): close Import modal
 * - confirmDelete($id): set selected product ID and open Delete modal
 * - closeDeleteModal(): close Delete modal
 * - deleteProduct(): delete selected product from database
 * 
 * All methods include proper flash messages for user feedback.
 */
class Products extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $categoryFilter = '';
    public $stockFilter = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    // Modal States
    public $showAddModal = false;
    public $showEditModal = false;
    public $showViewModal = false;
    public $showImportModal = false;
    public $showDeleteModal = false;

    // Form Data
    public $productForm = [
        'sku' => '',
        'barcode' => '',
        'name' => '',
        'description' => '',
        'category_id' => '',
        'supplier_id' => '',
        'brand' => '',
        'unit' => 'pcs',
        'size' => '',
        'cost_price' => '',
        'selling_price' => '',
        'wholesale_price' => '',
        'wholesale_min_qty' => '',
        'stock_quantity' => 0,
        'min_stock' => 5,
        'is_active' => true
    ];

    public $selectedProduct = null;
    public $productToDelete = null;
    public $importFile = null;

    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'stockFilter' => ['except' => '']
    ];

    protected $rules = [
        'productForm.name' => 'required|min:3|max:255',
        'productForm.category_id' => 'required|exists:categories,id',
        'productForm.supplier_id' => 'required|exists:suppliers,id',
        'productForm.cost_price' => 'required|numeric|min:0|max:99999999.99',
        'productForm.selling_price' => 'required|numeric|min:0|max:99999999.99',
        'productForm.stock_quantity' => 'required|integer|min:0',
        'productForm.min_stock' => 'required|integer|min:0',
        'productForm.unit' => 'required|string|max:20',
        'productForm.brand' => 'nullable|string|max:100',
        'productForm.description' => 'nullable|string|max:1000',
        'productForm.sku' => 'nullable|string|max:50|unique:products,sku',
        'productForm.barcode' => 'nullable|string|max:50|unique:products,barcode',
        'productForm.size' => 'nullable|string|max:50'
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStockFilter()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    // ==================== MODAL METHODS ====================

    /**
     * Reset validation and open Add Product modal
     */
    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

    /**
     * Close Add Product modal
     */
    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }

    public function openEditModal($productId)
    {
        $product = Product::findOrFail($productId);
        $this->selectedProduct = $product;
        $this->productForm = [
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'name' => $product->name,
            'description' => $product->description,
            'category_id' => $product->category_id,
            'supplier_id' => $product->supplier_id,
            'brand' => $product->brand,
            'unit' => $product->unit,
            'size' => $product->size,
            'cost_price' => $product->cost_price,
            'selling_price' => $product->selling_price,
            'wholesale_price' => $product->wholesale_price,
            'wholesale_min_qty' => $product->wholesale_min_qty,
            'stock_quantity' => $product->stock_quantity,
            'min_stock' => $product->min_stock,
            'is_active' => $product->is_active
        ];
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    public function openViewModal($productId)
    {
        $this->selectedProduct = Product::with(['category', 'supplier'])->findOrFail($productId);
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedProduct = null;
    }

    /**
     * Open Import modal
     */
    public function openImportModal()
    {
        $this->showImportModal = true;
    }

    /**
     * Close Import modal
     */
    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importFile = null;
    }

    /**
     * Generate CSV template with headers and sample data
     */
    private function generateCsvTemplate()
    {
        $output = fopen('php://temp', 'r+');
        
        // CSV Headers
        $headers = [
            'name',
            'category_name', 
            'supplier_name',
            'cost_price',
            'selling_price',
            'stock_quantity',
            'brand',
            'unit',
            'description'
        ];
        
        fputcsv($output, $headers);
        
        // Sample data rows
        $sampleData = [
            [
                'Laptop ASUS ROG',
                'Electronics',
                'PT Tech Supplier',
                '8000000',
                '12000000',
                '5',
                'ASUS',
                'pcs',
                'Gaming laptop with high performance'
            ],
            [
                'Mouse Wireless',
                'Electronics',
                'PT Tech Supplier', 
                '150000',
                '250000',
                '20',
                'Logitech',
                'pcs',
                'Wireless mouse with ergonomic design'
            ],
            [
                'Kopi Arabica Premium',
                'Food & Beverage',
                'CV Coffee Supply',
                '45000',
                '65000',
                '100',
                'Premium Coffee',
                'kg',
                'High quality arabica coffee beans'
            ]
        ];
        
        foreach ($sampleData as $row) {
            fputcsv($output, $row);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Set selected product ID and open Delete modal
     */
    public function confirmDelete($productId)
    {
        \Log::info('confirmDelete called', ['productId' => $productId]);
        
        $this->productToDelete = Product::findOrFail($productId);
        $this->showDeleteModal = true;
        
        \Log::info('Delete modal opened', [
            'productToDelete' => $this->productToDelete->name,
            'showDeleteModal' => $this->showDeleteModal
        ]);
    }

    /**
     * Close Delete modal
     */
    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->productToDelete = null;
    }

    /**
     * Close Delete modal (alias for cancelDelete)
     */
    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->productToDelete = null;
    }

    // ==================== CRUD METHODS ====================

    /**
     * Save new product to database with flash messages
     */
    public function saveProduct()
    {
        $this->validate();

        try {
            // Auto-generate SKU if empty
            if (empty($this->productForm['sku'])) {
                $this->productForm['sku'] = Product::generateSku();
            }

            Product::create($this->productForm);

            session()->flash('message', 'Produk berhasil dibuat!');
            $this->closeAddModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat produk: ' . $e->getMessage());
        }
    }

    public function updateProduct()
    {
        // Custom validation for edit mode to exclude current product from unique checks
        $rules = $this->rules;
        if ($this->selectedProduct) {
            $rules['productForm.sku'] = 'nullable|string|max:50|unique:products,sku,' . $this->selectedProduct->id;
            $rules['productForm.barcode'] = 'nullable|string|max:50|unique:products,barcode,' . $this->selectedProduct->id;
        }
        
        $this->validate($rules);

        if (!$this->selectedProduct) {
            session()->flash('error', 'Produk tidak ditemukan!');
            $this->closeEditModal();
            return;
        }

        try {
            // Ensure numeric values are properly formatted
            $this->productForm['cost_price'] = floatval($this->productForm['cost_price']);
            $this->productForm['selling_price'] = floatval($this->productForm['selling_price']);
            $this->productForm['stock_quantity'] = intval($this->productForm['stock_quantity']);
            $this->productForm['min_stock'] = intval($this->productForm['min_stock']);

            $this->selectedProduct->update($this->productForm);

            session()->flash('message', 'Produk berhasil diperbarui!');
            $this->closeEditModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    /**
     * Delete selected product from database with flash messages
     */
    public function deleteProduct()
    {
        if (!$this->productToDelete) {
            session()->flash('error', 'Produk tidak ditemukan!');
            $this->cancelDelete();
            return;
        }

        try {
            $productName = $this->productToDelete->name;
            $this->productToDelete->delete();
            
            session()->flash('message', "Produk '{$productName}' berhasil dihapus!");
            $this->cancelDelete();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus produk: ' . $e->getMessage());
            $this->cancelDelete();
        }
    }

    public function importProducts()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls|max:2048'
        ]);

        try {
            $path = $this->importFile->store('imports', 'local');
            $fullPath = storage_path('app/' . $path);
            
            // Process Excel file only
            $result = $this->processExcelImport($fullPath);
            
            // Clean up temporary file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            if ($result['success']) {
                session()->flash('message', "Berhasil mengimpor {$result['imported']} produk!");
                if ($result['errors'] > 0) {
                    session()->flash('warning', "Import selesai dengan {$result['errors']} kesalahan. Periksa format dan coba lagi untuk item yang gagal.");
                }
            } else {
                session()->flash('error', 'Import gagal: ' . $result['message']);
            }
            
            $this->closeImportModal();
            $this->resetPage();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Import gagal: ' . $e->getMessage());
            $this->closeImportModal();
        }
    }

    private function processCsvImport($filePath)
    {
        if (!file_exists($filePath)) {
            return ['success' => false, 'message' => 'File not found'];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Cannot read file'];
        }

        $header = fgetcsv($handle); // Read header row
        
        $imported = 0;
        $errors = 0;
        $errorMessages = [];
        
        while (($data = fgetcsv($handle)) !== false) {
            try {
                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }

                // Assume CSV format: name, category_name, supplier_name, cost_price, selling_price, stock_quantity, brand, unit, description
                $productData = [
                    'name' => trim($data[0] ?? ''),
                    'category_name' => trim($data[1] ?? ''),
                    'supplier_name' => trim($data[2] ?? ''),
                    'cost_price' => floatval(str_replace(',', '', $data[3] ?? 0)),
                    'selling_price' => floatval(str_replace(',', '', $data[4] ?? 0)),
                    'stock_quantity' => intval($data[5] ?? 0),
                    'brand' => trim($data[6] ?? ''),
                    'unit' => trim($data[7] ?? 'pcs'),
                    'description' => trim($data[8] ?? ''),
                ];

                // Validate required fields
                if (empty($productData['name'])) {
                    $errors++;
                    $errorMessages[] = "Row " . ($imported + $errors) . ": Product name is required";
                    continue;
                }

                // Find category
                $category = Category::where('name', 'LIKE', $productData['category_name'])->first();
                if (!$category) {
                    $errors++;
                    $errorMessages[] = "Row " . ($imported + $errors) . ": Category '{$productData['category_name']}' not found";
                    continue;
                }
                
                // Find supplier
                $supplier = Supplier::where('name', 'LIKE', $productData['supplier_name'])->first();
                if (!$supplier) {
                    $errors++;
                    $errorMessages[] = "Row " . ($imported + $errors) . ": Supplier '{$productData['supplier_name']}' not found";
                    continue;
                }

                // Validate prices
                if ($productData['cost_price'] <= 0 || $productData['selling_price'] <= 0) {
                    $errors++;
                    $errorMessages[] = "Row " . ($imported + $errors) . ": Invalid prices for '{$productData['name']}'";
                    continue;
                }
                
                // Create product
                Product::create([
                    'sku' => Product::generateSku(),
                    'name' => $productData['name'],
                    'category_id' => $category->id,
                    'supplier_id' => $supplier->id,
                    'cost_price' => $productData['cost_price'],
                    'selling_price' => $productData['selling_price'],
                    'stock_quantity' => $productData['stock_quantity'],
                    'min_stock' => 5, // Default
                    'brand' => $productData['brand'],
                    'unit' => $productData['unit'],
                    'description' => $productData['description'],
                    'is_active' => true
                ]);
                
                $imported++;
                
            } catch (\Exception $e) {
                $errors++;
                $errorMessages[] = "Row " . ($imported + $errors) . ": " . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'errorMessages' => array_slice($errorMessages, 0, 5) // Limit error messages
        ];
    }

    /**
     * Process Excel file import
     */
    private function processExcelImport($filePath)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();
            
            // Skip header row
            array_shift($data);
            
            $imported = 0;
            $errors = 0;
            $errorMessages = [];
            
            foreach ($data as $rowIndex => $row) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    // Excel format: name, category_name, supplier_name, cost_price, selling_price, stock_quantity, brand, unit, description
                    $productData = [
                        'name' => trim($row[0] ?? ''),
                        'category_name' => trim($row[1] ?? ''),
                        'supplier_name' => trim($row[2] ?? ''),
                        'cost_price' => floatval($row[3] ?? 0),
                        'selling_price' => floatval($row[4] ?? 0),
                        'stock_quantity' => intval($row[5] ?? 0),
                        'brand' => trim($row[6] ?? ''),
                        'unit' => trim($row[7] ?? 'pcs'),
                        'description' => trim($row[8] ?? ''),
                    ];

                    // Validate required fields
                    if (empty($productData['name'])) {
                        $errors++;
                        $errorMessages[] = "Row " . ($rowIndex + 2) . ": Product name is required";
                        continue;
                    }

                    // Find category
                    $category = Category::where('name', 'LIKE', $productData['category_name'])->first();
                    if (!$category) {
                        $errors++;
                        $errorMessages[] = "Row " . ($rowIndex + 2) . ": Category '{$productData['category_name']}' not found";
                        continue;
                    }
                    
                    // Find supplier
                    $supplier = Supplier::where('name', 'LIKE', $productData['supplier_name'])->first();
                    if (!$supplier) {
                        $errors++;
                        $errorMessages[] = "Row " . ($rowIndex + 2) . ": Supplier '{$productData['supplier_name']}' not found";
                        continue;
                    }

                    // Validate prices
                    if ($productData['cost_price'] <= 0 || $productData['selling_price'] <= 0) {
                        $errors++;
                        $errorMessages[] = "Row " . ($rowIndex + 2) . ": Invalid prices for '{$productData['name']}'";
                        continue;
                    }
                    
                    // Create product
                    Product::create([
                        'sku' => Product::generateSku(),
                        'name' => $productData['name'],
                        'category_id' => $category->id,
                        'supplier_id' => $supplier->id,
                        'cost_price' => $productData['cost_price'],
                        'selling_price' => $productData['selling_price'],
                        'stock_quantity' => $productData['stock_quantity'],
                        'min_stock' => 5, // Default
                        'brand' => $productData['brand'],
                        'unit' => $productData['unit'],
                        'description' => $productData['description'],
                        'is_active' => true
                    ]);
                    
                    $imported++;
                    
                } catch (\Exception $e) {
                    $errors++;
                    $errorMessages[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                }
            }
            
            return [
                'success' => true,
                'imported' => $imported,
                'errors' => $errors,
                'errorMessages' => array_slice($errorMessages, 0, 5) // Limit error messages
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to read Excel file: ' . $e->getMessage()
            ];
        }
    }

    // Utility Methods
    public function resetForm()
    {
        $this->productForm = [
            'sku' => '',
            'barcode' => '',
            'name' => '',
            'description' => '',
            'category_id' => '',
            'supplier_id' => '',
            'brand' => '',
            'unit' => 'pcs',
            'size' => '',
            'cost_price' => '',
            'selling_price' => '',
            'wholesale_price' => '',
            'wholesale_min_qty' => '',
            'stock_quantity' => 0,
            'min_stock' => 5,
            'is_active' => true
        ];
        $this->selectedProduct = null;
        $this->productToDelete = null;
    }

    public function generateBarcode()
    {
        // Generate a simple barcode based on timestamp and random number
        $barcode = time() . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
        $this->productForm['barcode'] = $barcode;
    }

    public function getProducts()
    {
        $query = Product::with(['category', 'supplier'])
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('sku', 'like', '%' . $this->search . '%')
                          ->orWhere('barcode', 'like', '%' . $this->search . '%')
                          ->orWhere('brand', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->categoryFilter, function ($q) {
                $q->where('category_id', $this->categoryFilter);
            })
            ->when($this->stockFilter, function ($q) {
                if ($this->stockFilter === 'low_stock') {
                    $q->whereColumn('stock_quantity', '<=', 'min_stock');
                } elseif ($this->stockFilter === 'out_of_stock') {
                    $q->where('stock_quantity', 0);
                } elseif ($this->stockFilter === 'in_stock') {
                    $q->where('stock_quantity', '>', 0);
                }
            })
            ->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(12);
    }

    public function render()
    {
        return view('livewire.products', [
            'products' => $this->getProducts(),
            'categories' => Category::active()->ordered()->get(),
            'suppliers' => Supplier::active()->get(),
            'totalProducts' => Product::count(),
            'lowStockCount' => Product::lowStock()->count(),
            'outOfStockCount' => Product::where('stock_quantity', 0)->count(),
            'totalValue' => Product::selectRaw('SUM(stock_quantity * cost_price) as total')->value('total') ?? 0
        ])->layout('layouts.epos', [
            'header' => 'Manajemen Produk'
        ]);
    }
}
