FROM php:8.2-apache

# Install required system packages
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client

# Install PHP extensions (CRITICAL)
RUN docker-php-ext-install pdo_mysql mysqli

# Enable Apache modules
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
