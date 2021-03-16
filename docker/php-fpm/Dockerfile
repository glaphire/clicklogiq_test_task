
FROM php:7.4.11-fpm

RUN apt-get update \
    && apt-get upgrade -y \
    && docker-php-ext-install ctype \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && docker-php-ext-enable pdo_mysql

#-e: It is used to exit immediately if a command exits with a non-zero status.
#-u: It is used to treat unset variables as an error when substituting.
#-x: It is used to print commands and their arguments in a sequential way (as they are executed).
RUN set -eux; apt-get update; apt-get install -y libzip-dev zlib1g-dev; docker-php-ext-install zip

RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get install -y git

RUN pecl install xdebug-2.9.3 \
    && docker-php-ext-enable xdebug

COPY php.ini /usr/local/etc/php/conf.d/php.ini

WORKDIR /app