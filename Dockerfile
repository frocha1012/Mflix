FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libssl-dev pkg-config libicu-dev \
    && docker-php-ext-install intl \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copia os diretórios do projeto
COPY public/ /var/www/html/
COPY src/ /var/www/html/src/
COPY vendor/ /var/www/html/vendor/
COPY .env /var/www/html/.env
