<?php

/**
 * InkManager — Cron de recordatorios WhatsApp (2 h antes del turno)
 *
 * Configurar en crontab (Linux/Mac):
 *   * * * * * php /ruta/a/Tatoo/cli/send_reminders.php >> /tmp/inkmanager_reminders.log 2>&1
 *
 * En Windows (Task Scheduler):
 *   Program : php.exe
 *   Args    : C:\xampp\htdocs\Tatoo\cli\send_reminders.php
 *   Trigger : Every 1 minute
 */

declare(strict_types=1);

// ── Bootstrap ─────────────────────────────────────────────────────────────────
define('ROOT_PATH',   dirname(__DIR__));
define('APP_PATH',    ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Cargar .env
if (file_exists(ROOT_PATH . '/.env')) {
    foreach (file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $_ENV[trim($k)] = trim($v);
    }
}

// Autoloader PSR-4 (mismo del index.php)
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) return;
    $relative = str_replace(['App\\', '\\'], ['', DIRECTORY_SEPARATOR], $class);
    $file = APP_PATH . DIRECTORY_SEPARATOR . $relative . '.php';
    if (file_exists($file)) require_once $file;
});

use App\Models\Turno;
use App\Services\WhatsAppService;

// ── Verificar que WhatsApp está configurado ────────────────────────────────────
$ws = new WhatsAppService();
if (!$ws->isEnabled()) {
    echo '[' . date('Y-m-d H:i:s') . '] WhatsApp no configurado (falta TWILIO_SID/TOKEN). Saliendo.' . PHP_EOL;
    exit(0);
}

// ── Buscar y enviar recordatorios ─────────────────────────────────────────────
$model   = new Turno();
$pending = $model->pendientesRecordatorio();

if (empty($pending)) {
    echo '[' . date('Y-m-d H:i:s') . '] Sin turnos pendientes de recordatorio.' . PHP_EOL;
    exit(0);
}

foreach ($pending as $turno) {
    $sent = $ws->recordatorio(
        (string) $turno['telefono'],
        (string) $turno['cliente_nombre'],
        (string) $turno['fecha_inicio'],
        (int)    $turno['duracion_min']
    );

    $ts = date('Y-m-d H:i:s');
    if ($sent) {
        $model->marcarReminderEnviado((int) $turno['id']);
        echo "[{$ts}] ✓ Recordatorio enviado → {$turno['cliente_nombre']} (turno #{$turno['id']})" . PHP_EOL;
    } else {
        echo "[{$ts}] ✗ Falló → {$turno['cliente_nombre']} (turno #{$turno['id']})" . PHP_EOL;
    }
}
