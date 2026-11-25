# Dockerfile for LandingFlow AI Production Container (Optional Docker Deployment)
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    sqlite-dev \
    libpng-dev \
    libwebp-dev \
    libjpeg-turbo-dev \
    curl \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo pdo_pgsql pdo_sqlite gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/landingflow-ai/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY backend/ ./
RUN composer dump-autoload --optimize

EXPOSE 9000
CMD ["php-fpm"]
