# Dockerfile
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev libcurl4-openssl-dev \
    && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql mbstring curl
RUN a2enmod rewrite

WORKDIR /var/www/html
COPY . .
