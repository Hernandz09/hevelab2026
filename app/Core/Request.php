<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    private string $method;
    private string $path;
    private string $basePath;
    private array $query;
    private array $body;

    public function __construct(string $method, string $path, string $basePath = '', array $query = [], array $body = [])
    {
        $this->method = strtoupper($method);
        $this->path = $path ?: '/';
        $this->basePath = $basePath;
        $this->query = $query;
        $this->body = $body;
    }

    public static function capture(): self
    {
        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
        $basePath = rtrim(str_replace('\\', '/', (string) dirname($scriptName)), '/');
        $basePath = $basePath === '/' ? '' : $basePath;

        $path = $uriPath;
        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        return new self($_SERVER['REQUEST_METHOD'] ?? 'GET', $path, $basePath, $_GET, self::resolveBody());
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return rtrim($this->path, '/') ?: '/';
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    public function allBody(): array
    {
        return $this->body;
    }

    private static function resolveBody(): array
    {
        $contentType = strtolower($_SERVER['CONTENT_TYPE'] ?? '');
        if (str_contains($contentType, 'application/json')) {
            $payload = file_get_contents('php://input') ?: '';
            $decoded = json_decode($payload, true);
            return is_array($decoded) ? $decoded : [];
        }

        return $_POST;
    }
}
