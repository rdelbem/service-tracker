FROM wordpress:php8.1-apache

# Install system dependencies and PHP extensions
RUN apt-get update && \
    apt-get install -y libxml2-dev && \
    docker-php-ext-install soap && \
    rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Fix uploads directory permissions
RUN mkdir -p /var/www/html/wp-content/uploads && \
    chown -R www-data:www-data /var/www/html/wp-content/uploads && \
    chmod -R 755 /var/www/html/wp-content/uploads
