# Usa imagem oficial do PHP com Apache
FROM php:8.2-apache

# Atualiza o sistema e instala dependências
RUN apt-get update && apt-get install -y \
    git unzip zip libssl-dev pkg-config libicu-dev \
    && docker-php-ext-install intl \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite

# Instala Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define a pasta pública como raiz do site
WORKDIR /var/www/html

# Copia ficheiros para o container
COPY public/ /var/www/html/
COPY src/ /var/www/html/src/
COPY vendor/ /var/www/html/vendor/
COPY .env /var/www/html/.env

# Define permissões corretas
RUN chown -R www-data:www-data /var/www/html
