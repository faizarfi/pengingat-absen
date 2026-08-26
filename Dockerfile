FROM php:8.2-cli-alpine

# Install system dependencies & PHP extensions
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    sqlite-dev \
    libzip-dev \
    oniguruma-dev \
    bash \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring xml zip bcmath pcntl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Copy environment file if not exists
RUN cp .env.example .env || true

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create SQLite database file if needed
RUN touch /var/www/database/database.sqlite \
    && chmod -R 777 /var/www/database \
    && chmod -R 777 /var/www/storage /var/www/bootstrap/cache

# Make entrypoint executable
RUN chmod +x /var/www/docker-entrypoint.sh

# Expose port (Render sets PORT env variable)
EXPOSE 8080

ENTRYPOINT ["/var/www/docker-entrypoint.sh"]
