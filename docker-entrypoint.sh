#!/bin/bash
set -e

echo "🚀 Starting Laravel initialization..."

# Create .env file
echo "📝 Creating .env file..."
cp /var/www/nileshenv /var/www/.env || echo "Failed to copy nileshenv to .env"

# Replace environment variables in .env
if [ -n "$APP_KEY" ]; then
    sed -i "s#APP_KEY=.*#APP_KEY=$APP_KEY#g" /var/www/.env
fi
    if [ -n "$APP_URL" ]; then
        sed -i "s#APP_URL=.*#APP_URL=$APP_URL#g" /var/www/.env
    fi
    if [ -n "$DB_HOST" ]; then
        sed -i "s#DB_HOST=.*#DB_HOST=$DB_HOST#g" /var/www/.env
    fi
    if [ -n "$DB_DATABASE" ]; then
        sed -i "s#DB_DATABASE=.*#DB_DATABASE=$DB_DATABASE#g" /var/www/.env
    fi
    if [ -n "$DB_USERNAME" ]; then
        sed -i "s#DB_USERNAME=.*#DB_USERNAME=$DB_USERNAME#g" /var/www/.env
    fi
    if [ -n "$DB_PASSWORD" ]; then
        sed -i "s#DB_PASSWORD=.*#DB_PASSWORD=$DB_PASSWORD#g" /var/www/.env
    fi
fi

# Ensure directories exist and have correct permissions
echo "📁 Setting up directories and permissions..."
mkdir -p /var/www/bootstrap/cache
mkdir -p /var/www/storage/framework/{sessions,views,cache}
mkdir -p /var/www/storage/app/public
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Remove existing storage link if it exists
if [ -L /var/www/public/storage ]; then
    rm /var/www/public/storage
fi

# Install composer dependencies
echo "📦 Installing dependencies..."
cd /var/www
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Clear and cache routes, config
echo "🧹 Optimizing application..."
php artisan optimize:clear
php artisan optimize

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# Create storage link
echo "🔗 Creating storage link..."
if [ -L /var/www/public/storage ]; then
    rm /var/www/public/storage
fi
php artisan storage:link --force
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    cd /var/www && php artisan key:generate --force
fi

# Cache configuration
echo "⚡ Optimizing Laravel..."
cd /var/www
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

# Check if composer install is needed
if [ ! -d "/var/www/vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
fi

# Run database migrations if DB is configured
if [ -n "$DB_HOST" ] && [ -n "$DB_DATABASE" ] && [ -n "$DB_USERNAME" ]; then
    echo "🔄 Running database migrations..."
    php artisan migrate --force
fi

echo "✨ Laravel initialization complete!"

# Start PHP-FPM and Nginx through supervisord
exec "$@"
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

# Configure Nginx with correct port
echo "🛠️ Configuring Nginx..."
sed -i "s/\${PORT}/$PORT/g" /etc/nginx/conf.d/default.conf
nginx -t

# Start supervisor (which manages nginx, php-fpm, and workers)
exec "$@"
