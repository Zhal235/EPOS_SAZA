# DOKPLOY DEPLOYMENT - EPOS SAZA

## 📋 Overview

Aplikasi ini otomatis deploy menggunakan **Dokploy** setiap kali ada push ke Git repository.

---

## ✅ DEPLOYMENT WORKFLOW

### **1. Update Environment Variables (First Time atau Perubahan Config)**

Login ke **Dokploy Dashboard**:
1. Buka project **epos-saza**
2. Masuk ke tab **Environment**
3. Pastikan variables berikut sudah diset:

```env
# ⚡ PERFORMANCE - CRITICAL
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_PREFIX=epos_cache

# 🔒 SECURITY
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY

# 📊 LOGGING
LOG_LEVEL=warning
LOG_CHANNEL=stack

# 💾 DATABASE
DB_CONNECTION=mysql
DB_HOST=epos-db
DB_PORT=3306
DB_DATABASE=epos_saza_production
DB_USERNAME=epos_user
DB_PASSWORD=YOUR_STRONG_PASSWORD

# 🔄 REDIS
REDIS_HOST=epos-redis
REDIS_CLIENT=phpredis
REDIS_PORT=6379

# 🔗 SIMPELS API
SIMPELS_API_URL=http://simpelssaza-simpelsapi-2ebzdr:8000/api/v1/wallets
SIMPELS_API_KEY=YOUR_SIMPELS_API_KEY
EPOS_WEBHOOK_SECRET=YOUR_WEBHOOK_SECRET
```

4. **Save** environment variables
5. **Important:** Setelah save, **Redeploy** aplikasi agar env baru apply

---

### **2. Push Code (Auto-Deploy Triggered)**

```bash
# Di local development
git add .
git commit -m "feat: your commit message"
git push origin main
```

**Dokploy akan otomatis:**
1. ✅ Detect push event (webhook dari Git)
2. ✅ Clone repository terbaru
3. ✅ Build Docker image (`docker-compose.yml`)
   - Build asset dengan Vite (Node.js)
   - Install composer dependencies
   - Apply PHP optimizations (OPcache, realpath cache)
4. ✅ Deploy containers:
   - `saza-epos-app` (Laravel app dengan Nginx + PHP-FPM)
   - `saza-epos-db` (MySQL)
   - `saza-epos-redis` (Redis)
5. ✅ Run entrypoint script:
   - Wait for database
   - Run migrations
   - Cache config, routes, views
   - Optimize autoloader

---

### **3. Monitor Deployment**

**Via Dokploy Dashboard:**
1. Login ke Dokploy
2. Buka project **epos-saza**
3. Masuk ke tab **Logs**
4. Monitor progress deployment:
   ```
   Building assets...
   Installing dependencies...
   Building Docker image...
   Starting containers...
   Running migrations...
   Optimizing Laravel...
   ✅ Deployment complete!
   ```
5. Status akan berubah menjadi **"Running"** (biasanya 2-5 menit)

**Via SSH (Optional):**
```bash
ssh user@your-server.com
docker ps | grep epos
docker logs saza-epos-app -f
```

---

### **4. Verify Deployment**

#### **A. Check Application Status**

**Via Dokploy Dashboard:**
- Tab **Logs**: Pastikan tidak ada error
- Tab **Metrics**: CPU, Memory, Network usage
- Tab **Terminal**: Access container shell

**Via SSH:**
```bash
# Check running containers
docker ps | grep saza-epos

# Check app logs
docker logs saza-epos-app --tail=100

# Check Redis
docker logs saza-epos-redis --tail=50

# Check MySQL
docker logs saza-epos-db --tail=50
```

#### **B. Verify Redis Cache**

```bash
# Masuk ke container
docker exec -it saza-epos-app php artisan tinker

# Test Redis
>>> Cache::store('redis')->put('test_deploy', 'success', 60);
>>> Cache::store('redis')->get('test_deploy');
# Output: "success" ✅
>>> exit
```

#### **C. Verify PHP OPcache**

```bash
# Check OPcache status
docker exec -it saza-epos-app php -i | grep opcache.enable
# Output: opcache.enable => On => On ✅

# Check OPcache stats
docker exec -it saza-epos-app php -r "print_r(opcache_get_status());"
```

#### **D. Verify Database**

```bash
# Check migrations
docker exec -it saza-epos-app php artisan migrate:status

# Check database connection
docker exec -it saza-epos-db mysql -u root -p
> SHOW DATABASES;
> USE epos_saza_production;
> SHOW TABLES;
> exit
```

