FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_mysql zip

RUN a2enmod rewrite

RUN sed -i 's/Listen 80/Listen 5005/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:5005/' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . .

RUN mkdir -p database public/uploads

RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /var/www/html/database \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/database \
    && chmod -R 775 /var/www/html/public/uploads

COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port
EXPOSE 5005

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["apache2-foreground"]