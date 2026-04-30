#!/bin/bash
set -e

echo "🚀 RalphMania startsolis..."

if [ ! -f /var/www/html/.env ]; then
    echo "⚠️  .env fails nav atrasts! Kopē no .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

cd /var/www/html

if [ -z "$(grep -E '^APP_KEY=.+' .env)" ]; then
    echo "🔑 Ģenerē APP_KEY..."
    php artisan key:generate --force
fi

echo "🧹 Attīra cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "🗄️  Palaiž migrācijas..."
php artisan migrate --force

echo "⚡ Optimizē production cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan storage:link 2>/dev/null || true

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "✅ Gatavs! Apache startē..."
exec apache2-foreground
