# 🧪 Testing Guide untuk Integrasi SIMPels API

## 📋 Pre-Testing Checklist

### ✅ Persiapan Server
- [ ] SIMPels server berjalan di `http://localhost:8000`
- [ ] ePOS server berjalan di `http://localhost:8001`
- [ ] Database SIMPels berisi data santri test
- [ ] RFID test cards tersedia

### ✅ Browser Setup
- [ ] Buka ePOS di browser: `http://localhost:8001`
- [ ] Login sebagai admin/cashier
- [ ] Buka Developer Console (F12)
- [ ] Pastikan tidak ada error JavaScript

---

## 🚀 Test Scenarios

### Test 1: API Connection
```javascript
// Jalankan di browser console
testAPIConnection();
```
**Expected Result:** 
- Console menampilkan "API connection successful"
- Notification success muncul

### Test 1.5: Sync Data dari SIMPels
1. Buka Customer Management
2. Klik tab "Santri"
3. Klik tombol "Sync from SIMPels"
4. Tunggu proses sync selesai
5. Ulangi untuk tab "Guru"

**Expected Result:**
- Data santri dan guru ter-sync dari SIMPels API
- Notification sukses menampilkan jumlah data yang di-sync
- Daftar santri/guru muncul di tabel

### Test 2: RFID Scanning (PRODUCTION MODE)
1. Buka POS Terminal
2. Tekan **Ctrl+R** atau klik input RFID
3. Masukkan salah satu RFID test:
   - `TEST123456789` (Ahmad - Saldo: Rp 100.000, Limit: Rp 50.000)
   - `TEST123456788` (Budi - Saldo: Rp 5.000, Limit: Rp 25.000) 
   - `TEST123456787` (Citra - Saldo: Rp 75.000, Limit: Rp 30.000)
4. Tekan Enter

**Expected Result:**
- 🔴 Mode PRODUCTION: Data santri diambil dari SIMPels API secara real-time
- Data santri muncul di panel customer
- Payment method "RFID" aktif dengan indikator 🔴 RFID Payment AKTIF
- Saldo dan informasi santri ditampilkan dari database SIMPels

**Test Data Available:**
```
Ahmad Test Santri (TEST123456789):
- NIS: TEST001
- Kelas: XII IPA 1  
- Saldo: Rp 100.000
- Limit: Rp 50.000
- Status: Aktif

Budi Test Santri (TEST123456788):
- NIS: TEST002
- Kelas: XI IPS 2
- Saldo: Rp 5.000 (untuk test insufficient balance)
- Limit: Rp 25.000
- Status: Aktif

Citra Test Santri (TEST123456787):
- NIS: TEST003
- Kelas: X A
- Saldo: Rp 75.000
- Limit: Rp 30.000
- Status: Aktif
```

### Test 3: RFID Transaction (PRODUCTION MODE)
1. Scan RFID santri dengan saldo cukup (gunakan `TEST123456789` - Ahmad)
2. Tambahkan item ke cart (maksimal Rp 50.000 sesuai limit)
3. Pilih payment method "RFID"  
4. Klik "Checkout"

**Expected Result:**
- 🔴 PRODUCTION: API call real-time ke SIMPels untuk validasi saldo & limit
- Konfirmasi pembayaran muncul dengan data real-time
- Pembayaran berhasil diproses melalui SIMPels API
- Saldo santri terpotong di sistem SIMPels
- Transaction log tersimpan di kedua sistem (EPOS + SIMPels)

### Test 4: Insufficient Balance
1. Scan RFID santri dengan saldo rendah (gunakan `TEST123456788` - Budi, saldo Rp 5.000)
2. Tambahkan item dengan total > Rp 5.000
3. Coba checkout

**Expected Result:**
- Error "Saldo tidak mencukupi"
- Transaksi dibatalkan

### Test 5: Daily Limit Exceeded
1. Scan RFID santri (gunakan `TEST123456787` - Citra, limit Rp 30.000)
2. Tambahkan item dengan total > Rp 30.000
3. Coba checkout

**Expected Result:**
- Error "Melebihi limit harian" atau "Transaksi melebihi limit yang diizinkan"
- Transaksi dibatalkan

### Test 6: Network Failure
1. Matikan SIMPels server
2. Scan RFID
3. Coba transaksi

**Expected Result:**
- Error handling yang proper
- Pesan error yang informatif
- System tetap stabil

### Test 7: Refund Process
1. Lakukan transaksi normal terlebih dahulu
2. Klik tombol "Refund" di header
3. Masukkan transaction reference
4. Masukkan nominal dan alasan refund
5. Process refund

**Expected Result:**
- Refund berhasil diproses
- Saldo santri bertambah
- Refund log tersimpan

### Test 8: Offline Mode
1. Lakukan transaksi normal
2. Matikan koneksi saat sync
3. Nyalakan kembali koneksi

**Expected Result:**
- Transaksi tersimpan di offline queue
- Auto-sync ketika koneksi kembali
- Tidak ada data yang hilang

---

## 📊 Monitoring & Debugging

### Log Viewer
1. Klik tombol "Logs" di header (admin only)
2. Filter berdasarkan level/category
3. Export logs jika diperlukan

