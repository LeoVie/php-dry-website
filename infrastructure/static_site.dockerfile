FROM nginx:1.17

ARG UID=1000
ARG GID=1000

ADD vhost_static_site.conf /etc/nginx/conf.d/default.conf

RUN usermod -u $UID www-data