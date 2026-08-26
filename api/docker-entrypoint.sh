#!/bin/bash
set -e

echo "==> Checking JWT keys..."
if [ ! -f "$JWT_SECRET_KEY" ] || [ ! -f "$JWT_PUBLIC_KEY" ]; then
    echo "    ERROR: JWT_SECRET_KEY/JWT_PUBLIC_KEY must point to existing .pem files"
    echo "    (mount them into the container — see compose.prod.yaml)"
    exit 1
fi

echo "==> Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "==> Warming up cache..."
php bin/console cache:warmup --env=prod --no-debug
chown -R www-data:www-data /var/www/html/var

echo "==> Starting server..."
exec "$@"
