#!/bin/bash
set -e

echo "🚀 Starting Laravel initialization..."

# Ensure directories exist and have correct permissions
echo "📁 Setting up directories and permissions..."
mkdir -p /var/www/bootstrap/cache
mkdir -p /var/www/storage/framework/{sessions,views,cache}
mkdir -p /var/www/storage/app/public
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Create storage link if it doesn't exist
if [ ! -L /var/www/public/storage ]; then
    echo "🔗 Creating storage link..."
    cd /var/www && php artisan storage:link --force
fi

# Handle key generation
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    cd /var/www && php artisan key:generate --force
fi

# Clear all caches first
echo "🧹 Clearing application cache..."
cd /var/www
php artisan optimize:clear

# Cache configuration in production
if [ "$APP_ENV" != "local" ]; then
    echo "📦 Caching configuration..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Run migrations if database is configured
if [ -n "$DB_HOST" ]; then
    echo "🔄 Running database migrations..."
    php artisan migrate --force
fi

echo "✅ Laravel initialization complete!"

# Start supervisor (which manages nginx, php-fpm, and workers)
exec "$@"
