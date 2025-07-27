FROM php:8.4-fpm

# Installa Nginx
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    supervisor \
    libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

COPY . .

RUN echo "APP_NAME=StockManager" > .env && \
    echo "APP_ENV=local" >> .env && \
    echo "APP_KEY=base64:K1QpQjYoTX6aHYIen7Go9RrBPwp4gxvCIf/NQtZSY5Y=" >> .env && \
    echo "APP_DEBUG=true" >> .env && \
    echo "APP_URL=http://localhost:80" >> .env && \
    echo "DB_CONNECTION=mysql" >> .env && \
    echo "DB_HOST=mysql" >> .env && \
    echo "DB_PORT=3306" >> .env && \
    echo "DB_DATABASE=stock" >> .env && \
    echo "DB_USERNAME=stockAdmin" >> .env && \
    echo "DB_PASSWORD=passwordAdmin" >> .env && \
    echo "SESSION_DRIVER=database" >> .env && \
    echo "CACHE_STORE=database" >> .env && \
    echo "QUEUE_CONNECTION=database" >> .env && \
    echo "LOG_CHANNEL=stack" >> .env && \
    echo "LOG_LEVEL=debug" >> .env

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod 755 ./entrypoint.sh

EXPOSE 80 443

CMD ["./entrypoint.sh"]

