# 🚀 Deployment Guide - EPOS SAZA

## Environment Configuration

### 🖥️ **LOCAL Development**
File: `.env`
```env
SIMPELS_API_URL=http://localhost:8001/api/v1/wallets
APP_ENV=local
APP_DEBUG=true
```

### 🌐 **PRODUCTION Deployment**
File: `.env` (di server production)

**Template tersedia di:** `.env.production`

#### Persiapan sebelum deploy:

```bash
# 1. Copy template production
cp .env.production .env

# 2. Update environment variables yang PENTING:
SIMPELS_API_URL=https://api-simpels.saza.sch.id/api/v1/wallets
SIMPELS_API_KEY=<API_KEY_dari_tim_simpels>
APP_ENV=production
APP_DEBUG=false
APP_URL=https://epos.saza.sch.id
```

---

## ✅ **Pre-Deployment Checklist**

### Environment Variables
- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] `APP_URL=https://epos.saza.sch.id` (ganti dengan domain actual)
- [ ] `SIMPELS_API_URL=https://api-simpels.saza.sch.id/api/v1/wallets`
- [ ] `SIMPELS_API_KEY=<api_key>` (dapatkan dari tim SAZA)
- [ ] `LOG_LEVEL=warning` (atau `error`)

### Database
- [ ] Database sudah disetup (migrasi sudah jalan)
- [ ] Admin user sudah dibuat: `php artisan db:seed --class=AdminUserSeeder`

### Cache & Config
- [ ] Jalankan: `php artisan config:cache`
- [ ] Jalankan: `php artisan route:cache`
- [ ] Jalankan: `php artisan view:cache`
- [ ] Jalankan: `composer install --no-dev --optimize-autoloader`

### Security
- [ ] `.env` ada di `.gitignore` (jangan commit)
- [ ] Generate APP_KEY: `php artisan key:generate`
- [ ] SSL Certificate sudah installed di web server

---

## 🔄 **Deployment Steps**

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies (production)
composer install --no-dev --optimize-autoloader

# 3. Update .env untuk production
# ⚠️ JANGAN LUPA EDIT:
#   - SIMPELS_API_URL
#   - SIMPELS_API_KEY
#   - APP_URL
#   - DB_HOST, DB_USER, DB_PASSWORD (jika tidak SQLite)

# 4. Run migrations
php artisan migrate --force

# 5. Seed admin data (hanya first time)
php artisan db:seed --class=AdminUserSeeder

# 6. Cache optimization
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Test API connection
php artisan tinker
# Lalu ketik: app(App\Services\SimpelsApiService::class)->testConnection()
# Seharusnya return status 'healthy'

# 8. Restart queue worker (jika ada)
php artisan queue:restart
```

---

## 🌐 **API Endpoints**

| Environment | URL |
|---|---|
| Local | `http://localhost:8001/api/v1/wallets` |
| Production | `https://api-simpels.saza.sch.id/api/v1/wallets` |

> **Note:** URL configuration via environment variable `SIMPELS_API_URL` di file `.env`

---

## 🧪 **Testing Connection**

### Via Artisan Tinker
```bash
php artisan tinker
app(App\Services\SimpelsApiService::class)->getHealthStatus()
```

### Via HTTP Request
```bash
curl -X GET "https://epos.saza.sch.id/api/simpels/test-connection"
```

Expected response:
```json
{
    "success": true,
    "message": "Connection test completed",
    "data": {
        "status": "healthy",
        "response_time_ms": 45.23,
        "api_url": "https://api-simpels.saza.sch.id/api/v1/wallets"
    }
}
```

---

## ⚠️ **Troubleshooting**

### "Connection timeout" error
- [ ] Pastikan `SIMPELS_API_URL` benar di `.env`
- [ ] Cek firewall rules
- [ ] Cek API availability di `SIMPELS_API_URL`

### "Invalid API Key" error
- [ ] Pastikan `SIMPELS_API_KEY` sudah set di `.env`
- [ ] Konfirmasi API key dengan tim SAZA

### 404 Santri not found
- [ ] RFID tag yang dicari tidak ada di SIMPELS
- [ ] Check logs: `storage/logs/laravel.log`

---

## 📝 **Disable Test Endpoints (Production Only)**

Untuk production, disarankan untuk disable testing endpoints di `routes/api.php`:

```php
// ❌ JANGAN UNCOMMENT TEST ROUTES DI PRODUCTION:
// Route::prefix('simpels')->group(function () {
//     Route::get('/test-connection', [SimpelsTestController::class, 'testConnection']);
//     Route::get('/test-santri/{uid}', [SimpelsTestController::class, 'testSantriLookup']);
//     Route::post('/test-transaction', [SimpelsTestController::class, 'testTransaction']);
// });
```

---

## 📚 **References**

- [Laravel Deployment](https://laravel.com/docs/deployment)
- [SIMPels API Documentation](https://api-simpels.saza.sch.id/docs)
- [Environment Variables](config/services.php#L36)
