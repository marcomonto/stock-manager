#!/bin/bash
set -e

echo "Checking database connection..."
for i in {1..10}; do
    echo "Database connection attempt $i/10"
    if php artisan db:show > /dev/null 2>&1; then
        echo "✅ Database connected!"
        break
    fi

    if [ $i -eq 10 ]; then
        echo "❌ Failed to connect to database after 10 attempts"
        echo "Database Host: ${DB_HOST:-not_set}"
        echo "Database Name: ${DB_DATABASE:-not_set}"
        exit 1
    fi

    echo "Database not ready, waiting 3 seconds..."
    sleep 3
done



chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage


echo "Optimizing autoload"
php artisan package:discover --ansi

echo "Database ready! Running migrations and seeders..."
php artisan migrate:fresh --seed

echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting supervisord..."
exec /usr/bin/supervisord -n
