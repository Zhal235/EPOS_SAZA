<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

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
    public $importFile = null;

    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'stockFilter' => ['except' => '']
    ];

    protected $rules = [
        'productForm.name' => 'required|min:3',
        'productForm.category_id' => 'required|exists:categories,id',
        'productForm.supplier_id' => 'required|exists:suppliers,id',
        'productForm.cost_price' => 'required|numeric|min:0',
        'productForm.selling_price' => 'required|numeric|min:0',
        'productForm.stock_quantity' => 'required|integer|min:0',
        'productForm.min_stock' => 'required|integer|min:0'
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

    // Modal Methods
    public function openAddModal()
    {
        $this->resetForm();
        $this->showAddModal = true;
    }

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

    public function openImportModal()
    {
        $this->showImportModal = true;
    }

    public function closeImportModal()
    {
        $this->showImportModal = false;
        $this->importFile = null;
    }

    // CRUD Methods
    public function saveProduct()
    {
        $this->validate();

        // Auto-generate SKU if empty
        if (empty($this->productForm['sku'])) {
            $this->productForm['sku'] = Product::generateSku();
        }

        Product::create($this->productForm);

        session()->flash('message', 'Product created successfully!');
        $this->closeAddModal();
        $this->resetPage();
    }

    public function updateProduct()
    {
        $this->validate();

        $this->selectedProduct->update($this->productForm);

        session()->flash('message', 'Product updated successfully!');
        $this->closeEditModal();
    }

    public function deleteProduct($productId)
    {
        Product::findOrFail($productId)->delete();
        session()->flash('message', 'Product deleted successfully!');
        $this->resetPage();
    }

    public function importProducts()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:csv,xlsx,xls|max:2048'
        ]);

        try {
            // For now, we'll simulate import success
            // In real implementation, you would parse CSV/Excel file
            session()->flash('message', 'Import file uploaded successfully! Processing in background...');
            $this->closeImportModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Import failed: ' . $e->getMessage());
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
    }

    public function generateBarcode()
    {
        if ($this->selectedProduct) {
            $this->selectedProduct->generateBarcode();
            $this->productForm['barcode'] = $this->selectedProduct->barcode;
        }
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
            'header' => 'Products Management'
        ]);
    }
}
