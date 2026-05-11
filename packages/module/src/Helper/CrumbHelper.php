<?php

/**
 * @package         Crumb
 * @copyright   (C) 2026 bmlt-enabled. All rights reserved.
 * @license         GNU General Public License version 2 or later
 */

namespace BmltEnabled\Module\Crumb\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Resolves module params into the settings array consumed by CrumbRenderer.
 *
 * Kept tiny on purpose — the actual HTML construction lives in the renderer
 * so the plugin and module render identically.
 */
class CrumbHelper
{
    public const ALLOWED_VIEWS = ['list', 'map'];

    /**
     * @return array<string,string>
     */
    public function getSettings(Registry $params): array
    {
        return [
            'server'             => trim((string) $params->get('server', '')),
            'service_body'       => (string) $params->get('service_body', ''),
            'format_ids'         => (string) $params->get('format_ids', ''),
            'view'               => (string) $params->get('view', ''),
            'css_template'       => (string) $params->get('css_template', ''),
            'base_path'          => (string) $params->get('base_path', ''),
            'geolocation_radius' => (string) $params->get('geolocation_radius', ''),
            'update_url'         => (string) $params->get('update_url', ''),
            'columns'            => (string) $params->get('columns', ''),
            'widget_config'      => (string) $params->get('widget_config', ''),
        ];
    }

    /**
     * Build the widget HTML. Duplicates the plugin's renderer to keep both
     * extensions independently installable; ~30 lines is cheaper than a
     * shared library dependency.
     *
     * @param array<string,string> $settings
     */
    public function render(array $settings): string
    {
        $server = $settings['server'] ?? '';
        if ($server === '') {
            return '<p style="color:red"><strong>Crumb:</strong> a <code>server</code> URL is required.</p>';
        }

        $serviceBody = $settings['service_body'] ?? '';
        $formatIds   = $settings['format_ids'] ?? '';
        $viewRaw     = $settings['view'] ?? '';
        $view        = \in_array($viewRaw, self::ALLOWED_VIEWS, true) ? $viewRaw : '';
        $basePath    = trim((string) ($settings['base_path'] ?? ''), '/');
        $template    = (string) ($settings['css_template'] ?? '');
        $updateUrl   = trim((string) ($settings['update_url'] ?? ''));
        $columns     = trim((string) ($settings['columns'] ?? ''));
        $widgetConf  = trim((string) ($settings['widget_config'] ?? ''));

        $attrs = [
            'id'          => 'crumb-widget',
            'data-server' => $server,
        ];
        if ($serviceBody !== '') {
            $attrs['data-service-body'] = trim((string) $serviceBody);
        }
        if ($formatIds !== '') {
            $attrs['data-format-ids'] = trim((string) $formatIds);
        }
        if ($view !== '') {
            $attrs['data-view'] = $view;
        }
        if ($basePath !== '') {
            $attrs['data-path'] = '/' . $basePath;
        }
        if ($updateUrl !== '') {
            $attrs['data-update-url'] = $updateUrl;
        }
        if ($columns !== '') {
            $attrs['data-columns'] = $columns;
        }

        $attrString = '';
        foreach ($attrs as $k => $v) {
            $attrString .= ' ' . $k . '="' . htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8') . '"';
        }

        $widget = '<div' . $attrString . '></div>';

        // Build CrumbWidgetConfig array from widget_config JSON plus settings.
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

        $configScript = '';
        if (!empty($configArray)) {
            $configScript = '<script>window.CrumbWidgetConfig = '
                . json_encode($configArray, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP)
                . ';</script>';
        }

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
