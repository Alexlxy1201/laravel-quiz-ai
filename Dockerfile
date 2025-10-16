# ---------- Build stage: install PHP deps with Composer ----------
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# ---------- Runtime stage ----------
FROM php:8.3-cli

RUN apt-get update && apt-get install -y --no-install-recommends       git curl zip unzip libzip-dev libicu-dev libonig-dev     && docker-php-ext-install       pdo_mysql       zip       bcmath       mbstring     && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY . .
COPY --from=vendor /app/vendor /app/vendor

RUN cp -n .env.example .env || true
RUN php -r "if (!preg_match('/^APP_KEY=.+/m', file_get_contents('.env'))) { passthru('php artisan key:generate --force'); }"

RUN chmod -R 775 storage bootstrap/cache || true

EXPOSE 8080

CMD php artisan config:cache &&     php artisan route:cache &&     php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
