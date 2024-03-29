FROM php:8.1-fpm-alpine3.18

WORKDIR /var/www/html

# Install packages
RUN apk add --no-cache \
  $PHPIZE_DEPS \
  openssl \
  bash \
  unzip \
  nano \
  libzip-dev \
  zlib-dev \
  libsodium-dev \
  icu-dev \
  git \
  supervisor \
  nginx \ 
  curl-dev \
  openssl-dev \
  php81-pecl-mongodb

# Copy nginx config file
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy supervisord config file
COPY docker/supervisord.conf /etc/supervisord.conf

# Copy php.ini
COPY docker/php.ini /usr/local/etc/php/php.ini

# Copy ini for mongodb driver
COPY docker/mongodb.ini /usr/local/etc/php/conf.d/mongodb.ini

# Copy generate certficate script
COPY docker/generate_cert /usr/local/bin/

#  Make the generate certificate script executable
RUN chmod +x /usr/local/bin/generate_cert
RUN mkdir /certs /etc/nginx/ssl
RUN generate_cert

# Install and configure extensions
RUN docker-php-ext-configure intl && \
    docker-php-ext-install zip sodium intl && \
    docker-php-ext-enable zip sodium && \
    pecl install mongodb && \
    pecl config-set php_ini /usr/local/etc/php/php.ini

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN addgroup app && \
    adduser -D -G app app && \
    adduser www-data app && \
    adduser nginx app

# Copy existing application directory and set www as owner
COPY --chown=app:app . /var/www/html 

# Set .env file based on example
COPY --chown=app:app ./.env.example /var/www/html/.env

# Change current user to www
USER app

# Install composer packages and generate app key
RUN composer install --optimize-autoloader --no-interaction
RUN php artisan key:generate

# Change current user back to root
USER root

# Define supervisor as entrypoint
ENTRYPOINT ["supervisord", "-c", "/etc/supervisord.conf"]

EXPOSE 80