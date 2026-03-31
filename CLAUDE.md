# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Popcorn AI Compliance — web app for managing fiscal compliance and anti-money laundering (AML/LFPIORPI) obligations in Mexico. Built with Laravel 11 + Vue 3 + Inertia.js + Tailwind CSS v4.

## Commands

```bash
# Full dev environment (Laravel server + queue + logs + Vite, all concurrent)
composer run dev

# Individual services
php artisan serve          # Laravel on :8000
npm run dev                # Vite hot-reload
npm run build              # Production frontend build

# Testing
php artisan test                          # All tests
php artisan test --filter=TestClassName   # Single test class
php artisan test tests/Feature/ExampleTest.php  # Single file

# Database
php artisan migrate
php artisan migrate:fresh --seed   # Reset + seed

# Docker (MySQL, Redis, n8n, phpMyAdmin)
docker-compose up -d
docker-compose --profile dev up -d  # Includes phpMyAdmin on :8080

# Linting
./vendor/bin/pint          # PHP code style (Laravel Pint)
```

## Architecture

**Request flow:** Browser → Laravel routes (`routes/web.php`) → Controller → `Inertia::render('PageName')` → Vue SFC (`resources/js/Pages/*.vue`) wrapped in `AppLayout.vue`.

**Frontend wiring:** `app.js` initializes Inertia+Vue3, resolves pages from `Pages/` directory, integrates Ziggy for `route()` helpers. Tailwind v4 configured via `@tailwindcss/vite` plugin.

**Backend:** Standard Laravel structure. `HandleInertiaRequests` middleware shares server-side props to Vue. Queue connection is database-backed (for n8n webhook integration).

**Infrastructure (docker-compose):** MySQL 8.0, Redis 7, n8n (workflow automation on :5678), phpMyAdmin (dev profile only).

## Database Schema

Three domain tables beyond Laravel defaults:

- **clients** — RFC (unique, 13 chars), régimen fiscal (SAT codes), `actividades_vulnerables` (JSON), `nivel_riesgo` enum (bajo/medio/alto), PEP flag. Soft deletes.
- **audit_logs** — Polymorphic entity tracking (`entidad_tipo`/`entidad_id`), JSON diffs (`datos_anteriores`/`datos_nuevos`), `canal` enum (sistema/n8n/manual).
- **expedientes_antilavado** — KYC records with UMA-based thresholds: 645 UMAs = aviso, 1500 UMAs = UIF report. `documentos_identidad`/`documentos_soporte` as JSON. 10-year retention (`fecha_vencimiento_retencion`). Folio format: `KYC-YYYY-NNNNN`. Soft deletes (compliance retention).

Dev uses SQLite; production uses MySQL via docker-compose.

## Domain Conventions

- **Language:** Spanish for domain fields (razon_social, rfc, regimen_fiscal), English for framework/code structure.
- **LFPIORPI:** Art. 17 = vulnerable activities, Art. 18 = 10-year record retention.
- **UMA 2026:** $117.31/día. Used to calculate `umas_equivalente` from `monto_operacion`.
- **PEP:** Persona Políticamente Expuesta (Politically Exposed Person).
- **n8n workflows** are stored in `docs/` (workflow-kyc-antilavado.json, workflow-alertas-fiscales.json).

## Testing

PHPUnit with SQLite `:memory:` database, array cache, sync queue. Test suites: `tests/Unit/` and `tests/Feature/`.
