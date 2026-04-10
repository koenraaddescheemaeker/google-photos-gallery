FROM webdevops/php-apache:8.2

ENV WEB_DOCUMENT_ROOT=/app/public

COPY . /app

RUN chown -R application:application /app