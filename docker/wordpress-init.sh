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

# PHP ini overrides from SSM-injected env vars (ECS secrets)
INI_DIR="/usr/local/etc/php/conf.d"
INI_FILE="${INI_DIR}/zz-php-overrides.ini"
mkdir -p "$INI_DIR"

if [ -n "${PHP_MEMORY_LIMIT:-}${PHP_MAX_EXECUTION_TIME:-}${PHP_MAX_INPUT_TIME:-}${PHP_UPLOAD_MAX_FILESIZE:-}${PHP_POST_MAX_SIZE:-}${PHP_MAX_INPUT_VARS:-}" ]; then
  {
    [ -n "${PHP_MEMORY_LIMIT:-}" ] && echo "memory_limit=${PHP_MEMORY_LIMIT}"
    [ -n "${PHP_MAX_EXECUTION_TIME:-}" ] && echo "max_execution_time=${PHP_MAX_EXECUTION_TIME}"
    [ -n "${PHP_MAX_INPUT_TIME:-}" ] && echo "max_input_time=${PHP_MAX_INPUT_TIME}"
    [ -n "${PHP_UPLOAD_MAX_FILESIZE:-}" ] && echo "upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE}"
    [ -n "${PHP_POST_MAX_SIZE:-}" ] && echo "post_max_size=${PHP_POST_MAX_SIZE}"
    [ -n "${PHP_MAX_INPUT_VARS:-}" ] && echo "max_input_vars=${PHP_MAX_INPUT_VARS}"
  } > "$INI_FILE"

  echo "Wrote PHP overrides to $INI_FILE"
else
  echo "No PHP_* env vars set; leaving PHP config unchanged"
fi

chown -R www-data:www-data /var/www/html/wp-content 2>/dev/null || true

exec docker-entrypoint.sh "$@"
