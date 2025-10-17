# ---------- Build stage ----------
FROM composer:2 AS builder
WORKDIR /app

RUN composer config -g process-timeout 1200 && composer --version
RUN composer create-project laravel/laravel:^10 . --no-dev --prefer-dist

COPY app/Http/Controllers/SolveController.php /app/app/Http/Controllers/SolveController.php
COPY routes/web.php /app/routes/web.php
COPY resources/views/solve.blade.php /app/resources/views/solve.blade.php
COPY public/css/quiz.css /app/public/css/quiz.css

# ---------- Runtime stage ----------
FROM php:8.3-cli
WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl zip unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install \
      pdo_mysql zip bcmath mbstring \
    && rm -rf /var/lib/apt/lists/*

COPY --from=builder /app /app

# 保证 storage 权限
RUN chmod -R 775 storage bootstrap/cache || true

# 暴露端口
EXPOSE 8080

# ✅ 启动时动态写入环境变量并启动 Laravel
CMD bash -c '\
  cp -n .env.example .env; \
  echo "\n# Injected from Railway runtime" >> .env; \
  echo "APP_ENV=production" >> .env; \
  echo "APP_KEY=${APP_KEY}" >> .env; \
  echo "APP_URL=${APP_URL}" >> .env; \
  echo "OPENAI_API_KEY=${OPENAI_API_KEY}" >> .env; \
  echo "OPENAI_MODEL=${OPENAI_MODEL}" >> .env; \
  echo "OPENAI_BASE_URL=${OPENAI_BASE_URL}" >> .env; \
  echo "MOCK=${MOCK}" >> .env; \
  php artisan config:clear && \
  php artisan route:clear && \
  php artisan cache:clear && \
  php artisan serve --host=0.0.0.0 --port=${PORT:-8080}'
