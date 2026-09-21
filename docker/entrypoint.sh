#!/bin/sh
set -eu

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    database_path="${DB_DATABASE:-/var/www/html/storage/app/data/database.sqlite}"
    mkdir -p "$(dirname "$database_path")"
    touch "$database_path"
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

php artisan migrate --force
php artisan db:seed --force
php artisan optimize:clear

exec "$@"
