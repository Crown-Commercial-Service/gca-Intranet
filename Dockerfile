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
  THEME_DIR="$(find wp-content/themes -maxdepth 2 -name package.json -print -quit | xargs -I{} dirname {} || true)"; \
  if [ -n "$THEME_DIR" ]; then \
    cd "$THEME_DIR"; \
    if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm i --frozen-lockfile; \
    else npm i; fi; \
    npm run build; \
  else \
    echo "No theme package.json found; skipping asset build"; \
  fi

##########
# 2) WordPress runtime (Apache) for ECS/Fargate
##########
FROM wordpress:6.5.5-php8.2-apache

# Common deps + extensions (trim if you want leaner)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libzip-dev unzip curl \
  && docker-php-ext-install zip mysqli \
  && a2enmod rewrite headers remoteip \
  && rm -rf /var/lib/apt/lists/*

# Trust ALB / reverse-proxy IP headers (private ranges)
RUN printf "%s\n" \
  "RemoteIPHeader X-Forwarded-For" \
  "RemoteIPTrustedProxy 10.0.0.0/8" \
  "RemoteIPTrustedProxy 172.16.0.0/12" \
  "RemoteIPTrustedProxy 192.168.0.0/16" \
  > /etc/apache2/conf-available/remoteip.conf \
  && a2enconf remoteip

# Copy WP content (themes/plugins) into the image
COPY wp-content/ /var/www/html/wp-content/

# Overwrite with built assets (if any were produced)
COPY --from=assets /app/wp-content/ /var/www/html/wp-content/

# Permissions
RUN chown -R www-data:www-data /var/www/html/wp-content

# WP hardening defaults + https-forwarded proto support
ENV WORDPRESS_CONFIG_EXTRA="\
define('DISALLOW_FILE_EDIT', true); \
define('DISALLOW_FILE_MODS', true); \
if (isset(\$_SERVER['HTTP_X_FORWARDED_PROTO']) && \$_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') { \$_SERVER['HTTPS'] = 'on'; } \
"

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD curl -fsS http://localhost/wp-login.php >/dev/null || exit 1

EXPOSE 80
