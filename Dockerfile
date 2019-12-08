FROM composer:1.9 as composer
WORKDIR /src
ADD ./composer.* /src/
RUN composer install --prefer-dist -q
RUN find . -type f ! \( -name '*.php' -or -name '*.pem' \) -delete

FROM php:7.2-apache
COPY --from=composer /src/vendor /var/www/vendor
COPY docroot /var/www/html
