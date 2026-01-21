# gca-Intranet — WordPress (Docker) Dev Environment

Local WordPress stack (Docker Compose) with wp-cli auto-install and GCA Intranet themes.  
Also includes an AWS-friendly WordPress container build (see `Dockerfile`) for container platforms (e.g. ECS/Fargate).

## What’s in this repo

- WordPress runs from the official Docker image (core not stored in Git)
- Themes are committed under:
  - `wp-content/themes/gca-intranet-foundation` (parent)
  - `wp-content/themes/gca-intranet` (child)
- Database: **MySQL 8.0** (local `docker-compose.yml`)
- One-time setup/initialisation via `wp-cli` container (installs WP + activates theme)
- `Dockerfile` builds an image suitable for AWS container platforms
  - In AWS you would typically use **RDS MySQL** and pass DB settings via environment variables / secrets

## Prerequisites

- Docker Desktop (or Docker Engine) installed and running
- Git

## Run (local or EC2)

### 1) Create env file

    cp .env.example .env
    # optional: edit WP_URL / credentials / WP_PORT

### 2) Start containers

Start the stack (builds the WordPress image and starts MySQL + WordPress):

    docker compose --env-file .env up -d --build

(Optional) Confirm services are running:

    docker compose ps

Note: the first run can take a short while while the database initialises.

### 3) One-time initialise WordPress (creates wp-config.php, installs WP, activates theme)

Run the wp-cli initialiser (safe to re-run; it will skip install if already installed):

    docker compose --env-file .env run --rm wpcli

## Access

- Site: `http://localhost:8080` (or whatever `WP_PORT` is set to)
- Admin: `http://localhost:8080/wp-admin`

Admin credentials come from `.env`:
- `WP_ADMIN_USER`
- `WP_ADMIN_PASSWORD`

## Reset (wipe DB + WP volumes)

    docker compose down -v
    docker compose --env-file .env up -d --build
    docker compose --env-file .env run --rm wpcli

## EC2 notes

- Set `WP_URL` to the EC2 domain/IP (e.g. `http://<public-ip>` or your DNS name)
- Set `WP_PORT=80` if running directly on port 80 (or keep 8080 behind a reverse proxy/ALB)
- `.env` is local-only and must NOT be committed (use `.env.example` as the template)

## Troubleshooting

- **Docker not found / not running:** install/start Docker Desktop, then retry.
- **Port already in use:** change `WP_PORT` in `.env` (e.g. `WP_PORT=8081`) and restart.
- **Stuck on installer / DB errors:** do a full reset:

    docker compose down -v
    docker compose --env-file .env up -d --build
    docker compose --env-file .env run --rm wpcli
