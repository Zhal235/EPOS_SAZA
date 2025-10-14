# Diagnosis Masalah Koneksi API EPOS ke SIMPels

## Tanggal: 14 Oktober 2025

## Status Saat Ini
- ✅ API SIMPels **BERJALAN** di `http://localhost:8001/api/epos`
- ✅ EPOS dikonfigurasi di port `8002`
- ✅ Koneksi API test berhasil (HTTP 200 OK)

## Konfigurasi yang Ditemukan

### EPOS_SAZA (.env)
```env
APP_URL=http://localhost:8002
SIMPELS_API_URL=http://localhost:8001/api/epos
SIMPELS_API_TIMEOUT=30
SIMPELS_API_KEY=your_api_key_here
```

### SIMPels (.env)
```env
APP_URL=http://localhost:8001
```

## Kemungkinan Penyebab Masalah

### 1. ❌ Server Tidak Berjalan di Localhost
**Gejala**: API terhubung saat online tapi tidak terhubung di localhost
**Penyebab**:
- Server SIMPels tidak berjalan di localhost:8001
- Server EPOS tidak berjalan di localhost:8002
- Port yang berbeda antara konfigurasi dan server aktual

**Solusi**:
```powershell
# Terminal 1 - Jalankan SIMPels
cd "c:\Users\Rhezal Maulana\Documents\GitHub\SIMPels"
php artisan serve --host=localhost --port=8001

# Terminal 2 - Jalankan EPOS
cd "c:\Users\Rhezal Maulana\Documents\GitHub\EPOS_SAZA"
php artisan serve --host=localhost --port=8002
```

### 2. ❌ Perbedaan URL Online vs Localhost
**Gejala**: Konfigurasi masih menggunakan URL online
**Penyebab**:
- `.env` EPOS mungkin masih punya `SIMPELS_API_URL` dengan domain online
- Hardcoded URL di frontend JavaScript

**Solusi**:
Pastikan `.env` menggunakan localhost:
```env
SIMPELS_API_URL=http://localhost:8001/api/epos
```

### 3. ❌ CORS (Cross-Origin Resource Sharing)
**Gejala**: Request blocked di browser console
**Penyebab**:
- CORS tidak dikonfigurasi untuk localhost
- Browser memblok request cross-origin

**Solusi**:
Sudah ada di `routes/api.php` SIMPels:
```php
Route::options('/{any}', function() {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN');
})->where('any', '.*');
```

### 4. ❌ Cache Konfigurasi Laravel
**Gejala**: Perubahan `.env` tidak terdeteksi
**Penyebab**:
- Laravel cache config belum di-clear

**Solusi**:
```powershell
cd "c:\Users\Rhezal Maulana\Documents\GitHub\EPOS_SAZA"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 5. ❌ Firewall atau Antivirus
**Gejala**: Koneksi ditolak di localhost
**Penyebab**:
- Windows Firewall memblokir koneksi localhost
- Antivirus memblokir port 8001 atau 8002

**Solusi**:
- Allow PHP atau Laravel dalam Windows Firewall
- Tambahkan exception di Antivirus

### 6. ❌ API Key Tidak Valid
**Gejala**: Error 401 Unauthorized
**Penyebab**:
- API Key tidak sesuai atau belum dikonfigurasi

**Current Config**:
```env
SIMPELS_API_KEY=your_api_key_here
```

**Solusi**:
Jika endpoint memerlukan API key yang valid, update dengan key yang benar.

## Cara Testing Koneksi

### 1. Test dari Terminal (Backend)
```powershell
# Test koneksi API
curl http://localhost:8001/api/epos/test-connection

# Test health check
curl http://localhost:8001/api/epos/health-check

# Test dari EPOS service
cd "c:\Users\Rhezal Maulana\Documents\GitHub\EPOS_SAZA"
php artisan tinker
>>> $service = app(\App\Services\SimpelsApiService::class);
>>> $result = $service->testConnection();
>>> print_r($result);
```

### 2. Test dari Browser (Frontend)
1. Buka: `http://localhost:8002`
2. Login ke EPOS
3. Buka POS Terminal
4. Klik tombol **"🌐 Test API"**
5. Lihat response di browser console (F12)

