## Build the website docker image

## builder image
FROM edbizarro/gitlab-ci-pipeline-php:7.4 AS build-env

COPY --chown=php:php . /var/www/html

RUN composer install --prefer-dist && \
    yarn && \
    yarn prod 

## production image
FROM nginx:1.20-alpine AS production-env

ENV LOCATION '/var/www/html'

WORKDIR $LOCATION

COPY --chown=nginx docker/nginx/nginx-default.conf /etc/nginx/conf.d/default.conf

COPY --chown=nginx --from=0 /var/www/html/build_production/ "$LOCATION"


EXPOSE 80

