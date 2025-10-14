FROM php:8.3-fpm-bookworm

# Install PHP extensions you actually use
RUN docker-php-ext-install pdo pdo_mysql

# --- XDEBUG: make optional & pinned ---
ARG INSTALL_XDEBUG=false
ARG XDEBUG_VERSION=3.3.2
RUN if [ "$INSTALL_XDEBUG" = "true" ]; then \
      pecl install xdebug-"$XDEBUG_VERSION" \
      && docker-php-ext-enable xdebug \
      && rm -rf /tmp/pear ~/.pearrc ; \
    fi

# Optional timezone
ENV TZ=Europe/Stockholm
