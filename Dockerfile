FROM php:8.2-fpm

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y --no-install-recommends \
    bash curl unzip zip \
    libpng-dev libjpeg-dev libwebp-dev libxpm-dev \
    libicu-dev libcurl4-openssl-dev \
    libzip-dev libonig-dev \
  && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-webp --with-xpm \
  && docker-php-ext-install -j"$(nproc)" \
    pdo_mysql mbstring exif pcntl intl curl zip gd opcache \
  && pecl install redis \
  && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
  && chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R ug+rw storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
