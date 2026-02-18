# Laravel Full Stack Developer (Backend-Schwerpunkt)

## 📋 Einleitung

Bevor du mit der Umsetzung der Aufgaben beginnst, lies bitte die gesamte Aufgabenstellung aufmerksam durch, um ein umfassendes Verständnis für die Anforderungen zu entwickeln.

### Wichtige Hinweise:
- ✅ Gute Codequalität (Struktur, Lesbarkeit, Dokumentation)
- ✅ Aufgaben sind aufeinander aufbauend
- ✅ Jede Aufgabe in einem eigenen Pull Request
- ✅ Laravel Best Practices konsequent befolgen
- ✅ Öffentliches GitHub-Repository
- ✅ **Nur RESTful API - keine Web-Benutzeroberflächen**
- ⏱️ Nimm dir so viel Zeit, wie du brauchst

---

## 1️⃣ Grundprüfung

### Beschreibung
Entwickle eine RESTful API für ein einfaches Aufgaben-Management-System mit einem Schwerpunkt auf dem Backend. Benutzer sollen in der Lage sein, Aufgaben zu erstellen, zu bearbeiten, zu löschen und aufzulisten.

### Anforderungen

#### 1. Datenbankmodell
- [x] Erstelle ein Datenbankmodell für Aufgaben mit folgenden Feldern:
  - Titel
  - Beschreibung
  - Status
- [x] Verwende Laravel-Migrationen zur Erstellung der Datenbankstruktur

#### 2. Eloquent-Modell
- [x] Implementiere ein Eloquent-Modell für Aufgaben mit den entsprechenden Beziehungen

#### 3. Controller
- [x] Erstelle einen Controller für Aufgaben mit CRUD-Operationen:
  - **C**reate (Erstellen)
  - **R**ead (Lesen)
  - **U**pdate (Aktualisieren)
  - **D**elete (Löschen)
- [x] Validiere Benutzereingaben gemäß den Anforderungen

#### 4. RESTful-Routen
- [x] Lege RESTful-Routen für die Aufgaben-Controller-Methoden fest

#### 5. Middleware
- [x] Implementiere eine Middleware, um sicherzustellen, dass alle Anfragen authentifiziert sind

#### 6. Authentifizierung
- [x] Implementiere Benutzer-Authentifizierung für API-Anfragen
- [x] Nur authentifizierte Benutzer dürfen Aufgaben erstellen, bearbeiten oder löschen

#### 7. Tests
- [x] Schreibe PHPUnit-Tests für wichtige API-Funktionen:
  - Erstellung
  - Aktualisierung
  - Löschung
  - Weitere wichtige Funktionen

#### 8. Dokumentation
- [x] Erstelle eine umfassende README-Datei im Projekt
- [x] Beschreibe Installation und Verwendung der API
- [x] Erstelle detaillierte API-Dokumentation unter `/docs/api_dokumentation.md`

### Umsetzung
- [ ] Erstelle einen eigenen Pull Request für die Grundprüfung
- [ ] Integriere die Änderungen in das Hauptprojekt
- [ ] Nacharbeiten und Feedback erfolgen über Kommentare im PR

### Technische Anforderungen
- **Framework:** Laravel
- **Datenbank:** MySQL
- **Best Practices:** Laravel-Konventionen beachten
- **Repository:** Öffentlich auf GitHub

### Bewertungskriterien
- Vollständigkeit
- Korrekte Anwendung von Laravel-Techniken
- Codequalität
- Testabdeckung
- Dokumentation

---

## 2️⃣ Erweiterte Prüfungsaufgabe

### Beschreibung
Nach erfolgreichem Abschluss der Grundprüfung soll eine hochperformante RESTful API für ein erweitertes Aufgaben-Management-System entwickelt werden. Diese Aufgabe deckt verschiedene Aspekte von Laravel ab, einschließlich Eloquent-Relations, Middleware, Authentifizierung und Event-Listeners.

### Anforderungen

#### 1. Datenbankmodell erweitern
- [x] Erweitere das Datenbankmodell für Aufgaben um ein neues Feld **"deadline"** für den Fälligkeitszeitpunkt
- [x] Füge zwei zusätzliche Eloquent-Modelle hinzu:
  - **Benutzer** (Users)
  - **Projekte** (Projects)
- [x] Stelle Beziehungen zwischen den Modellen her:
  - Ein Benutzer kann mehrere Aufgaben haben (1:n)
  - Eine Aufgabe kann zu einem Projekt gehören (n:1)
  - Ein Projekt kann mehrere Aufgaben haben (1:n)
- [x] Implementiere Migrationen, um die Datenbankstruktur zu aktualisieren

#### 2. Controller erweitern
- [x] Passe den Aufgaben-Controller an, um die neuen Beziehungen zu berücksichtigen
- [x] Implementiere Methoden, um Aufgaben für einen bestimmten Benutzer abzurufen
- [x] Implementiere Methoden, um Aufgaben für ein bestimmtes Projekt abzurufen
- [x] Erweitere das Projekt um die Möglichkeit, die Deadline einer Aufgabe zu aktualisieren
- [x] Implementiere eine zusätzliche Methode, um alle **überfälligen Aufgaben** zurückzugeben

