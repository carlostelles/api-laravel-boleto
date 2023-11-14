FROM php:8.1-fpm-alpine

WORKDIR /var/www/html

# Install packages
RUN apk add --no-cache \
  $PHPIZE_DEPS \
  openssl \
  bash \
  unzip \
  vim \
  libzip-dev \
  zlib-dev \
  libsodium-dev \
  icu-dev \
  git

# Install extensions
RUN docker-php-ext-configure intl && \
    docker-php-ext-install zip sodium intl && \
    docker-php-ext-enable zip sodium

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Add user for laravel application
RUN addgroup www && \
    adduser -D -G www www

# Copy existing application directory and set www as owner
COPY --chown=www:www . /var/www/html 

# Set .env file based on example
COPY --chown=www:www ./.env.example /var/www/html/.env

# Change current user to www
USER www

# Install composer packages and generate app key
RUN composer install --optimize-autoloader --no-interaction
RUN php artisan key:generate

EXPOSE 9000

ENTRYPOINT ["php-fpm"]