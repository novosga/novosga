FROM trafex/php-nginx:3.4.0 as php

USER root

RUN apk add --no-cache \
    git \
    php82-iconv \
    php82-pdo_mysql \
    php82-pdo_pgsql \
    php82-simplexml \
    php82-tokenizer \
    php82-xmlwriter \
    php82-fileinfo \
    php82-sodium \
    php82-xsl

ADD etc/php.ini /etc/php82/conf.d/custom.ini
ADD etc/nginx.conf /etc/nginx/conf.d/default.conf

USER 65534
ADD --chown=65534:65534 . /var/www/html


FROM php as composer

ENV COMPOSER_CACHE_DIR /tmp/
ENV APP_ENV prod
ENV APP_DEBUG 0

RUN curl -o composer.phar https://getcomposer.org/download/2.7.2/composer.phar

RUN set -xe \
    && php composer.phar install --no-dev --optimize-autoloader \
    && php composer.phar dump-autoload --no-dev --classmap-authoritative \
    && php composer.phar dump-env prod


FROM php

COPY --from=composer --chown=65534:65534 /var/www/html/vendor /var/www/html/vendor
COPY --from=composer --chown=65534:65534 /var/www/html/public/bundles /var/www/html/public/bundles
