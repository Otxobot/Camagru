FROM php:8.2-fpm

# Install PostgreSQL driver, GD for image manipulation
RUN apt-get update && apt-get install -y \
    libpq-dev libpng-dev zip unzip ssmtp \
    && docker-php-ext-install pdo pdo_pgsql gd

# Install Xdebug and set up logging
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && mkdir -p /var/log \
    && touch /var/log/xdebug.log \
    && chmod 666 /var/log/xdebug.log

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN echo "sendmail_path = /usr/sbin/ssmtp -t" >> /usr/local/etc/php/php.ini

# Create www-data user and set proper ownership
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

WORKDIR /var/www/html