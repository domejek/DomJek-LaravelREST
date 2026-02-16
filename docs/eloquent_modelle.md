# Eloquent Modelle

## Übersicht

Die Eloquent-Modelle bilden die Datenbanktabellen auf PHP-Klassen ab und ermöglichen den Zugriff auf die Datenbank mittels objektorientierter Methoden.

## Modelle

### 1. User (app/Models/User.php)

Das User-Modell repräsentiert Benutzer des Systems.

**Beziehungen:**
- `tasks()`: 1:n Beziehung zu Tasks (ein User hat viele Tasks)
- `projects()`: 1:n Beziehung zu Projects (ein User hat viele Projects)

**Methoden:**
- `isAdmin()`: Prüft ob der Benutzer Admin-Rechte hat

**Felder:**
- id, name, email, password, role, timestamps

---

### 2. Task (app/Models/Task.php)

Das Task-Modell repräsentiert Aufgaben im System.

**Beziehungen:**
- `user()`: n:1 Beziehung zu User (eine Task gehört einem User)
- `project()`: n:1 Beziehung zu Project (eine Task gehört zu einem Project)

**Felder:**
- id, user_id, project_id, title, description, status, deadline, timestamps

**Casts:**
- `deadline` wird als datetime gecastet

---

### 3. Project (app/Models/Project.php)

Das Project-Modell repräsentiert Projekte im System.

**Beziehungen:**
- `tasks()`: 1:n Beziehung zu Tasks (ein Project hat viele Tasks)
- `user()`: n:1 Beziehung zu User (ein Project gehört einem User)

**Felder:**
- id, name, description, timestamps

---

## Beziehungen (ERD)

```
User (1) ----< (n) Task
     |
     ----< (n) Project
               |
               ----< (n) Task
```

- Ein User kann viele Tasks erstellen (1:n)
- Ein User kann viele Projects haben (1:n)
- Ein Project kann viele Tasks haben (1:n)
- Eine Task gehört zu einem User (n:1)
- Eine Task gehört zu einem Project (n:1)
- Ein Project gehört zu einem User (n:1)

---

## Verwendung

```php
// Alle Tasks eines Users
$user->tasks;

// Alle Tasks eines Projekts
$project->tasks;

// Der Owner eines Tasks
$task->user;

// Das Projekt einer Task
$task->project;

// Alle überfälligen Tasks eines Users
$user->tasks()->where('deadline', '<', now())->get();
```
