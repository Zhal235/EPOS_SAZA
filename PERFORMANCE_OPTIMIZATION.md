# 🚀 PERFORMANCE OPTIMIZATION GUIDE - EPOS SAZA

## 📊 Analisis Masalah Performa

Aplikasi EPOS terasa berat di server production karena beberapa faktor:

### 1. **Cache & Session Configuration**
- ❌ Cache driver menggunakan `database` (lambat)
- ❌ Session driver menggunakan `database` (lambat)
- ✅ Redis sudah tersedia tapi tidak digunakan optimal

### 2. **PHP Configuration**
- ❌ OPcache tidak dikonfigurasi dengan baik
- ❌ Realpath cache tidak dioptimalkan
- ❌ Memory limit mungkin terlalu kecil

### 3. **Database Queries**
- ⚠️ Beberapa N+1 query potential
- ⚠️ Tidak ada query result caching
- ⚠️ Index database mungkin belum optimal

### 4. **Laravel Optimization**
- ❌ Config/route/view caching belum dijalan
- ❌ Autoloader optimization belum maksimal
- ❌ Debug mode masih aktif (?)

---

## 🔧 SOLUSI IMPLEMENTASI

### **A. Environment Variables (.env Production)**

Pastikan di server production `.env` sudah dikonfigurasi dengan optimal:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://epos-simpels.saza.sch.id

# Logging - kurangi verbosity
LOG_CHANNEL=stack
LOG_LEVEL=warning
LOG_STACK=daily

# Cache - GUNAKAN REDIS
CACHE_DRIVER=redis
CACHE_PREFIX=epos_cache

# Session - GUNAKAN REDIS
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Queue - GUNAKAN REDIS
QUEUE_CONNECTION=redis

# Redis Configuration
REDIS_HOST=epos-redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=phpredis

# Database - MySQL di Production
DB_CONNECTION=mysql
DB_HOST=epos-db
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

# View Compilation
VIEW_COMPILED_PATH=/var/www/html/storage/framework/views
```

---

### **B. Dockerfile Optimization**

Update file [Dockerfile](Dockerfile) untuk menambahkan PHP optimization:

```dockerfile
FROM php:8.2-fpm-alpine

# Install dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    oniguruma-dev \
    icu-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip \
    && apk add --no-cache $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del $PHPIZE_DEPS

# ========================================
# 🚀 PHP OPTIMIZATION CONFIGURATION
# ========================================

# Create PHP config directory
RUN mkdir -p /usr/local/etc/php/conf.d

# PHP-FPM Optimization
RUN echo "memory_limit = 512M" > /usr/local/etc/php/conf.d/memory.ini \
    && echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "max_execution_time = 60" >> /usr/local/etc/php/conf.d/memory.ini \
    && echo "max_input_time = 60" >> /usr/local/etc/php/conf.d/memory.ini

# OPcache Configuration (CRITICAL for Performance)
RUN echo "opcache.enable=1" > /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.save_comments=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && docker-php-ext-enable opcache

# Realpath Cache (Important for Laravel)
RUN echo "realpath_cache_size=4096K" > /usr/local/etc/php/conf.d/realpath.ini \
    && echo "realpath_cache_ttl=600" >> /usr/local/etc/php/conf.d/realpath.ini

# ========================================

# Configure Nginx
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor.conf /etc/supervisord.conf

# Setup working directory
WORKDIR /var/www/html

# Copy dependencies
COPY --from=deps /app/vendor /var/www/html/vendor
COPY . .
COPY --from=assets /app/public/build /var/www/html/public/build

# Install composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Finish composer setup with optimization
RUN composer dump-autoload --optimize --classmap-authoritative

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod +x /var/www/html/docker/entrypoint.sh

# Expose port
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s \
  CMD curl -f http://localhost/ || exit 1

# Start supervisor
ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
```

---

### **C. Update Entrypoint Script**

Update file [docker/entrypoint.sh](docker/entrypoint.sh) untuk menjalankan optimization commands:

```bash
#!/bin/sh
set -e

# Wait for database to be ready
echo "Waiting for database..."
sleep 10

# Run migrations (first time setup)
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

# 🚀 LARAVEL OPTIMIZATION COMMANDS
echo "Optimizing Laravel application..."

# Clear all caches first
php artisan optimize:clear

# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize autoloader (again, untuk memastikan)
composer dump-autoload --optimize --classmap-authoritative

echo "✅ Optimization complete!"

