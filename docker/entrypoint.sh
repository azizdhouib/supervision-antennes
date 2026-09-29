#!/bin/bash
set -e

# Copy .env if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Install composer dependencies if vendor is missing
if [ ! -d "vendor" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations
php artisan migrate --force

exec "$@"
