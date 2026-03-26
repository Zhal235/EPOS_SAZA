# 🔥 QUICK FIX - Performance Optimization

## ⚡ Masalah Utama

EPOS lambat di server karena:
1. ❌ Cache menggunakan `database` bukan `redis`
2. ❌ Session menggunakan `database` bukan `redis`  
3. ❌ PHP OPcache tidak dikonfigurasi
4. ❌ Nginx tidak ada compression & caching
5. ❌ Database query tanpa index optimal

---

## 🎯 Solusi dengan Dokploy (5 menit)

### **STEP 1: Update Environment Variables di Dokploy**

1. **Login ke Dokploy Dashboard**
2. Buka project **epos-saza** > **Environment**
3. **Update atau tambahkan** variable berikut:

```env
CACHE_DRIVER=redis        # ⚡ PENTING: Ganti dari database
SESSION_DRIVER=redis      # ⚡ PENTING: Ganti dari database
QUEUE_CONNECTION=redis    # ⚡ PENTING: Ganti dari database
APP_DEBUG=false          # 🔒 SECURITY: Matikan debug mode
LOG_LEVEL=warning        # 📉 Kurangi logging overhead
APP_ENV=production       # ✅ Production mode
```

4. **Save** perubahan environment

### **STEP 2: Push Code ke Git**

```bash
# Di local development
git add .
git commit -m "feat: add performance optimizations"
git push origin main
```

### **STEP 3: Redeploy di Dokploy**

**Opsi A - Manual Trigger:**
1. Di Dokploy Dashboard > Project **epos-saza**
2. Klik tombol **"Redeploy"** atau **"Deploy"**
3. Pantau di tab **Logs** hingga selesai

**Opsi B - Auto Deploy (jika sudah diaktifkan):**
- Dokploy akan **otomatis detect** push ke Git
- Tunggu 2-5 menit untuk rebuild & redeploy
- Cek tab **Logs** untuk progress

### **STEP 4: Verify (Opsional)**

```bash
# SSH ke server (jika perlu)
ssh user@your-server.com

# Test Redis cache
docker exec -it saza-epos-app php artisan tinker
>>> Cache::store('redis')->put('test', 'ok', 60);
>>> Cache::store('redis')->get('test');
# Output: "ok" ✅
>>> exit

# Check PHP OPcache
docker exec -it saza-epos-app php -i | grep opcache.enable
# Output: opcache.enable => On => On ✅
```

---

## 📊 Expected Results

**Sebelum:**
- ⏱️ Page load: 3-5 detik
- 🐌 Database queries: 50-100 per request
- 💾 Memory usage: tinggi

**Sesudah:**
- ⚡ Page load: 0.5-1 detik (3-5x lebih cepat!)
- 🚀 Database queries: 10-20 per request
- ✅ Memory usage: optimal dengan OPcache

---

## � DEPLOYMENT (DOKPLOY - OTOMATIS)

Karena menggunakan Dokploy, deployment otomatis ketika push ke Git!

### **1. Di Local (Development)**

```bash
# Commit & push perubahan
git add .
git commit -m "feat: add performance optimizations"
git push origin main
```

### **2. Dokploy (Otomatis)**

Dokploy akan **otomatis**:
1. ✅ Detect push ke repository
2. ✅ Clone latest code
3. ✅ Build Docker image dengan optimization baru (OPcache, dll)
4. ✅ Deploy container baru
5. ✅ Run migrations (via entrypoint.sh)
6. ✅ Optimize Laravel (via entrypoint.sh)

**Pantau deployment:**
- Login ke **Dokploy Dashboard**
- Buka project **epos-saza** > Tab **Logs**
- Tunggu hingga status "Running" (biasanya 2-5 menit)

### **3. Verify di Server (Opsional)**

Jika deployment sudah selesai, verify optimization:

```bash
# Test via Dokploy Terminal (di dashboard)
# Atau SSH ke server:
ssh user@your-server.com

# Check container status
docker ps | grep epos

# Test Redis cache (masuk ke container)
docker exec -it saza-epos-app php artisan tinker
>>> Cache::store('redis')->put('test', 'ok', 60);
>>> Cache::store('redis')->get('test');
# Output: "ok" ✅
>>> exit

# Check PHP OPcache
docker exec -it saza-epos-app php -i | grep opcache.enable
# Output: opcache.enable => On => On ✅
```

---

## 🔍 Troubleshooting

### Issue: Deployment gagal di Dokploy

**Solution:**
1. Cek **Logs** tab di Dokploy
2. Pastikan environment variables sudah benar
3. Cek apakah build error (biasanya composer/npm issue)
4. Klik **Redeploy** untuk retry

### Issue: Redis error

**Solution:**
```bash
# Via Dokploy Terminal atau SSH
docker restart saza-epos-redis

# Check Redis logs
docker logs saza-epos-redis --tail=50
```

### Issue: Aplikasi masih lambat

**Solution:**
```bash
# Clear semua cache
docker exec -it saza-epos-app php artisan optimize:clear

# Restart container
docker restart saza-epos-app

# Check PHP OPcache status
docker exec -it saza-epos-app php -i | grep opcache
# Harus output: opcache.enable => On
```

### Issue: Permission error

**Solution:**
```bash
# Fix permissions
docker exec -it saza-epos-app chown -R www-data:www-data /var/www/html/storage
docker exec -it saza-epos-app chmod -R 775 /var/www/html/storage
```

### Issue: Environment variables tidak apply

**Solution:**
1. Update environment di **Dokploy Dashboard** > Environment tab
2. **Save** perubahan
3. Klik **Redeploy** (harus redeploy agar env apply)

---

## 📁 File yang Diubah

✅ [Dockerfile](Dockerfile) - Tambah PHP OPcache & optimization
✅ [docker/nginx/default.conf](docker/nginx/default.conf) - Tambah gzip & caching
✅ [docker/entrypoint.sh](docker/entrypoint.sh) - Auto-optimization on deploy
✅ [database/migrations/*_add_performance_indexes.php](database/migrations) - Database indexes
✅ [.env.production.example](.env.production.example) - Template production config

---

## 🎓 Penjelasan Teknis

### 1. Redis Cache
Redis adalah in-memory cache yang **100x lebih cepat** dari database cache.

### 2. PHP OPcache
OPcache menyimpan compiled PHP code di memory, tidak perlu compile ulang setiap request.

### 3. Database Indexes
Index membuat query database **10-50x lebih cepat** untuk pencarian dan filtering.

### 4. Nginx Gzip
Compression membuat transfer data **3-5x lebih kecil**, page load lebih cepat.

### 5. Laravel Caching
Config/route/view cache menghilangkan file reading overhead.

---

## 📞 Support

Jika masih lambat setelah optimization:
1. Check log: `docker compose logs -f epos-saza`
2. Check Redis: `docker compose exec epos-redis redis-cli INFO`
3. Check MySQL: `docker compose exec epos-db mysql -u root -p`

Baca dokumentasi lengkap: [PERFORMANCE_OPTIMIZATION.md](PERFORMANCE_OPTIMIZATION.md)

---

**Last Updated:** March 26, 2026
