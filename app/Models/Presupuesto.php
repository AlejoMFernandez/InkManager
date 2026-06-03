<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Presupuesto extends Model
{
    protected string $table  = 'presupuestos';
    protected bool   $scoped = true;

    /**
     * Todos los presupuestos del estudio, con nombre de cliente.
     *
     * @param string|null $estado  Filtra por estado ('borrador'|'enviado'|'aceptado'|'rechazado')
     */
    public function todos(?string $estado = null): array
    {
        $sid    = $this->sid();
        $where  = $sid > 0 ? 'WHERE p.studio_id = ?' : 'WHERE 1=1';
        $params = $sid > 0 ? [$sid] : [];

        if ($estado !== null) {
            $where   .= ' AND p.estado = ?';
            $params[] = $estado;
        }

        return $this->query(
            "SELECT p.*, c.nombre AS cliente_nombre, c.instagram AS cliente_instagram
             FROM presupuestos p
             JOIN clientes c ON c.id = p.cliente_id
             {$where}
             ORDER BY p.fecha DESC, p.id DESC",
            $params
        );
    }

    /**
     * Presupuestos de un cliente específico.
     */
    public function porCliente(int $clienteId): array
    {
        $sid    = $this->sid();
        $extra  = $sid > 0 ? 'AND p.studio_id = ?' : '';
        $params = [$clienteId];
        if ($sid > 0) $params[] = $sid;

        return $this->query(
            "SELECT p.*, c.nombre AS cliente_nombre
             FROM presupuestos p
             JOIN clientes c ON c.id = p.cliente_id
             WHERE p.cliente_id = ? {$extra}
             ORDER BY p.fecha DESC, p.id DESC",
            $params
        );
    }

    /**
     * Presupuesto con datos del cliente (para show / print).
     */
    public function conCliente(int $id): ?array
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND p.studio_id = ?' : '';
        $params    = [$id];
        if ($sid > 0) $params[] = $sid;

        return $this->queryOne(
            "SELECT p.*,
                    c.nombre    AS cliente_nombre,
                    c.instagram AS cliente_instagram,
                    c.telefono  AS cliente_telefono
             FROM presupuestos p
             JOIN clientes c ON c.id = p.cliente_id
             WHERE p.id = ? {$andStudio}",
            $params
        );
    }

    public function crear(array $d): int
    {
        $sid = $this->sid();
        $this->execute(
            'INSERT INTO presupuestos
             (studio_id, cliente_id, numero, titulo, descripcion, monto, estado, validez_dias, fecha, notas)
             VALUES (?,?,?,?,?,?,?,?,?,?)',
            [
                $sid ?: 1,
                (int) $d['cliente_id'],
                $this->generarNumero($sid ?: 1),
                trim($d['titulo']),
                !empty($d['descripcion']) ? trim($d['descripcion']) : null,
                !empty($d['monto'])       ? (float) $d['monto']     : null,
                $d['estado']              ?? 'borrador',
                (int) ($d['validez_dias'] ?? 30),
                $d['fecha']               ?? date('Y-m-d'),
                !empty($d['notas'])       ? trim($d['notas'])       : null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [
            (int) $d['cliente_id'],
            trim($d['titulo']),
            !empty($d['descripcion']) ? trim($d['descripcion']) : null,
            !empty($d['monto'])       ? (float) $d['monto']     : null,
            $d['estado']              ?? 'borrador',
            (int) ($d['validez_dias'] ?? 30),
            $d['fecha']               ?? date('Y-m-d'),
            !empty($d['notas'])       ? trim($d['notas'])       : null,
            $id,
        ];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE presupuestos
             SET cliente_id=?, titulo=?, descripcion=?, monto=?,
                 estado=?, validez_dias=?, fecha=?, notas=?
             WHERE id=? {$andStudio}",
            $params
        );
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $validos = ['borrador', 'enviado', 'aceptado', 'rechazado'];
        if (!in_array($estado, $validos, true)) return false;

        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [$estado, $id];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE presupuestos SET estado=? WHERE id=? {$andStudio}",
            $params
        );
    }

    // ── Private helpers ───────────────────────────────────────────────────

    /**
     * Auto-genera el número correlativo del presupuesto: PRE-{YYYY}-{NNN}
     * El contador se reinicia por año y por estudio.
     */
    private function generarNumero(int $studioId): string
    {
        $year = (int) date('Y');
        $row  = $this->queryOne(
            "SELECT MAX(CAST(SUBSTRING_INDEX(numero, '-', -1) AS UNSIGNED)) AS ultimo
             FROM presupuestos
             WHERE studio_id = ? AND numero LIKE ?",
            [$studioId, "PRE-{$year}-%"]
        );
        $sig = (int) ($row['ultimo'] ?? 0) + 1;
        return sprintf('PRE-%d-%03d', $year, $sig);
    }
}
