FROM php:8.5-fpm
RUN apt update && apt install -y git

RUN pecl install inotify && docker-php-ext-enable inotify

COPY --from=composer:2.9 /usr/bin/composer /usr/bin/composer
