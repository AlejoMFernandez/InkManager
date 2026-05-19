<?php

declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!file_exists($path)) return;

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
    }
}

loadEnv(ROOT_PATH . '/.env');

// Inyectar vars del proceso (Railway no usa .env)
foreach (getenv() as $k => $v) {
    if (!isset($_ENV[$k])) $_ENV[$k] = $v;
}

// ── Railway-style MYSQL_URL parsing ──────────────────────────────
// Railway / Heroku publican MYSQL_URL = mysql://user:pass@host:port/dbname
if (!empty($_ENV['MYSQL_URL'])) {
    $u = parse_url($_ENV['MYSQL_URL']);
    return [
        'host'     => $u['host']             ?? 'localhost',
        'port'     => (string)($u['port']    ?? '3306'),
        'dbname'   => ltrim($u['path'] ?? '', '/') ?: 'railway',
        'user'     => $u['user']             ?? 'root',
        'password' => $u['pass']             ?? '',
        'charset'  => 'utf8mb4',
    ];
}

return [
    // Railway vars > genéricas > defaults
    'host'     => $_ENV['MYSQLHOST']     ?? $_ENV['DB_HOST']  ?? 'localhost',
    'port'     => $_ENV['MYSQLPORT']     ?? $_ENV['DB_PORT']  ?? '3306',
    'dbname'   => $_ENV['MYSQL_DATABASE']?? $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME']  ?? 'tattoo_studio',
    'user'     => $_ENV['MYSQLUSER']     ?? $_ENV['DB_USER']  ?? 'root',
    'password' => $_ENV['MYSQL_PASSWORD']?? $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASS']  ?? '',
    'charset'  => 'utf8mb4',
];
