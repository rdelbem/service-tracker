FROM wordpress:php8.1-apache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install additional PHP extensions our plugin needs
RUN docker-php-ext-install soap
