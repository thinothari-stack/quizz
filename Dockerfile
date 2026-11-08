FROM composer:2 AS composer

FROM php:8.3-apache-bookworm

ENV APP_ENV=prod \
    APP_DEBUG=0

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libicu-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" dom gd intl opcache pdo_pgsql zip \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist

COPY . .
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

RUN composer dump-autoload --classmap-authoritative --no-dev --no-scripts \
    && mkdir -p var/cache var/log public/profil public/type public/reponse public/reponse_bonus \
    && chown -R www-data:www-data var public/profil public/type public/reponse public/reponse_bonus

EXPOSE 80

CMD ["apache2-foreground"]
