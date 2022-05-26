FROM nginx:1.17

ADD vhost_static_site.conf /etc/nginx/conf.d/default.conf

RUN usermod -u 1000 www-data