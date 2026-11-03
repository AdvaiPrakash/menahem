# ==============================================================================
# Stage 1: Build Frontend Assets (Vite + Tailwind CSS v4)
# ==============================================================================
FROM node:22-alpine AS frontend-builder

WORKDIR /app

# Cache dependency installations
COPY package.json package-lock.json ./
RUN npm install


# Copy files necessary for Vite compilation and Tailwind class discovery
COPY vite.config.js ./
COPY resources/ resources/
COPY app/ app/
COPY routes/ routes/
COPY public/ public/

# Compile frontend production assets (outputs to public/build)
RUN npm run build

# ==============================================================================
# Stage 2: Install Composer Dependencies
# ==============================================================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ==============================================================================
# Stage 3: Production Application Image (PHP 8.3 FPM + Nginx + Supervisor)
# ==============================================================================
FROM php:8.3-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Set production environment defaults
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# Install system dependencies (Nginx, Supervisor, curl, bash)
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash \
    sed \
    libcap

# Install PHP extensions using the official extension installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    bcmath \
    curl \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    zip

# Copy Composer binary from composer image
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Configure Nginx & run as www-data
RUN sed -i 's/user nginx;/user www-data;/' /etc/nginx/nginx.conf \
    && chown -R www-data:www-data /var/lib/nginx /var/log/nginx

# Copy service configurations
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-laravel.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Copy application source code
COPY . /var/www/html

# Copy vendor dependencies from composer builder
COPY --from=composer-builder /app/vendor /var/www/html/vendor

# Copy compiled frontend assets from node builder
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Complete composer autoload dump with all application classes present
RUN composer dump-autoload --optimize --no-dev

# Setup entrypoint script (normalize Windows CRLF to LF and ensure execution permissions)
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh && chmod +x /usr/local/bin/entrypoint.sh

# Ensure correct permissions for Laravel writable directories
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose HTTP port
EXPOSE 80

# Health check to ensure Nginx & PHP-FPM are responding
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
