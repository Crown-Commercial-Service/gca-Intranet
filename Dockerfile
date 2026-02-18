# syntax=docker/dockerfile:1

##########
# 1) (Optional) Build theme assets if your theme has package.json
##########
FROM node:20-alpine AS assets
WORKDIR /app

# Copy only wp-content first (faster caching)
COPY wp-content/ ./wp-content/

# If your theme has package.json, build it.
# If you don't use Node tooling, this stage does nothing harmful.
RUN set -eux; \
  THEME_DIR="wp-content/themes/gca-intranet-foundation"; \
  if [ -f "$THEME_DIR/package.json" ]; then \
    cd "$THEME_DIR"; \
    if [ -f package-lock.json ]; then \
      npm ci; \
    else \
      npm i; \
    fi; \
    npm run build; \
    echo "=== CSS files in assets stage after build ==="; \
    ls -lah assets/dist/ || true; \
    echo "=== Full path check ==="; \
    find /app -name "gca-theme.css" || echo "File not found"; \
  else \
    echo "No package.json found at $THEME_DIR"; \
  fi

##########
# 2) WordPress runtime (Apache)
##########
FROM wordpress:6.5.5-php8.2-apache

# Common deps + extensions (trim if you want leaner)
# - default-mysql-client provides mysql/mysqlcheck (useful for wp-cli db commands)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev unzip curl default-mysql-client \
  && docker-php-ext-install zip mysqli \
  && a2enmod rewrite headers remoteip \
  && rm -rf /var/lib/apt/lists/*

# Install WP-CLI into the image (AWS-friendly: avoids downloading at runtime)
ARG WP_CLI_VERSION=2.12.0
RUN curl -sSLo /usr/local/bin/wp "https://github.com/wp-cli/wp-cli/releases/download/v${WP_CLI_VERSION}/wp-cli-${WP_CLI_VERSION}.phar" \
  && chmod +x /usr/local/bin/wp \
  && wp --info

# Trust reverse-proxy IP headers (private ranges)
RUN printf "%s\n" \
  "RemoteIPHeader X-Forwarded-For" \
  "RemoteIPTrustedProxy 10.0.0.0/8" \
  "RemoteIPTrustedProxy 172.16.0.0/12" \
  "RemoteIPTrustedProxy 192.168.0.0/16" \
  > /etc/apache2/conf-available/remoteip.conf \
  && a2enconf remoteip

# Respect ALB/CloudFront forwarded proto so WP doesn't redirect to http://...:8080
RUN printf "%s\n" \
  "SetEnvIf X-Forwarded-Proto https HTTPS=on" \
  "SetEnvIf X-Forwarded-Port 443 HTTPS=on" \
  > /etc/apache2/conf-available/forwarded-ssl.conf \
  && a2enconf forwarded-ssl

# Switch Apache to listen on 8080 instead of 80 (for non-root/service user on Fargate)
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
 && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

# Copy WP content (themes/plugins) into the image
COPY wp-content/ /var/www/html/wp-content/

# Overwrite with built assets (if any were produced)
COPY --from=assets /app/wp-content/ /var/www/html/wp-content/

# IMPORTANT for AWS/EFS: keep a seed copy OUTSIDE /var/www/html,
# because an EFS mount on /var/www/html/wp-content will hide the image content.
RUN mkdir -p /opt/wp-content-seed
COPY wp-content/ /opt/wp-content-seed/
COPY --from=assets /app/wp-content/ /opt/wp-content-seed/

# Add init wrappers/scripts
COPY docker/wordpress-init.sh /usr/local/bin/wordpress-init.sh
RUN chmod +x /usr/local/bin/wordpress-init.sh

# One-off init script (used by docker compose run init, and by ECS one-off init tasks)
COPY docker/wp-init.sh /usr/local/bin/wp-init.sh
RUN chmod +x /usr/local/bin/wp-init.sh

# Permissions (best effort; volume mounts may override at runtime)
RUN chown -R www-data:www-data /var/www/html/wp-content || true

# Local-friendly defaults:
# - Allow plugin/theme installs (DO NOT set DISALLOW_FILE_MODS)
# - Block theme/plugin editor
# - Support X-Forwarded-Proto for HTTPS behind proxies
#
# NOTE: In Dockerfile ENV, "$" expands, so PHP $_SERVER must be written as $$_SERVER
COPY docker/wp-config-extra.php /opt/wp-config-extra.php
ENV WORDPRESS_CONFIG_EXTRA="require '/opt/wp-config-extra.php';"

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD curl -fsS http://localhost:8080/wp-login.php >/dev/null || exit 1

ENTRYPOINT ["wordpress-init.sh"]
CMD ["apache2-foreground"]

EXPOSE 8080