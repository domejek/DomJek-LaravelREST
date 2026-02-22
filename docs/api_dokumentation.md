# Task Management API Dokumentation

## Übersicht

Diese API ermöglicht das Verwalten von Aufgaben (Tasks) mit vollem CRUD-Support. Alle Endpoints (außer Register/Login) erfordern eine Authentifizierung via Laravel Sanctum Token.

**Basis-URL:**
- Docker: `http://localhost:8080/api`
- Lokal: `http://localhost:8000/api`

> **Wichtig:** Immer den `Accept: application/json` Header senden, um JSON-Responses zu erhalten und Redirects bei Validierungsfehlern zu vermeiden.

---

## Docker Setup

### Container starten
```bash
./build.sh
```

### Container verwalten
```bash
# Container stoppen
docker-compose down

# Logs anzeigen
docker-compose logs -f app

# In Container einloggen
docker-compose exec app bash

# Tests ausführen
docker-compose exec app php artisan test
```

### Verfügbare Services
| Service | Port | Beschreibung |
|---------|------|--------------|
| nginx | 8080 | Web Server |
| app | 9000 | PHP-FPM |
| db | 3306 | MySQL 8.0 |

---

## Authentifizierung

### Benutzer registrieren

**Endpoint:** `POST /api/register`

**Request Body:**
```json
{
    "name": "Max Mustermann",
    "email": "max@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201):**
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

---

### Benutzer anmelden

**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
    "email": "max@example.com",
    "password": "password123"
}
```

**Response (200):**
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

---

### Benutzer abmelden

**Endpoint:** `POST /api/logout`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
    "message": "Logged out successfully"
}
```

---

### Aktuellen Benutzer abrufen

**Endpoint:** `GET /api/user`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
    "id": 1,
    "name": "Max Mustermann",
    "email": "max@example.com",
    "role": "user"
}
```

---

## CRUD Operationen für Tasks

### 1. Alle Aufgaben auflisten

**Endpoint:** `GET /api/tasks`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
[
    {
        "id": 1,
        "user_id": 1,
        "project_id": null,
        "title": "Aufgabe 1",
        "description": "Beschreibung der Aufgabe",
        "status": "todo",
        "deadline": "2026-02-20T12:00:00.000000Z",
        "created_at": "2026-02-17T10:00:00.000000Z",
        "updated_at": "2026-02-17T10:00:00.000000Z",
        "user": {...},
        "project": null
    }
]
```

---

### 2. Einzelne Aufgabe anzeigen

**Endpoint:** `GET /api/tasks/{id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
    "id": 1,
    "user_id": 1,
    "project_id": null,
    "title": "Aufgabe 1",
    "description": "Beschreibung der Aufgabe",
    "status": "todo",
    "deadline": "2026-02-20T12:00:00.000000Z",
    "created_at": "2026-02-17T10:00:00.000000Z",
    "updated_at": "2026-02-17T10:00:00.000000Z",
    "user": {...},
    "project": null
}
```

**Error Response (403):**
```json
{
    "message": "Unauthorized"
}
```

---

### 3. Aufgabe erstellen

**Endpoint:** `POST /api/tasks`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**
```json
{
    "title": "Neue Aufgabe",
    "description": "Detaillierte Beschreibung",
    "status": "todo",
    "deadline": "2026-02-25 14:30:00",
    "project_id": 1
}
```

**Validierungsregeln:**
| Feld | Regel | Beschreibung |
|------|-------|--------------|
| title | required, max:255 | Erforderlich, maximal 255 Zeichen |
| description | required | Erforderlich |
| status | required, in:todo,in_progress,done | Nur erlaubte Werte |
| deadline | required, date, after:now | Gültiges Datum in der Zukunft |
| project_id | nullable, exists:projects,id | Optional, muss existieren |

**Response (201):**
```json
{
    "id": 2,
    "user_id": 1,
    "project_id": 1,
    "title": "Neue Aufgabe",
    "description": "Detaillierte Beschreibung",
    "status": "todo",
    "deadline": "2026-02-25T14:30:00.000000Z",
    "created_at": "2026-02-17T12:00:00.000000Z",
    "updated_at": "2026-02-17T12:00:00.000000Z",
    "user": {...},
    "project": {...}
}
```

**Error Response (422) - Validierungsfehler:**
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "title": ["The title field is required."],
        "status": ["The status field must be one of: todo, in_progress, done."]
    }
}
```

---

### 4. Aufgabe aktualisieren

**Endpoint:** `PUT /api/tasks/{id}` oder `PATCH /api/tasks/{id}`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body (alle Felder optional):**
```json
{
    "title": "Aktualisierter Titel",
    "status": "in_progress",
    "deadline": "2026-03-01 09:00:00"
}
```

**Validierungsregeln (Update):**
| Feld | Regel | Beschreibung |
|------|-------|--------------|
| title | sometimes, max:255 | Maximal 255 Zeichen |
| description | sometimes | - |
| status | sometimes, in:todo,in_progress,done | Nur erlaubte Werte |
| deadline | sometimes, date, after:now | Gültiges Datum in der Zukunft |
| project_id | nullable, exists:projects,id | Optional |