### Console Commands
```javascript
// Cek status offline queue
transactionProcessor.getOfflineQueueStatus()

// Cek customer yang sedang aktif
customerScanner.currentCustomer

// Cek log statistics
transactionLogger.getLogStats()

// Test API individual
simpelsAPI.testConnection()

// Clear offline queue
transactionProcessor.clearOfflineQueue()
```

### Browser DevTools
- **Network Tab**: Monitor API calls
- **Console Tab**: Error messages dan logs
- **Application Tab**: LocalStorage data
- **Performance Tab**: Monitor performance issues

---

## 🔧 Troubleshooting

### Problem: RFID tidak bisa scan
**Debug Steps:**
1. Cek console untuk error JavaScript
2. Pastikan input RFID dalam focus
3. Test dengan manual input (Ctrl+R)
4. Cek format RFID (minimal 8 karakter)

**Fix:**
```javascript
// Refresh scanner
customerScanner = new CustomerScanner();
```

### Problem: API tidak respond
**Debug Steps:**
1. Cek server SIMPels status
2. Test koneksi: `testAPIConnection()`
3. Cek network tab di DevTools
4. Verify API endpoint URLs

**Fix:**
- Restart SIMPels server
- Check firewall settings
- Verify API configuration

### Problem: Transaction gagal sync
**Debug Steps:**
1. Cek offline queue: `transactionProcessor.getOfflineQueueStatus()`
2. Monitor API calls di network tab
3. Cek error logs

**Fix:**
```javascript
// Force sync offline transactions
transactionProcessor.syncOfflineTransactions()
```

### Problem: Customer data tidak muncul
**Debug Steps:**
1. Verify RFID tag di database SIMPels
2. Cek status santri (harus 'aktif')
3. Test dengan RFID yang sudah diketahui valid

**Fix:**
- Update database SIMPels
- Refresh customer data: `customerScanner.refreshCustomerData()`

---

## 📈 Performance Testing

### Load Testing
```javascript
// Simulate multiple rapid scans
for(let i = 0; i < 10; i++) {
    setTimeout(() => {
        customerScanner.scanRFID('TEST' + i.toString().padStart(9, '0'));
    }, i * 1000);
}
```

### Memory Testing
```javascript
// Check memory usage
console.log('Transaction History:', JSON.parse(localStorage.getItem('epos_transaction_history') || '[]').length);
console.log('Logs:', JSON.parse(localStorage.getItem('epos_logs') || '[]').length);
console.log('Offline Queue:', JSON.parse(localStorage.getItem('epos_offline_queue') || '[]').length);
```

### Storage Testing
```javascript
// Check localStorage usage
let total = 0;
for(let key in localStorage) {
    if(localStorage.hasOwnProperty(key)) {
        total += localStorage[key].length;
    }
}
console.log('LocalStorage usage:', (total / 1024 / 1024).toFixed(2) + ' MB');
```

---

## 📋 Test Results Template

### Test Session: [Date]
**Tester:** [Name]
**Environment:** [Local/Staging/Production]
**Browser:** [Chrome/Firefox/Safari + Version]

| Test Case | Status | Notes | Time |
|-----------|--------|-------|------|
| API Connection | ✅/❌ | | |
| RFID Scanning | ✅/❌ | | |
| Normal Transaction | ✅/❌ | | |
| Insufficient Balance | ✅/❌ | | |
| Daily Limit | ✅/❌ | | |
| Network Failure | ✅/❌ | | |
| Refund Process | ✅/❌ | | |
| Offline Mode | ✅/❌ | | |

**Issues Found:**
1. [Description]
2. [Description]

**Performance Notes:**
- API Response Time: [ms]
- RFID Scan Time: [ms]
- Transaction Process Time: [ms]

---

## 🚀 Deployment Checklist

### Pre-Production
- [ ] All tests passing
- [ ] Performance acceptable
- [ ] Error handling tested
- [ ] Security review completed
- [ ] Database backup completed

### Production Setup
- [ ] Update API endpoints to production URLs
- [ ] Disable debug mode
- [ ] Configure proper logging
- [ ] Setup monitoring alerts
- [ ] Train staff on new features

### Post-Deployment
- [ ] Monitor API performance
- [ ] Check error rates
- [ ] Verify transaction accuracy
- [ ] Collect user feedback
- [ ] Document any issues

---

**Contact untuk Support:**
- Developer: [Contact Info]
- System Admin: [Contact Info]
- Emergency: [Phone Number]

---

## 🔴 **PRODUCTION MODE STATUS**

**RFID Payment System telah AKTIF dan keluar dari testing mode:**

### ✅ **Production Features:**
- 🔴 **Real-time SIMPels API Integration**
- 🔴 **Live RFID Payment Processing** 
- 🔴 **Real-time Balance Deduction**
- 🔴 **Live Transaction Sync**
- 🔴 **Production Error Handling & Logging**

### 🎯 **Mode PRODUCTION Indikator:**
- Console log: "🔴 SIMPels API Integration loaded - PRODUCTION MODE"
- POS UI: "🔴 RFID Payment AKTIF"
- Debug mode: `false` (production optimized)

**Sistem siap untuk operasional real!** 🚀