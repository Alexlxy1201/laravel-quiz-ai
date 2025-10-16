FROM php:8.3-cli

# 安装系统依赖
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libzip-dev \
    && docker-php-ext-install \
    pdo_mysql \
    zip \
    && rm -rf /var/lib/apt/lists/*

# 安装 Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 设置工作目录
WORKDIR /app

# 复制 composer 文件
COPY composer.json composer.lock ./

# 安装依赖
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 复制应用代码
COPY . .

# 复制 .env.example 到 .env
RUN cp .env.example .env || true

# 生成 APP_KEY
RUN php artisan key:generate --force

# 设置权限
RUN chmod -R 775 storage bootstrap/cache

# 暴露端口
EXPOSE 8080

# 启动命令
CMD php artisan config:cache && \
    php artisan route:cache && \
    php artisan serve --host=0.0.0.0 --port=${PORT:-8080}