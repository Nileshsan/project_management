#!/bin/bash

# Create required directories if they don't exist
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p storage/debugbar
mkdir -p public/storage
mkdir -p storage/app/public/user-uploads

# Set proper permissions
chmod -R 775 bootstrap/cache
chmod -R 775 storage
chown -R www-data:www-data storage bootstrap/cache

# Create storage link if it doesn't exist
if [ ! -L "public/storage" ]; then
    php artisan storage:link
fi

# Set permissions before installation
chmod -R 775 storage bootstrap/cache

# Install dependencies with platform requirements ignored
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Generate application key if not set
php artisan key:generate --force

# Clear any previous cached data
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set up storage link
php artisan storage:link --force

# Run database migrations if database is configured
if [ -n "$DB_HOST" ]; then
    php artisan migrate --force
fi

# Optimize the application
php artisan optimize

# Set final permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Cache configuration and routes for better performance
php artisan config:cache
php artisan route:cache
