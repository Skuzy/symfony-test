FROM php:7.4-fpm
RUN apt-get update \
 && apt-get install -y git zlib1g-dev zip unzip \
 && docker-php-ext-install pdo pdo_mysql \
 && curl -sS https://getcomposer.org/installer \
  | php -- --install-dir=/usr/local/bin --filename=composer

COPY ./ /app
WORKDIR /app
