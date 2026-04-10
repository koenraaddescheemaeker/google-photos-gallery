FROM webdevops/php-apache:8.2

RUN docker-php-ext-install pdo pdo_pgsql

ENV WEB_DOCUMENT_ROOT=/app/public

COPY . /app

RUN chown -R application:application /app