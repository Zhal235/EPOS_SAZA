#!/bin/sh
set -e

# Wait for database to be ready
echo "Waiting for database connection..."
until php -r "
    \$conn = @new mysqli(
        getenv('DB_HOST'),
        getenv('DB_USERNAME'),
        getenv('DB_PASSWORD'),
        getenv('DB_DATABASE'),
        (int)getenv('DB_PORT') ?: 3306
    );
    if (\$conn->connect_error) { exit(1); }
    exit(0);
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

# Run Laravel setup
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "Running migrations..."
php artisan migrate --force

echo "Starting Supervisor..."
exec "$@"
