# === Base image ===
FROM dunglas/frankenphp

# === Set working directory ===
WORKDIR /var/www/html

# === System dependencies ===
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip git libpq-dev \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# === Install PHP extensions needed by Laravel ===
RUN install-php-extensions pcntl pdo_pgsql

# === Copy project files ===
COPY . .

# === Install Composer dependencies ===
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --optimize-autoloader --no-interaction

# === Expose port for Octane ===
EXPOSE 8000

# === Default command ===
CMD ["php", "artisan", "octane:start", "--watch", "--host=0.0.0.0"]

