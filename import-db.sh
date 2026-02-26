#!/bin/bash

# ==========================================
# 1. LOAD ENVIRONMENT VARIABLES
# ==========================================
if [ -f .env ]; then
    echo "⚙️ Loading configuration from .env file..."
    export $(grep -v '^#' .env | xargs)
else
    echo "❌ Error: Cannot find .env file in the current directory."
    exit 1
fi

# ==========================================
# 2. SCRIPT CONFIGURATION
# ==========================================
DB_FILE="local.db.latest.sql"
DB_CONTAINER="gca-intranet-db-1"
WP_CONTAINER="gca-intranet-wordpress-1"


# URLs for Find & Replace
OLD_URL="${FIND_REPLACE_OLD_URL}"
NEW_URL="${FIND_REPLACE_NEW_URL}"

# Admin Email Fallback (WP requires an email to create a user)
ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@localhost.com}"

# ==========================================
# 3. EXECUTION
# ==========================================
echo "🚀 Starting WordPress Database Import..."

if [ ! -f "$DB_FILE" ]; then
    echo "❌ Error: Cannot find '$DB_FILE' in the current directory."
    exit 1
fi

echo "📦 Importing $DB_FILE into the database..."
docker exec -i "$DB_CONTAINER" mysql -u"$DB_USER" -p"$DB_PASSWORD" "$DB_NAME" < "$DB_FILE"

if [ $? -ne 0 ]; then
    echo "❌ Error: Database import failed."
    exit 1
fi
echo "✅ Database imported successfully."

echo "🔍 Running Find & Replace and User Checks..."
docker exec -i "$WP_CONTAINER" bash -c "
    # 1. Setup WP-CLI command
    if ! command -v wp &> /dev/null; then
        echo '📥 WP-CLI not found natively, downloading...'
        curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
        WP_CMD='php wp-cli.phar'
    else
        WP_CMD='wp'
    fi

    # 2. Find and Replace
    \$WP_CMD search-replace '$OLD_URL' '$NEW_URL' --all-tables --allow-root
    \$WP_CMD rewrite flush --allow-root
    \$WP_CMD cache flush --allow-root

    # 3. Ensure Admin User Exists
    echo '👤 Checking for admin user ($WP_ADMIN_USER)...'
    if \$WP_CMD user get '$WP_ADMIN_USER' --allow-root > /dev/null 2>&1; then
        echo '✅ Admin user already exists. Updating password just in case...'
        \$WP_CMD user update '$WP_ADMIN_USER' --user_pass='$WP_ADMIN_PASSWORD' --allow-root
    else
        echo '➕ Creating admin user...'
        \$WP_CMD user create '$WP_ADMIN_USER' '$ADMIN_EMAIL' --role=administrator --user_pass='$WP_ADMIN_PASSWORD' --allow-root
    fi
"

echo "🎉 All done! Your local database is now synced and ready to go on port ${WP_PORT}."