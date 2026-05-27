FROM php:8.4-fpm

LABEL maintainer="Krishna"

ARG UID=1000
ARG GID=1000
ARG TIMEZONE=Asia/Kuala_Lumpur

ENV APP_HOME=/var/www/html
ENV USERNAME=www-data

# Install system dependencies
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        zlib1g-dev \
        g++ \
        git \
        libicu-dev \
        libzip-dev \
        zip \
        curl \
        wget \
        nano \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (opcache is bundled in php:8.4-fpm)
RUN docker-php-ext-install intl pdo pdo_mysql zip \
    && pecl install apcu xdebug \
    && docker-php-ext-enable apcu xdebug

# Copy PHP configuration
COPY php/php.ini /usr/local/etc/php/conf.d/
COPY php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n' ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini

# Alias composer to disable xdebug overhead
RUN echo 'alias composer="XDEBUG_MODE=off composer"' >> ~/.bashrc

# Install Node.js
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && npm install --global yarn \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Configure www-data user and set up document root
RUN mkdir -p ${APP_HOME}/public /home/${USERNAME} \
    && usermod -u ${UID} ${USERNAME} -d /home/${USERNAME} \
    && groupmod -g ${GID} ${USERNAME} \
    && chown -R ${USERNAME}:${USERNAME} ${APP_HOME} /home/${USERNAME}

WORKDIR ${APP_HOME}
