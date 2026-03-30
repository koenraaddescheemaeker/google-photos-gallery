FROM php:8.2-apache

# 1. Verhoog de PHP & Apache limieten naar 3600 seconden (1 uur)
# Dit moet matchen met de timeout die je in Coolify voor de Cron/Scheduled Task zet.
RUN echo "max_execution_time = 3600" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "max_input_time = 3600" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "Timeout 3600" >> /etc/apache2/apache2.conf

# 2. Kopieer de bestanden
COPY . /var/www/html/

# 3. Stel de juiste rechten in zodat Apache alles kan lezen
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80