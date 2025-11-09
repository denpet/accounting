FROM nginx:latest AS base

ARG USER_NAME=www-data
ARG GROUP_NAME=www-data

# Install system dependencies and PHP extensions required for Laravel + MySQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        nodejs \
        npm \
    && npm install -g @quasar/cli \
    && apt-get autoremove -y && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* 
RUN ln -sf /bin/bash /bin/sh

WORKDIR /var/eden/app

FROM base AS production
ARG PRODUCT_API_URL
COPY ./app /var/eden/app/
RUN npm install
RUN quasar build

FROM base AS development
