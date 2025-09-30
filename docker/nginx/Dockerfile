FROM nginx:latest

RUN usermod -u 1000 www-data && groupmod -g 1000 www-data
RUN chown -R www-data:www-data /var/www/html
RUN chmod 755 /var/www/html

RUN sed -i 's/user  nginx;/user  www-data;/' /etc/nginx/nginx.conf

RUN rm /etc/nginx/conf.d/default.conf
COPY default.conf /etc/nginx/conf.d/default.conf