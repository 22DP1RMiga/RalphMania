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

# SQL imports caur PHP - tikai INSERT dati, izlaiž CREATE TABLE
if [ -f /var/www/html/database/ralphmania.sql ]; then
    PRODUCT_COUNT=$(php -r "
        \$pdo = new PDO('mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT')?:'3306').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT=>false]);
        echo \$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    " 2>/dev/null || echo "0")

    if [ "${FORCE_SQL_IMPORT}" = "true" ] || [ "${PRODUCT_COUNT}" = "0" ] || [ -z "${PRODUCT_COUNT}" ]; then
        echo "📦 Importē SQL datus (tikai INSERT)..."
        php -r "
            \$host = getenv('DB_HOST');
            \$port = getenv('DB_PORT') ?: '3306';
            \$db   = getenv('DB_DATABASE');
            \$user = getenv('DB_USERNAME');
            \$pass = getenv('DB_PASSWORD');
            \$pdo = new PDO(
                \"mysql:host={\$host};port={\$port};dbname={\$db}\",
                \$user, \$pass,
                [PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false]
            );
            // Izslēdz FK pārbaudes importa laikā
            \$pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            \$pdo->exec('SET NAMES utf8mb4');

            \$sql = file_get_contents('/var/www/html/database/ralphmania.sql');
            \$lines = explode(\"\n\", \$sql);
            \$ok = 0; \$skip = 0; \$fail = 0;
            \$buffer = '';

            foreach (\$lines as \$line) {
                \$line = trim(\$line);
                // Izlaiž komentārus un tukšas rindiņas
                if (empty(\$line) || strpos(\$line, '--') === 0 || strpos(\$line, '/*') === 0) continue;
                // Izlaiž CREATE, DROP, ALTER, SET (tikai INSERT vajag)
                if (preg_match('/^(CREATE|DROP|ALTER|LOCK|UNLOCK)/i', \$line)) { \$skip++; continue; }

                \$buffer .= ' ' . \$line;

                if (substr(\$buffer, -1) === ';') {
                    \$stmt = trim(\$buffer);
                    \$buffer = '';
                    if (empty(\$stmt)) continue;
                    try {
                        \$pdo->exec(\$stmt);
                        \$ok++;
                    } catch(Exception \$e) {
                        \$fail++;
                        // echo 'KĻŪDA: ' . \$e->getMessage() . PHP_EOL;
                    }
                }
            }
            \$pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            echo \"✅ Importēti: {\$ok}, izlaisti: {\$skip}, kļūdas: {\$fail}\n\";
        "
        echo "✅ SQL imports pabeigts!"
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
