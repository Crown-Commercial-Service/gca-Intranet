#!/bin/sh
set -e

mkdir -p /var/www/html/wp-content/languages \
         /var/www/html/wp-content/upgrade \
         /var/www/html/wp-content/uploads

if [ -d /opt/wp-content-seed ] && [ ! -f /var/www/html/wp-content/.seeded ] && [ ! -d /var/www/html/wp-content/themes ]; then
  echo "Seeding wp-content from /opt/wp-content-seed..."
  cp -a /opt/wp-content-seed/. /var/www/html/wp-content/ 2>/dev/null || true
  touch /var/www/html/wp-content/.seeded || true
fi

chown -R www-data:www-data /var/www/html/wp-content 2>/dev/null || true

if [ "$1" = "wp" ]; then
    if [ ! -f /var/www/html/wp-config.php ]; then
        echo "Running WordPress setup for WP-CLI..."
        docker-entrypoint.sh apache2-foreground &
        APACHE_PID=$!
        count=0
        while [ ! -f /var/www/html/wp-config.php ] && [ $count -lt 30 ]; do
            sleep 1
            count=$((count + 1))
        done
        kill "$APACHE_PID" 2>/dev/null || true
        wait "$APACHE_PID" 2>/dev/null || true
    fi
    exec "$@"
fi

exec docker-entrypoint.sh "$@"
