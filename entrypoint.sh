#!/bin/bash
set -e

echo "--- Starting PHP Native App Container ---"

# Ensure the database directory exists (useful for SQLite volume)
if [ ! -d "/var/www/html/database" ]; then
    mkdir -p /var/www/html/database
fi

# Ensure uploads directory exists
if [ ! -d "/var/www/html/public/uploads" ]; then
    mkdir -p /var/www/html/public/uploads
fi

# Fix permissions for www-data (Apache user)
# When volumes are mounted, permissions might reset to root, so we fix them here on startup.
chown -R www-data:www-data /var/www/html/database
chown -R www-data:www-data /var/www/html/public/uploads

echo "--- Configuration Ready. Executing Command... ---"

# Execute the CMD passed in Dockerfile (apache2-foreground)
exec "$@"