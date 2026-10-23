#!/bin/sh
set -e

echo "Starting BrewPOS Container Setup..."

# Ensure storage and bootstrap directories exist with write permissions
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/bootstrap/cache \
         /tmp/opcache

chmod -R 777 /var/www/storage /var/www/bootstrap/cache /tmp/opcache

# Wait for MySQL to be ready
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection ($DB_HOST:${DB_PORT:-3306})..."
    until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . (getenv('DB_PORT') ?: '3306') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { exit(1); }"; do
        echo "Database is still initializing... sleeping 2s"
        sleep 2
    done
    echo "Database is connected and ready!"
fi

# Run database migrations
echo "Executing database migrations..."
php artisan migrate --force

# Seed database if necessary (idempotent)
echo "Executing database seeders..."
php artisan db:seed --force

echo "BrewPOS initialization complete! Launching PHP-FPM..."
exec php-fpm
