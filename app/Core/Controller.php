<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    /**
     * Render a view inside a layout.
     *
     * @param string $view   Dot-notation path relative to app/Views/ (e.g. 'clientes.index')
     * @param array  $data   Variables extracted into view scope
     * @param string|null $layout  Layout name inside app/Views/layouts/, null = no layout
     */
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        $viewFile = APP_PATH . '/Views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$viewFile}");
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout !== null) {
            $layoutFile = APP_PATH . '/Views/layouts/' . $layout . '.php';
            if (!file_exists($layoutFile)) {
                throw new \RuntimeException("Layout not found: {$layoutFile}");
            }
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $path, bool $absolute = false): void
    {
        $url = $absolute ? $path : BASE_URL . '/' . ltrim($path, '/');
        header('Location: ' . $url);
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/';
        header('Location: ' . $ref);
        exit;
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    protected function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}
