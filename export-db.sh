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

# ==========================================
# 3. EXECUTION
# ==========================================
echo "🚀 Starting WordPress Database Export..."

# Dump the Database using credentials from the .env file
echo "📦 Exporting database '$DB_NAME' to '$DB_FILE'..."

# We use mysqldump inside the container and pipe (>) the output to a local file
docker exec "$DB_CONTAINER" mysqldump -u"$DB_USER" -p"$DB_PASSWORD" --no-tablespaces "$DB_NAME" > "$DB_FILE"

# Check if the command was successful
if [ $? -ne 0 ]; then
    echo "❌ Error: Database export failed."
    # Remove the file if the export failed so we don't leave a broken file behind
    rm -f "$DB_FILE"
    exit 1
fi

echo "✅ Export complete! You can now share '$DB_FILE' with the team."
