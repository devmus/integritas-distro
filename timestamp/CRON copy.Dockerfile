# CRON.Dockerfile
FROM php:8.1-cli

# Install cron and tools
RUN apt-get update && apt-get -y install cron dos2unix

# PHP extensions
RUN docker-php-ext-install pdo_mysql

# App files
RUN mkdir -p /cron_scripts
WORKDIR /cron_scripts
COPY test_cron.php /cron_scripts/

# Cron job (cron.d format)
# ─ includes a user field (root)
COPY ./crontab /etc/cron.d/timestamp
RUN dos2unix /etc/cron.d/timestamp /cron_scripts/test_cron.php \
 && chmod 0644 /etc/cron.d/timestamp \
 && chown root:root /etc/cron.d/timestamp

# Run cron in foreground so logs go to docker logs
CMD ["bash","-lc","cron -f -L 0"]
