FROM php:8.2-fpm AS build

ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libzip-dev libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev zlib1g-dev libssl-dev \
    pkg-config wget ca-certificates npm supervisor \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl bcmath pcntl pdo pdo_mysql zip gd opcache sockets \
    && pecl install redis mongodb \
    && docker-php-ext-enable redis mongodb

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN usermod -u 1000 www-data && chown -R www-data:www-data /var/www/html

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

COPY docker/app/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY docker/app/php.ini /usr/local/etc/php/conf.d/custom.ini

RUN npm install -g npm@latest n && n latest
COPY composer.json composer.lock ./
COPY . .

RUN composer install --optimize-autoloader --no-scripts

COPY docker/app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

EXPOSE 9000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
