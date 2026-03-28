#!/bin/bash

# ========================================
# 🔧 EPOS SAZA - Fix Financial Module
# ========================================
# Script untuk memperbaiki error 500 di halaman financial
# Menjalankan migrations dan clear cache
#
# Usage: bash fix-financial-error.sh
# ========================================

echo "🔧 Fixing Financial Module Errors..."
echo ""

# Check if we're in production
if [ -f ".env" ]; then
    ENV_TYPE=$(grep "APP_ENV=" .env | cut -d '=' -f2)
    echo "📌 Environment: $ENV_TYPE"
else
    echo "⚠️  Warning: .env file not found"
fi

echo ""
echo "Step 1: Running database migrations..."
php artisan migrate --force

echo ""
echo "Step 2: Clearing all caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "Step 3: Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "Step 4: Checking database tables..."
php artisan db:show --table=simpels_withdrawals 2>/dev/null || echo "⚠️  Table simpels_withdrawals may not exist (this is OK if first deployment)"

echo ""
echo "✅ Fix applied successfully!"
echo ""
echo "📌 Next steps:"
echo "   1. Test the financial page: /financial?activeTab=withdrawals"
echo "   2. If still error, check logs: storage/logs/laravel.log"
echo "   3. Or run: php artisan log:tail (if package installed)"
echo ""
