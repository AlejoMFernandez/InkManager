<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, string $handler, array $middleware): void
    {
        $this->routes[] = compact('method', 'path', 'handler', 'middleware');
    }

    public function dispatch(): void
    {
        $uri    = strtok($_SERVER['REQUEST_URI'], '?');
        $uri    = rawurldecode($uri);
        $method = $_SERVER['REQUEST_METHOD'];

        // Strip the app base path so routes are written without it
        $base = BASE_URL;
        if ($base !== '' && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }
        $uri = '/' . ltrim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $pattern = $this->toRegex($route['path']);
            if (!preg_match($pattern, $uri, $matches)) continue;

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            foreach ($route['middleware'] as $mw) {
                $this->runMiddleware($mw);
            }

            $this->call($route['handler'], $params);
            return;
        }

        $this->notFound();
    }

    private function toRegex(string $path): string
    {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#u';
    }

    private function runMiddleware(string $mw): void
    {
        match ($mw) {
            'auth'  => Auth::requireAuth(),
            'owner' => Auth::requireOwner(),
            'admin' => Auth::requireAdmin(),
            default => null,
        };
    }

    private function call(string $handler, array $params): void
    {
        [$class, $method] = explode('@', $handler, 2);
        $fqn = 'App\\Controllers\\' . $class;

        if (!class_exists($fqn)) {
            $this->notFound();
            return;
        }

        $controller = new $fqn();
        $controller->$method($params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo <<<HTML
        <!doctype html><html lang="es"><head><meta charset="utf-8">
        <title>404</title>
        <style>body{background:#111827;color:#f9fafb;font-family:sans-serif;
        display:flex;align-items:center;justify-content:center;height:100vh;margin:0}
        h1{color:#dc2626;font-size:4rem;margin:0}p{color:#9ca3af}</style></head>
        <body><div style="text-align:center"><h1>404</h1><p>Página no encontrada.</p>
        <a href="javascript:history.back()" style="color:#dc2626">← Volver</a></div></body></html>
        HTML;
        exit;
    }
}