# Start supervisor (nginx + php-fpm)
exec "$@"
```

Jangan lupa set executable:
```bash
chmod +x docker/entrypoint.sh
```

---

### **D. Nginx Configuration Optimization**

Buat atau update file `docker/nginx/default.conf`:

```nginx
# FastCGI cache configuration
fastcgi_cache_path /var/cache/nginx levels=1:2 keys_zone=EPOS:100m inactive=60m;
fastcgi_cache_key "$scheme$request_method$host$request_uri";

server {
    listen 80;
    listen [::]:80;
    server_name _;
    
    root /var/www/html/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/rss+xml
        font/truetype
        font/opentype
        application/vnd.ms-fontobject
        image/svg+xml;

    # Client body size
    client_max_body_size 20M;
    client_body_timeout 60s;
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }

    # PHP-FPM configuration
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        try_files $uri =404;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        
        # Performance tuning
        fastcgi_buffering on;
        fastcgi_buffer_size 32k;
        fastcgi_buffers 16 16k;
        fastcgi_busy_buffers_size 64k;
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_read_timeout 60s;

        # Cache for static content requests (optional)
        # Uncomment if you want to cache some GET requests
        # fastcgi_cache EPOS;
        # fastcgi_cache_valid 200 60m;
        # fastcgi_cache_bypass $http_pragma $http_authorization;
        # add_header X-Cache-Status $upstream_cache_status;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

### **E. Database Query Optimization**

#### 1. **Tambahkan Index di Migration Baru**

Buat migration untuk menambahkan index yang hilang:

```bash
php artisan make:migration add_performance_indexes_to_tables
```

Isi migration:
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Transaction indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['status', 'created_at']);
            $table->index(['payment_method', 'status']);
            $table->index('user_id');
        });

        // Transaction items indexes
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->index('created_at');
            $table->index(['product_id', 'created_at']);
        });

        // Products indexes (jika belum ada)
        Schema::table('products', function (Blueprint $table) {
            $table->index(['outlet_type', 'is_active']);
            $table->index(['outlet_type', 'is_active', 'stock_quantity']);
            $table->index('name'); // Untuk search
        });

        // Categories & Tenants
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['is_active', 'display_order']);
        });

        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->index(['is_active', 'display_order']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['payment_method', 'status']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['product_id', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['outlet_type', 'is_active']);
            $table->dropIndex(['outlet_type', 'is_active', 'stock_quantity']);
            $table->dropIndex(['name']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'display_order']);
        });

        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropIndex(['is_active', 'display_order']);
            });
        }
    }
};
```

#### 2. **Cache Results di Model**

Update model `Product` untuk caching:

```php
// Di app/Models/Product.php, tambahkan:
use Illuminate\Support\Facades\Cache;

public static function getCachedActiveByOutlet(string $outletType)
{
    return Cache::remember(
        "products.active.{$outletType}",
        now()->addMinutes(30),
        fn() => static::active()
            ->byOutlet($outletType)
            ->with(['category:id,name', 'tenant:id,name'])
            ->get()
    );
}
```

#### 3. **Optimize Dashboard Queries**

Tambahkan caching di Dashboard component untuk statistics yang sering diakses.

---

### **F. Livewire Optimization**

Update `config/livewire.php` untuk lazy loading:

```php
return [
    // ... existing config ...
    
    'lazy_placeholder' => 'livewire.lazy-loading', // Create this view
    
    // Enable legacy model binding if needed
    'legacy_model_binding' => false,
    
    // Disable unnecessary features
    'inject_morph_markers' => false, // Set to false for production
];
```

---

### **G. Supervisor Configuration**

Update `docker/supervisor.conf` untuk menambahkan queue worker (jika belum):

```ini
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisor/supervisord.log
pidfile=/var/run/supervisord.pid

[program:nginx]
command=/usr/sbin/nginx -g "daemon off;"
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:php-fpm]
command=php-fpm8 -F
autostart=true
autorestart=true
priority=5
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
stopwaitsecs=3600
```

---

## 🚀 DEPLOYMENT CHECKLIST

**Note:** Aplikasi ini menggunakan **Dokploy** untuk deployment otomatis.

### **1. Update Environment Variables di Dokploy**

Sebelum deploy, pastikan environment variables sudah optimal:

**Login ke Dokploy Dashboard:**
1. Buka project **epos-saza** > Tab **Environment**
2. Update atau tambahkan variable berikut:

```env
# Cache & Session - CRITICAL untuk performance
CACHE_DRIVER=redis
CACHE_PREFIX=epos_cache
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Database
DB_CONNECTION=mysql
DB_HOST=epos-db
DB_PORT=3306

