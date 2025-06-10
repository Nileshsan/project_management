# Stage 1: PHP-FPM and Nginx
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    libonig-dev \
    libxml2-dev \
    libxslt1-dev \
    zip \
    unzip \
    python3-supervisor \
    libcurl4-openssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install pdo_pgsql pgsql pdo_mysql mbstring exif pcntl bcmath gd xsl curl

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application code
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy custom nginx config
COPY nginx.conf /etc/nginx/nginx.conf
RUN mkdir -p /etc/nginx/conf.d && cp /etc/nginx/nginx.conf /etc/nginx/conf.d/default.conf

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 8080 for Render
EXPOSE 8080

# Start Nginx and PHP-FPM via Supervisor
COPY supervisor.conf /etc/supervisor/conf.d/supervisor.conf
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisor.conf"]
