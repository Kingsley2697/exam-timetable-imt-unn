FROM php:8.3-apache

# SQLite development files are required to compile the PDO SQLite driver.
# The installed drivers match the MySQL and SQLite connections in db.php.
RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# This application has index.php at its repository root, so Apache's default
# document root is deliberately used as the application document root.
WORKDIR /var/www/html
COPY . .

# Apache must be able to create the SQLite fallback database when no MySQL
# connection is configured.
RUN chown -R www-data:www-data /var/www/html

EXPOSE 10000

# Render supplies the PORT environment variable at runtime. Its value is
# applied to both Apache's listener and default virtual host before startup.
CMD ["/bin/sh", "-c", "PORT=${PORT:-10000}; sed -i \"s/^Listen 80$/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -i \"s/<VirtualHost [*]:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf; apache2-foreground"]
