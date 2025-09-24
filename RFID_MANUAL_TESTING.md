# Testing Instructions untuk RFID Payment

## Perbaikan yang Telah Dilakukan ✅

### 1. JavaScript Class Definition Errors
- ✅ Fixed: `CustomerScanner is not defined` error
- ✅ Fixed: `TransactionProcessor is not defined` error  
- ✅ Fixed: Conditional class definition dengan proper closing brackets

### 2. RFID Integration Issues
- ✅ Enhanced: Auto-retry untuk CustomerScanner integration
- ✅ Enhanced: Proper error handling dan console logging
- ✅ Fixed: Checkout button selector untuk Livewire integration
- ✅ Added: Delay initialization untuk memastikan semua modules loaded

### 3. Testing Functions Added
- ✅ `window.testRfidScan(rfidTag)` - Manual testing function
- ✅ Enhanced console logging untuk debugging
- ✅ Auto-focus pada RFID input field

## Manual Testing Procedures

### A. Test Console Errors (RESOLVED)
1. Open browser console (F12)
2. Reload POS page: `http://127.0.0.1:8001/pos`
3. **Expected:** No more "CustomerScanner is not defined" errors
4. **Expected:** No more "TransactionProcessor is not defined" errors

### B. Test RFID Input Processing

#### Method 1: Browser Console Testing
```javascript
// Test RFID scan function
window.testRfidScan('RFID12345');

// Check if CustomerScanner is loaded
console.log('CustomerScanner:', window.customerScanner);

// Check if TransactionProcessor is loaded  
console.log('TransactionProcessor:', window.transactionProcessor);
```

#### Method 2: Manual RFID Input
1. Add products to cart
2. Select "RFID" payment method
3. Click "Process Payment" 
4. RFID modal should open
5. Type in RFID input field: `RFID12345`
6. Press Enter
7. **Expected:** API call to SIMPels to get santri data

#### Method 3: Keyboard Shortcut
1. Press `Ctrl+R` anywhere on POS page
2. Enter RFID in popup dialog
3. **Expected:** Same processing as Method 2

### C. Test API Integration Flow

#### Complete Payment Flow Test:
```javascript
// 1. Initialize test data
const testRfid = 'RFID12345';

// 2. Test API connection
simpelsAPI.testConnection().then(result => {
    console.log('API Connection:', result);
});

// 3. Test santri lookup
simpelsAPI.getSantriByRFID(testRfid).then(result => {
    console.log('Santri Data:', result);
});

// 4. Test full RFID scan
window.testRfidScan(testRfid);
```

### D. Expected Console Output (Success)
```
POS Terminal RFID Integration initializing...
Initializing RFID integration...
CustomerScanner found, setting up integration...
Process Payment button found, setting up RFID integration...
RFID integration initialized successfully
API Integration initialized with terminal: EPOS_SAZA_xxxx
SUCCESS: SIMPels API connection successful
```

### E. RFID Payment Test Scenario

#### Prerequisites:
- Cart has products
- RFID payment method selected
- Server running on port 8001

#### Steps:
1. **Add Products:** Click product cards to add to cart
2. **Select Payment:** Choose "RFID" method
3. **Open Payment:** Click "Process Payment" button
4. **RFID Modal:** Should open with input field focused
5. **Input RFID:** Type/scan: `RFID12345` or use `window.testRfidScan('RFID12345')`
6. **Customer Found:** Should show customer details in modal
7. **Confirm Payment:** Click "Confirm Payment" button
8. **Process:** Should call API and complete payment

#### Expected Results:
- ✅ Customer data appears in modal
- ✅ Balance validation works
- ✅ Payment processes successfully
- ✅ Cart clears after payment
- ✅ Transaction recorded in database
- ✅ Success message displayed

## Debugging Commands

### Check Module Status:
```javascript
console.log('Modules loaded:', {
    customerScanner: !!window.customerScanner,
    transactionProcessor: !!window.transactionProcessor,
    simpelsAPI: !!window.simpelsAPI,
    livewire: !!window.Livewire
});
```

### Test API Endpoints:
```javascript
// Test API connection
fetch('http://localhost:8000/api/epos/test-connection')
    .then(r => r.json())
    .then(console.log);

// Test santri lookup
fetch('http://localhost:8000/api/epos/santri/RFID12345')
    .then(r => r.json()) 
    .then(console.log);
```

### Manual Customer Data Setting:
```javascript
// Manually set customer data for testing
const testCustomer = {
    id: 1,
    nama_santri: 'Test Santri',
    kelas: 'X-A',
    saldo: 50000,
    rfid_tag: 'RFID12345'
};

// Set in Livewire
Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
    .set('selectedSantri', testCustomer);
```

## Production Checklist

### Before Going Live:
- [ ] All console errors resolved
- [ ] RFID hardware tested with real cards
- [ ] API endpoints returning correct data
- [ ] Payment flow completes successfully
- [ ] Error handling works properly
- [ ] Offline queue functioning
- [ ] Transaction logging active
- [ ] Balance validation accurate

### Hardware Requirements:
- [ ] RFID reader connected and configured
- [ ] Network connection to SIMPels server
- [ ] Proper API credentials configured
- [ ] Database connections stable

## Support & Troubleshooting

### Common Issues:
1. **"CustomerScanner not found"** - Wait 2-3 seconds, should auto-retry
2. **"API connection failed"** - Check network and API endpoints  
3. **"Customer data not setting"** - Check browser console for Livewire errors
4. **"Payment not processing"** - Verify cart has items and customer is selected

### Contact Information:
- Check server logs: `storage/logs/laravel.log`
- Browser console for JavaScript errors
- Network tab for API call failures

Last Updated: September 24, 2025 - 12:45 PM
Status: READY FOR TESTING 🚀