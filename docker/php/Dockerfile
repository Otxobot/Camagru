FROM php:8.2-fpm

# Install PostgreSQL driver, GD for image manipulation
RUN apt-get update && apt-get install -y \
    libpq-dev libpng-dev zip unzip msmtp gettext-base \
    && docker-php-ext-install pdo pdo_pgsql gd

# Install Xdebug and set up logging
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && mkdir -p /var/log \
    && touch /var/log/xdebug.log \
    && chmod 666 /var/log/xdebug.log

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY docker/php/.msmtprc.template /etc/msmtprc.template
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# RUN  chmod 644 /etc/msmtprc && chown www-data:www-data /etc/msmtprc
RUN  touch /var/log/msmtp.log && chmod 666 /var/log/msmtp.log
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini


# Create www-data user and set proper ownership
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

WORKDIR /var/www/html

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]