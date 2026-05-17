<?php

declare(strict_types=1);

namespace Tests\Unit;

use BmltEnabled\Plugin\Content\Crumb\Helper\CrumbRenderer;
use PHPUnit\Framework\TestCase;

final class CrumbRendererTest extends TestCase
{
    public function testReturnsErrorWhenServerMissing(): void
    {
        $output = CrumbRenderer::render([]);
        $this->assertStringContainsString('Crumb:', $output);
        $this->assertStringContainsString('server', $output);
    }

    public function testEmitsWidgetDivAndLoaderWhenServerSet(): void
    {
        $output = CrumbRenderer::render(['server' => 'https://bmlt.example.org/main_server']);
        $this->assertStringContainsString('id="crumb-widget"', $output);
        $this->assertStringContainsString('data-server="https://bmlt.example.org/main_server"', $output);
        $this->assertStringContainsString('crumb-widget.js', $output);
    }

    public function testServiceBodyAttributeOmittedWhenEmpty(): void
    {
        $output = CrumbRenderer::render(['server' => 'https://x/main_server']);
        $this->assertStringNotContainsString('data-service-body', $output);
    }

    public function testServiceBodyAttributeEmittedWhenSet(): void
    {
        $output = CrumbRenderer::render([
            'server'       => 'https://x/main_server',
            'service_body' => '42,57',
        ]);
        $this->assertStringContainsString('data-service-body="42,57"', $output);
    }

    public function testFormatIdsAttributeEmittedFromSettings(): void
    {
        $output = CrumbRenderer::render([
            'server'     => 'https://x/main_server',
            'format_ids' => '17,54',
        ]);
        $this->assertStringContainsString('data-format-ids="17,54"', $output);
    }

    public function testFormatIdsAttributeOmittedWhenEmpty(): void
    {
        $output = CrumbRenderer::render(['server' => 'https://x/main_server']);
        $this->assertStringNotContainsString('data-format-ids', $output);
    }

