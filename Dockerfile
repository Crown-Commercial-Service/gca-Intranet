# STAGE 1: Build the assets (using the logic you found)
FROM node:20-alpine AS builder
WORKDIR /app
COPY wp-content/themes/gca-intranet-foundation ./theme-folder

RUN set -eux; \
    cd ./theme-folder; \
    npm install; \
    # We override the command here to ensure the load-path is absolute and correct
    ./node_modules/.bin/sass assets/scss/theme.scss assets/dist/gca-theme.css \
    --style=compressed \
    --load-path=node_modules \
    --quiet-deps; \
    # Run the rest of the build (copying JS)
    npm run js:copygovuk-frontend; \
    npm run js:minifygovuk-frontend

# STAGE 2: The actual WordPress container
FROM wordpress:6.9.4-php8.2-apache

COPY docker/php.ini /usr/local/etc/php/conf.d/custom-php.ini

# 1. Install system dependencies (zip for WP-CLI/GDS)
RUN apt-get update && apt-get install -y libzip-dev unzip && docker-php-ext-install zip \
  && docker-php-ext-install zip \
  && pecl install redis \
  && docker-php-ext-enable redis

# 2. Install WP-CLI into the image
ARG WP_CLI_VERSION=2.12.0
RUN curl -sSLo /usr/local/bin/wp "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar" \
  && chmod +x /usr/local/bin/wp \
  && wp --info

# 3. Copy the whole wp-content (contains your php files)
COPY wp-content/ /var/www/html/wp-content/

# 4. Pull ONLY the compiled CSS/JS from the builder stage
# This ensures ECR gets the "ready to go" assets
COPY --from=builder /app/theme-folder/assets/dist/ /var/www/html/wp-content/themes/gca-intranet-foundation/assets/dist/
COPY --from=builder /app/theme-folder/assets/scripts/ /var/www/html/wp-content/themes/gca-intranet-foundation/assets/scripts/

# Ensure Apache listens on 8080 for ECS
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

# One-off init script (used by docker compose run init, and by ECS one-off init tasks)
COPY docker/wp-init.sh /usr/local/bin/wp-init.sh
RUN chmod +x /usr/local/bin/wp-init.sh

# Add the custom WordPress config bridge
COPY docker/wp-config-extra.php /opt/wp-config-extra.php
ENV WORDPRESS_CONFIG_EXTRA="require '/opt/wp-config-extra.php';"

# Add the main startup init script
COPY docker/wordpress-init.sh /usr/local/bin/wordpress-init.sh
RUN chmod +x /usr/local/bin/wordpress-init.sh

# Tell AWS ECS how to verify the container is healthy
HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD curl -fsS http://localhost:8080/wp-login.php >/dev/null || exit 1

# Run the init script, then start Apache
ENTRYPOINT ["wordpress-init.sh"]
CMD ["apache2-foreground"]