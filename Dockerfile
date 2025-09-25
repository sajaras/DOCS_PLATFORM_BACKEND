# Use the official PHP 7.4-FPM image as a base
FROM php:7.4-fpm

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev # <-- Add dependency for the zip extension

# Install PHP extensions required by Laravel
# ---- MODIFIED THIS LINE ----
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install and enable OPcache
RUN docker-php-ext-install opcache
# Copy our custom opcache config file
COPY docker-compose/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# ---- NEW LINE TO FIX GIT ERROR ----
# Set the git safe directory
RUN git config --global --add safe.directory /var/www

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . .

# Copy existing application directory permissions
COPY --chown=www-data:www-data . .

# Run Composer
RUN composer install --no-dev --optimize-autoloader

# Expose port 9000 and start php-fpm server
EXPOSE 9000
CMD ["php-fpm"]