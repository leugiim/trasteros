#!/bin/bash
set -e

# Decode JWT keys from base64 env vars
if [ -n "$JWT_PRIVATE_KEY_BASE64" ]; then
    echo "$JWT_PRIVATE_KEY_BASE64" | base64 -d > /var/www/html/config/jwt/private.pem
    chmod 600 /var/www/html/config/jwt/private.pem
fi

if [ -n "$JWT_PUBLIC_KEY_BASE64" ]; then
    echo "$JWT_PUBLIC_KEY_BASE64" | base64 -d > /var/www/html/config/jwt/public.pem
fi

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

# Warmup cache
php bin/console cache:warmup --env=prod --no-debug

exec "$@"
