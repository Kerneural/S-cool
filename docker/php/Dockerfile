FROM php:8.2-fpm-alpine


# cai dat cac thu vien he thong can thiet

RUN apk add --no-cache \
    curl \
    git \
    libzip-dev \
    libpng-dev \
    icu-dev \
    oniguruma-dev \
    mysql-client

# Cai dat cac extensioni php cho Laravel & mysql


RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    bcmath \
    intl \
    zip \
    gd \
    opcache

# Require the fixed Expat release for CVE-2026-117610 and CVE-2026-118370.
RUN apk upgrade --no-cache && apk add --no-cache 'libexpat>=2.10.0-r0'

    # Cài đặt Composer chính thức từ Docker Hub
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/security.ini /usr/local/etc/php/conf.d/zz-scool-security.ini
COPY docker/php/verify-security.php /usr/local/bin/scool-verify-php-security.php
RUN php /usr/local/bin/scool-verify-php-security.php
WORKDIR /var/www/html

COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini
