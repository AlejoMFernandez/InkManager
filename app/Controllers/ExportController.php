<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Cliente;
use App\Models\Studio;

class ExportController extends Controller
{
    /** GET /export/clientes — CSV download de todos los clientes del estudio */
    public function clientes(array $params = []): void
    {
        Auth::requireAuth();

        $sid    = Auth::studioId();
        $db     = Database::get();
        $extra  = $sid > 0 ? 'WHERE c.studio_id = ?' : '';
        $qp     = $sid > 0 ? [$sid] : [];

        $stmt = $db->prepare(
            "SELECT c.nombre, c.telefono, c.primera_visita,
                    COUNT(DISTINCT t.id)                AS total_tatuajes,
                    COUNT(DISTINCT tu.id)               AS total_turnos,
                    COALESCE(SUM(t.sesiones_hechas), 0) AS sesiones_hechas,
                    c.notas, c.created_at
             FROM clientes c
             LEFT JOIN tatuajes t  ON t.cliente_id = c.id
             LEFT JOIN turnos   tu ON tu.cliente_id = c.id
             {$extra}
             GROUP BY c.id
             ORDER BY c.nombre ASC"
        );
        $stmt->execute($qp);
        $rows = $stmt->fetchAll();

        $this->sendCsvHeaders('clientes_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM para Excel

        fputcsv($out, [
            'Nombre', 'Teléfono', 'Primera visita',
            'Tatuajes', 'Turnos', 'Sesiones hechas', 'Notas', 'Registrado',
        ], ';');

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['nombre'],
                $r['telefono']      ?? '',
                $r['primera_visita'] ? date('d/m/Y', strtotime($r['primera_visita'])) : '',
                (int) $r['total_tatuajes'],
                (int) $r['total_turnos'],
                (int) $r['sesiones_hechas'],
                $r['notas']         ?? '',
                $r['created_at']    ? date('d/m/Y', strtotime($r['created_at'])) : '',
            ], ';');
        }

        fclose($out);
        exit;
    }

    /** GET /export/turnos?desde=YYYY-MM-DD&hasta=YYYY-MM-DD — CSV download de turnos */
    public function turnos(array $params = []): void
    {
        Auth::requireAuth();

        $desde = trim($_GET['desde'] ?? '');
        $hasta = trim($_GET['hasta'] ?? '');

        $sid    = Auth::studioId();
        $db     = Database::get();

        $conditions = [];
        $qp         = [];

        if ($sid > 0) {
            $conditions[] = 'tu.studio_id = ?';
            $qp[]         = $sid;
        }
        if ($desde !== '') {
            $conditions[] = 'DATE(tu.fecha_inicio) >= ?';
            $qp[]         = $desde;
        }
        if ($hasta !== '') {
            $conditions[] = 'DATE(tu.fecha_inicio) <= ?';
            $qp[]         = $hasta;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $db->prepare(
            "SELECT tu.id, c.nombre AS cliente_nombre, c.telefono,
                    tu.fecha_inicio, tu.duracion_min, tu.estado, tu.sena, tu.notas,
                    tu.created_at
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             {$where}
             ORDER BY tu.fecha_inicio DESC"
        );
        $stmt->execute($qp);
        $rows = $stmt->fetchAll();

        $this->sendCsvHeaders('turnos_' . date('Ymd_His') . '.csv');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");

        fputcsv($out, [
            'ID', 'Cliente', 'Teléfono', 'Fecha', 'Duración (min)',
            'Estado', 'Seña (ARS)', 'Notas', 'Registrado',
        ], ';');

        $estadosLabel = [
            'agendado'   => 'Agendado',
            'confirmado' => 'Confirmado',
            'hecho'      => 'Hecho',
            'cancelado'  => 'Cancelado',
        ];

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['cliente_nombre'],
                $r['telefono']   ?? '',
                $r['fecha_inicio'] ? date('d/m/Y H:i', strtotime($r['fecha_inicio'])) : '',
                $r['duracion_min'],
                $estadosLabel[$r['estado']] ?? $r['estado'],
                $r['sena']       !== null ? number_format((float) $r['sena'], 2, ',', '.') : '',
                $r['notas']      ?? '',
                $r['created_at'] ? date('d/m/Y', strtotime($r['created_at'])) : '',
            ], ';');
        }

        fclose($out);
        exit;
    }

    /** GET /clientes/{id}/imprimir — ficha imprimible (HTML standalone) */
    public function fichaCliente(array $params = []): void
    {
        Auth::requireAuth();

        $model   = new Cliente();
        $cliente = $model->conTatuajes((int) ($params['id'] ?? 0));
        if (!$cliente) {
            http_response_code(404);
            echo '404 — Cliente no encontrado';
            return;
        }

        // Historial completo de turnos del cliente
        $sid    = Auth::studioId();
        $db     = Database::get();
        $sidW   = $sid > 0 ? 'AND tu.studio_id = ?' : '';
        $hParams = [$cliente['id']];
        if ($sid > 0) $hParams[] = $sid;

        $stmt = $db->prepare(
            "SELECT tu.fecha_inicio, tu.duracion_min, tu.estado, tu.sena, tu.notas,
                    e.nombre AS estilo_nombre
             FROM turnos tu
             LEFT JOIN tatuajes t ON t.id = tu.tatuaje_id
             LEFT JOIN estilos  e ON e.id = t.estilo_id
             WHERE tu.cliente_id = ? {$sidW}
             ORDER BY tu.fecha_inicio DESC"
        );
        $stmt->execute($hParams);
        $historial = $stmt->fetchAll();

        // Nombre del estudio
        $studio      = (new Studio())->find(Auth::studioId());
        $studioNombre = $studio['nombre'] ?? 'InkManager';

        $this->render('clientes.print', compact('cliente', 'historial', 'studioNombre'), null);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function sendCsvHeaders(string $filename): void
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
