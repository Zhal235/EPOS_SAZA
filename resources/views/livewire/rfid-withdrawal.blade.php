
<div class="h-full bg-gray-50 flex flex-col items-center justify-center p-6 space-y-8" 
     x-data="{ 
        rfidBuffer: '', 
        lastTime: 0 
     }"
     @keydown.window="
        const now = Date.now();
        if (now - lastTime > 100) { rfidBuffer = ''; }
        lastTime = now;
        
        if ($event.key === 'Enter') {
            if (rfidBuffer.length > 5) { // Minimal length check
                console.log('RFID Scanned:', rfidBuffer);
                $wire.dispatch('handleRfidScan', { rfid: rfidBuffer });
                rfidBuffer = '';
                $event.preventDefault();
            }
        } else if ($event.key.length === 1) {
            rfidBuffer += $event.key;
        }
     ">
    
    <!-- Header -->
    <div class="text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 mb-2">Penarikan Saldo Santri</h2>
        <p class="text-gray-500 max-w-lg mx-auto">
            Scan kartu RFID santri untuk melakukan pencairan saldo menjadi uang tunai.
        </p>
    </div>

    <!-- RFID Scanning Area -->
    <div class="w-full max-w-md bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden relative">
        @if(!$selectedSantri)
            <!-- Scan Mode -->
            <div class="p-10 flex flex-col items-center justify-center text-center space-y-6 min-h-[300px]">
                <div class="relative">
                    <div class="absolute inset-0 bg-indigo-500 opacity-20 rounded-full animate-ping"></div>
                    <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center relative z-10 transition-transform duration-300 hover:scale-110">
                        <i class="fas fa-wifi text-4xl text-indigo-600"></i>
                    </div>
                </div>
                
                <h3 class="text-xl font-bold text-gray-800">Menunggu Scan Kartu...</h3>
                <p class="text-gray-500 text-sm">Tempelkan kartu pada reader RFID</p>
                
                <!-- Hidden RFID Input (Auto-focused by JS not strictly needed with window listener but good fallback) -->
                <input type="text" id="rfid-input" class="opacity-0 absolute inset-0 cursor-default" autocomplete="off">
            </div>
        @else
            <!-- Santri Data & Form -->
            <div class="p-6">
                <!-- Santri Profile -->
                <div class="flex items-center space-x-4 mb-6 pb-6 border-b border-gray-100">
                    <div class="w-14 h-14 bg-indigo-600 rounded-full flex items-center justify-center text-white text-xl font-bold shadow-md">
                        {{ substr($selectedSantri['nama_santri'] ?? '?', 0, 1) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-900 truncate">{{ $selectedSantri['nama_santri'] ?? 'Unknown' }}</h3>
                        <p class="text-sm text-gray-500">Kelas {{ $selectedSantri['kelas'] ?? '-' }}</p>
                        <p class="text-xs text-indigo-500 font-mono mt-1 w-full truncate">
                             {{ is_array($selectedSantri['rfid_tag']) ? ($selectedSantri['rfid_tag']['uid'] ?? '-') : ($selectedSantri['rfid_tag'] ?? $selectedSantri['rfid_uid'] ?? '-') }}
                        </p>
                    </div>
                    <button wire:click="cancel" class="text-gray-400 hover:text-red-500 transition-colors" title="Batalkan / Ganti Santri">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Balance Info -->
                <div class="bg-indigo-50 rounded-lg p-4 mb-6 flex justify-between items-center border border-indigo-100">
                    <span class="text-sm text-indigo-800 font-medium">Saldo Tersedia</span>
                    <span class="text-xl font-bold text-indigo-700">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                </div>

                <!-- Withdrawal Form -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nominal Penarikan</label>
                        <div class="relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <!-- Updated to use lazy updating to not re-render constantly -->
                            <input type="number" wire:model.blur="withdrawAmount" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 pr-4 sm:text-lg border-gray-300 rounded-md py-3 transition-colors" placeholder="0">
                        </div>
                        @error('withdrawAmount') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (Optional)</label>
                        <textarea wire:model.blur="notes" rows="2" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md transition-colors" placeholder="Keterangan penarikan..."></textarea>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button wire:click="cancel" class="flex-1 py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Batal
                        </button>
                        <button wire:click="confirmWithdrawal" class="flex-1 py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-lg shadow-indigo-200">
                            Lanjutkan
                        </button>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Loading Overlay -->
        <div wire:loading wire:target="handleRfidScan, processWithdrawal, confirmWithdrawal" class="absolute inset-0 bg-white/90 backdrop-blur-sm flex items-center justify-center z-20 rounded-xl transition-all duration-300">
            <div class="flex flex-col items-center animate-pulse">
                <i class="fas fa-circle-notch fa-spin text-4xl text-indigo-600 mb-3"></i>
                <p class="text-gray-600 font-medium">Memproses...</p>
            </div>
        </div>
    </div>
    
    <!-- Footer Help -->
    <div class="text-center text-sm text-gray-400 select-none">
        <p>Pastikan saldo mencukupi sebelum melakukan penarikan.</p>
        <p class="mt-1 text-xs opacity-75">Versi Sistem: 1.2.0 • EPOS Santri Withdrawal</p>
    </div>

    <!-- Confirmation Modal (Tailwind UI) -->
    @if($showConfirmation)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-filter backdrop-blur-sm" aria-hidden="true" wire:click="$set('showConfirmation', false)"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Heroicon name: outline/exclamation -->
                            <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Konfirmasi Penarikan Tunai
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">
                                    Anda akan melakukan penarikan saldo untuk santri <strong>{{ $selectedSantri['nama_santri'] }}</strong>.
                                </p>
                                
                                <div class="bg-yellow-50 p-4 rounded-md border border-yellow-100 mb-4">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm text-yellow-800">Nominal Penarikan:</span>
                                        <span class="text-lg font-bold text-gray-900">Rp {{ number_format($withdrawAmount, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center border-t border-yellow-200 pt-1 mt-1">
                                        <span class="text-xs text-yellow-700">Sisa Saldo:</span>
                                        <span class="text-sm font-semibold text-yellow-700">Rp {{ number_format($balance - $withdrawAmount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                
                                <p class="text-xs text-gray-400 italic">
                                    * Pastikan uang tunai sudah disiapkan sebelum menekan tombol konfirmasi.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" wire:click="processWithdrawal" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Konfirmasi & Cairkan
                    </button>
                    <button type="button" wire:click="$set('showConfirmation', false)" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

