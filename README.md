# Task Management API

Eine RESTful API für ein Aufgaben-Management-System mit Laravel.

## Features

- **Benutzer-Authentifizierung** - Registrierung, Login, Logout via Laravel Sanctum
- **Role-Based Access Control** - Benutzer und Admin-Rollen
- **Task CRUD** - Erstellen, Lesen, Aktualisieren, Löschen von Aufgaben
- **Project CRUD** - Erstellen, Lesen, Aktualisieren, Löschen von Projekten
- **Beziehungen** - Benutzer → Projekte → Aufgaben (1:n)
- **Deadline-Management** - Fälligkeitsdaten mit Überfälligkeits-Benachrichtigungen
- **Validierung** - Umfassende Form-Request Validierung
- **Events & Notifications** - Automatische E-Mail-Benachrichtigungen bei überfälligen Deadlines
- **Tests** - Umfassende PHPUnit Test-Suite
- **Docker** - Fertige Docker-Konfiguration für einfaches Deployment

## Tech Stack

- **Backend:** Laravel 12
- **Datenbank:** MySQL 8.0
- **Auth:** Laravel Sanctum
- **Testing:** PHPUnit
- **CI/CD:** GitHub Actions
- **Container:** Docker & Docker Compose

---

## Schnellstart mit Docker (Empfohlen)

### Voraussetzungen

- Docker Desktop
- Docker Compose

### Installation

1. **Repository klonen:**
```bash
git clone <repository-url>
cd DomJek-LaravelREST
```

2. **Container starten:**
```bash
./build.sh
```

Das Skript führt automatisch aus:
- Docker Container bauen und starten
- Composer Dependencies installieren
- Datenbank-Migrationen ausführen
- Application Key generieren

3. **API testen:**
```bash
# Registrierung
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```

Die API ist verfügbar unter: `http://localhost:8080/api`

### Docker Container verwalten

```bash
# Container stoppen
docker-compose down

# Container neu bauen
docker-compose build --no-cache

# Logs anzeigen
docker-compose logs -f app

# In Container einloggen
docker-compose exec app bash
```

---

## Lokale Installation (Ohne Docker)

### Voraussetzungen

- PHP >= 8.2
- Composer
- MySQL Datenbank
- Node.js (optional für Assets)

### Schritte

1. **Repository klonen:**
```bash
git clone <repository-url>
cd DomJek-LaravelREST
```

2. **Abhängigkeiten installieren:**
```bash
composer install
```

3. **Umgebung konfigurieren:**
```bash
cp .env.example .env
```

4. **Datenbank konfigurieren** (in `.env`):
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=
```

5. **Application Key generieren:**
```bash
php artisan key:generate
```

6. **Migrationen ausführen:**
```bash
php artisan migrate
```

7. **Server starten:**
```bash
php artisan serve
```

Die API ist verfügbar unter `http://localhost:8000/api`

---

## API Endpoints

### Authentifizierung

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| POST | /api/register | Benutzer registrieren |
| POST | /api/login | Benutzer anmelden |
| POST | /api/logout | Benutzer abmelden |
| GET | /api/user | Aktuellen Benutzer abrufen |

### Tasks

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| GET | /api/tasks | Alle Aufgaben auflisten |
| POST | /api/tasks | Neue Aufgabe erstellen |
| GET | /api/tasks/{id} | Einzelne Aufgabe anzeigen |
| PUT | /api/tasks/{id} | Aufgabe aktualisieren |
| DELETE | /api/tasks/{id} | Aufgabe löschen |
| GET | /api/tasks/overdue | Überfällige Aufgaben |
| GET | /api/users/{id}/tasks | Aufgaben eines Benutzers |
| GET | /api/projects/{id}/tasks | Aufgaben eines Projekts |

### Projects

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| GET | /api/projects | Alle Projekte auflisten |
| POST | /api/projects | Neues Projekt erstellen |
| GET | /api/projects/{id} | Einzelnes Projekt anzeigen |
| PUT | /api/projects/{id} | Projekt aktualisieren |
| DELETE | /api/projects/{id} | Projekt löschen |

---

## API Verwendung

> **Wichtig:** Immer den `Accept: application/json` Header senden, um JSON-Responses zu erhalten und Redirects bei Validierungsfehlern zu vermeiden.

### 1. Registrierung

```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Max Mustermann",
    "email": "max@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

**Response:**
```json
{
  "user": {
    "id": 1,
    "name": "Max Mustermann",
    "email": "max@example.com",
    "role": "user"
  },
  "token": "1|abc123..."
}
```

### 2. Login

```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "max@example.com",
    "password": "password123"
  }'
```

### 3. API nutzen (mit Token)

```bash
# Projekte abrufen
curl http://localhost:8080/api/projects \
  -H "Accept: application/json" \
  -H "Authorization: Bearer DEIN_TOKEN"

# Projekt erstellen
curl -X POST http://localhost:8080/api/projects \
  -H "Accept: application/json" \
  -H "Authorization: Bearer DEIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Mein Projekt","description":"Beschreibung"}'

