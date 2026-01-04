#!/bin/bash
set -e

# Create necessary directories if they don't exist
mkdir -p /var/www/html/log
mkdir -p /var/www/html/temp/sessions
mkdir -p /var/www/html/temp/cache

# Set correct write permissions
chown -R www-data:www-data /var/www/html/log /var/www/html/temp 2>/dev/null || true
chmod -R 775 /var/www/html/log /var/www/html/temp 2>/dev/null || true

# Check if vendor/autoload.php exists, if not, install dependencies
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Vendor directory not found, installing Composer dependencies..."
    composer install --no-interaction
fi

# Run original CMD
exec "$@"
