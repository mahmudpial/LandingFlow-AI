#!/usr/bin/env bash
set -euo pipefail

# LandingFlow AI — Production Release Deployment Script

APP_DIR="${APP_DIR:-/var/www/landingflow-ai}"

echo "[1/5] Updating Backend Composer Dependencies..."
cd "${APP_DIR}/backend"
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "[2/5] Running Database Migrations & Seeding Templates..."
php artisan migrate --force
php artisan db:seed --class=TemplateSeeder --force

echo "[3/5] Caching Laravel Configuration & Routes..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true

echo "[4/5] Building Vue 3 + TypeScript Production Bundle..."
cd "${APP_DIR}/frontend"
npm ci
npm run build

echo "[5/5] Reloading Services..."
if command -v systemctl >/dev/null 2>&1; then
  sudo systemctl reload php8.3-fpm || true
  sudo systemctl reload nginx || true
fi

echo "LandingFlow AI deployment completed successfully."