---

## 🔄 MANUAL REDEPLOY

Jika perlu trigger manual deploy (tanpa push ke Git):

1. Login ke **Dokploy Dashboard**
2. Buka project **epos-saza**
3. Klik tombol **"Redeploy"** atau **"Deploy"**
4. Confirm action
5. Monitor di tab **Logs**

**Gunakan manual redeploy untuk:**
- Setelah update environment variables
- Setelah update Dokploy settings
- Force rebuild jika deployment gagal
- Testing deployment process

---

## 🎯 POST-DEPLOYMENT CHECKLIST

Setelah deployment berhasil:

- [ ] ✅ Application accessible via domain
- [ ] ✅ HTTPS/SSL working (Let's Encrypt)
- [ ] ✅ Login page loading
- [ ] ✅ Can login with admin credentials
- [ ] ✅ POS Terminal working
- [ ] ✅ Dashboard showing statistics
- [ ] ✅ SIMPELS integration working
- [ ] ✅ No errors in logs

**Test Critical Features:**
```bash
# Via browser atau Postman
- Login: https://epos-simpels.saza.sch.id/login
- Dashboard: https://epos-simpels.saza.sch.id/dashboard
- POS: https://epos-simpels.saza.sch.id/pos
- Test RFID scan
- Test transaction
```

---

## 🐛 TROUBLESHOOTING

### Issue: Container Name Conflict

**Error Message:**
```
Error response from daemon: Conflict. The container name "/saza-epos-app" is already in use
```

**Root Cause:** Container lama masih ada di server dan mencegah deploy baru.

**✅ Solution (Sudah Diperbaiki):**
Sejak update terbaru, `docker-compose.yml` tidak lagi menggunakan hardcoded container names. Docker akan auto-generate nama unik sehingga tidak ada konflik.

**Jika masih terjadi error:**
1. **Via Dokploy Console** (jika tersedia):
   - Buka project > Terminal/Console
   - Jalankan:
     ```bash
     docker rm -f saza-epos-app saza-epos-db saza-epos-redis
     ```
   - Redeploy aplikasi

2. **Via SSH** (jika ada akses):
   - SSH ke server
   - Jalankan cleanup script:
     ```bash
     cd /path/to/project
     bash docker-cleanup.sh
     ```
   - Atau manual:
     ```bash
     docker stop saza-epos-app saza-epos-db saza-epos-redis
     docker rm -f saza-epos-app saza-epos-db saza-epos-redis
     ```
   - Redeploy dari Dokploy dashboard

3. **Tanpa akses Terminal:**
   - Update ke versi terbaru dengan `git pull`
   - File `docker-compose.yml` yang baru sudah fix masalah ini
   - Redeploy akan otomatis menggunakan nama dinamis

### Issue: Deployment Stuck/Failed

**Solution:**
1. Check **Logs** tab di Dokploy untuk error messages
2. Common errors:
   - **Composer install error**: Check `composer.json` syntax
   - **NPM build error**: Check `package.json` dan Vite config
   - **Docker build error**: Check `Dockerfile` syntax
   - **Container conflict**: Lihat section "Container Name Conflict" di atas
3. Fix error di local, commit, push lagi
4. Atau klik **Redeploy** untuk retry

### Issue: Redis Connection Error

**Symptoms:** Cache errors, session errors in logs

**Solution:**
```bash
# Check Redis container
docker ps | grep redis

# Restart Redis
docker restart saza-epos-redis

# Check Redis connectivity dari app
docker exec -it saza-epos-app php artisan tinker
>>> Redis::connection()->ping();
# Output: PONG ✅
```

### Issue: Database Connection Error

**Symptoms:** SQLSTATE errors, migration errors

**Solution:**
```bash
# Check MySQL container
docker ps | grep mysql

# Check logs
docker logs saza-epos-db --tail=100

# Verify environment variables
# Di Dokploy > Environment:
# DB_HOST=epos-db (bukan localhost!)
# DB_PASSWORD harus match dengan MYSQL_ROOT_PASSWORD

# Test connection dari app
docker exec -it saza-epos-app php artisan tinker
>>> DB::connection()->getPdo();
# Tidak ada error = berhasil ✅
```

### Issue: Environment Variables Tidak Apply

**Symptoms:** Masih pakai old config, cache driver masih database

**Solution:**
1. Update environment di **Dokploy Dashboard**
2. **Save** perubahan
3. **PENTING:** Harus **Redeploy** (restart saja tidak cukup!)
4. Verify setelah redeploy:
   ```bash
   docker exec -it saza-epos-app php artisan tinker
   >>> config('cache.default');
   # Output: "redis" ✅
   ```

### Issue: Aplikasi Masih Lambat

**Check Optimization Status:**
```bash
# 1. Check cache driver
docker exec -it saza-epos-app php artisan tinker
>>> config('cache.default');
# Harus: "redis" bukan "database"

# 2. Check OPcache
docker exec -it saza-epos-app php -i | grep opcache.enable
# Harus: On

# 3. Check Laravel caches
docker exec -it saza-epos-app ls -la bootstrap/cache/
# Harus ada: config.php, routes-v7.php

# 4. Clear & rebuild cache
docker exec -it saza-epos-app php artisan optimize:clear
docker exec -it saza-epos-app php artisan config:cache
docker exec -it saza-epos-app php artisan route:cache
docker exec -it saza-epos-app php artisan view:cache
```

### Issue: Permission Errors (Storage)

**Symptoms:** Unable to write to log, cache, session files

**Solution:**
```bash
# Fix permissions
docker exec -it saza-epos-app chown -R www-data:www-data /var/www/html/storage
docker exec -it saza-epos-app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker exec -it saza-epos-app chmod -R 775 /var/www/html/storage
docker exec -it saza-epos-app chmod -R 775 /var/www/html/bootstrap/cache

# Restart container
docker restart saza-epos-app
```

### Issue: 502 Bad Gateway

**Causes:** PHP-FPM not running, Nginx misconfigured

**Solution:**
```bash
# Check container logs
docker logs saza-epos-app --tail=100

# Check if PHP-FPM running
docker exec -it saza-epos-app ps aux | grep php-fpm

# Restart container
docker restart saza-epos-app

# Check Nginx config
docker exec -it saza-epos-app nginx -t
```

---

## 📂 ROLLBACK

Jika deployment baru bermasalah dan perlu rollback:

**Opsi 1: Git Revert**
```bash
# Di local
git log --oneline  # Lihat commit history
git revert <commit-hash>
git push origin main
# Dokploy akan auto-deploy commit revert
```

**Opsi 2: Manual di Dokploy**
1. Di Dokploy, check build history
2. Pilih build previous yang working
3. Redeploy dari build tersebut

**Opsi 3: Force Previous Docker Image**
```bash
# SSH ke server
docker images | grep epos-saza

# Tag previous working image
docker tag epos-saza-app:previous epos-saza-app:latest

# Restart container
docker restart saza-epos-app
```

---

## 📊 MONITORING

### **Via Dokploy Dashboard**
- **Logs**: Real-time application logs
- **Metrics**: CPU, Memory, Disk, Network
- **Terminal**: Direct container access
- **Events**: Deployment history

### **Via SSH Commands**
```bash
# Resource usage
docker stats saza-epos-app saza-epos-db saza-epos-redis

# Application logs
docker logs saza-epos-app -f --tail=100

# Redis stats
docker exec -it saza-epos-redis redis-cli INFO stats

# MySQL process list
docker exec -it saza-epos-db mysql -u root -p
> SHOW PROCESSLIST;
```

### **Health Checks**
```bash
# HTTP health check
curl -I http://localhost

# Redis health check
docker exec -it saza-epos-redis redis-cli PING
# Output: PONG

# MySQL health check
docker exec -it saza-epos-db mysqladmin ping -u root -p
# Output: mysqld is alive
```

---

## 🔐 SECURITY NOTES

1. **Never commit `.env` file** - Always in `.gitignore`
2. **Use strong passwords** for DB_PASSWORD (min 16+ chars)
3. **Rotate APP_KEY** periodically
4. **Keep SIMPELS_API_KEY secret** - Only in Dokploy env
5. **Monitor logs** for suspicious activities
6. **Regular backups** of database
7. **Update dependencies** regularly

---

## 📞 SUPPORT

**Jika ada masalah:**
1. Check logs di Dokploy Dashboard
2. Refer to this troubleshooting guide
3. Check main documentation: [PERFORMANCE_OPTIMIZATION.md](PERFORMANCE_OPTIMIZATION.md)
4. Contact DevOps team

---

**Last Updated:** March 26, 2026  
**Deployment Platform:** Dokploy  
**Container Runtime:** Docker