#### 3. RESTful-Routen
- [x] Füge RESTful-Routen hinzu, um die neuen Controller-Methoden anzusprechen

#### 4. Middleware
- [x] Implementiere eine Middleware, um sicherzustellen, dass nur autorisierte Benutzer Aufgaben mit überfälligen Deadlines bearbeiten können
- [x] Füge eine Middleware hinzu, um sicherzustellen, dass ein Benutzer nur auf seine eigenen Aufgaben zugreifen kann

#### 5. Authentifizierung (optional)
- [x] Erweitere die Authentifizierung, um die **Rolle des Benutzers** zu berücksichtigen
- [x] Implementiere Admin-Rolle mit folgenden Berechtigungen:
  - Admins dürfen nicht nur ihre eigenen Aufgaben bearbeiten
  - Admins dürfen Aufgaben anderer Benutzer mit überfälligen Deadlines bearbeiten
  - Stelle sicher, dass die notwendige Autorisierung für Admin-Benutzer implementiert ist

**Begründung:** Diese Regelung ist sinnvoll, um sicherzustellen, dass Administratoren die Flexibilität haben, dringende Aufgaben im gesamten System zu verwalten und nicht nur auf ihre eigenen beschränkt sind.

#### 6. Event-Listener
- [x] Implementiere einen Event-Listener, der aufgerufen wird, wenn eine Aufgabe aktualisiert wurde
- [x] Der Listener soll überprüfen, ob die Deadline abgelaufen ist
- [x] Gegebenenfalls eine **Benachrichtigung (Notification)** an den Benutzer senden

#### 7. Tests
- [x] Schreibe PHPUnit-Tests für:
  - Neue Funktionen
  - Leistungsfähigkeit der API
  - Event-Listener
  - Beziehungen zwischen Benutzern, Aufgaben und Projekten

#### 8. Dokumentation
- [x] Ergänze die README-Datei um detaillierte Anweisungen zur Installation
- [x] Dokumentiere die Verwendung der erweiterten Funktionen

### Umsetzung
- [ ] Erstelle einen eigenen Pull Request für die erweiterte Prüfung
- [ ] Integriere die Änderungen in das Hauptprojekt
- [ ] Nacharbeiten und Feedback erfolgen über Kommentare im PR

### Besondere Hinweise
- ⚡ Achte besonders auf die **Leistungsfähigkeit der API**, insbesondere bei der Abfrage überfälliger Aufgaben
- 🎯 Nutze Laravel-Events und -Listeners zur Überwachung der Aufgabenaktualisierung

### Bewertungskriterien
Neben den vorherigen Kriterien zusätzlich:
- Implementierung der neuen Features
- Leistungsfähigkeit der API
- Saubere Umsetzung von Event-Listeners

---

## 3️⃣ Zusatzaufgabe

### Beschreibung
In der bestehenden Aufgaben-Management-Anwendung soll die Validierung der Benutzereingaben verbessert werden. Bestimmte Felder sollen spezifischen Validierungsregeln unterliegen.

### Aufgaben

#### 1. Titel und Beschreibung
- [x] Begrenze die maximale Länge des Titels auf **255 Zeichen**
- [x] Stelle sicher, dass **Titel erforderlich** ist
- [x] Stelle sicher, dass **Beschreibung erforderlich** ist

#### 2. Status
- [x] Stelle sicher, dass der Status nur bestimmte Werte annehmen kann:
  - `"todo"`
  - `"in_progress"`
  - `"done"`

#### 3. Fälligkeitsdatum (Deadline)
- [x] Überprüfe, ob das Fälligkeitsdatum ein **gültiges Datum** ist
- [x] Stelle sicher, dass das Datum **in der Zukunft liegt**

### Validierungsregeln - Übersicht

| Feld | Regel | Beschreibung |
|------|-------|--------------|
| Titel | required, max:255 | Erforderlich, maximal 255 Zeichen |
| Beschreibung | required | Erforderlich |
| Status | required, in:todo,in_progress,done | Nur erlaubte Werte |
| Deadline | required, date, after:now | Gültiges Datum in der Zukunft |

---

## 📊 Projekt-Übersicht

### Technologie-Stack
- **Backend Framework:** Laravel (neueste Version)
- **Datenbank:** MySQL
- **Testing:** PHPUnit
- **API-Typ:** RESTful
- **Versionskontrolle:** Git (GitHub)

### Datenbankschema (nach Erweiterung)

```
Users (Benutzer)
├── id
├── name
├── email
├── password
├── role (optional: 'user' / 'admin')
└── timestamps

Projects (Projekte)
├── id
├── name
├── description
└── timestamps

Tasks (Aufgaben)
├── id
├── user_id (FK -> Users)
├── project_id (FK -> Projects)
├── title
├── description
├── status (enum: 'todo', 'in_progress', 'done')
├── deadline
└── timestamps
```

### API-Endpoints (Beispiel)

