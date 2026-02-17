<div>
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <h1 class="text-2xl font-semibold text-gray-900">Customer Management</h1>
            <div class="flex space-x-3">
                <!-- Test Connection Button -->
                <button 
                    wire:click="testSIMPelsConnection" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                >
                    <i class="fas fa-plug mr-2"></i>Test SIMPels Connection
                </button>
                
                <!-- Add Customer Button -->
                <button 
                    wire:click="openAddModal" 
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition duration-200"
                >
                    <i class="fas fa-plus mr-2"></i>Add {{ ucfirst($activeTab) }}
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button 
                wire:click="switchTab('umum')"
                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'umum' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Umum
                <span class="ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded-full">{{ $this->regularCustomersCount }}</span>
            </button>
            <button 
                wire:click="switchTab('santri')"
                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'santri' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Santri
                <span class="ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded-full">{{ $this->santriCount }}</span>
            </button>
            <button 
                wire:click="switchTab('guru')"
                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'guru' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Guru
                <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-600 rounded-full">{{ $this->guruCount }}</span>
            </button>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <!-- Tab specific content header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-lg font-medium text-gray-900">
                        @if($activeTab === 'santri')
                            Data Santri
                        @elseif($activeTab === 'guru')
                            Data Guru
                        @else
                            Data Customer Umum
                        @endif
                    </h2>
                    <p class="text-sm text-gray-500">
                        Total: {{ $this->totalCustomers }} | Active: {{ $this->activeCustomers }}
                    </p>
                </div>
                
                <!-- Sync Buttons for Santri and Guru tabs -->
                @if($activeTab === 'santri')
                    <button 
                        wire:click="syncSantriFromAPI" 
                        {{ $isSyncing ? 'disabled' : '' }}
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200 {{ $isSyncing ? 'opacity-50 cursor-not-allowed' : '' }}"
                    >
                        @if($isSyncing)
                            <i class="fas fa-spinner fa-spin mr-2"></i>Syncing...
                        @else
                            <i class="fas fa-sync mr-2"></i>Sync from SIMPels
                        @endif
                    </button>
                @elseif($activeTab === 'guru')
                    <button 
                        wire:click="syncGuruFromAPI" 
                        {{ $isSyncing ? 'disabled' : '' }}
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-200 {{ $isSyncing ? 'opacity-50 cursor-not-allowed' : '' }}"
                    >
                        @if($isSyncing)
                            <i class="fas fa-spinner fa-spin mr-2"></i>Syncing...
                        @else
                            <i class="fas fa-sync mr-2"></i>Sync from SIMPels
                        @endif
                    </button>
                @endif
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-64">
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search" 
                        placeholder="Search by name, email, or phone..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
                
                @if($activeTab === 'umum')
                    <select wire:model.live="customerType" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Types</option>
                        <option value="regular">Regular</option>
                        <option value="umum">Umum</option>
                    </select>
                @endif
                
                <select wire:model.live="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                
                <button 
                    wire:click="clearFilters" 
                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-200"
                >
                    Clear
                </button>
            </div>

            <!-- Customer Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                @if($activeTab === 'santri')
                                    Santri
                                @elseif($activeTab === 'guru')
                                    Guru
                                @else
                                    Customer
                                @endif
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                            @if($activeTab === 'santri')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Class</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFID</th>
                            @elseif($activeTab === 'guru')
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIP</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Experience</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">RFID</th>
                            @else
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            @endif
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <i class="fas fa-user text-gray-500"></i>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $customer->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $customer->phone ?? '-' }}
                                </td>
                                @if($activeTab === 'santri')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $customer->nis ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $customer->class ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium {{ ($customer->balance ?? 0) > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            Rp {{ number_format($customer->balance ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($customer->rfid_number)
                                            <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">
                                                {{ $customer->rfid_number }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @elseif($activeTab === 'guru')
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $customer->nip ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $customer->subject ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $customer->experience ?? 0 }} tahun
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @if($customer->rfid_number)
                                            <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">
                                                {{ $customer->rfid_number }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                @else
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="px-2 py-1 text-xs bg-gray-100 text-gray-800 rounded-full">
                                            {{ ucfirst($customer->customer_type ?? 'regular') }}
                                        </span>
                                    </td>
                                @endif
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($customer->is_active)
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Active</span>
                                    @else
                                        <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded-full">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="editCustomer({{ $customer->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="deleteCustomer({{ $customer->id }})" wire:confirm="Are you sure you want to delete this customer?" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $activeTab === 'santri' ? 7 : ($activeTab === 'guru' ? 7 : 5) }}" class="px-6 py-12 text-center text-sm text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                                        <p class="text-lg font-medium text-gray-400 mb-2">No {{ $activeTab }} found</p>
                                        @if($activeTab === 'santri')
                                            <p class="text-sm text-gray-400">Click "Sync from SIMPels" to import santri data</p>
                                        @elseif($activeTab === 'guru')
                                            <p class="text-sm text-gray-400">Click "Sync from SIMPels" to import guru data</p>
                                        @else
                                            <p class="text-sm text-gray-400">Click "Add Customer" to create new customer</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($customers->hasPages())
                <div class="mt-6">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Add Customer Modal -->
    @if($showAddModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">
                        {{ $isEditing ? 'Edit' : 'Add' }} {{ ucfirst($activeTab) }}
                    </h3>
                </div>
                
                <form wire:submit.prevent="saveCustomer" class="px-6 py-4">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                            <input 
                                type="text" 
                                wire:model="name" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input 
                                type="email" 
                                wire:model="email" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input 
                                type="text" 
                                wire:model="phone" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password {{ $isEditing ? '(Leave blank to keep)' : '' }}</label>
                            <input 
                                type="password" 
                                wire:model="password" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                {{ !$isEditing ? 'required' : '' }}
                            >
                            @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        @if($activeTab === 'santri')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIS</label>
                                <input 
                                    type="text" 
                                    wire:model="nis" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('nis') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Class</label>
                                <input 
                                    type="text" 
                                    wire:model="class" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('class') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @elseif($activeTab === 'guru')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NIP</label>
                                <input 
                                    type="text" 
                                    wire:model="nip" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('nip') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                                <input 
                                    type="text" 
                                    wire:model="subject" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                @error('subject') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Experience (Years)</label>
                                <input 
                                    type="number" 
                                    wire:model="experience" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="0"
                                >
                                @error('experience') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="flex items-center">
                            <input 
                                type="checkbox" 
                                wire:model="is_active" 
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                            >
                            <label class="ml-2 block text-sm text-gray-900">Active</label>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                        <button 
                            type="button" 
                            wire:click="closeAddModal"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 transition duration-200"
                        >
                            Cancel
                        </button>
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-200"
                        >
                            {{ $isEditing ? 'Update' : 'Save' }} {{ ucfirst($activeTab) }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>