#!/bin/bash

# ========================================
# 🧹 EPOS SAZA - Docker Cleanup Script
# ========================================
# Script untuk membersihkan container lama yang konflik
# Gunakan script ini jika deployment gagal karena container conflict
#
# Usage: bash docker-cleanup.sh
# ========================================

echo "🧹 Cleaning up EPOS SAZA containers..."

# Stop dan hapus container dengan nama hardcoded (legacy)
echo "📦 Stopping and removing legacy containers..."
docker stop saza-epos-app saza-epos-db saza-epos-redis 2>/dev/null || true
docker rm -f saza-epos-app saza-epos-db saza-epos-redis 2>/dev/null || true

# Stop dan hapus semua container dari project ini (berdasarkan label atau prefix)
echo "📦 Stopping all epos-saza related containers..."
docker ps -a --filter "name=epos" --format "{{.Names}}" | xargs -r docker stop 2>/dev/null || true
docker ps -a --filter "name=epos" --format "{{.Names}}" | xargs -r docker rm -f 2>/dev/null || true

# Opsional: Hapus image lama untuk force rebuild
# Uncomment jika ingin rebuild dari scratch
# echo "🗑️  Removing old images..."
# docker rmi epos-saza-app:latest 2>/dev/null || true

# Cleanup dangling images dan volumes
echo "🗑️  Cleaning up dangling images..."
docker image prune -f

echo ""
echo "✅ Cleanup complete!"
echo ""
echo "📌 Next steps:"
echo "   1. Redeploy dari Dokploy dashboard"
echo "   2. Atau jalankan: docker-compose up -d"
echo ""