```
POST   /api/register          - Benutzerregistrierung
POST   /api/login             - Benutzeranmeldung
POST   /api/logout            - Benutzerabmeldung
GET    /api/user             - Aktuellen Benutzer abrufen

GET    /api/tasks             - Alle Aufgaben auflisten
POST   /api/tasks             - Neue Aufgabe erstellen
GET    /api/tasks/{id}        - Einzelne Aufgabe anzeigen
PUT    /api/tasks/{id}        - Aufgabe aktualisieren
DELETE /api/tasks/{id}        - Aufgabe löschen

GET    /api/tasks/overdue     - Überfällige Aufgaben
GET    /api/users/{id}/tasks  - Aufgaben eines Benutzers
GET    /api/projects/{id}/tasks - Aufgaben eines Projekts

GET    /api/projects          - Alle Projekte auflisten
POST   /api/projects          - Neues Projekt erstellen
GET    /api/projects/{id}     - Einzelnes Projekt anzeigen
PUT    /api/projects/{id}     - Projekt aktualisieren
DELETE /api/projects/{id}     - Projekt löschen
```

---

## ✅ Checkliste für die Abgabe

### Grundprüfung
- [x] Datenbankmigrationen erstellt
- [x] Eloquent-Modelle implementiert
- [x] Controller mit CRUD-Operationen
- [x] RESTful-Routen definiert
- [x] Authentifizierungs-Middleware
- [x] API-Authentifizierung implementiert
- [x] PHPUnit-Tests geschrieben
- [x] README-Datei erstellt
- [ ] Pull Request erstellt

### Erweiterte Prüfung
- [x] Deadline-Feld hinzugefügt
- [x] User- und Project-Modelle erstellt
- [x] Beziehungen implementiert
- [x] Controller erweitert
- [x] Überfällige Aufgaben-Endpoint
- [x] Autorisierungs-Middleware
- [x] Admin-Rolle (optional)
- [x] Event-Listener für Aufgaben-Updates
- [x] Notification-System
- [x] Performance-Optimierung
- [x] Erweiterte Tests
- [x] README aktualisiert
- [x] Project Controller mit CRUD
- [ ] Pull Request erstellt

### Zusatzaufgabe
- [x] Validierung für Titel (max 255, required)
- [x] Validierung für Beschreibung (required)
- [x] Validierung für Status (enum)
- [x] Validierung für Deadline (date, future)
- [x] Tests für Validierungen
- [x] Project CRUD mit Validierung
- [ ] Pull Request erstellt

### Allgemein
- [x] Code folgt Laravel Best Practices
- [x] Code ist gut dokumentiert
- [x] Code ist lesbar und strukturiert
- [x] API-Dokumentation unter /docs erstellt
- [ ] Öffentliches GitHub-Repository erstellt
- [ ] Alle Pull Requests sind sauber getrennt
- [x] README ist vollständig und verständlich

---

## 🎯 Bewertungskriterien

### Code-Qualität
- Saubere und lesbare Code-Struktur
- Einhaltung von Laravel-Konventionen
- Konsistente Namensgebung
- Angemessene Code-Kommentare

### Funktionalität
- Alle Anforderungen vollständig umgesetzt
- API funktioniert korrekt
- Fehlerbehandlung implementiert
- Edge Cases berücksichtigt

### Testing
- Gute Testabdeckung
- Tests sind aussagekräftig
- Tests decken wichtige Szenarien ab

### Performance
- Effiziente Datenbankabfragen
- Optimierung bei überfälligen Aufgaben
- Vermeidung von N+1-Problemen

### Dokumentation
- README ist vollständig
- Installation ist klar beschrieben
- API-Endpoints sind dokumentiert
- Beispiele sind vorhanden

---

## 📚 Hilfreiche Laravel-Konzepte

### Für die Grundprüfung
- Migrations
- Eloquent Models
- Controllers
- Routing
- Middleware
- Authentication (Sanctum/Passport)
- Validation
- PHPUnit Testing

### Für die erweiterte Prüfung
- Eloquent Relationships (hasMany, belongsTo)
- Authorization & Policies
- Events & Listeners
- Notifications
- Query Optimization
- Roles & Permissions

### Für die Zusatzaufgabe
- Form Request Validation
- Custom Validation Rules
- Error Messages

---

## 💡 Tipps

1. **Beginne mit der Grundprüfung** und stelle sicher, dass alles funktioniert, bevor du zur erweiterten Prüfung übergehst
2. **Teste regelmäßig** während der Entwicklung
3. **Committe oft** mit aussagekräftigen Commit-Messages
4. **Dokumentiere während du entwickelst**, nicht erst am Ende
5. **Nutze Laravel-Features** wie Form Requests für Validierung
6. **Achte auf Performance** bei Datenbankabfragen (Eager Loading)
7. **Schreibe sauberen Code** - Refactoring ist Teil des Prozesses

---

## 📞 Kontakt

Bei Fragen oder Unsicherheiten nicht zögern, Kontakt aufzunehmen!

**Viel Erfolg bei der Umsetzung! 🚀**