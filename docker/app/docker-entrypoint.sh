#!/bin/bash
set -e

echo "Waiting for database connection..."
until php -r "try { new PDO(getenv('DB_CONNECTION').':host='.getenv('DB_HOST').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'Database ready.'; exit; } catch (Exception \$e) { echo 'Waiting for DB...'; sleep(3); }"; do
  sleep 3
done

if [ -f /var/www/html/vendor/autoload.php ]; then
    echo "Running migrations..."
    php artisan migrate --seed
else
    echo "Skipping migrations: dependencies not installed"
fi

exec "$@"
