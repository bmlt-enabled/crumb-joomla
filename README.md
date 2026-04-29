<p align="center">
  <img src="crumb-logo.svg" alt="Crumb Widget logo" width="128" height="128">
</p>

# Crumb for Joomla

Joomla extension package that embeds the [Crumb](https://crumb.bmlt.app/) BMLT meeting-finder widget in your site. Bundles a content plugin (for the `{crumb …}` shortcode in articles) and a site module (for module positions). Supports **Joomla 4, 5, and 6**.

Sister projects:
- [crumb](https://github.com/bmlt-enabled/crumb) — WordPress plugin
- [crumb-drupal](https://github.com/bmlt-enabled/crumb-drupal) — Drupal module
- [crumb-widget](https://github.com/bmlt-enabled/crumb-widget) — the underlying JS widget

## Requirements

- Joomla 4.0+, 5.x, or 6.x
- PHP 8.2+

## Install

1. Download `pkg_crumb.zip` from the [Releases page](https://github.com/bmlt-enabled/crumb-joomla/releases).
2. In Joomla admin: **System → Install → Extensions → Upload Package File**, drop the zip.
3. Enable the **Content - Crumb** plugin under **System → Plugins** (it ships disabled by default, like all Joomla content plugins).
4. Optionally publish the **Crumb** module to a position under **Content → Site Modules**.

## Usage

### In articles (content plugin)

After enabling **Content - Crumb**, drop a shortcode anywhere in an article:

```
{crumb}
```

By default the plugin reads its settings (server URL, service body IDs, etc.) from the plugin's params. Override per-shortcode:

```
{crumb server="https://bmlt.example.org/main_server" service_body="42" view="map"}
```

Recognised shortcode args: `server`, `service_body`, `view` (`list`/`map`), `geolocation` (`true`/`false`).

### In a module position (module)

Publish the **Crumb** module to any module position (sidebar, footer, etc.). Configure server URL and other options on the module's edit screen.

## Settings

Both the plugin and the module expose the same fields:

| Field | Description |
|---|---|
| **BMLT Server URL** | Required. Full URL to your BMLT server (e.g. `https://your-server/main_server`). |
| **Service Body IDs** | Optional. Single ID or comma-separated list. Empty = all meetings. Child service bodies are always included. |
| **Default View** | `list` or `map`. Overridable per shortcode and via `?view=` query param. |
| **CSS Template** | `Full Width` or `Full Width (Force Viewport)` for breaking out of narrow content areas. |
| **Base Path for Pretty URLs** | e.g. `meetings` → `/meetings/monday-night-meeting-42` (History API routing). Empty = hash-based routing. |
| **Widget Configuration (JSON)** | Advanced. JSON for `CrumbWidgetConfig`. See the [widget docs](https://crumb.bmlt.app/) for the full schema (`darkMode`, `geolocation`, `nowOffset`, `columns`, etc.). |

## Local development

```bash
make dev          # starts Joomla + MariaDB on http://localhost:8080
make install      # builds pkg_crumb.zip and installs it into the running container
make logs         # tail Joomla logs
make shell        # bash inside the container
make down         # stop the stack
make nuke         # stop and wipe volumes (fresh DB next start)
```

Joomla admin defaults: `admin` / `adminadminadmin`.

To target Joomla 6 specifically:

```bash
docker compose build --build-arg JOOMLA_TAG=6-php8.3-apache
docker compose up
```

### Build the installable zip

```bash
make build       # → build/pkg_crumb.zip
```

### Lint and tests

```bash
make lint        # phpcs (PSR-12)
make fmt         # phpcbf auto-fix
make test        # phpunit
```

## CI / Release

- PRs run lint + PHPUnit on PHP 8.2/8.3/8.4 + a package build (artifact uploaded).
- Pushing a tag (`v0.1.0`, etc.) builds the package and creates a GitHub release with the zip attached.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
