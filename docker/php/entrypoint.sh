#!/bin/bash

# Substitute environment variables in msmtprc
envsubst < /etc/msmtprc.template > /etc/msmtprc
chmod 600 /etc/msmtprc
chown www-data:www-data /etc/msmtprc

mkdir -p /var/www/html/public/uploads
chown -R www-data:www-data /var/www/html/public/uploads
chmod 755 /var/www/html/public/uploads

# Start php-fpm
exec "$@"