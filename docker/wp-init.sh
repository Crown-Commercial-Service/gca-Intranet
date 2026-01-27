#!/bin/sh
set -e

cd /var/www/html

# 0) Ensure WordPress core exists in /var/www/html (init task does not reliably get the "copy core" behavior)
if [ ! -f /var/www/html/wp-includes/version.php ]; then
  echo "WordPress core missing in /var/www/html - copying from /usr/src/wordpress (excluding wp-content)..."
  tar -C /usr/src/wordpress --exclude='./wp-content' -cf - . | tar -C /var/www/html -xf -
fi

# 1) Ensure expected dirs exist (EFS reality)
mkdir -p wp-content/languages wp-content/upgrade wp-content/uploads || true

# 2) Seed wp-content into volume if empty (covers EFS + also covers cases where entrypoint didn’t run)
if [ -d /opt/wp-content-seed ] && [ ! -f /var/www/html/wp-content/.seeded ]; then
  echo "Seeding wp-content from /opt/wp-content-seed..."
  cp -a /opt/wp-content-seed/. /var/www/html/wp-content/ 2>/dev/null || true
  touch /var/www/html/wp-content/.seeded || true
fi

# 3) Wait for DB TCP (no mysqlcheck needed)
DB_HOST="${WORDPRESS_DB_HOST%%:*}"
DB_PORT="${WORDPRESS_DB_HOST##*:}"
[ "$DB_HOST" = "$DB_PORT" ] && DB_PORT=3306

echo "Waiting for DB ${DB_HOST}:${DB_PORT}..."
DB_HOST="$DB_HOST" DB_PORT="$DB_PORT" php -r '
for ($i=0; $i<120; $i++) {
  $host = getenv("DB_HOST");
  $port = (int)getenv("DB_PORT");
  $s = @fsockopen($host, $port, $errno, $errstr, 1);
  if ($s) { fclose($s); echo "DB reachable\n"; exit(0); }
  sleep(1);
}
fwrite(STDERR, "DB not reachable\n");
exit(1);
'

# 4) Create wp-config.php if missing
if [ ! -f wp-config.php ]; then
  echo "Creating wp-config.php..."
  wp config create --allow-root --skip-check \
    --dbname="$WORDPRESS_DB_NAME" \
    --dbuser="$WORDPRESS_DB_USER" \
    --dbpass="$WORDPRESS_DB_PASSWORD" \
    --dbhost="$WORDPRESS_DB_HOST"
fi

# 5) Install WordPress (idempotent)
SITE_URL="${WP_HOME:-${WP_URL:-http://localhost:8080}}"

if ! wp core is-installed --allow-root >/dev/null 2>&1; then
  echo "Installing WordPress..."
  wp core install --allow-root \
    --url="$SITE_URL" \
    --title="${WP_TITLE:-GCA Intranet}" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL"
else
  echo "WordPress already installed."
fi

# 6) Apply URLs if provided
[ -n "$WP_HOME" ] && wp option update home "$WP_HOME" --allow-root || true
[ -n "$WP_SITEURL" ] && wp option update siteurl "$WP_SITEURL" --allow-root || true

# 7) Activate theme + permalinks
[ -n "$WP_THEME" ] && wp theme activate "$WP_THEME" --allow-root || true
wp rewrite structure "/%postname%/" --allow-root || true
wp rewrite flush --allow-root || true

# 8) Permissions (best effort)
chown -R www-data:www-data /var/www/html/wp-content 2>/dev/null || true

echo "INIT DONE"
