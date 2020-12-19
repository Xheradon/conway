################################################################################
# composer                                                                     #
################################################################################
FROM composer:latest
WORKDIR /opt/project
COPY . ./

RUN composer install -o --classmap-authoritative --ignore-platform-reqs --no-scripts -n --no-dev

################################################################################
# project                                                                      #
################################################################################
FROM php:8.0.0-cli-alpine3.12
LABEL maintainer="me@iavanamaro.dev"
WORKDIR /opt/project
COPY --from=0 /opt/project ./
ENTRYPOINT ["php", "bin/console"]
