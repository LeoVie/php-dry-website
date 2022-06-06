ARG PHP_VERSION=8.0
FROM php:${PHP_VERSION}-fpm-alpine3.14 as composer

COPY install_composer.sh /install_composer.sh
RUN chmod +x /install_composer.sh && /install_composer.sh
RUN mv composer.phar /usr/bin/composer

FROM php:${PHP_VERSION}-fpm-alpine3.14 as runner
COPY --from=composer /usr/bin/composer /usr/bin

RUN apk update && apk upgrade && apk add git

WORKDIR /app

ENTRYPOINT ["composer"]