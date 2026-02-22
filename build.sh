#!/bin/bash

set -e

echo "=== Starting Laravel Deployment ==="

echo "1. Stopping existing containers and removing volumes..."
docker-compose down -v 2>/dev/null || true

echo "2. Building Docker containers..."
docker-compose build --no-cache

echo "3. Starting containers..."
docker-compose up -d

echo "4. Waiting for database to be ready..."
for i in {1..30}; do
    if docker-compose exec -T db mysql -uroot -proot -e "SELECT 1" &>/dev/null; then
        echo "Database is ready!"
        break
    fi
    echo "Waiting for database... ($i/30)"
    sleep 2
done

echo "5. Installing dependencies..."
docker-compose exec -T app composer install --no-interaction --prefer-dist

echo "6. Setting up environment..."
docker-compose exec -T app sh -c "cp /var/www/.env.example /var/www/.env 2>/dev/null || true"
docker-compose exec -T app php artisan key:generate

echo "7. Configuring database..."
docker-compose exec -T app sh -c "
sed -i 's/DB_HOST=.*/DB_HOST=db/' /var/www/.env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=task_management/' /var/www/.env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=laravel/' /var/www/.env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=secret/' /var/www/.env
"

echo "8. Running migrations..."
docker-compose exec -T app php artisan migrate --force

echo "9. Setting permissions..."
docker-compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "=== Deployment Complete ==="
echo "Laravel API: http://localhost:8080"
echo "MySQL: localhost:3306"
echo ""
echo "DBeaver Connection:"
echo "  Host: localhost"
echo "  Port: 3306"
echo "  Database: task_management"
echo "  User: laravel (password: secret)"
echo "  User: root (password: root)"
