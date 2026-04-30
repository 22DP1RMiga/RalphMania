FROM php:8.3-apache

# Izmanto install-php-extensions - daudz ātrāk, mazāk RAM
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions \
    && install-php-extensions pdo_mysql mbstring zip gd bcmath tokenizer xml curl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache config
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite headers

WORKDIR /var/www/html

# Kopē projektu
COPY . .

# PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Node/Vite build - izmanto apt nodejs (lētāk nekā nvm)
RUN apt-get update && apt-get install -y nodejs npm --no-install-recommends \
    && npm ci && npm run build \
    && rm -rf node_modules \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

COPY docker/start.sh /start.sh
RUN chmod +x /start.sh
CMD ["/start.sh"]
