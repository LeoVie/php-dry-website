FROM php:8.1.2-fpm

ARG UID=1000
ARG GID=1000

RUN usermod -u $UID www-data