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
}
