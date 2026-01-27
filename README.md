# gca-Intranet — WordPress (Docker) Dev Environment

Local WordPress stack (Docker Compose) with wp-cli auto-install and GCA Intranet themes.  

This repo also includes an AWS-friendly WordPress container build for deployment to EC2 / ECS-style environments.

---

## What’s in this repo

### WordPress
- WordPress runs from the official WordPress Docker image
- WordPress core is **not** committed to Git

### Themes (committed to Git)
Custom themes live under:
- `wp-content/themes/gca-intranet-foundation` (parent theme)
- `wp-content/themes/gca-intranet` (child theme)

### Database
- MySQL 8.0 for local development (via Docker Compose)

### One-time bootstrap via `wp-cli`
A `wp-cli` container handles the one-time WordPress setup:
- Creates `wp-config.php`
- Installs WordPress
- Activates the GCA theme
- Sets permalinks

### AWS container build
- A `Dockerfile` is included for AWS container platforms
- In AWS you would typically use RDS MySQL
- DB credentials are provided via environment variables or secrets

---

## Prerequisites
- Docker Desktop (or Docker Engine) installed and running
- Git

---

## Run (Local or EC2)

### 1) Create environment file
Copy the example file and adjust values if needed:

```bash
cp .env.example .env
```

### Common values you may want to change
- `WP_URL`
- `WP_PORT`
- Admin username / password

---

## 2) Start containers

Build and start WordPress + MySQL:

```bash
docker compose --env-file .env up -d --build
```

(Optional) Confirm services are running:

```bash
docker compose ps
```

> Note: the first run can take a short while while MySQL initialises.

---

## 3) One-time initialise WordPress

Run the `wp-cli` bootstrap container.

This is safe to re-run — it will skip steps if WordPress is already installed.

```bash
docker compose --env-file .env run --rm wpcli
```

This will:
- Ensure WordPress core exists
- Create `wp-config.php` (if missing)
- Install WordPress
- Activate the GCA theme
- Set permalink structure

---

## Access
- Site: `http://localhost:8080` (or whatever `WP_PORT` is set to)
- Admin: `http://localhost:8080/wp-admin`

Admin credentials are defined in `.env`:
- `WP_ADMIN_USER`
- `WP_ADMIN_PASSWORD`

---

## Reset (wipe database + WordPress volumes)

If things get into a bad state, do a full reset:

```bash
docker compose down -v
docker compose --env-file .env up -d --build
docker compose --env-file .env run --rm wpcli
```

This removes all local data and re-initialises WordPress.

---

## Theme development notes
- Themes live directly in this repo under `wp-content/themes/`
- No symlinks are used
- Changes to themes should be committed to Git
- WordPress core is provided entirely by Docker

---

## EC2 / AWS notes
- Set `WP_URL` to the EC2 public IP or DNS name  
  e.g. `http://<public-ip>` or `https://intranet.example.gov.uk`
- Set `WP_PORT=80` if exposing WordPress directly

In production:
- Use RDS MySQL
- Do **not** use the local MySQL container
- `.env` is local only
- Never commit `.env`
- Use `.env.example` as the template

---

## Troubleshooting

### Docker not running
Start Docker Desktop and retry.

### Port already in use
- Change `WP_PORT` in `.env`
- Restart containers

### Stuck on WordPress install screen
Run the `wp-cli` bootstrap again:

```bash
docker compose --env-file .env run --rm wpcli
```

If still broken, do a full reset (see above).

### Database connection errors
Confirm MySQL container is running:

```bash
docker compose ps
```

Check DB logs:

```bash
docker compose logs db
