#!/bin/sh

if [ ! -f /var/www/storage/database.sqlite ] && [ "${DB_CONNECTION}" = "sqlite" ]; then
    touch /var/www/storage/database.sqlite && chown application:www-data /var/www/storage/database.sqlite
fi

su-exec application php artisan migrate:retry

supervisord -c /etc/supervisor.d/supervisord.ini