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

## Feature Flags

The `gca-feature-flags` plugin provides a lightweight feature-flag system for toggling functionality without code deploys.

### How it works

- Flags are **registered in code** (in the theme) and **toggled via the WordPress Admin UI**.
- The Admin UI lives at **WP Admin → Settings → Feature Flags**.
- Each flag has a label, optional description, optional tags, and a default on/off state.
- Flags that have never been saved in the admin fall back to their registered `default`.

### Plugin location

```
wp-content/plugins/gca-feature-flags/gca-feature-flags.php
```

This plugin is committed to Git and activated alongside the theme.

### Registering a flag

Each feature lives in its own file under `wp-content/themes/gca-intranet/inc/features/`. All files in that directory are automatically loaded by `inc/features.php`.

Create a new file, e.g. `inc/features/my-feature.php`:

```php
gca_register_feature_flag('my-feature', [
    'label'       => 'My Feature',
    'description' => 'Short description of what this controls.',
    'default'     => false,          // on or off by default
    'tags'        => ['ui', 'beta'], // optional — used to filter flags in the admin UI
]);
```

The flag slug (`my-feature`) must be a unique, URL-safe string. It is used as the key for checking and storing the flag's state.

### Checking a flag

Call `gca_flag_enabled()` anywhere in template files, theme PHP, or plugins:

```php
if (gca_flag_enabled('my-feature')) {
    // render the new feature
}
```

### Toggling a flag

1. Go to **WP Admin → Settings → Feature Flags**.
2. Use the toggle switch next to the flag you want to change.
3. Click **Save Changes** — changes take effect immediately.

The admin page supports **search** and **tag filtering** to quickly find flags in large lists.

### Example: Environment Banner

The built-in `environment-banner` flag (`inc/features/environment-banner.php`) is a working example:

```php
gca_register_feature_flag('environment-banner', [
    'label'       => 'Environment Banner',
    'description' => 'Shows a yellow banner at the top of every page indicating the current environment. Never shown in production.',
    'default'     => false,
    'tags'        => ['ui', 'environment'],
]);

add_action('wp_body_open', function (): void {
    if (!gca_flag_enabled('environment-banner')) {
        return;
    }
    // ... render the banner
});
```

### Reference

| Function | Description |
|---|---|
| `gca_register_feature_flag($id, $args)` | Register a flag (call before any `is_enabled` check) |
| `gca_flag_enabled($id)` | Returns `true` if the flag is enabled, `false` otherwise |

### Feature inventory

Each feature file lives in `wp-content/themes/gca-intranet-foundation/inc/features/`.

| Flag ID | Label | Description | Cron job |
|---|---|---|---|
| `environment-banner` | Environment Banner | Shows a yellow banner at the top of every page indicating the current environment. Never shown in production. | — |
| `cron-manager` | Cron Manager | Admin interface (Tools → Cron Jobs) to view, create, edit, delete, and manually run WordPress cron jobs. | — |
| `workday-user-sync` | Workday User Sync | Syncs WordPress users with the Workday staff list API. Schedule via Tools → Cron Jobs or run manually with `wp gca sync-users`. | `gca_sync_workday_users` |
| `google-profile-picture` | Google Profile Picture Sync | Downloads the user's Google profile picture on each SSO login and uses it as their avatar across the site. | — |
| `purge-events` | Purge Events | Permanently deletes event posts more than one month after their end date (or start date if no end date is set). Schedule via Tools → Cron Jobs or run manually with `wp gca purge-events`. | `gca_purge_events` |
| `author-selector` | Author Selector | Replaces the default WordPress author meta box on `blog` and `work_update` posts with a searchable Select2 dropdown. Each option shows the user's profile image (Google SSO photo → Gravatar → WP default). All users are pre-loaded as inline JSON — no AJAX required. | — |
| `directorate-contact` | Directorate Contact Popup | Shows a contact popup in the footer for pages tagged with a `responsible_team` taxonomy term. The popup title, description, and contact items are configured on the term edit screen (Taxonomy → Edit Term → Directorate Contact Popup). The responsible-team pill always renders; the popup is only attached when this flag is enabled. | — |

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
