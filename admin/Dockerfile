# Production: composer + frontend build in image.
# MySQL/Redis: set DB_HOST, REDIS_HOST via environment.

# Stage 1: build frontend (Vite)
FROM node:alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN if [ -f package-lock.json ]; then npm ci; else npm install; fi
COPY . .
RUN npm run build

# Stage 2: PHP app
FROM php:8.5-cli-alpine
RUN apk add --no-cache git unzip libzip-dev libpng-dev libxml2-dev oniguruma-dev
RUN docker-php-ext-install pdo_mysql zip gd xml mbstring
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY composer.json composer.lock* ./
# --no-scripts: avoid post-autoload-dump (artisan package:discover) until app is copied
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

COPY . .
# Laravel package discovery (needs .env to bootstrap)
RUN cp .env.example .env && php artisan package:discover --ansi

# Static assets into public (flat structure only: public/assets/ and public/assets/admin/)
# Do NOT copy into public/assets/admin/assets/ or any nested admin/assets path.
RUN mkdir -p public/assets/admin/css public/assets/admin/js public/assets/admin/img public/uploads bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && cp -r assets/fonts assets/js assets/bootstrap public/assets/ \
    && cp -r assets/admin/css/* public/assets/admin/css/ \
    && cp -r assets/admin/js public/assets/admin/ \
    && (test -d assets/admin/img && cp -r assets/admin/img public/assets/admin/ || true) \
    && chmod -R 775 bootstrap/cache storage public/uploads

# Vite-built admin.css only (do not overwrite whole public/assets/admin/css)
COPY --from=frontend /app/public/assets/admin/css/admin.css ./public/assets/admin/css/

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
