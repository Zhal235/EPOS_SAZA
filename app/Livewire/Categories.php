<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class Categories extends Component
{
    use WithPagination;

    // Tab state
    public $activeTab = 'categories';

    // Modal properties for Categories
    public $showAddCategoryModal = false;
    public $showEditCategoryModal = false;
    public $showDeleteCategoryModal = false;

    // Modal properties for Suppliers
    public $showAddSupplierModal = false;
    public $showEditSupplierModal = false;
    public $showDeleteSupplierModal = false;

    // Form properties for Categories
    public $categoryForm = [
        'name' => '',
        'description' => '',
        'icon' => 'fas fa-tag',
        'color' => '#6366F1',
        'is_active' => true
    ];

    // Form properties for Suppliers
    public $supplierForm = [
        'name' => '',
        'contact_person' => '',
        'email' => '',
        'phone' => '',
        'address' => '',
        'is_active' => true
    ];

    public $selectedCategory = null;
    public $selectedSupplier = null;
    public $categoryToDelete = null;
    public $supplierToDelete = null;

    // Search and filters
    public $search = '';
    public $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'activeTab' => ['except' => 'categories']
    ];

    public function rules()
    {
        if ($this->activeTab === 'categories') {
            return [
                'categoryForm.name' => 'required|string|max:255|unique:categories,name,' . ($this->selectedCategory->id ?? 'NULL'),
                'categoryForm.description' => 'nullable|string|max:500',
                'categoryForm.icon' => 'required|string|max:100',
                'categoryForm.color' => 'required|string|max:7',
                'categoryForm.is_active' => 'boolean'
            ];
        } else {
            return [
                'supplierForm.name' => 'required|string|max:255|unique:suppliers,name,' . ($this->selectedSupplier->id ?? 'NULL'),
                'supplierForm.contact_person' => 'required|string|max:255',
                'supplierForm.email' => 'nullable|email|max:255',
                'supplierForm.phone' => 'required|string|max:20',
                'supplierForm.address' => 'nullable|string|max:500',
                'supplierForm.is_active' => 'boolean'
            ];
        }
    }

    public function mount()
    {
        $this->resetForms();
    }

    public function resetForms()
    {
        $this->categoryForm = [
            'name' => '',
            'description' => '',
            'icon' => 'fas fa-tag',
            'color' => '#6366F1',
            'is_active' => true
        ];
        
        $this->supplierForm = [
            'name' => '',
            'contact_person' => '',
            'email' => '',
            'phone' => '',
            'address' => '',
            'is_active' => true
        ];
        
        $this->selectedCategory = null;
        $this->selectedSupplier = null;
        $this->resetValidation();
    }

    // Tab switching
    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    // ======== CATEGORY METHODS ========
    public function openAddCategoryModal()
    {
        $this->resetForms();
        $this->showAddCategoryModal = true;
    }

    public function closeAddCategoryModal()
    {
        $this->showAddCategoryModal = false;
        $this->resetForms();
    }

    public function saveCategory()
    {
        $this->validate();

        try {
            Category::create($this->categoryForm);
            session()->flash('message', 'Kategori berhasil dibuat!');
            $this->closeAddCategoryModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat kategori: ' . $e->getMessage());
        }
    }

    public function openEditCategoryModal($categoryId)
    {
        $this->selectedCategory = Category::findOrFail($categoryId);
        $this->categoryForm = [
            'name' => $this->selectedCategory->name,
            'description' => $this->selectedCategory->description,
            'icon' => $this->selectedCategory->icon,
            'color' => $this->selectedCategory->color,
            'is_active' => $this->selectedCategory->is_active
        ];
        $this->showEditCategoryModal = true;
    }

    public function closeEditCategoryModal()
    {
        $this->showEditCategoryModal = false;
        $this->resetForms();
    }

    public function updateCategory()
    {
        $this->validate();

        try {
            $this->selectedCategory->update($this->categoryForm);
            session()->flash('message', 'Kategori berhasil diperbarui!');
            $this->closeEditCategoryModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui kategori: ' . $e->getMessage());
        }
    }

    public function confirmDeleteCategory($categoryId)
    {
        $this->categoryToDelete = Category::findOrFail($categoryId);
        $this->showDeleteCategoryModal = true;
    }

    public function closeDeleteCategoryModal()
    {
        $this->showDeleteCategoryModal = false;
        $this->categoryToDelete = null;
    }

    public function deleteCategory()
    {
        try {
            if ($this->categoryToDelete->products()->count() > 0) {
                session()->flash('error', 'Tidak dapat menghapus kategori yang memiliki produk. Silakan pindahkan produk terlebih dahulu.');
                $this->closeDeleteCategoryModal();
                return;
            }

            $this->categoryToDelete->delete();
            session()->flash('message', 'Kategori berhasil dihapus!');
            $this->closeDeleteCategoryModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus kategori: ' . $e->getMessage());
        }
    }

    // ======== SUPPLIER METHODS ========
    public function openAddSupplierModal()
    {
        $this->resetForms();
        $this->showAddSupplierModal = true;
    }

    public function closeAddSupplierModal()
    {
        $this->showAddSupplierModal = false;
        $this->resetForms();
    }

    public function saveSupplier()
    {
        $this->validate();

        try {
            Supplier::create($this->supplierForm);
            session()->flash('message', 'Supplier berhasil dibuat!');
            $this->closeAddSupplierModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat supplier: ' . $e->getMessage());
        }
    }

    public function openEditSupplierModal($supplierId)
    {
        $this->selectedSupplier = Supplier::findOrFail($supplierId);
        $this->supplierForm = [
            'name' => $this->selectedSupplier->name,
            'contact_person' => $this->selectedSupplier->contact_person,
            'email' => $this->selectedSupplier->email,
            'phone' => $this->selectedSupplier->phone,
            'address' => $this->selectedSupplier->address,
            'is_active' => $this->selectedSupplier->is_active
        ];
        $this->showEditSupplierModal = true;
    }

    public function closeEditSupplierModal()
    {
        $this->showEditSupplierModal = false;
        $this->resetForms();
    }

    public function updateSupplier()
    {
        $this->validate();

        try {
            $this->selectedSupplier->update($this->supplierForm);
            session()->flash('message', 'Supplier berhasil diperbarui!');
            $this->closeEditSupplierModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal memperbarui supplier: ' . $e->getMessage());
        }
    }

    public function confirmDeleteSupplier($supplierId)
    {
        $this->supplierToDelete = Supplier::findOrFail($supplierId);
        $this->showDeleteSupplierModal = true;
    }

    public function closeDeleteSupplierModal()
    {
        $this->showDeleteSupplierModal = false;
        $this->supplierToDelete = null;
    }

    public function deleteSupplier()
    {
        try {
            if ($this->supplierToDelete->products()->count() > 0) {
                session()->flash('error', 'Tidak dapat menghapus supplier yang memiliki produk. Silakan pindahkan produk terlebih dahulu.');
                $this->closeDeleteSupplierModal();
                return;
            }

            $this->supplierToDelete->delete();
            session()->flash('message', 'Supplier berhasil dihapus!');
            $this->closeDeleteSupplierModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus supplier: ' . $e->getMessage());
        }
    }

    public function render()
    {
        if ($this->activeTab === 'categories') {
            $categories = Category::query()
                ->where('outlet_type', 'store') // sembunyikan kategori foodcourt
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('description', 'like', '%' . $this->search . '%');
                })
                ->when($this->statusFilter === 'active', function ($query) {
                    $query->where('is_active', true);
                })
                ->when($this->statusFilter === 'inactive', function ($query) {
                    $query->where('is_active', false);
                })
                ->orderBy('name')
                ->paginate(12);

            $totalCategories = Category::where('outlet_type', 'store')->count();
            $activeCategories = Category::where('outlet_type', 'store')->where('is_active', true)->count();

            return view('livewire.categories', [
                'categories' => $categories,
                'suppliers' => collect(),
                'totalCategories' => $totalCategories,
                'activeCategories' => $activeCategories,
                'totalSuppliers' => 0,
                'activeSuppliers' => 0,
            ])->layout('layouts.epos', ['header' => 'Categories & Suppliers']);
        } else {
            $suppliers = Supplier::query()
                ->where('is_tenant_supplier', false) // sembunyikan supplier dummy tenant
                ->when($this->search, function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('contact_person', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%');
                })
                ->when($this->statusFilter === 'active', function ($query) {
                    $query->where('is_active', true);
                })
                ->when($this->statusFilter === 'inactive', function ($query) {
                    $query->where('is_active', false);
                })
                ->orderBy('name')
                ->paginate(12);

            $totalSuppliers = Supplier::where('is_tenant_supplier', false)->count();
            $activeSuppliers = Supplier::where('is_tenant_supplier', false)->where('is_active', true)->count();

            return view('livewire.categories', [
                'categories' => collect(),
                'suppliers' => $suppliers,
                'totalCategories' => 0,
                'activeCategories' => 0,
                'totalSuppliers' => $totalSuppliers,
                'activeSuppliers' => $activeSuppliers,
            ])->layout('layouts.epos', ['header' => 'Kategori & Supplier']);
        }
    }
}
