#!/bin/sh
set -e

echo "==> Waiting for database..."
until php -r "new PDO('mysql:host=$DB_HOST;port=${DB_PORT:-3306}', '$DB_USERNAME', '$DB_PASSWORD');" 2>/dev/null; do
  sleep 2
done
echo "==> Database is ready."

cd /var/www/html

# Generate app key if missing
if [ -z "$APP_KEY" ]; then
  php artisan key:generate --force
fi

# Run migrations
php artisan migrate --force

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Fix permissions in case volumes mounted
chown -R www-data:www-data storage bootstrap/cache

echo "==> Starting services..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
