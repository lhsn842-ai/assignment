FROM php:8.2-fpm
ENV COMPOSER_ALLOW_SUPERUSER=1
WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libonig-dev libzip-dev libxml2-dev \
    libpng-dev libjpeg-dev libfreetype6-dev zlib1g-dev libssl-dev \
    pkg-config wget ca-certificates supervisor curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install intl bcmath pcntl pdo pdo_mysql zip gd opcache sockets \
    && pecl install redis mongodb xdebug \
    && docker-php-ext-enable redis mongodb xdebug

RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY package*.json ./
RUN npm install
RUN node --version
RUN npm --version
RUN echo "=== STARTING BUILD ==="

COPY . .
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

RUN npm run build
RUN ls -la public/
RUN find public/ -name "*.js" -o -name "*.css" -o -name "manifest.json" | head -10

RUN composer install --optimize-autoloader --no-scripts

COPY docker/app/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY docker/app/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/app/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY docker/app/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

EXPOSE 9000
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]