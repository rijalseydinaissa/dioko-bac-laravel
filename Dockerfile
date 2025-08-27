# Dockerfile simple et optimisé pour Render
FROM php:8.2-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy composer files
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Complete composer installation
RUN COMPOSER_ALLOW_SUPERUSER=1 composer run-script post-autoload-dump

# Set permissions
RUN chmod -R 755 storage bootstrap/cache

# Cache configuration for production (optimisation)
RUN php artisan config:cache || true
RUN php artisan route:cache || true

# Create startup script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Expose port
EXPOSE 8000

# Use startup script instead of direct artisan serve
CMD ["/usr/local/bin/start.sh"]