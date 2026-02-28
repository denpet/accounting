FROM php:8.5-fpm AS base

ARG USER_NAME=eden
ARG GROUP_NAME=eden

# Install system dependencies and PHP extensions required for Laravel + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && echo 'memory_limit = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini \
    && echo 'upload_max_filesize = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini \
    && echo 'post_max_size = 2048M' >> /usr/local/etc/php/conf.d/docker-php-memlimit.ini

RUN groupadd -g 1000 ${GROUP_NAME}
RUN useradd ${USER_NAME} -u 1000 -g ${GROUP_NAME} -s /bin/bash -m
RUN usermod -aG ${GROUP_NAME} ${USER_NAME}
RUN ln -sf /bin/bash /bin/sh

RUN mkdir -p /var/www/.local /var/www/.config /var/www/.cache /var/www/.pki
RUN chown -R ${USER_NAME}:${GROUP_NAME} /var/www

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
COPY --from=composer /usr/bin/composer /usr/bin/composer
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    echo "xdebug.mode=develop,coverage,debug,profile" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.idekey=DOCKER" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log=/dev/stdout" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.log_level=0" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini && \
    echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini  

USER $USER_NAME
