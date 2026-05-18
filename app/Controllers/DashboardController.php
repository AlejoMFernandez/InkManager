<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(array $params = []): void
    {
        $db = Database::get();

        $totalClientes = (int) $db->query('SELECT COUNT(*) FROM clientes')->fetchColumn();
        $totalTatuajes = (int) $db->query('SELECT COUNT(*) FROM tatuajes')->fetchColumn();
        $totalTurnos   = (int) $db->query(
            "SELECT COUNT(*) FROM turnos WHERE estado IN ('agendado','confirmado')"
        )->fetchColumn();

        $ingMes = (float) $db->query(
            "SELECT COALESCE(SUM(t.precio), 0)
             FROM tatuajes t
             WHERE MONTH(t.fecha) = MONTH(CURDATE())
               AND YEAR(t.fecha)  = YEAR(CURDATE())"
        )->fetchColumn();

        $proximosTurnos = $db->query(
            "SELECT tu.*, c.nombre AS cliente_nombre
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.fecha_inicio >= NOW()
               AND tu.estado IN ('agendado','confirmado')
             ORDER BY tu.fecha_inicio ASC
             LIMIT 5"
        )->fetchAll();

        $this->render('dashboard', compact(
            'totalClientes',
            'totalTatuajes',
            'totalTurnos',
            'ingMes',
            'proximosTurnos'
        ));
    }
}
