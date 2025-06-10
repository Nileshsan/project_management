#!/bin/bash

# Create required directories if they don't exist
mkdir -p bootstrap/cache
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/app/public
mkdir -p storage/logs
mkdir -p storage/debugbar

# Ensure storage and public directories exist
mkdir -p public/storage
mkdir -p storage/app/public/user-uploads

# Set permissions before installation
chmod -R 775 storage bootstrap/cache

# Install dependencies with platform requirements ignored
COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs

# Generate application key if not set
php artisan key:generate --force

# Clear any previous cached data
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

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
