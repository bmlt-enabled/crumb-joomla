<?php

/**
 * @package         Crumb
 * @copyright   (C) 2026 bmlt-enabled. All rights reserved.
 * @license         GNU General Public License version 2 or later
 */

namespace BmltEnabled\Plugin\Content\Crumb\Extension;

\defined('_JEXEC') or die;

use BmltEnabled\Plugin\Content\Crumb\Helper\CrumbRenderer;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Replaces {crumb ...} shortcodes in article content with the Crumb widget.
 *
 * Recognised forms:
 *   {crumb}
 *   {crumb server="https://..."}
 *   {crumb server="https://..." service_body="42" view="map" geolocation="true"}
 *
 * Per-shortcode args override the plugin's saved params for that one render.
 */
final class Crumb extends CMSPlugin
{
    /**
     * @var bool
     */
    protected $autoloadLanguage = true;

    public function onContentPrepare(string $context, &$article = null, &$params = null, int $page = 0): void
    {
        if ($article === null || !isset($article->text) || strpos($article->text, '{crumb') === false) {
            return;
        }

        // com_finder content indexing should not see widget HTML.
        if ($context === 'com_finder.indexer') {
            return;
        }

        $settings = [
            'server'             => (string) $this->params->get('server', ''),
            'service_body'       => (string) $this->params->get('service_body', ''),
            'format_ids'         => (string) $this->params->get('format_ids', ''),
            'view'               => (string) $this->params->get('view', ''),
            'css_template'       => (string) $this->params->get('css_template', ''),
            'base_path'          => (string) $this->params->get('base_path', ''),
            'geolocation_radius' => (string) $this->params->get('geolocation_radius', ''),
            'widget_config'      => (string) $this->params->get('widget_config', ''),
        ];

        $article->text = preg_replace_callback(
            '/\{crumb(\s+[^}]*)?\}/i',
            static function (array $m) use ($settings): string {
                $overrides = self::parseShortcodeArgs($m[1] ?? '');
                return CrumbRenderer::render($settings, $overrides);
            },
            $article->text
        );
    }

    /**
     * Parse `key="value" key2='value2' key3=value3` into an associative array.
     *
     * @return array<string,string>
     */
    private static function parseShortcodeArgs(string $raw): array
    {
        $out = [];
        if (trim($raw) === '') {
            return $out;
        }

        // Match key="…", key='…', or key=bareword.
        $pattern = '/([a-zA-Z_][a-zA-Z0-9_-]*)\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s"\'}]+))/';
        preg_match_all($pattern, $raw, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            $key   = strtolower($m[1]);
            $value = $m[3] ?? $m[4] ?? $m[5] ?? '';
            $out[$key] = $value;
        }
        return $out;
    }
}
