#!/bin/bash
set -e

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Run database migrations and holiday sync
php artisan migrate --force || true
php artisan holidays:sync || true

# Cache configurations
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Register Telegram Webhook if domain is provided
if [ -n "$APP_URL" ] && [ -n "$TELEGRAM_BOT_TOKEN" ]; then
    php artisan telegram:set-webhook "$APP_URL/api/telegram/webhook" || true
fi

# Start PHP built-in server with PORT provided by Render (default 8080 or 10000)
PORT="${PORT:-8080}"
echo "🚀 Starting Laravel server on port $PORT..."
exec php -S 0.0.0.0:"$PORT" -t public/
