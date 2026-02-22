# Task Management API

Eine vollständige RESTful API für ein Aufgaben-Management-System, entwickelt mit **Laravel 11** und **MySQL**.
---

## Inhaltsverzeichnis

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Architektur & Datenbankstruktur](#architektur--datenbankstruktur)
- [Installation mit Docker](#installation-mit-docker)
- [Installation ohne Docker (lokal)](#installation-ohne-docker-lokal)
- [Umgebungsvariablen](#umgebungsvariablen)
- [API Endpoints](#api-endpoints)
- [Authentifizierung & Middleware](#authentifizierung--middleware)
- [Rollen & Berechtigungen](#rollen--berechtigungen)
- [Validierungsregeln](#validierungsregeln)
- [Events & Notifications](#events--notifications)
- [HTTP-Statuscodes](#http-statuscodes)
- [Tests](#tests)
- [CI/CD](#cicd)
- [Pull Requests](#pull-requests)
- [Weitere Dokumentation](#weitere-dokumentation)

---

## Features

| Feature | Beschreibung |
|---|---|
| **Benutzer-Authentifizierung** | Registrierung, Login, Logout via Laravel Sanctum (Token-basiert) |
| **Role-Based Access Control** | Rollen `user` und `admin` mit unterschiedlichen Berechtigungen |
| **Task CRUD** | Erstellen, Lesen, Aktualisieren, Löschen von Aufgaben |
| **Project CRUD** | Erstellen, Lesen, Aktualisieren, Löschen von Projekten |
| **Beziehungen** | Benutzer → Aufgaben (1:n) · Projekte → Aufgaben (1:n) |
| **Deadline-Management** | Fälligkeitsdaten mit automatischer Überfälligkeitsprüfung |
| **Validierung** | Umfassende Form-Request Validierung mit sprechenden Fehlermeldungen |
| **Events & Notifications** | Automatische E-Mail-Benachrichtigung bei überfälliger Deadline nach Task-Update |
| **Middleware** | Auth-Middleware, Owner-Check, Deadline-Schutz |
| **Tests** | 116 PHPUnit-Tests mit 270 Assertions (Feature & Unit) |
| **CI/CD** | GitHub Actions: Tests, Code Style, Docker Build, Security Audit |

---

## Tech Stack

| Komponente | Technologie |
|---|---|
| Backend Framework | Laravel 11 |
| Sprache | PHP 8.2+ |
| Datenbank (Produktion) | MySQL 8.0 |
| Datenbank (Tests) | SQLite (In-Memory) |
| Authentifizierung | Laravel Sanctum |
| Testing | PHPUnit |
| Container | Docker & Docker Compose |
| CI/CD | GitHub Actions |

---

## Architektur & Datenbankstruktur

### Datenbankmodell

```
users
├── id (PK)
├── name
├── email (unique)
├── password
├── role  →  'user' | 'admin'
└── timestamps

projects
├── id (PK)
├── name
├── description (nullable)
└── timestamps

tasks
├── id (PK)
├── title           (max. 255 Zeichen)
├── description
├── status          →  'todo' | 'in_progress' | 'done'
├── deadline        (nullable, muss in der Zukunft liegen)
├── user_id (FK)    →  users.id
├── project_id (FK) →  projects.id (nullable)
└── timestamps
```

### Beziehungen (Eloquent)

```
User        ──< Tasks      (hasMany / belongsTo)
Project     ──< Tasks      (hasMany / belongsTo)
Task        >── User       (belongsTo)
Task        >── Project    (belongsTo, optional)
```

### Code-Struktur (relevante Dateien)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php       # Registrierung, Login, Logout
│   │   ├── TaskController.php       # Task CRUD + overdue + Beziehungsrouten
│   │   └── ProjectController.php   # Project CRUD
│   ├── Middleware/
│   │   ├── EnsureTaskOwner.php      # Nur eigene Tasks bearbeitbar
│   │   └── EnsureNotOverdueOrAdmin.php  # Überfällige Tasks nur für Admins
│   └── Requests/
│       ├── StoreTaskRequest.php     # Validierung beim Erstellen
│       ├── UpdateTaskRequest.php    # Validierung beim Aktualisieren
│       ├── StoreProjectRequest.php
│       └── UpdateProjectRequest.php
├── Models/
│   ├── User.php
│   ├── Task.php
│   └── Project.php
├── Events/
│   └── TaskUpdated.php
├── Listeners/
│   └── CheckTaskDeadlineListener.php
└── Notifications/
    └── TaskDeadlineNotification.php

database/migrations/
routes/api.php
tests/Feature/   (AuthTest, TaskCrudTest, ProjectCrudTest)
tests/Unit/      (Model-, Event-, Request-Tests)
```

---

## Installation mit Docker

> **Empfohlener Weg** – keine lokale PHP/MySQL-Installation nötig.

### Voraussetzungen

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installiert und gestartet
- Git

### Schritte

**1. Repository klonen**

```bash
git clone https://github.com/domejek/DomJek-LaravelREST.git
cd DomJek-LaravelREST
```

**2. Docker-Container starten**

```bash
docker compose up -d
```

Folgende Container werden gestartet:

| Container | Dienst | Port |
|---|---|---|
| `app` | Laravel (PHP-FPM + Nginx) | `8000` |
| `db` | MySQL 8.0 | `3306` |

**3. Abhängigkeiten installieren & App initialisieren**

```bash
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

**4. Optional: Testdaten einspielen**

```bash
docker compose exec app php artisan db:seed
```

Die API ist jetzt erreichbar unter: **`http://localhost:8000/api`**

### Container stoppen

```bash
docker compose down
```

### Logs anzeigen

```bash
docker compose logs app
docker compose logs db
```

---

## Installation ohne Docker (lokal)

### Voraussetzungen

- PHP >= 8.2
- Composer
- MySQL-Datenbank
- Node.js (optional)

### Schritte

**1. Repository klonen**

```bash
git clone https://github.com/domejek/DomJek-LaravelREST.git
cd DomJek-LaravelREST
```

**2. Abhängigkeiten installieren**

```bash
composer install
```

**3. Application Key generieren**

```bash
php artisan key:generate
```

**4. Datenbank konfigurieren** (in `.env`):

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=
```

**5. Datenbank anlegen & Migrationen ausführen**

```bash
php artisan migrate
```

**6. Entwicklungsserver starten**

```bash
php artisan serve
```

Die API ist dann verfügbar unter: **`http://localhost:8000/api`**

---

## Umgebungsvariablen

Alle relevanten Variablen der `.env`-Datei im Überblick:

### Datenbank

```env
DB_CONNECTION=mysql
DB_HOST=db              # Bei Docker: 'db' (Container-Name), sonst '127.0.0.1'
DB_PORT=3306
DB_DATABASE=task_management
DB_USERNAME=root
DB_PASSWORD=secret
```

### E-Mail (für Deadline-Benachrichtigungen)

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit        # Bei Docker: Mailpit-Container (localhost für lokale Nutzung)
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@taskmanagement.local"
MAIL_FROM_NAME="Task Management API"
```

> Für lokale Tests ohne echten Mail-Server: `MAIL_MAILER=log` setzt Mails ins Laravel-Log.

### Sanctum

```env
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

---

## API Endpoints

Alle Routen (außer Register/Login) erfordern einen gültigen Bearer Token im `Authorization`-Header.

### Authentifizierung (öffentlich)

| Methode | Endpoint | Beschreibung |
|---|---|---|
| `POST` | `/api/register` | Benutzer registrieren |
| `POST` | `/api/login` | Einloggen, Token erhalten |

### Authentifizierung (geschützt)

| Methode | Endpoint | Beschreibung |
|---|---|---|
| `POST` | `/api/logout` | Ausloggen, Token invalidieren |
| `GET` | `/api/user` | Eigenes Profil abrufen |

### Tasks (alle geschützt)

| Methode | Endpoint | Beschreibung | Berechtigung |
|---|---|---|---|
| `GET` | `/api/tasks` | Alle eigenen Aufgaben | Eigene Tasks |
| `POST` | `/api/tasks` | Neue Aufgabe erstellen | Authentifiziert |
| `GET` | `/api/tasks/{id}` | Einzelne Aufgabe anzeigen | Eigentümer |
| `PUT` | `/api/tasks/{id}` | Aufgabe aktualisieren | Eigentümer / Admin |
| `DELETE` | `/api/tasks/{id}` | Aufgabe löschen | Eigentümer |
| `GET` | `/api/tasks/overdue` | Überfällige Aufgaben | Authentifiziert |
| `GET` | `/api/users/{id}/tasks` | Aufgaben eines Benutzers | Authentifiziert |
| `GET` | `/api/projects/{id}/tasks` | Aufgaben eines Projekts | Authentifiziert |

### Projects (alle geschützt)

| Methode | Endpoint | Beschreibung |
|---|---|---|
| `GET` | `/api/projects` | Alle Projekte auflisten |
| `POST` | `/api/projects` | Neues Projekt erstellen |
| `GET` | `/api/projects/{id}` | Einzelnes Projekt anzeigen |
| `PUT` | `/api/projects/{id}` | Projekt aktualisieren |
| `DELETE` | `/api/projects/{id}` | Projekt löschen |

### Request & Response Beispiele

**Registrierung**

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mankurium",
    "email": "mankurium@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

```json
{
  "user": { "id": 1, "name": "Mankurium", "email": "mankurium@example.com", "role": "user" },
  "token": "1|abc123..."
}
```

**Task erstellen**

```bash
curl -X POST http://localhost:8000/api/tasks \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Manatränke brauen",
    "description": "Verjüngungstränke für Mana herstellen",
    "status": "todo",
    "deadline": "2026-12-01 10:00:00",
    "project_id": 1
  }'
```

**Task aktualisieren**

```bash
curl -X PUT http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -d '{ "status": "in_progress" }'
```

---

## Authentifizierung & Middleware

Die API verwendet **Laravel Sanctum** für Token-basierte Authentifizierung. Nach dem Login erhält der Benutzer einen API-Token, der bei jedem Request als Bearer Token übergeben werden muss.

```
Authorization: Bearer <token>
```

### Middleware-Stack

| Middleware | Datei | Funktion |
|---|---|---|
| `auth:sanctum` | Laravel Built-in | Prüft ob gültiger Token vorhanden |
| `EnsureTaskOwner` | `app/Http/Middleware/EnsureTaskOwner.php` | Verhindert Zugriff auf Tasks anderer Benutzer |
| `EnsureNotOverdueOrAdmin` | `app/Http/Middleware/EnsureNotOverdueOrAdmin.php` | Nur Admins dürfen überfällige Tasks bearbeiten |

**Angewendete Middleware pro Route:**

- `POST /tasks`, `GET /tasks` → `auth:sanctum`
- `PUT /tasks/{id}`, `DELETE /tasks/{id}` → `auth:sanctum` + `EnsureTaskOwner` + `EnsureNotOverdueOrAdmin`
- `GET /tasks/{id}` → `auth:sanctum` + `EnsureTaskOwner`

---

## Rollen & Berechtigungen

| Aktion | `user` | `admin` |
|---|---|---|
| Eigene Tasks erstellen | ✅ | ✅ |
| Eigene Tasks lesen | ✅ | ✅ |
| Eigene Tasks bearbeiten | ✅ | ✅ |
| Eigene Tasks löschen | ✅ | ✅ |
| Fremde Tasks bearbeiten | ❌ | ✅ |
| Überfällige Tasks bearbeiten | ❌ | ✅ |
| Alle Tasks im System sehen | ❌ | ✅ |

### Admin-Benutzer anlegen

Methode 1 – direkt per SQL (z. B. in DBeaver):

```sql
UPDATE users SET role = 'admin' WHERE email = 'deine@email.com';
```

Methode 2 – via Artisan (Docker):

```bash
docker compose exec app php artisan tinker
# In Tinker:
\App\Models\User::where('email', 'deine@email.com')->update(['role' => 'admin']);
```

Nach der Rollenänderung erneut einloggen, um einen Token mit den neuen Berechtigungen zu erhalten.

---

## Validierungsregeln

### Tasks

| Feld | Regel | Beschreibung |
|---|---|---|
| `title` | `required`, `string`, `max:255` | Erforderlich, max. 255 Zeichen |
| `description` | `required`, `string` | Erforderlich |
| `status` | `required`, `in:todo,in_progress,done` | Nur diese drei Werte erlaubt |
| `deadline` | `nullable`, `date`, `after:now` | Optionales Datum, muss in der Zukunft liegen |
| `project_id` | `nullable`, `exists:projects,id` | Optional, Projekt muss existieren |

### Projects

| Feld | Regel | Beschreibung |
|---|---|---|
| `name` | `required`, `string`, `max:255` | Erforderlich, max. 255 Zeichen |
| `description` | `nullable`, `string` | Optional |

### Fehlerantwort bei Validierungsfehler (422)

```json
{
  "message": "Die übermittelten Daten sind ungültig.",
  "errors": {
    "status": ["Der ausgewählte Status ist ungültig."],
    "deadline": ["Das Fälligkeitsdatum muss ein Datum in der Zukunft sein."]
  }
}
```

---

## Events & Notifications

Das System implementiert einen vollständigen Laravel Event/Listener-Zyklus:

### Ablauf

```
Task wird aktualisiert (PUT /tasks/{id})
        │
        ▼
TaskUpdated Event wird gefeuert
        │
        ▼
CheckTaskDeadlineListener prüft: Ist deadline < now()?
        │
    Ja  ▼
TaskDeadlineNotification wird an den Task-Eigentümer gesendet
(E-Mail via konfiguriertem MAIL_MAILER)
```

### Konfiguration

Für E-Mail-Benachrichtigungen muss `MAIL_MAILER` in der `.env` konfiguriert sein (siehe [Umgebungsvariablen](#umgebungsvariablen)).

Zum lokalen Testen ohne SMTP-Server:
```env
MAIL_MAILER=log
```
→ Mails werden dann in `storage/logs/laravel.log` geschrieben.

---

## HTTP-Statuscodes

| Code | Bedeutung | Wann |
|---|---|---|
| `200 OK` | Erfolgreich | GET, PUT, DELETE |
| `201 Created` | Erstellt | POST (Task/Project/Register) |
| `204 No Content` | Gelöscht | DELETE (ohne Body) |
| `401 Unauthorized` | Nicht authentifiziert | Kein oder ungültiger Token |
| `403 Forbidden` | Keine Berechtigung | Fremder Task, nicht-Admin bearbeitet überfälligen Task |
| `404 Not Found` | Nicht gefunden | Task/Project-ID existiert nicht |
| `422 Unprocessable Entity` | Validierungsfehler | Ungültige Eingaben |

---

## Tests

Das Projekt verfügt über **116 Tests** mit **270 Assertions**.

Tests verwenden eine **SQLite In-Memory Datenbank** – kein Einfluss auf die Produktionsdatenbank.

### Tests ausführen

**Mit Docker:**

```bash
docker compose exec app php artisan test
```

**Lokal:**

```bash
php artisan test
```

### Gefiltert ausführen

```bash
# Nur Feature-Tests
php artisan test --filter=Feature

# Nur Unit-Tests
php artisan test --filter=Unit

# Einzelne Test-Klasse
php artisan test --filter=AuthTest
php artisan test --filter=TaskCrudTest
php artisan test --filter=ProjectCrudTest
```

### Test-Abdeckung

| Kategorie | Test-Klasse | Tests | Was wird getestet |
|---|---|---|---|
| Feature | `AuthTest` | 14 | Registrierung, Login, Logout, Validierung, doppelte E-Mail |
| Feature | `TaskCrudTest` | 35 | CRUD, Validierung, Zugriffsrechte, 404, Relations, overdue |
| Feature | `ProjectCrudTest` | 22 | CRUD, Validierung, Zugriffsrechte, 404 |
| Unit | `TaskModelTest` | 6 | Eloquent-Beziehungen, Fillable-Felder |
| Unit | `ProjectModelTest` | 5 | Eloquent-Beziehungen, Fillable-Felder |
| Unit | `UserModelTest` | 6 | Beziehungen, `isAdmin()`-Methode |
| Unit | `TaskUpdatedEventTest` | 3 | Event-Erstellung, Dispatch |
| Unit | `CheckTaskDeadlineListenerTest` | 3 | Notification-Trigger bei abgelaufener Deadline |
| Unit | `TaskDeadlineNotificationTest` | 4 | Mail/Array-Format, ShouldQueue |
| Unit | `TaskFormRequestTest` | 10 | Store/Update Validierungsregeln vollständig |
| Unit | `ProjectFormRequestTest` | 8 | Store/Update Validierungsregeln vollständig |

Detaillierte Test-Dokumentation: [docs/testing.md](docs/testing.md)

---

## CI/CD

Das Projekt verwendet **GitHub Actions** für automatisierte Qualitätssicherung:

| Workflow | Auslöser | Beschreibung |
|---|---|---|
| **CI** | Push / Pull Request | PHPUnit Tests auf PHP 8.2, 8.3, 8.4 + Laravel Pint Code Style |
| **Docker** | Push auf `main` | Docker Image Build & Push zu GitHub Container Registry |
| **Security** | Wöchentlich + Push | Composer Audit + Dependency Security Scanner |

**Dependabot** aktualisiert automatisch Abhängigkeiten für Composer, npm und GitHub Actions.

Details: [docs/github_actions.md](docs/github_actions.md)

---

## Pull Requests

Die Aufgabenstellung wurde in separaten Pull Requests umgesetzt:

| PR | Inhalt |
|---|---|
| **PR #1 – Grundprüfung** | Datenbankmodell, Eloquent-Modelle, Controller, RESTful-Routen, Sanctum-Auth, Middleware, PHPUnit-Tests |
| **PR #2 – Erweiterte Prüfung** | Deadline-Feld, Projekt-Modell, Beziehungen, overdue-Endpoint, Admin-Rolle, Event/Listener/Notification |
| **PR #3 – Zusatzaufgabe** | Erweiterte Validierungsregeln (Status-Enum, Deadline after:now, Titel max:255) |


---

## Weitere Dokumentation

| Dokument | Beschreibung |
|---|---|
| [docs/api_dokumentation.md](docs/api_dokumentation.md) | Vollständige API-Referenz mit Request/Response-Beispielen |
| [docs/eloquent_modelle.md](docs/eloquent_modelle.md) | Detaillierte Beschreibung der Eloquent-Modelle und Beziehungen |
| [docs/testing.md](docs/testing.md) | Teststrategien, Testaufbau und Ausführungsdetails |
| [docs/github_actions.md](docs/github_actions.md) | CI/CD-Pipeline Beschreibung |