### 3. Test Endpoint Spesifik
```powershell
# Test get all santri
curl http://localhost:8001/api/epos/santri/all

# Test get santri by RFID
curl http://localhost:8001/api/epos/santri/rfid/ABC123

# Test limit summary
curl http://localhost:8001/api/epos/limit/summary
```

## Langkah Troubleshooting

### Step 1: Pastikan Kedua Server Berjalan
```powershell
# Cek proses PHP yang berjalan
Get-Process | Where-Object {$_.ProcessName -eq "php"}

# Atau test port
Test-NetConnection -ComputerName localhost -Port 8001
Test-NetConnection -ComputerName localhost -Port 8002
```

### Step 2: Clear All Cache
```powershell
cd "c:\Users\Rhezal Maulana\Documents\GitHub\EPOS_SAZA"
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3: Check Logs
```powershell
# EPOS Logs
Get-Content "c:\Users\Rhezal Maulana\Documents\GitHub\EPOS_SAZA\storage\logs\laravel.log" -Tail 50

# SIMPels Logs
Get-Content "c:\Users\Rhezal Maulana\Documents\GitHub\SIMPels\storage\logs\laravel.log" -Tail 50
```

### Step 4: Test Network Connection
```powershell
# Ping localhost
ping localhost

# Test specific ports
curl http://localhost:8001/api/epos/system-info
curl http://localhost:8002/api/health
```

## Checklist Pengecekan

- [ ] Apakah server SIMPels berjalan di port 8001?
- [ ] Apakah server EPOS berjalan di port 8002?
- [ ] Apakah `.env` EPOS punya `SIMPELS_API_URL=http://localhost:8001/api/epos`?
- [ ] Apakah cache Laravel sudah di-clear?
- [ ] Apakah browser console menunjukkan error CORS?
- [ ] Apakah endpoint `/api/epos/test-connection` bisa diakses dari curl?
- [ ] Apakah ada error di `storage/logs/laravel.log`?
- [ ] Apakah Windows Firewall tidak memblokir koneksi?

## Endpoint yang Tersedia di SIMPels

### System Endpoints
- `GET /api/epos/test-connection` - Test koneksi
- `GET /api/epos/health-check` - Health status
- `GET /api/epos/system-info` - System info

### Santri Endpoints
- `GET /api/epos/santri/all` - Get all santri
- `GET /api/epos/santri/rfid/{tag}` - Get santri by RFID
- `GET /api/epos/santri/{id}/saldo` - Get saldo santri
- `POST /api/epos/santri/{id}/deduct` - Deduct saldo
- `POST /api/epos/santri/{id}/refund` - Refund saldo

### Guru Endpoints
- `GET /api/epos/guru/all` - Get all guru
- `GET /api/epos/guru/rfid/{tag}` - Get guru by RFID

### Limit Endpoints
- `GET /api/epos/limit/summary` - Get limit summary
- `GET /api/epos/limit/santri/{id}` - Get limit by santri ID
- `GET /api/epos/limit/rfid/{tag}` - Get limit by RFID

### Transaction Endpoints
- `POST /api/epos/transaction/sync` - Sync transaction
- `GET /api/epos/transaction/{id}/history` - Get history

## Log untuk Debugging

File service `SimpelsApiService.php` sudah memiliki logging:
```php
Log::info("SIMPels API Request: {$method} {$url}", ['data' => $data]);
Log::info("SIMPels API Response: {$response->status()}", [...]);
Log::error("SIMPels API Error: " . $e->getMessage(), [...]);
```

Cek di: `storage/logs/laravel.log`

## Kesimpulan

Masalah kemungkinan besar disebabkan oleh:
1. **Server tidak berjalan di localhost** (paling umum)
2. Cache Laravel yang belum di-clear
3. Konfigurasi `.env` yang tidak sesuai

## Next Steps

1. **Pastikan kedua server berjalan**:
   ```powershell
   # Terminal 1
   cd SIMPels
   php artisan serve --port=8001
   
   # Terminal 2  
   cd EPOS_SAZA
   php artisan serve --port=8002
   ```

2. **Clear cache**:
   ```powershell
   php artisan config:clear
   ```

3. **Test koneksi**:
   ```powershell
   curl http://localhost:8001/api/epos/test-connection
   ```

4. **Check logs** jika masih error

---

**Created**: 14 Oktober 2025
**Status**: ✅ API Online | ❓ Perlu testing di localhost
