<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffManagement extends Component
{
    use WithPagination;

    public $search = '';
    
    // Form fields
    public $userId;
    public $name = '';
    public $email = '';
    public $phone = '';
    public $role = 'cashier';
    public $password = '';
    public $password_confirmation = '';
    public $is_active = true;
    
    // Modal states
    public $showModal = false;
    public $isEditMode = false;
    public $showDeleteModal = false;
    public $staffToDelete = null;

    protected $queryString = ['search'];

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,manager,cashier',
            'is_active' => 'boolean',
        ];

        if (!$this->isEditMode) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } elseif (!empty($this->password)) {
            $rules['password'] = 'string|min:8|confirmed';
        }

        return $rules;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->showModal = true;
    }

    public function openEditModal($id)
    {
        $user = User::findOrFail($id);
        
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->is_active = (bool) $user->is_active;
        
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        try {
            if ($this->isEditMode) {
                $user = User::findOrFail($this->userId);
                
                $data = [
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'role' => $this->role,
                    'is_active' => $this->is_active,
                ];

                if (!empty($this->password)) {
                    $data['password'] = Hash::make($this->password);
                }

                $user->update($data);
                
                session()->flash('message', 'Staf berhasil diperbarui!');
            } else {
                User::create([
                    'name' => $this->name,
                    'email' => $this->email,
                    'phone' => $this->phone,
                    'role' => $this->role,
                    'password' => Hash::make($this->password),
                    'is_active' => $this->is_active,
                ]);
                
                session()->flash('message', 'Staf berhasil dibuat!');
            }

            $this->closeModal();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->staffToDelete = User::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function deleteStaff()
    {
        if ($this->staffToDelete) {
            // Prevent deleting yourself
            if ($this->staffToDelete->id === auth()->id()) {
                session()->flash('error', 'Anda tidak dapat menghapus akun sendiri!');
                $this->showDeleteModal = false;
                return;
            }

            $this->staffToDelete->delete();
            session()->flash('message', 'Staf berhasil dihapus!');
            $this->showDeleteModal = false;
            $this->staffToDelete = null;
            $this->resetPage();
        }
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri!');
            return;
        }

        $user->update(['is_active' => !$user->is_active]);
        session()->flash('message', 'Status staf berhasil diperbarui!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->showDeleteModal = false;
        $this->resetForm();
        $this->resetValidation();
    }

    private function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->role = 'cashier';
        $this->password = '';
        $this->password_confirmation = '';
        $this->is_active = true;
    }

    public function render()
    {
        // Query hanya untuk STAFF (admin, manager, cashier) - BUKAN customer
        $query = User::whereIn('role', ['admin', 'manager', 'cashier']);

        // Search filter
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        $staffMembers = $query->orderBy('created_at', 'desc')->paginate(20);

        // Statistics - hanya untuk STAFF
        $stats = [
            'total' => User::whereIn('role', ['admin', 'manager', 'cashier'])->count(),
            'admin' => User::where('role', 'admin')->count(),
            'manager' => User::where('role', 'manager')->count(),
            'cashier' => User::where('role', 'cashier')->count(),
            'active' => User::whereIn('role', ['admin', 'manager', 'cashier'])->where('is_active', true)->count(),
            'inactive' => User::whereIn('role', ['admin', 'manager', 'cashier'])->where('is_active', false)->count(),
        ];

        return view('livewire.staff-management', [
            'staffMembers' => $staffMembers,
            'stats' => $stats,
        ])->layout('layouts.epos', [
            'header' => 'Manajemen Staf'
        ]);
    }
}
