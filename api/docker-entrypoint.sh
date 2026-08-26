#!/bin/bash
set -e

echo "==> Checking JWT keys..."
# This entrypoint runs as root, where a plain `-r` test is meaningless —
# Linux's access(2) grants root read access regardless of permission bits.
# Apache/PHP actually serve requests as www-data, a different user than
# whoever owns the bind-mounted host files, so check readability as that
# user specifically or a bad mount silently 500s on first login instead of
# failing loudly here.
if ! su -s /bin/sh www-data -c "[ -r '$JWT_SECRET_KEY' ] && [ -r '$JWT_PUBLIC_KEY' ]"; then
    echo "    ERROR: JWT_SECRET_KEY/JWT_PUBLIC_KEY must point to .pem files"
    echo "    that exist AND are readable by www-data (mount them into the"
    echo "    container — see compose.prod.yaml)"
    exit 1
fi

echo "==> Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --env=prod

echo "==> Warming up cache..."
php bin/console cache:warmup --env=prod --no-debug
chown -R www-data:www-data /var/www/html/var

echo "==> Starting server..."
exec "$@"
