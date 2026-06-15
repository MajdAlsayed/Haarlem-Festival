FROM php:fpm

# Install system dependencies and Composer
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libzip-dev libonig-dev libpng-dev libjpeg-dev libfreetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring gd \
    && curl -sS https://getcomposer.org/installer -o composer-setup.php \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php \
    && rm -rf /var/lib/apt/lists/*

# Allow jazz/dance admin audio uploads (must match nginx client_max_body_size).
RUN printf '%s\n' 'upload_max_filesize = 128M' 'post_max_size = 128M' > /usr/local/etc/php/conf.d/zz-uploads.ini

WORKDIR /app

# Allow running Composer as root within the container
ENV COMPOSER_ALLOW_SUPERUSER=1

# On container start, install dependencies if vendor is missing, then start php-fpm
CMD ["sh", "-lc", "[ -f vendor/autoload.php ] || composer install --no-interaction --no-progress; exec php-fpm"]