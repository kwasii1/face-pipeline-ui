# Face Pipeline UI

A real-time photo organization dashboard built with Laravel, Livewire, and Reverb. Upload photos, trigger AI-powered face detection and clustering via a FastAPI backend, and organize people into projects — all with live progress updates over WebSockets.

## Tech Stack

-   **PHP 8.4** + Laravel 13
-   **Livewire 4** — reactive, full-page components
-   **Reverb** — Laravel's first-party WebSocket server
-   **Horizon** — Redis queue supervisor
-   **Tailwind CSS v4** — utility-first CSS
-   **FastAPI** (external microservice) — face detection and clustering engine
-   **PostgreSQL** — primary database
-   **Redis** — queue driver, cache, Reverb scaling

## Prerequisites

-   PHP 8.4+
-   [Composer](https://getcomposer.org/)
-   [Node.js 22+](https://nodejs.org/)
-   PostgreSQL 16+
-   Redis 7+
-   A running [FastAPI face pipeline service](https://github.com/kwasii1/face-pipeline) at `FASTAPI_BASE_URL`

## Quick Start (Local Development)

```bash
# Clone the repository
git clone https://github.com/kwasii1/face-pipeline-ui.git
cd face-pipeline-ui

# Install PHP dependencies
composer install

# Create environment file and generate an app key
cp .env.example .env
php artisan key:generate

# Install frontend dependencies and compile assets
npm install
npm run build

# Run database migrations
php artisan migrate

# Start all dev services concurrently
composer run dev
```

The `composer run dev` script starts the Vite dev server, Laravel queue worker, Reverb WebSocket server, and the PHP built-in server simultaneously.

## Environment Variables

Copy `.env.example` to `.env` and adjust the following:

| Variable               | Description                                      | Default               |
| ---------------------- | ------------------------------------------------ | --------------------- |
| `APP_NAME`             | Application name                                 | `Laravel`             |
| `APP_ENV`              | Environment (`local` / `production`)             | `local`               |
| `APP_KEY`              | Application encryption key (auto-generated)      | —                     |
| `APP_DEBUG`            | Debug mode                                       | `true`                |
| `APP_URL`              | Application base URL                             | `http://localhost`    |
| `DB_CONNECTION`        | Database driver                                  | `pgsql`               |
| `DB_HOST` / `DB_PORT`  | PostgreSQL host and port                         | `127.0.0.1` / `5432`  |
| `DB_DATABASE`          | Database name                                    | —                     |
| `DB_USERNAME`          | Database user                                    | —                     |
| `DB_PASSWORD`          | Database password                                | —                     |
| `REDIS_HOST` / `REDIS_PORT` | Redis host and port                         | `127.0.0.1` / `6379`  |
| `QUEUE_CONNECTION`     | Queue driver                                     | `redis`               |
| `REVERB_APP_ID`        | Reverb application ID                            | —                     |
| `REVERB_APP_KEY`       | Reverb application key                           | —                     |
| `REVERB_APP_SECRET`    | Reverb application secret                        | —                     |
| `REVERB_HOST`          | Public Reverb hostname                           | —                     |
| `FASTAPI_BASE_URL`     | FastAPI face-pipeline service URL                | `http://127.0.0.1:8001` |
| `SHARED_STORAGE_PATH`  | Path to shared photo storage directory           | —                     |
| `RUN_MIGRATIONS`       | Run migrations at container start (Docker only)  | `false`               |

See `.env.example` for the full list of configurable options.

## Docker

The same image can run in two modes:

### All-in-one (default)

All processes (nginx, PHP-FPM, Horizon, Reverb) run in a single container managed by supervisord. Good for simple setups.

```bash
docker pull ghcr.io/kwasii1/face-pipeline-ui:latest
docker run -d \
  --name face-pipeline-ui \
  -p 80:80 \
  -p 8080:8080 \
  -e APP_KEY=base64:... \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=postgres \
  -e REDIS_HOST=redis \
  ... \
  ghcr.io/kwasii1/face-pipeline-ui:latest
```

### Multi-container (per-role)

Set `CONTAINER_ROLE` to split processes into separate containers — useful for scaling Horizon workers independently or running Reverb on its own port mapping:

```bash
# Web server (nginx + PHP-FPM)
docker run -d --name app-web -p 80:80 \
  -e CONTAINER_ROLE=web \
  ghcr.io/kwasii1/face-pipeline-ui:latest

# Horizon queue worker
docker run -d --name app-horizon \
  -e CONTAINER_ROLE=horizon \
  ghcr.io/kwasii1/face-pipeline-ui:latest

# Reverb WebSocket server
docker run -d --name app-reverb -p 8080:8080 \
  -e CONTAINER_ROLE=reverb \
  ghcr.io/kwasii1/face-pipeline-ui:latest

# Scheduler (optional — runs schedule:work)
docker run -d --name app-scheduler \
  -e CONTAINER_ROLE=scheduler \
  ghcr.io/kwasii1/face-pipeline-ui:latest
```

**Supported roles:**

| Role        | Command                                              |
| ----------- | ---------------------------------------------------- |
| *(unset)*   | supervisord with all 4 processes (backward compatible) |
| `web`       | supervisord with nginx + PHP-FPM only                 |
| `horizon`   | `php artisan horizon`                                 |
| `reverb`    | `php artisan reverb:start --no-interaction`           |
| `scheduler` | `php artisan schedule:work`                           |

**Exposed ports:**

| Port | Service                 |
| ---- | ----------------------- |
| 80   | Nginx / web application |
| 8080 | Reverb WebSocket server |

### Build Locally

```bash
docker build -t face-pipeline-ui .
```

### Opt-in Migrations

Set `RUN_MIGRATIONS=true` to automatically run `php artisan migrate --force` on container start. Disabled by default to prevent unintended schema changes. In a multi-container setup, run migrations on only one container (e.g., the `web` container) to avoid race conditions.

## Services

This application depends on the following external services:

-   **PostgreSQL** — primary data store
-   **Redis** — queue backend for Horizon and Reverb scaling
-   **FastAPI face pipeline** — performs face detection, embedding extraction, and clustering

All three should be reachable from the container at their configured hostnames/ports.

## Testing

```bash
composer test
```

Runs PHPStan static analysis, Laravel Pint code style checks, and the Pest test suite.

## License

MIT
