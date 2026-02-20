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

## Tech Stack

- **Backend:** Laravel 11
- **Datenbank:** MySQL / SQLite (Testing)
- **Auth:** Laravel Sanctum
- **Testing:** PHPUnit
- **CI/CD:** GitHub Actions

## Installation

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
cp .env .env.editor
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

Die API ist dann verfügbar unter `http://localhost:8000/api`

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

## Request Beispiele

### Registrierung
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"name":"Maxemail":"max@example Mustermann",".com","password":"password123","password_confirmation":"password123"}'
```

### Task erstellen
```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"title":"Meine Aufgabe","description":"Beschreibung","status":"todo","deadline":"2026-03-01 12:00:00"}'
```

### Task aktualisieren
```bash
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'
```

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

## Rollen und Berechtigungen

- **user** (Standard): Kann nur eigene Aufgaben erstellen, bearbeiten und löschen
- **admin**: Kann alle Aufgaben im System verwalten, auch überfällige

## Events und Notifications

Bei jeder Task-Aktualisierung wird geprüft, ob die Deadline abgelaufen ist. Ist dies der Fall, erhält der Benutzer eine E-Mail-Benachrichtigung.

## Tests

Alle Tests ausführen:
```bash
php artisan test
```

Nur Feature-Tests:
```bash
php artisan test --filter=Feature
```

Nur Unit-Tests:
```bash
php artisan test --filter=Unit
```

## CI/CD

Das Projekt verwendet GitHub Actions für Continuous Integration und Deployment:

| Workflow | Beschreibung |
|----------|--------------|
| **CI** | PHPUnit Tests (PHP 8.2-8.4) + Laravel Pint Code Style |
| **Docker** | Docker Image Build & Push zu GitHub Container Registry |
| **Security** | Composer Audit + Security Scanner (wöchentlich) |

**Dependabot** aktualisiert automatisch Dependencies für composer, npm und GitHub Actions.

Details: [docs/github_actions.md](docs/github_actions.md)

## Dokumentation

Detaillierte API-Dokumentation: [docs/api_dokumentation.md](docs/api_dokumentation.md)

## Lizenz

MIT License
