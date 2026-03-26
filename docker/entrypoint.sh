#!/bin/sh
set -e

# Wait for database to be ready
echo "Waiting for database connection..."
until php -r "
    try {
        \$dsn = 'mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: 3306) . ';dbname=' . getenv('DB_DATABASE');
        new PDO(\$dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_TIMEOUT => 3]);
        exit(0);
    } catch (Exception \$e) {
        exit(1);
    }
" 2>/dev/null; do
    echo "Database not ready, retrying in 3 seconds..."
    sleep 3
done
echo "Database is ready."

# Storage setup
echo "Setting up storage..."
mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan storage:link --force 2>/dev/null || true

# Publish Livewire assets
echo "Publishing Livewire assets..."
php artisan livewire:publish --force 2>/dev/null || true

# Run Laravel setup
echo "🚀 Optimizing Laravel application..."

# Clear all caches first (important for clean state)
php artisan optimize:clear

# Cache configuration for faster boot
echo "  ➤ Caching configuration..."
php artisan config:cache

# Cache routes for faster routing
echo "  ➤ Caching routes..."
php artisan route:cache

# Cache views for faster rendering
echo "  ➤ Caching views..."
php artisan view:cache

# Cache events for faster event dispatch
echo "  ➤ Caching events..."
php artisan event:cache

# Optimize composer autoloader (again, for safety)
echo "  ➤ Optimizing autoloader..."
composer dump-autoload --optimize --classmap-authoritative --no-dev 2>/dev/null || true

echo "✅ Optimization complete!"

echo "Running migrations..."
php artisan migrate --force

echo "Starting Supervisor..."
exec "$@"
