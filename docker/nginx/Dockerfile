FROM nginx:latest

# Set nginx user to match host user UID
RUN usermod -u 1000 www-data && groupmod -g 1000 www-data

# Modify nginx.conf to run worker processes as www-data
RUN sed -i 's/user  nginx;/user  www-data;/' /etc/nginx/nginx.conf

# Remove the default config
RUN rm /etc/nginx/conf.d/default.conf

# Copy your custom config
COPY default.conf /etc/nginx/conf.d/default.conf