FROM php:8.3-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers expires

COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/localcapital.ini
COPY docker/entrypoint.sh /usr/local/bin/localcapital-entrypoint

RUN chmod +x /usr/local/bin/localcapital-entrypoint

WORKDIR /var/www/html

COPY . /var/www/html

ENTRYPOINT ["localcapital-entrypoint"]
CMD ["apache2-foreground"]
