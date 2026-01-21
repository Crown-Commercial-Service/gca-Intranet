# gca-Intranet — WordPress (Docker) Dev Environment
Local + EC2 WordPress stack with wp-cli auto-install and GCA Intranet themes.


## What’s in this repo

- WordPress runs from the official Docker image (core not stored in Git)
- Themes are committed under:
  - `wp-content/themes/gca-intranet-foundation` (parent)
  - `wp-content/themes/gca-intranet` (child)
- Database: MariaDB
- One-time setup/initialisation via `wp-cli` container (auto-installs WP + activates theme)

## Prerequisites

- Docker Desktop (or Docker Engine) installed and running
- Git

## Run (local or EC2)

### 1) Create env file

    cp .env.example .env
    # optional: edit WP_URL / credentials / WP_PORT

### 2) Start containers

    docker compose --env-file .env up -d --build

### 3) One-time initialise WordPress (creates wp-config.php, installs WP, activates theme)

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
- **Port 8080 already in use:** change `WP_PORT` in `.env` (e.g. `WP_PORT=8081`) and restart.
- **Stuck on installer / DB errors:** do a full reset:

    docker compose down -v
    docker compose --env-file .env up -d --build
    docker compose --env-file .env run --rm wpcli
