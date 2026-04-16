<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<int, array{pattern:string,handler:callable|array}>> */
    private array $routes = [];

    public function get(string $pattern, callable|array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable|array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, callable|array $handler): void
    {
        $this->routes[strtoupper($method)][] = [
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method();
        $path = $request->path();

        foreach ($this->routes[$method] ?? [] as $route) {
            $params = $this->match($route['pattern'], $path);
            if ($params === null) {
                continue;
            }

            $handler = $route['handler'];

            if (is_array($handler) && count($handler) === 2) {
                $controller = new $handler[0]();
                $action = $handler[1];
                $controller->$action($request, ...array_values($params));
                return;
            }

            $handler($request, ...array_values($params));
            return;
        }

        if (str_starts_with($path, '/api/')) {
            Response::notFound('API route not found');
        }

        http_response_code(404);
        echo 'Pagina no encontrada';
    }

    private function match(string $pattern, string $path): ?array
    {
        $normalizedPattern = rtrim($pattern, '/');
        if ($normalizedPattern === '') {
            $normalizedPattern = '/';
        }

        $regex = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $normalizedPattern);
        $regex = '#^' . ($regex ?? '') . '$#';

        if (preg_match($regex, $path, $matches) !== 1) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (!is_int($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
