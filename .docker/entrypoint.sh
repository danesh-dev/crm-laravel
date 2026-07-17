#!/usr/bin/env sh

set -e

echo "Waiting for database..."

until php artisan migrate --force >/dev/null 2>&1; do
    sleep 2
done

php artisan package:discover --ansi

php artisan optimize

exec "$@"