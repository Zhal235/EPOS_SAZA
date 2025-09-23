// Refund Processor for SIMPels Integration
class RefundProcessor {
    constructor() {
        this.isProcessing = false;
        this.refundHistory = this.loadRefundHistory();
    }
    
    /**
     * Process refund transaction
     */
    async processRefund(originalTransactionRef, refundAmount, reason) {
        if (this.isProcessing) {
            throw new Error('Refund sedang diproses. Silakan tunggu...');
        }
        
        this.isProcessing = true;
        
        try {
            // Find original transaction
            const originalTransaction = await this.findOriginalTransaction(originalTransactionRef);
            
            if (!originalTransaction) {
                throw new Error('Transaksi asli tidak ditemukan');
            }
            
            // Validate refund amount
            if (refundAmount <= 0) {
                throw new Error('Nominal refund harus lebih besar dari 0');
            }
            
            if (refundAmount > originalTransaction.totalAmount) {
                throw new Error('Nominal refund tidak boleh melebihi total transaksi asli');
            }
            
            // Check if refund already processed
            const existingRefund = await this.checkExistingRefund(originalTransactionRef);
            if (existingRefund) {
                throw new Error('Refund untuk transaksi ini sudah pernah diproses');
            }
            
            // Process refund via API
            const response = await simpelsAPI.processRefund(
                originalTransaction.santri_id,
                refundAmount,
                originalTransactionRef,
                reason
            );
            
            if (!response.success) {
                throw new Error(response.message || 'Refund gagal diproses');
            }
            
            // Generate refund reference
            const refundRef = this.generateRefundRef();
            
            // Save refund record
            const refundRecord = {
                refund_ref: refundRef,
                original_transaction_ref: originalTransactionRef,
                santri_id: originalTransaction.santri_id,
                refund_amount: refundAmount,
                reason: reason,
                new_balance: response.data.saldo_sesudah,
                processed_at: new Date().toISOString(),
                processed_by: this.getCurrentCashierName(),
                status: 'completed'
            };
            
            this.saveRefundRecord(refundRecord);
            this.logRefundSuccess(refundRecord);
            
            return {
                success: true,
                refundRef: refundRef,
                newBalance: response.data.saldo_sesudah,
                refundRecord: refundRecord
            };
            
        } catch (error) {
            this.logRefundError(originalTransactionRef, error);
            throw error;
        } finally {
            this.isProcessing = false;
        }
    }
    
    /**
     * Find original transaction
     */
    async findOriginalTransaction(transactionRef) {
        // First try to find in local transaction history
        const localTransaction = this.findLocalTransaction(transactionRef);
        if (localTransaction) {
            return localTransaction;
        }
        
        // If not found locally, try to get from API or database
        // This would require additional API endpoint for transaction lookup
        try {
            // Placeholder for API call to get transaction details
            console.warn('Transaction not found locally, API lookup not implemented yet');
            return null;
        } catch (error) {
            console.error('Error looking up transaction:', error);
            return null;
        }
    }
    
    /**
     * Find transaction in local history
     */
    findLocalTransaction(transactionRef) {
        const history = JSON.parse(localStorage.getItem('epos_transaction_history') || '[]');
        return history.find(t => t.ref === transactionRef && t.status === 'completed');
    }
    
    /**
     * Check if refund already exists for transaction
     */
    async checkExistingRefund(transactionRef) {
        return this.refundHistory.find(r => r.original_transaction_ref === transactionRef);
    }
    
    /**
     * Generate refund reference
     */
    generateRefundRef() {
        const timestamp = Date.now();
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return `REFUND${timestamp}${random}`;
    }
    
    /**
     * Save refund record
     */
    saveRefundRecord(refundRecord) {
        this.refundHistory.unshift(refundRecord);
        
        // Keep only last 500 refunds
        if (this.refundHistory.length > 500) {
            this.refundHistory = this.refundHistory.slice(0, 500);
        }
        
        this.saveRefundHistory();
    }
    
    /**
     * Save refund history to localStorage
     */
    saveRefundHistory() {
        localStorage.setItem('epos_refund_history', JSON.stringify(this.refundHistory));
    }
    
    /**
     * Load refund history from localStorage
     */
    loadRefundHistory() {
        const saved = localStorage.getItem('epos_refund_history');
        return saved ? JSON.parse(saved) : [];
    }
    
