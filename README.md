# gca-Intranet — WordPress Intranet (Themes + Docker Dev Environment)

Local WordPress stack (Docker Compose) with wp-cli auto-install and GCA Intranet themes.

This repo also includes an AWS-friendly WordPress container build for deployment to EC2 / ECS-style environments.

---

## What’s in this repo

### WordPress
- WordPress runs from the official WordPress Docker image
- WordPress core is **not** committed to Git

### Themes & Plugins (committed to Git)
Custom themes and plugins live under:
- `wp-content/themes/gca-intranet-foundation` (parent theme)
- `wp-content/themes/gca-intranet` (child theme)
- `wp-content/plugins/gca-custom`

*Note: The entire local `./wp-content` folder is bind-mounted to the container locally. If you install a new plugin via the WordPress Admin GUI, it will sync to your local machine so it can be committed to Git.*

### Database
- MySQL 8.0 for local development (via Docker Compose)

### One-time bootstrap via wp-cli
A `wp-cli` container handles the one-time WordPress setup:
- Creates `wp-config.php`
- Installs WordPress
- Activates the GCA theme
- Sets permalinks

### AWS container build
A `Dockerfile` is included for AWS container platforms.
In AWS you would typically use RDS MySQL.
DB credentials should be provided via environment variables or secrets.

---

## Prerequisites
- Docker Desktop (or Docker Engine) installed and running
- Git
- *No local PHP, Node.js, or npm required (Docker handles all dependencies).*

---

## Run (Local or EC2)

### 1) Create environment file
Copy the example file and adjust values if needed:

```bash
cp .env.example .env
```

Common values you may want to change:
- `WP_URL`
- `WP_PORT`
- Admin username / password
- **Important:** Ensure `WP_HOME` and `WP_SITEURL` explicitly match your URL and port (e.g., `http://localhost:8090`) to prevent WordPress redirect loops.

### 2) Start containers
If you're on local you need to pass in the compose file to use: `docker-compose.local.yml`.

**LOCAL build:**

```bash
docker compose -f docker-compose.local.yml up -d --build
```


**SERVER build:**

```bash
docker compose --env-file .env up -d --build
```

(Optional) Confirm services are running:

```bash
docker compose ps
```

Note: the first run can take a short while while MySQL initialises.

### 3) One-time initialise WordPress
Run the wp-cli bootstrap container. This is safe to re-run — it will skip steps if WordPress is already installed.

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

## Front-end build (theme assets)
If your theme uses an npm build step for CSS/JS, use the built-in Docker container to compile assets (no local Node installation required).

To install dependencies and build once:

```bash
docker compose -f docker-compose.local.yml run --rm theme-builder
```

To watch for file changes during development:

```bash
docker compose -f docker-compose.local.yml run --rm theme-builder npm run watch
```

> Whether compiled assets should be committed depends on repo convention. Follow existing patterns in this repo.

---

## Header/Footer QA checklist (responsive)
Use these checks when validating header/footer changes.

### Expected layout
- **Desktop (≥992px)**
  - Logo sits in the left gutter (outside the boxed container alignment)
  - Boxed area: row 1 = utility links + search, row 2 = primary nav
  - Dropdown chevrons appear to the **right** of nav items (outlined “V”)

- **Mobile (<992px)**
  - Row 1: logo left, utility links top-right (stacked)
  - Row 2: search left, menu toggle right

### Interaction checks
- Keyboard: Tab through skip link → utility → search → nav; focus visible throughout
- Mobile menu toggle updates `aria-expanded`
- Dropdowns open via click and keyboard (Enter/Space)

---

## EC2 / AWS notes
- Set `WP_URL` to the EC2 public IP or DNS name  
  e.g. `http://<public-ip>` or `https://intranet.example.gov.uk`
- Set `WP_PORT=80` if exposing WordPress directly

In production:
- Use RDS MySQL
- Do not use the local MySQL container
- `.env` is local only — **never commit `.env`**
- Use `.env.example` as the template

---

## Troubleshooting

### Docker not running
Start Docker Desktop and retry.

### Port already in use
- Change `WP_PORT` in `.env`
- Restart containers

### Redirecting to Port 80 or blank localhost
WordPress relies on absolute URLs. Ensure `WP_HOME` and `WP_SITEURL` in your `.env` file explicitly include your custom port (e.g., `http://localhost:8090`). Restart the container and test in an **Incognito Window** to clear cached browser redirects.

### Stuck on WordPress install screen
Run the wp-cli bootstrap again:

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
```

---
