#!/bin/sh

su-exec application php artisan migrate:retry

supervisord -c /etc/supervisor.d/supervisord.ini