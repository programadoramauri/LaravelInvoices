FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
  git curl zip unzip libpq-dev libonig-dev libzip-dev libxml2-dev \
  && docker-php-ext-install pdo pdo_mysql zip xml

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
