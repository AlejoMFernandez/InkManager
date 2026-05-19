<?php

declare(strict_types=1);

// ── Static-file pass-through for PHP built-in server (Railway / php -S) ─────
// El built-in server pasa todo a este script; si el path apunta a un archivo
// real (CSS, JS, imágenes), devolvemos false para que el server lo sirva.
if (PHP_SAPI === 'cli-server') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';
    $file    = __DIR__ . $reqPath;
    if ($reqPath !== '/' && is_file($file)) {
        return false;
    }
}

// ── Path constants ────────────────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);

// ── Autoloader (PSR-4 style, no Composer) ────────────────────────────────────
spl_autoload_register(static function (string $class): void {
    // App\Core\Router  → app/Core/Router.php
    // App\Controllers\ → app/Controllers/*.php
    // App\Models\      → app/Models/*.php
    if (!str_starts_with($class, 'App\\')) return;

    $relative = str_replace(['App\\', '\\'], ['', DIRECTORY_SEPARATOR], $class);
    $file = APP_PATH . DIRECTORY_SEPARATOR . $relative . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});

// ── Load .env early (for BASE_URL override + DB env on bootstrap) ─────────────
// Local (XAMPP) usa .env. Railway/prod usa vars del proceso (getenv).
// PHP's built-in server NO popula $_ENV automáticamente con vars del proceso
// (variables_order = "GPCS" por default), así que las traemos a mano de getenv().
if (file_exists(ROOT_PATH . '/.env')) {
    foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}
// Inyectar vars de entorno del proceso (Railway, Docker, etc.) en $_ENV.
// .env (si existe) toma precedencia: solo llenamos lo que falta.
foreach (getenv() as $k => $v) {
    if (!isset($_ENV[$k])) $_ENV[$k] = $v;
}

// ── Session (secure cookies cuando hay HTTPS detrás de proxy) ─────────────────
$isHttps = ($_SERVER['HTTPS'] ?? '') === 'on'
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// ── BASE_URL / PUBLIC_URL: env override > auto-detect ────────────────────────
// XAMPP subdir : doc-root = htdocs/      → BASE_URL = "/Tatoo", PUBLIC_URL = "/Tatoo/public"
// Railway etc. : doc-root = public/      → BASE_URL = "",        PUBLIC_URL = ""
if (isset($_ENV['BASE_URL'])) {
    $base   = rtrim($_ENV['BASE_URL'], '/');
    $public = $_ENV['PUBLIC_URL'] ?? $base; // si no se especifica, doc-root ya es public
} else {
    $docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $rootPath = rtrim(str_replace('\\', '/', ROOT_PATH), '/');
    if ($docRoot && str_starts_with($rootPath, $docRoot)) {
        // doc-root contiene a ROOT_PATH → typical XAMPP
        $base   = rtrim(substr($rootPath, strlen($docRoot)), '/');
        $public = $base . '/public';
    } else {
        // doc-root es public/ (Railway / php -S -t public)
        $base   = '';
        $public = '';
    }
}
define('BASE_URL',  $base);
define('PUBLIC_URL', $public);

// ── Routes ────────────────────────────────────────────────────────────────────
use App\Core\Router;

$router = new Router();

// Auth
$router->get( '/login',  'AuthController@showLogin');
$router->post('/login',  'AuthController@login');
$router->get( '/logout', 'AuthController@logout');

// Dashboard
$router->get('/',          'DashboardController@index', ['auth']);
$router->get('/dashboard', 'DashboardController@index', ['auth']);

// Clientes
$router->get( '/clientes',                  'ClientesController@index',     ['auth']);
$router->get( '/clientes/nuevo',            'ClientesController@nuevo',     ['auth']);
$router->post('/clientes/nuevo',            'ClientesController@guardar',   ['auth']);
$router->get( '/clientes/{id}',             'ClientesController@ficha',     ['auth']);
$router->get( '/clientes/{id}/editar',      'ClientesController@editar',    ['auth']);
$router->post('/clientes/{id}/editar',      'ClientesController@actualizar', ['auth']);
$router->post('/clientes/{id}/borrar',      'ClientesController@borrar',   ['auth']);

// Turnos
$router->get( '/turnos',                    'TurnosController@index',        ['auth']);
$router->get( '/turnos/nuevo',              'TurnosController@nuevo',        ['auth']);
$router->post('/turnos/nuevo',              'TurnosController@guardar',      ['auth']);
$router->get( '/turnos/{id}',               'TurnosController@ver',          ['auth']);
$router->get( '/turnos/{id}/editar',        'TurnosController@editar',       ['auth']);
$router->post('/turnos/{id}/editar',        'TurnosController@actualizar',   ['auth']);
$router->post('/turnos/{id}/borrar',        'TurnosController@borrar',       ['auth']);
$router->post('/turnos/{id}/estado',        'TurnosController@cambiarEstado', ['auth']);

// API Turnos (JSON — FullCalendar + drag & drop)
$router->get( '/api/turnos',                'TurnosController@feed',         ['auth']);
$router->post('/api/turnos/{id}/reagendar', 'TurnosController@reagendar',    ['auth']);
$router->post('/api/turnos/{id}/estado',    'TurnosController@cambiarEstado',['auth']);

// API Tatuajes (JSON — usada por markers.js vía Fetch)
$router->post('/api/tatuajes',                        'TatuajesController@store',   ['auth']);
$router->post('/api/tatuajes/{id}/actualizar',        'TatuajesController@update',  ['auth']);
$router->post('/api/tatuajes/{id}/borrar',            'TatuajesController@destroy', ['auth']);

// ── Dispatch ──────────────────────────────────────────────────────────────────
$router->dispatch();
