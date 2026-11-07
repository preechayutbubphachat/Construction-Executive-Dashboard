# Staging Infrastructure Provisioning & Deployment Readiness Implementation Plan

> **Goal:** Establish a production-parity staging environment using Docker, Nginx, MariaDB, and Redis, configure deep health check probes (`/health/liveness`, `/health/readiness`), and build automated GitHub Actions CI/CD workflows.
> **Architecture:** Multi-stage Dockerized PHP 8.2-FPM application with Nginx reverse proxy, MariaDB 10.11+ database, Redis caching, comprehensive health check endpoints, and automated CI pipelines.
> **Tech Stack / Libraries:** Docker & Docker Compose, Nginx Alpine, PHP 8.2-FPM, MariaDB 10.11, Redis 7, Laravel 11, GitHub Actions.
> **Execution Strategy:** Test-Driven Development (TDD) for health probes, verified configuration files, and zero-downtime staging deployment runbook.

---

## 1. Global Constraints & Invariants
1. **Zero Impact on Local Dev & Existing Tests:** Local SQLite-based development and test runners must remain 100% operational and fast without requiring Docker running locally.
2. **Default-Deny & Security Parity:** Staging environment must enforce production-grade security headers, non-root user execution inside containers, and sanitized error responses.
3. **Database Compatibility:** Eloquent models and migrations must remain dual-compatible with SQLite (testing) and MariaDB/MySQL (staging/production).
4. **Health Probe Non-Destructive:** Health checks must be non-mutating (pure read checks) and return HTTP 200 on healthy, HTTP 503 on unready.

---

## 2. Architectural Blueprint & File Map

```
c:\2025\web-69\05-Construction-Executive-Dashboard\
├── .github/
│   └── workflows/
│       └── ci.yml                                      # Automated CI (Pint, PHPUnit, Docker Config)
├── application/
│   ├── app/
│   │   └── Http/
│   │       └── Controllers/
│   │           └── HealthCheckController.php           # Deep Liveness & Readiness Probes
│   ├── docker/
│   │   ├── nginx/
│   │   │   ├── nginx.conf                              # Main Nginx config
│   │   │   └── default.conf                            # Server block with security headers & FastCGI
│   │   └── php/
│   │       ├── Dockerfile                              # Multi-stage production-ready build
│   │       ├── docker-entrypoint.sh                    # Container bootstrap & migration gate
│   │       ├── php.ini                                 # PHP 8.2 production settings
│   │       └── opcache.ini                             # High-performance OPcache tuning
│   ├── docs/
│   │   └── deployment/
│   │       └── STAGING_DEPLOYMENT_RUNBOOK.md           # Operational guide for staging deployment
│   ├── tests/
│   │   └── Feature/
│   │       └── HealthCheckEndpointsTest.php            # TDD feature tests for health probes
│   ├── .env.staging.example                            # Staging environment template
│   └── docker-compose.staging.yml                      # Parity composition (Nginx + PHP-FPM + MariaDB + Redis)
```

---

## 3. Bite-Sized Implementation Tasks

- [ ] **Task 1: Health Check Probes (TDD)**
  - Write `tests/Feature/HealthCheckEndpointsTest.php` asserting `/health/liveness` returns 200 JSON, `/health/readiness` verifies DB and storage, and simulated DB failure returns 503.
  - Implement `HealthCheckController.php` with `liveness()` and `readiness()` methods.
  - Register `/health/liveness` and `/health/readiness` in `routes/web.php`.
  - Verify all tests pass.

- [ ] **Task 2: Production PHP-FPM & Nginx Dockerization**
  - Create `application/docker/php/Dockerfile` (multi-stage build with assets compiled via Node.js, PHP 8.2-FPM Alpine base, non-root `www-data` user, production extensions: `pdo_mysql`, `bcmath`, `gd`, `zip`, `opcache`).
  - Create `application/docker/php/php.ini` and `application/docker/php/opcache.ini`.
  - Create `application/docker/php/docker-entrypoint.sh` for runtime initialization.
  - Create `application/docker/nginx/nginx.conf` and `application/docker/nginx/default.conf` with FastCGI proxy, gzip, and security headers.

- [ ] **Task 3: Docker Compose Staging Environment & Template**
  - Create `application/docker-compose.staging.yml` defining services (`app`, `web`, `db` [MariaDB 10.11], `redis` [Redis 7]) with healthchecks, restart policies, and named volumes.
  - Create `application/.env.staging.example` with documented staging database, cache, and session variables.

- [ ] **Task 4: GitHub Actions CI/CD Pipeline Configuration**
  - Create `.github/workflows/ci.yml` running linting (`pint --test`), test execution (`phpunit`), and security/config verification.

- [ ] **Task 5: Staging Deployment Runbook & Final Verification**
  - Create `application/docs/deployment/STAGING_DEPLOYMENT_RUNBOOK.md`.
  - Run the full test suite and Laravel Pint to confirm 100% compliance.
  - Update `.agent/memory/active_context.md`.
  - Commit all infrastructure files to Git.

---

## 4. Risks & Mitigation
- **Risk:** Staging Docker changes could inadvertently break local development.
  - *Mitigation:* Keep `docker-compose.staging.yml` isolated; local developers continue using standard PHP CLI / artisan serve without mandatory Docker dependencies.
- **Risk:** Database migration discrepancies between SQLite and MariaDB.
  - *Mitigation:* All migrations already use standard Laravel schema blueprint methods (`string`, `text`, `decimal`, `timestamp`) with no SQLite-specific raw SQL.
