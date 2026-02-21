<?php

namespace App\Livewire;

use App\Models\Tenant;
use App\Models\TenantLedger;
use App\Models\TenantWithdrawal;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FoodcourtFinance extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $search = '';
    public $selectedTenantId = null;
    
    // Withdrawal Properties
    public $showWithdrawModal = false;
    public $withdrawAmount = 0;
    public $withdrawNotes = '';
    public $proofImage;
    
    // History Modal
    public $showHistoryModal = false;
    public $historyTransactions = [];
    public $historyWithdrawals = [];
    
    // Pagination reset
    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function selectTenant($tenantId)
    {
        $this->selectedTenantId = $tenantId;
        $this->showHistoryModal = true;
        
        $this->loadHistory();
    }
    
    public function loadHistory()
    {
        if (!$this->selectedTenantId) return;
        
        $this->historyTransactions = TenantLedger::where('tenant_id', $this->selectedTenantId)
            ->latest()
            ->limit(50)
            ->get();
            
        $this->historyWithdrawals = TenantWithdrawal::where('tenant_id', $this->selectedTenantId)
            ->latest()
            ->limit(20)
            ->get();
    }

    public function openWithdrawModal($tenantId)
    {
        $this->selectedTenantId = $tenantId;
        $tenant = Tenant::find($tenantId);
        
        if (!$tenant) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Error',
                'message' => 'Tenant tidak ditemukan.'
            ]);
            return;
        }

        $this->withdrawAmount = $tenant->balance; // Default to full balance
        $this->withdrawNotes = '';
        $this->showWithdrawModal = true;
        
        // Ensure modal shows up
        $this->dispatch('open-withdraw-modal');
    }

    public function closeWithdrawModal()
    {
        $this->showWithdrawModal = false;
        $this->reset(['withdrawAmount', 'withdrawNotes', 'proofImage']);
    }
    
    public function closeHistoryModal()
    {
        $this->showHistoryModal = false;
        $this->selectedTenantId = null;
    }

    public function processWithdrawal()
    {
        $this->validate([
            'withdrawAmount' => 'required|numeric|min:1',
            'withdrawNotes' => 'nullable|string|max:255',
        ]);

        $tenant = Tenant::findOrFail($this->selectedTenantId);

        if ($this->withdrawAmount > $tenant->balance) {
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Saldo Tidak Cukup',
                'message' => 'Jumlah penarikan melebihi saldo tenant!'
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            // Create Withdrawal Record
            // Assuming this action is done by Admin and money is transferred immediately
            $withdrawal = TenantWithdrawal::create([
                'tenant_id' => $tenant->id,
                'amount' => $this->withdrawAmount,
                'status' => 'completed',
                'processed_by' => Auth::id(),
                'processed_at' => now(),
                'notes' => $this->withdrawNotes,
                // 'proof_image' => ... handle upload if needed
            ]);

            // Create Ledger Entry (Debit)
            $previousBalance = $tenant->balance;
            $newBalance = $previousBalance - $this->withdrawAmount;
            
            TenantLedger::create([
                'tenant_id' => $tenant->id,
                'type' => 'withdrawal',
                'amount' => -$this->withdrawAmount,
                'balance_before' => $previousBalance,
                'balance_after' => $newBalance,
                'withdrawal_id' => $withdrawal->id,
                'description' => "Penarikan Saldo #{$withdrawal->reference_number}",
            ]);

            // Update Tenant Balance
            $tenant->decrement('balance', $this->withdrawAmount);

            DB::commit();

            $this->dispatch('showNotification', [
                'type' => 'success',
                'title' => '✅ Penarikan Berhasil',
                'message' => 'Saldo berhasil ditarik dan dicatat.'
            ]);
            
            $this->closeWithdrawModal();
            
            // Refresh history if open
            if ($this->showHistoryModal) {
                $this->loadHistory();
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showNotification', [
                'type' => 'error',
                'title' => '❌ Gagal',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $tenants = Tenant::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('owner_name', 'like', '%' . $this->search . '%')
                      ->orWhere('booth_number', 'like', '%' . $this->search . '%');
            })
            ->withCount(['products'])
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.foodcourt-finance', [
            'tenants' => $tenants,
            'selectedTenant' => $this->selectedTenantId ? Tenant::find($this->selectedTenantId) : null,
        ])->layout('layouts.epos', [
            'header' => 'Keuangan Foodcourt' // Ensure layout supports header slot or remove if handled by layout
        ]);
    }
}
