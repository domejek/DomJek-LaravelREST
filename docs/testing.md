# Testing Dokumentation

## Übersicht

Das Projekt verfügt über eine umfassende PHPUnit Test-Suite mit **116 Tests** und **270 Assertions**.

## Test-Struktur

```
tests/
├── Feature/                          # Feature/Integration Tests
│   ├── AuthTest.php                  # Authentifizierung (14 Tests)
│   ├── TaskCrudTest.php              # Task CRUD + Rechte (35 Tests)
│   └── ProjectCrudTest.php           # Project CRUD + Rechte (22 Tests)
├── Unit/                             # Unit Tests
│   ├── TaskModelTest.php             # Task Model (6 Tests)
│   ├── ProjectModelTest.php          # Project Model (5 Tests)
│   ├── UserModelTest.php             # User Model (6 Tests)
│   ├── TaskUpdatedEventTest.php      # Event Tests (3 Tests)
│   ├── CheckTaskDeadlineListenerTest.php  # Listener Tests (3 Tests)
│   ├── TaskDeadlineNotificationTest.php   # Notification Tests (4 Tests)
│   ├── TaskFormRequestTest.php       # Task Request Validation (10 Tests)
│   └── ProjectFormRequestTest.php    # Project Request Validation (8 Tests)
└── TestCase.php                      # Basis Test-Klasse
```

## Tests ausführen

### Alle Tests
```bash
php artisan test
# oder
composer test
```

### Gefilterte Tests
```bash
# Nur Feature-Tests
php artisan test --filter=Feature

# Nur Unit-Tests
php artisan test --filter=Unit

# Spezifische Test-Klasse
php artisan test --filter=AuthTest
php artisan test --filter=TaskCrudTest
php artisan test --filter=TaskModelTest

# Mit Parallelausführung
php artisan test --parallel
```

### Test mit Coverage
```bash
php artisan test --coverage
```

---

## Feature Tests

### AuthTest (14 Tests)

| Test | Beschreibung |
|------|--------------|
| `test_user_can_register` | Erfolgreiche Registrierung mit Token |
| `test_registration_validates_required_fields` | Name, Email, Password Pflichtfelder |
| `test_registration_validates_unique_email` | Keine doppelten E-Mails |
| `test_registration_validates_password_confirmation` | Password muss bestätigt werden |
| `test_registration_validates_password_min_length` | Mindestlänge Password |
| `test_registration_validates_email_format` | Gültiges E-Mail Format |
| `test_user_can_login` | Erfolgreicher Login mit Token |
| `test_login_fails_with_invalid_credentials` | Falsche Credentials |
| `test_login_validates_required_fields` | Email, Password Pflichtfelder |
| `test_login_validates_email_format` | Gültiges E-Mail Format |
| `test_user_can_logout` | Token wird gelöscht |
| `test_logout_without_token_returns_401` | Logout ohne Auth |
| `test_unauthenticated_user_cannot_access_protected_routes` | 401 Response |
| `test_new_user_has_default_role` | Rolle "user" bei Registrierung |

---

### TaskCrudTest (35 Tests)

#### CRUD Operationen
| Test | Beschreibung |
|------|--------------|
| `test_user_can_create_task` | Task erstellen mit allen Feldern |
| `test_user_can_view_all_own_tasks` | Nur eigene Tasks sichtbar |
| `test_admin_can_view_all_tasks` | Admin sieht alle Tasks |
| `test_user_can_view_single_task` | Einzelnen Task abrufen |
| `test_user_can_update_own_task` | Eigenen Task aktualisieren |
| `test_user_can_delete_own_task` | Eigenen Task löschen |

#### Berechtigungen
| Test | Beschreibung |
|------|--------------|
| `test_user_cannot_view_other_users_task` | 403 bei fremdem Task |
| `test_user_cannot_update_other_users_task` | 403 bei Update fremdem Task |
| `test_user_cannot_delete_other_users_task` | 403 bei Delete fremdem Task |
| `test_admin_can_view_any_task` | Admin darf alles sehen |
| `test_admin_can_update_any_task` | Admin darf alles aktualisieren |
| `test_admin_can_delete_any_task` | Admin darf alles löschen |
| `test_user_cannot_update_overdue_task` | User darf überfällige nicht ändern |
| `test_admin_can_update_overdue_task` | Admin darf überfällige ändern |

#### Validierung
| Test | Beschreibung |
|------|--------------|
| `test_task_creation_validates_required_fields` | title, description, status, deadline |
| `test_task_creation_validates_title_max_length` | Max 255 Zeichen |
| `test_task_creation_validates_status_enum` | todo, in_progress, done |
| `test_task_creation_validates_deadline_must_be_future` | Datum in Zukunft |
| `test_task_creation_validates_project_id_exists` | Projekt muss existieren |
| `test_update_validates_status_enum` | Status Validierung |
| `test_update_validates_deadline_must_be_future` | Deadline Validierung |

