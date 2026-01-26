#!/bin/sh
set -e

mkdir -p /var/www/html/wp-content/languages /var/www/html/wp-content/upgrade

chown -R www-data:www-data /var/www/html/wp-content/languages /var/www/html/wp-content/upgrade 2>/dev/null || true
chmod 775 /var/www/html/wp-content/languages /var/www/html/wp-content/upgrade 2>/dev/null || true

exec docker-entrypoint.sh "$@"
