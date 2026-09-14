# syntax=docker/dockerfile:1.7

############################################
# Composer dependencies
############################################
FROM composer:2.8 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install \
        --no-dev \
        --prefer-dist \
        --no-interaction \
        --no-scripts \
        --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize


############################################
# Frontend assets
############################################
FROM node:22-alpine AS frontend

WORKDIR /app

RUN npm install -g npm@11.6.2

COPY package.json package-lock.json ./

RUN --mount=type=cache,target=/root/.npm \
    npm ci \
        --no-audit \
        --no-fund

COPY . .

# Flux CSS is loaded from Laravel vendor/
COPY --from=vendor /app/vendor ./vendor

RUN npm run build


############################################
# Production image
############################################
FROM dunglas/frankenphp:1.6-php8.4-alpine

RUN install-php-extensions \
    pdo_pgsql \
    pcntl \
    bcmath \
    intl \
    zip

WORKDIR /app

COPY --from=vendor /app /app

COPY --from=frontend /app/public/build /app/public/build

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data \
        storage \
        bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]