    /**
     * Get refund history
     */
    getRefundHistory(limit = 50) {
        return this.refundHistory.slice(0, limit);
    }
    
    /**
     * Show refund dialog
     */
    async showRefundDialog() {
        return new Promise((resolve) => {
            // Create modal dialog
            const modal = this.createRefundModal();
            document.body.appendChild(modal);
            
            // Setup form handlers
            const form = modal.querySelector('#refund-form');
            const cancelBtn = modal.querySelector('#cancel-refund');
            const closeBtn = modal.querySelector('#close-refund');
            
            const cleanup = () => {
                document.body.removeChild(modal);
            };
            
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const formData = new FormData(form);
                const refundData = {
                    originalTransactionRef: formData.get('original_transaction_ref'),
                    refundAmount: parseFloat(formData.get('refund_amount')),
                    reason: formData.get('reason')
                };
                
                cleanup();
                resolve(refundData);
            });
            
            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(null);
            });
            
            closeBtn.addEventListener('click', () => {
                cleanup();
                resolve(null);
            });
        });
    }
    
    /**
     * Create refund modal
     */
    createRefundModal() {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50';
        modal.innerHTML = `
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Process Refund</h3>
                        <button id="close-refund" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="refund-form" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Transaction Reference *
                            </label>
                            <input type="text" name="original_transaction_ref" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="e.g. EPOS1632456789001123">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Refund Amount *
                            </label>
                            <input type="number" name="refund_amount" required min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                   placeholder="0">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Reason *
                            </label>
                            <select name="reason" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                                <option value="">Select reason</option>
                                <option value="barang_rusak">Barang Rusak</option>
                                <option value="salah_item">Salah Item</option>
                                <option value="permintaan_customer">Permintaan Customer</option>
                                <option value="kesalahan_kasir">Kesalahan Kasir</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                        
                        <div class="flex space-x-3 pt-4">
                            <button type="button" id="cancel-refund"
                                    class="flex-1 px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                Process Refund
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        return modal;
    }
    
    /**
     * Process refund from dialog
     */
    async processRefundFromDialog() {
        try {
            const refundData = await this.showRefundDialog();
            
            if (!refundData) {
                return; // User cancelled
            }
            
            // Show loading
            this.showLoading('Processing refund...');
            
            const result = await this.processRefund(
                refundData.originalTransactionRef,
                refundData.refundAmount,
                refundData.reason
            );
            
            if (result.success) {
                this.showSuccess(`Refund berhasil! Referensi: ${result.refundRef}`);
                
                // Update customer balance if customer is currently selected
                if (customerScanner.currentCustomer && 
                    customerScanner.currentCustomer.id === result.refundRecord.santri_id) {
                    customerScanner.currentCustomer.saldo = result.newBalance;
                    customerScanner.displayCustomerInfo(customerScanner.currentCustomer);
                }
                
                // Print refund receipt if needed
                this.printRefundReceipt(result.refundRecord);
            }
            
        } catch (error) {
            this.showError('Refund gagal: ' + error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    /**
     * Print refund receipt
     */
    printRefundReceipt(refundRecord) {
        console.log('Printing refund receipt:', refundRecord);
        // Implement based on your receipt printer system
    }
    
    /**
     * Get current cashier name
     */
    getCurrentCashierName() {
        const user = window.currentUser || {};
        return user.name || 'Unknown Cashier';
    }
    
    /**
     * Utility functions
     */
    showLoading(message = 'Loading...') {
        console.log('Loading:', message);
        // Integrate with your loading system
    }
    
    hideLoading() {
        console.log('Hide loading');
        // Integrate with your loading system
    }
    
    showSuccess(message) {
        console.log('Success:', message);
        if (window.showNotification) {
            window.showNotification(message, 'success');
        } else {
            alert(message);
        }
    }
    
    showError(message) {
        console.error('Error:', message);
        if (window.showNotification) {
            window.showNotification(message, 'error');
        } else {
            alert('Error: ' + message);
        }
    }
    
    /**
     * Logging functions
     */
    logRefundSuccess(refundRecord) {
        console.log('[RefundProcessor] Refund successful:', refundRecord);
    }
    
    logRefundError(transactionRef, error) {
        console.error('[RefundProcessor] Refund failed:', transactionRef, error);
    }
}

// Create global instance
window.refundProcessor = new RefundProcessor();

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RefundProcessor;
}