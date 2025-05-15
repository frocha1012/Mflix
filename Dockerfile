FROM php:8.2-apache

# Instalar dependências + certificados raiz
RUN apt-get update && apt-get install -y \
    git unzip zip libssl-dev pkg-config libicu-dev ca-certificates \
    && docker-php-ext-install intl \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite

# Instalar o Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html
