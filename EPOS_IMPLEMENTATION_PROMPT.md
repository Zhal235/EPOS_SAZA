# 🚀 Prompt Penerapan Integrasi API SIMPels ke ePOS

## 📋 Checklist Implementasi

### Phase 1: Persiapan Infrastruktur
- [ ] **Server SIMPels** berjalan di `http://localhost:8000`
- [ ] **Database SIMPels** terisi data santri dan RFID
- [ ] **Network connection** antara ePOS dan SIMPels server
- [ ] **RFID Reader** terkonfigurasi di sistem ePOS
- [ ] **Menu "Customers"** sudah tersedia di ePOS

### Phase 2: Konfigurasi API Endpoint
- [ ] Test koneksi: `GET /api/epos/limit/summary`
- [ ] Validasi response format JSON
- [ ] Setup error handling untuk network issues
- [ ] Konfigurasi timeout (30 detik recommended)

### Phase 3: Implementasi Customer Module
- [ ] **RFID Scanner Integration**
- [ ] **Customer Data Display**
- [ ] **Balance Checking**
- [ ] **Limit Validation**

### Phase 4: Payment Processing
- [ ] **Transaction Validation**
- [ ] **Saldo Deduction**
- [ ] **Transaction Sync**
- [ ] **Receipt Generation**

### Phase 5: Additional Features
- [ ] **Refund Processing**
- [ ] **Offline Mode**
- [ ] **Error Recovery**
- [ ] **Audit Logging**

---

## 🛠️ Implementasi Step-by-Step

### Step 1: Setup API Configuration
```javascript
// config/api.js
const API_CONFIG = {
    baseURL: 'http://localhost:8000/api/epos',
    timeout: 30000,
    retries: 3,
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
};

// utils/api.js
class SIMPelsAPI {
    constructor() {
        this.baseURL = API_CONFIG.baseURL;
        this.timeout = API_CONFIG.timeout;
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            timeout: this.timeout,
            headers: API_CONFIG.headers,
            ...options
        };
        
        try {
            const response = await fetch(url, config);
            return await response.json();
        } catch (error) {
            throw new Error(`API Error: ${error.message}`);
        }
    }
}

const api = new SIMPelsAPI();
```

### Step 2: Customer Scanner Module
```javascript
// modules/customer-scanner.js
class CustomerScanner {
    constructor() {
        this.currentCustomer = null;
        this.rfidReader = null;
    }
    
    async scanRFID(rfidTag) {
        try {
            showLoading('Mencari data santri...');
            
            const response = await api.request(`/santri/rfid/${rfidTag}`);
            
            if (response.success) {
                this.currentCustomer = response.data;
                this.displayCustomerInfo(this.currentCustomer);
                this.enableTransactionMode();
                return true;
            } else {
                showError(response.message);
                return false;
            }
        } catch (error) {
            showError('Gagal memuat data santri: ' + error.message);
            return false;
        } finally {
            hideLoading();
        }
    }
    
    displayCustomerInfo(customer) {
        // Update UI elements
        document.getElementById('customer-name').textContent = customer.nama_santri;
        document.getElementById('customer-nis').textContent = customer.nis;
        document.getElementById('customer-class').textContent = customer.kelas || '-';
        document.getElementById('customer-balance').textContent = formatCurrency(customer.saldo);
        
        // Show customer panel
        document.getElementById('customer-panel').style.display = 'block';
    }
    
    enableTransactionMode() {
        document.getElementById('add-item-btn').disabled = false;
        document.getElementById('checkout-btn').disabled = false;
    }
    
    clearCustomer() {
        this.currentCustomer = null;
        document.getElementById('customer-panel').style.display = 'none';
        this.disableTransactionMode();
    }
    
    disableTransactionMode() {
        document.getElementById('add-item-btn').disabled = true;
        document.getElementById('checkout-btn').disabled = true;
    }
}
```

