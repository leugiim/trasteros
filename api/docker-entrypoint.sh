#!/bin/bash
set -e

echo "==> Decoding JWT keys..."
if [ -n "$JWT_PRIVATE_KEY_BASE64" ]; then
    echo "$JWT_PRIVATE_KEY_BASE64" | tr -d '\r\n ' | base64 -d > /var/www/html/config/jwt/private.pem
    chmod 600 /var/www/html/config/jwt/private.pem
    echo "    private.pem OK"
fi

if [ -n "$JWT_PUBLIC_KEY_BASE64" ]; then
    echo "$JWT_PUBLIC_KEY_BASE64" | tr -d '\r\n ' | base64 -d > /var/www/html/config/jwt/public.pem
    echo "    public.pem OK"
fi

echo "==> Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "==> Warming up cache..."
php bin/console cache:warmup --env=prod --no-debug

echo "==> Starting server..."
exec "$@"
