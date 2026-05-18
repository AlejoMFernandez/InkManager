<?php
/**
 * InkManager — Script de instalación / setup inicial
 * Local (XAMPP): http://localhost/Tatoo/setup.php
 * Railway/prod : https://tu-app.up.railway.app/setup.php?token=SETUP_TOKEN
 *
 * En producción se requiere ?token= que matchee SETUP_TOKEN env.
 * Idempotente: se puede correr múltiples veces sin romper nada.
 */
declare(strict_types=1);

define('ROOT_PATH', __DIR__);

function loadEnv(string $path): void {
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}
loadEnv(__DIR__ . '/.env');

// ── Token-gate en producción ────────────────────────────────────
$isProd = ($_ENV['APP_ENV'] ?? 'local') !== 'local';
if ($isProd) {
    $expected = $_ENV['SETUP_TOKEN'] ?? '';
    $given    = $_GET['token']      ?? '';
    if ($expected === '' || !hash_equals($expected, $given)) {
        http_response_code(403);
        die('<h2 style="font-family:sans-serif;color:#dc2626;padding:2rem">403 · Setup bloqueado. Pasá ?token=SETUP_TOKEN.</h2>');
    }
}

// ── Cargar config (soporta Railway MYSQL_URL y vars individuales) ─
if (!empty($_ENV['MYSQL_URL'])) {
    $u = parse_url($_ENV['MYSQL_URL']);
    $cfg = [
        'host'     => $u['host'] ?? 'localhost',
        'port'     => (string)($u['port'] ?? '3306'),
        'user'     => $u['user'] ?? 'root',
        'password' => $u['pass'] ?? '',
        'dbname'   => ltrim($u['path'] ?? '', '/') ?: 'railway',
    ];
} else {
    $cfg = [
        'host'     => $_ENV['MYSQLHOST']      ?? $_ENV['DB_HOST'] ?? 'localhost',
        'port'     => $_ENV['MYSQLPORT']      ?? $_ENV['DB_PORT'] ?? '3306',
        'user'     => $_ENV['MYSQLUSER']      ?? $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['MYSQL_PASSWORD'] ?? $_ENV['MYSQLPASSWORD'] ?? $_ENV['DB_PASS'] ?? '',
        'dbname'   => $_ENV['MYSQL_DATABASE'] ?? $_ENV['MYSQLDATABASE'] ?? $_ENV['DB_NAME'] ?? 'tattoo_studio',
    ];
}

$errors   = [];
$success  = [];
$warnings = [];

