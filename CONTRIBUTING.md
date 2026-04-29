# Contributing

Thanks for considering a contribution.

## Dev setup

The repo ships a Docker harness that brings up a real Joomla site backed by MariaDB, lets you build and install the package into it from source, and tears down cleanly when you're done.

### One-time prerequisites

- Docker Desktop (or any Docker Engine ≥ 24) with `docker compose`
- PHP 8.2+ and Composer on the host (only needed to run `make lint` / `make test` outside the container)
- `zip` and `xmllint` on the host (used by `make build` and CI's manifest validation)

### Bring up the stack

```bash
make dev          # Joomla + MariaDB on http://localhost:8080
```

First boot takes ~30–60s while Joomla's silent installer initializes the database. Watch progress with `make logs`.

| URL                                 | Purpose                |
|-------------------------------------|------------------------|
| http://localhost:8080               | Public site (frontend) |
| http://localhost:8080/administrator | Admin dashboard        |

**Default admin credentials** (set in `docker-compose.yml`):

- Username: `admin`
- Password: `adminadminadmin`

To target a specific Joomla major version (default is 5 LTS):

```bash
docker compose build --build-arg JOOMLA_TAG=6-php8.3-apache
docker compose up
```

### Install / reinstall the package

```bash
make install      # build pkg_crumb.zip and install it into the running container
```

`make install` is idempotent — re-run it any time you change source. It builds a fresh package zip, copies it in, and runs `php cli/joomla.php extension:install --path=/tmp/pkg_crumb.zip`.

After install, three extensions appear in **Extensions → Manage**:

- `plg_content_crumb` — content plugin (**ships disabled** by Joomla convention)
- `mod_crumb` — site module
- `Crumb` — the package wrapper

### Enable and configure the content plugin

Joomla disables newly installed content plugins by default. Enable it via either path:

**Admin UI:**
1. **System → Plugins** → search "Crumb"
2. Open **Content - Crumb**, set the BMLT server URL on the **Plugin** tab, save, set status to **Enabled**

**CLI (faster for iteration):**
```bash
docker compose exec joomla php cli/joomla.php plugin:publish --plugin=crumb --group=content
```

Then drop `{crumb}` (or `{crumb server="https://..." view="map"}`) into any article body and reload the public page.

### Module setup

Publish the module via **Content → Site Modules → New → Crumb**, pick a position from your active template, set the BMLT server URL, save.

### Other targets

```bash
make test         # PHPUnit unit tests (runs on host, no container needed)
make lint         # phpcs (PSR-12)
make fmt          # phpcbf auto-fix
make build        # build/pkg_crumb.zip without installing
make logs         # tail the Joomla container's apache + php logs
make shell        # bash inside the joomla container
make down         # stop the stack (data persists)
make nuke         # stop and wipe volumes (fresh DB next start)
```

### Troubleshooting

- **`make install` fails with "Unable to install extension"** — Joomla's CLI swallows install errors. Check that no stale `crumb` rows exist in `#__extensions` from a prior failed install: `make shell` then `php cli/joomla.php extension:list | grep -i crumb`. Remove with `php cli/joomla.php extension:remove <id>`.
- **Admin redirects to install screen** — the silent installer hasn't finished yet; wait for `make logs` to settle, or `make nuke && make dev` for a clean slate.
- **Plugin enabled but `{crumb}` doesn't render** — confirm the article's text filter (Global Configuration → Text Filters) isn't stripping `{}`. The "Default Black List" filter strips a lot; switch the relevant user group to "No Filtering" for testing.

## Pull requests

- Target the `main` branch.
- Run `make lint` and `make test` before pushing.
- New features should include unit tests where the rendering / parsing logic is exercised.
- Joomla manifest files (`*.xml`) are validated with `xmllint --noout` in CI; keep them well-formed.

## Cross-version support

The package targets **Joomla 4, 5, and 6**. Avoid:

- Legacy classes (`JFactory`, `JPlugin`, `JModuleHelper`) — use the namespaced `Joomla\CMS\…` equivalents.
- Calling `JLoader` / autoload by side-effect — rely on the namespace declared in each manifest.
- Writing the entry-point class as a method on `JPlugin` rather than `\Joomla\CMS\Plugin\CMSPlugin`.

## Reporting issues

Please open an issue on the [GitHub tracker](https://github.com/bmlt-enabled/crumb-joomla/issues) with:

- Joomla version (`Help → System Information`)
- PHP version
- Plugin / module version (visible in **Extensions → Manage**)
- Steps to reproduce
