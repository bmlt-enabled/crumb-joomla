# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

## 0.3.0 (May 6, 2026)

### Added
- **Geolocation Radius** setting on both the content plugin and site module — dedicated field for geolocation search radius. Positive integer = fixed radius in miles (or km per server settings). Negative integer = BMLT auto-radius (e.g. `-50` finds ~50 nearby meetings).
- `geolocation_radius` shortcode attribute on the content plugin to override the radius per article (`{crumb geolocation_radius="-50"}`).

## 0.2.0 (May 5, 2026)

### Added
- **Format IDs** field on the content plugin and site module — single ID or comma-separated list of BMLT format IDs to lock the widget to. Can be overridden per shortcode via `format_ids="17,54"`.

## 0.1.0 (April 29, 2026)

Initial release. Embed the [Crumb](https://crumb.bmlt.app/) BMLT meeting-finder widget in Joomla 4, 5, or 6 sites.

### Added
- **Content plugin** (`plg_content_crumb`) — replaces `{crumb …}` shortcodes in articles. Per-shortcode args (`server`, `service_body`, `view`, `geolocation`) override the plugin's saved params.
- **Site module** (`mod_crumb`) — renders the widget in any Joomla module position.
- **Package** (`pkg_crumb`) — single-zip install bundling both extensions.
- **Settings** on plugin and module: BMLT server URL, service body IDs, default view (list/map), CSS template (full-width / full-width-force), base path for History API pretty URLs, and a JSON `widget_config` for advanced `CrumbWidgetConfig` options (dark mode, geolocation, columns, etc.).
- **Docker Compose dev harness** (Joomla 5 LTS by default; Joomla 6 via `JOOMLA_TAG` build arg) with a `Makefile` covering build / install / lint / test / shell / nuke.
- **PHPUnit unit tests** for the renderer (server-required guard, attribute escaping, view validation, base-path normalization, JSON widget-config emission, override precedence).
- **GitHub Actions CI**: lint, multi-version PHPUnit (8.2 / 8.3 / 8.4), XML manifest validation, package build on PR (artifact uploaded), GitHub release with attached zip on tag push.
