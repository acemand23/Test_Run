<?php
declare(strict_types=1);

/** Loads config.php once and exposes values. */
final class Config
{
    private static ?array $data = null;

    public static function load(): void
    {
        if (self::$data !== null) {
            return;
        }
        $path = dirname(__DIR__) . '/config.php';
        if (!is_file($path)) {
            Http::error(500, 'config_missing',
                'config.php not found. Copy config.sample.php to config.php and fill it in.');
        }
        self::$data = require $path;
    }

    /** @return mixed */
    public static function get(string $key, $default = null)
    {
        self::load();
        return self::$data[$key] ?? $default;
    }
}
