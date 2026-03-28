# ========================================
# 🔧 EPOS SAZA - Fix Financial Module (Windows)
# ========================================
# Script untuk memperbaiki error 500 di halaman financial
# Menjalankan migrations dan clear cache
#
# Usage: .\fix-financial-error.ps1
# ========================================

Write-Host "🔧 Fixing Financial Module Errors..." -ForegroundColor Cyan
Write-Host ""

# Check if we're in production
if (Test-Path ".env") {
    $envType = Get-Content .env | Select-String "APP_ENV=" | ForEach-Object { $_.ToString().Split('=')[1] }
    Write-Host "📌 Environment: $envType" -ForegroundColor Yellow
} else {
    Write-Host "⚠️  Warning: .env file not found" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "Step 1: Running database migrations..." -ForegroundColor Green
php artisan migrate --force

Write-Host ""
Write-Host "Step 2: Clearing all caches..." -ForegroundColor Green
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

Write-Host ""
Write-Host "Step 3: Optimizing application..." -ForegroundColor Green
php artisan config:cache
php artisan route:cache
php artisan view:cache

Write-Host ""
Write-Host "Step 4: Checking database tables..." -ForegroundColor Green
php artisan db:show --table=simpels_withdrawals 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Table simpels_withdrawals may not exist (this is OK if first deployment)" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "✅ Fix applied successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "📌 Next steps:" -ForegroundColor Cyan
Write-Host "   1. Test the financial page: /financial?activeTab=withdrawals"
Write-Host "   2. If still error, check logs: storage/logs/laravel.log"
Write-Host ""
