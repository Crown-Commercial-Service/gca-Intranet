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
FROM wordpress:6.5.5-php8.2-apache

# 1. Install system dependencies (zip for WP-CLI/GDS)
RUN apt-get update && apt-get install -y libzip-dev unzip && docker-php-ext-install zip

# 2. Copy the whole wp-content (contains your php files)
COPY wp-content/ /var/www/html/wp-content/

# 3. Pull ONLY the compiled CSS/JS from the builder stage
# This ensures ECR gets the "ready to go" assets
COPY --from=builder /app/theme-folder/assets/dist/ /var/www/html/wp-content/themes/gca-intranet-foundation/assets/dist/
COPY --from=builder /app/theme-folder/assets/scripts/ /var/www/html/wp-content/themes/gca-intranet-foundation/assets/scripts/

# Ensure Apache listens on 8080 for ECS
RUN sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf && \
    sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf