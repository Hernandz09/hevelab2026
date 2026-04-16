<?php

declare(strict_types=1);

namespace App\Services;

final class HookConfigService
{
    private string $configPath;
    private string $cachePath;

    public function __construct()
    {
        $this->configPath = __DIR__ . '/../../config/hooks.php';
        $this->cachePath = __DIR__ . '/../../storage/cache/hook-flags.json';
    }

    public function getFlags(): array
    {
        $defaults = require $this->configPath;
        if (!is_file($this->cachePath)) {
            return $defaults;
        }

        $decoded = json_decode((string) file_get_contents($this->cachePath), true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        return array_merge($defaults, $decoded);
    }

    public function saveFlags(array $flags): array
    {
        $current = $this->getFlags();
        $filtered = [];

        foreach ($current as $key => $value) {
            $filtered[$key] = (bool) ($flags[$key] ?? $value);
        }

        file_put_contents($this->cachePath, json_encode($filtered, JSON_PRETTY_PRINT));
        return $filtered;
    }
}
