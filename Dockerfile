FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev \
    librabbitmq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install \
    pdo_pgsql \
    bcmath \
    zip \
    sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --optimize-autoloader

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