**Response (200):**
```json
{
    "id": 2,
    "user_id": 1,
    "project_id": 1,
    "title": "Aktualisierter Titel",
    "description": "Detaillierte Beschreibung",
    "status": "in_progress",
    "deadline": "2026-03-01T09:00:00.000000Z",
    ...
}
```

**Error Response (403) - Überfällige Aufgabe:**
```json
{
    "message": "Cannot update overdue tasks"
}
```

---

### 5. Aufgabe löschen

**Endpoint:** `DELETE /api/tasks/{id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (204):**
```
(No content)
```

**Error Response (403):**
```json
{
    "message": "Unauthorized"
}
```

---

## Zusätzliche Endpoints

### Überfällige Aufgaben

**Endpoint:** `GET /api/tasks/overdue`

**Headers:**
```
Authorization: Bearer <token>
```

**Beschreibung:** Gibt alle Aufgaben zurück, deren Deadline abgelaufen ist.

---

### Aufgaben eines Benutzers

**Endpoint:** `GET /api/users/{id}/tasks`

**Headers:**
```
Authorization: Bearer <token>
```

**Beschreibung:** Gibt alle Aufgaben eines bestimmten Benutzers zurück. Normale Benutzer können nur ihre eigenen Aufgaben abrufen. Admins können alle Benutzer abfragen.

---

### Aufgaben eines Projekts

**Endpoint:** `GET /api/projects/{id}/tasks`

**Headers:**
```
Authorization: Bearer <token>
```

**Beschreibung:** Gibt alle Aufgaben eines bestimmten Projekts zurück.

---

## Rollen und Berechtigungen

### Rollen
- **user** (Standard): Kann nur eigene Aufgaben erstellen, bearbeiten und löschen
- **admin**: Kann alle Aufgaben im System verwalten

### Berechtigungen
- Normale Benutzer können überfällige Aufgaben nicht bearbeiten
- Admins können alle Aufgaben (auch überfällige) bearbeiten

---

## Beispiel: Vollständiger Workflow

### Schritt 1: Registrierung
```bash
curl -X POST http://localhost:8080/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"name":"Test User","email":"test@example.com","password":"password123","password_confirmation":"password123"}'
```

### Schritt 2: Anmeldung (oder Token aus Registrierung verwenden)
```bash
curl -X POST http://localhost:8080/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
```

### Schritt 3: Task erstellen
```bash
curl -X POST http://localhost:8080/api/tasks \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"title":"Meine erste Aufgabe","description":"Beschreibung","status":"todo","deadline":"2026-03-01 12:00:00"}'
```

### Schritt 4: Tasks abrufen
```bash
curl -X GET http://localhost:8080/api/tasks \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json"
```

### Schritt 5: Task aktualisieren
```bash
curl -X PUT http://localhost:8080/api/tasks/1 \
  -H "Authorization: Bearer <token>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"status":"in_progress"}'
```

### Schritt 6: Task löschen
```bash
curl -X DELETE http://localhost:8080/api/tasks/1 \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json"
```

---

## Postman-Konfiguration

1. **Neue Collection erstellen** → "Task API"
2. **Environment Variable hinzufügen:** `base_url = http://localhost:8080/api`
3. **Authentifizierung:**
   - Nach Login/Register: Token kopieren
   - In Collection → Auth → Bearer Token → `{{token}}` setzen
4. **Headers setzen:**
   - `Accept: application/json` (für alle Requests)

### Beispiel-Requests:

| Methode | Endpoint | Beschreibung |
|---------|----------|--------------|
| POST | {{base_url}}/register | Registrieren |
| POST | {{base_url}}/login | Anmelden |
| POST | {{base_url}}/logout | Abmelden |
| GET | {{base_url}}/tasks | Alle Tasks |
| POST | {{base_url}}/tasks | Task erstellen |
| GET | {{base_url}}/tasks/{id} | Task anzeigen |
| PUT | {{base_url}}/tasks/{id} | Task aktualisieren |
| DELETE | {{base_url}}/tasks/{id} | Task löschen |
| GET | {{base_url}}/tasks/overdue | Überfällige Tasks |
| GET | {{base_url}}/users/{id}/tasks | Tasks nach Benutzer |
| GET | {{base_url}}/projects/{id}/tasks | Tasks nach Projekt |
| GET | {{base_url}}/projects | Alle Projekte |
| POST | {{base_url}}/projects | Projekt erstellen |
| GET | {{base_url}}/projects/{id} | Projekt anzeigen |
| PUT | {{base_url}}/projects/{id} | Projekt aktualisieren |
| DELETE | {{base_url}}/projects/{id} | Projekt löschen |

---

## Error Codes

