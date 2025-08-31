FROM php:8.2-fpm AS build

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y git unzip zip libicu-dev libonig-dev libzip-dev libxml2-dev libpng-dev libjpeg-dev libfreetype6-dev zlib1g-dev libssl-dev pkg-config wget ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl bcmath pcntl pdo pdo_mysql zip gd opcache sockets \
    && pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && usermod -u 1000 www-data \
    && chown -R www-data:www-data /var/www/html

RUN apt-get update && apt-get install -y npm
RUN npm install npm@latest -g && \
    npm install n -g && \
    n latest
RUN apt-get update && apt-get install -y supervisor

COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
#FROM build AS prod
#
#COPY ./dist ./dist
