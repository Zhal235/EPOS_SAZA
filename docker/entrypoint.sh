#!/bin/sh
set -e

# Wait for database
echo "Waiting for database..."
# In dokploy or production, user might use a wait-for-it script, or just fail and restart.
# Simple check logic isn't built-in to alpine, but we can try connecting with php.

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
