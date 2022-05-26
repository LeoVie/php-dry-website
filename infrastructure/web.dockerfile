FROM nginx:1.17

ADD vhost_web.conf /etc/nginx/conf.d/default.conf

RUN usermod -u 1000 www-data