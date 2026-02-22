FROM php:8.5-cli-alpine
RUN apk add --no-cache git unzip libzip-dev libpng-dev libxml2-dev oniguruma-dev nodejs npm
RUN docker-php-ext-install pdo_mysql zip gd xml mbstring
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
WORKDIR /app
COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction
COPY . .
RUN cp -r assets/fonts assets/js assets/bootstrap public/assets/ \
	&& cp assets/admin/css/*.css public/assets/admin/css/ 2>/dev/null || true \
	&& cp -r assets/admin/js public/assets/admin/ 2>/dev/null || true \
	&& cp -r assets/admin/img public/assets/admin/ 2>/dev/null || true
RUN composer dump-autoload --optimize
RUN chmod -R 775 storage bootstrap/cache 2>/dev/null || true
EXPOSE 8080
CMD ["sh", "-c", "mkdir -p public/assets/admin/css public/assets/admin/js public/assets/admin/img && cp -r assets/fonts assets/js assets/bootstrap public/assets/ 2>/dev/null || true && cp assets/admin/css/*.css public/assets/admin/css/ 2>/dev/null || true && cp -r assets/admin/js public/assets/admin/ 2>/dev/null || true && cp -r assets/admin/img public/assets/admin/ 2>/dev/null || true && if [ ! -f .env ]; then cp .env.example .env && php artisan key:generate --no-interaction --force; fi && sed -i \"s|^DB_HOST=.*|DB_HOST=${DB_HOST:-127.0.0.1}|\" .env && sed -i \"s|^DB_USERNAME=.*|DB_USERNAME=${DB_USERNAME:-dansday_main}|\" .env && sed -i \"s|^DB_PASSWORD=.*|DB_PASSWORD=${DB_PASSWORD:-password}|\" .env && sed -i \"s|^APP_URL=.*|APP_URL=${APP_URL:-http://localhost:8080}|\" .env && if [ ! -f vendor/autoload.php ]; then composer install --no-interaction; fi && php artisan migrate --force --no-interaction && exec php artisan serve --host=0.0.0.0 --port=8080"]
