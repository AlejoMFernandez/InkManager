<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(array $params = []): void
    {
        $db  = Database::get();
        $sid = Auth::studioId();

        // ── KPIs ──────────────────────────────────────────────────────────────
        $stmtCli = $db->prepare('SELECT COUNT(*) FROM clientes WHERE studio_id = ?');
        $stmtCli->execute([$sid]);
        $totalClientes = (int) $stmtCli->fetchColumn();

        $stmtTat = $db->prepare('SELECT COUNT(*) FROM tatuajes WHERE studio_id = ?');
        $stmtTat->execute([$sid]);
        $totalTatuajes = (int) $stmtTat->fetchColumn();

        $stmtTur = $db->prepare(
            "SELECT COUNT(*) FROM turnos WHERE studio_id = ? AND estado IN ('agendado','confirmado')"
        );
        $stmtTur->execute([$sid]);
        $totalTurnos = (int) $stmtTur->fetchColumn();

        $stmtIng = $db->prepare(
            "SELECT COALESCE(SUM(t.precio), 0)
             FROM tatuajes t
             WHERE t.studio_id = ?
               AND MONTH(t.fecha) = MONTH(CURDATE())
               AND YEAR(t.fecha)  = YEAR(CURDATE())"
        );
        $stmtIng->execute([$sid]);
        $ingMes = (float) $stmtIng->fetchColumn();

        $stmtProx = $db->prepare(
            "SELECT tu.*, c.nombre AS cliente_nombre
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.studio_id = ?
               AND tu.fecha_inicio >= NOW()
               AND tu.estado IN ('agendado','confirmado')
             ORDER BY tu.fecha_inicio ASC
             LIMIT 5"
        );
        $stmtProx->execute([$sid]);
        $proximosTurnos = $stmtProx->fetchAll();

        // ── Chart data ────────────────────────────────────────────────────────

        $last6 = [];
        for ($i = 5; $i >= 0; $i--) {
            $last6[date('Y-m', strtotime("-{$i} months"))] = 0;
        }

        // Ingresos por mes
        $stmtIngM = $db->prepare(
            "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    COALESCE(SUM(precio), 0)    AS total
             FROM tatuajes
             WHERE studio_id = ?
               AND fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
               AND fecha IS NOT NULL
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmtIngM->execute([$sid]);

        $ingMap = $last6;
        foreach ($stmtIngM->fetchAll() as $r) {
            if (isset($ingMap[$r['mes']])) $ingMap[$r['mes']] = (float) $r['total'];
        }

        // Clientes nuevos por mes
        $stmtCliM = $db->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes,
                    COUNT(*)                          AS total
             FROM clientes
             WHERE studio_id = ?
               AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             GROUP BY mes
             ORDER BY mes ASC"
        );
        $stmtCliM->execute([$sid]);

        $cliMap = $last6;
        foreach ($stmtCliM->fetchAll() as $r) {
            if (isset($cliMap[$r['mes']])) $cliMap[$r['mes']] = (int) $r['total'];
        }

        // Labels de meses (abreviados — lang-aware)
        $nombMes = [
            '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic',
        ];
        $chartMesLabels = array_values(
            array_map(fn($m) => $nombMes[substr($m, 5, 2)], array_keys($ingMap))
        );
        $chartIngData = array_values($ingMap);
        $chartCliData = array_values($cliMap);

        // Tatuajes por estilo
        $stmtEst = $db->prepare(
            "SELECT COALESCE(e.nombre, 'Sin estilo') AS estilo,
                    COUNT(t.id)                       AS total
             FROM tatuajes t
             LEFT JOIN estilos e ON e.id = t.estilo_id
             WHERE t.studio_id = ?
             GROUP BY t.estilo_id, e.nombre
             ORDER BY total DESC
             LIMIT 7"
        );
        $stmtEst->execute([$sid]);
        $tatuajesPorEstilo = $stmtEst->fetchAll();

        // Turnos por estado (últimos 30 días)
        $stmtEstTur = $db->prepare(
            "SELECT estado, COUNT(*) AS total
             FROM turnos
             WHERE studio_id = ?
               AND fecha_inicio >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY estado"
        );
        $stmtEstTur->execute([$sid]);

        $estadoMap = ['hecho' => 0, 'confirmado' => 0, 'agendado' => 0, 'cancelado' => 0];
        foreach ($stmtEstTur->fetchAll() as $r) {
            if (isset($estadoMap[$r['estado']])) {
                $estadoMap[$r['estado']] = (int) $r['total'];
            }
        }

        $this->render('dashboard', compact(
            'totalClientes',
            'totalTatuajes',
            'totalTurnos',
            'ingMes',
            'proximosTurnos',
            'chartMesLabels',
            'chartIngData',
            'chartCliData',
            'tatuajesPorEstilo',
            'estadoMap'
        ));
    }
}
