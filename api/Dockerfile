FROM php:8.4-apache

RUN apt-get update && apt-get install -y \
    libpq-dev libicu-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_pgsql intl opcache zip \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN APP_ENV=prod composer install --no-dev --no-scripts --optimize-autoloader

COPY . .

RUN echo "APP_ENV=prod" > .env

RUN mkdir -p config/jwt var/cache var/log \
    && chown -R www-data:www-data var

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
