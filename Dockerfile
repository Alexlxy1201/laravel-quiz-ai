# ---------- Build stage ----------
FROM composer:2 AS builder
WORKDIR /app

# Configure composer timeout and create Laravel project
RUN composer config -g process-timeout 1200 && composer --version
RUN composer create-project laravel/laravel:^10 . --no-dev --prefer-dist --no-scripts

# Copy application files
COPY app/Http/Controllers/SolveController.php /app/app/Http/Controllers/SolveController.php
COPY routes/web.php /app/routes/web.php
COPY resources/views/solve.blade.php /app/resources/views/solve.blade.php
COPY public/css/quiz.css /app/public/css/quiz.css

# Run composer scripts after copying files
RUN composer dump-autoload --optimize

# ---------- Runtime stage ----------
FROM php:8.3-cli
WORKDIR /app

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl zip unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install \
      pdo_mysql zip bcmath mbstring intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy application from builder
COPY --from=builder /app /app

# Set proper permissions for Laravel directories
RUN chown -R www-data:www-data /app \
    && chmod -R 755 /app/storage /app/bootstrap/cache

# Create non-root user for running the application
USER www-data

# Expose port
EXPOSE 8080

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s --retries=3 \
  CMD curl -f http://localhost:${PORT:-8080}/health || exit 1

# Start Laravel with optimized environment setup
CMD bash -c '\
  cp .env.example .env && \
  echo "APP_ENV=production" >> .env && \
  echo "APP_DEBUG=false" >> .env && \
  echo "APP_URL=${APP_URL:-http://localhost:8080}" >> .env && \
  echo "LOG_CHANNEL=stderr" >> .env && \
  echo "LOG_LEVEL=info" >> .env && \
  echo "OPENAI_API_KEY=${OPENAI_API_KEY:-}" >> .env && \
  echo "OPENAI_MODEL=${OPENAI_MODEL:-gpt-4o-mini}" >> .env && \
  echo "OPENAI_BASE_URL=${OPENAI_BASE_URL:-https://api.openai.com/v1}" >> .env && \
  echo "MOCK=${MOCK:-false}" >> .env && \
  echo "DB_CONNECTION=${DB_CONNECTION:-sqlite}" >> .env && \
  echo "DB_DATABASE=/app/storage/database.sqlite" >> .env && \
  echo "CACHE_DRIVER=file" >> .env && \
  echo "SESSION_DRIVER=file" >> .env && \
  echo "QUEUE_CONNECTION=sync" >> .env && \
  if [ -z "${APP_KEY}" ]; then \
    php artisan key:generate --force; \
  else \
    echo "APP_KEY=${APP_KEY}" >> .env; \
  fi && \
  echo "=== Environment Variables ===" && \
  cat .env && \
  echo "===========================" && \
  php artisan config:clear && \
  php artisan cache:clear && \
  php artisan route:clear && \
  php artisan view:clear && \
  php artisan serve --host=0.0.0.0 --port=${PORT:-8080}'