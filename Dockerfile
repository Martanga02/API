FROM php:8.5-apache

# unzip: Composer lo necesita para extraer los paquetes que instala más abajo.
# libpq-dev: librería cliente de PostgreSQL, necesaria para compilar pdo_pgsql.
RUN apt-get update && apt-get install -y --no-install-recommends unzip libpq-dev && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql

RUN a2enmod rewrite

# Por defecto Apache trae AllowOverride None y el .htaccess del proyecto
# (que rutea /api/... y reenvía el header Authorization) queda ignorado.
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY ./tp_parte3/ /var/www/html/

# Instala PHPUnit (require-dev) para poder correr los tests con
# `docker compose exec api vendor/bin/phpunit`
RUN composer install

EXPOSE 80
