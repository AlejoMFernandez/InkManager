<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Turno extends Model
{
    protected string $table  = 'turnos';
    protected bool   $scoped = true;

    /** Todos los turnos en rango para FullCalendar (JSON events) */
    public function enRango(string $desde, string $hasta): array
    {
        $sid    = $this->sid();
        $extra  = '';
        $params = [$desde, $hasta];
        if ($sid > 0) { $extra = 'AND tu.studio_id = ?'; $params[] = $sid; }

        return $this->query(
            "SELECT tu.*, c.nombre AS cliente_nombre
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.fecha_inicio BETWEEN ? AND ?
               {$extra}
             ORDER BY tu.fecha_inicio",
            $params
        );
    }

    /** Próximos N turnos (para dashboard) */
    public function proximos(int $limit = 10): array
    {
        $sid    = $this->sid();
        $extra  = '';
        $params = [];
        if ($sid > 0) { $extra = 'AND tu.studio_id = ?'; $params[] = $sid; }
        $params[] = $limit;

        return $this->query(
            "SELECT tu.*, c.nombre AS cliente_nombre
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.fecha_inicio >= NOW()
               AND tu.estado IN ('agendado','confirmado')
               {$extra}
             ORDER BY tu.fecha_inicio ASC
             LIMIT ?",
            $params
        );
    }

    public function crear(array $d): int
    {
        $sid = $this->sid();
        $this->execute(
            'INSERT INTO turnos (studio_id, cliente_id, tatuaje_id, fecha_inicio, duracion_min, estado, sena, notas)
             VALUES (?,?,?,?,?,?,?,?)',
            [
                $sid ?: 1,
                (int)   $d['cliente_id'],
                !empty($d['tatuaje_id']) ? (int) $d['tatuaje_id'] : null,
                $d['fecha_inicio'],
                (int) ($d['duracion_min'] ?? 60),
                $d['estado']           ?? 'agendado',
                !empty($d['sena'])      ? (float) $d['sena'] : null,
                $d['notas']            ?? null,
            ]
        );
        return $this->lastInsertId();
    }

    public function actualizar(int $id, array $d): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [
            (int)   $d['cliente_id'],
            !empty($d['tatuaje_id']) ? (int) $d['tatuaje_id'] : null,
            $d['fecha_inicio'],
            (int) ($d['duracion_min'] ?? 60),
            $d['estado']           ?? 'agendado',
            !empty($d['sena'])      ? (float) $d['sena'] : null,
            $d['notas']            ?? null,
            $id,
        ];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE turnos SET cliente_id=?,tatuaje_id=?,fecha_inicio=?,
             duracion_min=?,estado=?,sena=?,notas=? WHERE id=? {$andStudio}",
            $params
        );
    }

    public function reagendar(int $id, string $fechaInicio, int $duracionMin): bool
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [$fechaInicio, $duracionMin, $id];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE turnos SET fecha_inicio=?, duracion_min=? WHERE id=? {$andStudio}",
            $params
        );
    }

    public function cambiarEstado(int $id, string $estado): bool
    {
        $validos = ['agendado', 'confirmado', 'hecho', 'cancelado'];
        if (!in_array($estado, $validos, true)) return false;

        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND studio_id = ?' : '';
        $params    = [$estado, $id];
        if ($sid > 0) $params[] = $sid;

        return $this->execute(
            "UPDATE turnos SET estado=? WHERE id=? {$andStudio}",
            $params
        );
    }

    public function conCliente(int $id): ?array
    {
        $sid       = $this->sid();
        $andStudio = $sid > 0 ? 'AND tu.studio_id = ?' : '';
        $params    = [$id];
        if ($sid > 0) $params[] = $sid;

        return $this->queryOne(
            "SELECT tu.*, c.nombre AS cliente_nombre, c.instagram AS cliente_instagram,
                    c.telefono AS cliente_telefono
             FROM turnos tu JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.id = ? {$andStudio}",
            $params
        );
    }

    /**
     * Turnos que empiezan en ≤ 2 h, en estado activo, sin recordatorio enviado aún.
     * Usado por cli/send_reminders.php (cron) — no scoped intencionalmente.
     */
    public function pendientesRecordatorio(): array
    {
        return $this->query(
            "SELECT tu.*, c.nombre AS cliente_nombre, c.telefono
             FROM turnos tu
             JOIN clientes c ON c.id = tu.cliente_id
             WHERE tu.estado IN ('agendado','confirmado')
               AND tu.fecha_inicio BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 2 HOUR)
               AND tu.reminder_sent = 0
               AND c.telefono IS NOT NULL
               AND c.telefono != ''",
            []
        );
    }

    public function marcarReminderEnviado(int $id): bool
    {
        return $this->execute(
            'UPDATE turnos SET reminder_sent = 1 WHERE id = ?',
            [$id]
        );
    }
}
