FROM composer:1.9 as composer
WORKDIR /src
ADD ./composer.* /src/
RUN composer install --prefer-dist -q
RUN find . -type f ! \( -name '*.php' -or -name '*.pem' \) -delete

FROM php:7.2-apache
RUN apt-get update && apt-get install -y libpq-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql  && \
    docker-php-ext-install pdo pdo_pgsql pgsql
COPY --from=composer /src/vendor /var/www/vendor
COPY docroot /var/www/html
