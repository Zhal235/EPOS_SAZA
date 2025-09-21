<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Products extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $stockFilter = '';
    public $sortBy = 'name';
    public $sortDirection = 'asc';

    protected $updatesQueryString = [
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => ''],
        'stockFilter' => ['except' => '']
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
            'totalProducts' => Product::count(),
            'lowStockCount' => Product::lowStock()->count(),
            'outOfStockCount' => Product::where('stock_quantity', 0)->count(),
            'totalValue' => Product::selectRaw('SUM(stock_quantity * cost_price) as total')->value('total') ?? 0
        ])->layout('layouts.epos', [
            'header' => 'Products Management'
        ]);
    }
}
