# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm-bookworm AS php-base

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PATH="/var/www/html/vendor/bin:${PATH}"

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        git \
        ghostscript \
        libfreetype6-dev \
        libicu-dev \
        libjpeg62-turbo-dev \
        libmagickwand-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
        libzip-dev \
        python3 \
        python3-pip \
        tesseract-ocr \
        tesseract-ocr-fra \
        unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" bcmath gd intl mbstring opcache pcntl pdo_mysql pdo_sqlite zip \
    && pecl install imagick redis \
    && docker-php-ext-enable imagick redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY resources/python/requirements.txt /tmp/python-requirements.txt

RUN python3 -m pip install --no-cache-dir --break-system-packages -r /tmp/python-requirements.txt \
    && rm /tmp/python-requirements.txt

COPY docker/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]
CMD ["php-fpm"]

FROM php-base AS vendor-production

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

FROM node:22-bookworm-slim AS frontend-build

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci

COPY --from=vendor-production /var/www/html/vendor ./vendor
COPY . .
RUN npm run build

FROM php-base AS app-production

COPY --chown=www-data:www-data --from=vendor-production /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=frontend-build /var/www/html/public/build ./public/build
COPY docker/php/production.ini /usr/local/etc/php/conf.d/99-irma.ini

RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -sfn ../storage/app/public public/storage \
    && chown -R www-data:www-data storage bootstrap/cache public

USER www-data

FROM php-base AS app-development

COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --optimize-autoloader

COPY . .
COPY docker/php/development.ini /usr/local/etc/php/conf.d/99-irma.ini

RUN mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && ln -sfn ../storage/app/public public/storage

FROM nginx:1.27-alpine AS nginx-production

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=app-production /var/www/html/public /var/www/html/public

WORKDIR /var/www/html
