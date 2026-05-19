FROM alpine/openssl AS cert

RUN mkdir /jwt \
    && openssl genrsa -out /jwt/private.pem 2048 \
    && openssl rsa -in /jwt/private.pem -pubout -out /jwt/public.pem


FROM mangati/frankenphp:1.12-php8.2-alpine AS runtime

ARG COMPOSER_AUTH
ARG GIT_COMMIT=dev

ENV COMPOSER_CACHE_DIR=/tmp/
ENV APP_ENV=prod
ENV APP_DEBUG=0
ENV MERCURE_PUBLIC_URL="/.well-known/mercure"
ENV MERCURE_URL="http://127.0.0.1:8080/.well-known/mercure"

COPY --chown=65534:65534 . /app
COPY --from=cert --chown=65534:65534 /jwt /app/config/jwt
COPY etc/Caddyfile /etc/frankenphp/Caddyfile
COPY etc/php.ini $PHP_INI_DIR/php.ini

USER 65534

RUN echo "APP_BUILD_NUMBER=$GIT_COMMIT" >> .env.local \
    && composer install --no-dev --optimize-autoloader \
    && composer dump-autoload --no-dev --classmap-authoritative \
    && composer dump-env prod

EXPOSE 8080
