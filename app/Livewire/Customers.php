<?php

namespace App\Livewire;

use App\Models\User;
use App\Services\SimpelsApiService;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Http;

class Customers extends Component
{
    use WithPagination;

    public $search = '';
    public $customerType = '';
    public $status = '';
    public $activeTab = 'umum'; // New property for active tab
    
    // Modal states
    public $showAddModal = false;
    public $showEditModal = false;
    public $isEditing = false;
    public $editingCustomerId = null;
    public $isSyncing = false;
    
    // Form properties
    public $name = '';
    public $email = '';
    public $phone = '';
    public $customer_type = 'regular';
    public $is_active = true;
    public $nis = '';
    public $nip = '';
    public $subject = '';
    public $experience = '';
    public $class = '';
    public $rfid_number = '';
    public $balance = 0;
    public $spending_limit = 50000;
    public $password = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'customerType' => ['except' => ''],
        'status' => ['except' => ''],
        'activeTab' => ['except' => 'umum'],
    ];
    
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($this->editingCustomerId)],
            'phone' => 'nullable|string|max:20',
            'customer_type' => 'required|in:regular,umum,santri,guru',
            'is_active' => 'boolean',
            'nis' => 'nullable|string|max:20',
            'nip' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:100',
            'experience' => 'nullable|integer|min:0',
            'class' => 'nullable|string|max:50',
            'rfid_number' => 'nullable|string|max:50',
            'balance' => 'nullable|numeric|min:0',
            'spending_limit' => 'nullable|numeric|min:0',
            'password' => $this->isEditing ? 'nullable|min:6' : 'required|min:6', // Password required only on create
        ];
    }

    public function updatingActiveTab()
    {
        $this->resetPage();
        $this->search = '';
        $this->customerType = '';
        $this->status = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCustomerType()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->customerType = '';
        $this->status = '';
        $this->resetPage();
    }

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->search = '';
        $this->customerType = '';
        $this->status = '';
    }
    
    public function testSIMPelsConnection()
    {
        try {
            $simpelsService = new SimpelsApiService();
            $healthStatus = $simpelsService->getHealthStatus();
            
            if ($healthStatus['status'] === 'healthy') {
                session()->flash('message', 'Koneksi ke SIMPels API berhasil! Response time: ' . $healthStatus['response_time_ms'] . 'ms');
            } else {
                session()->flash('error', 'Koneksi ke SIMPels API gagal: ' . $healthStatus['error']);
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error koneksi SIMPels API: ' . $e->getMessage() . '. Pastikan SIMPels server berjalan.');
        }
    }
    
    public function syncSantriFromAPI()
    {
        if ($this->isSyncing) {
            return;
        }
        
        $this->isSyncing = true;
        
        try {
            // Add delay to show loading state
            usleep(500000); // 0.5 second
            
            $simpelsService = new SimpelsApiService();
            \Log::info('Attempting to sync santri from SIMPels API using SimpelsApiService');
            
            $apiData = $simpelsService->getAllSantri(false); // Don't use cache for sync
            
            if (!isset($apiData['success']) || !$apiData['success']) {
                $errorMsg = 'API Error: ' . ($apiData['message'] ?? 'Unknown error from SIMPels');
                \Log::error($errorMsg, $apiData);
                session()->flash('error', $errorMsg);
                return;
            }
            
            $santriList = $apiData['data'] ?? [];
            \Log::info('Received ' . count($santriList) . ' santri from SIMPels');
            
            if (empty($santriList)) {
                session()->flash('message', 'Tidak ada data santri yang ditemukan di SIMPels.');
                return;
            }
            
            $syncedCount = 0;
            $updatedCount = 0;
            $errorCount = 0;
            
            \Log::info('Starting sync process for ' . count($santriList) . ' santri records');
            
            foreach ($santriList as $index => $santriData) {
                try {
                    \Log::info("Processing santri #{$index}: {$santriData['nama_santri']} (NIS: {$santriData['nis']})");
                    
                    // Validate required fields
                    if (empty($santriData['nis']) || empty($santriData['nama_santri'])) {
                        $errorCount++;
                        \Log::warning('Skipping santri due to missing required fields', $santriData);
                        continue;
                    }
                    
                    // Find existing santri by NIS (primary key for santri)
                    $existingSantri = User::where('nis', $santriData['nis'])->first();
                    
                    $userData = [
                        'name' => $santriData['nama_santri'],
                        'email' => $santriData['email'] ?? $santriData['nis'] . '@santri.simpels.local',
                        'phone' => $santriData['no_hp'] ?? null,
                        'password' => bcrypt('santri123'), // Default password
                        'role' => 'customer', // Customer role - cannot login to EPOS
                        'customer_type' => 'santri',
                        'nis' => $santriData['nis'],
                        'class' => $santriData['kelas'] ?? null,
                        'rfid_number' => $santriData['rfid_tag'] ?? null,
                        'balance' => $santriData['saldo'] ?? 0,
                        'spending_limit' => $santriData['limit_harian'] ?? 50000,
                        'is_active' => ($santriData['status'] ?? 'aktif') === 'aktif',
                    ];
                    
                    if ($existingSantri) {
                        // Update existing santri
                        $existingSantri->update($userData);
                        $updatedCount++;
                        \Log::info('Updated santri: ' . $santriData['nama_santri']);
                    } else {
                        // Create new santri
                        User::create($userData);
                        $syncedCount++;
                        \Log::info('Created new santri: ' . $santriData['nama_santri']);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errorMsg = "Error syncing santri {$santriData['nama_santri']} (NIS: {$santriData['nis']}): " . $e->getMessage();
                    \Log::error($errorMsg, [
                        'santri_data' => $santriData,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
            
            $message = "Sync santri berhasil! {$syncedCount} santri baru ditambahkan, {$updatedCount} santri diperbarui dari SIMPels.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} data gagal diproses.";
            }
            
            \Log::info($message);
            session()->flash('message', $message);
            
            // Clear cache after successful sync
            $simpelsService->clearCache();
            
        } catch (\Exception $e) {
            $errorMsg = 'Error sync santri dari SIMPels: ' . $e->getMessage();
            \Log::error($errorMsg);
            session()->flash('error', $errorMsg);
        } finally {
            $this->isSyncing = false;
        }
    }
    
    public function syncGuruFromAPI()
    {
        if ($this->isSyncing) {
            return;
        }
        
        $this->isSyncing = true;
        
        try {
            // Add delay to show loading state
            usleep(500000); // 0.5 second
            
            $simpelsService = new SimpelsApiService();
            \Log::info('Attempting to sync guru from SIMPels API using SimpelsApiService');
            
            $apiData = $simpelsService->getAllGuru(false); // Don't use cache for sync
            
            if (!isset($apiData['success']) || !$apiData['success']) {
                $errorMsg = 'API Error: ' . ($apiData['message'] ?? 'Unknown error from SIMPels');
                \Log::error($errorMsg, $apiData);
                session()->flash('error', $errorMsg);
                return;
            }
            
            $guruList = $apiData['data'] ?? [];
            \Log::info('Received ' . count($guruList) . ' guru from SIMPels');
            
            if (empty($guruList)) {
                session()->flash('message', 'Tidak ada data guru yang ditemukan di SIMPels.');
                return;
            }
            
            $syncedCount = 0;
            $updatedCount = 0;
            $errorCount = 0;
            
            foreach ($guruList as $guruData) {
                try {
                    // Validate required fields
                    if (empty($guruData['nip']) || empty($guruData['nama_guru'])) {
                        $errorCount++;
                        \Log::warning('Skipping guru due to missing required fields', $guruData);
                        continue;
                    }
                    
                    // Find existing guru by NIP or RFID
                    $existingGuru = User::where('nip', $guruData['nip'])
                                      ->orWhere('rfid_number', $guruData['rfid_tag'] ?? null)
                                      ->first();
                    
                    $userData = [
                        'name' => $guruData['nama_guru'],
                        'email' => $guruData['email'] ?? $guruData['nip'] . '@guru.simpels.local',
                        'phone' => $guruData['no_hp'] ?? null,
                        'password' => bcrypt('customer-no-login-' . rand(1000000, 9999999)), // Customer can't login
                        'role' => 'customer', // Guru adalah PELANGGAN, bukan staff
                        'customer_type' => 'guru',
                        'nip' => $guruData['nip'],
                        'subject' => $guruData['mata_pelajaran'] ?? null,
                        'experience' => $guruData['pengalaman_tahun'] ?? 0,
                        'rfid_number' => $guruData['rfid_tag'] ?? null,
                        'balance' => $guruData['saldo'] ?? 0,
                        'spending_limit' => $guruData['limit_harian'] ?? 100000,
                        'is_active' => ($guruData['status'] ?? 'aktif') === 'aktif',
                    ];
                    
                    if ($existingGuru) {
                        // Update existing guru
                        $existingGuru->update($userData);
                        $updatedCount++;
                        \Log::info('Updated guru: ' . $guruData['nama_guru']);
                    } else {
                        // Create new guru
                        User::create($userData);
                        $syncedCount++;
                        \Log::info('Created new guru: ' . $guruData['nama_guru']);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    \Log::error("Error syncing guru: " . $e->getMessage(), $guruData);
                }
            }
            
            $message = "Sync guru berhasil! {$syncedCount} guru baru ditambahkan, {$updatedCount} guru diperbarui dari SIMPels.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} data gagal diproses.";
            }
            
            \Log::info($message);
            session()->flash('message', $message);
            
            // Clear cache after successful sync
            $simpelsService->clearCache();
            
        } catch (\Exception $e) {
            $errorMsg = 'Error sync guru dari SIMPels: ' . $e->getMessage();
            \Log::error($errorMsg);
            session()->flash('error', $errorMsg);
        } finally {
            $this->isSyncing = false;
        }
    }
    
    public function openAddModal()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showAddModal = true;
        
        // Set customer type based on active tab
        switch ($this->activeTab) {
            case 'santri':
                $this->customer_type = 'santri';
                break;
            case 'guru':
                $this->customer_type = 'guru';
                break;
            case 'umum':
            default:
                $this->customer_type = 'regular';
                break;
        }
    }

    public function editCustomer($id)
    {
        $this->resetForm();
        $this->isEditing = true;
        $this->editingCustomerId = $id;
        
        $customer = User::findOrFail($id);
        
        $this->name = $customer->name;
        $this->email = $customer->email;
        $this->phone = $customer->phone;
        $this->customer_type = $customer->customer_type;
        $this->is_active = (bool) $customer->is_active;
        $this->nis = $customer->nis;
        $this->nip = $customer->nip;
        $this->subject = $customer->subject;
        $this->experience = $customer->experience;
        $this->class = $customer->class;
        $this->rfid_number = $customer->rfid_number;
        $this->balance = $customer->balance;
        $this->spending_limit = $customer->spending_limit;
        
        $this->showAddModal = true;
    }

    public function deleteCustomer($id)
    {
        $customer = User::findOrFail($id);
        $customer->delete();
        session()->flash('message', 'Pelanggan berhasil dihapus.');
        $this->dispatch('showNotification', [
            'type' => 'success',
            'title' => 'Berhasil',
            'message' => 'Pelanggan berhasil dihapus'
        ]);
    }
    
    public function closeAddModal()
    {
        $this->showAddModal = false;
        $this->resetForm();
    }
    
    public function resetForm()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->customer_type = 'regular';
        $this->is_active = true;
        $this->nis = '';
        $this->nip = '';
        $this->subject = '';
        $this->experience = '';
        $this->class = '';
        $this->rfid_number = '';
        $this->balance = 0;
        $this->spending_limit = 50000;
        $this->password = '';
        $this->isEditing = false;
        $this->editingCustomerId = null;
        $this->resetErrorBag();
    }
    
    public function saveCustomer()
    {
        $this->validate();
        
        // Customer data - TIDAK BISA LOGIN ke sistem EPOS (hanya data member/pelanggan)
        $data = [
            'name' => $this->name,
            'email' => $this->email ?: ($this->nis ?: $this->nip) . '@customer.local', // Generate email if empty
            'phone' => $this->phone,
            'customer_type' => $this->customer_type,
            'is_active' => $this->is_active,
            'role' => 'customer', // Customer role - TIDAK BISA LOGIN
            'rfid_number' => $this->rfid_number,
            'balance' => $this->balance ?? 0,
            'spending_limit' => $this->spending_limit ?? 50000,
        ];

        // Handle password
        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        } elseif (!$this->isEditing) {
            $data['password'] = bcrypt('customer-no-login-' . rand(1000000, 9999999));
        }
        
        // Add specific fields based on customer type
        if ($this->customer_type === 'santri') {
            $data['nis'] = $this->nis;
            $data['class'] = $this->class;
        } elseif ($this->customer_type === 'guru') {
            $data['nip'] = $this->nip;
            $data['subject'] = $this->subject;
            $data['experience'] = $this->experience;
        }
        
        if ($this->isEditing) {
            User::where('id', $this->editingCustomerId)->update($data);
            $message = ucfirst($this->activeTab) . ' berhasil diperbarui!';
        } else {
            User::create($data);
            $message = ucfirst($this->activeTab) . ' berhasil ditambahkan sebagai pelanggan!';
        }
        
        session()->flash('message', $message);
        $this->closeAddModal();
    }

    public function getCustomersProperty()
    {
        $query = User::query();

        // Filter based on active tab
        switch ($this->activeTab) {
            case 'santri':
                $query->where('customer_type', 'santri');
                break;
            case 'guru':
                $query->where('customer_type', 'guru');
                break;
            case 'umum':
            default:
                $query->where(function ($q) {
                    $q->where('customer_type', 'regular')
                      ->orWhere('customer_type', 'umum')
                      ->orWhereNull('customer_type');
                });
                break;
        }

        // Apply search filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        }

        // Apply customer type filter (only if not using tab filtering)
        if ($this->customerType && $this->activeTab === 'umum') {
            $query->where('customer_type', $this->customerType);
        }

        // Apply status filter
        if ($this->status !== '') {
            $query->where('is_active', $this->status === 'active');
        }

        // Exclude test data for clean customer management (but allow for santri tab for testing)
        if ($this->activeTab !== 'santri') {
            $query->where('email', 'not like', '%.simpels.local');
        }

        return $query->latest()->paginate(50);
    }

    public function getTotalCustomersProperty()
    {
        switch ($this->activeTab) {
            case 'santri':
                return User::where('customer_type', 'santri')
                          ->count();
            case 'guru':
                return User::where('customer_type', 'guru')
                          ->count();
            case 'umum':
            default:
                return User::where(function ($query) {
                    $query->where('customer_type', 'regular')
                          ->orWhere('customer_type', 'umum')
                          ->orWhereNull('customer_type');
                })->whereNotIn('role', ['admin', 'manager', 'cashier'])
                  ->count();
        }
    }

    public function getActiveCustomersProperty()
    {
        switch ($this->activeTab) {
            case 'santri':
                return User::where('customer_type', 'santri')
                          ->where('is_active', true)
                          ->count();
            case 'guru':
                return User::where('customer_type', 'guru')
                          ->where('is_active', true)
                          ->count();
            case 'umum':
            default:
                return User::where(function ($query) {
                    $query->where('customer_type', 'regular')
                          ->orWhere('customer_type', 'umum')
                          ->orWhereNull('customer_type');
                })->where('is_active', true)
                  ->whereNotIn('role', ['admin', 'manager', 'cashier'])
                  ->count();
        }
    }

    public function getSantriCountProperty()
    {
        return User::where('customer_type', 'santri')
                  ->count();
    }

    public function getGuruCountProperty()
    {
        return User::where('customer_type', 'guru')
                  ->count();
    }

    public function getRegularCustomersCountProperty()
    {
        return User::where(function ($query) {
            $query->where('customer_type', 'regular')
                  ->orWhere('customer_type', 'umum')
                  ->orWhereNull('customer_type');
        })->whereNotIn('role', ['admin', 'manager', 'cashier'])
          ->count();
    }

    public function render()
    {
        return view('livewire.customers', [
            'customers' => $this->customers,
        ])->layout('layouts.epos', ['header' => 'Manajemen Pelanggan']);
    }
}
