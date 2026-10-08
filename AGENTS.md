# AGENTS.md

Instructions for AI agents (and humans) working in this repository.

## Git workflow — IMPORTANT

**Always commit and push to GitHub when you finish a task.** Do not leave work
sitting in the working tree, and do not wait to be asked.

```powershell
cd C:\xampp\htdocs\hms
git status                     # what changed
git add -A
git commit -m "describe the change"
git push
```

### Rules

- **Push after every completed task.** A finished change is not finished until
  it is on `origin/main`.
- **Never force-push to `main`.** It rewrites published history.
- **Never commit secrets, credentials, or personal contact details.** See
  "Personalisation" below.
- If the push fails on authentication, it needs a human — report it rather than
  retrying in a loop. Auth uses HTTPS + Git Credential Manager (browser popup)
  or a Personal Access Token (`repo` scope).
- Before committing, review what is staged (`git diff --cached --name-only`) and
  confirm no `.env`, `frontend/uploads/*` content, or `*.log` files are included.

## Project

Hospital Management System — multi-department PHP/MySQL app running on Laragon
(Apache, MySQL 8.4, PHP 8.3). No Composer, no npm, no build step. The checkout
lives at `C:\xampp\htdocs\hms\` and `C:\laragon\www\hms` is a **directory
junction** to it, so Laragon serves this same folder — there is only one copy.
Open `http://localhost/hms/setup.php` for the one-time installer.

```
backend/api/       REST-ish JSON endpoints (auth, patients, visits, ...)
backend/config/    config.php, database.php (Laragon MySQL: root / no password)
backend/includes/  shared helper functions
backend/models/    User, Patient
database/          schema.sql — imported by setup.php
frontend/          dashboard.php (SPA shell), index.php (login), pages/*.php
ai_engine/         Django + FastAPI service scaffolding (not required to run HMS)
setup.php          one-time installer: creates DB, imports schema, seeds admin
```

Default login after setup: `admin` / `Admin@123` — change immediately.

## Conventions

- Pages under `frontend/pages/` are HTML **fragments** loaded into
  `#page-content` by the SPA shell in `frontend/dashboard.php`. They are not
  standalone documents — do not add `<html>`/`<head>` to them.
- No Bootstrap. The dashboard defines local CSS utility shims (`.row`,
  `.col-md-6`, `.d-flex`, …) inside its own `<style>` block.
- `frontend/dashboard.php` holds `MODULE_PAGE_MAP` and `loadModuleTab()`, which
  map dashboard card keys to SPA pages. Adding a module card means adding it in
  three places: the card markup, `MODULE_PAGE_MAP`, and the sidebar `data-page`
  link.
- Verify PHP changes with `C:\laragon\bin\php\php-8.3.33-Win32-vs16-x64\php.exe -l <file>`
  before committing.

## Personalisation

`frontend/dashboard.php` defines `$contactEmail` / `$contactPhone` near the top
and uses them for the dashboard contact banner **and** the SPA module-page
footer. Edit those two variables to change the contact details in both places.
They are committed as generic placeholders (`support@example.com`) so that no
personal email address or phone number is published in this public repository —
keep it that way, or supply values from a local, uncommitted config.
