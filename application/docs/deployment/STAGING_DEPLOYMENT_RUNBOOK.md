# Construction Executive Dashboard — Staging Deployment Runbook

> **Target Environment:** Staging (Docker Compose, PHP 8.2-FPM, Nginx, MariaDB 10.11, Redis 7)  
> **Host URL:** `http://<staging-server-ip>:8080`  
> **Health Endpoints:** `/health/liveness`, `/health/readiness`, `/up`

---

## 1. Prerequisites

Before deploying to the staging server, ensure the following are installed:
- **Docker Engine:** `24.0+`
- **Docker Compose:** `v2.20+`
- **Git:** `2.40+`

---

## 2. Initial Setup & Configuration

1. **Clone the repository onto the staging host:**
   ```bash
   git clone https://github.com/preechayutbubphachat/Construction-Executive-Dashboard.git /opt/ced-app
   cd /opt/ced-app/application
   ```

2. **Prepare the Staging Environment File:**
   ```bash
   cp .env.staging.example .env.staging
   ```

3. **Generate a unique Application Key:**
   ```bash
   # Generate key locally or using docker run
   docker run --rm -v $(pwd):/app -w /app php:8.2-cli php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   ```
   Paste the generated key into `APP_KEY` in `.env.staging`.

4. **Verify Database and Port settings in `.env.staging`:**
   ```env
   APP_ENV=staging
   APP_DEBUG=false
   APP_URL=http://your-staging-host:8080
   STAGING_WEB_PORT=8080
   DB_DATABASE=ced_staging
   DB_USERNAME=ced_user
   DB_PASSWORD=<secure_staging_password>
   DB_ROOT_PASSWORD=<secure_root_password>
   ```

---

## 3. Launching the Staging Stack

1. **Build and start the containers in detached mode:**
   ```bash
   docker compose -f docker-compose.staging.yml up -d --build
   ```

2. **Check container health and startup status:**
   ```bash
   docker compose -f docker-compose.staging.yml ps
   ```
   *Expected output:*
   - `ced-staging-db`: Up (healthy)
   - `ced-staging-redis`: Up (healthy)
   - `ced-staging-app`: Up
   - `ced-staging-web`: Up (healthy)

---

## 4. Health Probe Verification

Once the stack is running, execute automated health checks:

1. **Liveness Probe (Verifies Nginx and PHP-FPM process):**
   ```bash
   curl -i http://localhost:8080/health/liveness
   ```
   *Expected Response:* `HTTP/1.1 200 OK`
   ```json
   {
     "status": "ok",
     "service": "ced-app",
     "timestamp": "2026-11-07T..."
   }
   ```

2. **Readiness Probe (Verifies DB connection, storage writeability):**
   ```bash
   curl -i http://localhost:8080/health/readiness
   ```
   *Expected Response:* `HTTP/1.1 200 OK`
   ```json
   {
     "status": "ready",
     "timestamp": "2026-11-07T...",
     "components": {
       "database": {
         "status": "ok",
         "message": "connected"
       },
       "storage": {
         "status": "ok",
         "path": "/var/www/html/storage"
       }
     }
   }
   ```

---

## 5. Maintenance Operations

### Inspecting Logs
```bash
# View live unified logs
docker compose -f docker-compose.staging.yml logs -f

# View application PHP-FPM / Laravel logs only
docker compose -f docker-compose.staging.yml logs -f app

# View web server access & error logs
docker compose -f docker-compose.staging.yml logs -f web
```

### Running Database Migrations Manually
```bash
docker compose -f docker-compose.staging.yml exec app php artisan migrate --status
```

### Seeding Initial Demonstration Data
```bash
docker compose -f docker-compose.staging.yml exec app php artisan db:seed --force
```

### Clearing Application Caches
```bash
docker compose -f docker-compose.staging.yml exec app php artisan optimize:clear
docker compose -f docker-compose.staging.yml exec app php artisan optimize
```

---

## 6. Shutdown and Tear Down

To stop the staging environment without losing database records:
```bash
docker compose -f docker-compose.staging.yml down
```

To completely reset the staging environment (including deleting database and storage volumes):
```bash
docker compose -f docker-compose.staging.yml down -v
```
