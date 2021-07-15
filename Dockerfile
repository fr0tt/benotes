FROM webdevops/php-nginx:7.4-alpine

LABEL mantainer="github.com/fr0tt"
LABEL description="Benotes"

ENV user application

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


RUN apk --no-cache update && apk --no-cache add \  
    git \
    curl \
    curl-dev \
    libpng-dev \
    libxml2-dev \
    libmcrypt-dev \
    libpq \
    postgresql-dev \
    zip \
    unzip \
    libzip-dev \
    libmcrypt-dev \
    openssl

RUN docker-php-ext-install \ 
    pdo \
    pdo_mysql \
    mysqli \ 
    pgsql \
    pdo_pgsql \
    opcache \
    exif \
    tokenizer \
    pcntl \
    bcmath \
    gd \
    curl \
    dom \
    xml


COPY ./docker/nginx/default.conf /opt/docker/etc/nginx/main.conf


USER $user

# will be overriden by the bind mount - if used
COPY . /var/www/

WORKDIR /var/www


ARG USE_COMPOSER
RUN if [ "$USE_COMPOSER" = "true" ] ; \
    then \
        composer install --prefer-dist --no-interaction ; \
    fi


ARG RUN_MIGRATION
RUN if [ "$RUN_MIGRATION" = "true" ] ; \
    then \
    php artisan migrate --force ; \
    fi


USER root


ARG INSTALL_NODE
RUN if [ "$INSTALL_NODE" = "true" ] ; \
    then \
        apk --no-cache add nodejs npm ; \
    fi

# will be overriden by the bind mount - if used
RUN ln -snf ../storage/app/public/ public/storage

USER $user





