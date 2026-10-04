<?php

namespace Tests\Unit\Services;

use App\Services\LocalizedUrlService;
use InvalidArgumentException;
use Tests\TestCase;

class LocalizedUrlServiceTest extends TestCase
{
    private LocalizedUrlService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'locales.default' => 'vi',
            'locales.supported' => ['vi', 'en', 'ja', 'ko', 'zh'],
            'locales.paths' => [
                'home' => [
                    'vi' => '/',
                    'en' => '/en',
                    'ja' => '/ja',
                    'ko' => '/ko',
                    'zh' => '/zh',
                ],
                'room.red' => [
                    'vi' => '/phong/{code}/do',
                    'en' => '/en/room/{code}/red',
                    'ja' => '/ja/room/{code}/red',
                    'ko' => '/ko/room/{code}/red',
                    'zh' => '/zh/room/{code}/red',
                ],
                'article' => [
                    'vi' => '/bai-viet/{slug}',
                    'en' => '/en/article/{slug}',
                    'ja' => '/ja/article/{slug}',
                    'ko' => '/ko/article/{slug}',
                    'zh' => '/zh/article/{slug}',
                ],
            ],
        ]);

        $this->service = new LocalizedUrlService();
    }

    public function test_path_resolves_locale_and_route_parameters(): void
    {
        $this->assertSame(
            '/en/room/abc123/red',
            $this->service->path('room.red', ['code' => 'abc123'], 'en')
        );
    }

    public function test_missing_locale_falls_back_to_default_locale_path(): void
    {
        $this->assertSame(
            '/phong/abc123/do',
            $this->service->path('room.red', ['code' => 'abc123'], 'fr')
        );
    }

    public function test_alternate_paths_contains_all_supported_locales(): void
    {
        $paths = $this->service->alternatePaths('room.red', ['code' => 'abc123']);

        $this->assertSame('/phong/abc123/do', $paths['vi']);
        $this->assertSame('/en/room/abc123/red', $paths['en']);
        $this->assertSame('/ja/room/abc123/red', $paths['ja']);
        $this->assertSame('/ko/room/abc123/red', $paths['ko']);
        $this->assertSame('/zh/room/abc123/red', $paths['zh']);
    }

    public function test_alternate_paths_can_use_locale_specific_parameters(): void
    {
        $paths = $this->service->alternatePathsForLocales('article', [
            'vi' => ['slug' => 'xin-chao'],
            'en' => ['slug' => 'hello'],
        ]);

        $this->assertSame('/bai-viet/xin-chao', $paths['vi']);
        $this->assertSame('/en/article/hello', $paths['en']);
        $this->assertSame('/ja/article/xin-chao', $paths['ja']);
        $this->assertSame('/ko/article/xin-chao', $paths['ko']);
        $this->assertSame('/zh/article/xin-chao', $paths['zh']);
    }

    public function test_legacy_room_paths_map_to_expected_locales(): void
    {
        $this->assertSame('en', $this->service->detectLocaleFromPath('/room/ABC'));
        $this->assertSame('ja', $this->service->detectLocaleFromPath('/rumu/ABC'));
        $this->assertSame('ko', $this->service->detectLocaleFromPath('/bang/ABC'));
        $this->assertSame('zh', $this->service->detectLocaleFromPath('/fangjian/ABC'));
        $this->assertSame('vi', $this->service->detectLocaleFromPath('/phong/ABC'));
    }

    public function test_supported_locale_in_path_wins_over_legacy_detection(): void
    {
        $this->assertSame('en', $this->service->detectLocaleFromPath('/en/room/ABC'));
        $this->assertSame('ja', $this->service->detectLocaleFromPath('/ja'));
    }

    public function test_unknown_path_uses_default_locale(): void
    {
        $this->assertSame('vi', $this->service->detectLocaleFromPath('/something/else'));
    }

    public function test_unknown_route_key_throws_a_clear_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->service->path('does.not.exist');
    }
}
