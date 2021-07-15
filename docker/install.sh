# How to use
# docker-compose exec app sh
# sh docker/install.sh

ln -snf ../storage/app/public/ public/storage && \
composer install --prefer-dist --no-interaction && \
php artisan install
