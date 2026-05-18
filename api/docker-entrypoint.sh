#!/bin/bash
set -e

echo "==> Writing JWT keys..."
if [ -n "$JWT_SECRET_KEY" ]; then
    printf '%s' "$JWT_SECRET_KEY" > /var/www/html/config/jwt/private.pem
    chmod 600 /var/www/html/config/jwt/private.pem
    echo "    private.pem OK"
fi

if [ -n "$JWT_PUBLIC_KEY" ]; then
    printf '%s' "$JWT_PUBLIC_KEY" > /var/www/html/config/jwt/public.pem
    echo "    public.pem OK"
fi

echo "==> Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "==> Warming up cache..."
php bin/console cache:warmup --env=prod --no-debug
chown -R www-data:www-data /var/www/html/var

echo "==> Starting server..."
exec "$@"