| Code | Beschreibung |
|------|--------------|
| 200 | Erfolgreich |
| 201 | Erstellt |
| 204 | Kein Inhalt |
| 400 | Bad Request |
| 401 | Unauthorized (nicht eingeloggt) |
| 403 | Forbidden (keine Berechtigung) |
| 404 | Nicht gefunden |
| 422 | Validierungsfehler |
| 500 | Server Error |

---

## CRUD Operationen für Projekte

### 1. Alle Projekte auflisten

**Endpoint:** `GET /api/projects`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
[
    {
        "id": 1,
        "user_id": 1,
        "name": "Projekt 1",
        "description": "Beschreibung des Projekts",
        "created_at": "2026-02-17T10:00:00.000000Z",
        "updated_at": "2026-02-17T10:00:00.000000Z",
        "user": {...}
    }
]
```

---

### 2. Einzelnes Projekt anzeigen

**Endpoint:** `GET /api/projects/{id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (200):**
```json
{
    "id": 1,
    "user_id": 1,
    "name": "Projekt 1",
    "description": "Beschreibung des Projekts",
    "created_at": "2026-02-17T10:00:00.000000Z",
    "updated_at": "2026-02-17T10:00:00.000000Z",
    "user": {...},
    "tasks": [...]
}
```

---

### 3. Projekt erstellen

**Endpoint:** `POST /api/projects`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Neues Projekt",
    "description": "Projektbeschreibung (optional)"
}
```

**Validierungsregeln:**
| Feld | Regel | Beschreibung |
|------|-------|--------------|
| name | required, max:255 | Erforderlich, maximal 255 Zeichen |
| description | nullable, string | Optional |

**Response (201):**
```json
{
    "id": 2,
    "user_id": 1,
    "name": "Neues Projekt",
    "description": "Projektbeschreibung",
    "created_at": "2026-02-17T12:00:00.000000Z",
    "updated_at": "2026-02-17T12:00:00.000000Z",
    "user": {...}
}
```

---

### 4. Projekt aktualisieren

**Endpoint:** `PUT /api/projects/{id}` oder `PATCH /api/projects/{id}`

**Headers:**
```
Authorization: Bearer <token>
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Aktualisierter Name",
    "description": "Aktualisierte Beschreibung"
}
```

**Response (200):**
```json
{
    "id": 2,
    "user_id": 1,
    "name": "Aktualisierter Name",
    "description": "Aktualisierte Beschreibung",
    ...
}
```

---

### 5. Projekt löschen

**Endpoint:** `DELETE /api/projects/{id}`

**Headers:**
```
Authorization: Bearer <token>
```

**Response (204):**
```
(No content)
```

---

## Event-Listener und Benachrichtigungen

### Task Updated Event

Wenn eine Aufgabe aktualisiert wird, wird automatisch das `TaskUpdated` Event ausgelöst.

**Event:** `App\Events\TaskUpdated`

**Listener:** `App\Listeners\CheckTaskDeadline`

### Benachrichtigung bei überfälliger Deadline

Wenn eine Aufgabe aktualisiert wird und die Deadline abgelaufen ist, wird eine E-Mail-Benachrichtigung an den Benutzer gesendet.

**Notification:** `App\Notifications\TaskDeadlineNotification`

**Betreff:** "Task Deadline Passed"

**Inhalt:**
- Titel der Aufgabe
- Hinweis, dass die Deadline überschritten wurde
- Link zur Aufgabe

---

## Test-Dokumentation

### Alle Tests ausführen

```bash
php artisan test
```

### Nach Kategorie filtern

```bash
# Nur Auth-Tests
php artisan test --filter=AuthTest

# Nur Task-Tests
php artisan test --filter=TaskCrudTest

# Nur Project-Tests
php artisan test --filter=ProjectCrudTest

# Nur Unit-Tests
php artisan test --filter=Unit
```

### Test-Abdeckung

**Gesamt: 116 Tests, 270 Assertions**

| Test-Suite | Tests | Beschreibung |
|------------|-------|-------------|
| **Feature Tests** | | |
| AuthTest | 14 | Registrierung, Login, Logout, Validierung |
| TaskCrudTest | 35 | CRUD + Validierung + Rechte + 404 |
| ProjectCrudTest | 22 | CRUD + Validierung + Rechte + 404 |
| **Unit Tests** | | |
| TaskModelTest | 6 | Model-Beziehungen, Fillable |
| ProjectModelTest | 5 | Model-Beziehungen, Fillable |
| UserModelTest | 6 | Model-Beziehungen, isAdmin() |
| TaskUpdatedEventTest | 3 | Event-Erstellung, Dispatch |
| CheckTaskDeadlineListenerTest | 3 | Notification-Trigger |
| TaskDeadlineNotificationTest | 4 | Mail/Array Format, ShouldQueue |
| TaskFormRequestTest | 10 | Store/Update Validierungsregeln |
| ProjectFormRequestTest | 8 | Store/Update Validierungsregeln |

### Detaillierte Test-Dokumentation

Siehe: [testing.md](testing.md)
