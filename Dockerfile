FROM php:8.0
RUN apt update && apt install -y git

COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer
