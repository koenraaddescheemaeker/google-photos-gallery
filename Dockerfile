# Gebruik de vlijmscherpe Apache-PHP basis
FROM webdevops/php-apache:8.2

# Zet de bestanden in de juiste map binnen de container
COPY . /app

# Zorg dat Apache de rechten heeft om de bestanden te lezen
RUN chown -R application:application /app