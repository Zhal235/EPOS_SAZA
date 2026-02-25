<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class TenantManagement extends Component
{
    use WithPagination;

    // List filters
    public string $search = '';

    // Create / Edit Tenant form
    public bool $showTenantModal = false;
    public ?int $editingTenantId = null;
    public string $tenantName = '';
    public string $tenantSlug = '';
    public string $boothNumber = '';
    public string $ownerName = '';
    public string $phone = '';
    public string $description = '';
    public bool $tenantIsActive = true;
    public int $sortOrder = 0;

    // Product modal
    public bool $showProductModal = false;
    public ?int $selectedTenantId = null;
    public string $productSearch = '';

    // New product form (name + selling price + commission only)
    public bool $showNewProductForm = false;
    public string $newProductName = '';
    public float|string $newProductPrice = 0;
    public string $newProductCommissionType = 'fixed';
    public float|string $newProductCommissionValue = 0;
    public bool $newProductTrackStock = false;
    public int $newProductStock = 0;

    // Edit product form properties
    public bool $showEditProductModal = false;
    public string $editingProductName = '';
    public float|string $editingProductPrice = 0;
    public string $editingProductCommissionType = 'fixed';
    public float|string $editingProductCommissionValue = 0;
    public bool $editingProductIsActive = true;
    public bool $editingProductTrackStock = false;
    public int $editingProductStock = 0;

    // Per-product commission override modal (deprecated, merged into edit)
    // public bool $showCommissionModal = false; 
    public ?int $editingProductId = null;
    // public string $productCommissionType = 'fixed';
    // public float|string $productCommissionValue = 0;

    protected $updatesQueryString = [
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'tenantName'     => 'required|string|max:100',
            'boothNumber'    => 'nullable|string|max:20',
            'ownerName'      => 'nullable|string|max:100',
            'phone'          => 'nullable|string|max:20',
            'description'    => 'nullable|string|max:500',
            'tenantIsActive' => 'boolean',
            'sortOrder'      => 'integer|min:0',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ─── Tenant CRUD ──────────────────────────────────────────────────────────

    public function openCreateModal(): void
    {
        $this->resetTenantForm();
        $this->showTenantModal = true;
    }

    public function openEditModal(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);

        $this->editingTenantId = $tenant->id;
        $this->tenantName      = $tenant->name;
        $this->tenantSlug      = $tenant->slug;
        $this->boothNumber     = $tenant->booth_number ?? '';
        $this->ownerName       = $tenant->owner_name ?? '';
        $this->phone           = $tenant->phone ?? '';
        $this->description     = $tenant->description ?? '';
        $this->tenantIsActive  = $tenant->is_active;
        $this->sortOrder       = $tenant->sort_order;

        $this->showTenantModal = true;
    }

    public function saveTenant(): void
    {
        $this->validate();

        $data = [
            'name'         => $this->tenantName,
            'slug'         => \Illuminate\Support\Str::slug($this->tenantName),
            'booth_number' => $this->boothNumber ?: null,
            'owner_name'   => $this->ownerName ?: null,
            'phone'        => $this->phone ?: null,
            'description'  => $this->description ?: null,
            'is_active'    => $this->tenantIsActive,
            'sort_order'   => $this->sortOrder,
        ];

        if ($this->editingTenantId) {
            Tenant::findOrFail($this->editingTenantId)->update($data);
            $msg = 'Tenant berhasil diperbarui!';
        } else {
            Tenant::create($data);
            $msg = 'Tenant berhasil ditambahkan!';
        }

        $this->showTenantModal = false;
        $this->resetTenantForm();
        $this->dispatch('showNotification', ['type' => 'success', 'title' => '✅ Berhasil', 'message' => $msg]);
    }

    public function toggleTenantActive(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);
        $tenant->update(['is_active' => !$tenant->is_active]);

        $label = $tenant->is_active ? 'dinonaktifkan' : 'diaktifkan';
        $this->dispatch('showNotification', [
            'type' => 'info',
            'title' => 'Status Diubah',
            'message' => "Tenant {$tenant->name} berhasil {$label}.",
        ]);
    }

    public function deleteTenant(int $tenantId): void
    {
        $tenant = Tenant::findOrFail($tenantId);

        if ($tenant->products()->where('is_active', true)->exists()) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Tidak Bisa Dihapus',
                'message' => 'Tenant masih memiliki produk aktif. Nonaktifkan atau pindahkan produk terlebih dahulu.',
            ]);
            return;
        }

        $name = $tenant->name;
        // Unlink products first
        $tenant->products()->update(['tenant_id' => null, 'outlet_type' => 'store']);
        $tenant->delete();

        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => '🗑️ Dihapus',
            'message' => "Tenant {$name} berhasil dihapus.",
        ]);
    }

    private function resetTenantForm(): void
    {
        $this->editingTenantId = null;
        $this->tenantName      = '';
        $this->tenantSlug      = '';
        $this->boothNumber     = '';
        $this->ownerName       = '';
        $this->phone           = '';
        $this->description     = '';
        $this->tenantIsActive  = true;
        $this->sortOrder       = 0;
        $this->resetValidation();
    }

    // ─── Product Assignment ───────────────────────────────────────────────────

    public function openProductModal(int $tenantId): void
    {
        $this->selectedTenantId      = $tenantId;
        $this->productSearch         = '';
        $this->showNewProductForm    = false;
        $this->showProductModal      = true;
    }

    public function unassignProduct(int $productId): void
    {
        // Deactivate the foodcourt product (don't convert to store - they're different)
        Product::findOrFail($productId)->update(['is_active' => false]);

        $this->dispatch('showNotification', [
            'type' => 'info',
            'title' => 'Produk Dinonaktifkan',
            'message' => 'Produk foodcourt dinonaktifkan.',
        ]);
    }

    public function createProductForTenant(): void
    {
        $this->validate([
            'newProductName'             => 'required|string|max:200',
            'newProductPrice'            => 'required|numeric|min:0',
            'newProductCommissionType'   => 'required|in:fixed,percentage',
            'newProductCommissionValue'  => 'required|numeric|min:0',
            'newProductStock'            => 'required_if:newProductTrackStock,true|integer|min:0',
        ]);

        if (!$this->selectedTenantId) return;

        $tenant = Tenant::findOrFail($this->selectedTenantId);

        // Auto-create Foodcourt category & supplier jika belum ada
        // outlet_type='foodcourt' agar tidak muncul di kategori toko
        $category = Category::firstOrCreate(
            ['slug' => 'foodcourt'],
            ['name' => 'Foodcourt', 'is_active' => true, 'outlet_type' => 'foodcourt']
        );
        $supplier = Supplier::firstOrCreate(
            ['name' => 'Tenant ' . $tenant->name],
            ['contact_person' => $tenant->owner_name ?? '-', 'is_tenant_supplier' => true]
        );

        Product::create([
            'name'             => $this->newProductName,
            'outlet_type'      => 'foodcourt',
            'tenant_id'        => $tenant->id,
            'selling_price'    => (float) $this->newProductPrice,
            'cost_price'       => 0,
            'stock_quantity'   => $this->newProductTrackStock ? $this->newProductStock : 0,
            'category_id'      => $category->id,
            'supplier_id'      => $supplier->id,
            'unit'             => 'porsi',
            'track_stock'      => $this->newProductTrackStock,
            'is_active'        => true,
            'commission_type'  => $this->newProductCommissionType,
            'commission_value' => (float) $this->newProductCommissionValue,
        ]);

        $this->newProductName            = '';
        $this->newProductPrice           = 0;
        $this->newProductCommissionType  = 'fixed';
        $this->newProductCommissionValue = 0;
        $this->newProductTrackStock      = false;
        $this->newProductStock           = 0;
        $this->showNewProductForm        = false;

        $this->dispatch('showNotification', [
            'type'    => 'success',
            'title'   => '✅ Menu Ditambahkan',
            'message' => 'Menu berhasil ditambahkan ke tenant.',
        ]);
    }

    // ─── Edit & Delete Product ──────────────────────────────────────────────

    public function openEditProductModal(int $productId): void
    {
        $product = Product::findOrFail($productId);
        
        $this->editingProductId              = $product->id;
        $this->editingProductName            = $product->name;
        $this->editingProductPrice           = $product->selling_price;
        $this->editingProductCommissionType  = $product->commission_type ?? 'fixed';
        $this->editingProductCommissionValue = $product->commission_value ?? 0;
        $this->editingProductIsActive        = $product->is_active;
        $this->editingProductTrackStock      = $product->track_stock;
        $this->editingProductStock           = $product->stock_quantity;
        
        $this->showEditProductModal = true;
    }

    public function updateProduct(): void
    {
        $this->validate([
            'editingProductName'             => 'required|string|max:200',
            'editingProductPrice'            => 'required|numeric|min:0',
            'editingProductCommissionType'   => 'required|in:fixed,percentage',
            'editingProductCommissionValue'  => 'required|numeric|min:0',
            'editingProductStock'            => 'required_if:editingProductTrackStock,true|integer|min:0',
        ]);

        $product = Product::findOrFail($this->editingProductId);
        
        $product->update([
            'name'             => $this->editingProductName,
            'selling_price'    => (float) $this->editingProductPrice,
            'commission_type'  => $this->editingProductCommissionType,
            'commission_value' => (float) $this->editingProductCommissionValue,
            'is_active'        => $this->editingProductIsActive,
            'track_stock'      => $this->editingProductTrackStock,
            'stock_quantity'   => $this->editingProductTrackStock ? $this->editingProductStock : 0,
        ]);

        $this->showEditProductModal = false;
        $this->editingProductId = null;

        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => '✅ Menu Diperbarui',
            'message' => 'Detail menu berhasil diperbarui.',
        ]);
    }

    public function deleteProduct(int $productId): void
    {
        $product = Product::findOrFail($productId);
        
        // Cek riwayat transaksi (opsional, bisa gunakan soft delete)
        // if ($product->transactionItems()->exists()) { ... }
        
        $product->delete();

        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => '🗑️ Dihapus',
            'message' => 'Menu berhasil dihapus.',
        ]);
    }
    
    // Deprecated methods removed (openCommissionModal, saveProductCommission, clearProductCommission)

    // ─── Computed properties ──────────────────────────────────────────────────

    public function getTenantsProperty()
    {
        return Tenant::withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('booth_number', 'like', '%' . $this->search . '%')
                ->orWhere('owner_name', 'like', '%' . $this->search . '%'))
            ->ordered()
            ->paginate(15);
    }

    public function getSelectedTenantProperty(): ?Tenant
    {
        return $this->selectedTenantId ? Tenant::find($this->selectedTenantId) : null;
    }

    public function getTenantProductsProperty()
    {
        if (!$this->selectedTenantId) return collect();

        return Product::with('category')
            ->where('tenant_id', $this->selectedTenantId)
            ->where('outlet_type', 'foodcourt') // hanya produk foodcourt milik tenant ini
            ->when($this->productSearch, fn ($q) => $q->where('name', 'like', '%' . $this->productSearch . '%'))
            ->orderBy('name')
            ->get();
    }

    public function getUnassignedProductsProperty()
    {
        // Tidak dipakai lagi - produk toko tidak bisa di-assign ke tenant
        return collect();
    }

    public function getCategoriesProperty()
    {
        return Category::active()->ordered()->get();
    }

    public function render()
    {
        return view('livewire.tenant-management')
            ->layout('layouts.epos', ['header' => 'Tenant Foodcourt']);
    }
}
