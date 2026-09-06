#!/usr/bin/env bash
set -euo pipefail

# Run from the Laravel project root (directory containing artisan).
#
# Optional environment variables:
#   MAINTENANCE_BYPASS_SECRET — if set, uses: php artisan down --secret=...
#   SKIP_PERMISSION_SEEDER=1 — do not run PermissionSeeder after migrate
#   SKIP_SETTINGS_SEEDER=1 — do not run SettingsSeeder after migrate
#   BUILD_FRONTEND=1 — run npm ci && npm run build (requires Node.js on the server)

echo "Optimizing Laravel application..."

if [[ ! -f artisan ]]; then
  echo "error: artisan not found; run this script from the project root" >&2
  exit 1
fi

mkdir -p bootstrap/cache \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views

if [[ -n "${MAINTENANCE_BYPASS_SECRET:-}" ]]; then
  php artisan down --secret="$MAINTENANCE_BYPASS_SECRET" || true
else
  php artisan down || true
fi

echo "Installing Composer dependencies..."
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if [[ "${BUILD_FRONTEND:-0}" == "1" ]] && [[ -f package.json ]] && command -v npm >/dev/null 2>&1; then
  echo "Building frontend assets..."
  npm ci --no-audit --no-fund
  npm run build
fi

echo "Running migrations..."
php artisan migrate --force

echo "Rebuilding caches..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

composer dump-autoload --optimize --no-dev

php artisan queue:restart 2>/dev/null || true

echo "Bringing application back up..."
php artisan up

echo "Done."