### Step 3: Transaction Processor
```javascript
// modules/transaction-processor.js
class TransactionProcessor {
    constructor() {
        this.cart = [];
        this.currentTransactionRef = null;
    }
    
    generateTransactionRef() {
        const timestamp = Date.now();
        const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        return `EPOS${timestamp}${random}`;
    }
    
    async validateTransaction(customer, totalAmount) {
        try {
            // Check balance
            if (customer.saldo < totalAmount) {
                throw new Error(`Saldo tidak mencukupi. Saldo: ${formatCurrency(customer.saldo)}, Dibutuhkan: ${formatCurrency(totalAmount)}`);
            }
            
            // Check limits
            const limitResponse = await api.request('/limit/check-rfid', {
                method: 'POST',
                body: JSON.stringify({
                    rfid_tag: customer.rfid_tag,
                    amount: totalAmount
                })
            });
            
            if (!limitResponse.success) {
                throw new Error(limitResponse.message);
            }
            
            return true;
        } catch (error) {
            throw error;
        }
    }
    
    async processPayment(customer, cart, totalAmount) {
        try {
            showLoading('Memproses pembayaran...');
            
            // Generate transaction reference
            this.currentTransactionRef = this.generateTransactionRef();
            
            // Validate transaction
            await this.validateTransaction(customer, totalAmount);
            
            // Create transaction description
            const description = cart.map(item => 
                `${item.name} (${item.quantity}x)`
            ).join(', ');
            
            // Deduct balance
            const deductResponse = await api.request(`/santri/${customer.id}/deduct`, {
                method: 'POST',
                body: JSON.stringify({
                    nominal: totalAmount,
                    keterangan: description,
                    transaction_ref: this.currentTransactionRef
                })
            });
            
            if (!deductResponse.success) {
                throw new Error(deductResponse.message);
            }
            
            // Sync transaction details
            await this.syncTransaction(customer.id, cart, totalAmount);
            
            // Update customer balance in UI
            customer.saldo = deductResponse.data.saldo_sesudah;
            customerScanner.displayCustomerInfo(customer);
            
            return {
                success: true,
                transactionRef: this.currentTransactionRef,
                newBalance: deductResponse.data.saldo_sesudah
            };
            
        } catch (error) {
            throw error;
        } finally {
            hideLoading();
        }
    }
    
    async syncTransaction(santriId, cart, totalAmount) {
        try {
            const syncData = {
                epos_transaction_id: this.currentTransactionRef,
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
                cashier_name: getCurrentCashier()
            };
            
            const response = await api.request('/transaction/sync', {
                method: 'POST',
                body: JSON.stringify(syncData)
            });
            
            if (!response.success) {
                console.warn('Transaction sync failed:', response.message);
                // Queue for retry
                this.queueFailedSync(syncData);
            }
        } catch (error) {
            console.error('Sync error:', error);
            this.queueFailedSync(syncData);
        }
    }
    
    queueFailedSync(syncData) {
        // Store failed syncs for retry
        let failedSyncs = JSON.parse(localStorage.getItem('failed_syncs') || '[]');
        failedSyncs.push({
            ...syncData,
            timestamp: Date.now()
        });
        localStorage.setItem('failed_syncs', JSON.stringify(failedSyncs));
    }
}
```

### Step 4: UI Integration
```javascript
// main.js - Integration with existing ePOS UI
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modules
    const customerScanner = new CustomerScanner();
    const transactionProcessor = new TransactionProcessor();
    
    // RFID scan event (adjust based on your RFID reader)
    document.getElementById('rfid-scan-btn').addEventListener('click', async () => {
        const rfidTag = prompt('Scan RFID or enter tag manually:');
        if (rfidTag) {
            await customerScanner.scanRFID(rfidTag);
        }
    });
    
    // Checkout process
    document.getElementById('checkout-btn').addEventListener('click', async () => {
        if (!customerScanner.currentCustomer) {
            showError('Silakan scan RFID santri terlebih dahulu');
            return;
        }
        
        const cart = getCartItems(); // Your existing cart function
        const totalAmount = calculateTotal(cart); // Your existing total calculation
        
        if (cart.length === 0) {
            showError('Keranjang masih kosong');
            return;
        }
        
        try {
            const result = await transactionProcessor.processPayment(
                customerScanner.currentCustomer,
                cart,
                totalAmount
            );
            
            if (result.success) {
                showSuccess(`Pembayaran berhasil! Saldo tersisa: ${formatCurrency(result.newBalance)}`);
                printReceipt(result.transactionRef, cart, totalAmount);
                clearCart(); // Your existing function
                customerScanner.clearCustomer();
            }
        } catch (error) {
            showError(error.message);
        }
    });
    
    // Clear customer
    document.getElementById('clear-customer-btn').addEventListener('click', () => {
        customerScanner.clearCustomer();
    });
});
```

