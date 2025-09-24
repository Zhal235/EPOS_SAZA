# RFID Payment Testing Guide

## Overview
Sistem pembayaran RFID telah diintegrasikan dengan API SIMPels dan siap untuk testing dengan RFID reader fisik.

## Perubahan yang Telah Dilakukan

### 1. Removed Simulation Mode ✅
- ❌ Dihapus: `simulateRfidScan()` method
- ❌ Dihapus: Tombol "Simulate RFID Scan"
- ✅ Diganti: Input field untuk RFID reader langsung

### 2. API Integration ✅
- ✅ CustomerScanner module terintegrasi dengan SIMPels API
- ✅ TransactionProcessor menggunakan real-time balance deduction
- ✅ Auto-sync transaction details ke SIMPels

### 3. RFID Input Handling ✅
- ✅ Auto-focus pada RFID input field
- ✅ Support keyboard input (Ctrl+R untuk manual input)
- ✅ Real-time scanning detection

## Testing Procedures

### Prerequisites
1. Server Laravel running: `http://127.0.0.1:8001`
2. SIMPels API accessible
3. RFID reader connected
4. Sample santri with RFID registered in SIMPels

### Test Scenarios

#### 1. Basic RFID Payment Flow
```
1. Login ke POS Terminal
2. Tambahkan produk ke cart
3. Pilih payment method: "RFID"
4. Klik "Checkout" → Modal RFID terbuka
5. Scan RFID card atau input manual
6. Sistem otomatis detect santri
7. Klik "Confirm Payment"
8. Pembayaran diproses via API
```

#### 2. Error Handling Tests
```
A. Invalid RFID
   - Scan RFID yang tidak terdaftar
   - Expected: Error message "RFID tidak terdaftar"

B. Insufficient Balance
   - Scan santri dengan saldo < total belanja
   - Expected: Error message dengan detail saldo

C. Network Error
   - Disconnect internet, scan RFID
   - Expected: Offline queue activated

D. Empty Cart
   - Scan RFID tanpa produk di cart
   - Expected: Error "Keranjang belanja kosong"
```

#### 3. Integration Tests
```
A. API Communication
   - Check browser console for API calls
   - Verify balance deduction in SIMPels
   - Confirm transaction sync

B. Real-time Updates
   - Balance update after payment
   - Stock reduction in local DB
   - Transaction history logging
```

## RFID Input Methods

### 1. Hardware RFID Reader
- Connect RFID reader to computer
- Reader should output to active input field
- Modal auto-focuses on RFID input field

### 2. Manual Input (Testing)
- Press `Ctrl+R` for manual input dialog
- Or type directly in RFID input field
- Press Enter to process

### 3. Barcode Scanner Fallback
- Some RFID readers work as barcode scanners
- Should work with existing input handling

## Expected API Flow

```
1. RFID Scan → CustomerScanner.scanRFID()
2. API Call → simpelsAPI.getSantriByRFID()
3. Customer Found → displayCustomerInfo()
4. Payment Confirm → TransactionProcessor.processPayment()
5. Balance Deduct → simpelsAPI.deductBalance()
6. Local Transaction → PosTerminal.processRfidPayment()
7. Sync Details → simpelsAPI.syncTransaction()
```

## Troubleshooting

### Common Issues
1. **RFID input tidak focus**
   - Check JavaScript console errors
   - Verify MutationObserver working

2. **API calls failed**
   - Check network connectivity
   - Verify SIMPels API endpoints
   - Check API credentials

3. **Customer data tidak tampil**
   - Verify RFID format
   - Check API response structure
   - Validate customer data mapping

### Debug Tools
1. Browser Console (F12)
2. Network tab untuk API calls
3. Local storage untuk offline queue
4. Server logs untuk errors

## Production Deployment Notes

### Security Considerations
- API credentials properly configured
- HTTPS for production
- Input sanitization
- Balance validation

### Performance
- API response caching
- Offline queue processing
- Connection timeout handling
- Auto-retry mechanisms

### Monitoring
- Transaction success/failure rates
- API response times
- Error logging
- Balance reconciliation

## Support Contact
For issues with RFID integration or API connectivity, check:
1. Server logs: `storage/logs/laravel.log`
2. Browser console errors
3. Network connectivity
4. SIMPels API status

Last Updated: September 24, 2025
Version: 2.0 (Real RFID Integration)