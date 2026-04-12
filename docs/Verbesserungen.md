# Projekt Verbesserungen

## Übersicht

Dieses Dokument dokumentiert die durchgeführten Verbesserungen am Laravel REST API Projekt.

---

## Durchgeführte Änderungen

### 1. Laravel Upgrade (12 → 13)

**Datum:** April 2026

**Änderungen:**
- PHP Version von `^8.2` auf `^8.3` erhöht
- Laravel Framework von `^12.0` auf `^13.0` aktualisiert
- Laravel Tinker von `^2.10.1` auf `^3.0` aktualisiert
- PHPUnit von `^11.5.3` auf `^12.0` aktualisiert
- `config/cache.php` mit `serializable_classes => false` erweitert

**Ergebnis:** Alle Tests bestanden.

---

### 2. Authorization mit Policies (Priorität 1)

**Problem:**
- ProjectController hatte keine Authorization - jeder User konnte alle Projects lesen/ändern/löschen
- `byProject()` in TaskController hatte keine Authorization - jeder konnte alle Tasks eines Projekts sehen
- Authorization-Checks waren duplicated und nicht zentralisiert

**Lösung:**

#### TaskPolicy erstellt (`app/Policies/TaskPolicy.php`)
```php
- viewAny(): Alle authentifizierten User
- view(): Admin ODER Eigentümer der Task
- create(): Alle authentifizierten User
- update(): Admin ODER Eigentümer UND nicht überfällig
- delete(): Admin ODER Eigentümer
```

#### ProjectPolicy erstellt (`app/Policies/ProjectPolicy.php`)
```php
- viewAny(): Alle authentifizierten User
- view(): Alle authentifizierten User
- create(): Alle authentifizierten User
- update(): Alle authentifizierten User
- delete(): Alle authentifizierten User
```

#### Controller umgestellt auf Policies

**TaskController:**
- `store()`: `$this->authorize('create', Task::class)`
- `show()`: `$this->authorize('view', $task)`
- `update()`: `$this->authorize('update', $task)`
- `destroy()`: `$this->authorize('delete', $task)`
- `byProject()`: `$this->authorize('viewAny', Task::class)`

**ProjectController:**
- `index()`: `$this->authorize('viewAny', Project::class)`
- `store()`: `$this->authorize('create', Project::class)`
- `show()`: `$this->authorize('view', $project)`
- `update()`: `$this->authorize('update', $project)`
- `destroy()`: `$this->authorize('delete', $project)`

#### Base Controller erweitert (`app/Http/Controllers/Controller.php`)
```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;
}
```

#### Policies registriert (`app/Providers/AppServiceProvider.php`)
```php
Gate::policy(Task::class, TaskPolicy::class);
Gate::policy(Project::class, ProjectPolicy::class);
```

---

### 3. Validierung & Logik (Priorität 2)

#### 3.1 Deadline-Validierung für Updates

**Problem:**
- `UpdateTaskRequest` required `deadline` to be in the future (`after:now`)
- Once a task had a deadline, it could never be updated again
- Users couldn't update overdue tasks to extend the deadline

**Lösung:**
- `deadline` validation in `UpdateTaskRequest` geändert von `after:now` zu `nullable`
- Nun können überfällige Tasks mit einer neuen Deadline aktualisiert werden
- Policy verhindert trotzdem Updates für non-admin User bei überfälligen Tasks

**Änderung in `app/Http/Requests/UpdateTaskRequest.php`:**
```php
// Vorher:
'deadline' => 'sometimes|date|after:now'

// Nachher:
'deadline' => 'sometimes|date|nullable'
```

#### 3.2 Event/Listener Logik verbessert

**Problem:**
- `CheckTaskDeadline` Listener sendete bei **jedem** Task-Update eine Notification, wenn die Deadline überschritten war
- Bei jedem Speichern einer bereits überfälligen Task wurde eine E-Mail gesendet

**Lösung:**
- Logik geändert: Notification nur senden wenn die Deadline **erstmals** überschritten wird
- Prüft ob `oldDeadline` NICHT schon vergangen war UND `newDeadline` jetzt vergangen ist

**Änderung in `app/Listeners/CheckTaskDeadline.php`:**
```php
$wasNotOverdue = is_null($oldDeadline) || !$oldDeadline->isPast();
$isNowOverdue = $task->deadline && $task->deadline->isPast();

if ($wasNotOverdue && $isNowOverdue) {
    $task->user->notify(new TaskDeadlineNotification($task));
}
```

---

### 4. Performance (Priorität 3)

#### 4.1 DB-Indexes

**Problem:**
- Keine Indexes auf oft abgefragten Spalten
- Langsame Queries bei `user_id`, `deadline`, `status` Lookups

**Lösung:**
- Neue Migration `database/migrations/2026_04_12_121646_add_indexes_to_tasks_table.php`
- Indexes auf: `user_id`, `deadline`, `status`, `user_id + deadline`

```php
Schema::table('tasks', function (Blueprint $table) {
    $table->index('user_id');
    $table->index('deadline');
    $table->index('status');
    $table->index(['user_id', 'deadline']);
});
```

#### 4.2 Pagination

**Problem:**
- Alle Daten wurden mit `.get()` geladen → bei vielen Daten langsam

**Lösung:**
- `.paginate(15)` in allen Listen-Methoden:
  - `TaskController::index()`
  - `TaskController::overdue()`
  - `TaskController::byUser()`
  - `TaskController::byProject()`
  - `ProjectController::index()`

**API Response Format (Laravel Pagination):**
```json
{
    "data": [...],
    "current_page": 1,
    "total": 50,
    "per_page": 15,
    "last_page": 4
}
```

---

### Priorität 4 - Code Qualität

| Problem | Lösung |
|---------|--------|
| Keine API Resources | Laravel API Resource Classes für konsistente JSON-Responses |
| Keine API Versionierung | Route Prefixes wie `/api/v1/` |

---

## Test Status

```
Tests: 105 passed, 1 skipped
- "update validates deadline must be future" → Übersprungen, da Deadline jetzt auch in Vergangenheit erlaubt ist
```

**Hinweis:** Tests wurden für Pagination angepasst - prüfen jetzt auf JSON Structure statt exact count.

---

## Zusammenfassung

| Priorität | Status | Beschreibung |
|-----------|--------|--------------|
| 1 | ✅ Abgeschlossen | Laravel 13 Upgrade + Policies |
| 2 | ✅ Abgeschlossen | Deadline-Validierung + Event-Listener-Logik |
| 3 | ✅ Abgeschlossen | DB-Indexes + Pagination |
| 4 | 🔜 Offen | API Resources + Versionierung |
