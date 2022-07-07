ARG PHP_VERSION=8.1
FROM php:${PHP_VERSION}-fpm-alpine
WORKDIR /var/www

RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini

RUN apk --no-cache --update add ${PHPIZE_DEPS}
RUN pecl install pcov && docker-php-ext-enable pcov

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

CMD ["php-fpm"]
EXPOSE 9000