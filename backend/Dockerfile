# Use the official PHP image from Docker Hub
FROM php:8.0-apache

# Install PostgreSQL PDO extensions (required for connecting to PostgreSQL)
RUN docker-php-ext-install pdo pdo_pgsql

# Set the working directory inside the container
WORKDIR /var/www/html

# Copy the backend code into the container
COPY ./ /var/www/html/

# Expose the port that Apache will run on
EXPOSE 80

# Start Apache server
CMD ["apache2-foreground"]
