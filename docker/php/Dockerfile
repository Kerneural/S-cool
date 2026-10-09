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

    # Cài đặt Composer chính thức từ Docker Hub
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/security.ini /usr/local/etc/php/conf.d/zz-scool-security.ini
WORKDIR /var/www/html
