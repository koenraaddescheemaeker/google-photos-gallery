FROM php:8.5-fpm-alpine
# Manu: We gebruiken de Alpine versie voor een vlijmscherpe, lichte container.
RUN docker-php-ext-install pdo_mysql
# Rest van je installatie...
# 1. Verhoog de PHP & Apache limieten naar 3600 seconden (1 uur)
# Dit matches met de timeout van je Coolify Cron / Scheduled Task.
RUN echo "max_execution_time = 3600" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_input_time = 3600" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "Timeout 3600" >> /etc/apache2/apache2.conf

# 2. Kopieer de applicatiebestanden
COPY . /var/www/html/

# 3. Stel de juiste rechten in voor Apache
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80