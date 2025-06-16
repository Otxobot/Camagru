FROM php:8.2-fpm

# Install PostgreSQL driver, GD for image manipulation
RUN apt-get update && apt-get install -y \
    libpq-dev libpng-dev \
    && docker-php-ext-install pdo pdo_pgsql gd

WORKDIR /var/www/html
