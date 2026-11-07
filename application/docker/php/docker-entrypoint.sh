#!/bin/bash
set -e

echo "[CED-INIT] Starting Construction Executive Dashboard container bootstrap..."

# Ensure storage permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

# Wait for MariaDB/MySQL if host is set
if [ -n "$DB_HOST" ] && [ "$DB_CONNECTION" != "sqlite" ]; then
    echo "[CED-INIT] Waiting for database at $DB_HOST:${DB_PORT:-3306}..."
    max_tries=30
    count=0
    until nc -z "$DB_HOST" "${DB_PORT:-3306}" || [ $count -ge $max_tries ]; do
        sleep 1
        count=$((count + 1))
    done

    if [ $count -ge $max_tries ]; then
        echo "[CED-INIT] ERROR: Database connection timed out after $max_tries seconds."
        exit 1
    fi
    echo "[CED-INIT] Database is reachable."
fi

# Link storage
if [ ! -L /var/www/html/public/storage ]; then
    echo "[CED-INIT] Linking public storage directory..."
    php artisan storage:link --force || true
fi

# Automatic Migration on container startup if enabled
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "[CED-INIT] Running database migrations..."
    php artisan migrate --force --no-interaction
fi

# Optimize Laravel cache in staging / production
if [ "$APP_ENV" = "staging" ] || [ "$APP_ENV" = "production" ]; then
    echo "[CED-INIT] Optimizing configuration, route, and view caches..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "[CED-INIT] Initialization complete. Executing command: $@"
exec "$@"
