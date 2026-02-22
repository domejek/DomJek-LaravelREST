#!/bin/bash

set -e

echo "=== Laravel Deployment starten ==="

echo "1. Vorhandene Container stoppen und Volumes entfernen..."
docker-compose down -v 2>/dev/null || true

echo "2. Docker Container bauen..."
docker-compose build --no-cache

echo "3. Container starten..."
docker-compose up -d

echo "4. Warten bis Datenbank bereit ist..."
for i in {1..30}; do
    if docker-compose exec -T db mysql -uroot -proot -e "SELECT 1" &>/dev/null; then
        echo "Datenbank ist bereit!"
        break
    fi
    echo "Warte auf Datenbank... ($i/30)"
    sleep 2
done

echo "5. Abhängigkeiten installieren..."
docker-compose exec -T app composer install --no-interaction --prefer-dist

echo "6. Umgebung einrichten..."
docker-compose exec -T app sh -c "cp /var/www/.env.example /var/www/.env 2>/dev/null || true"
docker-compose exec -T app php artisan key:generate

echo "7. Datenbank konfigurieren..."
docker-compose exec -T app sh -c "
sed -i 's/DB_HOST=.*/DB_HOST=db/' /var/www/.env
sed -i 's/DB_DATABASE=.*/DB_DATABASE=task_management/' /var/www/.env
sed -i 's/DB_USERNAME=.*/DB_USERNAME=laravel/' /var/www/.env
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=secret/' /var/www/.env
"

echo "8. Migrationen ausführen..."
docker-compose exec -T app php artisan migrate --force

echo "9. Berechtigungen setzen..."
docker-compose exec -T app chmod -R 775 /var/www/storage /var/www/bootstrap/cache

echo "=== Deployment abgeschlossen ==="
echo "Laravel API: http://localhost:8080"
echo "MySQL: localhost:3306"
echo ""
echo "DBeaver Verbindung:"
echo "  Host: localhost"
echo "  Port: 3306"
echo "  Datenbank: task_management"
echo "  Benutzer: laravel (Passwort: secret)"
echo "  Benutzer: root (Passwort: root)"
