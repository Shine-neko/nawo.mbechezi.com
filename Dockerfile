FROM php:8.0-fpm
RUN apt update && apt install -y git

RUN pecl install inotify && docker-php-ext-enable inotify

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer
