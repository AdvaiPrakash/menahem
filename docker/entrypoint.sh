#!/bin/sh
set -e

# Support dynamic PORT environment variable (for Render, Railway, Fly.io, Cloud Run, Heroku)
PORT="${PORT:-80}"
if [ -f /etc/nginx/http.d/default.conf ]; then
    sed -i "s/listen [0-9]\+;/listen ${PORT};/g" /etc/nginx/http.d/default.conf 2>/dev/null || true
    sed -i "s/listen \[::\]:[0-9]\+;/listen \[::\]:${PORT};/g" /etc/nginx/http.d/default.conf 2>/dev/null || true
fi

# Ensure storage, cache, and database directories exist
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database

# Ensure .env file exists
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.example ]; then
        cp /var/www/html/.env.example /var/www/html/.env
    else
        touch /var/www/html/.env
    fi
fi

# Ensure APP_KEY is present; generate one if missing
if [ -z "$APP_KEY" ]; then
    if ! grep -q "^APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
        echo "Generating application encryption key..."
        php artisan key:generate --force --no-interaction || true
    fi
fi

# Ensure SQLite database exists and has open permissions if DB_CONNECTION is sqlite
DB_CONN="${DB_CONNECTION:-sqlite}"
if [ "$DB_CONN" = "sqlite" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
    fi
    chmod 777 /var/www/html/database
    chmod 666 /var/www/html/database/database.sqlite
fi

# Clear any stale cached configuration from previous builds
php artisan optimize:clear --no-interaction || true

# Always ensure database migrations run so sessions, cache, and users tables exist
echo "Running database migrations..."
php artisan migrate --force --no-interaction || true

# Ensure storage symlink exists
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link --no-interaction || true
fi

# In production, cache routes and views
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "Caching routes and views for production..."
    php artisan route:cache --no-interaction || true
    php artisan view:cache --no-interaction || true
fi

# Set proper permissions for Laravel writable directories (executed AFTER all artisan commands)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
if [ "$DB_CONN" = "sqlite" ]; then
    chmod 777 /var/www/html/database
    chmod 666 /var/www/html/database/database.sqlite
fi

exec "$@"
