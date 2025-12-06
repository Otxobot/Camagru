#!/bin/bash

# Substitute environment variables in msmtprc
envsubst < /etc/msmtprc.template > /etc/msmtprc
chmod 600 /etc/msmtprc
chown www-data:www-data /etc/msmtprc

# Start php-fpm
exec "$@"