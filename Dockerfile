FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

RUN a2enmod rewrite

# Por defecto Apache trae AllowOverride None y el .htaccess del proyecto
# (que rutea /api/... y reenvía el header Authorization) queda ignorado.
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . /var/www/html/

EXPOSE 80
