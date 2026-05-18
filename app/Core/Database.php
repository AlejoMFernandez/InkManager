<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $cfg = require ROOT_PATH . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['dbname'],
            $cfg['charset']
        );

        try {
            self::$instance = new PDO($dsn, $cfg['user'], $cfg['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            $msg = ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
                ? $e->getMessage()
                : 'Error de conexión a la base de datos.';
            die('<h2 style="font-family:sans-serif;color:#dc2626">DB Error: ' . htmlspecialchars($msg) . '</h2>');
        }

        return self::$instance;
    }
}
