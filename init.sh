#!/bin/bash
set -e

echo "🚀 Starting Laravel initialization..."

# Ensure .env exists
if [ ! -f .env ]; then
  echo "⚙️  .env not found, creating from example..."
  cp .env.example .env || true
fi

# Ensure APP_KEY exists
if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
  echo "🔑 Generating Laravel APP_KEY..."
  php artisan key:generate --force
else
  echo "✅ APP_KEY already set."
fi

# Optional caches
php artisan config:cache || true
php artisan route:cache || true

echo "🌐 Launching Laravel server..."
exec php artisan serve --host=0.0.0.0 --port=$PORT
