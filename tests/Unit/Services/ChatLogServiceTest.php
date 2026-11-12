<?php

namespace Tests\Unit\Services;

use App\Services\ChatLogService;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ChatLogServiceTest extends TestCase
{
    private const CODE = 'c52562efe4b8d90de795ddc290d275d5';

    private ChatLogService $service;
    private string $publicDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Never write into the real public/ folder from a test.
        $this->publicDir = sys_get_temp_dir() . '/chatlog-test-' . uniqid();
        File::ensureDirectoryExists($this->publicDir);
        $this->app->usePublicPath($this->publicDir);

        $this->service = new ChatLogService();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicDir);

        parent::tearDown();
    }

    public function test_every_supported_locale_has_unique_chat_settings(): void
    {
        $supported = config('locales.supported');
        $chat = config('locales.chat');

        $this->assertEqualsCanonicalizing($supported, array_keys($chat), 'locales.chat must cover exactly locales.supported');

        foreach (['session', 'folder', 'suffix'] as $field) {
            $values = array_column($chat, $field);
            $this->assertCount(count($values), array_unique($values), "Duplicate chat '{$field}' across locales");
        }
    }

    public static function legacyLocales(): array
    {
        // These must never change: they match chat files already on disk.
        return [
            'vi' => ['vi', 'CoTuong_VI-', 'phongChatLog/' . self::CODE . '-phongchatlog.html'],
            'en' => ['en', 'CoTuong_EN-', 'roomChatLog/' . self::CODE . '-roomchatlog.html'],
            'ja' => ['ja', 'CoTuong_JA-', 'rumuChatLog/' . self::CODE . '-rumuchatlog.html'],
            'ko' => ['ko', 'CoTuong_KO-', 'bangChatLog/' . self::CODE . '-bangchatlog.html'],
            'zh' => ['zh', 'CoTuong_ZH-', 'fangjianChatLog/' . self::CODE . '-fangjianchatlog.html'],
        ];
    }

    #[DataProvider('legacyLocales')]
    public function test_legacy_locales_keep_their_existing_paths(string $locale, string $sessionPrefix, string $relative): void
    {
        $this->assertSame($sessionPrefix . self::CODE, $this->service->sessionName(self::CODE, $locale));
        $this->assertSame($relative, $this->service->relativePath(self::CODE, $locale));
    }

    public static function newLocales(): array
    {
        return [
            'de' => ['de', 'CoTuong_DE-', 'raumChatLog/' . self::CODE . '-raumchatlog.html'],
            'fr' => ['fr', 'CoTuong_FR-', 'salonChatLog/' . self::CODE . '-salonchatlog.html'],
            'es' => ['es', 'CoTuong_ES-', 'salaChatLog/' . self::CODE . '-salachatlog.html'],
            'ru' => ['ru', 'CoTuong_RU-', 'komnataChatLog/' . self::CODE . '-komnatachatlog.html'],
        ];
    }

    #[DataProvider('newLocales')]
    public function test_new_locales_get_their_own_chat_log(string $locale, string $sessionPrefix, string $relative): void
    {
        $this->assertSame($sessionPrefix . self::CODE, $this->service->sessionName(self::CODE, $locale));
        $this->assertSame($relative, $this->service->relativePath(self::CODE, $locale));
    }

    public function test_unknown_locale_falls_back_to_english(): void
    {
        $this->assertSame(
            $this->service->relativePath(self::CODE, 'en'),
            $this->service->relativePath(self::CODE, 'pt')
        );
    }

    public function test_locale_defaults_to_the_application_locale(): void
    {
        app()->setLocale('fr');

        $this->assertSame(
            $this->service->relativePath(self::CODE, 'fr'),
            $this->service->relativePath(self::CODE)
        );
    }

    public function test_room_code_must_be_a_plain_token(): void
    {
        $this->assertSame(self::CODE, $this->service->normalizeRoomCode(self::CODE));

        foreach (['', '../../index', 'a/b', 'a\\b', "x\0y", "abc\"&&sleep(5)&&\"", '<!--', str_repeat('a', 65)] as $bad) {
            $this->assertNull($this->service->normalizeRoomCode($bad), 'Should reject: ' . json_encode($bad));
        }

        $this->assertNull($this->service->normalizeRoomCode(null));
        $this->assertNull($this->service->normalizeRoomCode(['a']));
    }

    #[DataProvider('newLocales')]
    public function test_folder_is_created_on_first_use_for_new_locales(string $locale): void
    {
        $path = $this->service->path(self::CODE, $locale);
        $this->assertFileDoesNotExist($path);

        $this->service->ensureExists(self::CODE, 'Welcome', $locale);

        $this->assertFileExists($path);
        $this->assertStringContainsString('Welcome', file_get_contents($path));
    }

    public function test_ensure_exists_does_not_overwrite_an_existing_log(): void
    {
        $this->service->ensureExists(self::CODE, 'Welcome', 'de');
        $this->service->append(self::CODE, "<div class='msgln'>hallo</div>\n", 'de');
        $this->service->ensureExists(self::CODE, 'Welcome again', 'de');

        $contents = file_get_contents($this->service->path(self::CODE, 'de'));
        $this->assertStringContainsString('hallo', $contents);
        $this->assertStringNotContainsString('Welcome again', $contents);
    }

    public function test_logs_are_kept_separate_per_locale(): void
    {
        $this->service->append(self::CODE, 'deutsch', 'de');
        $this->service->append(self::CODE, 'francais', 'fr');

        $this->assertStringNotContainsString('francais', file_get_contents($this->service->path(self::CODE, 'de')));
        $this->assertStringNotContainsString('deutsch', file_get_contents($this->service->path(self::CODE, 'fr')));
    }
}
