// Transaction Processor for SIMPels Integration
if (typeof TransactionProcessor === 'undefined') {
class TransactionProcessor {
    constructor() {
        this.currentTransaction = null;
        this.isProcessing = false;
        this.offlineQueue = this.loadOfflineQueue();
        this.transactionCounter = this.loadTransactionCounter();
        
        // Initialize periodic sync for offline transactions
        this.startOfflineSync();
    }
    
    /**
     * Generate unique transaction reference
     */
    generateTransactionRef() {
        const timestamp = Date.now();
        const counter = this.getNextTransactionCounter();
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return `EPOS${timestamp}${counter}${random}`;
    }
    
    /**
     * Get next transaction counter
     */
    getNextTransactionCounter() {
        this.transactionCounter++;
        this.saveTransactionCounter();
        return this.transactionCounter.toString().padStart(4, '0');
    }
    
    /**
     * Save transaction counter to localStorage
     */
    saveTransactionCounter() {
        localStorage.setItem('epos_transaction_counter', this.transactionCounter.toString());
    }
    
    /**
     * Load transaction counter from localStorage
     */
    loadTransactionCounter() {
        const saved = localStorage.getItem('epos_transaction_counter');
        return saved ? parseInt(saved) : 1;
    }
    
    /**
     * Validate transaction before processing
     */
    async validateTransaction(customer, cart, totalAmount) {
        const errors = [];
        
        // Real RFID Payment Mode - Production validation
        
        try {
            // Validate customer
            if (!customer) {
                errors.push('Customer tidak ditemukan. Silakan scan RFID terlebih dahulu.');
            }
            
            // Validate cart
            if (!cart || cart.length === 0) {
                errors.push('Keranjang belanja kosong');
            }
            
            // Validate amount
            if (!totalAmount || totalAmount <= 0) {
                errors.push('Total amount tidak valid');
            }
            
            if (errors.length > 0) {
                throw new Error(errors.join(', '));
            }
            
            // Check customer balance
            if (customer.saldo < totalAmount) {
                throw new Error(`Saldo tidak mencukupi. Saldo: ${this.formatCurrency(customer.saldo)}, Dibutuhkan: ${this.formatCurrency(totalAmount)}`);
            }
            
            // Validate transaction limits via API
            const limitResponse = await simpelsAPI.checkTransactionLimit(customer.rfid_tag, totalAmount);
            
            if (!limitResponse.success) {
                throw new Error(limitResponse.message || 'Transaksi melebihi limit yang diizinkan');
            }
            
            // Additional business validation
            if (totalAmount > 500000) { // 500k limit
                errors.push('Transaksi melebihi limit maksimal Rp 500.000');
            }
            
            // Validate individual items
            for (const item of cart) {
                if (!item.id || !item.name || !item.price || !item.quantity) {
                    errors.push(`Item tidak valid: ${item.name || 'Unknown item'}`);
                }
                
                if (item.quantity <= 0) {
                    errors.push(`Quantity tidak valid untuk item: ${item.name}`);
                }
                
                if (item.price <= 0) {
                    errors.push(`Harga tidak valid untuk item: ${item.name}`);
                }
            }
            
            if (errors.length > 0) {
                throw new Error(errors.join(', '));
            }
            
            return true;
            
        } catch (error) {
            throw error;
        }
    }
    
    /**
     * Process payment transaction
     */
    async processPayment(customer, cart, totalAmount, paymentMethod = 'rfid') {
        if (this.isProcessing) {
            throw new Error('Transaksi sedang diproses. Silakan tunggu...');
        }
        
        this.isProcessing = true;
        
        try {
            // Generate transaction reference
            const transactionRef = this.generateTransactionRef();
            this.currentTransaction = {
                ref: transactionRef,
                customer: customer,
                cart: [...cart],
                totalAmount: totalAmount,
                paymentMethod: paymentMethod,
                timestamp: new Date().toISOString(),
                status: 'processing'
            };
            
            this.logTransactionStart(this.currentTransaction);
            
            // Validate transaction
            await this.validateTransaction(customer, cart, totalAmount);
            
            // Create transaction description
            const description = cart.map(item => 
                `${item.name} (${item.quantity}x)`
            ).join(', ');
            
            // Process payment via API
            const deductResponse = await simpelsAPI.deductBalance(
                customer.id,
                totalAmount,
                description,
                transactionRef
            );
            
            if (!deductResponse.success) {
                throw new Error(deductResponse.message || 'Pembayaran gagal');
            }
            
            // Update transaction status
            this.currentTransaction.status = 'paid';
            this.currentTransaction.newBalance = deductResponse.data.saldo_sesudah;
            this.currentTransaction.deductionId = deductResponse.data.id;
            
            // Sync transaction details to SIMPels
            await this.syncTransactionDetails(customer.id, cart, totalAmount, transactionRef);
            
            // Update customer balance in scanner
            customer.saldo = deductResponse.data.saldo_sesudah;
            customerScanner.displayCustomerInfo(customer);
            
            // Log successful transaction
            this.logTransactionSuccess(this.currentTransaction);
            
            return {
                success: true,
                transactionRef: transactionRef,
                newBalance: deductResponse.data.saldo_sesudah,
                deductionId: deductResponse.data.id,
                timestamp: this.currentTransaction.timestamp
            };
            
        } catch (error) {
            // Update transaction status
            if (this.currentTransaction) {
                this.currentTransaction.status = 'failed';
                this.currentTransaction.error = error.message;
            }
            
            this.logTransactionError(this.currentTransaction, error);
            throw error;
        } finally {
            this.isProcessing = false;
        }
    }
    
    /**
     * Sync transaction details to SIMPels
     */
    async syncTransactionDetails(santriId, cart, totalAmount, transactionRef) {
        try {
            const syncData = {
                epos_transaction_id: transactionRef,
                santri_id: santriId,
                total_amount: totalAmount,
                items: cart.map(item => ({
                    product_id: item.id,
                    product_name: item.name,
                    quantity: item.quantity,
                    price: item.price,
                    subtotal: item.quantity * item.price
                })),
                payment_method: 'rfid',
                transaction_date: new Date().toISOString(),
                cashier_name: this.getCurrentCashierName(),
                pos_terminal_id: this.getPosTerminalId()
            };
            
            const response = await simpelsAPI.syncTransaction(syncData);
            
            if (!response.success) {
                console.warn('Transaction sync failed:', response.message);
                this.queueForOfflineSync(syncData);
            } else {
                console.log('Transaction synced successfully:', response.data);
            }
            
        } catch (error) {
            console.error('Sync error:', error);
            // Queue for offline sync
            this.queueForOfflineSync({
                epos_transaction_id: transactionRef,
                santri_id: santriId,
                total_amount: totalAmount,
                items: cart.map(item => ({
                    product_id: item.id,
                    product_name: item.name,
                    quantity: item.quantity,
                    price: item.price,
                    subtotal: item.quantity * item.price
                })),
                payment_method: 'rfid',
                transaction_date: new Date().toISOString(),
                cashier_name: this.getCurrentCashierName(),
                pos_terminal_id: this.getPosTerminalId()
            });
        }
    }
    
    /**
     * Queue transaction for offline sync
     */
    queueForOfflineSync(syncData) {
        this.offlineQueue.push({
            ...syncData,
            queued_at: new Date().toISOString(),
            retry_count: 0,
            status: 'pending'
        });
        
        this.saveOfflineQueue();
        console.log('Transaction queued for offline sync:', syncData.epos_transaction_id);
    }
    
    /**
     * Save offline queue to localStorage
     */
    saveOfflineQueue() {
        localStorage.setItem('epos_offline_queue', JSON.stringify(this.offlineQueue));
    }
    
    /**
     * Load offline queue from localStorage
     */
    loadOfflineQueue() {
        const saved = localStorage.getItem('epos_offline_queue');
        return saved ? JSON.parse(saved) : [];
    }
    
    /**
     * Start periodic offline sync
     */
    startOfflineSync() {
        // Sync every 5 minutes
        setInterval(() => {
            this.syncOfflineTransactions();
        }, 5 * 60 * 1000);
        
        // Initial sync after 10 seconds
        setTimeout(() => {
            this.syncOfflineTransactions();
        }, 10000);
    }
    
    /**
     * Sync offline transactions
     */
    async syncOfflineTransactions() {
        if (this.offlineQueue.length === 0) {
            return;
        }
        
        console.log(`Attempting to sync ${this.offlineQueue.length} offline transactions`);
        
        const successfulSyncs = [];
        
        for (const transaction of this.offlineQueue) {
            if (transaction.retry_count >= 5) {
                console.warn('Max retries reached for transaction:', transaction.epos_transaction_id);
                continue;
            }
            
            try {
                const response = await simpelsAPI.syncTransaction(transaction);
                
                if (response.success) {
                    successfulSyncs.push(transaction);
                    console.log('Offline transaction synced:', transaction.epos_transaction_id);
                } else {
                    transaction.retry_count++;
                    console.warn('Offline sync failed:', transaction.epos_transaction_id, response.message);
                }
            } catch (error) {
                transaction.retry_count++;
                console.error('Offline sync error:', transaction.epos_transaction_id, error.message);
            }
        }
        
        // Remove successfully synced transactions
        this.offlineQueue = this.offlineQueue.filter(
            transaction => !successfulSyncs.includes(transaction)
        );
        
        this.saveOfflineQueue();
        
        if (successfulSyncs.length > 0) {
            console.log(`Successfully synced ${successfulSyncs.length} offline transactions`);
        }
    }
    
    /**
     * Get current cashier name
     */
    getCurrentCashierName() {
        // Get from session or user data
        const user = window.currentUser || {};
        return user.name || 'Unknown Cashier';
    }
    
    /**
     * Get POS terminal ID
     */
    getPosTerminalId() {
        // Get from system configuration
        return localStorage.getItem('pos_terminal_id') || 'EPOS_TERMINAL_01';
    }
    
    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }
    
    /**
     * Get transaction history
     */
    getTransactionHistory(limit = 50) {
        const history = JSON.parse(localStorage.getItem('epos_transaction_history') || '[]');
        return history.slice(0, limit);
    }
    
    /**
     * Save transaction to history
     */
    saveTransactionToHistory(transaction) {
        let history = JSON.parse(localStorage.getItem('epos_transaction_history') || '[]');
        history.unshift(transaction);
        
        // Keep only last 1000 transactions
        if (history.length > 1000) {
            history = history.slice(0, 1000);
        }
        
        localStorage.setItem('epos_transaction_history', JSON.stringify(history));
    }
    
    /**
     * Logging functions
     */
    logTransactionStart(transaction) {
        console.log('[TransactionProcessor] Transaction started:', transaction.ref);
        this.saveTransactionToHistory({
            ...transaction,
            event: 'started'
        });
    }
    
    logTransactionSuccess(transaction) {
        console.log('[TransactionProcessor] Transaction successful:', transaction.ref);
        this.saveTransactionToHistory({
            ...transaction,
            event: 'completed'
        });
    }
    
    logTransactionError(transaction, error) {
        console.error('[TransactionProcessor] Transaction failed:', transaction?.ref, error);
        if (transaction) {
            this.saveTransactionToHistory({
                ...transaction,
                event: 'failed',
                error: error.message
            });
        }
    }
    
    /**
     * Get offline queue status
     */
    getOfflineQueueStatus() {
        return {
            total: this.offlineQueue.length,
            pending: this.offlineQueue.filter(t => t.status === 'pending').length,
            failed: this.offlineQueue.filter(t => t.retry_count >= 5).length
        };
    }
    
    /**
     * Clear offline queue
     */
    clearOfflineQueue() {
        this.offlineQueue = [];
        this.saveOfflineQueue();
        console.log('Offline queue cleared');
    }
}

// Create global instance only if not exists
if (!window.transactionProcessor) {
    window.transactionProcessor = new TransactionProcessor();
}

// Export for module use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TransactionProcessor;
}

} // End of conditional class definition