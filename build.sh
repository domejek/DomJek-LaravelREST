#!/bin/bash

set -e

echo "=== Starting Laravel Deployment ==="

echo "1. Stopping existing containers..."
docker-compose down 2>/dev/null || true

echo "2. Building Docker containers..."
docker-compose build --no-cache

echo "3. Starting containers..."
docker-compose up -d

echo "4. Waiting for database to be ready..."
for i in {1..30}; do
    if docker-compose exec db mysql -uroot -proot -e "SELECT 1" &>/dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Waiting for database... ($i/30)"
    sleep 2
done

echo "5. Creating Laravel project..."
docker-compose exec app sh -c "composer create-project laravel/laravel /var/www/html --prefer-dist --no-interaction --no-scripts 2>/dev/null || true"

echo "6. Moving Laravel files to /var/www..."
docker-compose exec app sh -c "if [ -d /var/www/html ]; then cp -r /var/www/html/* /var/www/; rm -rf /var/www/html; fi"

echo "7. Installing dependencies..."
docker-compose exec app composer install --no-interaction --prefer-dist

echo "8. Copying migrations..."
docker cp docker/migrations/. $(docker-compose ps -q app):/var/www/database/migrations/

echo "9. Setting up environment..."
docker-compose exec app sh -c "cp /var/www/.env.example /var/www/.env 2>/dev/null || true"
docker-compose exec app php artisan key:generate

echo "10. Configuring database..."
docker-compose exec app php -r "
\$env = file_get_contents('/var/www/.env');
\$env = str_replace('DB_HOST=127.0.0.1', 'DB_HOST=db', \$env);
\$env = str_replace('DB_PORT=3306', 'DB_PORT=3306', \$env);
\$env = str_replace('DB_DATABASE=laravel', 'DB_DATABASE=laravel', \$env);
\$env = str_replace('DB_USERNAME=root', 'DB_USERNAME=laravel', \$env);
\$env = str_replace('DB_PASSWORD=', 'DB_PASSWORD=secret', \$env);
file_put_contents('/var/www/.env', \$env);
"

echo "11. Running migrations..."
docker-compose exec app php artisan migrate --force

echo "12. Setting permissions..."
docker-compose exec app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "=== Deployment Complete ==="
echo "Laravel app: http://localhost:8080"
echo "MySQL: localhost:3306"
