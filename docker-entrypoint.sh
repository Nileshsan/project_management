#!/bin/bash
set -e

# Create Laravel storage symlinks if they don't exist
if [ ! -d /var/www/public/storage ]; then
    cd /var/www && php artisan storage:link
fi

# Clear and cache routes and config in production
if [ "$APP_ENV" != "local" ]; then
    cd /var/www
    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan route:clear
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Run migrations if DB is configured
if [ -n "$DB_HOST" ]; then
    cd /var/www && php artisan migrate --force
fi

# Start application
exec "$@"
