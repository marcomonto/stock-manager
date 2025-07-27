#!/bin/bash
set -e

echo "Optimizing autoload"
php artisan package:discover --ansi

echo "Database ready! Running migrations and seeders..."
php artisan migrate:fresh --seed

echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

echo "Starting supervisord..."
exec /usr/bin/supervisord -n
