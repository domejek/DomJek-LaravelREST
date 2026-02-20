# GitHub Actions CI/CD Dokumentation

Diese Dokumentation beschreibt die GitHub Actions Workflows für das Task Management API Projekt.

## Übersicht

| Workflow | Datei | Trigger |
|----------|-------|---------|
| CI | `.github/workflows/ci.yml` | Push/PR auf main, master, develop |
| Docker | `.github/workflows/docker.yml` | Push/Tags auf main, master |
| Security | `.github/workflows/security.yml` | Push/PR + wöchentlich (Montags) |

---

## CI Workflow

**Datei:** `.github/workflows/ci.yml`

### Jobs

#### 1. Tests

Führt PHPUnit Tests mit paralleler Ausführung durch.

| Einstellung | Wert |
|-------------|------|
| Runner | `ubuntu-latest` |
| PHP Versionen | 8.2, 8.3, 8.4 |
| Datenbank | SQLite (in-memory) |
| Node.js | 20 |

**Schritte:**
1. Checkout Code
2. PHP Setup mit Extensions (dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite)
3. Node.js Setup
4. Composer Dependencies installieren
5. NPM Dependencies installieren
6. `.env.example` kopieren
7. Application Key generieren
8. Assets builden (`npm run build`)
9. Tests ausführen (`php artisan test --parallel`)

#### 2. Pint (Code Style)

Prüft den Code-Style mit Laravel Pint.

| Einstellung | Wert |
|-------------|------|
| Runner | `ubuntu-latest` |
| PHP Version | 8.2 |

**Schritte:**
1. Checkout Code
2. PHP Setup
3. Composer Dependencies installieren
4. Pint Check (`vendor/bin/pint --test`)

### Konfiguration anpassen

Um zusätzliche PHP-Extensions hinzuzufügen:

```yaml
- name: Setup PHP
  uses: shivammathur/setup-php@v2
  with:
    php-version: ${{ matrix.php }}
    extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, redis
```

---

## Docker Workflow

**Datei:** `.github/workflows/docker.yml`

### Jobs

#### 1. Build

Baut das Docker Image und pusht es zur GitHub Container Registry (GHCR).

| Einstellung | Wert |
|-------------|------|
| Registry | `ghcr.io` |
| Image Name | Repository Name |
| Cache | GitHub Actions Cache |

**Trigger:**
- Push auf `main`/`master` → Image wird gepusht
- Tags (`v*`) → Versionierte Images
- Pull Requests → Nur Build, kein Push

**Image Tags:**
- Branch-Name (z.B. `main`)
- PR-Nummer (z.B. `pr-42`)
- SemVer bei Tags (z.B. `1.0.0`, `1.0`)

#### 2. Docker Compose Test

Validiert die `docker-compose.yml` Konfiguration.

**Schritte:**
1. Validate: `docker-compose config -q`
2. Build: `docker-compose build --no-cache`

### Image verwenden

```bash
# Login zu GHCR
docker login ghcr.io -u USERNAME -p GITHUB_TOKEN

# Image pullen
docker pull ghcr.io/OWNER/DomJek-LaravelREST:main

# Container starten
docker run -p 8080:80 ghcr.io/OWNER/DomJek-LaravelREST:main
```

---

## Security Workflow

**Datei:** `.github/workflows/security.yml`

### Jobs

#### 1. Composer Audit

Prüft bekannte Sicherheitslücken in Composer Dependencies.

```bash
composer audit --no-dev
```

#### 2. Symfony Security Checker

Verwendet den Symfony Security Checker für erweiterte Sicherheitsprüfungen.

### Schedule

Der Security Workflow läuft automatisch:
- Bei jedem Push/PR
- Jeden Montag um 06:00 UTC

---

## Dependabot

**Datei:** `.github/dependabot.yml`

### Konfiguration

| Ecosystem | Interval | Limit |
|-----------|----------|-------|
| composer | Wöchentlich (Montags 06:00) | 10 PRs |
| npm | Wöchentlich (Montags 06:00) | 10 PRs |
| GitHub Actions | Wöchentlich (Montags 06:00) | 5 PRs |

### Labels

Automatische Labels für PRs:
- `dependencies` - Alle Dependency Updates
- `php` - Composer Updates
- `javascript` - NPM Updates
- `github-actions` - Workflow Updates

### NPM Major Versionen ignorieren

Dependabot ignoriert Major-Version-Updates für NPM Packages:

```yaml
ignore:
  - dependency-name: "*"
    update-types: ["version-update:semver-major"]
```

---

## Status Badges

Füge diese Badges zur README hinzu:

```markdown
[![CI](https://github.com/OWNER/REPO/actions/workflows/ci.yml/badge.svg)](https://github.com/OWNER/REPO/actions/workflows/ci.yml)
[![Docker](https://github.com/OWNER/REPO/actions/workflows/docker.yml/badge.svg)](https://github.com/OWNER/REPO/actions/workflows/docker.yml)
[![Security](https://github.com/OWNER/REPO/actions/workflows/security.yml/badge.svg)](https://github.com/OWNER/REPO/actions/workflows/security.yml)
```

---

## Voraussetzungen

### .env.example

Die CI benötigt eine `.env.example` Datei im Repository Root:

```env
APP_NAME=Laravel
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=sqlite

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
```

### Dockerfile

Der Docker Workflow erwartet ein Dockerfile unter `docker/Dockerfile`.

---

## Troubleshooting

### Tests schlagen fehl

1. Prüfe ob `.env.example` existiert
2. Prüfe ob alle Migrations korrekt sind
3. Führe Tests lokal aus: `php artisan test`

### Pint schlägt fehl

Führe Pint lokal aus, um Auto-Fix durchzuführen:

```bash
vendor/bin/pint
```

### Docker Build schlägt fehl

1. Prüfe `docker/Dockerfile` Syntax
2. Teste lokal: `docker-compose build`
3. Prüfe ob alle benötigten Files im Context sind

---

## Erweiterungen

### Code Coverage hinzufügen

```yaml
- name: Execute tests with coverage
  run: php artisan test --coverage-clover=coverage.xml

- name: Upload coverage to Codecov
  uses: codecov/codecov-action@v4
  with:
    file: ./coverage.xml
```

### Deployment zu Production

```yaml
deploy:
  needs: [tests, pint]
  runs-on: ubuntu-latest
  if: github.ref == 'refs/heads/main'
  steps:
    - name: Deploy to production
      run: |
        # Deployment Script hier
```

### Slack Notifications

```yaml
- name: Notify Slack on failure
  if: failure()
  uses: slackapi/slack-github-action@v1
  with:
    channel-id: 'CHANNEL_ID'
    slack-message: 'CI failed for ${{ github.repository }}'
  env:
    SLACK_BOT_TOKEN: ${{ secrets.SLACK_BOT_TOKEN }}
```
