<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void { $this->add('GET', $path, $handler); }
    public function post(string $path, array $handler): void { $this->add('POST', $path, $handler); }

    private function add(string $method, string $path, array $handler): void
    {
        $this->routes[] = [$method, rtrim($path, '/') ?: '/', $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim(BASE_URL, '/');
        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base)) ?: '/';
        }
        $path = rtrim($path, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            $pattern = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[0-9]+)', $routePath);
            if ($routeMethod === $method && preg_match('#^' . $pattern . '$#', $path, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) $params[] = $value;
                }
                [$class, $action] = $handler;
                (new $class)->{$action}(...$params);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Page not found';
    }
}
