#!/bin/bash

# Install dependencies
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear any previous cached data
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Set up storage link
php artisan storage:link

# Run database migrations
php artisan migrate --force

# Optimize the application
php artisan optimize

# Set permissions
chmod -R 777 storage bootstrap/cache

# Cache configuration and routes for better performance
php artisan config:cache
php artisan route:cache
