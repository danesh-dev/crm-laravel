# ============================================================
# Composer dependencies
# ============================================================
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-interaction \
    --no-scripts \
    --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize


# ============================================================
# Frontend build
# ============================================================
FROM node:22-alpine AS frontend

WORKDIR /app

COPY package*.json ./

# If your package-lock.json is fixed, replace with:
# RUN npm ci
RUN npm install

COPY . .

# Flux CSS lives inside vendor/
COPY --from=vendor /app/vendor ./vendor

RUN npm run build


# ============================================================
# Runtime
# ============================================================
FROM dunglas/frankenphp:1.6-php8.4-alpine

RUN install-php-extensions \
    pdo_pgsql \
    pgsql \
    bcmath \
    exif \
    intl \
    pcntl \
    opcache \
    zip

WORKDIR /app

# Application source
COPY . .

# Composer dependencies
COPY --from=vendor /app/vendor ./vendor

# Built frontend assets
COPY --from=frontend /app/public/build ./public/build

# Storage
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/views \
    storage/framework/sessions \
    storage/logs \
    bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache

# Laravel package discovery
RUN php artisan package:discover --ansi

USER www-data

EXPOSE 80

CMD ["php","artisan","octane:frankenphp","--host=0.0.0.0","--port=80"]