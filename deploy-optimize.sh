#!/bin/bash

# =============================================
# EPOS SAZA - Deployment & Optimization Script
# =============================================
# Run this script on server after git pull
# Usage: ./deploy-optimize.sh

set -e

echo "🚀 EPOS SAZA - Deployment & Optimization"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if running in production
if [ "$APP_ENV" != "production" ] && [ -f .env ]; then
    APP_ENV=$(grep APP_ENV .env | cut -d '=' -f2)
fi

if [ "$APP_ENV" != "production" ]; then
    echo -e "${YELLOW}⚠️  WARNING: Not running in production mode!${NC}"
    read -p "Continue anyway? (y/N) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Step 1: Stop containers
echo -e "${YELLOW}[1/8] Stopping containers...${NC}"
docker compose down

# Step 2: Pull latest code (if needed)
echo -e "${YELLOW}[2/8] Pulling latest code...${NC}"
git pull origin main || echo "Already up to date"

# Step 3: Rebuild containers
echo -e "${YELLOW}[3/8] Rebuilding containers...${NC}"
docker compose build --no-cache epos-saza

# Step 4: Start containers
echo -e "${YELLOW}[4/8] Starting containers...${NC}"
docker compose up -d

# Step 5: Wait for services to be ready
echo -e "${YELLOW}[5/8] Waiting for services to be ready...${NC}"
sleep 30

# Step 6: Run migrations
echo -e "${YELLOW}[6/8] Running database migrations...${NC}"
docker compose exec -T epos-saza php artisan migrate --force

# Step 7: Clear and rebuild caches
echo -e "${YELLOW}[7/8] Optimizing application...${NC}"
docker compose exec -T epos-saza php artisan optimize:clear
docker compose exec -T epos-saza php artisan config:cache
docker compose exec -T epos-saza php artisan route:cache
docker compose exec -T epos-saza php artisan view:cache
docker compose exec -T epos-saza php artisan event:cache

# Step 8: Verify deployment
echo -e "${YELLOW}[8/8] Verifying deployment...${NC}"
docker compose ps

echo ""
echo -e "${GREEN}✅ Deployment complete!${NC}"
echo ""
echo "📊 Check application status:"
echo "  - Logs: docker compose logs -f epos-saza"
echo "  - Health: docker compose ps"
echo "  - Redis: docker compose exec epos-redis redis-cli PING"
echo ""
echo "🔍 Test Redis cache:"
echo "  docker compose exec epos-saza php artisan tinker"
echo "  >>> Cache::store('redis')->put('test', 'ok', 60);"
echo "  >>> Cache::store('redis')->get('test');"
echo ""
echo -e "${GREEN}🎉 Done!${NC}"
