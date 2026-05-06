#!/bin/bash
set -e

echo "🚀 RalphMania startsolis..."

cd /var/www/html

# Izveido .env no env mainīgajiem
if [ ! -f /var/www/html/.env ]; then
    echo "📝 Izveido .env..."
    cat > /var/www/html/.env << EOF
APP_NAME="${APP_NAME:-RalphMania}"
APP_ENV="${APP_ENV:-production}"
APP_KEY="${APP_KEY}"
APP_DEBUG="${APP_DEBUG:-false}"
APP_URL="${APP_URL}"
APP_TIMEZONE="${APP_TIMEZONE:-Europe/Riga}"
APP_LOCALE="${APP_LOCALE:-en}"
APP_FALLBACK_LOCALE="${APP_FALLBACK_LOCALE:-en}"

LOG_CHANNEL="${LOG_CHANNEL:-stack}"
LOG_STACK="${LOG_STACK:-single}"
LOG_LEVEL="${LOG_LEVEL:-error}"

DB_CONNECTION="${DB_CONNECTION:-mysql}"
DB_HOST="${DB_HOST}"
DB_PORT="${DB_PORT:-3306}"
DB_DATABASE="${DB_DATABASE}"
DB_USERNAME="${DB_USERNAME}"
DB_PASSWORD="${DB_PASSWORD}"

SESSION_DRIVER="${SESSION_DRIVER:-file}"
SESSION_LIFETIME="${SESSION_LIFETIME:-120}"
SESSION_ENCRYPT="${SESSION_ENCRYPT:-false}"
SESSION_PATH="${SESSION_PATH:-/}"

CACHE_STORE="${CACHE_STORE:-file}"
QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"
FILESYSTEM_DISK="${FILESYSTEM_DISK:-local}"

MAIL_MAILER="${MAIL_MAILER:-smtp}"
MAIL_HOST="${MAIL_HOST}"
MAIL_PORT="${MAIL_PORT:-587}"
MAIL_USERNAME="${MAIL_USERNAME}"
MAIL_PASSWORD="${MAIL_PASSWORD}"
MAIL_ENCRYPTION="${MAIL_ENCRYPTION:-tls}"
MAIL_FROM_ADDRESS="${MAIL_FROM_ADDRESS}"
MAIL_FROM_NAME="${MAIL_FROM_NAME:-RalphMania}"

BCRYPT_ROUNDS="${BCRYPT_ROUNDS:-12}"
VITE_APP_NAME="${VITE_APP_NAME:-RalphMania}"
EOF
fi

# Cache attīrīšana
echo "🧹 Attīra cache..."
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true

# Migrācijas
echo "🗄️  Palaiž migrācijas..."
php artisan migrate --force || echo "⚠️  Migrācijas neizdevās, turpina..."

# Roles seeder - ja roles tabula ir tukša
ROLE_COUNT=$(mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" -e "SELECT COUNT(*) FROM roles;" 2>/dev/null | tail -1 || echo "0")
if [ "${ROLE_COUNT}" = "0" ] || [ -z "${ROLE_COUNT}" ]; then
    echo "🌱 Palaiž RoleSeeder..."
    php artisan db:seed --class=RoleSeeder --force || echo "⚠️  RoleSeeder neizdevās"
else
    echo "ℹ️  Lomas jau eksistē (${ROLE_COUNT} gab.)"
fi

# Importē SQL dump JA produktu tabula ir tukša
if [ -f /var/www/html/database/ralphmania.sql ]; then
    PRODUCT_COUNT=$(mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" -e "SELECT COUNT(*) FROM products;" 2>/dev/null | tail -1 || echo "0")
    if [ "${PRODUCT_COUNT}" = "0" ] || [ -z "${PRODUCT_COUNT}" ]; then
        echo "📦 Importē SQL dump..."
        mysql -h "${DB_HOST}" -P "${DB_PORT:-3306}" -u "${DB_USERNAME}" -p"${DB_PASSWORD}" "${DB_DATABASE}" < /var/www/html/database/ralphmania.sql \
            && echo "✅ SQL imports veiksmīgs!" \
            || echo "⚠️  SQL imports neizdevās"
    else
        echo "ℹ️  Produkti jau eksistē (${PRODUCT_COUNT} gab.), izlaiž importu."
    fi
fi

# Production cache
echo "⚡ Production cache..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

php artisan storage:link 2>/dev/null || true

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

echo "✅ Gatavs! Apache startē..."
exec apache2-foreground