### Step 5: Refund Module
```javascript
// modules/refund-processor.js
class RefundProcessor {
    async processRefund(originalTransactionRef, refundAmount, reason) {
        try {
            showLoading('Memproses refund...');
            
            // Find original transaction (you need to implement this based on your transaction storage)
            const originalTransaction = await this.findOriginalTransaction(originalTransactionRef);
            
            if (!originalTransaction) {
                throw new Error('Transaksi asli tidak ditemukan');
            }
            
            const response = await api.request(`/santri/${originalTransaction.santri_id}/refund`, {
                method: 'POST',
                body: JSON.stringify({
                    nominal: refundAmount,
                    original_transaction_ref: originalTransactionRef,
                    refund_reason: reason
                })
            });
            
            if (response.success) {
                showSuccess('Refund berhasil diproses');
                return response.data;
            } else {
                throw new Error(response.message);
            }
        } catch (error) {
            throw error;
        } finally {
            hideLoading();
        }
    }
}
```

---

## 🎯 Testing Scenarios

### Test Case 1: Normal Transaction
```
1. Scan RFID: TEST123456789
2. Add items to cart: Nasi Gudeg (15000), Es Teh (5000)
3. Check total: 20000
4. Process checkout
5. Verify: Saldo terpotong, transaksi tersimpan
```

### Test Case 2: Insufficient Balance
```
1. Scan RFID santri dengan saldo rendah
2. Add items dengan total > saldo
3. Process checkout
4. Verify: Error "Saldo tidak mencukupi"
```

### Test Case 3: Daily Limit Exceeded
```
1. Scan RFID santri yang sudah mencapai limit harian
2. Add items
3. Process checkout
4. Verify: Error "Melebihi limit harian"
```

### Test Case 4: Network Failure
```
1. Disconnect network
2. Scan RFID
3. Verify: Proper error handling
4. Reconnect network
5. Verify: System recovery
```

### Test Case 5: Refund Process
```
1. Find successful transaction
2. Initiate refund with original transaction ref
3. Enter refund amount and reason
4. Process refund
5. Verify: Saldo bertambah, refund tercatat
```

---

## 📊 Monitoring & Logging

### Log Events
```javascript
// utils/logger.js
class TransactionLogger {
    static log(level, message, data = null) {
        const timestamp = new Date().toISOString();
        const logEntry = {
            timestamp,
            level,
            message,
            data,
            session_id: getSessionId()
        };
        
        console.log(`[${level.toUpperCase()}] ${message}`, data);
        
        // Store to local storage for debugging
        this.storeLog(logEntry);
    }
    
    static storeLog(entry) {
        let logs = JSON.parse(localStorage.getItem('transaction_logs') || '[]');
        logs.push(entry);
        
        // Keep only last 1000 logs
        if (logs.length > 1000) {
            logs = logs.slice(-1000);
        }
        
        localStorage.setItem('transaction_logs', JSON.stringify(logs));
    }
    
    static info(message, data) { this.log('info', message, data); }
    static warn(message, data) { this.log('warn', message, data); }
    static error(message, data) { this.log('error', message, data); }
}

// Usage examples:
TransactionLogger.info('Customer scanned', { rfid: 'ABC123', santri_id: 123 });
TransactionLogger.error('Payment failed', { error: error.message, amount: 25000 });
```

---

## 🔧 Troubleshooting Guide

### Problem: RFID tidak terbaca
**Solution:**
1. Check RFID reader connection
2. Verify RFID tag condition
3. Test with known working tag
4. Check driver installation

### Problem: API connection failed
**Solution:**
1. Verify SIMPels server is running
2. Check network connectivity
3. Validate API endpoint URLs
4. Check firewall settings

### Problem: Customer data tidak muncul
**Solution:**
1. Verify RFID tag registered in database
2. Check santri status (must be 'aktif')
3. Validate API response format
4. Check console for JavaScript errors

### Problem: Payment processing failed
**Solution:**
1. Check customer balance
2. Verify daily limits
3. Check transaction validation
4. Review API error responses

---

## 📞 Support Checklist

Before contacting support, please check:
- [ ] Server SIMPels status
- [ ] Network connectivity
- [ ] Browser console errors
- [ ] Transaction logs
- [ ] RFID reader status
- [ ] Database connectivity

**Emergency Contact:**
- Phone: +62-xxx-xxxx-xxxx
- Email: support@simpels.pesantren.id
- WhatsApp: +62-xxx-xxxx-xxxx

---

*Panduan ini akan diupdate berkala. Pastikan selalu menggunakan versi terbaru.*