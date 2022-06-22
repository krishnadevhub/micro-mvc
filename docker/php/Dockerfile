FROM php:8.0-fpm

LABEL maintainer="Krishna"

ENV APP_HOME /var/www/html
ARG UID=1000
ARG GID=1000
ENV USERNAME=www-data
ARG TIMEZONE=Asia/Kuala_Lumpur

COPY php/php.ini /usr/local/etc/php/conf.d/

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev zip curl wget zsh fonts-powerline locales nano \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

COPY php/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone
RUN printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN echo 'alias composer="XDEBUG_MODE=off composer"' >> ~/.bashrc

# create document root, fix permissions for www-data user and change owner to www-data
RUN mkdir -p $APP_HOME/public && \
    mkdir -p /home/$USERNAME && chown $USERNAME:$USERNAME /home/$USERNAME \
    && usermod -u $UID $USERNAME -d /home/$USERNAME \
    && groupmod -g $GID $USERNAME \
    && chown -R ${USERNAME}:${USERNAME} $APP_HOME

# set working directory
WORKDIR $APP_HOME