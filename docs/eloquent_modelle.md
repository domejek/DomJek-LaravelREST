# Eloquent Modelle

## Übersicht

Die Eloquent-Modelle bilden die Datenbanktabellen auf PHP-Klassen ab und ermöglichen den Zugriff auf die Datenbank mittels objektorientierter Methoden.

## Modelle

### 1. User (app/Models/User.php)

Das User-Modell repräsentiert Benutzer des Systems.

**Beziehungen:**
- `tasks()`: 1:n Beziehung zu Tasks (ein Benutzer hat viele Aufgaben)
- `projects()`: 1:n Beziehung zu Projects (ein Benutzer hat viele Projekte)

**Methoden:**
- `isAdmin()`: Prüft ob der Benutzer Admin-Rechte hat

**Felder:**
- id, name, email, password, role, timestamps

---

### 2. Task (app/Models/Task.php)

Das Task-Modell repräsentiert Aufgaben im System.

**Beziehungen:**
- `user()`: n:1 Beziehung zu User (eine Aufgabe gehört einem Benutzer)
- `project()`: n:1 Beziehung zu Project (eine Aufgabe gehört zu einem Projekt)

**Felder:**
- id, user_id, project_id, title, description, status, deadline, timestamps

**Casts:**
- `deadline` wird als datetime gecastet

---

### 3. Project (app/Models/Project.php)

Das Project-Modell repräsentiert Projekte im System.

**Beziehungen:**
- `tasks()`: 1:n Beziehung zu Tasks (ein Projekt hat viele Aufgaben)
- `user()`: n:1 Beziehung zu User (ein Projekt gehört einem Benutzer)

**Felder:**
- id, user_id, name, description, timestamps

---

## Beziehungen (ERD)

```
Benutzer (1) ----< (n) Aufgabe
     |
     ----< (n) Projekt
               |
               ----< (n) Aufgabe
```

- Ein Benutzer kann viele Aufgaben erstellen (1:n)
- Ein Benutzer kann viele Projekte haben (1:n)
- Ein Projekt kann viele Aufgaben haben (1:n)
- Eine Aufgabe gehört zu einem Benutzer (n:1)
- Eine Aufgabe gehört zu einem Projekt (n:1)
- Ein Projekt gehört zu einem Benutzer (n:1)

---

## Verwendung

```php
// Alle Aufgaben eines Benutzers
$benutzer->tasks;

// Alle Aufgaben eines Projekts
$projekt->tasks;

// Der Owner einer Aufgabe
$aufgabe->user;

// Das Projekt einer Aufgabe
$aufgabe->project;

// Alle überfälligen Aufgaben eines Benutzers
$benutzer->tasks()->where('deadline', '<', now())->get();
```

---

## Tests

Die Tests befinden sich unter `/tests/Unit/` und testen folgende Szenarien:

### Model Tests

#### TaskModelTest (6 Tests)
- Aufgabe gehört zu einem Benutzer
- Aufgabe gehört zu einem Projekt
- Aufgabe kann ein Fälligkeitsdatum haben
- Aufgabe hat ausfüllbare Attribute
- Aufgabe kann mit Factory erstellt werden
- Aufgabe hat Standard-Status

#### ProjectModelTest (5 Tests)
- Projekt gehört zu einem Benutzer
- Projekt hat viele Aufgaben
- Projekt hat ausfüllbare Attribute
- Projekt kann mit Factory erstellt werden
- Projekt kann null Beschreibung haben

#### UserModelTest (6 Tests)
- Benutzer hat viele Aufgaben
- Benutzer hat viele Projekte
- isAdmin() gibt true für Admin zurück
- isAdmin() gibt false für normalen Benutzer zurück
- Benutzer hat Standard-Rolle
- Benutzer hat ausfüllbare Attribute

### Event & Listener Tests

#### TaskUpdatedEventTest (3 Tests)
- Event wird mit Task erstellt
- Event wird mit Task und alter Deadline erstellt
- Event ist dispatchable

#### CheckTaskDeadlineListenerTest (3 Tests)
- Listener sendet Benachrichtigung bei überfälliger Aufgabe
- Listener sendet keine Benachrichtigung bei zukünftiger Deadline
- Listener sendet keine Benachrichtigung ohne Deadline

#### TaskDeadlineNotificationTest (4 Tests)
- Notification verwendet Mail-Kanal
- Notification Mail enthält Task-Titel
- Notification toArray enthält Task-Daten
- Notification implementiert ShouldQueue

### Form Request Tests

#### TaskFormRequestTest (10 Tests)
- Store/Update Request authorize
- Validierungsregeln für title, description, status, deadline, project_id
- sometimes Validierung bei Update

#### ProjectFormRequestTest (8 Tests)
- Store/Update Request authorize
- Validierungsregeln für name, description
- sometimes Validierung bei Update

### Factories

Unter `/database/factories/` befinden sich:
- `UserFactory.php`: Erstellt Benutzer mit Standard-Rolle "user", unterstützt `admin()` Methode
- `ProjectFactory.php`: Erstellt Projekt mit zufälligen Daten
- `TaskFactory.php`: Erstellt Aufgabe mit zufälligen Daten, unterstützt `erledigt()`, `inArbeit()`, `ueberfaellig()` Methoden

### Detaillierte Test-Dokumentation

Siehe: [testing.md](testing.md)