#### Spezielle Endpoints
| Test | Beschreibung |
|------|--------------|
| `test_user_can_view_overdue_tasks` | Überfällige eigene Tasks |
| `test_admin_overdue_tasks_shows_all` | Admin sieht alle überfälligen |
| `test_user_can_view_own_tasks_by_user_id` | Tasks nach User-ID |
| `test_user_cannot_view_other_users_tasks_by_id` | 403 bei fremdem User |
| `test_admin_can_view_any_users_tasks` | Admin darf alle User-Tasks |
| `test_user_can_view_tasks_by_project` | Tasks nach Projekt |
| `test_task_creation_with_project_id` | Task mit Projekt verknüpfen |

#### Error Handling
| Test | Beschreibung |
|------|--------------|
| `test_view_non_existent_task_returns_404` | 404 bei nicht existentem Task |
| `test_update_non_existent_task_returns_404` | 404 bei Update |
| `test_delete_non_existent_task_returns_404` | 404 bei Delete |
| `test_unauthenticated_user_cannot_create_task` | 401 ohne Token |

#### Response Struktur
| Test | Beschreibung |
|------|--------------|
| `test_task_response_includes_user_and_project` | Eager Loading Relations |

---

### ProjectCrudTest (22 Tests)

#### CRUD Operationen
| Test | Beschreibung |
|------|--------------|
| `test_user_can_create_project` | Projekt erstellen |
| `test_user_can_view_all_own_projects` | Nur eigene Projekte |
| `test_admin_can_view_all_projects` | Admin sieht alle |
| `test_user_can_view_single_project` | Einzelnes Projekt |
| `test_user_can_update_own_project` | Eigenes Projekt aktualisieren |
| `test_user_can_delete_own_project` | Eigenes Projekt löschen |

#### Berechtigungen
| Test | Beschreibung |
|------|--------------|
| `test_user_cannot_view_other_users_project` | 403 bei fremdem Projekt |
| `test_user_cannot_update_other_users_project` | 403 bei Update |
| `test_user_cannot_delete_other_users_project` | 403 bei Delete |
| `test_admin_can_view_any_project` | Admin darf alles |
| `test_admin_can_update_any_project` | Admin darf alles |
| `test_admin_can_delete_any_project` | Admin darf alles |

#### Validierung
| Test | Beschreibung |
|------|--------------|
| `test_project_creation_validates_required_fields` | Name Pflichtfeld |
| `test_project_creation_validates_title_max_length` | Max 255 Zeichen |

#### Error Handling
| Test | Beschreibung |
|------|--------------|
| `test_view_non_existent_project_returns_404` | 404 Response |
| `test_update_non_existent_project_returns_404` | 404 Response |
| `test_delete_non_existent_project_returns_404` | 404 Response |
| `test_unauthenticated_user_cannot_create_project` | 401 ohne Token |

#### Response Struktur
| Test | Beschreibung |
|------|--------------|
| `test_project_show_includes_tasks` | Tasks werden mitgeladen |
| `test_project_response_includes_user` | User wird mitgeladen |

#### Optionale Felder
| Test | Beschreibung |
|------|--------------|
| `test_project_creation_with_nullable_description` | Description optional |
| `test_project_update_with_description` | Description aktualisieren |

---

## Unit Tests

### TaskModelTest (6 Tests)

| Test | Beschreibung |
|------|--------------|
| `test_task_gehoert_zu_benutzer` | BelongsTo User Beziehung |
| `test_task_gehoert_zu_projekt` | BelongsTo Project Beziehung |
| `test_task_kann_faelligkeitsdatum_haben` | Deadline Cast zu Carbon |
| `test_task_hat_ausfuellbare_attribute` | Fillable Attribute |
| `test_task_kann_mit_factory_erstellt_werden` | Factory funktioniert |
| `test_task_hat_standard_status` | Standard: "todo" |

---

### ProjectModelTest (5 Tests)

| Test | Beschreibung |
|------|--------------|
| `test_projekt_gehoert_zu_benutzer` | BelongsTo User Beziehung |
| `test_projekt_hat_viele_aufgaben` | HasMany Tasks Beziehung |
| `test_projekt_hat_ausfuellbare_attribute` | Fillable Attribute |
| `test_projekt_kann_mit_factory_erstellt_werden` | Factory funktioniert |
| `test_projekt_kann_null_beschreibung_haben` | Nullable Description |

---

### UserModelTest (6 Tests)

| Test | Beschreibung |
|------|--------------|
| `test_benutzer_hat_viele_aufgaben` | HasMany Tasks Beziehung |
| `test_benutzer_hat_viele_projekte` | HasMany Projects Beziehung |
| `test_ist_admin_gibt_true_fuer_admin_zurueck` | isAdmin() true |
| `test_ist_admin_gibt_false_fuer_normalen_benutzer_zurueck` | isAdmin() false |
| `test_benutzer_hat_standard_rolle` | Standard: "user" |
| `test_benutzer_hat_ausfuellbare_attribute` | Fillable Attribute |

---

### TaskUpdatedEventTest (3 Tests)

