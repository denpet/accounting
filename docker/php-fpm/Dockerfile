FROM php:8.3-fpm AS base

ARG USER_NAME=wroom
ARG GROUP_NAME=wroom

# Install system dependencies and PHP extensions required for Laravel + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        chromium \
        nodejs \
        npm \
        zlib1g-dev \
        libpng-dev \
        libzip-dev \
        nano \
        iputils-ping \
        telnet \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        sockets \
        gd \
        zip \
    && npm install -g puppeteer \
    && npx puppeteer browsers install chrome \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini \
    && echo 'upload_max_filesize = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini \
    && echo 'post_max_size = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

RUN groupadd -g 1000 ${GROUP_NAME}
RUN useradd ${USER_NAME} -u 1000 -g ${GROUP_NAME} -s /bin/bash
RUN usermod -aG ${GROUP_NAME} ${USER_NAME}

RUN mkdir -p /var/www/.local /var/www/.config /var/www/.cache /var/www/.pki
RUN chown -R ${USER_NAME}:${GROUP_NAME} /var/www
RUN ln -sf /bin/bash /bin/sh

WORKDIR /var/eden/api

EXPOSE 9000
CMD ["php-fpm"]

FROM base AS production
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN composer self-update

COPY ./api /var/eden/api/
COPY ./api/.env.prod /var/eden/api/.env
RUN composer install
RUN npm install 

RUN chown -R ${USER_NAME}:${GROUP_NAME} /var/eden/api
USER $USER_NAME

FROM base AS development    
ARG XDEBUG_ENABLED=true
ARG XDEBUG_MODE=develop,coverage,debug,profile
ARG XDEBUG_HOST=host.docker.internal
ARG XDEBUG_IDE_KEY=DOCKER
ARG XDEBUG_LOG=/dev/stdout
ARG XDEBUG_LOG_LEVEL=0

RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "xdebug.mode=${XDEBUG_MODE}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=${XDEBUG_IDE_KEY}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log=${XDEBUG_LOG}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log_level=${XDEBUG_LOG_LEVEL}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=${XDEBUG_HOST}" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini  

USER $USER_NAME
