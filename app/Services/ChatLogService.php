<?php

namespace App\Services;

/**
 * Locale-aware access to the per-room chat log files.
 *
 * All locale-specific values (log folder, file suffix, PHP session name) come
 * from config('locales.chat'), so supporting a new language is a config change
 * rather than an edit in every controller and view that touches the chat.
 */
class ChatLogService
{
    /** Locale used when the requested one has no chat settings. */
    public const FALLBACK_LOCALE = 'en';

    /** Room codes are 32 hex chars; this is deliberately a little looser. */
    private const ROOM_CODE_PATTERN = '/^[A-Za-z0-9_-]{1,64}$/';

    /**
     * Resolve chat settings for a locale, falling back to English for
     * locales that have no entry.
     *
     * @return array{session: string, folder: string, suffix: string}
     */
    public function settings(?string $locale = null): array
    {
        $configs = config('locales.chat', []);
        $locale = strtolower((string) ($locale ?: app()->getLocale()));

        return $configs[$locale]
            ?? $configs[self::FALLBACK_LOCALE]
            ?? ['session' => 'EN', 'folder' => 'roomChatLog', 'suffix' => 'roomchatlog'];
    }

    /**
     * Returns the room code if it is safe to use in a file name, otherwise null.
     *
     * Room codes come from the request (ChatController) and end up in a file
     * path, so anything that is not a plain token is rejected.
     */
    public function normalizeRoomCode(mixed $roomCode): ?string
    {
        if (!is_string($roomCode) || preg_match(self::ROOM_CODE_PATTERN, $roomCode) !== 1) {
            return null;
        }

        return $roomCode;
    }

    /** PHP session name that holds the chat nickname for this room + locale. */
    public function sessionName(string $roomCode, ?string $locale = null): string
    {
        return 'CoTuong_' . $this->settings($locale)['session'] . '-' . $roomCode;
    }

    /** Path relative to public/, e.g. "salonChatLog/<code>-salonchatlog.html". */
    public function relativePath(string $roomCode, ?string $locale = null): string
    {
        $settings = $this->settings($locale);

        return "{$settings['folder']}/{$roomCode}-{$settings['suffix']}.html";
    }

    /** Absolute filesystem path of the log file. */
    public function path(string $roomCode, ?string $locale = null): string
    {
        return public_path($this->relativePath($roomCode, $locale));
    }

    /** Public URL the chat widget polls. */
    public function url(string $roomCode, ?string $locale = null): string
    {
        return url($this->relativePath($roomCode, $locale));
    }

    /**
     * Create the log file (and its folder) with a welcome line if it does not
     * exist yet. Folders for newly added locales are created on first use.
     */
    public function ensureExists(string $roomCode, string $welcomeText, ?string $locale = null): void
    {
        $path = $this->path($roomCode, $locale);

        if (is_file($path)) {
            return;
        }

        $directory = dirname($path);
        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return;
        }

        $time = date('H:i');
        $line = "<div class='msgln system-msg'><span class='chat-time'>{$time}</span> "
              . "<span class='welcome-info'>👋 {$welcomeText}</span></div>\n";

        @file_put_contents($path, $line, LOCK_EX);
    }

    /** Append one pre-built HTML line to the log. */
    public function append(string $roomCode, string $html, ?string $locale = null): bool
    {
        $path = $this->path($roomCode, $locale);
        $directory = dirname($path);

        if (!is_dir($directory) && !@mkdir($directory, 0775, true) && !is_dir($directory)) {
            return false;
        }

        return @file_put_contents($path, $html, FILE_APPEND | LOCK_EX) !== false;
    }
}
