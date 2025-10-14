<div>
    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Total Staff</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $stats['total'] }}</h3>
                </div>
                <div class="bg-indigo-100 dark:bg-indigo-900 rounded-full p-3">
                    <i class="fas fa-users text-indigo-600 dark:text-indigo-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Admins</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $stats['admin'] }}</h3>
                </div>
                <div class="bg-red-100 dark:bg-red-900 rounded-full p-3">
                    <i class="fas fa-crown text-red-600 dark:text-red-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Managers</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $stats['manager'] }}</h3>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 rounded-full p-3">
                    <i class="fas fa-user-tie text-blue-600 dark:text-blue-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Cashiers</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $stats['cashier'] }}</h3>
                </div>
                <div class="bg-green-100 dark:bg-green-900 rounded-full p-3">
                    <i class="fas fa-cash-register text-green-600 dark:text-green-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Active</p>
                    <h3 class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $stats['active'] }}</h3>
                </div>
                <div class="bg-green-100 dark:bg-green-900 rounded-full p-3">
                    <i class="fas fa-check-circle text-green-600 dark:text-green-300"></i>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase">Inactive</p>
                    <h3 class="text-2xl font-bold text-gray-600 dark:text-gray-400 mt-1">{{ $stats['inactive'] }}</h3>
                </div>
                <div class="bg-gray-100 dark:bg-gray-900 rounded-full p-3">
                    <i class="fas fa-ban text-gray-600 dark:text-gray-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Actions -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <!-- Search -->
            <div class="relative flex-1 max-w-md">
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Search staff by name, email, or phone..." 
                       class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                <i class="fas fa-search absolute left-3 top-4 text-gray-400"></i>
            </div>

            <!-- Add Staff Button -->
            <button wire:click="openCreateModal" 
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i>Add New Staff
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-500 text-green-700 px-6 py-4 rounded-lg mb-6 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
                <span class="font-medium">{{ session('message') }}</span>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 text-red-700 px-6 py-4 rounded-lg mb-6 shadow-sm">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Staff Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Staff Member</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($staffMembers as $staff)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white
                                        {{ $staff->role === 'admin' ? 'bg-gradient-to-br from-red-500 to-pink-600' : '' }}
                                        {{ $staff->role === 'manager' ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : '' }}
                                        {{ $staff->role === 'cashier' ? 'bg-gradient-to-br from-green-500 to-emerald-600' : '' }}">
                                        {{ strtoupper(substr($staff->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $staff->name }}</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">ID: {{ $staff->id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        <i class="fas fa-envelope text-gray-400 mr-2"></i>{{ $staff->email }}
                                    </p>
                                    @if($staff->phone)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <i class="fas fa-phone text-gray-400 mr-2"></i>{{ $staff->phone }}
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $staff->role === 'admin' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                                    {{ $staff->role === 'manager' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                    {{ $staff->role === 'cashier' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}">
                                    @if($staff->role === 'admin')
                                        <i class="fas fa-crown mr-1"></i>
                                    @elseif($staff->role === 'manager')
                                        <i class="fas fa-user-tie mr-1"></i>
                                    @else
                                        <i class="fas fa-cash-register mr-1"></i>
                                    @endif
                                    {{ ucfirst($staff->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button wire:click="toggleStatus({{ $staff->id }})" 
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold transition-colors
                                        {{ $staff->is_active ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 hover:bg-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 hover:bg-gray-200' }}">
                                    <span class="w-2 h-2 rounded-full mr-2 {{ $staff->is_active ? 'bg-green-500' : 'bg-gray-500' }}"></span>
                                    {{ $staff->is_active ? 'Active' : 'Inactive' }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 dark:text-white">
                                    {{ $staff->created_at->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $staff->created_at->diffForHumans() }}
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <button wire:click="openEditModal({{ $staff->id }})" 
                                            class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900 rounded-lg transition-colors"
                                            title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $staff->id }})" 
                                            class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900 rounded-lg transition-colors"
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                    <i class="fas fa-users text-3xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-500 dark:text-gray-400 font-medium">No staff members found</p>
                                <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">Try adjusting your search or filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900">
            {{ $staffMembers->links() }}
        </div>
    </div>

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showModal') }">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Background Overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                     @click="$wire.closeModal()"></div>

                <!-- Modal Panel -->
                <div class="relative inline-block w-full max-w-2xl px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:p-6">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            <i class="fas {{ $isEditMode ? 'fa-edit' : 'fa-plus' }} text-indigo-600 mr-2"></i>
                            {{ $isEditMode ? 'Edit Staff Member' : 'Add New Staff Member' }}
                        </h3>
                        <button @click="$wire.closeModal()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form wire:submit.prevent="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-user mr-1"></i>Full Name *
                                </label>
                                <input type="text" wire:model="name" 
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="Enter full name">
                                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-envelope mr-1"></i>Email *
                                </label>
                                <input type="email" wire:model="email" 
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="email@example.com">
                                @error('email') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-phone mr-1"></i>Phone Number
                                </label>
                                <input type="text" wire:model="phone" 
                                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                       placeholder="+62 xxx xxxx xxxx">
                                @error('phone') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Role -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-user-tag mr-1"></i>Role *
                                </label>
                                <select wire:model="role" 
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                                    <option value="cashier">Cashier</option>
                                    <option value="manager">Manager</option>
                                    <option value="admin">Admin</option>
                                </select>
                                @error('role') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    <i class="fas fa-toggle-on mr-1"></i>Status
                                </label>
                                <div class="flex items-center gap-4 mt-3">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 dark:peer-focus:ring-indigo-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-green-600"></div>
                                        <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300">
                                            {{ $is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <!-- Password Section -->
                            <div class="md:col-span-2 border-t border-gray-200 dark:border-gray-700 pt-4 mt-2">
                                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">
                                    <i class="fas fa-lock mr-1"></i>
                                    {{ $isEditMode ? 'Change Password (leave blank to keep current)' : 'Set Password *' }}
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Password -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Password {{ $isEditMode ? '' : '*' }}
                                        </label>
                                        <input type="password" wire:model="password" 
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                               placeholder="••••••••">
                                        @error('password') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Confirm Password -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Confirm Password {{ $isEditMode ? '' : '*' }}
                                        </label>
                                        <input type="password" wire:model="password_confirmation" 
                                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                                               placeholder="••••••••">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <button type="button" @click="$wire.closeModal()" 
                                    class="px-6 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 rounded-lg font-medium transition-all">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg">
                                <i class="fas fa-save mr-2"></i>{{ $isEditMode ? 'Update' : 'Create' }} Staff
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal && $staffToDelete)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <!-- Background Overlay -->
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                     @click="$wire.closeModal()"></div>

                <!-- Modal Panel -->
                <div class="relative inline-block w-full max-w-md px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white dark:bg-gray-800 rounded-lg shadow-xl sm:my-8 sm:align-middle sm:p-6">
                    <div class="text-center">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full mb-4">
                            <i class="fas fa-exclamation-triangle text-3xl text-red-600 dark:text-red-300"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Delete Staff Member</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-6">
                            Are you sure you want to delete <strong>{{ $staffToDelete->name }}</strong>? This action cannot be undone.
                        </p>

                        <div class="flex items-center justify-center gap-3">
                            <button wire:click="closeModal" 
                                    class="px-6 py-2 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 border-2 border-gray-300 dark:border-gray-600 hover:border-gray-400 rounded-lg font-medium transition-all">
                                <i class="fas fa-times mr-2"></i>Cancel
                            </button>
                            <button wire:click="deleteStaff" 
                                    class="px-6 py-2 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white rounded-lg font-medium transition-all shadow-md hover:shadow-lg">
                                <i class="fas fa-trash mr-2"></i>Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
