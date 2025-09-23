# 📱 Panduan Integrasi API SIMPels dengan Aplikasi ePOS

## 📋 Daftar Isi
- [Persiapan](#persiapan)
- [Konfigurasi API](#konfigurasi-api)
- [Implementasi Customer Santri](#implementasi-customer-santri)
- [Testing & Debugging](#testing--debugging)
- [Error Handling](#error-handling)
- [Best Practices](#best-practices)

---

## 🛠️ Persiapan

### Sistem Requirements
- ✅ **SIMPels API Server** berjalan di port 8000 (Laravel)
- ✅ **ePOS Application** dengan menu "Customers" untuk santri
- ✅ **RFID Reader** terhubung ke sistem ePOS
- ✅ **Network Connection** antara ePOS dan SIMPels server

### Base URL API
```
Base URL: http://localhost:8000/api/epos/
```

### Authentication
Saat ini menggunakan `api` middleware tanpa token. Untuk production, implementasikan API key.

---

## 🔧 Konfigurasi API

### 1. Endpoint Yang Tersedia

| Endpoint | Method | Fungsi | Parameter Wajib |
|----------|---------|---------|-----------------|
| `santri/rfid/{tag}` | GET | Data santri via RFID | `tag` |
| `santri/{id}/saldo` | GET | Cek saldo dompet | `santriId` |
| `santri/{id}/deduct` | POST | Potong saldo transaksi | `nominal`, `keterangan`, `transaction_ref` |
| `santri/{id}/refund` | POST | Refund transaksi | `nominal`, `original_transaction_ref`, `refund_reason` |
| `limit/check-rfid` | POST | Validasi limit via RFID | `rfid_tag`, `amount` |
| `transaction/sync` | POST | Sync transaksi ke SIMPels | `epos_transaction_id`, `santri_id`, `total_amount`, `items[]` |

### 2. Header Requirements
```http
Content-Type: application/json
Accept: application/json
```

---

## 👥 Implementasi Customer Santri

### 1. Scan RFID untuk Identifikasi Santri

#### Request:
```http
GET /api/epos/santri/rfid/{tag}
```

#### Contoh Implementation (JavaScript):
```javascript
async function scanRfidCustomer(rfidTag) {
    try {
        const response = await fetch(`http://localhost:8000/api/epos/santri/rfid/${rfidTag}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Tampilkan data santri di interface ePOS
            displayCustomerInfo(data.data);
            return data.data;
        } else {
            showError(data.message);
            return null;
        }
    } catch (error) {
        showError('Koneksi ke server bermasalah: ' + error.message);
        return null;
    }
}

function displayCustomerInfo(santri) {
    // Update UI dengan data santri
    document.getElementById('customer-name').textContent = santri.nama_santri;
    document.getElementById('customer-nis').textContent = santri.nis;
    document.getElementById('customer-class').textContent = santri.kelas;
    document.getElementById('customer-balance').textContent = formatRupiah(santri.saldo);
    
    // Enable checkout button jika saldo mencukupi
    document.getElementById('checkout-btn').disabled = false;
}
```

#### Response Success:
```json
{
    "success": true,
    "message": "Data santri berhasil ditemukan",
    "data": {
        "id": 123,
        "nis": "20240001",
        "nama_santri": "Ahmad Rizki Maulana",
        "kelas": "XII IPA 1",
        "asrama": "Asrama Putra A",
        "rfid_tag": "ABC123456789",
        "saldo": 75000,
        "status": "aktif",
        "foto": "storage/santri/photos/123.jpg"
    }
}
```

### 2. Validasi Limit Sebelum Transaksi

#### Request:
```http
POST /api/epos/limit/check-rfid
Content-Type: application/json

{
    "rfid_tag": "ABC123456789",
    "amount": 25000
}
```

#### Implementation:
```javascript
async function checkTransactionLimit(rfidTag, totalAmount) {
    try {
        const response = await fetch('http://localhost:8000/api/epos/limit/check-rfid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                rfid_tag: rfidTag,
                amount: totalAmount
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            return true; // Transaksi diizinkan
        } else {
            showWarning(data.message);
            return false; // Transaksi ditolak
        }
    } catch (error) {
        showError('Error validasi limit: ' + error.message);
        return false;
    }
}
```

### 3. Proses Pembayaran (Deduct Saldo)

#### Request:
```http
POST /api/epos/santri/{santriId}/deduct
Content-Type: application/json

{
    "nominal": 25000,
    "keterangan": "Pembelian makanan dan minuman",
    "transaction_ref": "EPOS202409230001"
}
```

#### Implementation:
```javascript
async function processPayment(santriId, amount, items, transactionRef) {
    try {
        // 1. Validasi limit terlebih dahulu
        const limitOk = await checkTransactionLimit(currentCustomer.rfid_tag, amount);
        if (!limitOk) {
            return false;
        }
        
        // 2. Proses pembayaran
        const keterangan = items.map(item => 
            `${item.name} (${item.qty}x)`
        ).join(', ');
        
        const response = await fetch(`http://localhost:8000/api/epos/santri/${santriId}/deduct`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nominal: amount,
                keterangan: keterangan,
                transaction_ref: transactionRef
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Update saldo di interface
            updateCustomerBalance(data.data.saldo_sesudah);
            
            // Sync transaksi ke SIMPels
            await syncTransactionToSIMPels(santriId, amount, items, transactionRef);
            
            showSuccess('Pembayaran berhasil!');
            return true;
        } else {
            showError(data.message);
            return false;
        }
    } catch (error) {
        showError('Error proses pembayaran: ' + error.message);
        return false;
    }
}
```

### 4. Sync Detail Transaksi ke SIMPels

#### Request:
```http
POST /api/epos/transaction/sync
Content-Type: application/json

{
    "epos_transaction_id": "EPOS202409230001",
    "santri_id": 123,
    "total_amount": 25000,
    "items": [
        {
            "product_id": "P001",
            "product_name": "Nasi Gudeg",
            "quantity": 1,
            "price": 15000,
            "subtotal": 15000
        },
        {
            "product_id": "P002", 
            "product_name": "Es Teh Manis",
            "quantity": 2,
            "price": 5000,
            "subtotal": 10000
        }
    ],
    "payment_method": "rfid",
    "transaction_date": "2024-09-23 14:30:00",
    "cashier_name": "Pak Ahmad"
}
```

#### Implementation:
```javascript
async function syncTransactionToSIMPels(santriId, totalAmount, items, transactionRef) {
    try {
        const response = await fetch('http://localhost:8000/api/epos/transaction/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                epos_transaction_id: transactionRef,
                santri_id: santriId,
                total_amount: totalAmount,
                items: items.map(item => ({
                    product_id: item.id,
                    product_name: item.name,
                    quantity: item.qty,
                    price: item.price,
                    subtotal: item.qty * item.price
                })),
                payment_method: 'rfid',
                transaction_date: new Date().toISOString(),
                cashier_name: getCurrentCashierName()
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            console.warn('Sync transaksi gagal:', data.message);
            // Bisa ditambahkan queue untuk retry sync nanti
        }
    } catch (error) {
        console.error('Error sync transaksi:', error);
        // Simpan untuk retry sync offline
    }
}
```

### 5. Proses Refund

#### Request:
```http
POST /api/epos/santri/{santriId}/refund
Content-Type: application/json

{
    "nominal": 15000,
    "original_transaction_ref": "EPOS202409230001",
    "refund_reason": "Barang rusak"
}
```

#### Implementation:
```javascript
async function processRefund(santriId, amount, originalTransactionRef, reason) {
    try {
        const response = await fetch(`http://localhost:8000/api/epos/santri/${santriId}/refund`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                nominal: amount,
                original_transaction_ref: originalTransactionRef,
                refund_reason: reason
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCustomerBalance(data.data.saldo_sesudah);
            showSuccess('Refund berhasil diproses!');
            return true;
        } else {
            showError(data.message);
            return false;
        }
    } catch (error) {
        showError('Error proses refund: ' + error.message);
        return false;
    }
}
```

---

## 🧪 Testing & Debugging

### 1. Test Koneksi API
```javascript
async function testApiConnection() {
    try {
        const response = await fetch('http://localhost:8000/api/epos/limit/summary');
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Koneksi API berhasil');
            return true;
        } else {
            console.error('❌ API Error:', data.message);
            return false;
        }
    } catch (error) {
        console.error('❌ Koneksi gagal:', error.message);
        return false;
    }
}
```

### 2. Debug Mode
```javascript
const DEBUG_MODE = true; // Set false untuk production

function debugLog(message, data = null) {
    if (DEBUG_MODE) {
        console.log(`[ePOS-API Debug] ${message}`, data);
    }
}

// Penggunaan:
debugLog('Sending payment request', { santriId, amount, transactionRef });
```

### 3. Test Data Santri
Untuk testing, gunakan RFID tag dummy: `TEST123456789`

---

## ⚠️ Error Handling

### 1. Response Error Types
```javascript
function handleApiError(error, context) {
    switch (error.status) {
        case 404:
            showError('Santri tidak ditemukan atau RFID tidak terdaftar');
            break;
        case 422:
            showError('Data tidak valid: ' + error.data.message);
            break;
        case 500:
            showError('Server error. Silakan coba lagi atau hubungi admin.');
            break;
        default:
            showError('Terjadi kesalahan: ' + error.message);
    }
    
    debugLog(`Error in ${context}`, error);
}
```

### 2. Offline Mode Handling
```javascript
let offlineQueue = [];

function queueOfflineTransaction(transactionData) {
    offlineQueue.push({
        ...transactionData,
        timestamp: new Date().toISOString(),
        status: 'pending'
    });
    
    // Simpan ke localStorage
    localStorage.setItem('epos_offline_queue', JSON.stringify(offlineQueue));
}

async function syncOfflineTransactions() {
    if (offlineQueue.length === 0) return;
    
    for (let transaction of offlineQueue) {
        try {
            await syncTransactionToSIMPels(
                transaction.santriId,
                transaction.amount,
                transaction.items,
                transaction.transactionRef
            );
            
            // Remove from queue if successful
            offlineQueue = offlineQueue.filter(t => t.transactionRef !== transaction.transactionRef);
        } catch (error) {
            console.warn('Failed to sync offline transaction:', error);
        }
    }
    
    localStorage.setItem('epos_offline_queue', JSON.stringify(offlineQueue));
}
```

---

## 💡 Best Practices

### 1. Security
```javascript
// Validasi input sebelum kirim ke API
function validateTransactionData(amount, santriId) {
    if (amount <= 0) {
        throw new Error('Nominal harus lebih besar dari 0');
    }
    
    if (amount > 1000000) { // 1 juta
        throw new Error('Nominal terlalu besar');
    }
    
    if (!santriId || santriId <= 0) {
        throw new Error('ID santri tidak valid');
    }
    
    return true;
}
```

### 2. Performance
```javascript
// Cache data santri untuk mengurangi API calls
let customerCache = new Map();

async function getCachedCustomerData(rfidTag) {
    if (customerCache.has(rfidTag)) {
        const cached = customerCache.get(rfidTag);
        
        // Cache valid selama 5 menit
        if (Date.now() - cached.timestamp < 300000) {
            return cached.data;
        }
    }
    
    // Fetch fresh data
    const freshData = await scanRfidCustomer(rfidTag);
    if (freshData) {
        customerCache.set(rfidTag, {
            data: freshData,
            timestamp: Date.now()
        });
    }
    
    return freshData;
}
```

### 3. User Experience
```javascript
// Loading indicators
function showLoading(message = 'Memproses...') {
    document.getElementById('loading-overlay').style.display = 'block';
    document.getElementById('loading-message').textContent = message;
}

function hideLoading() {
    document.getElementById('loading-overlay').style.display = 'none';
}

// Contoh penggunaan
async function handleCheckout() {
    showLoading('Memproses pembayaran...');
    
    try {
        const success = await processPayment(currentCustomer.id, totalAmount, cartItems, generateTransactionRef());
        
        if (success) {
            showSuccess('Pembayaran berhasil!');
            clearCart();
            resetCustomer();
        }
    } finally {
        hideLoading();
    }
}
```

---

## 🔄 Workflow Lengkap

### 1. Alur Transaksi Normal
```
1. Scan RFID → GET /api/epos/santri/rfid/{tag}
2. Tampilkan data santri di interface
3. Tambah item ke keranjang
4. Validasi limit → POST /api/epos/limit/check-rfid
5. Konfirmasi pembayaran
6. Proses pembayaran → POST /api/epos/santri/{id}/deduct
7. Sync detail transaksi → POST /api/epos/transaction/sync
8. Cetak struk/selesai
```

### 2. Alur Refund
```
1. Input nomor transaksi asli
2. Pilih item yang di-refund
3. Input alasan refund
4. Proses refund → POST /api/epos/santri/{id}/refund
5. Update saldo santri
6. Cetak bukti refund
```

---

## 📞 Support & Troubleshooting

### Kontak Technical Support
- **Developer:** Tim SIMPels
- **Email:** admin@simpels.pesantren.id
- **Phone:** +62-xxx-xxxx-xxxx

### FAQ
**Q: Bagaimana jika koneksi internet terputus?**
A: Gunakan offline mode untuk menyimpan transaksi dan sync otomatis saat koneksi kembali.

**Q: RFID tidak terbaca?**
A: Periksa koneksi RFID reader dan pastikan tag dalam kondisi baik.

**Q: Saldo santri tidak update?**
A: Cek log API call dan pastikan sync transaksi berhasil.

---

*Dokumentasi ini akan diupdate seiring perkembangan fitur. Last updated: September 2025*