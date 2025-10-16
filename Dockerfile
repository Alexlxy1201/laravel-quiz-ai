# ---------- Build stage: create a fresh Laravel 10 project ----------
FROM composer:2 AS builder
WORKDIR /app

# Speed up composer operations
RUN composer config -g process-timeout 1200 && composer --version

# Create Laravel 10 skeleton (no dev deps)
RUN composer create-project laravel/laravel:^10 . --no-dev --prefer-dist

# Overlay our student-only app files
COPY app/Http/Controllers/SolveController.php /app/app/Http/Controllers/SolveController.php
COPY routes/web.php /app/routes/web.php
COPY resources/views/solve.blade.php /app/resources/views/solve.blade.php
COPY public/css/quiz.css /app/public/css/quiz.css

# ---------- Runtime stage ----------
FROM php:8.3-cli

# System deps + PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl zip unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install \
      pdo_mysql \
      zip \
      bcmath \
      mbstring \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copy the built Laravel app from builder
COPY --from=builder /app /app

# Ensure .env exists (from Laravel's .env.example)
RUN cp -n .env.example .env || true

# Generate APP_KEY if missing
RUN php -r "if (!preg_match('/^APP_KEY=.+/m', file_get_contents('.env'))) { passthru('php artisan key:generate --force'); }"

# Permissions
RUN chmod -R 775 storage bootstrap/cache || true

EXPOSE 8080

CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