# Redis
REDIS_HOST=epos-redis
REDIS_CLIENT=phpredis

# App Settings
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
```

3. **Save** perubahan

### **2. Push Code ke Git (Auto-Deploy)**

```bash
# Di local development
git add .
git commit -m "feat: add performance optimizations"
git push origin main
```

### **3. Dokploy Deployment (Otomatis)**

**Jika Auto-Deploy Aktif:**
- Dokploy akan detect push dan **otomatis rebuild**
- Pantau di **Logs** tab
- Tunggu hingga status **"Running"** (2-5 menit)

**Jika Manual Deploy:**
1. Login ke Dokploy Dashboard
2. Buka project **epos-saza**
3. Klik tombol **"Redeploy"**
4. Pantau di tab **Logs**

**Dokploy akan otomatis:**
- ✅ Clone latest code
- ✅ Build image dengan PHP OPcache
- ✅ Deploy container baru
- ✅ Run migrations (via entrypoint.sh)
- ✅ Cache config/routes/views (via entrypoint.sh)

### **4. Verify Deployment (Opsional)**

```bash
# SSH ke server
ssh user@your-server.com

# Check container status
docker ps | grep epos

# Verify Redis cache
docker exec -it saza-epos-app php artisan tinker
>>> Cache::store('redis')->put('test', 'working', 60);
>>> Cache::store('redis')->get('test');
# Output: "working" ✅
>>> exit

# Verify OPcache
docker exec -it saza-epos-app php -i | grep opcache.enable
# Output: opcache.enable => On => On ✅

# Check logs
docker logs saza-epos-app --tail=100
```

### **5. Monitor Performance via Dokploy**

**Melalui Dokploy Dashboard:**
1. Buka project **epos-saza**
2. Tab **Logs**: Lihat application logs real-time
3. Tab **Metrics** (jika ada): CPU, Memory, Network usage
4. Tab **Terminal**: Access container shell langsung

**Atau via SSH:**
```bash
# SSH ke server
ssh user@your-server.com

# Check logs
docker logs saza-epos-app --tail=100 -f

# Check Redis stats
docker exec -it saza-epos-redis redis-cli INFO stats

# Check Redis keys
docker exec -it saza-epos-redis redis-cli KEYS "epos_cache*"

# Check MySQL process
docker exec -it saza-epos-db mysql -u root -p
> SHOW PROCESSLIST;
> exit
```

---

## 📈 EXPECTED IMPROVEMENTS

Setelah optimization:
- ✅ **Page Load Time**: 3-5x lebih cepat
- ✅ **Database Queries**: Berkurang 50-70%
- ✅ **Memory Usage**: Lebih efficient dengan OPcache
- ✅ **Concurrent Users**: Dapat handle 5-10x lebih banyak
- ✅ **Redis Cache Hit Rate**: 80-90%

---

## 🔍 TROUBLESHOOTING

### Issue: Aplikasi masih lambat

**Solution:**
```bash
# Clear semua cache
docker compose exec epos-saza php artisan optimize:clear

# Restart container
docker compose restart epos-saza

# Check Redis memory
docker compose exec epos-redis redis-cli INFO memory
```

### Issue: Error 500 setelah optimization

**Solution:**
```bash
# Check logs
docker compose logs epos-saza --tail=100

# Permissions issue?
docker compose exec epos-saza chown -R www-data:www-data /var/www/html/storage
docker compose exec epos-saza chmod -R 775 /var/www/html/storage
```

### Issue: OPcache tidak aktif

**Solution:**
```bash
# Verify OPcache status
docker compose exec epos-saza php -i | grep opcache

# Jika tidak muncul, rebuild container
docker compose build --no-cache epos-saza
docker compose up -d
```

---

## 📚 REFERENCES

- [Laravel Performance](https://laravel.com/docs/10.x/deployment#optimization)
- [PHP OPcache](https://www.php.net/manual/en/book.opcache.php)
- [Redis Caching](https://redis.io/docs/manual/patterns/caching/)
- [Nginx Performance](https://www.nginx.com/blog/tuning-nginx/)

---

## 🎯 NEXT STEPS (Optional Advanced Optimization)

1. **CDN Integration** - Untuk static assets
2. **Database Replication** - MySQL read replicas
3. **Horizontal Scaling** - Multiple app containers
4. **APM Integration** - New Relic / Datadog monitoring
5. **Query Optimization** - Analyze slow query log

---

**Last Updated:** March 26, 2026  
**Maintained by:** EPOS SAZA Team
