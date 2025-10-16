# ---------- Build stage: install PHP deps with Composer ----------
FROM composer:2 AS vendor
WORKDIR /app

# 只拷贝 composer 文件，最大化缓存命中
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# ---------- Runtime stage ----------
FROM php:8.3-cli

# 安装系统依赖 + PHP 扩展
RUN apt-get update && apt-get install -y --no-install-recommends \
      git curl zip unzip libzip-dev libicu-dev libonig-dev \
    && docker-php-ext-install \
      pdo_mysql \
      zip \
      bcmath \
      mbstring \
    && rm -rf /var/lib/apt/lists/*

# 工作目录
WORKDIR /app

# 复制应用代码
COPY . .

# 复制 vendor（来自 build 阶段）
COPY --from=vendor /app/vendor /app/vendor

# 复制 .env.example → .env（若不存在）
RUN cp -n .env.example .env || true

# 生成 APP_KEY（如未生成）
RUN php -r "if (!preg_match('/^APP_KEY=.+/m', file_get_contents('.env'))) { passthru('php artisan key:generate --force'); }"

# 权限
RUN chmod -R 775 storage bootstrap/cache || true

# 暴露端口
EXPOSE 8080

# 启动命令
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
```

### 3. 创建 `.dockerignore`
```
.git
node_modules
vendor
storage/logs/*
storage/framework/cache/*
storage/framework/sessions/*
.env
.env.backup
*.log