    public function testFormatIdsOverrideBeatsSavedSetting(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'format_ids' => '99'],
            ['format_ids' => '17']
        );
        $this->assertStringContainsString('data-format-ids="17"', $output);
        $this->assertStringNotContainsString('data-format-ids="99"', $output);
    }

    public function testEmptyFormatIdsOverrideOmitsAttribute(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'format_ids' => '99'],
            ['format_ids' => '']
        );
        $this->assertStringNotContainsString('data-format-ids', $output);
    }

    public function testColumnsAttributeEmittedFromSettings(): void
    {
        $output = CrumbRenderer::render([
            'server'  => 'https://x/main_server',
            'columns' => 'time,name,location,address,service_body',
        ]);
        $this->assertStringContainsString('data-columns="time,name,location,address,service_body"', $output);
    }

    public function testColumnsAttributeOmittedWhenEmpty(): void
    {
        $output = CrumbRenderer::render(['server' => 'https://x/main_server']);
        $this->assertStringNotContainsString('data-columns', $output);
    }

    public function testColumnsOverrideBeatsSavedSetting(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'columns' => 'time,name'],
            ['columns' => 'name,location']
        );
        $this->assertStringContainsString('data-columns="name,location"', $output);
        $this->assertStringNotContainsString('data-columns="time,name"', $output);
    }

    public function testColumnsOverrideTrimmed(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server'],
            ['columns' => '  time,name  ']
        );
        $this->assertStringContainsString('data-columns="time,name"', $output);
    }

    public function testEmptyColumnsOverrideOmitsAttribute(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'columns' => 'time,name'],
            ['columns' => '']
        );
        $this->assertStringNotContainsString('data-columns', $output);
    }

    public function testQueryAttributeEmittedFromOverride(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server'],
            ['query' => 'meeting_key=location_nation&meeting_key_value[]=USA']
        );
        // htmlspecialchars escapes & → &amp;; [ ] pass through (Joomla's {…} shortcode
        // closer doesn't conflict with brackets, unlike WordPress / Drupal).
        $this->assertStringContainsString(
            'data-query="meeting_key=location_nation&amp;meeting_key_value[]=USA"',
            $output
        );
    }

    public function testQueryOverrideTrimmed(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server'],
            ['query' => '  weekdays=2  ']
        );
        $this->assertStringContainsString('data-query="weekdays=2"', $output);
    }

    public function testEmptyQueryOverrideOmitsAttribute(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server'],
            ['query' => '']
        );
        $this->assertStringNotContainsString('data-query', $output);
    }

    public function testNoQueryOmitsAttribute(): void
    {
        $output = CrumbRenderer::render(['server' => 'https://x/main_server']);
        $this->assertStringNotContainsString('data-query', $output);
    }

    public function testInvalidViewIsDroppedNotPassedThrough(): void
    {
        $output = CrumbRenderer::render([
            'server' => 'https://x/main_server',
            'view'   => 'banana',
        ]);
        $this->assertStringNotContainsString('data-view', $output);
    }

    public function testValidViewIsEmitted(): void
    {
        $output = CrumbRenderer::render([
            'server' => 'https://x/main_server',
            'view'   => 'map',
        ]);
        $this->assertStringContainsString('data-view="map"', $output);
    }

    public function testBasePathNormalisedWithLeadingSlash(): void
    {
        $output = CrumbRenderer::render([
            'server'    => 'https://x/main_server',
            'base_path' => 'meetings',
        ]);
        $this->assertStringContainsString('data-path="/meetings"', $output);
    }

    public function testInvalidJsonWidgetConfigIsIgnoredSilently(): void
    {
        $output = CrumbRenderer::render([
            'server'        => 'https://x/main_server',
            'widget_config' => '{ not valid json',
        ]);
        $this->assertStringNotContainsString('CrumbWidgetConfig', $output);
    }

    public function testValidJsonWidgetConfigIsEmittedAsScript(): void
    {
        $output = CrumbRenderer::render([
            'server'        => 'https://x/main_server',
            'widget_config' => '{"darkMode":"auto","geolocation":true}',
        ]);
        $this->assertStringContainsString('window.CrumbWidgetConfig', $output);
        $this->assertStringContainsString('"darkMode":"auto"', $output);
    }

    public function testPerInstanceOverridesBeatSavedSettings(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://saved/main_server', 'view' => 'list'],
            ['view' => 'map']
        );
        $this->assertStringContainsString('data-view="map"', $output);
        $this->assertStringNotContainsString('data-view="list"', $output);
    }

    public function testHtmlSpecialCharsEscapedInAttributes(): void
    {
        $output = CrumbRenderer::render([
            'server'       => 'https://x/main_server',
            'service_body' => '"><script>alert(1)</script>',
        ]);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $output);
    }

    public function testGeolocationRadiusSettingMergesAsInteger(): void
    {
        $output = CrumbRenderer::render([
            'server'             => 'https://x/main_server',
            'geolocation_radius' => '-50',
        ]);
        $this->assertStringContainsString('window.CrumbWidgetConfig', $output);
        $this->assertStringContainsString('"geolocationRadius":-50', $output);
    }

    public function testGeolocationRadiusSettingPreservesIntegerType(): void
    {
        $output = CrumbRenderer::render([
            'server'             => 'https://x/main_server',
            'geolocation_radius' => '25',
        ]);
        preg_match('/window\.CrumbWidgetConfig\s*=\s*(\{.*?\});/', $output, $m);
        $config = json_decode($m[1], true);
        $this->assertSame(25, $config['geolocationRadius'], 'geolocationRadius must be an integer, not a string.');
    }

    public function testGeolocationRadiusOverrideTakesPrecedenceOverSetting(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'geolocation_radius' => '-50'],
            ['geolocation_radius' => '30']
        );
        $this->assertStringContainsString('"geolocationRadius":30', $output);
        $this->assertStringNotContainsString('"geolocationRadius":-50', $output);
    }

    public function testWidgetConfigGeolocationRadiusTakesPrecedenceOverSetting(): void
    {
        $output = CrumbRenderer::render([
            'server'             => 'https://x/main_server',
            'geolocation_radius' => '-50',
            'widget_config'      => '{"geolocationRadius":10}',
        ]);
        $this->assertStringContainsString('"geolocationRadius":10', $output);
        $this->assertStringNotContainsString('"geolocationRadius":-50', $output);
    }

    public function testZeroGeolocationRadiusIsIgnored(): void
    {
        $output = CrumbRenderer::render([
            'server'             => 'https://x/main_server',
            'geolocation_radius' => '0',
        ]);
        $this->assertStringNotContainsString('CrumbWidgetConfig', $output);
    }

    public function testEmptyGeolocationRadiusProducesNoConfigScript(): void
    {
        $output = CrumbRenderer::render(['server' => 'https://x/main_server']);
        $this->assertStringNotContainsString('CrumbWidgetConfig', $output);
    }

    public function testGeolocationSettingOnMergesIntoConfig(): void
    {
        $output = CrumbRenderer::render([
            'server'      => 'https://x/main_server',
            'geolocation' => '1',
        ]);
        $this->assertStringContainsString('window.CrumbWidgetConfig', $output);
        $this->assertStringContainsString('"geolocation":true', $output);
    }

    public function testGeolocationSettingOffMergesIntoConfig(): void
    {
        $output = CrumbRenderer::render([
            'server'      => 'https://x/main_server',
            'geolocation' => '0',
        ]);
        $this->assertStringContainsString('"geolocation":false', $output);
    }

    public function testEmptyGeolocationSettingProducesNoConfigScript(): void
    {
        $output = CrumbRenderer::render([
            'server'      => 'https://x/main_server',
            'geolocation' => '',
        ]);
        $this->assertStringNotContainsString('CrumbWidgetConfig', $output);
    }

    public function testWidgetConfigGeolocationTakesPrecedenceOverSetting(): void
    {
        $output = CrumbRenderer::render([
            'server'        => 'https://x/main_server',
            'geolocation'   => '0',
            'widget_config' => '{"geolocation":true}',
        ]);
        $this->assertStringContainsString('"geolocation":true', $output);
        $this->assertStringNotContainsString('"geolocation":false', $output);
    }

    public function testShortcodeGeolocationOverrideEmitsDataAttribute(): void
    {
        // Per-shortcode geolocation override is emitted as data-geolocation, which the
        // widget reads after CrumbWidgetConfig — so it overrides the admin setting.
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'geolocation' => '0'],
            ['geolocation' => 'true']
        );
        $this->assertStringContainsString('data-geolocation="true"', $output);
    }

    public function testLanguageSettingMergesIntoConfig(): void
    {
        $output = CrumbRenderer::render([
            'server'   => 'https://x/main_server',
            'language' => 'es',
        ]);
        $this->assertStringContainsString('window.CrumbWidgetConfig', $output);
        $this->assertStringContainsString('"language":"es"', $output);
    }

    public function testLanguageOverrideTakesPrecedenceOverSetting(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'language' => 'es'],
            ['language' => 'de']
        );
        $this->assertStringContainsString('"language":"de"', $output);
        $this->assertStringNotContainsString('"language":"es"', $output);
    }

    public function testLanguageOverrideDroppedForUnsupportedCode(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'language' => 'es'],
            ['language' => 'banana']
        );
        // Saved 'es' fills in because the unsupported override never wrote a value.
        $this->assertStringContainsString('"language":"es"', $output);
    }

    public function testWidgetConfigLanguageTakesPrecedenceOverSetting(): void
    {
        $output = CrumbRenderer::render([
            'server'        => 'https://x/main_server',
            'language'      => 'es',
            'widget_config' => '{"language":"fr"}',
        ]);
        $this->assertStringContainsString('"language":"fr"', $output);
    }

    public function testEmptyLanguageProducesNoConfigScript(): void
    {
        $output = CrumbRenderer::render([
            'server'   => 'https://x/main_server',
            'language' => '',
        ]);
        $this->assertStringNotContainsString('CrumbWidgetConfig', $output);
    }

    public function testLanguageOverrideTrimsAndLowercases(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server'],
            ['language' => '  FR  ']
        );
        $this->assertStringContainsString('"language":"fr"', $output);
    }

    public function testUpdateUrlSettingEmitsDataAttribute(): void
    {
        $output = CrumbRenderer::render([
            'server'     => 'https://x/main_server',
            'update_url' => 'https://example.org/form/?meeting_id={meeting_id}',
        ]);
        $this->assertStringContainsString(
            'data-update-url="https://example.org/form/?meeting_id={meeting_id}"',
            $output
        );
    }

    public function testUpdateUrlMailtoIsEmitted(): void
    {
        $output = CrumbRenderer::render([
            'server'     => 'https://x/main_server',
            'update_url' => 'mailto:web@example.org?subject=Update%20{meeting_name}',
        ]);
        $this->assertStringContainsString(
            'data-update-url="mailto:web@example.org?subject=Update%20{meeting_name}"',
            $output
        );
    }

    public function testEmptyUpdateUrlOmitsAttribute(): void
    {
        $output = CrumbRenderer::render(['server' => 'https://x/main_server']);
        $this->assertStringNotContainsString('data-update-url', $output);
    }

    public function testUpdateUrlOverrideBeatsSavedSetting(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'update_url' => 'https://saved/?meeting_id={meeting_id}'],
            ['update_url' => 'https://override/?meeting_id={meeting_id}']
        );
        $this->assertStringContainsString('data-update-url="https://override/?meeting_id={meeting_id}"', $output);
        $this->assertStringNotContainsString('saved', $output);
    }

    public function testEmptyUpdateUrlOverrideOmitsAttribute(): void
    {
        $output = CrumbRenderer::render(
            ['server' => 'https://x/main_server', 'update_url' => 'https://saved/?meeting_id={meeting_id}'],
            ['update_url' => '']
        );
        $this->assertStringNotContainsString('data-update-url', $output);
    }

    public function testUpdateUrlIsHtmlEscaped(): void
    {
        $output = CrumbRenderer::render([
            'server'     => 'https://x/main_server',
            'update_url' => '"><script>alert(1)</script>',
        ]);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $output);
    }
}
