# How to use
# docker-compose exec app sh
# sh docker/update.sh

composer install --prefer-dist --no-interaction && \
php artisan migrate