// ── 1. Conectar sin DB para crearla (skip si ya existe / no tenemos privilegios) ─
// En Railway la DB ya viene creada y el user no puede hacer CREATE DATABASE.
try {
    $pdoNoDB = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4",
        $cfg['user'], $cfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    try {
        $pdoNoDB->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['dbname']}`
                        CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $success[] = "Base de datos `{$cfg['dbname']}` OK";
    } catch (PDOException $e) {
        $warnings[] = "No se pudo crear DB (probablemente ya existe en Railway): usando `{$cfg['dbname']}`";
    }
} catch (PDOException $e) {
    $errors[] = 'No se pudo conectar a MySQL: ' . $e->getMessage();
    goto render;
}

// ── 2. Conectar con la DB y correr schema ─────────────────────
try {
    $pdo = new PDO(
        "mysql:host={$cfg['host']};port={$cfg['port']};dbname={$cfg['dbname']};charset=utf8mb4",
        $cfg['user'], $cfg['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    // Quitar el CREATE DATABASE y USE para no pisar la conexión actual
    $schema = preg_replace('/CREATE DATABASE.*?;/is', '', $schema);
    $schema = preg_replace('/USE\s+`?\w+`?\s*;/i', '', $schema);

    foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
        $pdo->exec($stmt);
    }
    $success[] = 'Tablas creadas/verificadas OK';
} catch (PDOException $e) {
    $errors[] = 'Error al crear tablas: ' . $e->getMessage();
    goto render;
}

// ── 3. Seed de estilos ────────────────────────────────────────
$estilos = ['Blackwork','Realismo','Fine Line','Traditional','Neo Traditional',
            'Watercolor','Geometric','Japanese','Tribal','Lettering'];
$ins = $pdo->prepare("INSERT IGNORE INTO estilos (nombre) VALUES (?)");
foreach ($estilos as $e) $ins->execute([$e]);
$success[] = 'Estilos insertados OK';

// ── 4. Crear usuario admin ────────────────────────────────────
$adminEmail    = 'admin@inkmanager.com';
$adminPassword = 'admin123';
$adminNombre   = 'Admin';

$existing = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
$existing->execute([$adminEmail]);

if ($existing->fetch()) {
    $warnings[] = "Usuario <strong>{$adminEmail}</strong> ya existe — no se sobreescribió.";
} else {
    $hash = password_hash($adminPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare('INSERT INTO usuarios (nombre, email, password_hash) VALUES (?,?,?)')
        ->execute([$adminNombre, $adminEmail, $hash]);
    $success[] = "Usuario admin creado: <strong>{$adminEmail}</strong> / <strong>{$adminPassword}</strong>";
}

render:
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InkManager Setup</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex items-center justify-center p-6">
<div class="w-full max-w-lg">
    <div class="flex items-center gap-3 mb-8">
        <div class="w-12 h-12 bg-red-600 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0
                         008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994
                         15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0
                         00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold text-white">InkManager Setup</h1>
            <p class="text-gray-500 text-sm">Instalación inicial</p>
        </div>
    </div>

    <?php foreach ($errors as $msg): ?>
    <div class="flex gap-3 p-4 mb-3 rounded-lg bg-red-500/10 border border-red-500/30 text-red-400 text-sm">
        <span class="font-bold">✗</span><span><?= $msg ?></span>
    </div>
    <?php endforeach; ?>

    <?php foreach ($warnings as $msg): ?>
    <div class="flex gap-3 p-4 mb-3 rounded-lg bg-yellow-500/10 border border-yellow-500/30 text-yellow-400 text-sm">
        <span class="font-bold">⚠</span><span><?= $msg ?></span>
    </div>
    <?php endforeach; ?>

    <?php foreach ($success as $msg): ?>
    <div class="flex gap-3 p-4 mb-3 rounded-lg bg-green-500/10 border border-green-500/30 text-green-400 text-sm">
        <span class="font-bold">✓</span><span><?= $msg ?></span>
    </div>
    <?php endforeach; ?>

    <?php if (empty($errors)): ?>
    <div class="mt-6 p-5 bg-gray-900 border border-gray-800 rounded-xl text-sm">
        <p class="text-gray-300 font-semibold mb-3">Instalación completada</p>
        <p class="text-gray-500 mb-1">Login: <code class="text-gray-300">admin@inkmanager.com</code></p>
        <p class="text-gray-500 mb-4">Contraseña: <code class="text-gray-300">admin123</code></p>
        <p class="text-yellow-400 text-xs mb-4">
            ⚠ Cambiá la contraseña antes de poner en producción.<br>
            ⚠ <strong>Borrá o renombrá este archivo (setup.php) ahora.</strong>
        </p>
        <?php $loginUrl = ($_ENV['BASE_URL'] ?? '') . '/login'; ?>
        <a href="<?= htmlspecialchars($loginUrl ?: '/login') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700
                  text-white rounded-lg text-sm font-medium transition-colors">
            Ir al login →
        </a>
    </div>
    <?php else: ?>
    <p class="text-gray-600 text-sm mt-4">
        Revisá la configuración en <code>.env</code> y asegurate de que XAMPP esté corriendo.
    </p>
    <?php endif; ?>
</div>
</body>
</html>
