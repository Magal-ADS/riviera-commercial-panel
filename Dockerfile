FROM composer:2 AS dependencies

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-progress --no-scripts

FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

WORKDIR /var/www/html
COPY . .
COPY --from=dependencies /app/vendor ./vendor

RUN touch .env \
    && mkdir -p storage/app/data storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/riviera-entrypoint
RUN chmod +x /usr/local/bin/riviera-entrypoint

EXPOSE 80
ENTRYPOINT ["riviera-entrypoint"]
CMD ["apache2-foreground"]
