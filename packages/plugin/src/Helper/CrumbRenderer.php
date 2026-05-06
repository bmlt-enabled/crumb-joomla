<?php

/**
 * @package         Crumb
 * @copyright   (C) 2026 bmlt-enabled. All rights reserved.
 * @license         GNU General Public License version 2 or later
 */

namespace BmltEnabled\Plugin\Content\Crumb\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\WebAsset\WebAssetManager;

/**
 * Builds the widget HTML for the Crumb content plugin and module.
 *
 * Rendering is intentionally string-based (no Joomla render arrays) so the
 * same helper can be reused by both the content plugin and the module without
 * coupling either to a specific document context.
 */
final class CrumbRenderer
{
    public const ALLOWED_VIEWS = ['list', 'map'];

    /**
     * Build the widget markup. Returns an empty string when no server is
     * configured, plus a visible error notice for editors.
     *
     * @param array<string,mixed> $settings  Resolved settings from plugin/module params.
     * @param array<string,mixed> $overrides Per-instance overrides (shortcode args).
     */
    public static function render(array $settings, array $overrides = []): string
    {
        $server = trim((string) ($overrides['server'] ?? $settings['server'] ?? ''));
        if ($server === '') {
            return '<p style="color:red"><strong>Crumb:</strong> a <code>server</code> URL is required.</p>';
        }

        $serviceBody = $overrides['service_body'] ?? $settings['service_body'] ?? '';
        $formatIds   = $overrides['format_ids'] ?? $settings['format_ids'] ?? '';
        $viewRaw     = $overrides['view'] ?? $settings['view'] ?? '';
        $view        = \in_array($viewRaw, self::ALLOWED_VIEWS, true) ? $viewRaw : '';
        $basePath    = trim((string) ($settings['base_path'] ?? ''), '/');
        $template    = (string) ($settings['css_template'] ?? '');
        $widgetConf  = trim((string) ($settings['widget_config'] ?? ''));

        $attrs = [
            'id'          => 'crumb-widget',
            'data-server' => $server,
        ];
        if ($serviceBody !== null && $serviceBody !== '') {
            $attrs['data-service-body'] = trim((string) $serviceBody);
        }
        if ($formatIds !== null && $formatIds !== '') {
            $attrs['data-format-ids'] = trim((string) $formatIds);
        }
        if ($view !== '') {
            $attrs['data-view'] = $view;
        }
        if ($basePath !== '') {
            $attrs['data-path'] = '/' . $basePath;
        }
        if (isset($overrides['geolocation']) && $overrides['geolocation'] !== null) {
            $geo = filter_var($overrides['geolocation'], FILTER_VALIDATE_BOOLEAN);
            $attrs['data-geolocation'] = $geo ? 'true' : 'false';
        }

        $attrString = '';
        foreach ($attrs as $k => $v) {
            $attrString .= ' ' . $k . '="' . htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') . '"';
        }

        $widget = '<div' . $attrString . '></div>';

        // Build CrumbWidgetConfig array from widget_config JSON plus settings/overrides.
        $configArray = [];
        if ($widgetConf !== '') {
            $decoded = json_decode($widgetConf, true);
            if (\is_array($decoded)) {
                $configArray = $decoded;
            }
        }

        // Merge geolocation_radius admin setting if not already set in widget_config JSON.
        $geoRadiusSetting = (string) ($settings['geolocation_radius'] ?? '');
        if ($geoRadiusSetting !== '' && !isset($configArray['geolocationRadius'])) {
            $radius = (int) $geoRadiusSetting;
            if ($radius !== 0) {
                $configArray['geolocationRadius'] = $radius;
            }
        }

        // Per-shortcode override takes precedence.
        $geoRadiusOverride = isset($overrides['geolocation_radius']) ? (string) $overrides['geolocation_radius'] : null;
        if ($geoRadiusOverride !== null && $geoRadiusOverride !== '') {
            $radius = (int) $geoRadiusOverride;
            if ($radius !== 0) {
                $configArray['geolocationRadius'] = $radius;
            }
        }

        $configScript = '';
        if (!empty($configArray)) {
            $configScript = '<script>window.CrumbWidgetConfig = '
                . json_encode($configArray, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)
                . ';</script>';
        }

        // Loader script (CDN). Pinned to a major-minor channel so cache stays warm
        // across patches; switch to a fully-pinned URL to lock the version.
        $loader = '<script src="https://cdn.aws.bmlt.app/crumb-widget.js" defer></script>';

        $output = $configScript . $widget . $loader;

        if ($template === 'full_width') {
            return '<div class="crumb-full-width">' . $output . '</div>';
        }
        if ($template === 'full_width_force') {
            return '<div class="crumb-full-width-force">' . $output . '</div>';
        }

        return $output;
    }
}
