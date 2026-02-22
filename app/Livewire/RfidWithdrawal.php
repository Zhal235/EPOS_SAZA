<?php

namespace App\Livewire;

use App\Services\SimpelsApiService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class RfidWithdrawal extends Component
{
    // Search & Scan
    public $search = '';
    public $rfidBuffer = '';
    
    // Santri Data
    public $selectedSantri = null;
    public $balance = 0;
    
    // Withdrawal Form
    public $withdrawAmount = 0;
    public $notes = '';
    
    // State
    public $isLoading = false;
    public $showConfirmation = false;
    
    // Service
    protected $simpelsApi;

    public function boot()
    {
        // Try creating service, but don't crash if app context not ready (e.g. tests)
        try {
            $this->simpelsApi = app(\App\Services\SimpelsApiService::class);
        } catch(\Exception $e) {
            Log::warning("SimpelsApiService not available in RfidWithdrawal: " . $e->getMessage());
        }
    }
    
    // Listen for RFID scan event from JS (similar to POS)
    protected $listeners = ['handleRfidScan' => 'handleRfidScan'];

    public function handleRfidScan($rfid)
    {
        // Reset previous selection
        $this->reset(['selectedSantri', 'balance', 'withdrawAmount', 'notes', 'showConfirmation']);
        $this->isLoading = true;
        
        try {
            // Find Santri via API
            // Check if service available
            if (!$this->simpelsApi) {
                throw new \Exception("Service API tidak aktif.");
            }

            $response = $this->simpelsApi->getSantriByRfid($rfid);
            
            if (!$response || !($response['success'] ?? false)) {
                $this->dispatch('showNotification', [
                    'type' => 'error', 
                    'title' => '❌ Tidak Ditemukan',
                    'message' => 'Kartu RFID tidak terdaftar dalam sistem.'
                ]);
                return;
            }

            $this->selectedSantri = $response['data'];
            // Normalize balance field
            $this->balance = $response['data']['saldo'] ?? 0;
            
            $this->dispatch('showNotification', [
                'type' => 'success', 
                'title' => '✅ Santri Ditemukan',
                'message' => "Data santri " . ($this->selectedSantri['nama_santri'] ?? 'Unknown') . " berhasil dimuat."
            ]);
            
        } catch (\Exception $e) {
            Log::error("RFID Withdrawal Scan Error: " . $e->getMessage());
            $this->dispatch('showNotification', [
                'type' => 'error', 
                'title' => '❌ Error',
                'message' => 'Gagal mengambil data santri: ' . $e->getMessage()
            ]);
        } finally {
            $this->isLoading = false;
        }
    }

    public function confirmWithdrawal()
    {
        // Validation
        if (!$this->selectedSantri) return;
        if ($this->withdrawAmount > $this->balance) {
             $this->addError('withdrawAmount', 'Saldo tidak mencukupi.');
             return;
        }
        if ($this->withdrawAmount < 1000) {
             $this->addError('withdrawAmount', 'Minimal penarikan Rp 1.000.');
             return;
        }
        
        $this->showConfirmation = true;
    }
    
    public function processWithdrawal()
    {
        if (!$this->selectedSantri) return;
        
        $this->isLoading = true;

        try {
            // In a real implementation:
            // 1. We create a negative transaction (Refund/Withdrawal) against the API
            // 2. OR we use a specific withdrawal endpoint
            
            // For now, we'll try to use the `processPayment` with a 'withdrawal' flag or description
            // Assuming the API handles balance deduction regardless of type
            
            $santriId = $this->selectedSantri['id'] ?? $this->selectedSantri['santri_id'];
            
            // Extract RFID UID safely
            $rfidTag = '';
            if (isset($this->selectedSantri['rfid_tag'])) {
                 $rfidTag = is_array($this->selectedSantri['rfid_tag']) 
                     ? ($this->selectedSantri['rfid_tag']['uid'] ?? '') 
                     : $this->selectedSantri['rfid_tag'];
            } elseif (isset($this->selectedSantri['rfid_uid'])) {
                 $rfidTag = $this->selectedSantri['rfid_uid'];
            }

            $ref = 'WD-' . time();

            // NOTE: Check if API supports this specific withdrawal flow
            // If not, this might need adjustment on the backend API side
            
            $response = $this->simpelsApi->processPayment([
                'santri_id' => $santriId,
                'rfid_tag' => $rfidTag,
                'amount' => $this->withdrawAmount,
                'transaction_ref' => $ref,
                'description' => "Penarikan Tunai (Cash Out) - " . ($this->notes ?: '-'),
                'items' => [], 
                'type' => 'withdrawal' // Custom Type
            ]);

            if ($response && ($response['success'] ?? false)) {
                $newBalance = $response['data']['new_balance'] ?? ($this->balance - $this->withdrawAmount);
                
                // Success Modal
                $this->dispatch('swal:modal', [
                    'type' => 'success',
                    'title' => '✅ Penarikan Berhasil',
                    'text' => "Penarikan tunai sebesar Rp " . number_format($this->withdrawAmount, 0, ',', '.') . " berhasil.\nSisa Saldo: Rp " . number_format($newBalance, 0, ',', '.')
                ]);
                
                $this->reset(['selectedSantri', 'balance', 'withdrawAmount', 'notes', 'showConfirmation']);
            } else {
                throw new \Exception($response['message'] ?? 'Unknown API error');
            }

        } catch (\Exception $e) {
            $this->dispatch('swal:modal', [
                'type' => 'error',
                'title' => '❌ Gagal',
                'text' => "Proses penarikan gagal: " . $e->getMessage()
            ]);
        } finally {
            $this->isLoading = false;
            $this->showConfirmation = false;
        }
    }
    
    public function cancel()
    {
        $this->reset(['selectedSantri', 'balance', 'withdrawAmount', 'notes', 'showConfirmation']);
    }

    public function render()
    {
        return view('livewire.rfid-withdrawal')->layout('layouts.epos', [
            'header' => 'Penarikan Tunai Santri'
        ]);
    }
}
