<div>
    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Tenant</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ $this->tenants->total() }}</h3>
                </div>
                <div class="bg-orange-100 rounded-full p-3">
                    <i class="fas fa-store text-orange-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Tenant Aktif</p>
                    <h3 class="text-2xl font-bold text-green-600 mt-1">{{ App\Models\Tenant::active()->count() }}</h3>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Total Menu</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ App\Models\Product::foodcourt()->count() }}</h3>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <i class="fas fa-utensils text-blue-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase">Menu Aktif</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ App\Models\Product::foodcourt()->active()->count() }}</h3>
                </div>
                <div class="bg-amber-100 rounded-full p-3">
                    <i class="fas fa-utensils text-amber-600"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Actions --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="relative flex-1 max-w-md">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Cari nama tenant, nomor booth, atau pemilik..."
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
            </div>
            <button wire:click="openCreateModal"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i>Tambah Tenant
            </button>
        </div>
    </div>

    {{-- Tenant Table --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gradient-to-r from-orange-500 to-amber-500">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Booth</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Nama Tenant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">Pemilik</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Jumlah Menu</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($this->tenants as $tenant)
                        <tr class="hover:bg-orange-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center justify-center w-10 h-10 bg-orange-100 rounded-lg font-bold text-orange-700 text-sm">
                                    {{ $tenant->booth_number ?? '#' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-semibold text-gray-900">{{ $tenant->name }}</p>
                                @if($tenant->description)
                                    <p class="text-sm text-gray-500 mt-0.5 truncate max-w-xs">{{ $tenant->description }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <p class="text-sm text-gray-900">{{ $tenant->owner_name ?? '-' }}</p>
                                @if($tenant->phone)
                                    <p class="text-xs text-gray-500 mt-0.5"><i class="fas fa-phone text-orange-400 mr-1"></i>{{ $tenant->phone }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    <i class="fas fa-utensils mr-1"></i>{{ $tenant->products_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button wire:click="toggleTenantActive({{ $tenant->id }})"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold transition-colors
                                        {{ $tenant->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $tenant->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                                    {{ $tenant->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openProductModal({{ $tenant->id }})"
                                            title="Kelola Menu"
                                            class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-sm">
                                        <i class="fas fa-utensils"></i>
                                    </button>
                                    <button wire:click="openEditModal({{ $tenant->id }})"
                                            title="Edit Tenant"
                                            class="p-2 text-white bg-yellow-500 hover:bg-yellow-600 rounded-lg transition-colors shadow-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="deleteTenant({{ $tenant->id }})"
                                            wire:confirm="Hapus tenant {{ $tenant->name }}?"
                                            title="Hapus"
                                            class="p-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center bg-white">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
                                    <i class="fas fa-store text-3xl text-orange-500"></i>
                                </div>
                                <p class="text-gray-900 font-medium">Belum ada tenant</p>
                                <p class="text-orange-600 text-sm mt-1">Klik "Tambah Tenant" untuk mendaftar booth foodcourt</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 bg-gradient-to-r from-orange-50 to-amber-50 border-t border-orange-100">
            {{ $this->tenants->links() }}
        </div>
    </div>

    {{--  Modal: Tambah / Edit Tenant  --}}
    @if ($showTenantModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showTenantModal', false)"></div>
                <div class="relative inline-block w-full max-w-sm px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:p-6">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">
                            <i class="fas {{ $editingTenantId ? 'fa-edit' : 'fa-plus' }} text-orange-500 mr-2"></i>
                            {{ $editingTenantId ? 'Edit Tenant' : 'Tambah Tenant Baru' }}
                        </h3>
                        <button wire:click="$set('showTenantModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    {{-- Form --}}
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Tenant <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="tenantName" placeholder="Nama warung / gerai"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                                @error('tenantName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Booth</label>
                                <input type="text" wire:model="boothNumber" placeholder="misal: A1, B3"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Telepon</label>
                                <input type="text" wire:model="phone" placeholder="08xx..."
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pemilik</label>
                                <input type="text" wire:model="ownerName" placeholder="Nama pemilik / pengelola"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                <textarea wire:model="description" rows="2" placeholder="Menu utama, keterangan tambahan..."
                                          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Urutan Tampil</label>
                                <input type="number" wire:model="sortOrder" min="0"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            </div>
                            <div class="flex items-end pb-1">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" wire:model="tenantIsActive" class="rounded text-orange-500 focus:ring-orange-500">
                                    <span class="text-sm text-gray-700 font-medium">Tenant Aktif</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    {{-- Footer --}}
                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                        <button wire:click="$set('showTenantModal', false)"
                                class="px-4 py-2.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="saveTenant"
                                class="px-6 py-2.5 text-sm font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-all">
                            <i class="fas fa-save mr-2"></i>Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{--  Modal: Kelola Menu Tenant  --}}
    @if ($showProductModal && $this->selectedTenant)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showProductModal', false)"></div>
                <div class="relative inline-block w-full max-w-2xl px-4 pt-5 pb-4 overflow-hidden text-left align-bottom bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:p-6">
                    {{-- Header --}}
                    <div class="flex items-center justify-between mb-1 pb-4 border-b border-gray-200">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">
                                <i class="fas fa-utensils text-orange-500 mr-2"></i>
                                Menu  {{ $this->selectedTenant->name }}
                                @if($this->selectedTenant->booth_number)
                                    <span class="text-sm font-normal text-gray-500">(Booth {{ $this->selectedTenant->booth_number }})</span>
                                @endif
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Komisi ditetapkan per menu  bukan di level tenant</p>
                        </div>
                        <button wire:click="$set('showProductModal', false)" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    {{-- Search + Toggle form --}}
                    <div class="flex items-center gap-2 my-4">
                        <div class="relative flex-1">
                            <input type="text" wire:model.live.debounce.300ms="productSearch"
                                   placeholder="Cari menu..."
                                   class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500">
                            <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                        </div>
                        <button wire:click="$toggle('showNewProductForm')"
                                class="px-4 py-2 text-sm font-medium rounded-lg transition
                                {{ $showNewProductForm
                                    ? 'bg-gray-200 text-gray-700 hover:bg-gray-300'
                                    : 'bg-orange-500 hover:bg-orange-600 text-white shadow' }}">
                            <i class="fas {{ $showNewProductForm ? 'fa-times' : 'fa-plus' }} mr-1"></i>
                            {{ $showNewProductForm ? 'Batal' : 'Tambah Menu' }}
                        </button>
                    </div>

                    {{-- Form tambah menu baru --}}
                    @if ($showNewProductForm)
                        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
                            <p class="text-xs font-semibold text-orange-700 uppercase mb-3">
                                <i class="fas fa-plus-circle mr-1"></i>Menu Baru untuk {{ $this->selectedTenant->name }}
                            </p>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model="newProductName" placeholder="misal: Nasi Goreng Spesial"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                    @error('newProductName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                                    <input type="number" wire:model="newProductPrice" min="0"
                                           placeholder="misal: 15000"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                    @error('newProductPrice') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Komisi <span class="text-red-500">*</span></label>
                                        <select wire:model="newProductCommissionType"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                            <option value="fixed">Nominal Tetap (Rp)</option>
                                            <option value="percentage">Persentase (%)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Nilai Komisi {{ $newProductCommissionType === 'percentage' ? '(%)' : '(Rp)' }} <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" wire:model="newProductCommissionValue" min="0" step="0.01"
                                               placeholder="{{ $newProductCommissionType === 'percentage' ? '20' : '2000' }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                        @error('newProductCommissionValue') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                                @if ($newProductPrice > 0 && $newProductCommissionValue > 0)
                                    @php
                                        $commCalc = $newProductCommissionType === 'percentage'
                                            ? $newProductPrice * $newProductCommissionValue / 100
                                            : $newProductCommissionValue;
                                        $tenantGets = $newProductPrice - $commCalc;
                                    @endphp
                                    <div class="bg-white border border-orange-200 rounded-lg px-3 py-2 text-xs text-gray-600 flex gap-4">
                                        <span>Harga jual: <strong class="text-gray-800">Rp {{ number_format($newProductPrice, 0, ',', '.') }}</strong></span>
                                        <span>Komisi: <strong class="text-indigo-700">Rp {{ number_format($commCalc, 0, ',', '.') }}</strong></span>
                                        <span>Tenant dapat: <strong class="text-green-700">Rp {{ number_format($tenantGets, 0, ',', '.') }}</strong></span>
                                    </div>
                                @endif
                                
                                {{-- Stock / BKL Option --}}
                                <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <label class="flex items-center gap-2 cursor-pointer mb-2">
                                        <input type="checkbox" wire:model.live="newProductTrackStock" class="rounded text-orange-500 focus:ring-orange-500">
                                        <span class="text-sm font-medium text-gray-700">Lacak Stok (BKL)</span>
                                    </label>
                                    @if($newProductTrackStock)
                                        <div>
                                            <input type="number" wire:model="newProductStock" min="0" placeholder="Stok awal"
                                                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                        </div>
                                    @endif
                                </div>

                                <div class="flex justify-end">
                                    <button wire:click="createProductForTenant"
                                            class="px-5 py-2 text-sm font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-all">
                                        <i class="fas fa-save mr-1"></i> Simpan Menu
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Daftar menu --}}
                    <div class="max-h-72 overflow-y-auto">
                        @forelse ($this->tenantProducts as $product)
                            <div class="flex items-center justify-between {{ $product->is_active ? 'bg-white hover:bg-orange-50' : 'bg-gray-50 opacity-60' }} border border-gray-200 rounded-lg px-4 py-3 mb-2 transition-colors">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-gray-900 text-sm">{{ $product->name }}</p>
                                        @if(!$product->is_active)
                                            <span class="text-xs bg-gray-200 text-gray-500 px-1.5 py-0.5 rounded">nonaktif</span>
                                        @endif
                                        @if($product->track_stock)
                                            <span class="text-xs bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Stok: {{ $product->stock_quantity }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-xs text-gray-500">Harga: <strong class="text-gray-800">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</strong></span>
                                        <span class="text-xs text-gray-500">Komisi: <strong class="text-indigo-700">{{ $product->effective_commission_label }}</strong></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 shrink-0 ml-3">
                                    <button wire:click="openEditProductModal({{ $product->id }})"
                                            title="Edit Menu"
                                            class="p-2 text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors shadow-sm">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <button wire:click="deleteProduct({{ $product->id }})"
                                            wire:confirm="Hapus menu '{{ $product->name }}' secara permanen?"
                                            title="Hapus Menu"
                                            class="p-2 text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm">
                                        <i class="fas fa-trash text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-10">
                                <div class="inline-flex items-center justify-center w-12 h-12 bg-orange-100 rounded-full mb-3">
                                    <i class="fas fa-utensils text-xl text-orange-500"></i>
                                </div>
                                <p class="text-gray-500 text-sm">Belum ada menu. Klik <strong>Tambah Menu</strong> di atas.</p>
                            </div>
                        @endforelse
                    </div>
                    <div class="flex justify-end mt-4 pt-4 border-t border-gray-200">
                        <button wire:click="$set('showProductModal', false)"
                                class="px-4 py-2.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{--  Modal: Edit Menu Tenant  --}}
    @if ($showEditProductModal)
        <div class="fixed inset-0 z-[60] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showEditProductModal', false)"></div>
                <div class="relative inline-block w-full max-w-lg px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-xl shadow-xl sm:my-8 sm:align-middle sm:p-6">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-edit text-amber-500 mr-2"></i>Edit Menu
                        </h3>
                        <button wire:click="$set('showEditProductModal', false)" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="editingProductName"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            @error('editingProductName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="editingProductPrice" min="0"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                            @error('editingProductPrice') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Komisi</label>
                                <select wire:model="editingProductCommissionType"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500">
                                    <option value="fixed">Nominal (Rp)</option>
                                    <option value="percentage">Persentase (%)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nilai Komisi</label>
                                <input type="number" wire:model="editingProductCommissionValue" min="0" step="0.01"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 space-y-3">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model="editingProductIsActive" class="rounded text-orange-500 focus:ring-orange-500">
                                <span class="text-sm font-medium text-gray-700">Menu Aktif (Tampil di POS)</span>
                            </label>
                            
                            <hr class="border-gray-200">

                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="editingProductTrackStock" class="rounded text-orange-500 focus:ring-orange-500">
                                <span class="text-sm font-medium text-gray-700">Lacak Stok (BKL)</span>
                            </label>

                            @if($editingProductTrackStock)
                                <div>
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Stok Saat Ini</label>
                                    <input type="number" wire:model="editingProductStock" min="0"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-orange-500">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-200">
                        <button wire:click="$set('showEditProductModal', false)"
                                class="px-4 py-2.5 text-sm font-medium border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button wire:click="updateProduct"
                                class="px-6 py-2.5 text-sm font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg shadow transition-all">
                            <i class="fas fa-save mr-2"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
