<?php

declare(strict_types=1);

namespace App\Services;

final class SystemConfigService
{
    private string $path;
    private static ?array $cached = null;
    private static ?int $cachedMtime = null;

    public function __construct()
    {
        $this->path = __DIR__ . '/../../config/system_config.json';
    }

    public function get(): array
    {
        $defaults = [
            'face_threshold' => 0.40,
            'otp_expiracion_min' => 5,
            'max_intentos_login' => 5,
            'updated_at' => null,
            'updated_by' => null,
        ];

        if (!is_file($this->path)) {
            return $defaults;
        }

        $mtime = @filemtime($this->path);
        if (is_int($mtime) && self::$cached !== null && self::$cachedMtime === $mtime) {
            return self::$cached;
        }

        $decoded = json_decode((string) file_get_contents($this->path), true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        $merged = array_merge($defaults, $decoded);
        if (is_int($mtime)) {
            self::$cached = $merged;
            self::$cachedMtime = $mtime;
        }
        return $merged;
    }
}
