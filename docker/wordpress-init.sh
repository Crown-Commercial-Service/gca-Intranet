#!/bin/sh
set -e

mkdir -p /var/www/html/wp-content/languages \
         /var/www/html/wp-content/upgrade \
         /var/www/html/wp-content/uploads

# If wp-content is an empty EFS volume, seed it from the image copy
if [ -d /opt/wp-content-seed ] && [ ! -f /var/www/html/wp-content/.seeded ] && [ ! -d /var/www/html/wp-content/themes ]; then
  echo "Seeding wp-content from /opt/wp-content-seed..."
  cp -a /opt/wp-content-seed/. /var/www/html/wp-content/ 2>/dev/null || true
  touch /var/www/html/wp-content/.seeded || true
fi

chown -R www-data:www-data /var/www/html/wp-content 2>/dev/null || true

exec docker-entrypoint.sh "$@"

