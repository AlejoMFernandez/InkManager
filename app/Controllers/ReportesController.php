<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class ReportesController extends Controller
{
    public function index(array $params = []): void
    {
        $db  = Database::get();
        $sid = Auth::studioId();

        // ── Period selection ──────────────────────────────────────────────────
        $periodo = $_GET['periodo'] ?? '3m';
        [$desde, $hasta] = match ($periodo) {
            '30d'  => [date('Y-m-d', strtotime('-30 days')), date('Y-m-d')],
            '6m'   => [date('Y-m-d', strtotime('-6 months')), date('Y-m-d')],
            '12m'  => [date('Y-m-d', strtotime('-12 months')), date('Y-m-d')],
            'ytd'  => [date('Y-01-01'), date('Y-m-d')],
            default => [date('Y-m-d', strtotime('-3 months')), date('Y-m-d')], // '3m'
        };

        // ── Month-range scaffold (all months between $desde and $hasta → 0) ───
        $monthRange = [];
        $cur = (new \DateTime($desde))->modify('first day of this month');
        $end = (new \DateTime($hasta))->modify('first day of this month');
        while ($cur <= $end) {
            $monthRange[$cur->format('Y-m')] = 0;
            $cur->modify('+1 month');
        }
        $mesNames = [
            '01' => 'Ene', '02' => 'Feb', '03' => 'Mar', '04' => 'Abr',
            '05' => 'May', '06' => 'Jun', '07' => 'Jul', '08' => 'Ago',
            '09' => 'Sep', '10' => 'Oct', '11' => 'Nov', '12' => 'Dic',
        ];

        // ── KPI: Ingresos del período (tatuajes.precio) ───────────────────────
        $s = $db->prepare(
            "SELECT COALESCE(SUM(precio), 0) FROM tatuajes
             WHERE studio_id = ? AND fecha BETWEEN ? AND ? AND fecha IS NOT NULL"
        );
        $s->execute([$sid, $desde, $hasta]);
        $kpiIngresos = (float) $s->fetchColumn();

        // ── KPI: Señas cobradas (turnos.sena) ────────────────────────────────
        $s = $db->prepare(
            "SELECT COALESCE(SUM(sena), 0) FROM turnos
             WHERE studio_id = ? AND DATE(fecha_inicio) BETWEEN ? AND ?"
        );
        $s->execute([$sid, $desde, $hasta]);
        $kpiSenas = (float) $s->fetchColumn();

        // ── KPI: Nuevos clientes ──────────────────────────────────────────────
        $s = $db->prepare(
            "SELECT COUNT(*) FROM clientes
             WHERE studio_id = ? AND DATE(created_at) BETWEEN ? AND ?"
        );
        $s->execute([$sid, $desde, $hasta]);
        $kpiNuevosClientes = (int) $s->fetchColumn();

        // ── KPI: Tasa de completados ──────────────────────────────────────────
        $s = $db->prepare(
            "SELECT
               SUM(estado = 'hecho')       AS hechos,
               SUM(estado != 'cancelado')  AS activos
             FROM turnos
             WHERE studio_id = ? AND DATE(fecha_inicio) BETWEEN ? AND ?"
        );
        $s->execute([$sid, $desde, $hasta]);
        $row = $s->fetch();
        $kpiTurnosHechos    = (int) ($row['hechos'] ?? 0);
        $kpiTasaCompletados = ($row['activos'] > 0)
            ? (int) round($row['hechos'] / $row['activos'] * 100)
            : 0;

        // ── Revenue by month (line chart) ─────────────────────────────────────
        $s = $db->prepare(
            "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, COALESCE(SUM(precio), 0) AS total
             FROM tatuajes
             WHERE studio_id = ? AND fecha BETWEEN ? AND ? AND fecha IS NOT NULL
             GROUP BY mes ORDER BY mes ASC"
        );
        $s->execute([$sid, $desde, $hasta]);
        $ingMap = $monthRange;
        foreach ($s->fetchAll() as $r) {
            if (isset($ingMap[$r['mes']])) $ingMap[$r['mes']] = (float) $r['total'];
        }

        // ── New clients by month (bar chart) ──────────────────────────────────
        $s = $db->prepare(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS mes, COUNT(*) AS total
             FROM clientes
             WHERE studio_id = ? AND DATE(created_at) BETWEEN ? AND ?
             GROUP BY mes ORDER BY mes ASC"
        );
        $s->execute([$sid, $desde, $hasta]);
        $cliMap = $monthRange;
        foreach ($s->fetchAll() as $r) {
            if (isset($cliMap[$r['mes']])) $cliMap[$r['mes']] = (int) $r['total'];
        }

        $chartLabels  = array_values(array_map(
            fn($m) => $mesNames[substr($m, 5, 2)] . " '" . substr($m, 2, 2),
            array_keys($ingMap)
        ));
        $chartIngData = array_values($ingMap);
        $chartCliData = array_values($cliMap);

        // ── Appointments by status (donut) ────────────────────────────────────
        $s = $db->prepare(
            "SELECT estado, COUNT(*) AS total FROM turnos
             WHERE studio_id = ? AND DATE(fecha_inicio) BETWEEN ? AND ?
             GROUP BY estado"
        );
        $s->execute([$sid, $desde, $hasta]);
        $estadoMap = ['agendado' => 0, 'confirmado' => 0, 'hecho' => 0, 'cancelado' => 0];
        foreach ($s->fetchAll() as $r) {
            if (isset($estadoMap[$r['estado']])) $estadoMap[$r['estado']] = (int) $r['total'];
        }

        // ── Revenue by tattoo style (horizontal bar) ──────────────────────────
        $s = $db->prepare(
            "SELECT COALESCE(e.nombre, 'Sin estilo') AS estilo,
                    COALESCE(SUM(t.precio), 0) AS total
             FROM tatuajes t
             LEFT JOIN estilos e ON e.id = t.estilo_id
             WHERE t.studio_id = ? AND t.fecha BETWEEN ? AND ?
               AND t.precio IS NOT NULL AND t.fecha IS NOT NULL
             GROUP BY t.estilo_id, e.nombre
             ORDER BY total DESC LIMIT 8"
        );
        $s->execute([$sid, $desde, $hasta]);
        $estilosData = $s->fetchAll();

        // ── Busiest day of week (DAYOFWEEK: 1=Sun,2=Mon…7=Sat) ───────────────
        $s = $db->prepare(
            "SELECT DAYOFWEEK(fecha_inicio) AS dia, COUNT(*) AS total
             FROM turnos
             WHERE studio_id = ? AND DATE(fecha_inicio) BETWEEN ? AND ?
               AND estado != 'cancelado'
             GROUP BY dia"
        );
        $s->execute([$sid, $desde, $hasta]);
        $diasMap = [2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 7 => 0, 1 => 0]; // Lun→Dom
        foreach ($s->fetchAll() as $r) {
            $diasMap[(int) $r['dia']] = (int) $r['total'];
        }
        $diasLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $diasData   = array_values($diasMap);

        // ── Top 10 clients in period ──────────────────────────────────────────
        $s = $db->prepare(
            "SELECT c.id, c.nombre, c.instagram,
                    COUNT(DISTINCT tu.id)                    AS num_turnos,
                    SUM(tu.estado = 'hecho')                 AS turnos_hechos,
                    COALESCE(SUM(t2.precio), 0)              AS total_invertido
             FROM clientes c
             JOIN  turnos   tu ON tu.cliente_id  = c.id
                              AND DATE(tu.fecha_inicio) BETWEEN ? AND ?
                              AND tu.studio_id   = ?
             LEFT JOIN tatuajes t2 ON t2.cliente_id = c.id
                              AND t2.fecha BETWEEN ? AND ?
                              AND t2.precio IS NOT NULL
                              AND t2.studio_id = ?
             WHERE c.studio_id = ?
             GROUP BY c.id
             ORDER BY turnos_hechos DESC, total_invertido DESC
             LIMIT 10"
        );
        $s->execute([$desde, $hasta, $sid, $desde, $hasta, $sid, $sid]);
        $topClientes = $s->fetchAll();

        // ── Caja: KPIs totales del período ───────────────────────────────────────
        $s = $db->prepare(
            "SELECT
               COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END), 0) AS ingresos,
               COALESCE(SUM(CASE WHEN tipo='egreso'  THEN monto ELSE 0 END), 0) AS egresos
             FROM pagos
             WHERE studio_id = ? AND fecha BETWEEN ? AND ?"
        );
        $s->execute([$sid, $desde, $hasta]);
        $row = $s->fetch();
        $cajaIngresos = (float)($row['ingresos'] ?? 0);
        $cajaEgresos  = (float)($row['egresos']  ?? 0);
        $cajaBalance  = $cajaIngresos - $cajaEgresos;

        // ── Caja: flujo por mes (grouped bar) ────────────────────────────────────
        $s = $db->prepare(
            "SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes,
                    COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END), 0) AS ingresos,
                    COALESCE(SUM(CASE WHEN tipo='egreso'  THEN monto ELSE 0 END), 0) AS egresos
             FROM pagos
             WHERE studio_id = ? AND fecha BETWEEN ? AND ?
             GROUP BY mes ORDER BY mes ASC"
        );
        $s->execute([$sid, $desde, $hasta]);
        $cajaIngMap = $monthRange;
        $cajaEgrMap = $monthRange;
        foreach ($s->fetchAll() as $r) {
            if (array_key_exists($r['mes'], $cajaIngMap)) {
                $cajaIngMap[$r['mes']] = (float) $r['ingresos'];
                $cajaEgrMap[$r['mes']] = (float) $r['egresos'];
            }
        }
        $cajaIngData = array_values($cajaIngMap);
        $cajaEgrData = array_values($cajaEgrMap);

        // ── Caja: totales por método de pago ─────────────────────────────────────
        $s = $db->prepare(
            "SELECT metodo,
                    COALESCE(SUM(CASE WHEN tipo='ingreso' THEN monto ELSE 0 END), 0) AS ingresos,
                    COALESCE(SUM(CASE WHEN tipo='egreso'  THEN monto ELSE 0 END), 0) AS egresos
             FROM pagos
             WHERE studio_id = ? AND fecha BETWEEN ? AND ?
             GROUP BY metodo
             ORDER BY ingresos DESC"
        );
        $s->execute([$sid, $desde, $hasta]);
        $cajaMetodos = $s->fetchAll();

        $this->render('reportes.index', compact(
            'periodo', 'desde', 'hasta',
            'kpiIngresos', 'kpiSenas', 'kpiNuevosClientes',
            'kpiTasaCompletados', 'kpiTurnosHechos',
            'chartLabels', 'chartIngData', 'chartCliData',
            'estadoMap', 'estilosData',
            'diasLabels', 'diasData',
            'topClientes',
            'cajaIngresos', 'cajaEgresos', 'cajaBalance',
            'cajaIngData', 'cajaEgrData', 'cajaMetodos'
        ));
    }
}
