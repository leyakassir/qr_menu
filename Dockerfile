FROM php:8.2-apache

# Install extensions needed for MySQL connection
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files into the Apache web root directory
COPY . /var/www/html/

# Update Apache document root for custom structures if needed (optional)
# RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

EXPOSE 80