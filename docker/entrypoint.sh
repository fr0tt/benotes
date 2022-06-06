#!/bin/sh

if [ ! -f /var/www/storage/database.sqlite ] && [ "${DB_CONNECTION}" = "sqlite" ]; then
    touch /var/www/storage/database.sqlite && chown 1000:1000 /var/www/storage/database.sqlite
fi

if [ "${RUN_MIGRATIONS}" = "true" ]; then
    php artisan migrate --force
fi