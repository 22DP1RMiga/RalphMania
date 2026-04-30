#!/bin/bash
set -e

echo "🚀 RalphMania startsolis..."

cd /var/www/html

# Coolify padod env mainīgos tieši - nav vajadzīgs .env fails
# Bet Laravel gaida .env - izveidojam to no env mainīgajiem
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Izveido .env no environment mainīgajiem..."
    printenv | grep -E '^(APP_|DB_|MAIL_|SESSION_|CACHE_|QUEUE_|LOG_|BCRYPT_|VITE_|FILESYSTEM_)' > /var/www/html/.env
fi

# Cache attīrīšana
echo "🧹 Attīra cache..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# Migrācijas
echo "🗄️  Palaiž migrācijas..."
php artisan migrate --force

# Production cache
echo "⚡ Optimizē production cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Storage symlink
php artisan storage:link 2>/dev/null || true

# Permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Gatavs! Apache startē..."
exec apache2-foreground
