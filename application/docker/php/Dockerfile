# syntax=docker/dockerfile:1.4

# ==============================================================================
# Stage 1: Build Frontend Assets (Vite / Tailwind / Livewire Assets)
# ==============================================================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci --prefer-offline --no-audit

COPY vite.config.js ./
COPY resources/ resources/
COPY public/ public/

RUN npm run build

# ==============================================================================
# Stage 2: PHP 8.2-FPM Production Runtime
# ==============================================================================
FROM php:8.2-fpm-alpine AS production

LABEL maintainer="CED Engineering Team"
LABEL description="Construction Executive Dashboard Production/Staging PHP-FPM Service"

# Set environment
ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=staging \
    APP_DEBUG=false

# Install production system dependencies & build tools
RUN apk update && apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    mariadb-connector-c \
    mariadb-client \
    su-exec \
    tzdata

# Configure and compile PHP core extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip

# Copy Composer binary from official image
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer dependency manifests first for build layer caching
COPY composer.json composer.lock ./

# Install vendor dependencies (no dev, optimized)
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

# Copy application source code
COPY . /var/www/html

# Copy pre-compiled frontend assets from frontend-builder stage
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Finalize composer autoload & dump
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Copy configuration files
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom-php.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Ensure storage and bootstrap directories exist with correct permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy container entrypoint script
COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["php-fpm"]
