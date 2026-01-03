#!/bin/bash
set -e

# Vytvoření potřebných adresářů, pokud neexistují
mkdir -p /var/www/html/log
mkdir -p /var/www/html/temp/sessions
mkdir -p /var/www/html/temp/cache

# Nastavení správných oprávnění pro zápis
chown -R www-data:www-data /var/www/html/log /var/www/html/temp 2>/dev/null || true
chmod -R 775 /var/www/html/log /var/www/html/temp 2>/dev/null || true

# Zkontroluj, zda existuje vendor/autoload.php, pokud ne, nainstaluj závislosti
if [ ! -f "/var/www/html/vendor/autoload.php" ]; then
    echo "Vendor directory not found, installing Composer dependencies..."
    composer install --no-interaction
fi

# Spusť původní CMD
exec "$@"

