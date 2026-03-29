<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

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
    public $showRestockModal = false;
    public $showUnitsModal = false;
    public $showUnitFormModal = false;

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

    public $restockForm = [
        'product_id' => null,
        'quantity' => 1,
        'unit_cost' => 0,
        'total_cost' => 0,
        'supplier_id' => '',
        'notes' => ''
    ];

    public $selectedProduct = null;
    public $productToDelete = null;
    public $importFile = null;

    // Product Units Management
    public $selectedProductForUnits = null;
    public $productUnits = [];
    public $unitForm = [
        'id' => null,
        'unit_name' => '',
        'conversion_rate' => 1,
        'is_sub_unit' => false,
        'is_base_unit' => false,
        'selling_price' => 0.0,
        'cost_price' => 0.0,
        'wholesale_price' => 0.0,
        'barcode' => '',
        'is_active' => true,
        'display_order' => 0,
    ];
    public $editingUnitId = null;

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

    // ==================== RESTOCK METHODS ====================
    
    public function openRestockModal($productId)
    {
        $product = Product::findOrFail($productId);
        $this->selectedProduct = $product;
        
        $this->restockForm = [
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_cost' => $product->cost_price,
            'total_cost' => $product->cost_price * 1,
            'supplier_id' => $product->supplier_id,
            'notes' => ''
        ];
        
        $this->showRestockModal = true;
    }

    public function closeRestockModal()
    {
        $this->showRestockModal = false;
        $this->selectedProduct = null;
        $this->restockForm = [
            'product_id' => null,
            'quantity' => 1,
            'unit_cost' => 0,
            'total_cost' => 0,
            'supplier_id' => '',
            'notes' => ''
        ];
    }
    
    public function updated($property)
    {
        if ($property === 'restockForm.quantity' || $property === 'restockForm.unit_cost') {
            $qty = (int) ($this->restockForm['quantity'] ?? 0);
            $cost = (float) ($this->restockForm['unit_cost'] ?? 0);
            $this->restockForm['total_cost'] = $qty * $cost;
        }
    }

    public function saveRestock()
    {
        $this->validate([
            'restockForm.product_id' => 'required|exists:products,id',
            'restockForm.quantity' => 'required|integer|min:1',
            'restockForm.unit_cost' => 'required|numeric|min:0',
            'restockForm.supplier_id' => 'required|exists:suppliers,id',
        ]);

        try {
            $product = Product::findOrFail($this->restockForm['product_id']);
            $qty = (int) $this->restockForm['quantity'];
            $unitCost = (float) $this->restockForm['unit_cost'];
            $totalCost = $qty * $unitCost; // Recalculate to be safe
            
            // 1. Update Product Stock and Cost Price
            $product->stock_quantity += $qty;
            $product->cost_price = $unitCost; // Update latest cost price
            $product->supplier_id = $this->restockForm['supplier_id']; // Update supplier if changed
            $product->save();
            
            // 2. Record Expense automatically
            $description = "Restock: {$product->name} (Qty: {$qty})";
            if (!empty($this->restockForm['notes'])) {
                $description .= " - " . $this->restockForm['notes'];
            }
            
            $financialService = app(\App\Services\FinancialService::class);
            $financialService->recordExpense(
                $totalCost,
                $description,
                'restock',
                "Product ID: {$product->id}, Supplier ID: {$this->restockForm['supplier_id']}"
            );

            session()->flash('message', "Berhasil restock {$qty} {$product->unit} '{$product->name}' dan mencatat pengeluaran Rp " . number_format($totalCost, 0, ',', '.'));
            $this->closeRestockModal();
            $this->resetPage(); // Refresh list

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal melakukan restock: ' . $e->getMessage());
        }
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

            // Convert empty strings to null for nullable unique/numeric fields
            // to avoid SQLSTATE 23000 duplicate empty-string constraint violations
            $this->productForm['barcode']          = $this->productForm['barcode']          !== '' ? $this->productForm['barcode']          : null;
            $this->productForm['wholesale_price']   = $this->productForm['wholesale_price']   !== '' ? $this->productForm['wholesale_price']   : null;
            $this->productForm['wholesale_min_qty'] = $this->productForm['wholesale_min_qty'] !== '' ? $this->productForm['wholesale_min_qty'] : null;

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

        $fullPath = null;

        try {
            $path = $this->importFile->store('imports');
            // Get absolute path correctly based on default disk driver
            $fullPath = Storage::path($path);
            
            // Process Excel file only
            $result = $this->processExcelImport($fullPath);
            
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
        } finally {
            // Clean up temporary file
            if ($fullPath && file_exists($fullPath)) {
                unlink($fullPath);
            }
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

                // Find or Create category
                $category = Category::firstOrCreate(
                    ['name' => $productData['category_name']],
                    [
                        'slug' => \Illuminate\Support\Str::slug($productData['category_name']),
                        'description' => 'Imported Category',
                        'is_active' => true
                    ]
                );
                
                // Find or Create supplier
                $supplier = Supplier::firstOrCreate(
                    ['name' => $productData['supplier_name']],
                    [
                        'code' => strtoupper(substr($productData['supplier_name'], 0, 3)) . rand(100, 999), 
                        'contact_person' => '-',
                        'phone' => '-',
                        'email' => null,
                        'address' => '-',
                        'is_active' => true
                    ]
                );

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

                    // Find or Create category
                    $category = Category::firstOrCreate(
                        ['name' => $productData['category_name']],
                        [
                            'slug' => \Illuminate\Support\Str::slug($productData['category_name']),
                            'description' => 'Imported Category',
                            'is_active' => true
                        ]
                    );

                    // Find or Create supplier
                    $supplier = Supplier::firstOrCreate(
                        ['name' => $productData['supplier_name']],
                        [
                            'code' => strtoupper(substr($productData['supplier_name'], 0, 3)) . rand(100, 999), 
                            'contact_person' => '-',
                            'phone' => '-',
                            'is_active' => true
                        ]
                    );

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

    // ==================== PRODUCT UNITS MANAGEMENT ====================

    /**
     * Open units management modal for a product
     */
    public function openUnitsModal($productId)
    {
        $this->selectedProductForUnits = Product::with('productUnits')->findOrFail($productId);
        $this->productUnits = $this->selectedProductForUnits->productUnits;
        $this->showUnitsModal = true;
    }

    /**
     * Close units modal
     */
    public function closeUnitsModal()
    {
        $this->showUnitsModal = false;
        $this->selectedProductForUnits = null;
        $this->productUnits = [];
        $this->resetUnitForm();
    }

    /**
     * Open form to add new unit
     */
    public function openAddUnitForm()
    {
        $this->resetUnitForm();
        $this->editingUnitId = null;
        
        // Pre-fill some values from parent product
        // Use getRawOriginal() to bypass faulty decimal cast on the model
        if ($this->selectedProductForUnits) {
            $product = $this->selectedProductForUnits;
            $raw_selling   = $product->getRawOriginal('selling_price');
            $raw_cost      = $product->getRawOriginal('cost_price');
            $raw_wholesale = $product->getRawOriginal('wholesale_price');
            $this->unitForm['selling_price']   = is_numeric($raw_selling)   ? (float) $raw_selling   : 0.0;
            $this->unitForm['cost_price']      = is_numeric($raw_cost)      ? (float) $raw_cost      : 0.0;
            $this->unitForm['wholesale_price'] = is_numeric($raw_wholesale) ? (float) $raw_wholesale : 0.0;
        }
        
        $this->showUnitFormModal = true;
    }

    /**
     * Open form to edit existing unit
     */
    public function openEditUnitForm($unitId)
    {
        $unit = ProductUnit::findOrFail($unitId);
        $this->editingUnitId = $unitId;
        
        $this->unitForm = [
            'id' => $unit->id,
            'unit_name' => $unit->unit_name,
            'conversion_rate' => (int) $unit->conversion_rate,
            'is_base_unit' => $unit->is_base_unit,
            'selling_price' => is_numeric($unit->getRawOriginal('selling_price')) ? (float) $unit->getRawOriginal('selling_price') : 0.0,
            'cost_price'    => is_numeric($unit->getRawOriginal('cost_price'))    ? (float) $unit->getRawOriginal('cost_price')    : 0.0,
            'wholesale_price' => is_numeric($unit->getRawOriginal('wholesale_price')) ? (float) $unit->getRawOriginal('wholesale_price') : 0.0,
            'barcode' => $unit->barcode ?? '',
            'is_active' => $unit->is_active,
            'display_order' => (int) $unit->display_order,
        ];
        
        $this->showUnitFormModal = true;
    }

    /**
     * Close unit form modal
     */
    public function closeUnitFormModal()
    {
        $this->showUnitFormModal = false;
        $this->resetUnitForm();
        $this->editingUnitId = null;
    }

    /**
     * Reset unit form
     */
    protected function resetUnitForm()
    {
        $this->unitForm = [
            'id' => null,
            'unit_name' => '',
            'conversion_rate' => 1,
            'is_sub_unit' => false,
            'is_base_unit' => false,
            'selling_price' => 0.0,
            'cost_price' => 0.0,
            'wholesale_price' => 0.0,
            'barcode' => '',
            'is_active' => true,
            'display_order' => 0,
        ];
    }

    /**
     * Save unit (create or update)
     */
    public function saveUnit()
    {
        // Validate
        $this->validate([
            'unitForm.unit_name' => 'required|string|max:50',
            'unitForm.conversion_rate' => 'required|integer|min:1',
            'unitForm.selling_price' => 'required|numeric|min:0',
            'unitForm.cost_price' => 'nullable|numeric|min:0',
            'unitForm.wholesale_price' => 'nullable|numeric|min:0',
            'unitForm.barcode' => 'nullable|string|max:50',
        ]);

        if (!$this->selectedProductForUnits) {
            session()->flash('error', 'Produk tidak ditemukan!');
            return;
        }

        try {
            $product = $this->selectedProductForUnits->fresh();
            $convRate    = (int) $this->unitForm['conversion_rate'];
            $isSubUnit   = $this->unitForm['is_sub_unit'] ?? false;
            $prodSellPrice = is_numeric($product->getRawOriginal('selling_price')) ? (float) $product->getRawOriginal('selling_price') : 0.0;
            $prodCostPrice = is_numeric($product->getRawOriginal('cost_price'))    ? (float) $product->getRawOriginal('cost_price')    : null;

            // Auto-hitung harga jika user tidak isi manual (selling_price = 0 atau kosong)
            $userSellPrice = is_numeric($this->unitForm['selling_price']) ? (float) $this->unitForm['selling_price'] : 0.0;
            if ($userSellPrice <= 0 && $convRate > 0 && $prodSellPrice > 0) {
                $sellingPrice = $isSubUnit
                    ? round($prodSellPrice / $convRate)
                    : round($prodSellPrice * $convRate);
            } else {
                $sellingPrice = $userSellPrice;
            }

            $costPrice      = is_numeric($this->unitForm['cost_price']) && $this->unitForm['cost_price'] !== '' && (float)$this->unitForm['cost_price'] > 0
                ? (float) $this->unitForm['cost_price'] : null;
            $wholesalePrice = is_numeric($this->unitForm['wholesale_price']) && $this->unitForm['wholesale_price'] !== '' && (float)$this->unitForm['wholesale_price'] > 0
                ? (float) $this->unitForm['wholesale_price'] : null;

            // ==========================================
            // KASUS SUB-UNIT: unit baru LEBIH KECIL dari unit produk
            // Contoh: produk unit=Dus stok=7, tambah pcs (1 Dus=24 pcs)
            // Sistem otomatis: update unit produk ke pcs, stok jadi 168,
            // buat product_unit untuk Dus (konversi=24).
            // ==========================================
            if (!$this->editingUnitId && ($this->unitForm['is_sub_unit'] ?? false)) {
                $oldUnit       = $product->getRawOriginal('unit') ?: $product->unit;
                $oldStock      = (int) $product->stock_quantity;
                $oldSellPrice  = is_numeric($product->getRawOriginal('selling_price')) ? (float) $product->getRawOriginal('selling_price') : 0.0;
                $oldCostPrice  = is_numeric($product->getRawOriginal('cost_price'))    ? (float) $product->getRawOriginal('cost_price')    : null;
                $oldWholesale  = is_numeric($product->getRawOriginal('wholesale_price')) ? (float) $product->getRawOriginal('wholesale_price') : null;

                // Update semua product_units yang ada – gandakan conversion_rate-nya
                ProductUnit::where('product_id', $product->id)
                    ->update(['conversion_rate' => \DB::raw('conversion_rate * ' . $convRate)]);

                // Buat unit untuk satuan LAMA produk (Dus) sebagai unit yang lebih besar
                ProductUnit::firstOrCreate(
                    ['product_id' => $product->id, 'unit_name' => $oldUnit],
                    [
                        'conversion_rate' => $convRate,
                        'is_base_unit'    => false,
                        'selling_price'   => $oldSellPrice,
                        'cost_price'      => $oldCostPrice,
                        'wholesale_price' => $oldWholesale,
                        'is_active'       => true,
                        'display_order'   => 10,
                    ]
                );

                // Buat unit BARU yang lebih kecil (pcs) sebagai base unit
                ProductUnit::firstOrCreate(
                    ['product_id' => $product->id, 'unit_name' => $this->unitForm['unit_name']],
                    [
                        'conversion_rate' => 1,
                        'is_base_unit'    => true,
                        'selling_price'   => $sellingPrice,
                        'cost_price'      => $costPrice,
                        'wholesale_price' => $wholesalePrice,
                        'barcode'         => $this->unitForm['barcode'] ?: null,
                        'is_active'       => true,
                        'display_order'   => 0,
                    ]
                );

                // Update unit & stok produk ke satuan terkecil
                $product->unit           = $this->unitForm['unit_name'];
                $product->stock_quantity = $oldStock * $convRate;
                $product->save();

                // Refresh selectedProductForUnits
                $this->selectedProductForUnits = $product->fresh();
                $this->productUnits = $this->selectedProductForUnits->productUnits;
                $this->closeUnitFormModal();
                session()->flash('message', "Berhasil! Stok produk dikonversi dari {$oldStock} {$oldUnit} menjadi {$product->stock_quantity} {$this->unitForm['unit_name']}.");
                return;
            }

            // ==========================================
            // KASUS NORMAL: unit baru LEBIH BESAR dari unit produk
            // Contoh: produk unit=pcs stok=168, tambah Dus (1 Dus=24 pcs)
            // ==========================================
            $data = [
                'product_id'      => $product->id,
                'unit_name'       => $this->unitForm['unit_name'],
                'conversion_rate' => $convRate,
                'is_base_unit'    => $this->unitForm['is_base_unit'] ?? false,
                'selling_price'   => $sellingPrice,
                'cost_price'      => $costPrice,
                'wholesale_price' => $wholesalePrice,
                'barcode'         => $this->unitForm['barcode'] ?: null,
                'is_active'       => $this->unitForm['is_active'] ?? true,
                'display_order'   => (int) ($this->unitForm['display_order'] ?? 0),
            ];

            if ($this->editingUnitId) {
                $unit = ProductUnit::findOrFail($this->editingUnitId);
                $unit->update($data);
                session()->flash('message', 'Unit berhasil diupdate!');
            } else {
                ProductUnit::create($data);
                session()->flash('message', 'Unit berhasil ditambahkan!');
            }

            $this->selectedProductForUnits = $product->fresh();
            $this->productUnits = $this->selectedProductForUnits->productUnits;
            $this->closeUnitFormModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menyimpan unit: ' . $e->getMessage());
        }
    }

    /**
     * Delete a unit
     */
    public function deleteUnit($unitId)
    {
        try {
            $unit = ProductUnit::findOrFail($unitId);
            $unit->delete();
            
            session()->flash('message', 'Unit berhasil dihapus!');
            
            // Refresh units list
            if ($this->selectedProductForUnits) {
                $this->productUnits = $this->selectedProductForUnits->fresh()->productUnits;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus unit: ' . $e->getMessage());
        }
    }

    /**
     * Toggle unit active status
     */
    public function toggleUnitStatus($unitId)
    {
        try {
            $unit = ProductUnit::findOrFail($unitId);
            $unit->is_active = !$unit->is_active;
            $unit->save();
            
            // Refresh units list
            if ($this->selectedProductForUnits) {
                $this->productUnits = $this->selectedProductForUnits->fresh()->productUnits;
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal mengubah status unit: ' . $e->getMessage());
        }
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
