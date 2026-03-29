FROM php:8.2-apache

# 1. Verhoog PHP limieten (Time-out en Geheugen)
RUN echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/custom.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/custom.ini

# 2. Verhoog Apache Time-out
RUN echo "Timeout 300" >> /etc/apache2/apache2.conf

COPY . /var/www/html/
EXPOSE 80