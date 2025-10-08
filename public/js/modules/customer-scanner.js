// Customer Scanner Module for RFID Integration
if (typeof CustomerScanner === 'undefined') {
class CustomerScanner {
    constructor() {
        this.currentCustomer = null;
        this.isScanning = false;
        this.rfidReader = null;
        this.scannerSettings = {
            autoScan: true,
            scanInterval: 1000,
            minTagLength: 8
        };
        
        this.initializeEventListeners();
    }
    
    /**
     * Initialize event listeners for RFID scanning
     */
    initializeEventListeners() {
        // Listen for manual RFID input
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                this.showManualRfidInput();
            }
        });
        
        // Auto-focus RFID input when page loads
        document.addEventListener('DOMContentLoaded', () => {
            this.setupRfidInput();
        });
    }
    
    /**
     * Setup RFID input field
     */
    setupRfidInput() {
        const rfidInput = document.getElementById('rfid-input');
        if (rfidInput) {
            rfidInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.scanRFID(rfidInput.value.trim());
                    rfidInput.value = '';
                }
            });
            
            // Auto-focus
            rfidInput.focus();
        }
    }
    
    /**
     * Show manual RFID input modal
     */
    showManualRfidInput() {
        const rfidTag = prompt('Scan RFID atau masukkan tag manual:');
        if (rfidTag && rfidTag.trim()) {
            this.scanRFID(rfidTag.trim());
        }
    }
    
    /**
     * Main RFID scanning function
     */
    async scanRFID(rfidTag) {
        if (this.isScanning) {
            console.warn('Scanning already in progress');
            return false;
        }
        
        if (!rfidTag || rfidTag.length < this.scannerSettings.minTagLength) {
            this.showError('Tag RFID tidak valid (minimal 8 karakter)');
            return false;
        }
        
        this.isScanning = true;
        
        try {
            this.showLoading('Mencari data santri...');
            this.logScanAttempt(rfidTag);
            
            const response = await simpelsAPI.getSantriByRFID(rfidTag);
            
            if (response.success) {
                this.currentCustomer = response.data;
                this.displayCustomerInfo(this.currentCustomer);
                this.enableTransactionMode();
                this.logSuccessfulScan(this.currentCustomer);
                this.showSuccess(`Santri ditemukan: ${this.currentCustomer.nama_santri}`);
                return true;
            } else {
                this.showError(response.message || 'RFID tidak terdaftar');
                this.logFailedScan(rfidTag, response.message);
                return false;
            }
        } catch (error) {
            // Use error handler for intelligent error notification
            if (window.errorHandler) {
                const context = {
                    operation: 'rfid_scan',
                    rfid_tag: rfidTag,
                    retryCallback: () => this.scanRFID(rfidTag)
                };
                window.errorHandler.handleAPIError(error, context);
            } else {
                this.showError('Gagal memuat data santri: ' + error.message);
            }
            this.logScanError(rfidTag, error);
            return false;
        } finally {
            this.hideLoading();
            this.isScanning = false;
            
            // Refocus RFID input for next scan
            const rfidInput = document.getElementById('rfid-input');
            if (rfidInput) {
                setTimeout(() => rfidInput.focus(), 100);
            }
        }
    }
    
    /**
     * Display customer information in UI
     */
    displayCustomerInfo(customer) {
        // Update customer panel
        const customerPanel = document.getElementById('customer-panel');
        const customerName = document.getElementById('customer-name');
        const customerNis = document.getElementById('customer-nis');
        const customerClass = document.getElementById('customer-class');
        const customerBalance = document.getElementById('customer-balance');
        const customerPhoto = document.getElementById('customer-photo');
        const customerAsrama = document.getElementById('customer-asrama');
        
        if (customerPanel) customerPanel.style.display = 'block';
        if (customerName) customerName.textContent = customer.nama_santri;
        if (customerNis) customerNis.textContent = customer.nis || '-';
        if (customerClass) customerClass.textContent = customer.kelas || '-';
        if (customerAsrama) customerAsrama.textContent = customer.asrama || '-';
        if (customerBalance) {
            customerBalance.textContent = this.formatCurrency(customer.saldo);
            customerBalance.className = customer.saldo > 10000 ? 'text-green-600 font-bold' : 'text-red-600 font-bold';
        }
        
        // Update customer photo
        if (customerPhoto && customer.foto) {
            customerPhoto.src = `http://localhost:8000/${customer.foto}`;
            customerPhoto.style.display = 'block';
        } else if (customerPhoto) {
            customerPhoto.style.display = 'none';
        }
        
        // Update customer info in transaction area
        this.updateTransactionCustomerInfo(customer);
    }
    
    /**
     * Update customer info in transaction/POS area
     */
    updateTransactionCustomerInfo(customer) {
        // Update selected customer display in existing POS system
        const selectedCustomerDisplay = document.getElementById('selectedSantri');
        if (selectedCustomerDisplay) {
            selectedCustomerDisplay.innerHTML = `
                <div class="flex items-center space-x-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                        ${customer.nama_santri.substring(0, 2).toUpperCase()}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">${customer.nama_santri}</p>
                        <p class="text-sm text-gray-600">${customer.kelas || 'No Class'} • Saldo: ${this.formatCurrency(customer.saldo)}</p>
                    </div>
                    <button onclick="customerScanner.clearCustomer()" class="ml-auto text-red-600 hover:text-red-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }
    }
    
    /**
     * Enable transaction mode
     */
    enableTransactionMode() {
        // Enable checkout button
        const checkoutBtn = document.getElementById('checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.classList.remove('opacity-50');
        }
        
        // Enable add item buttons
        const addItemBtns = document.querySelectorAll('.add-item-btn');
        addItemBtns.forEach(btn => {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        });
        
        // Show RFID payment option
        const rfidPaymentOption = document.getElementById('rfid-payment-option');
        if (rfidPaymentOption) {
            rfidPaymentOption.style.display = 'block';
        }
        
        // Update payment method selection
        const paymentMethod = document.querySelector('input[name="payment_method"][value="rfid"]');
        if (paymentMethod) {
            paymentMethod.checked = true;
            paymentMethod.dispatchEvent(new Event('change'));
        }
    }
    
    /**
     * Clear current customer
     */
    clearCustomer() {
        this.currentCustomer = null;
        
        // Hide customer panel
        const customerPanel = document.getElementById('customer-panel');
        if (customerPanel) customerPanel.style.display = 'none';
        
        // Clear selected customer display
        const selectedCustomerDisplay = document.getElementById('selectedSantri');
        if (selectedCustomerDisplay) {
            selectedCustomerDisplay.innerHTML = `
                <div class="text-center p-4 text-gray-500">
                    <i class="fas fa-user-slash text-2xl mb-2"></i>
                    <p>Tidak ada santri yang dipilih</p>
                    <p class="text-sm">Scan RFID untuk memilih santri</p>
                </div>
            `;
        }
        
        this.disableTransactionMode();
        this.logCustomerCleared();
        this.showInfo('Customer cleared');
    }
    
    /**
     * Disable transaction mode
     */
    disableTransactionMode() {
        // Disable checkout button
        const checkoutBtn = document.getElementById('checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.classList.add('opacity-50');
        }
        
        // Hide RFID payment option
        const rfidPaymentOption = document.getElementById('rfid-payment-option');
        if (rfidPaymentOption) {
            rfidPaymentOption.style.display = 'none';
        }
        
        // Reset payment method
        const cashPaymentMethod = document.querySelector('input[name="payment_method"][value="cash"]');
        if (cashPaymentMethod) {
            cashPaymentMethod.checked = true;
            cashPaymentMethod.dispatchEvent(new Event('change'));
        }
    }
    
    /**
     * Refresh customer data
     */
    async refreshCustomerData() {
        if (!this.currentCustomer || !this.currentCustomer.rfid_tag) {
            return false;
        }
        
        try {
            this.showLoading('Memperbarui data santri...');
            const response = await simpelsAPI.getSantriByRFID(this.currentCustomer.rfid_tag);
            
            if (response.success) {
                this.currentCustomer = response.data;
                this.displayCustomerInfo(this.currentCustomer);
                this.showSuccess('Data santri diperbarui');
                return true;
            } else {
                this.showError('Gagal memperbarui data: ' + response.message);
                return false;
            }
        } catch (error) {
            // Use error handler for intelligent error notification
            if (window.errorHandler) {
                const context = {
                    operation: 'balance_check',
                    rfid_tag: this.currentCustomer.rfid_tag,
                    customer_name: this.currentCustomer.nama_santri,
                    retryCallback: () => this.refreshCustomerData()
                };
                window.errorHandler.handleAPIError(error, context);
            } else {
                this.showError('Error memperbarui data: ' + error.message);
            }
            return false;
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Utility functions
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    showLoading(message = 'Loading...') {
        // Implementation depends on your existing loading system
        console.log(`Loading: ${message}`);
        // Example:
        // window.showLoading && window.showLoading(message);
    }
    
    hideLoading() {
        // Implementation depends on your existing loading system
        console.log('Hide loading');
        // Example:
        // window.hideLoading && window.hideLoading();
    }
    
    showSuccess(message) {
        console.log(`Success: ${message}`);
        // Integrate with your notification system
        if (window.showToast) {
            window.showToast(message, 'success');
        }
    }
    
    showError(message) {
        console.error(`Error: ${message}`);
        // Integrate with your notification system
        if (window.showToast) {
            window.showToast(message, 'error');
        } else {
            alert(message);
        }
    }
    
    showInfo(message) {
        console.info(`Info: ${message}`);
        if (window.showToast) {
            window.showToast(message, 'info');
        }
    }
    
    showWarning(message) {
        console.warn(`Warning: ${message}`);
        if (window.showToast) {
            window.showToast(message, 'warning');
        }
    }
    
    /**
     * Logging functions
     */
    logScanAttempt(rfidTag) {
        console.log(`[CustomerScanner] Scan attempt: ${rfidTag}`);
    }
    
    logSuccessfulScan(customer) {
        console.log(`[CustomerScanner] Successful scan:`, customer);
    }
    
    logFailedScan(rfidTag, message) {
        console.warn(`[CustomerScanner] Failed scan: ${rfidTag} - ${message}`);
    }
    
    logScanError(rfidTag, error) {
        console.error(`[CustomerScanner] Scan error: ${rfidTag}`, error);
    }
    
    logCustomerCleared() {
        console.log(`[CustomerScanner] Customer cleared`);
    }
}

// Create global instance only if not exists
if (!window.customerScanner) {
    window.customerScanner = new CustomerScanner();
}

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CustomerScanner;
}

} // End of conditional class definition