
FROM php:7.4.8-fpm

RUN apt-get update \
    && apt-get upgrade -y \
    && docker-php-ext-install ctype \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql

RUN pecl install xdebug-2.9.1 \
    && docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /app