FROM php:8.5-fpm-alpine

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
        git \
        unzip \
        shadow \
        libzip-dev \
        icu-dev \
        oniguruma-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mysqli \
        mbstring \
        intl \
        zip

RUN groupmod -o -g "${GID}" www-data \
    && usermod  -o -u "${UID}" -g "${GID}" www-data \
    && sed -i "s|^user = .*|user = www-data|; s|^group = .*|group = www-data|; s|^listen.owner = .*|listen.owner = www-data|; s|^listen.group = .*|listen.group = www-data|" /usr/local/etc/php-fpm.d/www.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

USER www-data
