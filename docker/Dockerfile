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
  nginx

# Copy nginx config file
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copy supervisord config file
COPY docker/supervisord.conf /etc/supervisord.conf

# Install extensions
RUN docker-php-ext-configure intl && \
    docker-php-ext-install zip sodium intl && \
    docker-php-ext-enable zip sodium

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN addgroup www && \
    adduser -D -G www www && \
    adduser www-data www && \
    adduser nginx www

# Copy existing application directory and set www as owner
COPY --chown=www:www . /var/www/html 

# Set .env file based on example
COPY --chown=www:www ./.env.example /var/www/html/.env

# Set write permission on storage folder
RUN chmod -R ug+w /var/www/html/storage

# Change current user to www
USER www

# Install composer packages and generate app key
RUN composer install --optimize-autoloader --no-interaction
RUN php artisan key:generate

# Change current user back to root
USER root

# Define supervisor as entrypoint
ENTRYPOINT ["supervisord", "-c", "/etc/supervisord.conf"]

EXPOSE 80