# Task erstellen
curl -X POST http://localhost:8080/api/tasks \
  -H "Accept: application/json" \
  -H "Authorization: Bearer DEIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Meine Aufgabe",
    "description": "Beschreibung",
    "status": "todo",
    "deadline": "2026-03-01 12:00:00"
  }'

# Task aktualisieren
curl -X PUT http://localhost:8080/api/tasks/1 \
  -H "Accept: application/json" \
  -H "Authorization: Bearer DEIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "in_progress"}'
```

---

## Validierungsregeln

### Tasks

| Feld | Regel | Beschreibung |
|------|-------|--------------|
| title | required, max:255 | Erforderlich, max. 255 Zeichen |
| description | required | Erforderlich |
| status | in:todo,in_progress,done | Nur erlaubte Werte |
| deadline | date, after:now | Gültiges Datum in der Zukunft |
| project_id | nullable, exists:projects,id | Optional |

### Projects

| Feld | Regel | Beschreibung |
|------|-------|--------------|
| name | required, max:255 | Erforderlich, max. 255 Zeichen |
| description | nullable, string | Optional |

---

## Rollen und Berechtigungen

- **user** (Standard): Kann nur eigene Aufgaben erstellen, bearbeiten und löschen
- **admin**: Kann alle Aufgaben im System verwalten, auch überfällige

---

## Events und Notifications

Bei jeder Task-Aktualisierung wird geprüft, ob die Deadline abgelaufen ist. Ist dies der Fall, erhält der Benutzer eine E-Mail-Benachrichtigung.

---

## Tests

Das Projekt verfügt über eine umfassende Test-Suite mit **116 Tests** und **270 Assertions**.

### Alle Tests ausführen

```bash
# Mit Docker
docker-compose exec app php artisan test

# Lokal
php artisan test
```

### Nach Kategorie filtern

```bash
php artisan test --filter=Feature
php artisan test --filter=Unit
php artisan test --filter=AuthTest
```

### Test-Abdeckung

| Kategorie | Test-Klasse | Tests | Beschreibung |
|-----------|-------------|-------|--------------|
| **Feature** | AuthTest | 14 | Registrierung, Login, Logout, Validierung |
| **Feature** | TaskCrudTest | 35 | CRUD, Validierung, Rechte, 404, Relations |
| **Feature** | ProjectCrudTest | 22 | CRUD, Validierung, Rechte, 404 |
| **Unit** | TaskModelTest | 6 | Model-Beziehungen, Fillable |
| **Unit** | ProjectModelTest | 5 | Model-Beziehungen, Fillable |
| **Unit** | UserModelTest | 6 | Model-Beziehungen, isAdmin() |
| **Unit** | TaskUpdatedEventTest | 3 | Event-Erstellung, Dispatch |
| **Unit** | CheckTaskDeadlineListenerTest | 3 | Notification-Trigger |
| **Unit** | TaskDeadlineNotificationTest | 4 | Mail/Array Format, ShouldQueue |
| **Unit** | TaskFormRequestTest | 10 | Store/Update Validierungsregeln |
| **Unit** | ProjectFormRequestTest | 8 | Store/Update Validierungsregeln |

Detaillierte Test-Dokumentation: [docs/testing.md](docs/testing.md)

---

## CI/CD

Das Projekt verwendet GitHub Actions für Continuous Integration und Deployment:

| Workflow | Beschreibung |
|----------|--------------|
| **CI** | PHPUnit Tests (PHP 8.2-8.4) + Laravel Pint Code Style |
| **Docker** | Docker Image Build & Push zu GitHub Container Registry |
| **Security** | Composer Audit + Security Scanner (wöchentlich) |

**Dependabot** aktualisiert automatisch Dependencies für composer, npm und GitHub Actions.

Details: [docs/github_actions.md](docs/github_actions.md)

---

## Projektstruktur

```
├── app/
│   ├── Http/Controllers/    # API Controller
│   ├── Models/              # Eloquent Models
│   ├── Events/              # Event Klassen
│   ├── Listeners/           # Event Listener
│   └── Notifications/       # E-Mail Benachrichtigungen
├── database/
│   └── migrations/          # Datenbank Migrationen
├── routes/
│   └── api.php              # API Routen
├── tests/
│   ├── Feature/             # Feature Tests
│   └── Unit/                # Unit Tests
├── docker/
│   ├── Dockerfile           # PHP-FPM Container
│   └── nginx.conf           # Nginx Konfiguration
├── docker-compose.yml       # Docker Compose Setup
├── build.sh                 # Build & Deploy Script
└── docs/                    # Dokumentation
```

---

## Dokumentation

- **API-Dokumentation:** [docs/api_dokumentation.md](docs/api_dokumentation.md)
- **Eloquent Modelle:** [docs/eloquent_modelle.md](docs/eloquent_modelle.md)
- **Testing:** [docs/testing.md](docs/testing.md)
- **CI/CD:** [docs/github_actions.md](docs/github_actions.md)

---

## Lizenz

MIT License