| Test | Beschreibung |
|------|--------------|
| `test_event_wird_mit_task_erstellt` | Event enthält Task |
| `test_event_wird_mit_task_und_alter_deadline_erstellt` | Event enthält oldDeadline |
| `test_event_ist_dispatchable` | Event kann dispatched werden |

---

### CheckTaskDeadlineListenerTest (3 Tests)

| Test | Beschreibung |
|------|--------------|
| `test_listener_sendet_benachrichtigung_bei_ueberfaelliger_aufgabe` | Notification bei Deadline < now |
| `test_listener_sendet_keine_benachrichtigung_bei_zukuenftiger_deadline` | Keine Notification |
| `test_listener_sendet_keine_benachrichtigung_ohne_deadline` | Keine Notification bei null |

---

### TaskDeadlineNotificationTest (4 Tests)

| Test | Beschreibung |
|------|--------------|
| `test_notification_verwendet_mail_kanal` | via() returns ['mail'] |
| `test_notification_mail_enthaelt_task_titel` | Task-Titel in Mail |
| `test_notification_to_array_enthaelt_task_daten` | Array mit task_id, title, message, deadline |
| `test_notification_implements_should_queue` | Implementiert ShouldQueue |

---

### TaskFormRequestTest (10 Tests)

#### StoreTaskRequest
| Test | Beschreibung |
|------|--------------|
| `test_store_task_request_authorize_gibt_true_zurueck` | authorize() = true |
| `test_store_task_request_rules_enthaelt_pflichtfelder` | Alle Felder vorhanden |
| `test_store_task_request_title_validation` | required, string, max:255 |
| `test_store_task_request_status_validation` | required, in:todo,in_progress,done |
| `test_store_task_request_deadline_validation` | required, date, after:now |
| `test_store_task_request_project_id_validation` | nullable, exists:projects,id |

#### UpdateTaskRequest
| Test | Beschreibung |
|------|--------------|
| `test_update_task_request_authorize_gibt_true_zurueck` | authorize() = true |
| `test_update_task_request_rules_verwendet_sometimes` | sometimes für alle Felder |
| `test_update_task_request_status_validation` | in:todo,in_progress,done |
| `test_update_task_request_deadline_validation` | after:now |

---

### ProjectFormRequestTest (8 Tests)

#### StoreProjectRequest
| Test | Beschreibung |
|------|--------------|
| `test_store_project_request_authorize_gibt_true_zurueck` | authorize() = true |
| `test_store_project_request_rules_enthaelt_felder` | name, description |
| `test_store_project_request_name_validation` | required, string, max:255 |
| `test_store_project_request_description_validation` | nullable, string |

#### UpdateProjectRequest
| Test | Beschreibung |
|------|--------------|
| `test_update_project_request_authorize_gibt_true_zurueck` | authorize() = true |
| `test_update_project_request_rules_verwendet_sometimes` | sometimes für name |
| `test_update_project_request_name_validation` | string, max:255 |
| `test_update_project_request_description_validation` | nullable, string |

---

## Test-Patterns

### RefreshDatabase
Alle Tests verwenden das `RefreshDatabase` Trait, um die Datenbank vor jedem Test zurückzusetzen.

```php
use RefreshDatabase;

protected function setUp(): void
{
    parent::setUp();
    // Setup Code
}
```

### Authentifizierung
Tests mit Sanctum Token-Authentifizierung:

```php
$user = User::factory()->create();
$token = $user->createToken('auth-token')->plainTextToken;

$response = $this->withHeaders([
    'Authorization' => 'Bearer '.$token,
])->getJson('/api/tasks');
```

### Factory Usage
Verwendung von Factories für Testdaten:

```php
// Einzelner User
$user = User::factory()->create(['role' => 'admin']);

// Mehrere Tasks
Task::factory()->count(3)->create(['user_id' => $user->id]);

// Mit Beziehungen
$project = Project::factory()->create();
Task::factory()->create(['project_id' => $project->id]);
```

### Notification Testing
Testen von Notifications mit Fake:

```php
use Illuminate\Support\Facades\Notification;

Notification::fake();

// ... Code der Notification auslöst

Notification::assertSentTo(
    $user,
    TaskDeadlineNotification::class
);
```

---

## Best Practices

1. **Isolierte Tests**: Jeder Test ist unabhängig und verwendet `RefreshDatabase`
2. **Aussagekräftige Namen**: Test-Methoden beschreiben das erwartete Verhalten
3. **Arrange-Act-Assert**: Strukturierte Test-Organisation
4. **Edge Cases**: Tests für Grenzfälle (404, 403, Validierungsfehler)
5. **Assertions**: Verwendung spezifischer Assertions (`assertDatabaseHas`, `assertJsonStructure`)

---

## CI/CD Integration

Die Tests werden automatisch in GitHub Actions ausgeführt:

- **PHP 8.2, 8.3, 8.4** Matrix-Tests
- **Laravel Pint** Code Style Check
- **Parallel Testing** für schnellere Ausführung

Siehe: [docs/github_actions.md](github_actions.md)
