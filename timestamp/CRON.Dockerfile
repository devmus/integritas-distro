# Use a current Debian base (no -slim variant for PHP images)
FROM php:8.3-cli-bookworm

# Prevent interactive tzdata prompts
ENV DEBIAN_FRONTEND=noninteractive

# Install only what's needed, then clean
RUN apt-get update \
 && apt-get install -y --no-install-recommends cron dos2unix \
 && docker-php-ext-install pdo_mysql \
 && rm -rf /var/lib/apt/lists/*

# App files
RUN mkdir -p /cron_scripts
WORKDIR /cron_scripts
COPY app/test_cron.php /cron_scripts/

# Cron job in /etc/cron.d (must include user field)
COPY crontab /etc/cron.d/timestamp
RUN dos2unix /etc/cron.d/timestamp /cron_scripts/test_cron.php \
 && chmod 0644 /etc/cron.d/timestamp \
 && chown root:root /etc/cron.d/timestamp

# Optional timezone
ENV TZ=Europe/Stockholm

# Optional: basic healthcheck (is the cron daemon alive?)
HEALTHCHECK --interval=30s --timeout=5s --retries=5 CMD pgrep cron || exit 1

# Run cron in foreground for docker logs
CMD ["bash","-lc","cron -f -L 0"]
