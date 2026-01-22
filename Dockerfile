FROM php:8.4.17-cli-alpine

WORKDIR /var/www/html

# -------------------------
# System + build dependencies (Alpine)
# -------------------------
RUN apk add --no-cache \
    git \
    unzip \
    curl \
    openssl-dev \
    zlib-dev \
    postgresql-dev \
    brotli-dev \
    pkgconf \
    autoconf \
    g++ \
    make

# -------------------------
# PHP core extensions
# -------------------------
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pcntl

# -------------------------
# Install Swoole (auto-picks correct version for PHP 8.5)
# -------------------------
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# -------------------------
# Verify extension
# -------------------------
RUN php -m | grep swoole

# -------------------------
# Copy app
# -------------------------
COPY . .

# -------------------------
# Install Composer
# -------------------------
RUN curl -sS https://getcomposer.org/installer \
    | php -- --install-dir=/usr/local/bin --filename=composer

RUN composer install --no-dev --optimize-autoloader --no-interaction

# -------------------------
# Permissions
# -------------------------
RUN chown -R www-data:www-data storage bootstrap/cache

# -------------------------
# Expose Port
# -------------------------
ENV PORT=8000
EXPOSE 8000

# -------------------------
# Start Laravel Octane with Swoole
# -------------------------
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000"]
