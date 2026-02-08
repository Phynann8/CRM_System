FROM php:7.1-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql mbstring \
    && a2enmod rewrite

WORKDIR /var/www/html
